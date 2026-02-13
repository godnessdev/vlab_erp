<?php

namespace App\Domain\Fiscal\Enums;

enum TipoOperacaoProtocoloEnum: string
{
    case ENVIO_LOTE = 'ENVIO_LOTE';
    case CONSULTA_LOTE = 'CONSULTA_LOTE';
    case CONSULTA_NFSE = 'CONSULTA_NFSE';
    case CANCELAMENTO = 'CANCELAMENTO';
}
