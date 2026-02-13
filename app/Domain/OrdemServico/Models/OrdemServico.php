<?php

namespace App\Domain\OrdemServico\Models;

use App\Domain\OrdemServico\Enums\PrioridadeOrdem;
use App\Domain\OrdemServico\Enums\StatusOrdemServico;
use App\Models\Empresa;
use App\Models\Papel;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Ordem de Serviço com controle de workflow e estados
 * 
 * @property string $id
 * @property string $empresa_id
 * @property string $cliente_id
 * @property string $numero_ordem
 * @property string $titulo
 * @property string $descricao
 * @property \Carbon\Carbon $data_abertura
 * @property \Carbon\Carbon $data_prevista_inicio
 * @property \Carbon\Carbon $data_prevista_conclusao
 * @property \Carbon\Carbon $data_inicio_real
 * @property \Carbon\Carbon $data_conclusao_real
 * @property StatusOrdemServico $status
 * @property PrioridadeOrdem $prioridade
 * @property float $valor_total_estimado
 * @property float $valor_total_executado
 * @property string $observacoes
 * 
 * @property-read Empresa $empresa
 * @property-read Papel $cliente
 * @property-read \Illuminate\Database\Eloquent\Collection<ItemOrdemServico> $itens
 * @property-read \Illuminate\Database\Eloquent\Collection<ApontamentoExecucao> $apontamentos
 * @property-read \Illuminate\Database\Eloquent\Collection<HistoricoOrdem> $historico
 */
class OrdemServico extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'numero_ordem',
        'titulo',
        'descricao',
        'data_abertura',
        'data_prevista_inicio',
        'data_prevista_conclusao',
        'data_inicio_real',
        'data_conclusao_real',
        'status',
        'prioridade',
        'valor_total_estimado',
        'valor_total_executado',
        'observacoes',
    ];

    protected $casts = [
        'status' => StatusOrdemServico::class,
        'prioridade' => PrioridadeOrdem::class,
        'data_abertura' => 'datetime',
        'data_prevista_inicio' => 'date',
        'data_prevista_conclusao' => 'date',
        'data_inicio_real' => 'datetime',
        'data_conclusao_real' => 'datetime',
        'valor_total_estimado' => 'decimal:2',
        'valor_total_executado' => 'decimal:2',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\OrdemServicoFactory::new();
    }

    /**
     * Boot do modelo - eventos e observadores
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ordem) {
            if (empty($ordem->numero_ordem)) {
                $ordem->numero_ordem = static::gerarNumeroOrdem($ordem->empresa_id);
            }
            
            if (empty($ordem->data_abertura)) {
                $ordem->data_abertura = now();
            }
        });

        static::updating(function ($ordem) {
            // Recalcular valores quando itens são alterados
            if ($ordem->isDirty(['valor_total_estimado'])) {
                static::recalcularValores($ordem);
            }
        });
    }

    /**
     * Relacionamentos
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Papel::class, 'cliente_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemOrdemServico::class)->orderBy('sequencia');
    }

    public function apontamentos(): HasMany
    {
        return $this->hasMany(ApontamentoExecucao::class)->orderBy('data_apontamento', 'desc');
    }

    public function historico(): HasMany
    {
        return $this->hasMany(HistoricoOrdem::class)->orderBy('data_evento', 'desc');
    }

    /**
     * Scopes
     */
    public function scopeAtivas($query)
    {
        return $query->whereIn('status', [
            StatusOrdemServico::ABERTA,
            StatusOrdemServico::EM_ANDAMENTO,
            StatusOrdemServico::PAUSADA,
            StatusOrdemServico::CONCLUIDA
        ]);
    }

    public function scopePorStatus($query, StatusOrdemServico $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorPrioridade($query, PrioridadeOrdem $prioridade)
    {
        return $query->where('prioridade', $prioridade);
    }

    public function scopeEmAtraso($query)
    {
        return $query->where('data_prevista_conclusao', '<', now())
            ->whereNotIn('status', [StatusOrdemServico::CONCLUIDA, StatusOrdemServico::FATURADA, StatusOrdemServico::CANCELADA]);
    }

    public function scopePorCliente($query, string $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    /**
     * Métodos de negócio - Estado e Transições
     */
    public function podeTransicionarPara(StatusOrdemServico $novoStatus): bool
    {
        return $this->status->podeTransicionarPara($novoStatus);
    }

    public function iniciar(): bool
    {
        if (!$this->podeTransicionarPara(StatusOrdemServico::EM_ANDAMENTO)) {
            return false;
        }

        // Validar se há pelo menos um item
        if ($this->itens()->count() === 0) {
            throw new \InvalidArgumentException('Ordem deve ter pelo menos um item para ser iniciada.');
        }

        $this->update([
            'status' => StatusOrdemServico::EM_ANDAMENTO,
            'data_inicio_real' => now(),
        ]);

        return true;
    }

    public function pausar(): bool
    {
        if (!$this->podeTransicionarPara(StatusOrdemServico::PAUSADA)) {
            return false;
        }

        $this->update(['status' => StatusOrdemServico::PAUSADA]);
        return true;
    }

    public function retomar(): bool
    {
        if (!$this->podeTransicionarPara(StatusOrdemServico::EM_ANDAMENTO)) {
            return false;
        }

        $this->update(['status' => StatusOrdemServico::EM_ANDAMENTO]);
        return true;
    }

    public function concluir(): bool
    {
        if (!$this->podeTransicionarPara(StatusOrdemServico::CONCLUIDA)) {
            return false;
        }

        // Validar se todos os itens estão concluídos
        $itensAtivos = $this->itens()->where('status', '!=', 'CONCLUIDO')->count();
        if ($itensAtivos > 0) {
            throw new \InvalidArgumentException('Todos os itens devem estar concluídos para finalizar a ordem.');
        }

        $this->update([
            'status' => StatusOrdemServico::CONCLUIDA,
            'data_conclusao_real' => now(),
        ]);

        return true;
    }

    public function cancelar(): bool
    {
        if (!$this->podeTransicionarPara(StatusOrdemServico::CANCELADA)) {
            return false;
        }

        $this->update(['status' => StatusOrdemServico::CANCELADA]);
        return true;
    }

    public function faturar(): bool
    {
        if (!$this->podeTransicionarPara(StatusOrdemServico::FATURADA)) {
            return false;
        }

        $this->update(['status' => StatusOrdemServico::FATURADA]);
        return true;
    }

    /**
     * Métodos de Cálculo
     */
    public function calcularValorEstimado(): float
    {
        return $this->itens->sum('subtotal');
    }

    public function calcularValorExecutado(): float
    {
        return $this->apontamentos()
            ->where('aprovado', true)
            ->sum(DB::raw('horas_trabalhadas * (SELECT preco_unitario FROM itens_ordem_servico WHERE id = item_ordem_id)'));
    }

    public function calcularPercentualConclusao(): float
    {
        $totalItens = $this->itens->sum('quantidade');
        if ($totalItens == 0) return 0;

        $totalExecutado = $this->itens->sum('quantidade_executada');
        return ($totalExecutado / $totalItens) * 100;
    }

    public function calcularHorasTrabalhadas(): float
    {
        return $this->apontamentos()->sum('horas_trabalhadas');
    }

    public function isEmAtraso(): bool
    {
        return $this->data_prevista_conclusao &&
               $this->data_prevista_conclusao->isPast() &&
               $this->status->isAtiva();
    }

    public function getDiasAtraso(): int
    {
        if (!$this->isEmAtraso()) return 0;
        
        return now()->diffInDays($this->data_prevista_conclusao);
    }

    /**
     * Métodos Utilitários
     */
    public static function gerarNumeroOrdem(string $empresaId): string
    {
        $ano = now()->format('Y');
        $mes = now()->format('m');
        
        $ultimo = static::where('empresa_id', $empresaId)
            ->where('numero_ordem', 'LIKE', "OS-{$ano}-{$mes}-%")
            ->orderBy('numero_ordem', 'desc')
            ->first();

        if ($ultimo) {
            $ultimoNumero = (int) substr($ultimo->numero_ordem, -4);
            $proximoNumero = $ultimoNumero + 1;
        } else {
            $proximoNumero = 1;
        }

        return sprintf('OS-%s-%s-%04d', $ano, $mes, $proximoNumero);
    }

    protected static function recalcularValores($ordem): void
    {
        $ordem->valor_total_estimado = $ordem->calcularValorEstimado();
        $ordem->valor_total_executado = $ordem->calcularValorExecutado();
    }

    /**
     * Acessores e Mutadores
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->getLabel();
    }

    public function getPrioridadeLabelAttribute(): string
    {
        return $this->prioridade->getLabel();
    }

    public function getPercentualConclusaoAttribute(): float
    {
        return $this->calcularPercentualConclusao();
    }

    public function getEmAtrasoAttribute(): bool
    {
        return $this->isEmAtraso();
    }
}
