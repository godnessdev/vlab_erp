<?php

namespace App\Domain\Fiscal\Enums;

enum StatusLoteEnum: string
{
    case GERADO = 'GERADO';
    case ENVIADO = 'ENVIADO';
    case PROCESSANDO = 'PROCESSANDO';
    case PROCESSADO = 'PROCESSADO';
    case ERRO = 'ERRO';
}
