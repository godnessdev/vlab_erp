<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Papel;
use App\Models\Permissao;

class PapeisSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar papéis existentes
        Papel::truncate();

        // Criar papéis do sistema
        $this->criarPapeisAdministrativo();
        $this->criarPapeisOperacionais();
        $this->criarPapeisConsulta();
    }

    private function criarPapeisAdministrativo(): void
    {
        // Super Administrador - Acesso total ao sistema
        $superAdmin = Papel::create([
            'nome' => 'super_admin',
            'descricao' => 'Super Administrador - Acesso total ao sistema',
            'nivel' => 1,
            'ativo' => true,
        ]);

        // Anexar todas as permissões ao super admin
        $todasPermissoes = Permissao::all()->pluck('id');
        $superAdmin->permissoes()->attach($todasPermissoes);

        // Administrador da Empresa - Gestão completa da empresa
        $adminEmpresa = Papel::create([
            'nome' => 'admin_empresa',
            'descricao' => 'Administrador da Empresa - Gestão completa da empresa',
            'nivel' => 2,
            'ativo' => true,
        ]);

        // Permissões para admin empresa
        $permissoesAdminEmpresa = Permissao::whereIn('nome', [
            'empresa.visualizar', 'empresa.editar', 'empresa.ativar', 'empresa.configurar',
            'filial.criar', 'filial.visualizar', 'filial.editar', 'filial.ativar', 'filial.configurar_fiscal', 'filial.listar',
            'usuario.criar', 'usuario.visualizar', 'usuario.editar', 'usuario.gerenciar_acesso', 'usuario.gerenciar_papeis', 'usuario.resetar_senha', 'usuario.listar',
            'sistema.dashboard', 'sistema.relatorios',
        ])->pluck('id');

        $adminEmpresa->permissoes()->attach($permissoesAdminEmpresa);
    }

    private function criarPapeisOperacionais(): void
    {
        // Gestor de Filial - Gestão da filial
        $gestorFilial = Papel::create([
            'nome' => 'gestor_filial',
            'descricao' => 'Gestor de Filial - Gestão operacional da filial',
            'nivel' => 3,
            'ativo' => true,
        ]);

        $permissoesGestorFilial = Permissao::whereIn('nome', [
            'empresa.visualizar',
            'filial.visualizar', 'filial.editar', 'filial.listar',
            'usuario.criar', 'usuario.visualizar', 'usuario.editar', 'usuario.listar',
            'sistema.dashboard', 'sistema.relatorios',
        ])->pluck('id');

        $gestorFilial->permissoes()->attach($permissoesGestorFilial);

        // Operador - Operações básicas
        $operador = Papel::create([
            'nome' => 'operador',
            'descricao' => 'Operador - Operações básicas do sistema',
            'nivel' => 4,
            'ativo' => true,
        ]);

        $permissoesOperador = Permissao::whereIn('nome', [
            'empresa.visualizar',
            'filial.visualizar', 'filial.listar',
            'usuario.visualizar',
            'sistema.dashboard',
        ])->pluck('id');

        $operador->permissoes()->attach($permissoesOperador);

        // Supervisor - Supervisão e relatórios
        $supervisor = Papel::create([
            'nome' => 'supervisor',
            'descricao' => 'Supervisor - Supervisão e geração de relatórios',
            'nivel' => 3,
            'ativo' => true,
        ]);

        $permissoesSupervisor = Permissao::whereIn('nome', [
            'empresa.visualizar',
            'filial.visualizar', 'filial.listar',
            'usuario.visualizar', 'usuario.listar',
            'sistema.dashboard', 'sistema.relatorios',
        ])->pluck('id');

        $supervisor->permissoes()->attach($permissoesSupervisor);
    }

    private function criarPapeisConsulta(): void
    {
        // Consultor - Apenas visualização
        $consultor = Papel::create([
            'nome' => 'consultor',
            'descricao' => 'Consultor - Acesso somente leitura',
            'nivel' => 5,
            'ativo' => true,
        ]);

        $permissoesConsultor = Permissao::whereIn('nome', [
            'empresa.visualizar',
            'filial.visualizar', 'filial.listar',
            'sistema.dashboard',
        ])->pluck('id');

        $consultor->permissoes()->attach($permissoesConsultor);

        // Auditoria - Visualização de logs e auditoria
        $auditor = Papel::create([
            'nome' => 'auditor',
            'descricao' => 'Auditor - Acesso a logs e auditoria',
            'nivel' => 3,
            'ativo' => true,
        ]);

        $permissoesAuditor = Permissao::whereIn('nome', [
            'empresa.visualizar',
            'filial.visualizar', 'filial.listar',
            'usuario.visualizar', 'usuario.listar',
            'sistema.dashboard', 'sistema.auditoria',
        ])->pluck('id');

        $auditor->permissoes()->attach($permissoesAuditor);
    }
}
