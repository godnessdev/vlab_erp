<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Renomear colunas existentes
            $table->renameColumn('nome', 'razao_social');
            $table->renameColumn('ie', 'inscricao_estadual');
            $table->renameColumn('im', 'inscricao_municipal');
            $table->renameColumn('email_contato', 'email');
            $table->renameColumn('telefone_contato', 'telefone');
            
            // Adicionar novas colunas
            $table->string('nome_fantasia', 255)->nullable()->after('razao_social');
            $table->jsonb('dados_endereco')->nullable()->after('telefone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Remover novas colunas
            $table->dropColumn(['nome_fantasia', 'dados_endereco']);
            
            // Reverter renomeações
            $table->renameColumn('razao_social', 'nome');
            $table->renameColumn('inscricao_estadual', 'ie');
            $table->renameColumn('inscricao_municipal', 'im');
            $table->renameColumn('email', 'email_contato');
            $table->renameColumn('telefone', 'telefone_contato');
        });
    }
};
