<?php

namespace App\Models;

use App\Domain\Identidade\Models\DadosEspecificosPapel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsuarioEmpresaPapel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'usuario_empresa_papel';

    protected $fillable = [
        'usuario_id',
        'empresa_id',
        'papel_id',
        'data_inicio',
        'data_fim',
        'status',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    protected $dates = [
        'data_inicio',
        'data_fim',
    ];

    /**
     * Relacionamentos
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function papel(): BelongsTo
    {
        return $this->belongsTo(Papel::class);
    }

    public function dadosEspecificos(): HasMany
    {
        return $this->hasMany(DadosEspecificosPapel::class, 'papel_id');
    }

    /**
     * Scopes
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ATIVO');
    }

    public function scopeVigente($query)
    {
        $hoje = now()->toDateString();

        return $query->where('data_inicio', '<=', $hoje)
            ->where(function ($q) use ($hoje) {
                $q->whereNull('data_fim')
                    ->orWhere('data_fim', '>=', $hoje);
            });
    }

    public function scopeExpirados($query)
    {
        $hoje = now()->toDateString();

        return $query->whereNotNull('data_fim')
            ->where('data_fim', '<', $hoje);
    }

    /**
     * Métodos auxiliares
     */
    public function isVigente(): bool
    {
        $hoje = now()->toDate();

        if ($this->data_inicio > $hoje) {
            return false; // Ainda não iniciou
        }

        if ($this->data_fim && $this->data_fim < $hoje) {
            return false; // Já expirou
        }

        return true;
    }

    public function isExpirado(): bool
    {
        return $this->data_fim && $this->data_fim < now()->toDate();
    }

    public function diasRestantes(): ?int
    {
        if (! $this->data_fim) {
            return null; // Indefinido
        }

        $dias = now()->diffInDays($this->data_fim, false);

        return $dias >= 0 ? $dias : 0;
    }

    /**
     * Obter valor de um dado específico
     */
    public function obterDado(string $chave): mixed
    {
        $dado = $this->dadosEspecificos()->where('chave', $chave)->first();

        return $dado ? $dado->valor_limpo : null;
    }

    /**
     * Definir valor de um dado específico
     */
    public function definirDado(string $chave, mixed $valor): void
    {
        $this->dadosEspecificos()->updateOrCreate(
            ['chave' => $chave],
            ['valor' => $valor]
        );
    }
}
