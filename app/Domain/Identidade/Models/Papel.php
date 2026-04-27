<?php

namespace App\Domain\Identidade\Models;

use App\Domain\Identidade\Enums\StatusPapel;
use App\Domain\Identidade\Enums\TipoPapel;
use App\Domain\Identidade\Factories\PapelFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Papel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'papeis';

    protected $fillable = [
        'pessoa_id',
        'empresa_id',
        'tipo_papel',
        'data_inicio',
        'data_fim',
        'status',
    ];

    protected $casts = [
        'tipo_papel' => TipoPapel::class,
        'status' => StatusPapel::class,
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    /**
     * Relacionamentos
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    // empresa() será implementado quando criarmos o domínio Empresa
    // public function empresa(): BelongsTo
    // {
    //     return $this->belongsTo(Empresa::class);
    // }

    public function dadosEspecificos(): HasMany
    {
        return $this->hasMany(DadosEspecificosPapel::class, 'papel_id');
    }

    /**
     * Scopes
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', StatusPapel::ATIVO)->whereNull('data_fim');
    }

    public function scopeInativos($query)
    {
        return $query->where('status', StatusPapel::INATIVO)->orWhereNotNull('data_fim');
    }

    public function scopePorTipo($query, TipoPapel $tipo)
    {
        return $query->where('tipo_papel', $tipo);
    }

    public function scopePorEmpresa($query, string $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeVigentes($query, ?Carbon $data = null)
    {
        $data = $data ?? now();

        return $query->where('data_inicio', '<=', $data)
            ->where(function ($q) use ($data) {
                $q->whereNull('data_fim')
                    ->orWhere('data_fim', '>=', $data);
            });
    }

    public function scopeHistorico($query)
    {
        return $query->whereNotNull('data_fim');
    }

    /**
     * Business Methods
     */
    public function isAtivo(): bool
    {
        return $this->status === StatusPapel::ATIVO && is_null($this->data_fim);
    }

    public function isInativo(): bool
    {
        return $this->status === StatusPapel::INATIVO || ! is_null($this->data_fim);
    }

    public function isVigente(?Carbon $data = null): bool
    {
        $data = $data ?? now();

        $iniciouVigencia = $this->data_inicio <= $data;
        $naoExpirou = is_null($this->data_fim) || $this->data_fim >= $data;

        return $iniciouVigencia && $naoExpirou && $this->isAtivo();
    }

    public function getDuracaoEmDias(): ?int
    {
        if (! $this->data_fim) {
            return $this->data_inicio->diffInDays(now());
        }

        return $this->data_inicio->diffInDays($this->data_fim);
    }

    public function getDuracaoEmMeses(): ?int
    {
        if (! $this->data_fim) {
            return $this->data_inicio->diffInMonths(now());
        }

        return $this->data_inicio->diffInMonths($this->data_fim);
    }

    public function ativar(): void
    {
        $this->update([
            'status' => StatusPapel::ATIVO,
            'data_fim' => null,
        ]);
    }

    public function inativar(?Carbon $dataFim = null): void
    {
        $this->update([
            'status' => StatusPapel::INATIVO,
            'data_fim' => $dataFim ?? now(),
        ]);
    }

    public function encerrar(?Carbon $dataFim = null): void
    {
        $this->update([
            'data_fim' => $dataFim ?? now(),
        ]);
    }

    public function prorrogar(Carbon $novaDataFim): void
    {
        $this->update(['data_fim' => $novaDataFim]);
    }

    public function removerDataFim(): void
    {
        $this->update(['data_fim' => null]);
    }

    /**
     * Dados Específicos
     */
    public function adicionarDado(string $chave, $valor): DadoEspecificoPapel
    {
        return $this->dadosEspecificos()->updateOrCreate(
            ['chave' => $chave],
            ['valor' => $valor]
        );
    }

    public function obterDado(string $chave, $default = null)
    {
        $dado = $this->dadosEspecificos()->where('chave', $chave)->first();

        if (! $dado) {
            return $default;
        }

        $valor = $dado->valor;

        // Tentar converter para número se for numérico
        if (is_numeric($valor)) {
            if (strpos($valor, '.') !== false) {
                return (float) $valor;
            } else {
                return (int) $valor;
            }
        }

        return $valor;
    }

    public function removerDado(string $chave): bool
    {
        return $this->dadosEspecificos()->where('chave', $chave)->delete();
    }

    public function getDadosArray(): array
    {
        return $this->dadosEspecificos()
            ->pluck('valor', 'chave')
            ->toArray();
    }

    /**
     * Permissões
     */
    public function getPermissoes(): array
    {
        return $this->tipo_papel->getPermissions();
    }

    public function temPermissao(string $permissao): bool
    {
        return in_array($permissao, $this->getPermissoes());
    }

    public function isInterno(): bool
    {
        return $this->tipo_papel->isInterno();
    }

    public function isExterno(): bool
    {
        return $this->tipo_papel->isExterno();
    }

    /**
     * Accessors
     */
    public function getDescricaoCompletaAttribute(): string
    {
        $descricao = $this->tipo_papel->label();

        if ($this->data_fim) {
            $descricao .= " (Encerrado em {$this->data_fim->format('d/m/Y')})";
        } elseif ($this->isInativo()) {
            $descricao .= ' (Inativo)';
        }

        return $descricao;
    }

    public function getPeriodoVigenciaAttribute(): string
    {
        $inicio = $this->data_inicio->format('d/m/Y');

        if ($this->data_fim) {
            return "{$inicio} até {$this->data_fim->format('d/m/Y')}";
        }

        return "Desde {$inicio}";
    }

    /**
     * Factory
     */
    protected static function newFactory()
    {
        return PapelFactory::new();
    }
}
