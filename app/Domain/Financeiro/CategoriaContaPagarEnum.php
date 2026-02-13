<?php

namespace App\Domain\Financeiro;

enum CategoriaContaPagarEnum: string
{
    case FORNECEDOR = 'FORNECEDOR';
    case FUNCIONARIO = 'FUNCIONARIO';
    case IMPOSTO = 'IMPOSTO';
    case DESPESA_GERAL = 'DESPESA_GERAL';
}
