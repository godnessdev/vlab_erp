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
        Schema::create('documentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pessoa_id')->comment('Referência à pessoa');
            $table->enum('tipo', ['CPF', 'CNPJ', 'RG', 'IE', 'IM'])->comment('Tipo do documento');
            $table->string('valor', 50)->comment('Valor do documento');
            $table->date('data_emissao')->nullable()->comment('Data de emissão');
            $table->string('orgao_emissor', 100)->nullable()->comment('Órgão emissor');
            $table->boolean('valido')->default(true)->comment('Se documento é válido');
            $table->timestamp('data_criacao')->useCurrent();
            
            // Foreign Keys
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
            
            // Constraints únicos
            $table->unique(['pessoa_id', 'tipo'], 'uk_documento_pessoa_tipo');
            $table->unique(['tipo', 'valor'], 'uk_documento_tipo_valor');
            
            // Índices
            $table->index('pessoa_id', 'idx_documento_pessoa_id');
            $table->index(['tipo', 'valor'], 'idx_documento_tipo_valor');
            $table->index('tipo');
            $table->index('valido');
            
            $table->comment('Múltiplos documentos por pessoa com validação');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
