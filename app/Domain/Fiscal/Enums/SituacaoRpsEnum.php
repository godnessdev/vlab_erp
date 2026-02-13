<?php

namespace App\Domain\Fiscal\Enums;

enum SituacaoRpsEnum: string
{
    case GERADO = 'GERADO';
    case ENVIADO = 'ENVIADO';
    case CONVERTIDO = 'CONVERTIDO';
    case ERRO = 'ERRO';
}
