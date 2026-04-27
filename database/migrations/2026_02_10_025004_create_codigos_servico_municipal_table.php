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
        Schema::create('codigos_servico_municipal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('servico_id')->comment('Referência ao serviço');
            $table->string('codigo_municipio_ibge', 7)->comment('Código IBGE do município');
            $table->string('codigo_servico', 20)->comment('Código do serviço no município');
            $table->string('descricao_municipal', 255)->nullable()->comment('Descrição conforme prefeitura');
            $table->decimal('aliquota_iss', 5, 2)->comment('Alíquota específica');
            $table->date('data_vigencia_inicio')->comment('Início da vigência');
            $table->date('data_vigencia_fim')->nullable()->comment('Fim da vigência');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('cascade');

            // Constraint único
            $table->unique(
                ['servico_id', 'codigo_municipio_ibge', 'data_vigencia_inicio'],
                'uk_codigo_servico_municipio_vigencia'
            );

            // Índices
            $table->index('servico_id', 'idx_codigo_servico_id');
            $table->index('codigo_municipio_ibge', 'idx_codigo_municipio');
            $table->index(['data_vigencia_inicio', 'data_vigencia_fim'], 'idx_codigo_vigencia');

            $table->comment('Códigos e alíquotas específicas por município');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigos_servico_municipal');
    }
};
