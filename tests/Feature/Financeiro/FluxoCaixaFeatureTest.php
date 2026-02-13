<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('consulta saldo acumulado via endpoint', function () {
    $empresa = \App\Models\Empresa::factory()->create();
    $response = $this->getJson('/financeiro/fluxo-caixa/saldo?empresa_id=' . $empresa->id);
    $response->assertOk();
});

it('consulta projeção de fluxo de caixa', function () {
    $empresa = \App\Models\Empresa::factory()->create();
    $response = $this->getJson('/financeiro/fluxo-caixa/projecao?empresa_id=' . $empresa->id . '&data_inicio=2026-01-01&data_fim=2026-12-31');
    $response->assertOk();
});
