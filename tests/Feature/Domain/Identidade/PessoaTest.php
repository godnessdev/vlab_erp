<?php

use App\Domain\Identidade\Enums\StatusPessoa;
use App\Domain\Identidade\Enums\TipoPessoa;
use App\Domain\Identidade\Models\Pessoa;

test('pode criar pessoa física', function () {
    $pessoa = Pessoa::factory()->fisica()->create([
        'nome_razao_social' => 'João Silva',
    ]);

    expect($pessoa)
        ->nome_razao_social->toBe('João Silva')
        ->tipo->toBe(TipoPessoa::FISICA)
        ->status->toBe(StatusPessoa::ATIVO)
        ->nome_fantasia->toBeNull()
        ->isAtiva()->toBeTrue()
        ->isFisica()->toBeTrue()
        ->isJuridica()->toBeFalse();
});

test('pode criar pessoa jurídica', function () {
    $pessoa = Pessoa::factory()->juridica()->create([
        'nome_razao_social' => 'Empresa XYZ Ltda',
        'nome_fantasia' => 'XYZ Soluções',
    ]);

    expect($pessoa)
        ->nome_razao_social->toBe('Empresa XYZ Ltda')
        ->nome_fantasia->toBe('XYZ Soluções')
        ->tipo->toBe(TipoPessoa::JURIDICA)
        ->status->toBe(StatusPessoa::ATIVO)
        ->isAtiva()->toBeTrue()
        ->isJuridica()->toBeTrue()
        ->isFisica()->toBeFalse();
});

test('pode inativar pessoa', function () {
    $pessoa = Pessoa::factory()->create();
    
    $pessoa->inativar();
    
    expect($pessoa->fresh())
        ->status->toBe(StatusPessoa::INATIVO)
        ->isInativa()->toBeTrue()
        ->isAtiva()->toBeFalse();
});

test('pode ativar pessoa inativa', function () {
    $pessoa = Pessoa::factory()->inativa()->create();
    
    $pessoa->ativar();
    
    expect($pessoa->fresh())
        ->status->toBe(StatusPessoa::ATIVO)
        ->isAtiva()->toBeTrue()
        ->isInativa()->toBeFalse();
});

test('pessoa física tem idade quando tem data de nascimento', function () {
    $pessoa = Pessoa::factory()->fisica()->create([
        'data_nascimento_constituicao' => now()->subYears(30)->toDateString(),
    ]);
    
    expect($pessoa->idade)->toBe(30);
});

test('pessoa jurídica tem tempo de constituição', function () {
    $pessoa = Pessoa::factory()->juridica()->create([
        'data_nascimento_constituicao' => now()->subYears(5)->toDateString(),
    ]);
    
    expect($pessoa->tempo_constituicao)->toBe(5);
});

test('get nome completo para pessoa física', function () {
    $pessoa = Pessoa::factory()->fisica()->create([
        'nome_razao_social' => 'João Silva',
    ]);
    
    expect($pessoa->getNomeCompleto())->toBe('João Silva');
});

test('get nome completo para pessoa jurídica com nome fantasia', function () {
    $pessoa = Pessoa::factory()->juridica()->create([
        'nome_razao_social' => 'Empresa XYZ Ltda',
        'nome_fantasia' => 'XYZ Soluções',
    ]);
    
    expect($pessoa->getNomeCompleto())->toBe('Empresa XYZ Ltda (XYZ Soluções)');
});

test('get nome completo para pessoa jurídica sem nome fantasia', function () {
    $pessoa = Pessoa::factory()->juridica()->create([
        'nome_razao_social' => 'Empresa ABC Ltda',
        'nome_fantasia' => null,
    ]);
    
    expect($pessoa->getNomeCompleto())->toBe('Empresa ABC Ltda');
});

test('scope ativas funciona corretamente', function () {
    $pessoaAtiva = Pessoa::factory()->create();
    $pessoaInativa = Pessoa::factory()->inativa()->create();
    
    $pessoasAtivas = Pessoa::ativas()->get();
    
    expect($pessoasAtivas->pluck('id'))
        ->toContain($pessoaAtiva->id)
        ->not->toContain($pessoaInativa->id);
});

test('scope físicas funciona corretamente', function () {
    $pessoaFisica = Pessoa::factory()->fisica()->create();
    $pessoaJuridica = Pessoa::factory()->juridica()->create();
    
    $pessoasFisicas = Pessoa::fisicas()->get();
    
    expect($pessoasFisicas->pluck('id'))
        ->toContain($pessoaFisica->id)
        ->not->toContain($pessoaJuridica->id);
});

test('scope jurídicas funciona corretamente', function () {
    $pessoaFisica = Pessoa::factory()->fisica()->create();
    $pessoaJuridica = Pessoa::factory()->juridica()->create();
    
    $pessoasJuridicas = Pessoa::juridicas()->get();
    
    expect($pessoasJuridicas->pluck('id'))
        ->toContain($pessoaJuridica->id)
        ->not->toContain($pessoaFisica->id);
});

test('relacionamento com endereços funciona', function () {
    $pessoa = Pessoa::factory()->comEndereco()->create();
    
    expect($pessoa->enderecos)->toHaveCount(1);
    expect($pessoa->enderecos->first()->pessoa_id)->toBe($pessoa->id);
});

test('relacionamento com contatos funciona', function () {
    $pessoa = Pessoa::factory()->comContatos()->create();
    
    expect($pessoa->contatos)->toHaveCount(2); // Email + Celular
    expect($pessoa->contatos->first()->pessoa_id)->toBe($pessoa->id);
});

test('pode obter CPF/CNPJ principal', function () {
    $pessoa = Pessoa::factory()->comCpf()->create();
    
    expect($pessoa->getCpfCnpj())->not->toBeNull();
    expect(strlen($pessoa->getCpfCnpj()))->toBe(11); // CPF sem formatação
});

test('pode obter email principal', function () {
    $pessoa = Pessoa::factory()->comContatos()->create();
    
    $email = $pessoa->getEmailPrincipal();
    
    expect($email)->not->toBeNull();
    expect($email)->toContain('@');
});

test('pode obter telefone principal', function () {
    $pessoa = Pessoa::factory()->comContatos()->create();
    
    $telefone = $pessoa->getTelefonePrincipal();
    
    expect($telefone)->not->toBeNull();
    expect(strlen($telefone))->toBeGreaterThanOrEqual(10);
});
