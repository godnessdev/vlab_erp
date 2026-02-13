<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('log_integracao_fiscal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->uuid('nfse_id')->nullable();
            $table->uuid('lote_id')->nullable();
            $table->timestamp('data_log');
            $table->string('endpoint_url', 500);
            $table->string('metodo_http', 10);
            $table->json('headers_request')->nullable();
            $table->text('request_body')->nullable();
            $table->integer('status_http');
            $table->json('headers_response')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('tempo_resposta_ms')->nullable();
            $table->text('erro_interno')->nullable();
            $table->index('empresa_id', 'idx_log_empresa_id');
            $table->index('data_log', 'idx_log_data');
            $table->index('nfse_id', 'idx_log_nfse_id');
            $table->index('status_http', 'idx_log_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_integracao_fiscal');
    }
};
