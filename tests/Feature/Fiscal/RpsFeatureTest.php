<?php

test('cria RPS via service e endpoint', function () {
    $fatura = \App\Domain\Financeiro\Fatura::factory()->create([
        'status' => 'ABERTA',
        'valor_servicos' => 1000,
    ]);

    $response = $this->postJson('/fiscal/rps', [
        'fatura_id' => $fatura->id,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('rps', [
        'fatura_id' => $fatura->id,
        'empresa_id' => $fatura->empresa_id,
        'valor_servicos' => 1000,
        'situacao' => 'GERADO',
    ]);
});
