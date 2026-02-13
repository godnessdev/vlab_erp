<?php

use App\Domain\Financeiro\FinanceiroService;
use App\Domain\Financeiro\ContaReceber;
use App\Domain\Financeiro\ContaPagar;
use App\Domain\Financeiro\Recebimento;
use App\Domain\Financeiro\Pagamento;
use App\Domain\Financeiro\FluxoCaixa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

// Optionally, wrap tests in a TestCase class for clarity
class FinanceiroServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cria_conta_a_receber_via_service()
    {
        $service = new FinanceiroService();
        $conta = $service->criarContaReceber([
            'empresa_id' => \Illuminate\Support\Str::uuid(),
            'fatura_id' => \Illuminate\Support\Str::uuid(),
            'numero_conta' => 'CR123',
            'cliente_id' => \Illuminate\Support\Str::uuid(),
            'valor_original' => 100,
            'valor_liquido_esperado' => 100,
            'data_vencimento' => now(),
            'data_emissao' => now(),
            'forma_cobranca' => 'BOLETO',
        ]);
        $this->assertInstanceOf(ContaReceber::class, $conta);
    }

    /** @test */
    public function cria_conta_a_pagar_via_service()
    {
        $service = new FinanceiroService();
        $conta = $service->criarContaPagar([
            'empresa_id' => \Illuminate\Support\Str::uuid(),
            'fornecedor_id' => \Illuminate\Support\Str::uuid(),
            'numero_conta' => 'CP123',
            'descricao' => 'Despesa',
            'categoria' => 'FORNECEDOR',
            'valor_original' => 100,
            'valor_total' => 100,
            'data_vencimento' => now(),
            'data_emissao' => now(),
        ]);
        $this->assertInstanceOf(ContaPagar::class, $conta);
    }
}
it('cria conta a receber via service', function () {
    $service = new FinanceiroService();
    $conta = $service->criarContaReceber([
        'empresa_id' => \Illuminate\Support\Str::uuid()->toString(),
        'fatura_id' => \Illuminate\Support\Str::uuid()->toString(),
        'numero_conta' => 'CR123',
        'cliente_id' => \Illuminate\Support\Str::uuid()->toString(),
        'valor_original' => 100,
        'valor_liquido_esperado' => 100,
        'data_vencimento' => now(),
        'data_emissao' => now(),
        'forma_cobranca' => 'BOLETO',
    ]);
    expect($conta)->toBeInstanceOf(ContaReceber::class);
});

it('cria conta a pagar via service', function () {
    $service = new FinanceiroService();
    $conta = $service->criarContaPagar([
        'empresa_id' => \Illuminate\Support\Str::uuid()->toString(),
        'fornecedor_id' => \Illuminate\Support\Str::uuid()->toString(),
        'numero_conta' => 'CP123',
        'descricao' => 'Despesa',
        'categoria' => 'FORNECEDOR',
        'valor_original' => 100,
        'valor_total' => 100,
        'data_vencimento' => now(),
        'data_emissao' => now(),
    ]);
    expect($conta)->toBeInstanceOf(ContaPagar::class);
});

it('registra recebimento via service', function () {
    $conta = ContaReceber::factory()->create();
    $service = new FinanceiroService();
    $rec = $service->registrarRecebimento([
        'conta_receber_id' => $conta->id,
        'data_recebimento' => now(),
        'valor_recebido' => 100,
        'forma_recebimento' => 'BOLETO',
    ]);
    expect($rec)->toBeInstanceOf(Recebimento::class);
});

it('registra pagamento via service', function () {
    $conta = ContaPagar::factory()->create();
    $service = new FinanceiroService();
    $pag = $service->registrarPagamento([
        'conta_pagar_id' => $conta->id,
        'data_pagamento' => now(),
        'valor_pago' => 100,
        'forma_pagamento' => 'BOLETO',
    ]);
    expect($pag)->toBeInstanceOf(Pagamento::class);
});

it('calcula saldo acumulado', function () {
    $empresaId = \Illuminate\Support\Str::uuid()->toString();
    FluxoCaixa::factory()->create([
        'empresa_id' => $empresaId,
        'valor' => 100,
        'realizado' => true,
    ]);
    $service = new FinanceiroService();
    $saldo = $service->calcularSaldoAcumulado($empresaId);
    expect($saldo)->toBeFloat();
    expect($saldo)->toBe(100.0);
});
