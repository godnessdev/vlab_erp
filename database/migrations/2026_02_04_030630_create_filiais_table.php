<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('filiais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->string('codigo', 10)->comment('Código único da filial dentro da empresa');
            $table->string('nome', 255);
            $table->enum('tipo', ['MATRIZ', 'FILIAL']);
            $table->string('cnpj', 18)->unique();
            $table->string('inscricao_estadual', 50)->nullable();
            $table->string('inscricao_municipal', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->boolean('ativo')->default(true);
            $table->jsonb('endereco')->comment('Dados de endereço da filial');
            $table->jsonb('configuracao_fiscal')->comment('Configurações fiscais específicas da filial');
            $table->timestamps();

            // Foreign keys
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            // Índices
            $table->index(['empresa_id', 'codigo'], 'idx_filial_empresa_codigo');
            $table->index(['empresa_id', 'ativo'], 'idx_filial_empresa_ativo');
            $table->index('cnpj');
            $table->index('tipo');

            // Constraint para garantir apenas uma matriz por empresa
            $table->unique(['empresa_id', 'tipo'], 'uk_empresa_matriz');

            $table->comment('Filiais e matriz da empresa');
        });

        // Habilitar RLS apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Habilitar RLS
            DB::statement('ALTER TABLE filiais ENABLE ROW LEVEL SECURITY');

            // Política RLS para isolamento multitenant
            DB::statement("
                CREATE POLICY tenant_isolation_filial ON filiais
                FOR ALL TO app_role
                USING (empresa_id = current_setting('app.tenant_id', true)::uuid)
            ");

            // Comentários
            DB::statement("COMMENT ON TABLE filiais IS 'Filiais e matriz da empresa'");
            DB::statement("COMMENT ON COLUMN filiais.codigo IS 'Código único da filial dentro da empresa'");
            DB::statement("COMMENT ON COLUMN filiais.endereco IS 'Dados de endereço da filial'");
            DB::statement("COMMENT ON COLUMN filiais.configuracao_fiscal IS 'Configurações fiscais específicas da filial'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filiais');
    }
};
