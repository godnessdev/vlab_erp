<?php

namespace App\Domain\OrdemServico\Models;

use App\Domain\CatalogoServicos\Models\Servico;
use App\Domain\OrdemServico\Enums\StatusItemOrdem;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Item de Ordem de Serviço
 * 
 * @property string $id
 * @property string $ordem_servico_id
 * @property string $servico_id
 * @property int $sequencia
 * @property string $descricao
 * @property float $quantidade
 * @property float $preco_unitario
 * @property float $subtotal
 * @property float $quantidade_executada
 * @property StatusItemOrdem $status
 * @property \Carbon\Carbon $data_inicio
 * @property \Carbon\Carbon $data_conclusao
 * @property string $observacoes
 * 
 * @property-read OrdemServico $ordemServico
 * @property-read Servico $servico
 * @property-read \Illuminate\Database\Eloquent\Collection<ApontamentoExecucao> $apontamentos
 */
class ItemOrdemServico extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'itens_ordem_servico';

    protected $fillable = [
        'ordem_servico_id',
        'servico_id',
        'sequencia',
        'descricao',
        'quantidade',
        'preco_unitario',
        'subtotal',
        'quantidade_executada',
        'status',
        'data_inicio',
        'data_conclusao',
        'observacoes',
    ];

    protected $casts = [
        'status' => StatusItemOrdem::class,
        'quantidade' => 'decimal:2',
        'preco_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantidade_executada' => 'decimal:2',
        'data_inicio' => 'datetime',
        'data_conclusao' => 'datetime',
        'sequencia' => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ItemOrdemServicoFactory::new();
    }

    /**
     * Boot do modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Calcular subtotal se não fornecido
            if (empty($item->subtotal)) {
                $item->subtotal = $item->quantidade * $item->preco_unitario;
            }

            // Definir sequência se não fornecida
            if (empty($item->sequencia)) {
                $ultimaSequencia = static::where('ordem_servico_id', $item->ordem_servico_id)
                    ->max('sequencia') ?? 0;
                $item->sequencia = $ultimaSequencia + 1;
            }
        });

        static::updating(function ($item) {
            // Recalcular subtotal se quantidade ou preço mudaram
            if ($item->isDirty(['quantidade', 'preco_unitario'])) {
                $item->subtotal = $item->quantidade * $item->preco_unitario;
            }
        });

        static::saved(function ($item) {
            // Atualizar totais da ordem quando item é alterado
            $item->ordemServico->update([
                'valor_total_estimado' => $item->ordemServico->calcularValorEstimado()
            ]);
        });
    }

    /**
     * Relacionamentos
     */
    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function apontamentos(): HasMany
    {
        return $this->hasMany(ApontamentoExecucao::class, 'item_ordem_id');
    }

    /**
     * Scopes
     */
    public function scopePorStatus($query, StatusItemOrdem $status)
    {
        return $query->where('status', $status);
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', StatusItemOrdem::EM_ANDAMENTO);
    }

    public function scopeConcluidos($query)
    {
        return $query->where('status', StatusItemOrdem::CONCLUIDO);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', StatusItemOrdem::PENDENTE);
    }

    public function scopePorOrdem($query, $ordemId)
    {
        return $query->where('ordem_servico_id', $ordemId)->orderBy('sequencia');
    }

    /**
     * Métodos de negócio - Controle de Estado
     */
    public function podeTransicionarPara(StatusItemOrdem $novoStatus): bool
    {
        return $this->status->podeTransicionarPara($novoStatus);
    }

    public function iniciar(): bool
    {
        if (!$this->podeTransicionarPara(StatusItemOrdem::EM_ANDAMENTO)) {
            return false;
        }

        $this->update([
            'status' => StatusItemOrdem::EM_ANDAMENTO,
            'data_inicio' => now(),
        ]);

        return true;
    }

    public function pausar(): bool
    {
        if (!$this->podeTransicionarPara(StatusItemOrdem::PAUSADO)) {
            return false;
        }

        $this->update(['status' => StatusItemOrdem::PAUSADO]);
        return true;
    }

    public function retomar(): bool
    {
        if (!$this->podeTransicionarPara(StatusItemOrdem::EM_ANDAMENTO)) {
            return false;
        }

        $this->update(['status' => StatusItemOrdem::EM_ANDAMENTO]);
        return true;
    }

    public function concluir(): bool
    {
        if (!$this->podeTransicionarPara(StatusItemOrdem::CONCLUIDO)) {
            return false;
        }

        $this->update([
            'status' => StatusItemOrdem::CONCLUIDO,
            'data_conclusao' => now(),
            'quantidade_executada' => $this->quantidade,
        ]);

        return true;
    }

    public function cancelar(): bool
    {
        if (!$this->podeTransicionarPara(StatusItemOrdem::CANCELADO)) {
            return false;
        }

        $this->update(['status' => StatusItemOrdem::CANCELADO]);
        return true;
    }

    /**
     * Métodos de Cálculo
     */
    public function calcularHorasTrabalhadas(): float
    {
        return $this->apontamentos()->sum('horas_trabalhadas');
    }

    public function calcularValorExecutado(): float
    {
        return $this->apontamentos()
            ->where('aprovado', true)
            ->sum(\DB::raw('horas_trabalhadas * ' . $this->preco_unitario));
    }

    public function calcularPercentualConclusao(): float
    {
        if ($this->quantidade == 0) return 0;
        
        return ($this->quantidade_executada / $this->quantidade) * 100;
    }

    /**
     * Métodos de Validação
     */
    public function validarQuantidadeExecutada(float $quantidade): bool
    {
        return $quantidade >= 0 && $quantidade <= $this->quantidade;
    }

    public function adicionarExecucao(float $quantidade): bool
    {
        $novaQuantidade = $this->quantidade_executada + $quantidade;
        
        if (!$this->validarQuantidadeExecutada($novaQuantidade)) {
            return false;
        }

        $this->update(['quantidade_executada' => $novaQuantidade]);

        // Auto-completar item se quantidade foi atingida
        if ($novaQuantidade >= $this->quantidade && $this->status !== StatusItemOrdem::CONCLUIDO) {
            $this->concluir();
        }

        return true;
    }

    /**
     * Métodos Utilitários
     */
    public function isCompleto(): bool
    {
        return $this->status === StatusItemOrdem::CONCLUIDO;
    }

    public function isEmAndamento(): bool
    {
        return $this->status === StatusItemOrdem::EM_ANDAMENTO;
    }

    public function isPendente(): bool
    {
        return $this->status === StatusItemOrdem::PENDENTE;
    }

    public function isCancelado(): bool
    {
        return $this->status === StatusItemOrdem::CANCELADO;
    }

    /**
     * Acessores
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->getLabel();
    }

    public function getPercentualConclusaoAttribute(): float
    {
        return $this->calcularPercentualConclusao();
    }

    public function getValorExecutadoAttribute(): float
    {
        return $this->calcularValorExecutado();
    }

    public function getHorasTrabalhadasAttribute(): float
    {
        return $this->calcularHorasTrabalhadas();
    }
}
