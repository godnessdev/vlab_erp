<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'empresas';

    protected $fillable = [
        'nome',
        'cnpj',
        'ie',
        'im',
        'regime_tributario',
        'data_constituicao',
        'email_contato',
        'telefone_contato',
        'status',
    ];

    protected $casts = [
        'data_constituicao' => 'date',
        'regime_tributario' => RegimeTributarioEnum::class,
        'status' => StatusEmpresaEnum::class,
        'dados_endereco' => 'array',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relacionamentos
     */
    public function filiais(): HasMany
    {
        return $this->hasMany(Filial::class);
    }

    public function parametrosOperacionais(): HasMany
    {
        return $this->hasMany(ParametroOperacional::class);
    }

    public function configuracoesFiscais(): HasMany
    {
        return $this->hasMany(ConfiguracaoFiscal::class);
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'usuario_empresa_papel')
            ->withPivot(['papel_id', 'data_inicio', 'data_fim', 'status'])
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeAtivas($query)
    {
        return $query->where('status', StatusEmpresaEnum::ATIVO);
    }

    public function scopePorRegime($query, RegimeTributarioEnum $regime)
    {
        return $query->where('regime_tributario', $regime);
    }

    public function scopePorCnpj($query, string $cnpj)
    {
        return $query->where('cnpj', $cnpj);
    }

    /**
     * Métodos de negócio
     */
    public function isAtiva(): bool
    {
        return $this->status === StatusEmpresaEnum::ATIVO;
    }

    public function podeSerExcluida(): bool
    {
        return $this->filiais()->count() === 0
            && $this->usuarios()->count() === 0;
    }

    public function formatarCnpj(): string
    {
        $cnpj = preg_replace('/\D/', '', $this->cnpj);

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    public function getFilialPrincipal(): ?Filial
    {
        return $this->filiais()->where('nome', 'MATRIZ')->first();
    }

    /**
     * Mutators
     */
    public function setCnpjAttribute(string $value): void
    {
        // Remove formatação do CNPJ
        $this->attributes['cnpj'] = preg_replace('/\D/', '', $value);
    }

    /**
     * Validações customizadas
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($empresa) {
            // Validar CNPJ único
            if (static::where('cnpj', $empresa->cnpj)->exists()) {
                throw new \InvalidArgumentException('CNPJ já cadastrado no sistema.');
            }
        });

        static::updating(function ($empresa) {
            // Validar CNPJ único (exceto o próprio registro)
            if (static::where('cnpj', $empresa->cnpj)
                ->where('id', '!=', $empresa->id)->exists()) {
                throw new \InvalidArgumentException('CNPJ já cadastrado no sistema.');
            }
        });
    }
}
