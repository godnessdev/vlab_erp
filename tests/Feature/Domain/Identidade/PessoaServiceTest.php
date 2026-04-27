<?php

use App\Domain\Identidade\Enums\TipoDocumento;
use App\Domain\Identidade\Enums\TipoPessoa;
use App\Domain\Identidade\Models\Pessoa;
use App\Domain\Identidade\Services\PessoaService;
use App\Domain\Identidade\Validators\DocumentoValidator;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->service = app(PessoaService::class);
    $this->validator = new DocumentoValidator;
});

describe('Criação de pessoa', function () {
    test('pode criar pessoa física completa', function () {
        $dadosPessoa = [
            'tipo' => 'FISICA',
            'nome_razao_social' => 'João Silva',
            'data_nascimento_constituicao' => '1990-01-01',
            'documentos' => [
                [
                    'tipo' => 'CPF',
                    'valor' => $this->validator->gerarCpfValido(),
                ],
            ],
            'enderecos' => [
                [
                    'tipo' => 'PRINCIPAL',
                    'logradouro' => 'Rua das Flores',
                    'numero' => '123',
                    'bairro' => 'Centro',
                    'cidade' => 'São Paulo',
                    'estado' => 'SP',
                    'cep' => '01234-567',
                ],
            ],
            'contatos' => [
                [
                    'tipo' => 'EMAIL',
                    'valor' => 'joao@email.com',
                    'principal' => true,
                ],
            ],
        ];

        $pessoa = $this->service->criar($dadosPessoa);

        expect($pessoa)
            ->nome_razao_social->toBe('João Silva')
            ->tipo->toBe(TipoPessoa::FISICA)
            ->documentos->toHaveCount(1)
            ->enderecos->toHaveCount(1)
            ->contatos->toHaveCount(1);
    });

    test('pode criar pessoa jurídica completa', function () {
        $dadosPessoa = [
            'tipo' => 'JURIDICA',
            'nome_razao_social' => 'Empresa XYZ Ltda',
            'nome_fantasia' => 'XYZ Soluções',
            'data_nascimento_constituicao' => '2010-01-01',
            'documentos' => [
                [
                    'tipo' => 'CNPJ',
                    'valor' => $this->validator->gerarCnpjValido(),
                ],
            ],
            'enderecos' => [
                [
                    'tipo' => 'PRINCIPAL',
                    'logradouro' => 'Av. Paulista',
                    'numero' => '1000',
                    'bairro' => 'Bela Vista',
                    'cidade' => 'São Paulo',
                    'estado' => 'SP',
                    'cep' => '01310-100',
                ],
            ],
            'contatos' => [
                [
                    'tipo' => 'EMAIL',
                    'valor' => 'contato@xyz.com.br',
                    'principal' => true,
                ],
            ],
        ];

        $pessoa = $this->service->criar($dadosPessoa);

        expect($pessoa)
            ->nome_razao_social->toBe('Empresa XYZ Ltda')
            ->nome_fantasia->toBe('XYZ Soluções')
            ->tipo->toBe(TipoPessoa::JURIDICA)
            ->documentos->toHaveCount(1)
            ->enderecos->toHaveCount(1)
            ->contatos->toHaveCount(1);
    });

    test('falha ao criar pessoa com nome muito curto', function () {
        $dados = [
            'tipo' => 'FISICA',
            'nome_razao_social' => 'A', // Muito curto
        ];

        expect(fn () => $this->service->criar($dados))
            ->toThrow(ValidationException::class);
    });

    test('falha ao criar pessoa física com CNPJ', function () {
        $dados = [
            'tipo' => 'FISICA',
            'nome_razao_social' => 'João Silva',
            'documentos' => [
                [
                    'tipo' => 'CNPJ',
                    'valor' => $this->validator->gerarCnpjValido(),
                ],
            ],
        ];

        expect(fn () => $this->service->criar($dados))
            ->toThrow(ValidationException::class);
    });

    test('falha ao criar pessoa jurídica com CPF', function () {
        $dados = [
            'tipo' => 'JURIDICA',
            'nome_razao_social' => 'Empresa XYZ Ltda',
            'documentos' => [
                [
                    'tipo' => 'CPF',
                    'valor' => $this->validator->gerarCpfValido(),
                ],
            ],
        ];

        expect(fn () => $this->service->criar($dados))
            ->toThrow(ValidationException::class);
    });

    test('falha ao criar pessoa física com nome fantasia', function () {
        $dados = [
            'tipo' => 'FISICA',
            'nome_razao_social' => 'João Silva',
            'nome_fantasia' => 'João Soluções', // Não permitido para PF
        ];

        expect(fn () => $this->service->criar($dados))
            ->toThrow(ValidationException::class);
    });
});

describe('Atualização de pessoa', function () {
    test('pode atualizar dados básicos da pessoa', function () {
        $pessoa = Pessoa::factory()->fisica()->create();

        $dadosAtualizacao = [
            'nome_razao_social' => 'João Silva Santos',
            'data_nascimento_constituicao' => '1985-05-15',
        ];

        $pessoaAtualizada = $this->service->atualizar($pessoa, $dadosAtualizacao);

        expect($pessoaAtualizada)
            ->nome_razao_social->toBe('João Silva Santos')
            ->data_nascimento_constituicao->format('Y-m-d')->toBe('1985-05-15');
    });
});

describe('Busca de pessoa', function () {
    test('pode buscar pessoa por documento', function () {
        $cpf = $this->validator->gerarCpfValido();
        $pessoa = Pessoa::factory()->fisica()->create();
        $pessoa->documentos()->create([
            'tipo' => 'CPF',
            'valor' => $cpf,
            'valido' => true,
        ]);

        $pessoaEncontrada = $this->service->buscarPorDocumento($cpf);

        expect($pessoaEncontrada->id)->toBe($pessoa->id);
    });

    test('busca por documento com formatação', function () {
        $cpf = $this->validator->gerarCpfValido();
        $cpfFormatado = $this->validator->formatarDocumento(TipoDocumento::CPF, $cpf);

        $pessoa = Pessoa::factory()->fisica()->create();
        $pessoa->documentos()->create([
            'tipo' => 'CPF',
            'valor' => $cpf,
            'valido' => true,
        ]);

        $pessoaEncontrada = $this->service->buscarPorDocumento($cpfFormatado);

        expect($pessoaEncontrada->id)->toBe($pessoa->id);
    });

    test('pode buscar pessoa por nome', function () {
        $pessoa = Pessoa::factory()->create(['nome_razao_social' => 'João Silva Santos']);

        $pessoas = $this->service->buscarPorNome('João');

        expect($pessoas->pluck('id'))->toContain($pessoa->id);
    });

    test('pode buscar pessoa por nome fantasia', function () {
        $pessoa = Pessoa::factory()->juridica()->create([
            'nome_razao_social' => 'Empresa XYZ Ltda',
            'nome_fantasia' => 'XYZ Soluções',
        ]);

        $pessoas = $this->service->buscarPorNome('XYZ');

        expect($pessoas->pluck('id'))->toContain($pessoa->id);
    });
});

describe('Gerenciamento de status', function () {
    test('pode inativar pessoa', function () {
        $pessoa = Pessoa::factory()->create();

        $resultado = $this->service->inativar($pessoa);

        expect($resultado)->toBeTrue();
        expect($pessoa->fresh()->isInativa())->toBeTrue();
    });

    test('pode ativar pessoa', function () {
        $pessoa = Pessoa::factory()->inativa()->create();

        $resultado = $this->service->ativar($pessoa);

        expect($resultado)->toBeTrue();
        expect($pessoa->fresh()->isAtiva())->toBeTrue();
    });
});

describe('Gerenciamento de papéis', function () {
    test('pode adicionar papel à pessoa', function () {
        $pessoa = Pessoa::factory()->create();
        $empresaId = fake()->uuid();

        $papel = $this->service->adicionarPapel($pessoa, 'CLIENTE', $empresaId);

        expect($papel)
            ->pessoa_id->toBe($pessoa->id)
            ->empresa_id->toBe($empresaId)
            ->tipo_papel->value->toBe('CLIENTE');
    });

    test('pode adicionar papel com dados específicos', function () {
        $pessoa = Pessoa::factory()->create();
        $empresaId = fake()->uuid();
        $dadosEspecificos = [
            'limite_credito' => 10000,
            'categoria' => 'OURO',
        ];

        $papel = $this->service->adicionarPapel($pessoa, 'CLIENTE', $empresaId, $dadosEspecificos);

        expect($papel->dadosEspecificos)->toHaveCount(2);
        expect($papel->obterDado('limite_credito'))->toBe(10000);
        expect($papel->obterDado('categoria'))->toBe('OURO');
    });

    test('falha ao adicionar papel duplicado', function () {
        $pessoa = Pessoa::factory()->create();
        $empresaId = fake()->uuid();

        $this->service->adicionarPapel($pessoa, 'CLIENTE', $empresaId);

        expect(fn () => $this->service->adicionarPapel($pessoa, 'CLIENTE', $empresaId))
            ->toThrow(ValidationException::class);
    });

    test('pode remover papel da pessoa', function () {
        $pessoa = Pessoa::factory()->create();
        $empresaId = fake()->uuid();

        $papel = $this->service->adicionarPapel($pessoa, 'CLIENTE', $empresaId);
        $resultado = $this->service->removerPapel($pessoa, 'CLIENTE', $empresaId);

        expect($resultado)->toBeTrue();
        expect($papel->fresh()->isInativo())->toBeTrue();
    });
});

describe('Validações de exclusão', function () {
    test('pode verificar se pessoa pode ser excluída', function () {
        $pessoa = Pessoa::factory()->create();

        expect($this->service->podeExcluir($pessoa))->toBeTrue();
    });

    test('não pode excluir pessoa com papéis ativos', function () {
        $pessoa = Pessoa::factory()->create();
        $empresaId = fake()->uuid();

        $this->service->adicionarPapel($pessoa, 'CLIENTE', $empresaId);

        expect($this->service->podeExcluir($pessoa))->toBeFalse();
    });

    test('pode excluir pessoa sem vínculos', function () {
        $pessoa = Pessoa::factory()->create();

        $resultado = $this->service->excluir($pessoa);

        expect($resultado)->toBeTrue();
        expect(Pessoa::find($pessoa->id))->toBeNull();
    });

    test('falha ao excluir pessoa com papéis ativos', function () {
        $pessoa = Pessoa::factory()->create();
        $empresaId = fake()->uuid();

        $this->service->adicionarPapel($pessoa, 'CLIENTE', $empresaId);

        expect(fn () => $this->service->excluir($pessoa))
            ->toThrow(ValidationException::class);
    });
});
