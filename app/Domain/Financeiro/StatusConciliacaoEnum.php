<?php

namespace App\Domain\Financeiro;

enum StatusConciliacaoEnum: string
{
    case PENDENTE = 'PENDENTE';
    case CONCILIADO = 'CONCILIADO';
    case DISCREPANTE = 'DISCREPANTE';
}
