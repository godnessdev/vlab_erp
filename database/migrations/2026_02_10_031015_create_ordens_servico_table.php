<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Criar enums apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("DO $$ BEGIN
                CREATE TYPE status_ordem_servico AS ENUM ('ABERTA', 'EM_ANDAMENTO', 'PAUSADA', 'CONCLUIDA', 'FATURADA', 'CANCELADA');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");

            DB::statement("DO $$ BEGIN
                CREATE TYPE prioridade_ordem AS ENUM ('BAIXA', 'NORMAL', 'ALTA', 'CRITICA');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");
        }

        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id')->comment('Tenant isolamento');
            $table->uuid('cliente_id')->comment('Cliente (via Papel)');
            $table->string('numero_ordem', 20)->comment('Número sequencial por empresa');
            $table->string('titulo', 255)->comment('Título resumido da ordem');
            $table->text('descricao')->nullable()->comment('Descrição detalhada');
            $table->timestamp('data_abertura')->comment('Data/hora de abertura');
            $table->date('data_prevista_inicio')->nullable()->comment('Previsão de início');
            $table->date('data_prevista_conclusao')->nullable()->comment('Previsão de conclusão');
            $table->timestamp('data_inicio_real')->nullable()->comment('Início real da execução');
            $table->timestamp('data_conclusao_real')->nullable()->comment('Conclusão real');
            $table->decimal('valor_total_estimado', 10, 2)->default(0)->comment('Valor estimado total');
            $table->decimal('valor_total_executado', 10, 2)->default(0)->comment('Valor executado (calculado)');
            $table->text('observacoes')->nullable()->comment('Observações gerais');
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('papeis')->onDelete('cascade');

            // Índices conforme especificação
            $table->index('empresa_id', 'idx_ordem_empresa_id');
            $table->index('cliente_id', 'idx_ordem_cliente_id');
            $table->index('numero_ordem', 'idx_ordem_numero');
            $table->index('data_abertura', 'idx_ordem_data_abertura');

            // Constraint único
            $table->unique(['empresa_id', 'numero_ordem'], 'uk_ordem_empresa_numero');
        });

        // Adicionar colunas enum apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ordens_servico ADD COLUMN status status_ordem_servico DEFAULT \'ABERTA\'');
            DB::statement('ALTER TABLE ordens_servico ADD COLUMN prioridade prioridade_ordem DEFAULT \'NORMAL\'');
            
            // Criar índices para as colunas enum
            DB::statement('CREATE INDEX idx_ordem_status ON ordens_servico (status)');
            DB::statement('CREATE INDEX idx_ordem_prioridade ON ordens_servico (prioridade)');
        } else {
            // Para SQLite/outros bancos, usar string
            Schema::table('ordens_servico', function (Blueprint $table) {
                $table->string('status', 20)->default('ABERTA');
                $table->string('prioridade', 20)->default('NORMAL');
                $table->index('status', 'idx_ordem_status');
                $table->index('prioridade', 'idx_ordem_prioridade');
            });
        }

        // Comentários para documentação apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("COMMENT ON TABLE ordens_servico IS 'Ordens de serviço com controle de workflow e estados'");
            DB::statement("COMMENT ON COLUMN ordens_servico.empresa_id IS 'Referência ao tenant para isolamento'");
            DB::statement("COMMENT ON COLUMN ordens_servico.numero_ordem IS 'Número sequencial único por empresa'");
            DB::statement("COMMENT ON COLUMN ordens_servico.status IS 'Controle de estado via máquina de estados'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
        
        // Remover enums apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS prioridade_ordem');
            DB::statement('DROP TYPE IF EXISTS status_ordem_servico');
        }
    }
};
