<?php

namespace App\Domain\Identidade\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DadoEspecificoPapel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'dados_especificos_papel';

    protected $fillable = [
        'papel_id',
        'chave',
        'valor',
    ];

    protected $casts = [
        'valor' => 'string',
    ];

    /**
     * Relacionamentos
     */
    public function papel(): BelongsTo
    {
        return $this->belongsTo(Papel::class);
    }

    /**
     * Scopes
     */
    public function scopePorChave($query, string $chave)
    {
        return $query->where('chave', $chave);
    }

    public function scopePorPapel($query, string $papelId)
    {
        return $query->where('papel_id', $papelId);
    }

    public function scopeComValor($query, $valor)
    {
        return $query->where('valor', $valor);
    }

    /**
     * Accessors & Mutators
     */
    public function getValorDecodificadoAttribute()
    {
        return $this->valor;
    }

    public function setValorAttribute($value): void
    {
        // Se for string JSON, converte para array
        if (is_string($value) && $this->isJson($value)) {
            $this->attributes['valor'] = json_decode($value, true);
        } else {
            $this->attributes['valor'] = $value;
        }
    }

    /**
     * Business Methods
     */
    public function getValue(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->valor;
        }

        if (is_array($this->valor) && array_key_exists($key, $this->valor)) {
            return $this->valor[$key];
        }

        return $default;
    }

    public function setValue(string $key, $value): void
    {
        $valores = is_array($this->valor) ? $this->valor : [];
        $valores[$key] = $value;
        
        $this->update(['valor' => $valores]);
    }

    public function removeValue(string $key): bool
    {
        if (!is_array($this->valor) || !array_key_exists($key, $this->valor)) {
            return false;
        }

        $valores = $this->valor;
        unset($valores[$key]);
        
        $this->update(['valor' => $valores]);
        
        return true;
    }

    public function hasValue(string $key): bool
    {
        return is_array($this->valor) && array_key_exists($key, $this->valor);
    }

    public function isString(): bool
    {
        return is_string($this->valor);
    }

    public function isArray(): bool
    {
        return is_array($this->valor);
    }

    public function isNumeric(): bool
    {
        return is_numeric($this->valor);
    }

    public function isBoolean(): bool
    {
        return is_bool($this->valor);
    }

    public function isEmpty(): bool
    {
        if (is_array($this->valor)) {
            return empty($this->valor);
        }
        
        return empty($this->valor);
    }

    public function getKeys(): array
    {
        if (!is_array($this->valor)) {
            return [];
        }
        
        return array_keys($this->valor);
    }

    public function merge(array $data): void
    {
        if (!is_array($this->valor)) {
            $this->update(['valor' => $data]);
            return;
        }
        
        $valores = array_merge($this->valor, $data);
        $this->update(['valor' => $valores]);
    }

    /**
     * Validações específicas por tipo de papel
     */
    public function validarParaCliente(): bool
    {
        // Validações específicas para dados de cliente
        return true;
    }

    public function validarParaPrestador(): bool
    {
        // Validações específicas para dados de prestador
        return true;
    }

    public function validarParaFuncionario(): bool
    {
        // Validações específicas para dados de funcionário
        return true;
    }

    public function validarParaFornecedor(): bool
    {
        // Validações específicas para dados de fornecedor
        return true;
    }

    public function validarParaContador(): bool
    {
        // Validações específicas para dados de contador
        return true;
    }

    /**
     * Helpers
     */
    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Factory
     */
    protected static function newFactory()
    {
        return \App\Domain\Identidade\Factories\DadoEspecificoPapelFactory::new();
    }
}
