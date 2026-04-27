<?php

use App\Domain\OrdemServico\Enums\StatusOrdemServico;

/**
 * Testes para o enum StatusOrdemServico
 */
test('pode obter todos os status disponíveis', function () {
    $status = StatusOrdemServico::cases();

    expect($status)->toHaveCount(6);

    $expectedStatus = [
        StatusOrdemServico::ABERTA,
        StatusOrdemServico::EM_ANDAMENTO,
        StatusOrdemServico::PAUSADA,
        StatusOrdemServico::CONCLUIDA,
        StatusOrdemServico::FATURADA,
        StatusOrdemServico::CANCELADA,
    ];

    foreach ($expectedStatus as $expected) {
        expect($status)->toContain($expected);
    }
});

test('pode obter labels dos status', function () {
    expect(StatusOrdemServico::ABERTA->getLabel())->toBe('Aberta');
    expect(StatusOrdemServico::EM_ANDAMENTO->getLabel())->toBe('Em Andamento');
    expect(StatusOrdemServico::PAUSADA->getLabel())->toBe('Pausada');
    expect(StatusOrdemServico::CONCLUIDA->getLabel())->toBe('Concluída');
    expect(StatusOrdemServico::FATURADA->getLabel())->toBe('Faturada');
    expect(StatusOrdemServico::CANCELADA->getLabel())->toBe('Cancelada');
});

test('pode obter cores dos status', function () {
    expect(StatusOrdemServico::ABERTA->getCor())->toBe('blue');
    expect(StatusOrdemServico::EM_ANDAMENTO->getCor())->toBe('green');
    expect(StatusOrdemServico::PAUSADA->getCor())->toBe('yellow');
    expect(StatusOrdemServico::CONCLUIDA->getCor())->toBe('purple');
    expect(StatusOrdemServico::FATURADA->getCor())->toBe('indigo');
    expect(StatusOrdemServico::CANCELADA->getCor())->toBe('red');
});

test('valida transições permitidas de aberta', function () {
    $status = StatusOrdemServico::ABERTA;

    expect($status->podeTransicionarPara(StatusOrdemServico::EM_ANDAMENTO))->toBeTrue();
    expect($status->podeTransicionarPara(StatusOrdemServico::CANCELADA))->toBeTrue();

    expect($status->podeTransicionarPara(StatusOrdemServico::PAUSADA))->toBeFalse();
    expect($status->podeTransicionarPara(StatusOrdemServico::CONCLUIDA))->toBeFalse();
    expect($status->podeTransicionarPara(StatusOrdemServico::FATURADA))->toBeFalse();
});

test('valida transições permitidas de em andamento', function () {
    $status = StatusOrdemServico::EM_ANDAMENTO;

    expect($status->podeTransicionarPara(StatusOrdemServico::PAUSADA))->toBeTrue();
    expect($status->podeTransicionarPara(StatusOrdemServico::CONCLUIDA))->toBeTrue();
    expect($status->podeTransicionarPara(StatusOrdemServico::CANCELADA))->toBeTrue();

    expect($status->podeTransicionarPara(StatusOrdemServico::ABERTA))->toBeFalse();
    expect($status->podeTransicionarPara(StatusOrdemServico::FATURADA))->toBeFalse();
});

test('valida transições permitidas de pausada', function () {
    $status = StatusOrdemServico::PAUSADA;

    expect($status->podeTransicionarPara(StatusOrdemServico::EM_ANDAMENTO))->toBeTrue();
    expect($status->podeTransicionarPara(StatusOrdemServico::CANCELADA))->toBeTrue();

    expect($status->podeTransicionarPara(StatusOrdemServico::ABERTA))->toBeFalse();
    expect($status->podeTransicionarPara(StatusOrdemServico::CONCLUIDA))->toBeFalse();
    expect($status->podeTransicionarPara(StatusOrdemServico::FATURADA))->toBeFalse();
});

test('valida transições permitidas de concluída', function () {
    $status = StatusOrdemServico::CONCLUIDA;

    expect($status->podeTransicionarPara(StatusOrdemServico::FATURADA))->toBeTrue();

    expect($status->podeTransicionarPara(StatusOrdemServico::ABERTA))->toBeFalse();
    expect($status->podeTransicionarPara(StatusOrdemServico::EM_ANDAMENTO))->toBeFalse();
    expect($status->podeTransicionarPara(StatusOrdemServico::PAUSADA))->toBeFalse();
    expect($status->podeTransicionarPara(StatusOrdemServico::CANCELADA))->toBeFalse();
});

test('status finais não permitem transições', function () {
    $statusFinais = [StatusOrdemServico::FATURADA, StatusOrdemServico::CANCELADA];

    foreach ($statusFinais as $statusFinal) {
        foreach (StatusOrdemServico::cases() as $outroStatus) {
            if ($outroStatus === $statusFinal) {
                continue;
            }

            expect($statusFinal->podeTransicionarPara($outroStatus))
                ->toBeFalse("Status {$statusFinal->value} não deveria poder transicionar para {$outroStatus->value}");
        }
    }
});

test('identifica status ativos corretamente', function () {
    $statusAtivos = [
        StatusOrdemServico::ABERTA,
        StatusOrdemServico::EM_ANDAMENTO,
        StatusOrdemServico::PAUSADA,
        StatusOrdemServico::CONCLUIDA,
    ];

    foreach ($statusAtivos as $status) {
        expect($status->isAtiva())->toBeTrue("Status {$status->value} deveria ser ativo");
    }

    $statusInativos = [StatusOrdemServico::FATURADA, StatusOrdemServico::CANCELADA];

    foreach ($statusInativos as $status) {
        expect($status->isAtiva())->toBeFalse("Status {$status->value} não deveria ser ativo");
    }
});

test('identifica status finais corretamente', function () {
    $statusFinais = [StatusOrdemServico::FATURADA, StatusOrdemServico::CANCELADA];

    foreach ($statusFinais as $status) {
        expect($status->isFinal())->toBeTrue("Status {$status->value} deveria ser final");
    }

    $statusNaoFinais = [
        StatusOrdemServico::ABERTA,
        StatusOrdemServico::EM_ANDAMENTO,
        StatusOrdemServico::PAUSADA,
        StatusOrdemServico::CONCLUIDA,
    ];

    foreach ($statusNaoFinais as $status) {
        expect($status->isFinal())->toBeFalse("Status {$status->value} não deveria ser final");
    }
});

test('obtem próximos status permitidos', function () {
    $proximosDeAberta = StatusOrdemServico::ABERTA->getProximosStatus();
    expect($proximosDeAberta)
        ->toContain(StatusOrdemServico::EM_ANDAMENTO)
        ->toContain(StatusOrdemServico::CANCELADA)
        ->toHaveCount(2);

    $proximosDeEmAndamento = StatusOrdemServico::EM_ANDAMENTO->getProximosStatus();
    expect($proximosDeEmAndamento)->toHaveCount(3);

    $proximosDeFaturada = StatusOrdemServico::FATURADA->getProximosStatus();
    expect($proximosDeFaturada)->toBeEmpty();
});
