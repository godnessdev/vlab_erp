<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Domain\Identidade\Services\PessoaService;
use App\Domain\Identidade\Validators\DocumentoValidator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Comandos do domínio Identidade
Artisan::command('identidade:gerar-cpf', function () {
    $validator = app(DocumentoValidator::class);
    $cpf = $validator->gerarCpfValido();
    $cpfFormatado = $validator->formatarDocumento(\App\Domain\Identidade\Enums\TipoDocumento::CPF, $cpf);
    $this->info("CPF válido gerado: " . $cpfFormatado);
})->purpose('Gera um CPF válido para testes');

Artisan::command('identidade:gerar-cnpj', function () {
    $validator = app(DocumentoValidator::class);
    $cnpj = $validator->gerarCnpjValido();
    $cnpjFormatado = $validator->formatarDocumento(\App\Domain\Identidade\Enums\TipoDocumento::CNPJ, $cnpj);
    $this->info("CNPJ válido gerado: " . $cnpjFormatado);
})->purpose('Gera um CNPJ válido para testes');

Artisan::command('identidade:validar-documento {tipo} {numero}', function ($tipo, $numero) {
    $validator = app(DocumentoValidator::class);
    $tipoEnum = \App\Domain\Identidade\Enums\TipoDocumento::from(strtoupper($tipo));
    
    $valido = $validator->validar($tipoEnum, $numero);
    
    if ($valido) {
        $this->info("✓ Documento {$tipo} '{$numero}' é válido");
    } else {
        $this->error("✗ Documento {$tipo} '{$numero}' é inválido");
    }
})->purpose('Valida um documento CPF, CNPJ, RG ou IE');

Artisan::command('identidade:estatisticas', function () {
    $this->info('Estatísticas do domínio Identidade:');
    $this->table(
        ['Entidade', 'Total', 'Ativas'],
        [
            ['Pessoas', \App\Domain\Identidade\Models\Pessoa::count(), \App\Domain\Identidade\Models\Pessoa::ativas()->count()],
            ['Pessoas Físicas', \App\Domain\Identidade\Models\Pessoa::fisicas()->count(), \App\Domain\Identidade\Models\Pessoa::fisicas()->ativas()->count()],
            ['Pessoas Jurídicas', \App\Domain\Identidade\Models\Pessoa::juridicas()->count(), \App\Domain\Identidade\Models\Pessoa::juridicas()->ativas()->count()],
            ['Documentos', \App\Domain\Identidade\Models\Documento::count(), \App\Domain\Identidade\Models\Documento::validos()->count()],
            ['Endereços', \App\Domain\Identidade\Models\Endereco::count(), '-'],
            ['Contatos', \App\Domain\Identidade\Models\Contato::count(), '-'],
            ['Papéis', \App\Domain\Identidade\Models\Papel::count(), \App\Domain\Identidade\Models\Papel::ativos()->count()],
        ]
    );
})->purpose('Mostra estatísticas das entidades do domínio Identidade');
