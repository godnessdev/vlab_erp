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
                CREATE TYPE tipo_evento_historico AS ENUM ('CRIACAO', 'ALTERACAO', 'STATUS_CHANGE', 'APROVACAO', 'APONTAMENTO', 'ANEXO');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");
        }

        Schema::create('historico_ordem', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ordem_servico_id')->comment('Referência à ordem');
            $table->timestamp('data_evento')->comment('Data/hora do evento');
            $table->string('status_anterior', 50)->nullable()->comment('Status anterior');
            $table->string('status_novo', 50)->nullable()->comment('Novo status');
            $table->uuid('usuario_id')->comment('Usuário responsável');
            $table->json('campos_alterados')->nullable()->comment('Campos que foram alterados');
            $table->json('valores_anteriores')->nullable()->comment('Valores antes da alteração');
            $table->json('valores_novos')->nullable()->comment('Novos valores');
            $table->text('observacoes')->nullable()->comment('Observações do evento');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');

            // Índices conforme especificação
            $table->index('ordem_servico_id', 'idx_historico_ordem_id');
            $table->index('data_evento', 'idx_historico_data_evento');
            $table->index('usuario_id', 'idx_historico_usuario_id');
        });

        // Adicionar coluna enum apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE historico_ordem ADD COLUMN tipo_evento tipo_evento_historico NOT NULL');
            
            // Criar índices para as colunas enum e JSON (usando jsonb para GIN)
            DB::statement('CREATE INDEX idx_historico_tipo_evento ON historico_ordem (tipo_evento)');
            DB::statement('CREATE INDEX gin_historico_campos ON historico_ordem USING GIN ((campos_alterados::jsonb))');
        } else {
            // Para SQLite/outros bancos, usar string
            Schema::table('historico_ordem', function (Blueprint $table) {
                $table->string('tipo_evento', 20)->after('data_evento');
                $table->index('tipo_evento', 'idx_historico_tipo_evento');
            });
        }

        // Comentários para documentação apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("COMMENT ON TABLE historico_ordem IS 'Histórico completo de mudanças para auditoria'");
            DB::statement("COMMENT ON COLUMN historico_ordem.campos_alterados IS 'JSON com lista dos campos alterados'");
            DB::statement("COMMENT ON COLUMN historico_ordem.valores_anteriores IS 'JSON com valores antes da alteração'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_ordem');
        
        // Remover enum apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS tipo_evento_historico');
        }
    }
};
