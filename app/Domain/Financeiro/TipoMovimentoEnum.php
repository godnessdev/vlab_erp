<?php

namespace App\Domain\Financeiro;

enum TipoMovimentoEnum: string
{
    case ENTRADA = 'ENTRADA';
    case SAIDA = 'SAIDA';
}
