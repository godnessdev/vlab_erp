<?php

use App\Domain\Identidade\Enums\TipoDocumento;
use App\Domain\Identidade\Models\Contato;
use App\Domain\Identidade\Models\Documento;
use App\Domain\Identidade\Models\Endereco;
use App\Domain\Identidade\Models\Papel;
use App\Domain\Identidade\Models\Pessoa;
use App\Domain\Identidade\Validators\DocumentoValidator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Comandos do domínio Identidade
Artisan::command('identidade:gerar-cpf', function () {
    $validator = app(DocumentoValidator::class);
    $cpf = $validator->gerarCpfValido();
    $cpfFormatado = $validator->formatarDocumento(TipoDocumento::CPF, $cpf);
    $this->info('CPF válido gerado: '.$cpfFormatado);
})->purpose('Gera um CPF válido para testes');

Artisan::command('identidade:gerar-cnpj', function () {
    $validator = app(DocumentoValidator::class);
    $cnpj = $validator->gerarCnpjValido();
    $cnpjFormatado = $validator->formatarDocumento(TipoDocumento::CNPJ, $cnpj);
    $this->info('CNPJ válido gerado: '.$cnpjFormatado);
})->purpose('Gera um CNPJ válido para testes');

Artisan::command('identidade:validar-documento {tipo} {numero}', function ($tipo, $numero) {
    $validator = app(DocumentoValidator::class);
    $tipoEnum = TipoDocumento::from(strtoupper($tipo));

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
            ['Pessoas', Pessoa::count(), Pessoa::ativas()->count()],
            ['Pessoas Físicas', Pessoa::fisicas()->count(), Pessoa::fisicas()->ativas()->count()],
            ['Pessoas Jurídicas', Pessoa::juridicas()->count(), Pessoa::juridicas()->ativas()->count()],
            ['Documentos', Documento::count(), Documento::validos()->count()],
            ['Endereços', Endereco::count(), '-'],
            ['Contatos', Contato::count(), '-'],
            ['Papéis', Papel::count(), Papel::ativos()->count()],
        ]
    );
})->purpose('Mostra estatísticas das entidades do domínio Identidade');
