<?php

test('emite NFS-e via service e endpoint', function () {
    $empresa = \App\Models\Empresa::factory()->create();
    $usuario = \App\Models\Usuario::factory()->create();
    $fatura = \App\Domain\Faturamento\Models\Fatura::factory()->create([
        'empresa_id' => $empresa->id,
        'cliente_id' => $usuario->id,
        'status' => 'ENVIADA',
        'valor_servicos' => 1000,
    ]);
    $rps = app(\App\Domain\Fiscal\Services\RpsService::class)->gerarRps($fatura->id);

    // Mock integração ACBrLib e dependências se necessário
    $response = $this->postJson('/fiscal/nfse', [
        'rps_id' => $rps->id,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('nfse', [
        'rps_id' => $rps->id,
        'empresa_id' => $fatura->empresa_id,
        'status' => 'AUTORIZADA',
    ]);
});

test('cancela NFS-e via endpoint', function () {
    $nfse = \Database\Factories\NfseFactory::new()->create([
        'status' => 'AUTORIZADA',
        'data_autorizacao' => now(),
    ]);

    // Mock integração ACBrLib e dependências se necessário
    $response = $this->postJson("/fiscal/nfse/{$nfse->id}/cancelar", [
        'motivo' => 'Cancelamento por erro de emissão fiscal.',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('nfse', [
        'id' => $nfse->id,
        'status' => 'CANCELADA',
    ]);
});
