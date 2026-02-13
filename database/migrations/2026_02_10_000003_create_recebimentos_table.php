<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recebimentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conta_receber_id');
            $table->date('data_recebimento');
            $table->decimal('valor_recebido', 15, 2);
            $table->decimal('valor_juros_recebido', 15, 2)->default(0);
            $table->decimal('valor_multa_recebida', 15, 2)->default(0);
            $table->decimal('valor_desconto_concedido', 15, 2)->default(0);
            $table->enum('forma_recebimento', ['DINHEIRO','BOLETO','PIX','CARTAO_CREDITO','CARTAO_DEBITO','TRANSFERENCIA']);
            $table->string('numero_transacao', 100)->nullable();
            $table->string('banco_origem', 10)->nullable();
            $table->string('agencia_origem', 10)->nullable();
            $table->string('conta_origem', 20)->nullable();
            $table->string('comprovante_url', 500)->nullable();
            $table->boolean('conciliado')->default(false);
            $table->date('data_conciliacao')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('conta_receber_id', 'idx_recebimento_conta_id');
            $table->index('data_recebimento', 'idx_recebimento_data');
            $table->index('numero_transacao', 'idx_recebimento_transacao');
            $table->index('conciliado', 'idx_recebimento_conciliado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recebimentos');
    }
};
