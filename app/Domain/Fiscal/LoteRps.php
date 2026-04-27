<?php

namespace App\Domain\Fiscal;

use App\Domain\Fiscal\Enums\AmbienteLoteEnum;
use App\Domain\Fiscal\Enums\StatusLoteEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteRps extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lote_rps';

    protected $fillable = [
        'empresa_id',
        'numero_lote',
        'data_geracao',
        'quantidade_rps',
        'valor_total_servicos',
        'valor_total_deducoes',
        'inscricao_municipal',
        'cnpj',
        'status_envio',
        'protocolo_recebimento',
        'data_envio',
        'data_processamento',
        'mensagem_retorno',
        'ambiente',
    ];

    protected $casts = [
        'data_geracao' => 'datetime',
        'valor_total_servicos' => 'decimal:2',
        'valor_total_deducoes' => 'decimal:2',
        'data_envio' => 'datetime',
        'data_processamento' => 'datetime',
        'status_envio' => StatusLoteEnum::class,
        'ambiente' => AmbienteLoteEnum::class,
    ];
}
