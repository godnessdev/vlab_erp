<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Evitar duplicação do ENUM (somente PostgreSQL)
        if (config('database.default') === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS status_pagamento_parcela');
            DB::statement("CREATE TYPE status_pagamento_parcela AS ENUM ('PENDENTE', 'PAGO', 'ATRASADO', 'CANCELADO')");
        }

        Schema::create('parcela_faturas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fatura_id')->index();
            $table->integer('numero_parcela');
            $table->date('data_vencimento')->index();

            // Valores da parcela
            $table->decimal('valor_parcela', 10, 2);
            $table->decimal('valor_juros', 10, 2)->default(0);
            $table->decimal('valor_total_parcela', 10, 2);

            // Status e pagamento
            $table->string('status_pagamento', 20)->default('PENDENTE')->index();
            $table->date('data_pagamento')->nullable();
            $table->decimal('valor_pago', 10, 2)->nullable();
            $table->string('forma_pagamento', 50)->nullable();
            $table->text('observacoes_pagamento')->nullable();

            // Timestamps
            $table->timestamps();

            // Foreign keys
            $table->foreign('fatura_id')->references('id')->on('faturas')->onDelete('cascade');

            // Constraints únicos
            $table->unique(['fatura_id', 'numero_parcela'], 'uk_parcela_fatura_numero');

            // Índices adicionais
            $table->index(['status_pagamento', 'data_vencimento']);
            $table->index(['data_pagamento']);
            $table->index(['fatura_id', 'status_pagamento']);
        });

        // Comentários (somente PostgreSQL)
        if (config('database.default') === 'pgsql') {
            DB::statement("COMMENT ON TABLE parcela_faturas IS 'Parcelas para pagamento das faturas'");
            DB::statement("COMMENT ON COLUMN parcela_faturas.numero_parcela IS 'Número sequencial da parcela dentro da fatura'");
            DB::statement("COMMENT ON COLUMN parcela_faturas.valor_juros IS 'Juros aplicados sobre a parcela'");
            DB::statement("COMMENT ON COLUMN parcela_faturas.forma_pagamento IS 'Forma como foi efetuado o pagamento'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parcela_faturas');
        if (config('database.default') === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS status_pagamento_parcela');
        }
    }
};
