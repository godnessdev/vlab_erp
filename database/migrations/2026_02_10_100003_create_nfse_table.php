<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nfse', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->uuid('rps_id');
            $table->bigInteger('numero_nfse');
            $table->string('codigo_verificacao', 20);
            $table->timestamp('data_emissao');
            $table->timestamp('data_autorizacao');
            $table->string('municipio_prestacao', 7);
            $table->string('url_visualizacao', 500)->nullable();
            $table->enum('status', ['AUTORIZADA','CANCELADA','SUBSTITUIDA'])->default('AUTORIZADA');
            $table->text('motivo_cancelamento')->nullable();
            $table->timestamp('data_cancelamento')->nullable();
            $table->text('xml_autorizacao')->nullable();
            $table->text('xml_cancelamento')->nullable();
            $table->string('hash_xml', 64)->nullable();
            $table->string('versao_schema', 10);
            $table->decimal('base_calculo_ibs', 15, 2)->default(0);
            $table->decimal('aliquota_ibs', 7, 4)->default(0);
            $table->decimal('valor_ibs', 15, 2)->default(0);
            $table->decimal('valor_ibs_retido', 15, 2)->default(0);
            $table->decimal('base_calculo_cbs', 15, 2)->default(0);
            $table->decimal('aliquota_cbs', 7, 4)->default(0);
            $table->decimal('valor_cbs', 15, 2)->default(0);
            $table->unique(['numero_nfse', 'municipio_prestacao'], 'uk_nfse_numero_municipio');
            $table->unique('rps_id', 'uk_nfse_rps');
            $table->index('empresa_id', 'idx_nfse_empresa_id');
            $table->index('numero_nfse', 'idx_nfse_numero');
            $table->index('data_emissao', 'idx_nfse_data_emissao');
            $table->index('status', 'idx_nfse_status');
            $table->index('codigo_verificacao', 'idx_nfse_codigo_verificacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfse');
    }
};
