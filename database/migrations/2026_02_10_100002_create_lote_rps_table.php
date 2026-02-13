<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lote_rps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->bigInteger('numero_lote');
            $table->timestamp('data_geracao');
            $table->integer('quantidade_rps');
            $table->decimal('valor_total_servicos', 15, 2);
            $table->decimal('valor_total_deducoes', 15, 2)->default(0);
            $table->string('inscricao_municipal', 20);
            $table->string('cnpj', 14);
            $table->enum('status_envio', ['GERADO','ENVIADO','PROCESSANDO','PROCESSADO','ERRO'])->default('GERADO');
            $table->string('protocolo_recebimento', 50)->nullable();
            $table->timestamp('data_envio')->nullable();
            $table->timestamp('data_processamento')->nullable();
            $table->text('mensagem_retorno')->nullable();
            $table->enum('ambiente', ['NACIONAL','MUNICIPAL'])->default('NACIONAL');
            $table->unique(['empresa_id', 'numero_lote'], 'uk_lote_empresa_numero');
            $table->index('empresa_id', 'idx_lote_empresa_id');
            $table->index('data_geracao', 'idx_lote_data_geracao');
            $table->index('status_envio', 'idx_lote_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_rps');
    }
};
