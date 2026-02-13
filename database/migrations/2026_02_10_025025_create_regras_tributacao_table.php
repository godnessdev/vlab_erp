<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Criar ENUM para regime_tributario se não existir
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("DO $$ 
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'regime_tributario_enum') THEN
                    CREATE TYPE regime_tributario_enum AS ENUM ('SIMPLES_NACIONAL', 'LUCRO_PRESUMIDO', 'LUCRO_REAL', 'LUCRO_ARBITRADO');
                END IF;
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");
        }

        Schema::create('regras_tributacao', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('servico_id')->comment('Referência ao serviço');
            $table->string('regime_tributario')->comment('SIMPLES_NACIONAL, LUCRO_PRESUMIDO, LUCRO_REAL, LUCRO_ARBITRADO');
            $table->decimal('aliquota_ir', 5, 2)->default(0)->comment('Alíquota Imposto de Renda');
            $table->decimal('aliquota_csll', 5, 2)->default(0)->comment('Alíquota CSLL');
            $table->decimal('aliquota_pis', 5, 2)->default(0)->comment('Alíquota PIS');
            $table->decimal('aliquota_cofins', 5, 2)->default(0)->comment('Alíquota COFINS');
            $table->boolean('retencao_inss')->default(false)->comment('Se há retenção de INSS');
            $table->jsonb('base_calculo_diferenciada')->nullable()->comment('Regras especiais de cálculo');
            $table->jsonb('regras_adicionais')->nullable()->comment('Regras específicas');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('cascade');

            // Constraint único
            $table->unique(['servico_id', 'regime_tributario'], 'uk_regra_servico_regime');

            // Índices
            $table->index('servico_id', 'idx_regra_servico_id');
            $table->index('regime_tributario', 'idx_regra_regime');

            $table->comment('Regras tributárias específicas por regime e serviço');
        });

        // Aplicar tipo ENUM e índices JSONB no PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE regras_tributacao ALTER COLUMN regime_tributario TYPE regime_tributario_enum USING regime_tributario::regime_tributario_enum");
            DB::statement('CREATE INDEX gin_regra_base_calculo ON regras_tributacao USING GIN (base_calculo_diferenciada)');
            DB::statement('CREATE INDEX gin_regra_adicionais ON regras_tributacao USING GIN (regras_adicionais)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regras_tributacao');

        // Remover ENUM se PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("DROP TYPE IF EXISTS regime_tributario_enum CASCADE");
        }
    }
};
