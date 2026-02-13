<?php

namespace App\Domain\Financeiro;

enum StatusContaPagarEnum: string
{
    case ABERTA = 'ABERTA';
    case PAGA = 'PAGA';
    case PARCIAL = 'PARCIAL';
    case ATRASADA = 'ATRASADA';
    case CANCELADA = 'CANCELADA';
}
