<?php

namespace App\Domain\Servicos\Models;

use App\Domain\Servicos\Enums\StatusServico;
use App\Domain\Servicos\Enums\UnidadeMedida;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Serviço prestado pela empresa
 * 
 * @property string $id
 * @property string $empresa_id
 * @property string $descricao
 * @property UnidadeMedida $unidade_medida
 * @property float $preco_base
 * @property float $aliquota_iss_default
 * @property string $classificacao_fiscal
 * @property string|null $observacoes
 * @property StatusServico $status
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property-read Empresa $empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<CodigoServicoMunicipal> $codigosMunicipais
 * @property-read \Illuminate\Database\Eloquent\Collection<RegraTributacao> $regrasTributacao
 */
class Servico extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'servicos';

    protected $fillable = [
        'empresa_id',
        'descricao',
        'unidade_medida',
        'preco_base',
        'aliquota_iss_default',
        'classificacao_fiscal',
        'observacoes',
        'status',
    ];

    protected $casts = [
        'unidade_medida' => UnidadeMedida::class,
        'status' => StatusServico::class,
        'preco_base' => 'decimal:2',
        'aliquota_iss_default' => 'decimal:2',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ServicoFactory::new();
    }

    /**
     * Relacionamentos
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function codigosMunicipais(): HasMany
    {
        return $this->hasMany(CodigoServicoMunicipal::class);
    }

    public function regrasTributacao(): HasMany
    {
        return $this->hasMany(RegraTributacao::class);
    }

    /**
     * Scopes
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', StatusServico::ATIVO);
    }

    public function scopePorClassificacao($query, string $classificacao)
    {
        return $query->where('classificacao_fiscal', $classificacao);
    }

    public function scopePorEmpresa($query, string $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    /**
     * Métodos de negócio
     */
    public function podeSerUsado(): bool
    {
        return $this->status->podeSerUsado();
    }

    public function inativar(): void
    {
        $this->update(['status' => StatusServico::INATIVO]);
    }

    public function ativar(): void
    {
        $this->update(['status' => StatusServico::ATIVO]);
    }

    public function descontinuar(): void
    {
        $this->update(['status' => StatusServico::DESCONTINUADO]);
    }

    /**
     * Obter alíquota ISS para um município específico
     */
    public function obterAliquotaISS(string $codigoMunicipio): float
    {
        $codigoMunicipal = $this->codigosMunicipais()
            ->where('codigo_municipio_ibge', $codigoMunicipio)
            ->where('data_vigencia_inicio', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('data_vigencia_fim')
                      ->orWhere('data_vigencia_fim', '>=', now()->toDateString());
            })
            ->orderBy('data_vigencia_inicio', 'desc')
            ->first();

        return $codigoMunicipal ? $codigoMunicipal->aliquota_iss : $this->aliquota_iss_default;
    }

    /**
     * Obter código do serviço para um município
     */
    public function obterCodigoMunicipio(string $codigoMunicipio): ?string
    {
        $codigoMunicipal = $this->codigosMunicipais()
            ->where('codigo_municipio_ibge', $codigoMunicipio)
            ->where('data_vigencia_inicio', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('data_vigencia_fim')
                      ->orWhere('data_vigencia_fim', '>=', now()->toDateString());
            })
            ->orderBy('data_vigencia_inicio', 'desc')
            ->first();

        return $codigoMunicipal?->codigo_servico;
    }

    /**
     * Calcular valor com tributação
     */
    public function calcularValorComTributacao(
        float $valorBase, 
        string $regimeTributario, 
        string $codigoMunicipio = null
    ): array {
        $resultado = [
            'valor_base' => $valorBase,
            'impostos' => [],
            'valor_total_impostos' => 0,
            'valor_liquido' => $valorBase,
        ];

        // ISS
        $aliquotaISS = $codigoMunicipio ? 
            $this->obterAliquotaISS($codigoMunicipio) : 
            $this->aliquota_iss_default;
        
        $valorISS = ($valorBase * $aliquotaISS) / 100;
        $resultado['impostos']['iss'] = [
            'aliquota' => $aliquotaISS,
            'valor' => $valorISS,
        ];
        $resultado['valor_total_impostos'] += $valorISS;

        // Outros impostos conforme regime
        $regraTributacao = $this->regrasTributacao()
            ->where('regime_tributario', $regimeTributario)
            ->first();

        if ($regraTributacao) {
            // IR
            if ($regraTributacao->aliquota_ir > 0) {
                $valorIR = ($valorBase * $regraTributacao->aliquota_ir) / 100;
                $resultado['impostos']['ir'] = [
                    'aliquota' => $regraTributacao->aliquota_ir,
                    'valor' => $valorIR,
                ];
                $resultado['valor_total_impostos'] += $valorIR;
            }

            // CSLL
            if ($regraTributacao->aliquota_csll > 0) {
                $valorCSLL = ($valorBase * $regraTributacao->aliquota_csll) / 100;
                $resultado['impostos']['csll'] = [
                    'aliquota' => $regraTributacao->aliquota_csll,
                    'valor' => $valorCSLL,
                ];
                $resultado['valor_total_impostos'] += $valorCSLL;
            }

            // PIS
            if ($regraTributacao->aliquota_pis > 0) {
                $valorPIS = ($valorBase * $regraTributacao->aliquota_pis) / 100;
                $resultado['impostos']['pis'] = [
                    'aliquota' => $regraTributacao->aliquota_pis,
                    'valor' => $valorPIS,
                ];
                $resultado['valor_total_impostos'] += $valorPIS;
            }

            // COFINS
            if ($regraTributacao->aliquota_cofins > 0) {
                $valorCOFINS = ($valorBase * $regraTributacao->aliquota_cofins) / 100;
                $resultado['impostos']['cofins'] = [
                    'aliquota' => $regraTributacao->aliquota_cofins,
                    'valor' => $valorCOFINS,
                ];
                $resultado['valor_total_impostos'] += $valorCOFINS;
            }
        }

        $resultado['valor_liquido'] = $valorBase - $resultado['valor_total_impostos'];

        return $resultado;
    }
}
