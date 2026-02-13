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
        // Criar ENUM para unidade_medida se não existir
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("DO $$ 
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'unidade_medida_enum') THEN
                    CREATE TYPE unidade_medida_enum AS ENUM ('HORA', 'DIA', 'PROJETO', 'UNIDADE', 'MES', 'PERCENTUAL');
                END IF;
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");

            DB::statement("DO $$ 
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_servico_enum') THEN
                    CREATE TYPE status_servico_enum AS ENUM ('ATIVO', 'INATIVO', 'DESCONTINUADO');
                END IF;
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");
        }

        Schema::create('servicos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id')->comment('Tenant isolamento');
            $table->string('descricao', 500)->comment('Descrição do serviço');
            $table->string('unidade_medida')->comment('HORA, DIA, PROJETO, UNIDADE, MES, PERCENTUAL');
            $table->decimal('preco_base', 10, 2)->comment('Preço base sugerido');
            $table->decimal('aliquota_iss_default', 5, 2)->comment('Alíquota ISS padrão');
            $table->string('classificacao_fiscal', 20)->comment('Código CNAE ou similar');
            $table->text('observacoes')->nullable()->comment('Observações adicionais');
            $table->string('status')->comment('ATIVO, INATIVO, DESCONTINUADO');
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            // Índices
            $table->index('empresa_id', 'idx_servico_empresa_id');
            $table->index('classificacao_fiscal', 'idx_servico_classificacao');
            $table->index('status', 'idx_servico_status');
            $table->index(['empresa_id', 'status'], 'idx_servico_empresa_status');

            $table->comment('Catálogo de serviços por empresa');
        });

        // Aplicar tipos ENUM no PostgreSQL após criação
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE servicos ALTER COLUMN unidade_medida TYPE unidade_medida_enum USING unidade_medida::unidade_medida_enum");
            DB::statement("ALTER TABLE servicos ALTER COLUMN status TYPE status_servico_enum USING status::status_servico_enum");
            
            // Definir valor padrão após conversão
            DB::statement("ALTER TABLE servicos ALTER COLUMN status SET DEFAULT 'ATIVO'::status_servico_enum");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicos');

        // Remover ENUMs se PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("DROP TYPE IF EXISTS unidade_medida_enum CASCADE");
            DB::statement("DROP TYPE IF EXISTS status_servico_enum CASCADE");
        }
    }
};
