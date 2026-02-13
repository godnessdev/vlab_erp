<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;

class PermissoesSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar permissões existentes
        Permissao::truncate();

        // Permissões do módulo Empresa
        $this->criarPermissoesEmpresa();
        
        // Permissões do módulo Filial
        $this->criarPermissoesFilial();
        
        // Permissões do módulo Usuário
        $this->criarPermissoesUsuario();
        
        // Permissões do módulo Sistema
        $this->criarPermissoesSistema();
    }

    private function criarPermissoesEmpresa(): void
    {
        $permissoes = [
            ['nome' => 'empresa.criar', 'descricao' => 'Criar nova empresa', 'acao' => 'criar', 'recurso' => 'dados_basicos'],
            ['nome' => 'empresa.visualizar', 'descricao' => 'Visualizar dados da empresa', 'acao' => 'visualizar', 'recurso' => 'dados_basicos'],
            ['nome' => 'empresa.editar', 'descricao' => 'Editar dados da empresa', 'acao' => 'editar', 'recurso' => 'dados_basicos'],
            ['nome' => 'empresa.excluir', 'descricao' => 'Excluir empresa', 'acao' => 'excluir', 'recurso' => 'dados_basicos'],
            ['nome' => 'empresa.ativar', 'descricao' => 'Ativar/Inativar empresa', 'acao' => 'gerenciar', 'recurso' => 'status'],
            ['nome' => 'empresa.configurar', 'descricao' => 'Configurar parâmetros operacionais', 'acao' => 'gerenciar', 'recurso' => 'parametros'],
            ['nome' => 'empresa.listar', 'descricao' => 'Listar todas as empresas', 'acao' => 'listar', 'recurso' => 'dados_basicos'],
        ];

        foreach ($permissoes as $permissao) {
            Permissao::create([
                'nome' => $permissao['nome'],
                'descricao' => $permissao['descricao'],
                'modulo' => 'empresa',
                'acao' => $permissao['acao'],
                'recurso' => $permissao['recurso'],
            ]);
        }
    }

    private function criarPermissoesFilial(): void
    {
        $permissoes = [
            ['nome' => 'filial.criar', 'descricao' => 'Criar nova filial', 'acao' => 'criar', 'recurso' => 'dados_basicos'],
            ['nome' => 'filial.visualizar', 'descricao' => 'Visualizar dados da filial', 'acao' => 'visualizar', 'recurso' => 'dados_basicos'],
            ['nome' => 'filial.editar', 'descricao' => 'Editar dados da filial', 'acao' => 'editar', 'recurso' => 'dados_basicos'],
            ['nome' => 'filial.excluir', 'descricao' => 'Excluir filial', 'acao' => 'excluir', 'recurso' => 'dados_basicos'],
            ['nome' => 'filial.ativar', 'descricao' => 'Ativar/Inativar filial', 'acao' => 'gerenciar', 'recurso' => 'status'],
            ['nome' => 'filial.configurar_fiscal', 'descricao' => 'Configurar dados fiscais', 'acao' => 'gerenciar', 'recurso' => 'configuracao_fiscal'],
            ['nome' => 'filial.listar', 'descricao' => 'Listar filiais da empresa', 'acao' => 'listar', 'recurso' => 'dados_basicos'],
        ];

        foreach ($permissoes as $permissao) {
            Permissao::create([
                'nome' => $permissao['nome'],
                'descricao' => $permissao['descricao'],
                'modulo' => 'filial',
                'acao' => $permissao['acao'],
                'recurso' => $permissao['recurso'],
            ]);
        }
    }

    private function criarPermissoesUsuario(): void
    {
        $permissoes = [
            ['nome' => 'usuario.criar', 'descricao' => 'Criar novo usuário', 'acao' => 'criar', 'recurso' => 'dados_basicos'],
            ['nome' => 'usuario.visualizar', 'descricao' => 'Visualizar dados do usuário', 'acao' => 'visualizar', 'recurso' => 'dados_basicos'],
            ['nome' => 'usuario.editar', 'descricao' => 'Editar dados do usuário', 'acao' => 'editar', 'recurso' => 'dados_basicos'],
            ['nome' => 'usuario.excluir', 'descricao' => 'Excluir usuário', 'acao' => 'excluir', 'recurso' => 'dados_basicos'],
            ['nome' => 'usuario.gerenciar_acesso', 'descricao' => 'Ativar/Bloquear usuário', 'acao' => 'gerenciar', 'recurso' => 'acesso'],
            ['nome' => 'usuario.gerenciar_papeis', 'descricao' => 'Vincular/Desvincular papéis', 'acao' => 'gerenciar', 'recurso' => 'papeis'],
            ['nome' => 'usuario.resetar_senha', 'descricao' => 'Resetar senha do usuário', 'acao' => 'gerenciar', 'recurso' => 'senha'],
            ['nome' => 'usuario.listar', 'descricao' => 'Listar usuários', 'acao' => 'listar', 'recurso' => 'dados_basicos'],
        ];

        foreach ($permissoes as $permissao) {
            Permissao::create([
                'nome' => $permissao['nome'],
                'descricao' => $permissao['descricao'],
                'modulo' => 'usuario',
                'acao' => $permissao['acao'],
                'recurso' => $permissao['recurso'],
            ]);
        }
    }

    private function criarPermissoesSistema(): void
    {
        $permissoes = [
            ['nome' => 'sistema.dashboard', 'descricao' => 'Acessar dashboard', 'acao' => 'visualizar', 'recurso' => 'dashboard'],
            ['nome' => 'sistema.relatorios', 'descricao' => 'Gerar relatórios', 'acao' => 'gerar', 'recurso' => 'relatorios'],
            ['nome' => 'sistema.configurar', 'descricao' => 'Configurar sistema', 'acao' => 'gerenciar', 'recurso' => 'configuracoes'],
            ['nome' => 'sistema.auditoria', 'descricao' => 'Visualizar logs de auditoria', 'acao' => 'visualizar', 'recurso' => 'auditoria'],
            ['nome' => 'sistema.backup', 'descricao' => 'Gerenciar backups', 'acao' => 'gerenciar', 'recurso' => 'backup'],
        ];

        foreach ($permissoes as $permissao) {
            Permissao::create([
                'nome' => $permissao['nome'],
                'descricao' => $permissao['descricao'],
                'modulo' => 'sistema',
                'acao' => $permissao['acao'],
                'recurso' => $permissao['recurso'],
            ]);
        }
    }
}
