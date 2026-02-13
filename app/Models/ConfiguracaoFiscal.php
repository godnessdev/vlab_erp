<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConfiguracaoFiscal extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'configuracoes_fiscais';

    protected $fillable = [
        'empresa_id',
        'filial_id',
        'aliquota_iss_default',
        'codigo_municipio_ibge',
        'certificado_digital_id',
        'webservice_url',
        'ambiente',
    ];

    protected $casts = [
        'aliquota_iss_default' => 'decimal:2',
        'ambiente' => AmbienteFiscalEnum::class,
    ];

    /**
     * Relacionamentos
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class);
    }

    // TODO: Implementar relacionamento com CertificadoDigital quando o domínio fiscal for criado
    // public function certificadoDigital(): BelongsTo
    // {
    //     return $this->belongsTo(CertificadoDigital::class);
    // }

    /**
     * Scopes
     */
    public function scopePorEmpresa($query, string $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopePorFilial($query, string $filialId)
    {
        return $query->where('filial_id', $filialId);
    }

    public function scopePorMunicipio($query, string $codigoIbge)
    {
        return $query->where('codigo_municipio_ibge', $codigoIbge);
    }

    public function scopeProducao($query)
    {
        return $query->where('ambiente', AmbienteFiscalEnum::PRODUCAO);
    }

    public function scopeHomologacao($query)
    {
        return $query->where('ambiente', AmbienteFiscalEnum::HOMOLOGACAO);
    }

    /**
     * Métodos de negócio
     */
    public function isProducao(): bool
    {
        return $this->ambiente === AmbienteFiscalEnum::PRODUCAO;
    }

    public function isHomologacao(): bool
    {
        return $this->ambiente === AmbienteFiscalEnum::HOMOLOGACAO;
    }

    public function getAliquotaFormatada(): string
    {
        return number_format($this->aliquota_iss_default, 2, ',', '.') . '%';
    }

    public function getMunicipioNome(): ?string
    {
        // TODO: Implementar busca do nome do município pelo código IBGE
        // quando o serviço de localidades for implementado
        return 'Município - Código IBGE: ' . $this->codigo_municipio_ibge;
    }

    public function getWebserviceStatus(): string
    {
        // TODO: Implementar verificação de status do webservice
        return 'Não verificado';
    }

    /**
     * Validações customizadas
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($config) {
            // Validar alíquota ISS
            if ($config->aliquota_iss_default < 0 || $config->aliquota_iss_default > 20) {
                throw new \InvalidArgumentException('Alíquota ISS deve estar entre 0% e 20%.');
            }

            // Validar código IBGE (7 dígitos)
            if (!preg_match('/^\d{7}$/', $config->codigo_municipio_ibge)) {
                throw new \InvalidArgumentException('Código IBGE deve ter 7 dígitos numéricos.');
            }

            // Validar URL do webservice
            if (!filter_var($config->webservice_url, FILTER_VALIDATE_URL)) {
                throw new \InvalidArgumentException('URL do webservice inválida.');
            }
        });

        static::creating(function ($config) {
            // Validar configuração única por empresa/filial/município
            $exists = static::where('empresa_id', $config->empresa_id)
                ->where('filial_id', $config->filial_id)
                ->where('codigo_municipio_ibge', $config->codigo_municipio_ibge)
                ->exists();

            if ($exists) {
                throw new \InvalidArgumentException('Já existe configuração fiscal para esta empresa/filial/município.');
            }
        });

        static::updating(function ($config) {
            // Validar configuração única por empresa/filial/município (exceto o próprio registro)
            $exists = static::where('empresa_id', $config->empresa_id)
                ->where('filial_id', $config->filial_id)
                ->where('codigo_municipio_ibge', $config->codigo_municipio_ibge)
                ->where('id', '!=', $config->id)
                ->exists();

            if ($exists) {
                throw new \InvalidArgumentException('Já existe configuração fiscal para esta empresa/filial/município.');
            }
        });
    }
}
