<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ordem específica para respeitar dependências
        $this->call([
            PermissoesSeeder::class,
            PapeisSeeder::class,
            EmpresaSeeder::class,
            UsuarioSeeder::class,
        ]);

        $this->command->info('Base de dados populada com sucesso!');
        $this->command->line('');
        $this->command->info('Credenciais de acesso:');
        $this->command->line('Super Admin: admin@sistema.com / admin123');
        $this->command->line('TechSol Admin: joao.silva@techsol.com.br / techsol123');
        $this->command->line('ABC Admin: ana.costa@abcservicos.com.br / abc123');
        $this->command->line('Gestor Filial: maria.santos@techsol.com.br / filial123');
        $this->command->line('Operador: carlos.oliveira@techsol.com.br / operador123');
    }
}
