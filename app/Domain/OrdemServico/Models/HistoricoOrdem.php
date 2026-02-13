<?php

namespace App\Domain\OrdemServico\Models;

use App\Domain\OrdemServico\Enums\TipoEventoHistorico;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Histórico de mudanças da Ordem de Serviço
 * 
 * @property string $id
 * @property string $ordem_servico_id
 * @property string $usuario_id
 * @property \Carbon\Carbon $data_evento
 * @property TipoEventoHistorico $tipo_evento
 * @property string $descricao
 * @property array $campos_alterados
 * @property array $valores_anteriores
 * @property array $valores_novos
 * 
 * @property-read OrdemServico $ordemServico
 * @property-read Usuario $usuario
 */
class HistoricoOrdem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'historico_ordem';

    protected $fillable = [
        'ordem_servico_id',
        'usuario_id',
        'data_evento',
        'tipo_evento',
        'descricao',
        'campos_alterados',
        'valores_anteriores',
        'valores_novos',
    ];

    protected $casts = [
        'tipo_evento' => TipoEventoHistorico::class,
        'data_evento' => 'datetime',
        'campos_alterados' => 'array',
        'valores_anteriores' => 'array',
        'valores_novos' => 'array',
    ];

    /**
     * Desabilitar timestamps automáticos
     */
    public $timestamps = false;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\HistoricoOrdemFactory::new();
    }

    /**
     * Boot do modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($historico) {
            if (empty($historico->data_evento)) {
                $historico->data_evento = now();
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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Scopes
     */
    public function scopePorOrdem($query, $ordemId)
    {
        return $query->where('ordem_servico_id', $ordemId);
    }

    public function scopePorTipoEvento($query, TipoEventoHistorico $tipoEvento)
    {
        return $query->where('tipo_evento', $tipoEvento);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_evento', [$dataInicio, $dataFim]);
    }

    public function scopeRecentes($query, $limite = 10)
    {
        return $query->orderBy('data_evento', 'desc')->limit($limite);
    }

    public function scopeHoje($query)
    {
        return $query->whereDate('data_evento', today());
    }

    public function scopeEssaSemana($query)
    {
        return $query->whereBetween('data_evento', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeEsseMes($query)
    {
        return $query->whereBetween('data_evento', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    /**
     * Métodos estáticos para criação de histórico
     */
    public static function registrarCriacao(OrdemServico $ordem, Usuario $usuario): self
    {
        return static::create([
            'ordem_servico_id' => $ordem->id,
            'usuario_id' => $usuario->id,
            'tipo_evento' => TipoEventoHistorico::CRIACAO,
            'descricao' => 'Ordem de serviço criada',
            'valores_novos' => $ordem->toArray(),
        ]);
    }

    public static function registrarMudancaStatus(
        OrdemServico $ordem,
        Usuario $usuario,
        $statusAnterior,
        $statusNovo
    ): self {
        return static::create([
            'ordem_servico_id' => $ordem->id,
            'usuario_id' => $usuario->id,
            'tipo_evento' => TipoEventoHistorico::MUDANCA_STATUS,
            'descricao' => "Status alterado de {$statusAnterior->getLabel()} para {$statusNovo->getLabel()}",
            'campos_alterados' => ['status'],
            'valores_anteriores' => ['status' => $statusAnterior->value],
            'valores_novos' => ['status' => $statusNovo->value],
        ]);
    }

    public static function registrarEdicao(
        OrdemServico $ordem,
        Usuario $usuario,
        array $camposAlterados
    ): self {
        $valoresAnteriores = [];
        $valoresNovos = [];
        $campos = [];

        foreach ($camposAlterados as $campo => $valores) {
            $campos[] = $campo;
            $valoresAnteriores[$campo] = $valores['old'] ?? null;
            $valoresNovos[$campo] = $valores['new'] ?? null;
        }

        return static::create([
            'ordem_servico_id' => $ordem->id,
            'usuario_id' => $usuario->id,
            'tipo_evento' => TipoEventoHistorico::EDICAO,
            'descricao' => 'Dados da ordem alterados: ' . implode(', ', $campos),
            'campos_alterados' => $campos,
            'valores_anteriores' => $valoresAnteriores,
            'valores_novos' => $valoresNovos,
        ]);
    }

    public static function registrarApontamento(
        OrdemServico $ordem,
        Usuario $usuario,
        ApontamentoExecucao $apontamento
    ): self {
        return static::create([
            'ordem_servico_id' => $ordem->id,
            'usuario_id' => $usuario->id,
            'tipo_evento' => TipoEventoHistorico::APONTAMENTO,
            'descricao' => "Apontamento registrado: {$apontamento->horas_trabalhadas}h",
            'valores_novos' => [
                'apontamento_id' => $apontamento->id,
                'horas_trabalhadas' => $apontamento->horas_trabalhadas,
                'descricao_atividade' => $apontamento->descricao_atividade,
            ],
        ]);
    }

    public static function registrarAprovacaoApontamento(
        OrdemServico $ordem,
        Usuario $aprovador,
        ApontamentoExecucao $apontamento
    ): self {
        return static::create([
            'ordem_servico_id' => $ordem->id,
            'usuario_id' => $aprovador->id,
            'tipo_evento' => TipoEventoHistorico::APROVACAO,
            'descricao' => "Apontamento aprovado: {$apontamento->horas_trabalhadas}h",
            'valores_novos' => [
                'apontamento_id' => $apontamento->id,
                'aprovado_por' => $aprovador->nome,
            ],
        ]);
    }

    public static function registrarComentario(
        OrdemServico $ordem,
        Usuario $usuario,
        string $comentario
    ): self {
        return static::create([
            'ordem_servico_id' => $ordem->id,
            'usuario_id' => $usuario->id,
            'tipo_evento' => TipoEventoHistorico::COMENTARIO,
            'descricao' => 'Comentário adicionado',
            'valores_novos' => [
                'comentario' => $comentario,
            ],
        ]);
    }

    public static function registrarCancelamento(
        OrdemServico $ordem,
        Usuario $usuario,
        string $motivo = null
    ): self {
        return static::create([
            'ordem_servico_id' => $ordem->id,
            'usuario_id' => $usuario->id,
            'tipo_evento' => TipoEventoHistorico::CANCELAMENTO,
            'descricao' => 'Ordem de serviço cancelada' . ($motivo ? ": {$motivo}" : ''),
            'valores_novos' => [
                'motivo_cancelamento' => $motivo,
            ],
        ]);
    }

    /**
     * Métodos Utilitários
     */
    public function getResumoMudancas(): string
    {
        if (empty($this->campos_alterados)) {
            return $this->descricao;
        }

        $mudancas = [];
        foreach ($this->campos_alterados as $campo) {
            $valorAnterior = $this->valores_anteriores[$campo] ?? 'N/A';
            $valorNovo = $this->valores_novos[$campo] ?? 'N/A';
            $mudancas[] = "{$campo}: {$valorAnterior} → {$valorNovo}";
        }

        return implode(', ', $mudancas);
    }

    public function isEventoCritico(): bool
    {
        return in_array($this->tipo_evento, [
            TipoEventoHistorico::CANCELAMENTO,
            TipoEventoHistorico::MUDANCA_STATUS
        ]);
    }

    /**
     * Acessores
     */
    public function getTipoEventoLabelAttribute(): string
    {
        return $this->tipo_evento->getLabel();
    }

    public function getTipoEventoIconeAttribute(): string
    {
        return $this->tipo_evento->getIcone();
    }

    public function getTipoEventoCorAttribute(): string
    {
        return $this->tipo_evento->getCor();
    }

    public function getResumoAttribute(): string
    {
        return $this->getResumoMudancas();
    }

    public function getTempoDecorridoAttribute(): string
    {
        return $this->data_evento->diffForHumans();
    }
}
