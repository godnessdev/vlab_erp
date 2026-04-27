<?php

namespace App\Domain\OrdemServico\Models;

use App\Domain\OrdemServico\Enums\StatusItemOrdem;
use App\Models\Usuario;
use Carbon\Carbon;
use Database\Factories\ApontamentoExecucaoFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apontamento de Execução
 *
 * @property string $id
 * @property string $ordem_servico_id
 * @property string $item_ordem_id
 * @property string $usuario_id
 * @property Carbon $data_apontamento
 * @property float $horas_trabalhadas
 * @property string $descricao_atividade
 * @property array $observacoes
 * @property bool $aprovado
 * @property string $aprovado_por
 * @property Carbon $data_aprovacao
 * @property-read OrdemServico $ordemServico
 * @property-read ItemOrdemServico $itemOrdem
 * @property-read Usuario $usuario
 * @property-read Usuario $aprovadoPor
 */
class ApontamentoExecucao extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'apontamentos_execucao';

    protected $fillable = [
        'ordem_servico_id',
        'item_ordem_id',
        'usuario_id',
        'data_apontamento',
        'horas_trabalhadas',
        'descricao_atividade',
        'observacoes',
        'aprovado',
        'aprovado_por',
        'data_aprovacao',
    ];

    protected $casts = [
        'data_apontamento' => 'datetime',
        'horas_trabalhadas' => 'decimal:2',
        'observacoes' => 'array',
        'aprovado' => 'boolean',
        'data_aprovacao' => 'datetime',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ApontamentoExecucaoFactory::new();
    }

    /**
     * Boot do modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($apontamento) {
            if (empty($apontamento->data_apontamento)) {
                $apontamento->data_apontamento = now();
            }
        });

        static::saved(function ($apontamento) {
            // Atualizar quantidade executada do item se aprovado
            if ($apontamento->aprovado && $apontamento->wasChanged('aprovado')) {
                $item = $apontamento->itemOrdem;
                $totalHoras = $item->apontamentos()->where('aprovado', true)->sum('horas_trabalhadas');

                // Converter horas para quantidade baseado no tipo de serviço
                $novaQuantidade = min($totalHoras, $item->quantidade);
                $item->update(['quantidade_executada' => $novaQuantidade]);

                // Auto-iniciar item se ainda estiver pendente
                if ($item->status === StatusItemOrdem::PENDENTE) {
                    $item->iniciar();
                }
            }
        });
    }

    /**
     * Relacionamentos
     */
    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function itemOrdem(): BelongsTo
    {
        return $this->belongsTo(ItemOrdemServico::class, 'item_ordem_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'aprovado_por');
    }

    /**
     * Scopes
     */
    public function scopeAprovados($query)
    {
        return $query->where('aprovado', true);
    }

    public function scopePendentesAprovacao($query)
    {
        return $query->where('aprovado', false);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_apontamento', [$dataInicio, $dataFim]);
    }

    public function scopePorOrdem($query, $ordemId)
    {
        return $query->where('ordem_servico_id', $ordemId);
    }

    public function scopePorItem($query, $itemId)
    {
        return $query->where('item_ordem_id', $itemId);
    }

    public function scopeHoje($query)
    {
        return $query->whereDate('data_apontamento', today());
    }

    public function scopeEssaSemana($query)
    {
        return $query->whereBetween('data_apontamento', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeEsseMes($query)
    {
        return $query->whereBetween('data_apontamento', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }

    /**
     * Métodos de negócio
     */
    public function aprovar(string $aprovadorId): bool
    {
        if ($this->aprovado) {
            return false; // Já aprovado
        }

        $this->update([
            'aprovado' => true,
            'aprovado_por' => $aprovadorId,
            'data_aprovacao' => now(),
        ]);

        return true;
    }

    public function reprovar(): bool
    {
        if (! $this->aprovado) {
            return false; // Já reprovado
        }

        $this->update([
            'aprovado' => false,
            'aprovado_por' => null,
            'data_aprovacao' => null,
        ]);

        return true;
    }

    public function calcularValor(): float
    {
        $precoHora = $this->itemOrdem->preco_unitario;

        return $this->horas_trabalhadas * $precoHora;
    }

    /**
     * Validações de negócio
     */
    public function validarHoras(): bool
    {
        // Não pode ter mais de 24 horas por dia
        if ($this->horas_trabalhadas > 24) {
            return false;
        }

        // Validar se não ultrapassa o limite diário do usuário
        $horasHoje = static::where('usuario_id', $this->usuario_id)
            ->whereDate('data_apontamento', $this->data_apontamento->toDateString())
            ->where('id', '!=', $this->id)
            ->sum('horas_trabalhadas');

        return ($horasHoje + $this->horas_trabalhadas) <= 24;
    }

    public function validarData(): bool
    {
        // Não pode ser no futuro
        if ($this->data_apontamento->isFuture()) {
            return false;
        }

        // Não pode ser muito antigo (mais de 30 dias)
        if ($this->data_apontamento->diffInDays(now()) > 30) {
            return false;
        }

        return true;
    }

    /**
     * Métodos Utilitários
     */
    public function isAprovado(): bool
    {
        return $this->aprovado === true;
    }

    public function isPendente(): bool
    {
        return $this->aprovado === false;
    }

    public function getStatusApontamento(): string
    {
        return $this->aprovado ? 'Aprovado' : 'Pendente';
    }

    /**
     * Acessores
     */
    public function getValorCalculadoAttribute(): float
    {
        return $this->calcularValor();
    }

    public function getStatusAttribute(): string
    {
        return $this->getStatusApontamento();
    }

    public function getHorasFormatadaAttribute(): string
    {
        $horas = floor($this->horas_trabalhadas);
        $minutos = ($this->horas_trabalhadas - $horas) * 60;

        return sprintf('%02d:%02d', $horas, $minutos);
    }

    /**
     * Mutadores
     */
    public function setObservacoesAttribute($value)
    {
        $this->attributes['observacoes'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getObservacoesAttribute($value)
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }
}
