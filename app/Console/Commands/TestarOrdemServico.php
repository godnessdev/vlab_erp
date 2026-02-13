<?php

namespace App\Console\Commands;

use App\Domain\OrdemServico\Enums\StatusOrdemServico;
use App\Domain\OrdemServico\Enums\PrioridadeOrdem;
use App\Domain\OrdemServico\Enums\StatusItemOrdem;
use App\Domain\OrdemServico\Enums\TipoEventoHistorico;
use App\Domain\OrdemServico\Models\OrdemServico;
use App\Domain\OrdemServico\Models\ItemOrdemServico;
use App\Domain\OrdemServico\Models\ApontamentoExecucao;
use App\Domain\OrdemServico\Models\HistoricoOrdem;
use App\Domain\OrdemServico\Services\OrdemServicoService;
use App\Domain\OrdemServico\Services\ApontamentoService;
use Illuminate\Console\Command;

class TestarOrdemServico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ordem-servico:testar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar funcionalidades do módulo Ordem de Serviço';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testando módulo Ordem de Serviço...');
        $this->newLine();

        // Teste 1: Verificar se os models podem ser instanciados
        $this->info('1. Testando Models:');
        $this->testModels();
        $this->newLine();

        // Teste 2: Verificar enums
        $this->info('2. Testando Enums:');
        $this->testEnums();
        $this->newLine();

        // Teste 3: Verificar tabelas no banco
        $this->info('3. Testando Tabelas no Banco:');
        $this->testTables();
        $this->newLine();

        // Teste 4: Verificar Services
        $this->info('4. Testando Services:');
        $this->testServices();
        $this->newLine();

        $this->info('✅ Testes concluídos!');
    }

    private function testModels()
    {
        try {
            $ordem = new OrdemServico();
            $this->line("   ✓ OrdemServico - Tabela: {$ordem->getTable()}");

            $item = new ItemOrdemServico();
            $this->line("   ✓ ItemOrdemServico - Tabela: {$item->getTable()}");

            $apontamento = new ApontamentoExecucao();
            $this->line("   ✓ ApontamentoExecucao - Tabela: {$apontamento->getTable()}");

            $historico = new HistoricoOrdem();
            $this->line("   ✓ HistoricoOrdem - Tabela: {$historico->getTable()}");
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao testar models: " . $e->getMessage());
        }
    }

    private function testEnums()
    {
        try {
            // Status Ordem Serviço
            $status = StatusOrdemServico::ABERTA;
            $this->line("   ✓ StatusOrdemServico::ABERTA - {$status->value} ({$status->getLabel()})");

            $prioridade = PrioridadeOrdem::ALTA;
            $this->line("   ✓ PrioridadeOrdem::ALTA - {$prioridade->value} ({$prioridade->getLabel()})");

            $statusItem = StatusItemOrdem::PENDENTE;
            $this->line("   ✓ StatusItemOrdem::PENDENTE - {$statusItem->value} ({$statusItem->getLabel()})");

            $tipoEvento = TipoEventoHistorico::CRIACAO;
            $this->line("   ✓ TipoEventoHistorico::CRIACAO - {$tipoEvento->value} ({$tipoEvento->getLabel()})");

            // Testar transições
            $transicao = StatusOrdemServico::ABERTA->podeTransicionarPara(StatusOrdemServico::EM_ANDAMENTO);
            $this->line("   ✓ Transição ABERTA -> EM_ANDAMENTO: " . ($transicao ? 'Permitido' : 'Negado'));
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao testar enums: " . $e->getMessage());
        }
    }

    private function testTables()
    {
        try {
            $tables = [
                'ordens_servico',
                'itens_ordem_servico',
                'apontamentos_execucao',
                'historico_ordem'
            ];

            foreach ($tables as $table) {
                $exists = \DB::getSchemaBuilder()->hasTable($table);
                if ($exists) {
                    $count = \DB::table($table)->count();
                    $this->line("   ✓ Tabela '{$table}' existe - {$count} registros");
                } else {
                    $this->error("   ❌ Tabela '{$table}' não existe");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao testar tabelas: " . $e->getMessage());
        }
    }

    private function testServices()
    {
        try {
            $ordemService = new OrdemServicoService();
            $this->line("   ✓ OrdemServicoService instanciado");

            $apontamentoService = new ApontamentoService();
            $this->line("   ✓ ApontamentoService instanciado");

            // Testar método de busca sem parâmetros
            $result = $ordemService->buscar();
            $this->line("   ✓ Busca de ordens - Total: {$result->total()} registros");
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao testar services: " . $e->getMessage());
        }
    }
}
