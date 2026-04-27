<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Criar enum apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("DO $$ BEGIN
                CREATE TYPE regime_tributario AS ENUM ('SIMPLES_NACIONAL', 'LUCRO_PRESUMIDO', 'LUCRO_REAL');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");

            DB::statement("DO $$ BEGIN
                CREATE TYPE status_empresa AS ENUM ('ATIVO', 'INATIVO', 'SUSPENSO');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");
        }

        Schema::create('empresas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome', 255)->notNullable();
            $table->string('cnpj', 18)->unique()->notNullable();
            $table->string('ie', 20)->nullable();
            $table->string('im', 20)->nullable();
            $table->date('data_constituicao')->nullable();
            $table->string('email_contato', 100)->nullable();
            $table->string('telefone_contato', 20)->nullable();
            $table->timestamps();

            // Índices conforme especificação
            $table->index('cnpj', 'idx_empresa_cnpj');
        });

        // Adicionar colunas enum apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE empresas ADD COLUMN regime_tributario regime_tributario DEFAULT \'SIMPLES_NACIONAL\'');
            DB::statement('ALTER TABLE empresas ADD COLUMN status status_empresa DEFAULT \'ATIVO\'');

            // Criar índices para as colunas enum
            DB::statement('CREATE INDEX idx_empresa_status ON empresas (status)');
            DB::statement('CREATE INDEX idx_empresa_regime ON empresas (regime_tributario)');
        } else {
            // Para SQLite/outros bancos, usar string
            Schema::table('empresas', function (Blueprint $table) {
                $table->string('regime_tributario', 50)->default('SIMPLES_NACIONAL');
                $table->string('status', 20)->default('ATIVO');
                $table->index('status', 'idx_empresa_status');
                $table->index('regime_tributario', 'idx_empresa_regime');
            });
        }

        // Habilitar RLS apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Habilitar RLS (Row Level Security)
            DB::statement('ALTER TABLE empresas ENABLE ROW LEVEL SECURITY');

            // Política RLS para isolamento multitenant
            DB::statement("
                CREATE POLICY tenant_isolation_empresa ON empresas
                FOR ALL TO app_role
                USING (id = current_setting('app.tenant_id', true)::uuid)
            ");

            // Comentários para documentação
            DB::statement("COMMENT ON TABLE empresas IS 'Entidade central do multitenancy - cada empresa é um tenant isolado'");
            DB::statement("COMMENT ON COLUMN empresas.id IS 'Identificador único do tenant - usado em toda aplicação para isolamento'");
            DB::statement("COMMENT ON COLUMN empresas.cnpj IS 'CNPJ formatado com validação matemática'");
            DB::statement("COMMENT ON COLUMN empresas.regime_tributario IS 'Determina regras fiscais aplicáveis'");
        }
    }

    public function down(): void
    {
        // Remover políticas RLS apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP POLICY IF EXISTS tenant_isolation_empresa ON empresas');
        }

        Schema::dropIfExists('empresas');

        // Remover enums apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS status_empresa');
            DB::statement('DROP TYPE IF EXISTS regime_tributario');
        }
    }
};
