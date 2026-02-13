<?php

namespace App\Domain\Financeiro\Enums;

enum CategoriaFluxoCaixaEnum: string
{
    case RECEBIMENTO = 'RECEBIMENTO';
    case PAGAMENTO = 'PAGAMENTO';
    case TRANSFERENCIA = 'TRANSFERENCIA';
}
