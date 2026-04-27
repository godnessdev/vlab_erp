<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_receber', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->uuid('fatura_id');
            $table->uuid('nfse_id')->nullable();
            $table->string('numero_conta', 20);
            $table->uuid('cliente_id');
            $table->decimal('valor_original', 15, 2);
            $table->decimal('valor_juros', 15, 2)->default(0);
            $table->decimal('valor_multa', 15, 2)->default(0);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_retencoes', 15, 2)->default(0);
            $table->decimal('valor_liquido_esperado', 15, 2);
            $table->date('data_vencimento');
            $table->date('data_emissao');
            $table->enum('status', ['ABERTA', 'PAGA', 'PARCIAL', 'ATRASADA', 'CANCELADA'])->default('ABERTA');
            $table->enum('forma_cobranca', ['BOLETO', 'PIX', 'CARTAO', 'DINHEIRO', 'TRANSFERENCIA']);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['empresa_id', 'numero_conta'], 'uk_conta_receber_empresa_numero');
            $table->unique(['fatura_id'], 'uk_conta_receber_fatura');
            $table->index('empresa_id', 'idx_conta_receber_empresa_id');
            $table->index('cliente_id', 'idx_conta_receber_cliente_id');
            $table->index('data_vencimento', 'idx_conta_receber_vencimento');
            $table->index('status', 'idx_conta_receber_status');
            $table->index('nfse_id', 'idx_conta_receber_nfse_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_receber');
    }
};
