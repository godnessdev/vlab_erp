<?php

namespace App\Domain\Fiscal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Fiscal\Enums\SituacaoRpsEnum;

class Rps extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rps';

    protected $fillable = [
        'empresa_id',
        'fatura_id',
        'numero_rps',
        'serie',
        'data_emissao',
        'competencia',
        'valor_servicos',
        'valor_deducoes',
        'valor_pis',
        'valor_cofins',
        'valor_inss',
        'valor_ir',
        'valor_csll',
        'base_calculo',
        'aliquota',
        'valor_iss',
        'valor_iss_retido',
        'valor_ibs',
        'valor_cbs',
        'descricao',
        'codigo_servico',
        'codigo_cnae',
        'item_lista_servico',
        'situacao',
        'usar_layout_nacional',
        'data_criacao',
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'competencia' => 'date',
        'valor_servicos' => 'decimal:2',
        'valor_deducoes' => 'decimal:2',
        'valor_pis' => 'decimal:2',
        'valor_cofins' => 'decimal:2',
        'valor_inss' => 'decimal:2',
        'valor_ir' => 'decimal:2',
        'valor_csll' => 'decimal:2',
        'base_calculo' => 'decimal:2',
        'aliquota' => 'decimal:4',
        'valor_iss' => 'decimal:2',
        'valor_iss_retido' => 'decimal:2',
        'valor_ibs' => 'decimal:2',
        'valor_cbs' => 'decimal:2',
        'usar_layout_nacional' => 'boolean',
        'data_criacao' => 'datetime',
        'situacao' => SituacaoRpsEnum::class,
    ];
}
