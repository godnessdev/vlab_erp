<?php

use App\Domain\OrdemServico\Enums\PrioridadeOrdem;

/**
 * Testes para o enum PrioridadeOrdem
 */
test('pode obter todas as prioridades disponíveis', function () {
    $prioridades = PrioridadeOrdem::cases();

    expect($prioridades)->toHaveCount(5);

    $expectedPrioridades = [
        PrioridadeOrdem::MUITO_BAIXA,
        PrioridadeOrdem::BAIXA,
        PrioridadeOrdem::NORMAL,
        PrioridadeOrdem::ALTA,
        PrioridadeOrdem::URGENTE,
    ];

    foreach ($expectedPrioridades as $expected) {
        expect($prioridades)->toContain($expected);
    }
});

test('pode obter labels das prioridades', function () {
    expect(PrioridadeOrdem::MUITO_BAIXA->getLabel())->toBe('Muito Baixa');
    expect(PrioridadeOrdem::BAIXA->getLabel())->toBe('Baixa');
    expect(PrioridadeOrdem::NORMAL->getLabel())->toBe('Normal');
    expect(PrioridadeOrdem::ALTA->getLabel())->toBe('Alta');
    expect(PrioridadeOrdem::URGENTE->getLabel())->toBe('Urgente');
});

test('pode obter pesos das prioridades', function () {
    expect(PrioridadeOrdem::MUITO_BAIXA->getPeso())->toBe(1);
    expect(PrioridadeOrdem::BAIXA->getPeso())->toBe(2);
    expect(PrioridadeOrdem::NORMAL->getPeso())->toBe(3);
    expect(PrioridadeOrdem::ALTA->getPeso())->toBe(4);
    expect(PrioridadeOrdem::URGENTE->getPeso())->toBe(5);
});

test('pode obter cores das prioridades', function () {
    expect(PrioridadeOrdem::MUITO_BAIXA->getCor())->toBe('gray');
    expect(PrioridadeOrdem::BAIXA->getCor())->toBe('blue');
    expect(PrioridadeOrdem::NORMAL->getCor())->toBe('green');
    expect(PrioridadeOrdem::ALTA->getCor())->toBe('yellow');
    expect(PrioridadeOrdem::URGENTE->getCor())->toBe('red');
});

test('identifica prioridades críticas', function () {
    expect(PrioridadeOrdem::URGENTE->isCritica())->toBeTrue();
    expect(PrioridadeOrdem::ALTA->isCritica())->toBeTrue();

    expect(PrioridadeOrdem::NORMAL->isCritica())->toBeFalse();
    expect(PrioridadeOrdem::BAIXA->isCritica())->toBeFalse();
    expect(PrioridadeOrdem::MUITO_BAIXA->isCritica())->toBeFalse();
});

test('calcula ajuste de cronograma', function () {
    expect(PrioridadeOrdem::MUITO_BAIXA->getAjusteCronograma())->toBe(2.0);
    expect(PrioridadeOrdem::BAIXA->getAjusteCronograma())->toBe(1.5);
    expect(PrioridadeOrdem::NORMAL->getAjusteCronograma())->toBe(1.0);
    expect(PrioridadeOrdem::ALTA->getAjusteCronograma())->toBe(0.8);
    expect(PrioridadeOrdem::URGENTE->getAjusteCronograma())->toBe(0.5);
});

test('ordenação por peso funciona corretamente', function () {
    $prioridades = [
        PrioridadeOrdem::NORMAL,
        PrioridadeOrdem::URGENTE,
        PrioridadeOrdem::BAIXA,
        PrioridadeOrdem::ALTA,
        PrioridadeOrdem::MUITO_BAIXA,
    ];

    // Ordenar por peso (decrescente - maior prioridade primeiro)
    usort($prioridades, function ($a, $b) {
        return $b->getPeso() <=> $a->getPeso();
    });

    $esperado = [
        PrioridadeOrdem::URGENTE,
        PrioridadeOrdem::ALTA,
        PrioridadeOrdem::NORMAL,
        PrioridadeOrdem::BAIXA,
        PrioridadeOrdem::MUITO_BAIXA,
    ];

    expect($prioridades)->toBe($esperado);
});

test('comparação entre prioridades', function () {
    expect(PrioridadeOrdem::URGENTE->getPeso())->toBeGreaterThan(PrioridadeOrdem::ALTA->getPeso());
    expect(PrioridadeOrdem::ALTA->getPeso())->toBeGreaterThan(PrioridadeOrdem::NORMAL->getPeso());
    expect(PrioridadeOrdem::NORMAL->getPeso())->toBeGreaterThan(PrioridadeOrdem::BAIXA->getPeso());
    expect(PrioridadeOrdem::BAIXA->getPeso())->toBeGreaterThan(PrioridadeOrdem::MUITO_BAIXA->getPeso());
});
