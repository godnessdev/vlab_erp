<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cria conta a receber via endpoint', function () {
    $empresa = \App\Models\Empresa::factory()->create();
    $faturaId = \Illuminate\Support\Str::uuid()->toString();
    $cliente = \App\Domain\Identidade\Models\Pessoa::factory()->create();
    $response = $this->postJson('/financeiro/contas-receber', [
        'empresa_id' => $empresa->id,
        'fatura_id' => $faturaId,
        'numero_conta' => 'CR123',
        'cliente_id' => $cliente->id,
        'valor_original' => 100,
        'valor_liquido_esperado' => 100,
        'data_vencimento' => now(),
        'data_emissao' => now(),
        'forma_cobranca' => 'BOLETO',
    ]);
    $response->assertCreated();
});

it('lista contas a receber', function () {
    $response = $this->getJson('/financeiro/contas-receber');
    $response->assertOk();
});
