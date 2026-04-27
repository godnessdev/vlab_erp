<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fluxo_caixa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->date('data_referencia');
            $table->enum('tipo_movimento', ['ENTRADA', 'SAIDA']);
            $table->enum('categoria', ['RECEBIMENTO', 'PAGAMENTO', 'TRANSFERENCIA']);
            $table->decimal('valor', 15, 2);
            $table->string('descricao', 255);
            $table->uuid('conta_receber_id')->nullable();
            $table->uuid('conta_pagar_id')->nullable();
            $table->boolean('realizado')->default(false);
            $table->date('data_realizacao')->nullable();
            $table->decimal('saldo_acumulado', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['empresa_id', 'data_referencia'], 'idx_fluxo_empresa_data');
            $table->index(['tipo_movimento', 'categoria'], 'idx_fluxo_tipo_categoria');
            $table->index('realizado', 'idx_fluxo_realizado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fluxo_caixa');
    }
};
