<?php

namespace App\Domain\Fiscal\Enums;

enum TipoEventoFiscalEnum: string
{
    case CANCELAMENTO = 'CANCELAMENTO';
    case SUBSTITUICAO = 'SUBSTITUICAO';
}
