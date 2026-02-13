<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('protocolo_fiscal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->uuid('lote_id')->nullable();
            $table->uuid('nfse_id')->nullable();
            $table->enum('tipo_operacao', ['ENVIO_LOTE','CONSULTA_LOTE','CONSULTA_NFSE','CANCELAMENTO']);
            $table->string('codigo_protocolo', 50)->nullable();
            $table->timestamp('data_envio');
            $table->timestamp('data_retorno')->nullable();
            $table->enum('status_resposta', ['PENDENTE','SUCESSO','ERRO','TIMEOUT']);
            $table->string('codigo_erro', 10)->nullable();
            $table->text('mensagem_erro')->nullable();
            $table->text('xml_envio');
            $table->text('xml_retorno')->nullable();
            $table->index('empresa_id', 'idx_protocolo_empresa_id');
            $table->index('lote_id', 'idx_protocolo_lote_id');
            $table->index('nfse_id', 'idx_protocolo_nfse_id');
            $table->index('data_envio', 'idx_protocolo_data_envio');
            $table->index('status_resposta', 'idx_protocolo_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocolo_fiscal');
    }
};
