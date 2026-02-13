<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Papel;
use App\Models\UsuarioEmpresaPapel;
use Carbon\Carbon;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Criar super administrador
        $this->criarSuperAdministrador();
        
        // Criar usuários para empresas de exemplo
        $this->criarUsuariosEmpresas();
    }

    private function criarSuperAdministrador(): void
    {
        // Criar pessoa para o super admin
        $pessoa = \App\Domain\Identidade\Models\Pessoa::create([
            'nome_razao_social' => 'Super Administrador',
            'tipo' => \App\Domain\Identidade\Enums\TipoPessoa::FISICA,
        ]);

        // Criar documento CPF
        \App\Domain\Identidade\Models\Documento::create([
            'pessoa_id' => $pessoa->id,
            'tipo' => 'CPF',
            'valor' => '123.456.789-00',
            'valido' => true,
        ]);

        // Criar contatos
        \App\Domain\Identidade\Models\Contato::create([
            'pessoa_id' => $pessoa->id,
            'tipo' => 'EMAIL',
            'valor' => 'admin@sistema.com',
            'principal' => true,
            'verificado' => true,
        ]);

        \App\Domain\Identidade\Models\Contato::create([
            'pessoa_id' => $pessoa->id,
            'tipo' => 'CELULAR',
            'valor' => '(11) 99999-9999',
            'principal' => false,
            'verificado' => true,
        ]);

        $superAdmin = Usuario::create([
            'pessoa_id' => $pessoa->id,
            'email' => 'admin@sistema.com',
            'password' => Hash::make('admin123'),
            'ultimo_login' => now(),
        ]);

        // Vincular ao papel de super admin em todas as empresas
        // Garante que o papel super_admin existe
        $papelSuperAdmin = Papel::firstOrCreate(
            ['nome' => 'super_admin'],
            [
                'descricao' => 'Super Administrador - Acesso total ao sistema',
                'nivel' => 1,
                'ativo' => true,
            ]
        );

        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            UsuarioEmpresaPapel::create([
                'usuario_id' => $superAdmin->id,
                'empresa_id' => $empresa->id,
                'papel_id' => $papelSuperAdmin->id,
                'data_inicio' => Carbon::now()->subMonth(), // Começou há 1 mês
                'data_fim' => null, // Sem data fim
                'status' => 'ATIVO',
            ]);
        }
    }

    private function criarUsuariosEmpresas(): void
    {
        $empresaTechSol = Empresa::where('cnpj', '12.345.678/0001-95')->first();
        $empresaABC = Empresa::where('cnpj', '98.765.432/0001-10')->first();

        if ($empresaTechSol) {
            $this->criarUsuariosTechSol($empresaTechSol);
        }

        if ($empresaABC) {
            $this->criarUsuariosABC($empresaABC);
        }
    }

    private function criarUsuariosTechSol(Empresa $empresa): void
    {
        // Administrador da TechSol
        $pessoaAdmin = \App\Domain\Identidade\Models\Pessoa::create([
            'nome_razao_social' => 'João Silva',
            'tipo' => \App\Domain\Identidade\Enums\TipoPessoa::FISICA,
        ]);

        $adminTechSol = Usuario::create([
            'pessoa_id' => $pessoaAdmin->id,
            'email' => 'joao.silva@techsol.com.br',
            'password' => Hash::make('techsol123'),
            'ultimo_login' => now()->subDays(2),
        ]);

        $papelAdmin = Papel::where('nome', 'admin_empresa')->first();
        UsuarioEmpresaPapel::create([
            'usuario_id' => $adminTechSol->id,
            'empresa_id' => $empresa->id,
            'papel_id' => $papelAdmin->id,
            'data_inicio' => Carbon::now()->subWeeks(3),
            'data_fim' => null,
            'status' => 'ATIVO',
        ]);

        // Gestor de Filial
        $pessoaGestor = \App\Domain\Identidade\Models\Pessoa::create([
            'nome_razao_social' => 'Maria Santos',
            'tipo' => \App\Domain\Identidade\Enums\TipoPessoa::FISICA,
        ]);

        $gestorFilial = Usuario::create([
            'pessoa_id' => $pessoaGestor->id,
            'email' => 'maria.santos@techsol.com.br',
            'password' => Hash::make('filial123'),
            'ultimo_login' => now()->subHours(5),
        ]);

        $papelGestor = Papel::where('nome', 'gestor_filial')->first();
        UsuarioEmpresaPapel::create([
            'usuario_id' => $gestorFilial->id,
            'empresa_id' => $empresa->id,
            'papel_id' => $papelGestor->id,
            'data_inicio' => Carbon::now()->subWeeks(2),
            'data_fim' => null,
            'status' => 'ATIVO',
        ]);

        // Operador
        $pessoaOperador = \App\Domain\Identidade\Models\Pessoa::create([
            'nome_razao_social' => 'Carlos Oliveira',
            'tipo' => \App\Domain\Identidade\Enums\TipoPessoa::FISICA,
        ]);

        $operador = Usuario::create([
            'pessoa_id' => $pessoaOperador->id,
            'email' => 'carlos.oliveira@techsol.com.br',
            'password' => Hash::make('operador123'),
            'ultimo_login' => now()->subMinutes(30),
        ]);

        $papelOperador = Papel::where('nome', 'operador')->first();
        UsuarioEmpresaPapel::create([
            'usuario_id' => $operador->id,
            'empresa_id' => $empresa->id,
            'papel_id' => $papelOperador->id,
            'data_inicio' => Carbon::now()->subWeek(),
            'data_fim' => null,
            'status' => 'ATIVO',
        ]);
    }

    private function criarUsuariosABC(Empresa $empresa): void
    {
        // Administrador da ABC
        $pessoaAdmin = \App\Domain\Identidade\Models\Pessoa::create([
            'nome_razao_social' => 'Ana Costa',
            'tipo' => \App\Domain\Identidade\Enums\TipoPessoa::FISICA,
        ]);

        $adminABC = Usuario::create([
            'pessoa_id' => $pessoaAdmin->id,
            'email' => 'ana.costa@abcservicos.com.br',
            'password' => Hash::make('abc123'),
            'ultimo_login' => now()->subDays(1),
        ]);

        $papelAdmin = Papel::where('nome', 'admin_empresa')->first();
        UsuarioEmpresaPapel::create([
            'usuario_id' => $adminABC->id,
            'empresa_id' => $empresa->id,
            'papel_id' => $papelAdmin->id,
            'data_inicio' => Carbon::now()->subWeeks(4),
            'data_fim' => null,
            'status' => 'ATIVO',
        ]);

        // Supervisor
        $pessoaSupervisor = \App\Domain\Identidade\Models\Pessoa::create([
            'nome_razao_social' => 'Roberto Lima',
            'tipo' => \App\Domain\Identidade\Enums\TipoPessoa::FISICA,
        ]);

        $supervisor = Usuario::create([
            'pessoa_id' => $pessoaSupervisor->id,
            'email' => 'roberto.lima@abcservicos.com.br',
            'password' => Hash::make('supervisor123'),
            'ultimo_login' => now()->subHours(3),
        ]);

        $papelSupervisor = Papel::where('nome', 'supervisor')->first();
        UsuarioEmpresaPapel::create([
            'usuario_id' => $supervisor->id,
            'empresa_id' => $empresa->id,
            'papel_id' => $papelSupervisor->id,
            'data_inicio' => Carbon::now()->subWeeks(3),
            'data_fim' => null,
            'status' => 'ATIVO',
        ]);

        // Consultor (usuário temporário com data fim)
        $pessoaConsultor = \App\Domain\Identidade\Models\Pessoa::create([
            'nome_razao_social' => 'Patricia Fernandes',
            'tipo' => \App\Domain\Identidade\Enums\TipoPessoa::FISICA,
        ]);

        $consultor = Usuario::create([
            'pessoa_id' => $pessoaConsultor->id,
            'email' => 'patricia.fernandes@consultor.com',
            'password' => Hash::make('consultor123'),
            'ultimo_login' => now()->subWeeks(1),
        ]);

        $papelConsultor = Papel::where('nome', 'consultor')->first();
        UsuarioEmpresaPapel::create([
            'usuario_id' => $consultor->id,
            'empresa_id' => $empresa->id,
            'papel_id' => $papelConsultor->id,
            'data_inicio' => Carbon::now()->subWeeks(2),
            'data_fim' => Carbon::now()->addMonth(), // Acesso temporário
            'status' => 'ATIVO',
        ]);
    }
}
