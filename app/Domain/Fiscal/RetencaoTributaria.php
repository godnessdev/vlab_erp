<?php

namespace App\Domain\Fiscal;

use App\Domain\Fiscal\Enums\ResponsavelRetencaoEnum;
use App\Domain\Fiscal\Enums\TipoRetencaoEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetencaoTributaria extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'retencao_tributaria';

    protected $fillable = [
        'nfse_id',
        'tipo_retencao',
        'base_calculo',
        'aliquota',
        'valor_retido',
        'responsavel_retencao',
        'codigo_receita',
    ];

    protected $casts = [
        'base_calculo' => 'decimal:2',
        'aliquota' => 'decimal:4',
        'valor_retido' => 'decimal:2',
        'tipo_retencao' => TipoRetencaoEnum::class,
        'responsavel_retencao' => ResponsavelRetencaoEnum::class,
    ];
}
