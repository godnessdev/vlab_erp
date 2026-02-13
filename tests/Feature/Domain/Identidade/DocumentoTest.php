<?php

use App\Domain\Identidade\Enums\TipoDocumento;
use App\Domain\Identidade\Models\Documento;
use App\Domain\Identidade\Models\Pessoa;
use App\Domain\Identidade\Validators\DocumentoValidator;

test('pode criar documento com dados válidos', function () {
    $pessoa = Pessoa::factory()->fisica()->create();
    $validator = new DocumentoValidator();
    
    $documento = Documento::factory()->create([
        'pessoa_id' => $pessoa->id,
        'tipo' => TipoDocumento::CPF,
        'valor' => $validator->gerarCpfValido(),
    ]);

    expect($documento)
        ->pessoa_id->toBe($pessoa->id)
        ->tipo->toBe(TipoDocumento::CPF)
        ->valido->toBeTrue()
        ->isPrincipal()->toBeTrue()
        ->isValido()->toBeTrue();
});

test('documento CPF é formatado corretamente', function () {
    $validator = new DocumentoValidator();
    $cpf = $validator->gerarCpfValido();
    
    $documento = Documento::factory()->create([
        'tipo' => TipoDocumento::CPF,
        'valor' => $cpf,
    ]);

    $formatado = $documento->valor_formatado;
    
    expect($formatado)
        ->toMatch('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/')
        ->toHaveLength(14);
});

test('documento CNPJ é formatado corretamente', function () {
    $validator = new DocumentoValidator();
    $cnpj = $validator->gerarCnpjValido();
    
    $documento = Documento::factory()->create([
        'tipo' => TipoDocumento::CNPJ,
        'valor' => $cnpj,
    ]);

    $formatado = $documento->valor_formatado;
    
    expect($formatado)
        ->toMatch('/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/')
        ->toHaveLength(18);
});

test('documento é mascarado para segurança', function () {
    $validator = new DocumentoValidator();
    
    $documento = Documento::factory()->create([
        'tipo' => TipoDocumento::CPF,
        'valor' => $validator->gerarCpfValido(),
    ]);

    $mascarado = $documento->valor_mascarado;
    
    expect($mascarado)
        ->toContain('***')
        ->not->toBe($documento->valor);
});

test('scope válidos funciona corretamente', function () {
    $documentoValido = Documento::factory()->valido()->create();
    $documentoInvalido = Documento::factory()->invalido()->create();
    
    $documentosValidos = Documento::validos()->get();
    
    expect($documentosValidos->pluck('id'))
        ->toContain($documentoValido->id)
        ->not->toContain($documentoInvalido->id);
});

test('scope principais funciona corretamente', function () {
    $cpf = Documento::factory()->cpf()->create();
    $cnpj = Documento::factory()->cnpj()->create();
    $rg = Documento::factory()->rg()->create();
    
    $principais = Documento::principais()->get();
    
    expect($principais->pluck('id'))
        ->toContain($cpf->id)
        ->toContain($cnpj->id)
        ->not->toContain($rg->id);
});

test('validação de documento funciona', function () {
    $validator = new DocumentoValidator();
    $cpfValido = $validator->gerarCpfValido();
    
    $documento = Documento::factory()->create([
        'tipo' => TipoDocumento::CPF,
        'valor' => $cpfValido,
        'valido' => false, // Inicialmente como inválido
    ]);

    $resultado = $documento->validar();
    
    expect($resultado)->toBeTrue();
    expect($documento->fresh()->valido)->toBeTrue();
});

test('RG pode estar vencido', function () {
    $documento = Documento::factory()->create([
        'tipo' => TipoDocumento::RG,
        'data_emissao' => now()->subYears(12), // 12 anos atrás
    ]);

    expect($documento->isVencido())->toBeTrue();
});

test('CPF não tem vencimento', function () {
    $documento = Documento::factory()->create([
        'tipo' => TipoDocumento::CPF,
        'data_emissao' => now()->subYears(20),
    ]);

    expect($documento->isVencido())->toBeFalse();
});

test('relacionamento com pessoa funciona', function () {
    $pessoa = Pessoa::factory()->create();
    $documento = Documento::factory()->create(['pessoa_id' => $pessoa->id]);
    
    expect($documento->pessoa->id)->toBe($pessoa->id);
    expect($pessoa->documentos->pluck('id'))->toContain($documento->id);
});

test('valor do documento é limpo na criação', function () {
    $documento = Documento::factory()->make([
        'tipo' => TipoDocumento::CPF,
        'valor' => '123.456.789-00', // Com formatação
    ]);
    
    $documento->save();
    
    expect($documento->valor)->toBe('12345678900'); // Sem formatação
});
