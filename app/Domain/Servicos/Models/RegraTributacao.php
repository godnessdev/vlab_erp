<?php

namespace App\Domain\Servicos\Models;

use App\Domain\Servicos\Enums\RegimeTributario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Regras tributárias específicas por regime e serviço
 * 
 * @property string $id
 * @property string $servico_id
 * @property RegimeTributario $regime_tributario
 * @property float $aliquota_ir
 * @property float $aliquota_csll
 * @property float $aliquota_pis
 * @property float $aliquota_cofins
 * @property bool $retencao_inss
 * @property array|null $base_calculo_diferenciada
 * @property array|null $regras_adicionais
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property-read Servico $servico
 */
class RegraTributacao extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'regras_tributacao';

    protected $fillable = [
        'servico_id',
        'regime_tributario',
        'aliquota_ir',
        'aliquota_csll',
        'aliquota_pis',
        'aliquota_cofins',
        'retencao_inss',
        'base_calculo_diferenciada',
        'regras_adicionais',
    ];

    protected $casts = [
        'regime_tributario' => RegimeTributario::class,
        'aliquota_ir' => 'decimal:2',
        'aliquota_csll' => 'decimal:2',
        'aliquota_pis' => 'decimal:2',
        'aliquota_cofins' => 'decimal:2',
        'retencao_inss' => 'boolean',
        'base_calculo_diferenciada' => 'json',
        'regras_adicionais' => 'json',
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
    public function scopePorRegime($query, RegimeTributario|string $regime)
    {
        if (is_string($regime)) {
            $regime = RegimeTributario::from($regime);
        }
        
        return $query->where('regime_tributario', $regime);
    }

    /**
     * Métodos de negócio
     */
    public function calcularTotalAliquotas(): float
    {
        return $this->aliquota_ir + 
               $this->aliquota_csll + 
               $this->aliquota_pis + 
               $this->aliquota_cofins;
    }

    public function temRetencoes(): bool
    {
        return $this->retencao_inss || 
               $this->aliquota_ir > 0 || 
               $this->aliquota_csll > 0;
    }

    public function calcularImpostos(float $valorBase): array
    {
        $impostos = [];

        if ($this->aliquota_ir > 0) {
            $impostos['ir'] = [
                'aliquota' => $this->aliquota_ir,
                'valor' => ($valorBase * $this->aliquota_ir) / 100,
            ];
        }

        if ($this->aliquota_csll > 0) {
            $impostos['csll'] = [
                'aliquota' => $this->aliquota_csll,
                'valor' => ($valorBase * $this->aliquota_csll) / 100,
            ];
        }

        if ($this->aliquota_pis > 0) {
            $impostos['pis'] = [
                'aliquota' => $this->aliquota_pis,
                'valor' => ($valorBase * $this->aliquota_pis) / 100,
            ];
        }

        if ($this->aliquota_cofins > 0) {
            $impostos['cofins'] = [
                'aliquota' => $this->aliquota_cofins,
                'valor' => ($valorBase * $this->aliquota_cofins) / 100,
            ];
        }

        return $impostos;
    }

    /**
     * Aplicar regras especiais de base de cálculo
     */
    public function calcularBaseCalculo(float $valorOriginal): float
    {
        if (empty($this->base_calculo_diferenciada)) {
            return $valorOriginal;
        }

        $regras = $this->base_calculo_diferenciada;
        $valorBase = $valorOriginal;

        // Aplicar desconto fixo
        if (isset($regras['desconto_fixo'])) {
            $valorBase -= $regras['desconto_fixo'];
        }

        // Aplicar desconto percentual
        if (isset($regras['desconto_percentual'])) {
            $valorBase -= ($valorOriginal * $regras['desconto_percentual']) / 100;
        }

        // Aplicar valor mínimo
        if (isset($regras['valor_minimo'])) {
            $valorBase = max($valorBase, $regras['valor_minimo']);
        }

        // Aplicar valor máximo
        if (isset($regras['valor_maximo'])) {
            $valorBase = min($valorBase, $regras['valor_maximo']);
        }

        return max($valorBase, 0); // Não pode ser negativo
    }

    /**
     * Definir regras adicionais específicas
     */
    public function definirRegra(string $chave, mixed $valor): void
    {
        $regras = $this->regras_adicionais ?: [];
        $regras[$chave] = $valor;
        $this->update(['regras_adicionais' => $regras]);
    }

    public function obterRegra(string $chave, mixed $default = null): mixed
    {
        return $this->regras_adicionais[$chave] ?? $default;
    }
}
