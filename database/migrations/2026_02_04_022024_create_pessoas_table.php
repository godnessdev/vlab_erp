<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pessoas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('tipo', ['FISICA', 'JURIDICA'])->comment('Tipo da pessoa');
            $table->string('nome_razao_social', 255)->comment('Nome ou razão social');
            $table->string('nome_fantasia', 255)->nullable()->comment('Apenas para pessoa jurídica');
            $table->date('data_nascimento_constituicao')->nullable()->comment('Data nascimento ou constituição');
            $table->enum('status', ['ATIVO', 'INATIVO'])->default('ATIVO');
            $table->timestamp('data_criacao')->useCurrent();
            $table->timestamp('data_atualizacao')->useCurrent()->useCurrentOnUpdate();
            
            // Índices
            $table->index('tipo');
            $table->index('status');
            $table->index(['tipo', 'status']);
            $table->index('nome_razao_social');
            
            // RLS (Row Level Security) será implementado via SQL
            $table->comment('Entidade central para pessoa física e jurídica');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};
