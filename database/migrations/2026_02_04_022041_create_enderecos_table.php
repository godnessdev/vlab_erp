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
        Schema::create('enderecos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pessoa_id')->comment('Referência à pessoa');
            $table->enum('tipo', ['PRINCIPAL', 'ENTREGA', 'FATURAMENTO'])->comment('Tipo do endereço');
            $table->string('logradouro', 255)->comment('Rua, avenida, etc');
            $table->string('numero', 20)->comment('Número do imóvel');
            $table->string('complemento', 100)->nullable()->comment('Apartamento, bloco, etc');
            $table->string('bairro', 100)->comment('Bairro');
            $table->string('cidade', 100)->comment('Cidade');
            $table->string('estado', 2)->comment('UF');
            $table->string('cep', 9)->comment('CEP formatado');
            $table->string('pais', 2)->default('BR')->comment('Código do país');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Coordenada GPS');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Coordenada GPS');
            $table->timestamp('data_criacao')->useCurrent();
            
            // Foreign Keys
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
            
            // Índices
            $table->index('pessoa_id', 'idx_endereco_pessoa_id');
            $table->index('cep', 'idx_endereco_cep');
            $table->index(['cidade', 'estado'], 'idx_endereco_cidade_estado');
            $table->index('tipo');
            
            $table->comment('Múltiplos endereços por pessoa com geolocalização');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enderecos');
    }
};
