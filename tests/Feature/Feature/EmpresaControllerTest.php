<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Papel;
use App\Enums\StatusEmpresa;
use App\Enums\RegimeTributario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class EmpresaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;
    protected Papel $papel;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Executar migrations e seeders necessários
        $this->artisan('migrate:fresh');
        $this->seed(['PermissoesSeeder', 'PapeisSeeder']);
        
        // Criar usuário de teste
        $this->usuario = Usuario::create([
            'nome' => 'Usuário Teste',
            'email' => 'teste@exemplo.com',
            'cpf' => '123.456.789-09',
            'password' => Hash::make('senha123'),
            'ativo' => true,
        ]);
        
        $this->papel = Papel::where('nome', 'super_admin')->first();
    }

    /** @test */
    public function pode_listar_empresas_como_admin()
    {
        $this->criarEmpresaTeste();
        
        $response = $this->actingAs($this->usuario)
            ->getJson('/api/empresas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'razao_social',
                        'nome_fantasia',
                        'cnpj',
                        'status',
                        'created_at'
                    ]
                ],
                'meta' => ['total', 'per_page', 'current_page']
            ]);
    }

    /** @test */
    public function pode_criar_empresa_com_dados_validos()
    {
        $dadosEmpresa = [
            'razao_social' => 'Empresa API Teste Ltda',
            'nome_fantasia' => 'API Teste',
            'cnpj' => '12.345.678/0001-95',
            'inscricao_estadual' => '123.456.789.123',
            'regime_tributario' => 'simples_nacional',
            'email' => 'contato@apiteste.com',
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

        $response = $this->actingAs($this->usuario)
            ->postJson('/api/empresas', $dadosEmpresa);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'razao_social',
                    'nome_fantasia',
                    'cnpj',
                    'status',
                    'matriz' => [
                        'id',
                        'codigo',
                        'nome',
                        'tipo'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'razao_social' => $dadosEmpresa['razao_social'],
                    'cnpj' => $dadosEmpresa['cnpj'],
                    'status' => StatusEmpresa::ATIVA->value,
                ]
            ]);
    }

    /** @test */
    public function nao_permite_criar_empresa_com_cnpj_invalido()
    {
        $dadosEmpresa = [
            'razao_social' => 'Empresa Teste',
            'cnpj' => '12.345.678/0001-99', // CNPJ inválido
            'email' => 'teste@empresa.com',
        ];

        $response = $this->actingAs($this->usuario)
            ->postJson('/api/empresas', $dadosEmpresa);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cnpj']);
    }

    /** @test */
    public function nao_permite_criar_empresa_com_cnpj_duplicado()
    {
        $empresa = $this->criarEmpresaTeste();

        $dadosNovaEmpresa = [
            'razao_social' => 'Nova Empresa',
            'cnpj' => $empresa->cnpj, // CNPJ duplicado
            'email' => 'nova@empresa.com',
        ];

        $response = $this->actingAs($this->usuario)
            ->postJson('/api/empresas', $dadosNovaEmpresa);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cnpj']);
    }

    /** @test */
    public function pode_visualizar_empresa_especifica()
    {
        $empresa = $this->criarEmpresaTeste();

        $response = $this->actingAs($this->usuario)
            ->getJson("/api/empresas/{$empresa->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'razao_social',
                    'nome_fantasia',
                    'cnpj',
                    'inscricao_estadual',
                    'regime_tributario',
                    'email',
                    'telefone',
                    'status',
                    'dados_endereco',
                    'filiais' => [
                        '*' => ['id', 'codigo', 'nome', 'tipo']
                    ],
                    'parametros_operacionais' => [
                        '*' => ['chave', 'valor', 'tipo']
                    ]
                ]
            ]);
    }

    /** @test */
    public function pode_atualizar_empresa()
    {
        $empresa = $this->criarEmpresaTeste();

        $novosDados = [
            'nome_fantasia' => 'Nome Fantasia Atualizado',
            'telefone' => '(11) 99999-9999',
            'email' => 'novo@email.com',
        ];

        $response = $this->actingAs($this->usuario)
            ->putJson("/api/empresas/{$empresa->id}", $novosDados);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'nome_fantasia' => $novosDados['nome_fantasia'],
                    'telefone' => $novosDados['telefone'],
                    'email' => $novosDados['email'],
                ]
            ]);
    }

    /** @test */
    public function pode_ativar_empresa()
    {
        $empresa = $this->criarEmpresaTeste(['status' => StatusEmpresa::INATIVA]);

        $response = $this->actingAs($this->usuario)
            ->patchJson("/api/empresas/{$empresa->id}/ativar");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => StatusEmpresa::ATIVA->value
                ]
            ]);
    }

    /** @test */
    public function pode_inativar_empresa()
    {
        $empresa = $this->criarEmpresaTeste();

        $response = $this->actingAs($this->usuario)
            ->patchJson("/api/empresas/{$empresa->id}/inativar");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => StatusEmpresa::INATIVA->value
                ]
            ]);
    }

    /** @test */
    public function pode_obter_estatisticas_empresa()
    {
        $empresa = $this->criarEmpresaTeste();

        $response = $this->actingAs($this->usuario)
            ->getJson("/api/empresas/{$empresa->id}/estatisticas");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_filiais',
                    'filiais_ativas',
                    'total_usuarios',
                    'usuarios_ativos',
                    'parametros_configurados'
                ]
            ]);
    }

    /** @test */
    public function pode_buscar_empresa_por_cnpj()
    {
        $empresa = $this->criarEmpresaTeste();

        $response = $this->actingAs($this->usuario)
            ->getJson("/api/empresas/buscar-por-cnpj?cnpj={$empresa->cnpj}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $empresa->id,
                    'cnpj' => $empresa->cnpj
                ]
            ]);
    }

    /** @test */
    public function retorna_404_ao_buscar_empresa_inexistente()
    {
        $response = $this->actingAs($this->usuario)
            ->getJson('/api/empresas/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function nao_permite_acesso_sem_autenticacao()
    {
        $response = $this->getJson('/api/empresas');

        $response->assertStatus(401);
    }

    /**
     * Helper para criar empresa de teste
     */
    private function criarEmpresaTeste(array $dadosExtras = []): Empresa
    {
        $dadosPadrao = [
            'razao_social' => 'Empresa Teste Ltda',
            'nome_fantasia' => 'Teste',
            'cnpj' => '12.345.678/0001-95',
            'inscricao_estadual' => '123.456.789.123',
            'regime_tributario' => RegimeTributario::SIMPLES_NACIONAL,
            'email' => 'contato@teste.com',
            'telefone' => '(11) 98765-4321',
            'status' => StatusEmpresa::ATIVA,
            'dados_endereco' => [
                'cep' => '01310-100',
                'logradouro' => 'Av. Paulista',
                'numero' => '1000',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
            ],
        ];

        return Empresa::create(array_merge($dadosPadrao, $dadosExtras));
    }
}
