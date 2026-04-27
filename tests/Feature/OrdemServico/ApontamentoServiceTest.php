<?php

use App\Domain\OrdemServico\Enums\PrioridadeOrdem;
use App\Domain\OrdemServico\Enums\StatusOrdemServico;
use App\Domain\OrdemServico\Enums\TipoApontamento;
use App\Domain\OrdemServico\Models\Apontamento;
use App\Domain\OrdemServico\Models\OrdemServico;
use App\Domain\OrdemServico\Services\ApontamentoService;
use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate');
    $this->apontamentoService = new ApontamentoService;
});

test('pode criar apontamento de tempo', function () {
    $empresa = Empresa::factory()->create();
    $cliente = Usuario::factory()->create();
    $usuario = Usuario::factory()->create();

    $ordem = OrdemServico::create([
        'empresa_id' => $empresa->id,
        'cliente_id' => $cliente->id,
        'titulo' => 'Teste Apontamento',
        'status' => StatusOrdemServico::EM_EXECUCAO,
        'prioridade' => PrioridadeOrdem::NORMAL,
        'valor_total_estimado' => 2000.00,
    ]);

    $dados = [
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario->id,
        'tipo' => TipoApontamento::TEMPO,
        'data_apontamento' => now()->toDateString(),
        'horas' => 4.5,
        'descricao' => 'Desenvolvimento de funcionalidade X',
    ];

    $apontamento = $this->apontamentoService->criar($dados);

    expect($apontamento)->toBeInstanceOf(Apontamento::class);
    expect($apontamento->tipo)->toBe(TipoApontamento::TEMPO);
    expect($apontamento->horas)->toBe(4.5);
    expect($apontamento->descricao)->toBe('Desenvolvimento de funcionalidade X');
});

test('pode criar apontamento de despesa', function () {
    $empresa = Empresa::factory()->create();
    $cliente = Usuario::factory()->create();
    $usuario = Usuario::factory()->create();

    $ordem = OrdemServico::create([
        'empresa_id' => $empresa->id,
        'cliente_id' => $cliente->id,
        'titulo' => 'Teste Apontamento Despesa',
        'status' => StatusOrdemServico::EM_EXECUCAO,
        'prioridade' => PrioridadeOrdem::NORMAL,
        'valor_total_estimado' => 3000.00,
    ]);

    $dados = [
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario->id,
        'tipo' => TipoApontamento::DESPESA,
        'data_apontamento' => now()->toDateString(),
        'valor' => 250.75,
        'descricao' => 'Compra de licenças de software',
    ];

    $apontamento = $this->apontamentoService->criar($dados);

    expect($apontamento)->toBeInstanceOf(Apontamento::class);
    expect($apontamento->tipo)->toBe(TipoApontamento::DESPESA);
    expect($apontamento->valor)->toBe(250.75);
    expect($apontamento->descricao)->toBe('Compra de licenças de software');
});

test('pode buscar apontamentos por ordem servico', function () {
    $empresa = Empresa::factory()->create();
    $cliente = Usuario::factory()->create();
    $usuario1 = Usuario::factory()->create();
    $usuario2 = Usuario::factory()->create();

    $ordem = OrdemServico::create([
        'empresa_id' => $empresa->id,
        'cliente_id' => $cliente->id,
        'titulo' => 'Teste Busca Apontamentos',
        'status' => StatusOrdemServico::EM_EXECUCAO,
        'prioridade' => PrioridadeOrdem::NORMAL,
        'valor_total_estimado' => 4000.00,
    ]);

    // Criar múltiplos apontamentos
    Apontamento::create([
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario1->id,
        'tipo' => TipoApontamento::TEMPO,
        'data_apontamento' => now(),
        'horas' => 2.0,
        'descricao' => 'Apontamento 1',
    ]);

    Apontamento::create([
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario2->id,
        'tipo' => TipoApontamento::DESPESA,
        'data_apontamento' => now(),
        'valor' => 150.00,
        'descricao' => 'Apontamento 2',
    ]);

    Apontamento::create([
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario1->id,
        'tipo' => TipoApontamento::TEMPO,
        'data_apontamento' => now(),
        'horas' => 3.5,
        'descricao' => 'Apontamento 3',
    ]);

    $apontamentos = $this->apontamentoService->buscarPorOrdemServico($ordem->id);

    expect($apontamentos)->toHaveCount(3);
});

test('pode calcular total de horas por ordem servico', function () {
    $empresa = Empresa::factory()->create();
    $cliente = Usuario::factory()->create();
    $usuario = Usuario::factory()->create();

    $ordem = OrdemServico::create([
        'empresa_id' => $empresa->id,
        'cliente_id' => $cliente->id,
        'titulo' => 'Teste Cálculo Horas',
        'status' => StatusOrdemServico::EM_EXECUCAO,
        'prioridade' => PrioridadeOrdem::NORMAL,
        'valor_total_estimado' => 3500.00,
    ]);

    // Criar apontamentos de tempo
    Apontamento::create([
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario->id,
        'tipo' => TipoApontamento::TEMPO,
        'data_apontamento' => now(),
        'horas' => 2.5,
        'descricao' => 'Desenvolvimento',
    ]);

    Apontamento::create([
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario->id,
        'tipo' => TipoApontamento::TEMPO,
        'data_apontamento' => now(),
        'horas' => 1.5,
        'descricao' => 'Testes',
    ]);

    // Apontamento de despesa (não deve contar para horas)
    Apontamento::create([
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario->id,
        'tipo' => TipoApontamento::DESPESA,
        'data_apontamento' => now(),
        'valor' => 100.00,
        'descricao' => 'Material',
    ]);

    $totalHoras = $this->apontamentoService->calcularTotalHoras($ordem->id);

    expect($totalHoras)->toBe(4.0);
});

test('pode calcular total de despesas por ordem servico', function () {
    $empresa = Empresa::factory()->create();
    $cliente = Usuario::factory()->create();
    $usuario = Usuario::factory()->create();

    $ordem = OrdemServico::create([
        'empresa_id' => $empresa->id,
        'cliente_id' => $cliente->id,
        'titulo' => 'Teste Cálculo Despesas',
        'status' => StatusOrdemServico::EM_EXECUCAO,
        'prioridade' => PrioridadeOrdem::NORMAL,
        'valor_total_estimado' => 5000.00,
    ]);

    // Criar apontamentos de despesa
    Apontamento::create([
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario->id,
        'tipo' => TipoApontamento::DESPESA,
        'data_apontamento' => now(),
        'valor' => 300.00,
        'descricao' => 'Licenças software',
    ]);

    Apontamento::create([
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario->id,
        'tipo' => TipoApontamento::DESPESA,
        'data_apontamento' => now(),
        'valor' => 150.75,
        'descricao' => 'Material escritório',
    ]);

    // Apontamento de tempo (não deve contar para despesas)
    Apontamento::create([
        'ordem_servico_id' => $ordem->id,
        'usuario_id' => $usuario->id,
        'tipo' => TipoApontamento::TEMPO,
        'data_apontamento' => now(),
        'horas' => 5.0,
        'descricao' => 'Desenvolvimento',
    ]);

    $totalDespesas = $this->apontamentoService->calcularTotalDespesas($ordem->id);

    expect($totalDespesas)->toBe(450.75);
});
