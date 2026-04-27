<?php

namespace App\Domain\Fiscal;

use App\Domain\Fiscal\Enums\StatusRespostaProtocoloEnum;
use App\Domain\Fiscal\Enums\TipoOperacaoProtocoloEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocoloFiscal extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'protocolo_fiscal';

    protected $fillable = [
        'empresa_id',
        'lote_id',
        'nfse_id',
        'tipo_operacao',
        'codigo_protocolo',
        'data_envio',
        'data_retorno',
        'status_resposta',
        'codigo_erro',
        'mensagem_erro',
        'xml_envio',
        'xml_retorno',
    ];

    protected $casts = [
        'data_envio' => 'datetime',
        'data_retorno' => 'datetime',
        'tipo_operacao' => TipoOperacaoProtocoloEnum::class,
        'status_resposta' => StatusRespostaProtocoloEnum::class,
    ];
}
