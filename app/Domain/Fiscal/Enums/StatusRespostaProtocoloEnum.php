<?php

namespace App\Domain\Fiscal\Enums;

enum StatusRespostaProtocoloEnum: string
{
    case PENDENTE = 'PENDENTE';
    case SUCESSO = 'SUCESSO';
    case ERRO = 'ERRO';
    case TIMEOUT = 'TIMEOUT';
}
