<?php

namespace App\Domain\Fiscal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Fiscal\Enums\TipoOperacaoProtocoloEnum;
use App\Domain\Fiscal\Enums\StatusRespostaProtocoloEnum;

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
