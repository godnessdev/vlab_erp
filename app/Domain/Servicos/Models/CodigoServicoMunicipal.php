<?php

namespace App\Domain\Servicos\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Códigos e alíquotas específicas por município
 * 
 * @property string $id
 * @property string $servico_id
 * @property string $codigo_municipio_ibge
 * @property string $codigo_servico
 * @property string|null $descricao_municipal
 * @property float $aliquota_iss
 * @property \Carbon\Carbon $data_vigencia_inicio
 * @property \Carbon\Carbon|null $data_vigencia_fim
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property-read Servico $servico
 */
class CodigoServicoMunicipal extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'codigos_servico_municipal';

    protected $fillable = [
        'servico_id',
        'codigo_municipio_ibge',
        'codigo_servico',
        'descricao_municipal',
        'aliquota_iss',
        'data_vigencia_inicio',
        'data_vigencia_fim',
    ];

    protected $casts = [
        'aliquota_iss' => 'decimal:2',
        'data_vigencia_inicio' => 'date',
        'data_vigencia_fim' => 'date',
    ];

    /**
     * Relacionamentos
     */
    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    /**
     * Scopes
     */
    public function scopeVigentes($query, ?string $data = null)
    {
        $data = $data ?: now()->toDateString();
        
        return $query->where('data_vigencia_inicio', '<=', $data)
                     ->where(function ($q) use ($data) {
                         $q->whereNull('data_vigencia_fim')
                           ->orWhere('data_vigencia_fim', '>=', $data);
                     });
    }

    public function scopePorMunicipio($query, string $codigoMunicipio)
    {
        return $query->where('codigo_municipio_ibge', $codigoMunicipio);
    }

    /**
     * Métodos de negócio
     */
    public function estaVigente(?string $data = null): bool
    {
        $data = $data ?: now()->toDateString();
        
        if ($this->data_vigencia_inicio > $data) {
            return false;
        }
        
        if ($this->data_vigencia_fim && $this->data_vigencia_fim < $data) {
            return false;
        }
        
        return true;
    }

    public function encerrarVigencia(?string $dataFim = null): void
    {
        $this->update([
            'data_vigencia_fim' => $dataFim ?: now()->toDateString()
        ]);
    }

    /**
     * Accessor para nome do município (pode ser integrado com API IBGE)
     */
    public function getNomeMunicipioAttribute(): string
    {
        // Aqui poderia haver integração com API do IBGE
        // Por enquanto retorna apenas o código
        return "Município {$this->codigo_municipio_ibge}";
    }
}
