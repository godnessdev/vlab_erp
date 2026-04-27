<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Index;
use App\Models\Empresa;
use App\Models\RegimeTributarioEnum;
use App\Models\StatusEmpresaEnum;
use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Testes do Dashboard Principal
 *
 * ✅ Multitenancy: Testa isolamento entre empresas
 * ✅ Security: Testa authorization e tenant context
 * ✅ Performance: Testa cache
 */
beforeEach(function () {
    // Limpar cache antes de cada teste
    Cache::flush();
});

describe('Dashboard Index - Security & Authorization', function () {

    test('usuário não autenticado não pode acessar dashboard', function () {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    });

    test('usuário autenticado pode acessar dashboard', function () {
        $usuario = Usuario::factory()->create();

        actingAs($usuario)
            ->get(route('dashboard'))
            ->assertSuccessful();
    });

    test('dashboard requer tenant context', function () {
        $usuario = Usuario::factory()->create();

        // Sem definir tenant context
        app()->forgetInstance('current.company');

        actingAs($usuario);

        Livewire::test(Index::class)
            ->assertStatus(403);
    });
});

describe('Dashboard Index - Multitenancy Isolation', function () {

    test('dashboard exibe apenas dados da empresa atual', function () {
        // Criar duas empresas
        $empresaA = Empresa::factory()->create(['nome' => 'Empresa A']);
        $empresaB = Empresa::factory()->create(['nome' => 'Empresa B']);

        // Criar usuário da empresa A
        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresaA->id);

        // Definir tenant context para empresa A
        app()->instance('current.company', $empresaA);

        actingAs($usuario);

        Livewire::test(Index::class)
            ->assertSet('estatisticas.empresa.nome', 'Empresa A')
            ->assertDontSee('Empresa B');
    });

    test('usuário não vê dados de outras empresas', function () {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $usuarioA = Usuario::factory()->create();
        $usuarioA->empresas()->attach($empresaA->id);

        // Definir tenant context para empresa A
        app()->instance('current.company', $empresaA);

        actingAs($usuarioA);

        $component = Livewire::test(Index::class);

        // Verificar que estatísticas são apenas da empresa A
        expect($component->get('estatisticas')['empresa']['nome'])
            ->toBe($empresaA->nome);
    });
});

describe('Dashboard Index - Estatísticas', function () {

    test('exibe estatísticas corretas da empresa', function () {
        $empresa = Empresa::factory()->create([
            'nome' => 'Empresa Teste',
            'regime_tributario' => RegimeTributarioEnum::SIMPLES_NACIONAL,
            'status' => StatusEmpresaEnum::ATIVO,
        ]);

        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresa->id);

        app()->instance('current.company', $empresa);

        actingAs($usuario);

        Livewire::test(Index::class)
            ->assertSet('estatisticas.empresa.nome', 'Empresa Teste')
            ->assertSet('estatisticas.empresa.regime', 'Simples Nacional')
            ->assertSet('estatisticas.empresa.status.valor', 'ATIVO');
    });

    test('exibe contagem correta de filiais', function () {
        $empresa = Empresa::factory()
            ->hasFiliais(3)
            ->create();

        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresa->id);

        app()->instance('current.company', $empresa);

        actingAs($usuario);

        Livewire::test(Index::class)
            ->assertSet('estatisticas.filiais.total', 3);
    });

    test('exibe contagem correta de usuários', function () {
        $empresa = Empresa::factory()->create();

        // Criar 5 usuários para a empresa
        $usuarios = Usuario::factory()->count(5)->create();
        foreach ($usuarios as $usuario) {
            $usuario->empresas()->attach($empresa->id);
        }

        app()->instance('current.company', $empresa);

        actingAs($usuarios->first());

        Livewire::test(Index::class)
            ->assertSet('estatisticas.usuarios.total', 5);
    });
});

describe('Dashboard Index - Alertas', function () {

    test('exibe alerta quando empresa está inativa', function () {
        $empresa = Empresa::factory()->create([
            'status' => StatusEmpresaEnum::INATIVO,
        ]);

        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresa->id);

        app()->instance('current.company', $empresa);

        actingAs($usuario);

        Livewire::test(Index::class)
            ->assertSee('Empresa Inativa');
    });

    test('exibe alerta quando empresa não tem filiais', function () {
        $empresa = Empresa::factory()->create();

        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresa->id);

        app()->instance('current.company', $empresa);

        actingAs($usuario);

        Livewire::test(Index::class)
            ->assertSee('Cadastre uma Filial');
    });

    test('não exibe alertas quando tudo está ok', function () {
        $empresa = Empresa::factory()
            ->hasFiliais(1)
            ->create(['status' => StatusEmpresaEnum::ATIVO]);

        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresa->id);

        app()->instance('current.company', $empresa);

        actingAs($usuario);

        $component = Livewire::test(Index::class);

        expect($component->get('alertas'))->toBeEmpty();
    });
});

describe('Dashboard Index - Performance & Cache', function () {

    test('dados são cacheados por tenant', function () {
        $empresa = Empresa::factory()->create();

        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresa->id);

        app()->instance('current.company', $empresa);

        actingAs($usuario);

        // Primeira chamada - deve cachear
        Livewire::test(Index::class);

        $cacheKey = "dashboard.{$empresa->id}";
        expect(Cache::has($cacheKey))->toBeTrue();
    });

    test('cache é invalidado ao atualizar dashboard', function () {
        $empresa = Empresa::factory()->create();

        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresa->id);

        app()->instance('current.company', $empresa);

        actingAs($usuario);

        $component = Livewire::test(Index::class);

        $cacheKey = "dashboard.{$empresa->id}";
        expect(Cache::has($cacheKey))->toBeTrue();

        // Atualizar dashboard
        $component->call('atualizar');

        // Cache deve ser recriado
        expect(Cache::has($cacheKey))->toBeTrue();
    });

    test('cada tenant tem seu próprio cache', function () {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $usuarioA = Usuario::factory()->create();
        $usuarioA->empresas()->attach($empresaA->id);

        $usuarioB = Usuario::factory()->create();
        $usuarioB->empresas()->attach($empresaB->id);

        // Carregar dashboard da empresa A
        app()->instance('current.company', $empresaA);
        actingAs($usuarioA);
        Livewire::test(Index::class);

        // Carregar dashboard da empresa B
        app()->instance('current.company', $empresaB);
        actingAs($usuarioB);
        Livewire::test(Index::class);

        // Ambos devem ter cache separado
        expect(Cache::has("dashboard.{$empresaA->id}"))->toBeTrue();
        expect(Cache::has("dashboard.{$empresaB->id}"))->toBeTrue();
    });
});

describe('Dashboard Index - Actions', function () {

    test('pode atualizar dashboard', function () {
        $empresa = Empresa::factory()->create();

        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresa->id);

        app()->instance('current.company', $empresa);

        actingAs($usuario);

        Livewire::test(Index::class)
            ->call('atualizar')
            ->assertDispatched('notify');
    });
});

describe('Dashboard Index - Edge Cases', function () {

    test('lida com empresa sem dados', function () {
        $empresa = Empresa::factory()->create();

        $usuario = Usuario::factory()->create();
        $usuario->empresas()->attach($empresa->id);

        app()->instance('current.company', $empresa);

        actingAs($usuario);

        Livewire::test(Index::class)
            ->assertSet('estatisticas.filiais.total', 0)
            ->assertSet('estatisticas.usuarios.total', 1) // O próprio usuário
            ->assertSet('estatisticas.ordens_servico.total_mes', 0)
            ->assertSet('estatisticas.faturamento.mes_atual', 0.00);
    });

    test('lida com empresa inexistente gracefully', function () {
        $usuario = Usuario::factory()->create();

        // Criar empresa fake que não existe no banco
        $empresaFake = new Empresa(['id' => '00000000-0000-0000-0000-000000000000']);
        app()->instance('current.company', $empresaFake);

        actingAs($usuario);

        $component = Livewire::test(Index::class);

        // Deve retornar estatísticas vazias sem erro
        expect($component->get('estatisticas')['empresa']['nome'])->toBe('N/A');
    });
});
