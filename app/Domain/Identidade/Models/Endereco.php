<?php

namespace App\Domain\Identidade\Models;

use App\Domain\Identidade\Enums\TipoEndereco;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endereco extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'enderecos';

    protected $fillable = [
        'pessoa_id',
        'tipo',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'pais',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'tipo' => TipoEndereco::class,
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'data_criacao' => 'datetime',
    ];

    public const CREATED_AT = 'data_criacao';
    public const UPDATED_AT = null;

    /**
     * Relacionamentos
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Scopes
     */
    public function scopePrincipais($query)
    {
        return $query->where('tipo', TipoEndereco::PRINCIPAL);
    }

    public function scopeEntrega($query)
    {
        return $query->where('tipo', TipoEndereco::ENTREGA);
    }

    public function scopeFaturamento($query)
    {
        return $query->where('tipo', TipoEndereco::FATURAMENTO);
    }

    public function scopePorCep($query, string $cep)
    {
        return $query->where('cep', $this->formatarCep($cep));
    }

    public function scopePorCidade($query, string $cidade, ?string $estado = null)
    {
        $query->where('cidade', 'like', "%{$cidade}%");
        
        if ($estado) {
            $query->where('estado', strtoupper($estado));
        }
        
        return $query;
    }

    /**
     * Accessors & Mutators
     */
    public function getCepFormatadoAttribute(): string
    {
        return $this->formatarCep($this->cep);
    }

    public function getEnderecoCompletoAttribute(): string
    {
        $endereco = "{$this->logradouro}, {$this->numero}";
        
        if ($this->complemento) {
            $endereco .= ", {$this->complemento}";
        }
        
        $endereco .= " - {$this->bairro}, {$this->cidade}/{$this->estado}";
        $endereco .= " - CEP: {$this->cep_formatado}";
        
        return $endereco;
    }

    public function setCepAttribute($value): void
    {
        $this->attributes['cep'] = $this->formatarCep($value);
    }

    public function setEstadoAttribute($value): void
    {
        $this->attributes['estado'] = strtoupper($value);
    }

    public function setCidadeAttribute($value): void
    {
        $this->attributes['cidade'] = ucwords(strtolower($value));
    }

    public function setBairroAttribute($value): void
    {
        $this->attributes['bairro'] = ucwords(strtolower($value));
    }

    public function setLogradouroAttribute($value): void
    {
        $this->attributes['logradouro'] = ucwords(strtolower($value));
    }

    /**
     * Business Methods
     */
    public function isPrincipal(): bool
    {
        return $this->tipo === TipoEndereco::PRINCIPAL;
    }

    public function isEntrega(): bool
    {
        return $this->tipo === TipoEndereco::ENTREGA;
    }

    public function isFaturamento(): bool
    {
        return $this->tipo === TipoEndereco::FATURAMENTO;
    }

    public function temCoordenadas(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function calcularDistancia(float $latitude, float $longitude): ?float
    {
        if (!$this->temCoordenadas()) {
            return null;
        }

        $earthRadius = 6371; // km

        $latDelta = deg2rad($latitude - $this->latitude);
        $lonDelta = deg2rad($longitude - $this->longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function buscarCoordenadas(): bool
    {
        // Implementar integração com API de geocoding (Google Maps, ViaCEP, etc.)
        // Por enquanto, retorna false
        return false;
    }

    public function validarCep(): bool
    {
        $cep = preg_replace('/[^0-9]/', '', $this->cep);
        return strlen($cep) === 8 && ctype_digit($cep);
    }

    /**
     * Helper Methods
     */
    private function formatarCep(string $cep): string
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) !== 8) {
            return $cep;
        }
        
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }

    /**
     * Factory
     */
    protected static function newFactory()
    {
        return \App\Domain\Identidade\Factories\EnderecoFactory::new();
    }
}
