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
        Schema::create('contatos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pessoa_id')->comment('Referência à pessoa');
            $table->enum('tipo', ['EMAIL', 'TELEFONE_FIXO', 'CELULAR', 'WHATSAPP'])->comment('Tipo do contato');
            $table->string('valor', 255)->comment('Valor do contato');
            $table->boolean('principal')->default(false)->comment('Contato principal');
            $table->boolean('verificado')->default(false)->comment('Se foi verificado');
            $table->timestamp('data_criacao')->useCurrent();
            
            // Foreign Keys
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
            
            // Constraints únicos
            $table->unique(['pessoa_id', 'tipo', 'valor'], 'uk_contato_pessoa_tipo_valor');
            
            // Índices
            $table->index('pessoa_id', 'idx_contato_pessoa_id');
            $table->index('valor', 'idx_contato_valor');
            $table->index('tipo');
            $table->index('principal');
            $table->index('verificado');
            
            $table->comment('Múltiplos contatos por pessoa com validação e verificação');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contatos');
    }
};
