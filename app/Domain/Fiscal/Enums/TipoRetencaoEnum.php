<?php

namespace App\Domain\Fiscal\Enums;

enum TipoRetencaoEnum: string
{
    case ISS = 'ISS';
    case INSS = 'INSS';
    case IR = 'IR';
    case CSLL = 'CSLL';
    case PIS = 'PIS';
    case COFINS = 'COFINS';
    case IBS_RET = 'IBS_RET';
    case CBS_RET = 'CBS_RET';
}
