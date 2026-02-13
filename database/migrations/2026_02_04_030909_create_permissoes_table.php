<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome', 100)->notNullable()->unique();
            $table->string('descricao', 255)->notNullable();
            $table->string('modulo', 50)->notNullable();
            $table->string('acao', 50)->notNullable();
            $table->string('recurso', 50)->notNullable();
            $table->timestamps();
            
            // Índices conforme especificação
            $table->index('modulo', 'idx_permissao_modulo');
            $table->index(['modulo', 'acao', 'recurso'], 'idx_permissao_map');
            
            // Constraint única composta
            $table->unique(['modulo', 'acao', 'recurso'], 'uk_permissao_mar');
        });

        // Comentários
        DB::statement("COMMENT ON TABLE permissoes IS 'Permissões granulares do sistema organizadas por módulo-ação-recurso'");
        DB::statement("COMMENT ON COLUMN permissoes.nome IS 'Nome único da permissão (ex: empresa.criar, filial.editar)'");
        DB::statement("COMMENT ON COLUMN permissoes.modulo IS 'Módulo do sistema (ex: empresa, filial, usuario)'");
        DB::statement("COMMENT ON COLUMN permissoes.acao IS 'Ação específica (ex: criar, editar, excluir, visualizar)'");
        DB::statement("COMMENT ON COLUMN permissoes.recurso IS 'Recurso específico (ex: dados_basicos, configuracao_fiscal)'");
    }

    public function down(): void
    {
        Schema::dropIfExists('permissoes');
    }
};
