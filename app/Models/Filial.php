<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Filial extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'filiais';

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nome',
        'tipo',
        'cnpj',
        'inscricao_estadual',
        'inscricao_municipal',
        'email',
        'telefone',
        'ativo',
        'endereco',
        'configuracao_fiscal',
    ];

    protected $casts = [
        'tipo' => TipoFilialEnum::class,
        'ativo' => 'boolean',
        'endereco' => 'array',
        'configuracao_fiscal' => 'array',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relacionamentos
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

    public function configuracoesFiscais(): HasMany
    {
        return $this->hasMany(ConfiguracaoFiscal::class);
    }

    /**
     * Scopes
     */
    public function scopeAtivas($query)
    {
        return $query->where('status', StatusFilialEnum::ATIVO);
    }

    public function scopePorEmpresa($query, string $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeMatriz($query)
    {
        return $query->where('nome', 'MATRIZ');
    }

    /**
     * Métodos de negócio
     */
    public function isAtiva(): bool
    {
        return $this->status === StatusFilialEnum::ATIVO;
    }

    public function isMatriz(): bool
    {
        return strtoupper($this->nome) === 'MATRIZ';
    }

    public function podeSerExcluida(): bool
    {
        return !$this->isMatriz() && $this->configuracoesFiscais()->count() === 0;
    }

    public function formatarCnpj(): ?string
    {
        if (!$this->cnpj_filial) {
            return null;
        }

        $cnpj = preg_replace('/\D/', '', $this->cnpj_filial);
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    public function getNomeCompleto(): string
    {
        return $this->empresa->nome . ' - ' . $this->nome;
    }

    /**
     * Mutators
     */
    public function setCnpjFilialAttribute(?string $value): void
    {
        if ($value) {
            // Remove formatação do CNPJ
            $this->attributes['cnpj_filial'] = preg_replace('/\D/', '', $value);
        } else {
            $this->attributes['cnpj_filial'] = null;
        }
    }

    /**
     * Validações customizadas
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($filial) {
            // Validar nome único por empresa
            if (static::where('empresa_id', $filial->empresa_id)
                ->where('nome', $filial->nome)->exists()) {
                throw new \InvalidArgumentException('Nome de filial já existe para esta empresa.');
            }

            // Validar CNPJ único se informado
            if ($filial->cnpj_filial && static::where('cnpj_filial', $filial->cnpj_filial)->exists()) {
                throw new \InvalidArgumentException('CNPJ da filial já cadastrado no sistema.');
            }
        });

        static::updating(function ($filial) {
            // Validar nome único por empresa (exceto o próprio registro)
            if (static::where('empresa_id', $filial->empresa_id)
                ->where('nome', $filial->nome)
                ->where('id', '!=', $filial->id)->exists()) {
                throw new \InvalidArgumentException('Nome de filial já existe para esta empresa.');
            }

            // Validar CNPJ único se informado (exceto o próprio registro)
            if ($filial->cnpj_filial && static::where('cnpj_filial', $filial->cnpj_filial)
                ->where('id', '!=', $filial->id)->exists()) {
                throw new \InvalidArgumentException('CNPJ da filial já cadastrado no sistema.');
            }
        });

        static::deleting(function ($filial) {
            if ($filial->isMatriz()) {
                throw new \InvalidArgumentException('Não é possível excluir a filial matriz.');
            }
        });
    }
}
