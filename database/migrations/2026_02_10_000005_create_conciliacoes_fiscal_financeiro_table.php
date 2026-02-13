<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conciliacoes_fiscal_financeiro', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conta_receber_id');
            $table->uuid('nfse_id');
            $table->timestamp('data_conciliacao');
            $table->decimal('valor_nfse', 15, 2);
            $table->decimal('valor_conta_receber', 15, 2);
            $table->decimal('valor_retencoes_nfse', 15, 2);
            $table->decimal('valor_liquido_nfse', 15, 2);
            $table->decimal('discrepancia_valor', 15, 2)->default(0);
            $table->enum('status_conciliacao', ['PENDENTE','CONCILIADO','DISCREPANTE'])->default('PENDENTE');
            $table->text('observacoes_discrepancia')->nullable();
            $table->uuid('usuario_conciliacao');
            $table->timestamps();
            $table->index('conta_receber_id', 'idx_conciliacao_conta_id');
            $table->index('nfse_id', 'idx_conciliacao_nfse_id');
            $table->index('data_conciliacao', 'idx_conciliacao_data');
            $table->index('status_conciliacao', 'idx_conciliacao_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conciliacoes_fiscal_financeiro');
    }
};
