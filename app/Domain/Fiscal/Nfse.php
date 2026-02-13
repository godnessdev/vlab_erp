<?php

namespace App\Domain\Fiscal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Fiscal\Enums\StatusNfseEnum;

class Nfse extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $table = 'nfse';

    protected $fillable = [
        'empresa_id',
        'rps_id',
        'numero_nfse',
        'codigo_verificacao',
        'data_emissao',
        'data_autorizacao',
        'municipio_prestacao',
        'url_visualizacao',
        'status',
        'motivo_cancelamento',
        'data_cancelamento',
        'xml_autorizacao',
        'xml_cancelamento',
        'hash_xml',
        'versao_schema',
        'base_calculo_ibs',
        'aliquota_ibs',
        'valor_ibs',
        'valor_ibs_retido',
        'base_calculo_cbs',
        'aliquota_cbs',
        'valor_cbs',
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'data_autorizacao' => 'datetime',
        'data_cancelamento' => 'datetime',
        'base_calculo_ibs' => 'decimal:2',
        'aliquota_ibs' => 'decimal:4',
        'valor_ibs' => 'decimal:2',
        'valor_ibs_retido' => 'decimal:2',
        'base_calculo_cbs' => 'decimal:2',
        'aliquota_cbs' => 'decimal:4',
        'valor_cbs' => 'decimal:2',
        'status' => StatusNfseEnum::class,
    ];
}
