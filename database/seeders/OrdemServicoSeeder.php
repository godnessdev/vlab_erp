<?php

namespace Database\Seeders;

use App\Domain\OrdemServico\Enums\PrioridadeOrdem;
use App\Domain\OrdemServico\Enums\StatusOrdemServico;
use App\Domain\OrdemServico\Enums\TipoEventoHistorico;
use App\Domain\OrdemServico\Models\HistoricoOrdem;
use App\Domain\OrdemServico\Models\OrdemServico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdemServicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Testando criação direta de Ordem de Serviço...');

        // Buscar registros existentes ou usar UUIDs fictícios para teste
        $empresaId = DB::table('empresas')->value('id') ?? '550e8400-e29b-41d4-a716-446655440000';
        $usuarioId = DB::table('usuarios')->value('id') ?? '550e8400-e29b-41d4-a716-446655440001';

        // Criar ordem de serviço de teste
        try {
            $ordem = OrdemServico::create([
                'empresa_id' => $empresaId,
                'cliente_id' => $usuarioId, // Usando usuário como cliente para teste
                'titulo' => 'OS de Teste - Desenvolvimento ERP',
                'descricao' => 'Ordem de serviço criada para teste do módulo.',
                'data_abertura' => now(),
                'data_prevista_inicio' => now()->addDays(1),
                'data_prevista_conclusao' => now()->addDays(30),
                'status' => StatusOrdemServico::ABERTA,
                'prioridade' => PrioridadeOrdem::NORMAL,
                'valor_total_estimado' => 5000.00,
                'valor_total_executado' => 0,
                'observacoes' => 'Ordem criada automaticamente pelo seeder de teste.',
            ]);

            $this->command->info('✅ Ordem criada com sucesso:');
            $this->command->line("   📋 {$ordem->numero_ordem} - {$ordem->titulo}");
            $this->command->line("   🎯 Status: {$ordem->status->getLabel()}");
            $this->command->line("   ⚡ Prioridade: {$ordem->prioridade->getLabel()}");
            $this->command->line('   💰 Valor: R$ '.number_format($ordem->valor_total_estimado, 2, ',', '.'));

            // Criar um histórico de teste
            HistoricoOrdem::create([
                'ordem_servico_id' => $ordem->id,
                'usuario_id' => $usuarioId,
                'data_evento' => now(),
                'tipo_evento' => TipoEventoHistorico::CRIACAO,
                'descricao' => 'Ordem de serviço criada pelo seeder de teste',
                'valores_novos' => [
                    'titulo' => $ordem->titulo,
                    'status' => $ordem->status->value,
                ],
            ]);

            $this->command->info('📝 Histórico criado com sucesso!');

        } catch (Exception $e) {
            $this->command->error('❌ Erro ao criar ordem: '.$e->getMessage());
            $this->command->line("   IDs usados: Empresa={$empresaId}, Usuario={$usuarioId}");
        }
    }
}
