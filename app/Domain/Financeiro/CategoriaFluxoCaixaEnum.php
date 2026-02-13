<?php

namespace App\Domain\Financeiro;

enum CategoriaFluxoCaixaEnum: string
{
    case RECEBIMENTO = 'RECEBIMENTO';
    case PAGAMENTO   = 'PAGAMENTO';
    case AJUSTE      = 'AJUSTE';
}
