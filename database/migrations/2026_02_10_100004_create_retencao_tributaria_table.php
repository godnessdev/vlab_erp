<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('retencao_tributaria', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('nfse_id');
            $table->enum('tipo_retencao', ['ISS','INSS','IR','CSLL','PIS','COFINS','IBS_RET','CBS_RET']);
            $table->decimal('base_calculo', 15, 2);
            $table->decimal('aliquota', 7, 4);
            $table->decimal('valor_retido', 15, 2);
            $table->enum('responsavel_retencao', ['TOMADOR','PRESTADOR']);
            $table->string('codigo_receita', 10)->nullable();
            $table->index('nfse_id', 'idx_retencao_nfse_id');
            $table->index('tipo_retencao', 'idx_retencao_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retencao_tributaria');
    }
};
