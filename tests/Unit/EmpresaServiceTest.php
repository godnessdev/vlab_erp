<?php

namespace Tests\Unit;

use App\Enums\RegimeTributario;
use App\Enums\StatusEmpresa;
use App\Enums\TipoFilial;
use App\Models\Empresa;
use App\Models\Filial;
use App\Services\EmpresaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmpresaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmpresaService $empresaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->empresaService = new EmpresaService;
    }

    /** @test */
    public function pode_criar_empresa_com_dados_validos()
    {
        $dados = [
            'razao_social' => 'Empresa Teste Ltda',
            'nome_fantasia' => 'Teste',
            'cnpj' => '12.345.678/0001-95',
            'inscricao_estadual' => '123.456.789.123',
            'regime_tributario' => RegimeTributario::SIMPLES_NACIONAL,
            'email' => 'contato@teste.com',
            'telefone' => '(11) 98765-4321',
            'dados_endereco' => [
                'cep' => '01310-100',
                'logradouro' => 'Av. Paulista',
                'numero' => '1000',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
            ],
        ];

        $empresa = $this->empresaService->criarEmpresa($dados);

        $this->assertInstanceOf(Empresa::class, $empresa);
        $this->assertEquals($dados['razao_social'], $empresa->razao_social);
        $this->assertEquals($dados['cnpj'], $empresa->cnpj);
        $this->assertEquals(StatusEmpresa::ATIVA, $empresa->status);

        // Verificar se matriz foi criada
        $matriz = $empresa->filiais()->where('tipo', TipoFilial::MATRIZ)->first();
        $this->assertNotNull($matriz);
        $this->assertEquals('001', $matriz->codigo);

        // Verificar se parâmetros operacionais foram criados
        $this->assertGreaterThan(0, $empresa->parametrosOperacionais()->count());
    }

    /** @test */
    public function valida_cnpj_matematicamente()
    {
        // CNPJ inválido
        $this->assertFalse($this->empresaService->validarCnpjMatematico('12.345.678/0001-99'));

        // CNPJ válido
        $this->assertTrue($this->empresaService->validarCnpjMatematico('12.345.678/0001-95'));

        // CNPJ com formato incorreto
        $this->assertFalse($this->empresaService->validarCnpjMatematico('123.456.789-01'));
    }

    /** @test */
    public function nao_permite_cnpj_duplicado()
    {
        // Criar primeira empresa
        $dados1 = [
            'razao_social' => 'Empresa 1',
            'cnpj' => '12.345.678/0001-95',
            'inscricao_estadual' => '123.456.789.123',
            'regime_tributario' => RegimeTributario::SIMPLES_NACIONAL,
            'email' => 'empresa1@teste.com',
        ];

        $this->empresaService->criarEmpresa($dados1);

        // Tentar criar segunda empresa com mesmo CNPJ
        $dados2 = [
            'razao_social' => 'Empresa 2',
            'cnpj' => '12.345.678/0001-95', // CNPJ duplicado
            'inscricao_estadual' => '987.654.321.987',
            'regime_tributario' => RegimeTributario::LUCRO_PRESUMIDO,
            'email' => 'empresa2@teste.com',
        ];

        $this->expectException(ValidationException::class);
        $this->empresaService->criarEmpresa($dados2);
    }

    /** @test */
    public function pode_atualizar_empresa()
    {
        $empresa = $this->criarEmpresaExemplo();

        $novosDados = [
            'nome_fantasia' => 'Nome Fantasia Atualizado',
            'telefone' => '(11) 99999-9999',
            'email' => 'novo@email.com',
        ];

        $empresaAtualizada = $this->empresaService->atualizarEmpresa($empresa->id, $novosDados);

        $this->assertEquals($novosDados['nome_fantasia'], $empresaAtualizada->nome_fantasia);
        $this->assertEquals($novosDados['telefone'], $empresaAtualizada->telefone);
        $this->assertEquals($novosDados['email'], $empresaAtualizada->email);
        $this->assertEquals($empresa->cnpj, $empresaAtualizada->cnpj); // CNPJ não mudou
    }

    /** @test */
    public function pode_ativar_e_inativar_empresa()
    {
        $empresa = $this->criarEmpresaExemplo();

        // Inativar
        $empresaInativa = $this->empresaService->alterarStatusEmpresa($empresa->id, StatusEmpresa::INATIVA);
        $this->assertEquals(StatusEmpresa::INATIVA, $empresaInativa->status);

        // Ativar novamente
        $empresaAtiva = $this->empresaService->alterarStatusEmpresa($empresa->id, StatusEmpresa::ATIVA);
        $this->assertEquals(StatusEmpresa::ATIVA, $empresaAtiva->status);
    }

    /** @test */
    public function obtem_estatisticas_empresa()
    {
        $empresa = $this->criarEmpresaExemplo();

        // Criar filiais adicionais
        Filial::create([
            'empresa_id' => $empresa->id,
            'codigo' => '002',
            'nome' => 'Filial Teste',
            'tipo' => TipoFilial::FILIAL,
            'cnpj' => '12.345.678/0002-76',
            'inscricao_estadual' => '123.456.789.124',
            'email' => 'filial@teste.com',
            'telefone' => '(11) 99999-9999',
            'ativo' => true,
            'endereco' => [],
            'configuracao_fiscal' => [],
        ]);

        $estatisticas = $this->empresaService->obterEstatisticas($empresa->id);

        $this->assertArrayHasKey('total_filiais', $estatisticas);
        $this->assertArrayHasKey('filiais_ativas', $estatisticas);
        $this->assertArrayHasKey('total_usuarios', $estatisticas);
        $this->assertEquals(2, $estatisticas['total_filiais']); // Matriz + 1 filial
    }

    /** @test */
    public function busca_empresa_por_cnpj()
    {
        $empresa = $this->criarEmpresaExemplo();

        $empresaEncontrada = $this->empresaService->buscarPorCnpj($empresa->cnpj);
        $this->assertEquals($empresa->id, $empresaEncontrada->id);

        $empresaNaoEncontrada = $this->empresaService->buscarPorCnpj('99.999.999/0001-99');
        $this->assertNull($empresaNaoEncontrada);
    }

    /** @test */
    public function cria_parametros_operacionais_padrao()
    {
        $empresa = $this->criarEmpresaExemplo();

        $parametros = $empresa->parametrosOperacionais;

        $this->assertGreaterThan(0, $parametros->count());

        // Verificar se parâmetros essenciais foram criados
        $parametrosChaves = $parametros->pluck('chave')->toArray();

        $this->assertContains('timezone', $parametrosChaves);
        $this->assertContains('moeda_padrao', $parametrosChaves);
        $this->assertContains('backup_automatico', $parametrosChaves);
    }

    /** @test */
    public function nao_permite_criar_empresa_com_cnpj_invalido()
    {
        $dados = [
            'razao_social' => 'Empresa Teste',
            'cnpj' => '12.345.678/0001-99', // CNPJ inválido
            'inscricao_estadual' => '123.456.789.123',
            'regime_tributario' => RegimeTributario::SIMPLES_NACIONAL,
            'email' => 'contato@teste.com',
        ];

        $this->expectException(ValidationException::class);
        $this->empresaService->criarEmpresa($dados);
    }

    /**
     * Helper para criar empresa de exemplo
     */
    private function criarEmpresaExemplo(): Empresa
    {
        return $this->empresaService->criarEmpresa([
            'razao_social' => 'Empresa Exemplo Ltda',
            'nome_fantasia' => 'Exemplo',
            'cnpj' => '12.345.678/0001-95',
            'inscricao_estadual' => '123.456.789.123',
            'regime_tributario' => RegimeTributario::SIMPLES_NACIONAL,
            'email' => 'contato@exemplo.com',
            'telefone' => '(11) 98765-4321',
            'dados_endereco' => [
                'cep' => '01310-100',
                'logradouro' => 'Av. Paulista',
                'numero' => '1000',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
            ],
        ]);
    }
}
