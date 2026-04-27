<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParametroOperacional extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'parametros_operacionais';

    protected $fillable = [
        'empresa_id',
        'chave',
        'valor',
        'tipo',
        'descricao',
        'publico',
    ];

    protected $casts = [
        'valor' => 'json',
        'publico' => 'boolean',
    ];

    /**
     * Relacionamentos
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Scopes
     */
    public function scopePorEmpresa($query, string $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopePorChave($query, string $chave)
    {
        return $query->where('chave', $chave);
    }

    public function scopePorTipo($query, TipoParametroEnum $tipo)
    {
        return $query->where('tipo_valor', $tipo);
    }

    /**
     * Métodos de negócio
     */
    public function getValorTipado()
    {
        return match ($this->tipo_valor) {
            TipoParametroEnum::STRING => (string) $this->valor,
            TipoParametroEnum::INTEGER => (int) $this->valor,
            TipoParametroEnum::DECIMAL => (float) $this->valor,
            TipoParametroEnum::BOOLEAN => (bool) $this->valor,
            TipoParametroEnum::JSON => $this->valor,
            TipoParametroEnum::ARRAY => is_array($this->valor) ? $this->valor : json_decode($this->valor, true),
            default => $this->valor,
        };
    }

    public function setValorTipado($valor): void
    {
        $this->valor = match ($this->tipo_valor) {
            TipoParametroEnum::JSON, TipoParametroEnum::ARRAY => is_string($valor) ? $valor : json_encode($valor),
            default => $valor,
        };
    }

    /**
     * Métodos estáticos para parâmetros comuns
     */
    public static function getParametro(string $empresaId, string $chave, $default = null)
    {
        $parametro = static::where('empresa_id', $empresaId)
            ->where('chave', $chave)
            ->first();

        return $parametro ? $parametro->getValorTipado() : $default;
    }

    public static function setParametro(string $empresaId, string $chave, $valor, string $descricao = '', TipoParametroEnum $tipo = TipoParametroEnum::STRING): self
    {
        $parametro = static::updateOrCreate(
            ['empresa_id' => $empresaId, 'chave' => $chave],
            ['descricao' => $descricao, 'tipo_valor' => $tipo]
        );

        $parametro->setValorTipado($valor);
        $parametro->save();

        return $parametro;
    }

    /**
     * Validações customizadas
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($parametro) {
            // Validar chave única por empresa
            if (static::where('empresa_id', $parametro->empresa_id)
                ->where('chave', $parametro->chave)->exists()) {
                throw new \InvalidArgumentException('Chave de parâmetro já existe para esta empresa.');
            }
        });

        static::updating(function ($parametro) {
            // Validar chave única por empresa (exceto o próprio registro)
            if (static::where('empresa_id', $parametro->empresa_id)
                ->where('chave', $parametro->chave)
                ->where('id', '!=', $parametro->id)->exists()) {
                throw new \InvalidArgumentException('Chave de parâmetro já existe para esta empresa.');
            }
        });
    }
}
