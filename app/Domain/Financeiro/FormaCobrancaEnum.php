<?php

namespace App\Domain\Financeiro;

enum FormaCobrancaEnum: string
{
    case BOLETO = 'BOLETO';
    case PIX = 'PIX';
    case CARTAO = 'CARTAO';
    case DINHEIRO = 'DINHEIRO';
    case TRANSFERENCIA = 'TRANSFERENCIA';
}
