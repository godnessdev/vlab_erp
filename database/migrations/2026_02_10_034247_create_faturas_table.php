<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
    // Evitar duplicação do ENUM
    DB::statement("DROP TYPE IF EXISTS status_fatura");
    DB::statement("CREATE TYPE status_fatura AS ENUM ('ABERTA', 'ENVIADA', 'PAGA', 'CANCELADA')");
        
        Schema::create('faturas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id')->index();
            $table->uuid('cliente_id')->index();
            $table->string('numero_fatura', 20)->index();
            $table->date('data_emissao')->index();
            $table->date('data_vencimento');
            $table->date('mes_referencia')->index();
            
            // Valores da fatura
            $table->decimal('valor_servicos', 10, 2);
            $table->decimal('valor_deducoes', 10, 2)->default(0);
            $table->decimal('valor_descontos', 10, 2)->default(0);
            $table->decimal('base_calculo_iss', 10, 2);
            $table->decimal('aliquota_iss', 5, 2);
            $table->decimal('valor_iss', 10, 2);
            $table->decimal('valor_retencoes', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2);
            $table->decimal('valor_liquido', 10, 2);
            
            // Status e metadados
            $table->string('status', 20)->default('ABERTA')->index();
            $table->jsonb('regras_cobranca')->nullable();
            $table->text('observacoes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('usuarios')->onDelete('restrict');
            
            // Constraints únicos
            $table->unique(['empresa_id', 'numero_fatura'], 'uk_fatura_empresa_numero');
            
            // Índices adicionais
            $table->index(['empresa_id', 'status']);
            $table->index(['cliente_id', 'data_emissao']);
            $table->index(['data_emissao', 'data_vencimento']);
        });

        // Criar índice GIN para JSONB
        DB::statement('CREATE INDEX idx_faturas_regras_cobranca_gin ON faturas USING GIN (regras_cobranca)');
        
        // Comentários na tabela
        DB::statement("COMMENT ON TABLE faturas IS 'Faturas emitidas para cobrança de serviços'");
        DB::statement("COMMENT ON COLUMN faturas.numero_fatura IS 'Número sequencial da fatura por empresa'");
        DB::statement("COMMENT ON COLUMN faturas.mes_referencia IS 'Mês de referência dos serviços faturados'");
        DB::statement("COMMENT ON COLUMN faturas.regras_cobranca IS 'Regras específicas de cobrança em JSON'");
    }

    public function down(): void
    {
        Schema::dropIfExists('faturas');
        DB::statement('DROP TYPE IF EXISTS status_fatura');
    }
};
