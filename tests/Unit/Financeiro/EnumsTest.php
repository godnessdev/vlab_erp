<?php

use App\Domain\Financeiro\StatusContaReceberEnum;
use App\Domain\Financeiro\StatusContaPagarEnum;
use App\Domain\Financeiro\FormaCobrancaEnum;
use App\Domain\Financeiro\CategoriaContaPagarEnum;
use App\Domain\Financeiro\TipoMovimentoEnum;
use App\Domain\Financeiro\StatusConciliacaoEnum;

it('valida enums de status de conta a receber', function () {
    expect(StatusContaReceberEnum::ABERTA->value)->toBe('ABERTA');
    expect(StatusContaReceberEnum::PAGA->value)->toBe('PAGA');
});

it('valida enums de status de conta a pagar', function () {
    expect(StatusContaPagarEnum::ABERTA->value)->toBe('ABERTA');
    expect(StatusContaPagarEnum::PAGA->value)->toBe('PAGA');
});

it('valida enums de forma de cobrança', function () {
    expect(FormaCobrancaEnum::PIX->value)->toBe('PIX');
    expect(FormaCobrancaEnum::BOLETO->value)->toBe('BOLETO');
});

it('valida enums de categoria de conta pagar', function () {
    expect(CategoriaContaPagarEnum::FORNECEDOR->value)->toBe('FORNECEDOR');
    expect(CategoriaContaPagarEnum::IMPOSTO->value)->toBe('IMPOSTO');
});

it('valida enums de tipo de movimento', function () {
    expect(TipoMovimentoEnum::ENTRADA->value)->toBe('ENTRADA');
    expect(TipoMovimentoEnum::SAIDA->value)->toBe('SAIDA');
});

it('valida enums de status de conciliação', function () {
    expect(StatusConciliacaoEnum::CONCILIADO->value)->toBe('CONCILIADO');
    expect(StatusConciliacaoEnum::DISCREPANTE->value)->toBe('DISCREPANTE');
});
