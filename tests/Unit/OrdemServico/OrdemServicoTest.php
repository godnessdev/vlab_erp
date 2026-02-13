<?php

use App\Domain\OrdemServico\Enums\StatusOrdemServico;
use App\Domain\OrdemServico\Enums\PrioridadeOrdem;
use App\Domain\OrdemServico\Models\OrdemServico;

test('pode instanciar ordem servico', function () {
    $ordem = new OrdemServico();
    expect($ordem)->toBeInstanceOf(OrdemServico::class);
});

test('fillable inclui campos necessarios', function () {
    $ordem = new OrdemServico();
    $fillable = $ordem->getFillable();
    
    expect($fillable)->toContain('empresa_id');
    expect($fillable)->toContain('cliente_id');
    expect($fillable)->toContain('titulo');
    expect($fillable)->toContain('status');
    expect($fillable)->toContain('prioridade');
    expect($fillable)->toContain('valor_total_estimado');
});

test('casts estao definidos corretamente', function () {
    $ordem = new OrdemServico();
    $casts = $ordem->getCasts();
    
    expect($casts)->toHaveKey('status');
    expect($casts)->toHaveKey('prioridade');
    expect($casts)->toHaveKey('data_abertura');
    expect($casts)->toHaveKey('data_prazo_previsto');
    expect($casts)->toHaveKey('data_prazo_acordado');
    expect($casts)->toHaveKey('data_finalizacao');
});

test('metodo getValorTotalComDesconto calcula corretamente', function () {
    $ordem = new OrdemServico();
    $ordem->valor_total_estimado = 1000.00;
    $ordem->desconto_percentual = 10.0;
    
    expect($ordem->getValorTotalComDesconto())->toBe(900.00);
});

test('metodo getValorTotalComDesconto sem desconto', function () {
    $ordem = new OrdemServico();
    $ordem->valor_total_estimado = 1000.00;
    $ordem->desconto_percentual = null;
    
    expect($ordem->getValorTotalComDesconto())->toBe(1000.00);
});

test('metodo isEmAndamento identifica corretamente', function () {
    $ordem = new OrdemServico();
    
    $ordem->status = StatusOrdemServico::EM_EXECUCAO;
    expect($ordem->isEmAndamento())->toBeTrue();
    
    $ordem->status = StatusOrdemServico::PAUSADA;
    expect($ordem->isEmAndamento())->toBeTrue();
    
    $ordem->status = StatusOrdemServico::ABERTA;
    expect($ordem->isEmAndamento())->toBeFalse();
    
    $ordem->status = StatusOrdemServico::CONCLUIDA;
    expect($ordem->isEmAndamento())->toBeFalse();
});

test('metodo isFinalizada identifica corretamente', function () {
    $ordem = new OrdemServico();
    
    $ordem->status = StatusOrdemServico::CONCLUIDA;
    expect($ordem->isFinalizada())->toBeTrue();
    
    $ordem->status = StatusOrdemServico::CANCELADA;
    expect($ordem->isFinalizada())->toBeTrue();
    
    $ordem->status = StatusOrdemServico::REJEITADA;
    expect($ordem->isFinalizada())->toBeTrue();
    
    $ordem->status = StatusOrdemServico::ABERTA;
    expect($ordem->isFinalizada())->toBeFalse();
    
    $ordem->status = StatusOrdemServico::EM_EXECUCAO;
    expect($ordem->isFinalizada())->toBeFalse();
});

test('metodo getDuracaoExecucao calcula corretamente', function () {
    $ordem = new OrdemServico();
    
    $dataInicio = now()->subDays(5);
    $dataFim = now();
    
    $ordem->data_inicio_execucao = $dataInicio;
    $ordem->data_finalizacao = $dataFim;
    
    expect($ordem->getDuracaoExecucao())->toBe(5);
});

test('metodo getDuracaoExecucao retorna null se nao iniciada', function () {
    $ordem = new OrdemServico();
    
    expect($ordem->getDuracaoExecucao())->toBeNull();
});

test('metodo getPorcentagemConclusao calcula corretamente', function () {
    $ordem = new OrdemServico();
    $ordem->horas_estimadas = 40;
    $ordem->horas_executadas = 20;
    
    expect($ordem->getPorcentagemConclusao())->toBe(50.0);
});

test('metodo getPorcentagemConclusao retorna zero se sem horas estimadas', function () {
    $ordem = new OrdemServico();
    $ordem->horas_estimadas = 0;
    $ordem->horas_executadas = 20;
    
    expect($ordem->getPorcentagemConclusao())->toBe(0.0);
});
