<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->uuid('fatura_id');
            $table->bigInteger('numero_rps');
            $table->string('serie', 5);
            $table->timestamp('data_emissao');
            $table->date('competencia');
            $table->decimal('valor_servicos', 15, 2);
            $table->decimal('valor_deducoes', 15, 2)->default(0);
            $table->decimal('valor_pis', 15, 2)->default(0);
            $table->decimal('valor_cofins', 15, 2)->default(0);
            $table->decimal('valor_inss', 15, 2)->default(0);
            $table->decimal('valor_ir', 15, 2)->default(0);
            $table->decimal('valor_csll', 15, 2)->default(0);
            $table->decimal('base_calculo', 15, 2);
            $table->decimal('aliquota', 5, 4);
            $table->decimal('valor_iss', 15, 2);
            $table->decimal('valor_iss_retido', 15, 2)->default(0);
            $table->decimal('valor_ibs', 15, 2)->default(0);
            $table->decimal('valor_cbs', 15, 2)->default(0);
            $table->text('descricao');
            $table->string('codigo_servico', 20);
            $table->string('codigo_cnae', 10)->nullable();
            $table->string('item_lista_servico', 5);
            $table->enum('situacao', ['GERADO', 'ENVIADO', 'CONVERTIDO', 'ERRO'])->default('GERADO');
            $table->boolean('usar_layout_nacional')->default(true);
            $table->timestamp('data_criacao');
            $table->unique(['empresa_id', 'serie', 'numero_rps'], 'uk_rps_empresa_serie_numero');
            $table->index('empresa_id', 'idx_rps_empresa_id');
            $table->index('fatura_id', 'idx_rps_fatura_id');
            $table->index('data_emissao', 'idx_rps_data_emissao');
            $table->index('situacao', 'idx_rps_situacao');
            $table->index('competencia', 'idx_rps_competencia');
            $table->index('usar_layout_nacional', 'idx_rps_layout_nacional');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps');
    }
};
