<?php

use App\Domain\Identidade\Models\Pessoa;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('cria conta a receber via endpoint', function () {
    $empresa = Empresa::factory()->create();
    $faturaId = Str::uuid()->toString();
    $cliente = Pessoa::factory()->create();
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
