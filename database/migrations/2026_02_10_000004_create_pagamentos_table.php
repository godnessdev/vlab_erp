<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conta_pagar_id');
            $table->date('data_pagamento');
            $table->decimal('valor_pago', 15, 2);
            $table->decimal('valor_juros_pago', 15, 2)->default(0);
            $table->decimal('valor_multa_paga', 15, 2)->default(0);
            $table->decimal('valor_desconto_obtido', 15, 2)->default(0);
            $table->enum('forma_pagamento', ['DINHEIRO','BOLETO','PIX','CARTAO','TRANSFERENCIA','CHEQUE']);
            $table->string('numero_transacao', 100)->nullable();
            $table->string('banco_destino', 10)->nullable();
            $table->string('agencia_destino', 10)->nullable();
            $table->string('conta_destino', 20)->nullable();
            $table->string('comprovante_url', 500)->nullable();
            $table->boolean('conciliado')->default(false);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('conta_pagar_id', 'idx_pagamento_conta_id');
            $table->index('data_pagamento', 'idx_pagamento_data');
            $table->index('numero_transacao', 'idx_pagamento_transacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
