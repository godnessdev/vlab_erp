<?php

use App\Domain\Faturamento\Enums\StatusFatura;

test('pode obter todos os status de fatura disponíveis', function () {
    $status = StatusFatura::cases();

    expect($status)->toHaveCount(4);

    $expectedStatus = [
        StatusFatura::ABERTA,
        StatusFatura::ENVIADA,
        StatusFatura::PAGA,
        StatusFatura::CANCELADA,
    ];

    foreach ($expectedStatus as $expectedStatus) {
        expect($status)->toContain($expectedStatus);
    }
});

test('pode obter labels dos status', function () {
    expect(StatusFatura::ABERTA->getLabel())->toBe('Aberta');
    expect(StatusFatura::ENVIADA->getLabel())->toBe('Enviada');
    expect(StatusFatura::PAGA->getLabel())->toBe('Paga');
    expect(StatusFatura::CANCELADA->getLabel())->toBe('Cancelada');
});

test('pode obter cores dos status', function () {
    expect(StatusFatura::ABERTA->getCor())->toBe('blue');
    expect(StatusFatura::ENVIADA->getCor())->toBe('yellow');
    expect(StatusFatura::PAGA->getCor())->toBe('green');
    expect(StatusFatura::CANCELADA->getCor())->toBe('red');
});

test('identifica status ativos corretamente', function () {
    expect(StatusFatura::ABERTA->isAtiva())->toBeTrue();
    expect(StatusFatura::ENVIADA->isAtiva())->toBeTrue();

    expect(StatusFatura::PAGA->isAtiva())->toBeFalse();
    expect(StatusFatura::CANCELADA->isAtiva())->toBeFalse();
});

test('identifica status finais corretamente', function () {
    expect(StatusFatura::PAGA->isFinal())->toBeTrue();
    expect(StatusFatura::CANCELADA->isFinal())->toBeTrue();

    expect(StatusFatura::ABERTA->isFinal())->toBeFalse();
    expect(StatusFatura::ENVIADA->isFinal())->toBeFalse();
});

test('valida transições permitidas de aberta', function () {
    $status = StatusFatura::ABERTA;

    expect($status->podeTransicionarPara(StatusFatura::ENVIADA))->toBeTrue();
    expect($status->podeTransicionarPara(StatusFatura::CANCELADA))->toBeTrue();

    expect($status->podeTransicionarPara(StatusFatura::PAGA))->toBeFalse();
    expect($status->podeTransicionarPara(StatusFatura::ABERTA))->toBeFalse();
});

test('valida transições permitidas de enviada', function () {
    $status = StatusFatura::ENVIADA;

    expect($status->podeTransicionarPara(StatusFatura::PAGA))->toBeTrue();
    expect($status->podeTransicionarPara(StatusFatura::CANCELADA))->toBeTrue();

    expect($status->podeTransicionarPara(StatusFatura::ABERTA))->toBeFalse();
    expect($status->podeTransicionarPara(StatusFatura::ENVIADA))->toBeFalse();
});

test('status finais não permitem transições', function () {
    expect(StatusFatura::PAGA->getProximosStatus())->toBeEmpty();
    expect(StatusFatura::CANCELADA->getProximosStatus())->toBeEmpty();
});

test('valida permissões de edição', function () {
    expect(StatusFatura::ABERTA->podeEditar())->toBeTrue();

    expect(StatusFatura::ENVIADA->podeEditar())->toBeFalse();
    expect(StatusFatura::PAGA->podeEditar())->toBeFalse();
    expect(StatusFatura::CANCELADA->podeEditar())->toBeFalse();
});

test('valida permissões de cancelamento', function () {
    expect(StatusFatura::ABERTA->podeCancelar())->toBeTrue();
    expect(StatusFatura::ENVIADA->podeCancelar())->toBeTrue();

    expect(StatusFatura::PAGA->podeCancelar())->toBeFalse();
    expect(StatusFatura::CANCELADA->podeCancelar())->toBeFalse();
});

test('obtem próximos status permitidos', function () {
    $proximosAberta = StatusFatura::ABERTA->getProximosStatus();
    expect($proximosAberta)->toContain(StatusFatura::ENVIADA);
    expect($proximosAberta)->toContain(StatusFatura::CANCELADA);
    expect($proximosAberta)->toHaveCount(2);

    $proximosEnviada = StatusFatura::ENVIADA->getProximosStatus();
    expect($proximosEnviada)->toContain(StatusFatura::PAGA);
    expect($proximosEnviada)->toContain(StatusFatura::CANCELADA);
    expect($proximosEnviada)->toHaveCount(2);
});
