<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Criar enum apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("DO $$ BEGIN
                CREATE TYPE status_item_ordem AS ENUM ('PENDENTE', 'EM_EXECUCAO', 'CONCLUIDO', 'CANCELADO');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");
        }

        Schema::create('itens_ordem_servico', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ordem_servico_id')->comment('Referência à ordem');
            $table->uuid('servico_id')->comment('Serviço do catálogo');
            $table->integer('sequencia')->comment('Ordem dos itens');
            $table->string('descricao_personalizada', 500)->nullable()->comment('Descrição específica');
            $table->decimal('quantidade', 10, 2)->comment('Quantidade planejada');
            $table->decimal('quantidade_executada', 10, 2)->default(0)->comment('Quantidade executada');
            $table->decimal('preco_unitario', 10, 2)->comment('Preço unitário acordado');
            $table->decimal('desconto_percentual', 5, 2)->default(0)->comment('Desconto em %');
            $table->decimal('desconto_valor', 10, 2)->default(0)->comment('Desconto em valor');
            $table->decimal('subtotal', 10, 2)->comment('Subtotal calculado');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico')->onDelete('cascade');
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('cascade');

            // Índices conforme especificação
            $table->index('ordem_servico_id', 'idx_item_ordem_id');
            $table->index('servico_id', 'idx_item_servico_id');
            $table->index(['ordem_servico_id', 'sequencia'], 'idx_item_sequencia');
        });

        // Adicionar coluna enum apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE itens_ordem_servico ADD COLUMN status status_item_ordem DEFAULT \'PENDENTE\'');
            
            // Criar índice para a coluna enum
            DB::statement('CREATE INDEX idx_item_status ON itens_ordem_servico (status)');
        } else {
            // Para SQLite/outros bancos, usar string
            Schema::table('itens_ordem_servico', function (Blueprint $table) {
                $table->string('status', 20)->default('PENDENTE');
                $table->index('status', 'idx_item_status');
            });
        }

        // Comentários para documentação apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("COMMENT ON TABLE itens_ordem_servico IS 'Itens/serviços que compõem a ordem'");
            DB::statement("COMMENT ON COLUMN itens_ordem_servico.sequencia IS 'Define a ordem de execução dos itens'");
            DB::statement("COMMENT ON COLUMN itens_ordem_servico.subtotal IS 'Calculado: quantidade * preco_unitario - descontos'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_ordem_servico');
        
        // Remover enum apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS status_item_ordem');
        }
    }
};
