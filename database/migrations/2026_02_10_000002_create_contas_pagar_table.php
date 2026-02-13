<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contas_pagar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->uuid('fornecedor_id');
            $table->string('numero_conta', 20);
            $table->string('descricao', 255);
            $table->enum('categoria', ['FORNECEDOR','FUNCIONARIO','IMPOSTO','DESPESA_GERAL']);
            $table->decimal('valor_original', 15, 2);
            $table->decimal('valor_juros', 15, 2)->default(0);
            $table->decimal('valor_multa', 15, 2)->default(0);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2);
            $table->date('data_vencimento');
            $table->date('data_emissao');
            $table->enum('status', ['ABERTA','PAGA','PARCIAL','ATRASADA','CANCELADA'])->default('ABERTA');
            $table->string('centro_custo', 50)->nullable();
            $table->string('numero_documento', 50)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        $table->softDeletes();
            $table->index('empresa_id', 'idx_conta_pagar_empresa_id');
            $table->index('fornecedor_id', 'idx_conta_pagar_fornecedor_id');
            $table->index('data_vencimento', 'idx_conta_pagar_vencimento');
            $table->index('status', 'idx_conta_pagar_status');
            $table->index('categoria', 'idx_conta_pagar_categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_pagar');
    }
};
