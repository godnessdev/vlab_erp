<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametros_operacionais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id')->notNullable();
            $table->string('chave', 100)->notNullable();
            $table->jsonb('valor')->notNullable();
            $table->timestamp('data_atualizacao')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
            
            // Chave estrangeira
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            
            // Constraint único - uma chave por empresa
            $table->unique(['empresa_id', 'chave'], 'uk_parametro_empresa_chave');
            
            // Índices conforme especificação
            $table->index('empresa_id', 'idx_parametro_empresa_id');
            $table->index('chave', 'idx_parametro_chave');
        });

        // Índice GIN para JSONB (somente PostgreSQL)
        if (config('database.default') === 'pgsql') {
            DB::statement('CREATE INDEX gin_parametro_valor ON parametros_operacionais USING GIN (valor)');
        } else {
            // Para SQLite, criar índice simples
            DB::statement('CREATE INDEX gin_parametro_valor ON parametros_operacionais (valor)');
        }

        // Habilitar RLS (somente PostgreSQL)
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE parametros_operacionais ENABLE ROW LEVEL SECURITY');
            
            // Política RLS
            DB::statement("
                CREATE POLICY tenant_isolation_parametro ON parametros_operacionais
                FOR ALL TO app_role
                USING (empresa_id = current_setting('app.tenant_id', true)::uuid)
            ");
        }

        // Comentários
        DB::statement("COMMENT ON TABLE parametros_operacionais IS 'Configurações extensíveis via EAV por empresa'");
        DB::statement("COMMENT ON COLUMN parametros_operacionais.valor IS 'Valor flexível em JSONB para diferentes tipos de configuração'");
    }

    public function down(): void
    {
        DB::statement('DROP POLICY IF EXISTS tenant_isolation_parametro ON parametros_operacionais');
        Schema::dropIfExists('parametros_operacionais');
    }
};
