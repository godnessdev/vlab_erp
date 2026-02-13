<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_faturas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fatura_id')->index();
            $table->uuid('ordem_servico_id')->index();
            $table->uuid('item_ordem_id')->nullable()->index();
            $table->integer('sequencia');
            
            // Descrição do serviço
            $table->string('descricao', 500);
            $table->decimal('quantidade', 10, 2);
            $table->string('unidade_medida', 10);
            
            // Preços e descontos
            $table->decimal('preco_unitario', 10, 2);
            $table->decimal('desconto_percentual', 5, 2)->default(0);
            $table->decimal('desconto_valor', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            
            // Informações fiscais
            $table->string('codigo_servico_municipal', 20);
            $table->decimal('aliquota_iss_item', 5, 2);
            
            // Timestamps
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('fatura_id')->references('id')->on('faturas')->onDelete('cascade');
            // $table->foreign('ordem_servico_id')->references('id')->on('ordem_servicos')->onDelete('restrict');
            
            // Índices
            $table->index(['fatura_id', 'sequencia'], 'idx_item_fatura_sequencia');
            $table->index(['ordem_servico_id', 'fatura_id']);
            
            // Unique constraint para evitar duplicação de itens
            $table->unique(['fatura_id', 'ordem_servico_id', 'item_ordem_id'], 'uk_item_fatura_ordem_item');
        });
        
        // Comentários
        DB::statement("COMMENT ON TABLE item_faturas IS 'Itens que compõem as faturas, originados das ordens de serviço'");
        DB::statement("COMMENT ON COLUMN item_faturas.sequencia IS 'Ordem de exibição do item na fatura'");
        DB::statement("COMMENT ON COLUMN item_faturas.codigo_servico_municipal IS 'Código do serviço conforme tabela municipal'");
    }

    public function down(): void
    {
        Schema::dropIfExists('item_faturas');
    }
};
