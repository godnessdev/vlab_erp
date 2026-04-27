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
                CREATE TYPE ambiente_fiscal AS ENUM ('PRODUCAO', 'HOMOLOGACAO');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");
        }

        Schema::create('configuracoes_fiscais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id')->notNullable();
            $table->uuid('filial_id')->nullable();
            $table->decimal('aliquota_iss_default', 5, 2)->notNullable();
            $table->string('codigo_municipio_ibge', 7)->notNullable();
            $table->uuid('certificado_digital_id')->nullable();
            $table->string('webservice_url', 255)->notNullable();
            $table->timestamps();

            // Chaves estrangeiras
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('filial_id')->references('id')->on('filiais')->onDelete('cascade');
            // TODO: certificado_digital_id será criado no domínio fiscal

            // Índices conforme especificação
            $table->index('empresa_id', 'idx_config_fiscal_empresa_id');
            $table->index('codigo_municipio_ibge', 'idx_config_fiscal_municipio');
            $table->index('filial_id', 'idx_config_fiscal_filial_id');
        });

        // Adicionar colunas enum apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE configuracoes_fiscais ADD COLUMN ambiente ambiente_fiscal DEFAULT \'PRODUCAO\'');
            DB::statement('ALTER TABLE configuracoes_fiscais ADD CONSTRAINT chk_aliquota_iss CHECK (aliquota_iss_default >= 0 AND aliquota_iss_default <= 20)');
        } else {
            // Para SQLite/outros bancos, usar string
            Schema::table('configuracoes_fiscais', function (Blueprint $table) {
                $table->string('ambiente', 20)->default('PRODUCAO');
            });
        }

        // Habilitar RLS (somente PostgreSQL)
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE configuracoes_fiscais ENABLE ROW LEVEL SECURITY');

            // Política RLS
            DB::statement("
                CREATE POLICY tenant_isolation_config_fiscal ON configuracoes_fiscais
                FOR ALL TO app_role
                USING (empresa_id = current_setting('app.tenant_id', true)::uuid)
            ");
        }

        // Comentários (somente PostgreSQL)
        if (config('database.default') === 'pgsql') {
            DB::statement("COMMENT ON TABLE configuracoes_fiscais IS 'Configurações fiscais por empresa/filial para múltiplas prefeituras'");
            DB::statement("COMMENT ON COLUMN configuracoes_fiscais.codigo_municipio_ibge IS 'Código IBGE do município para configuração fiscal'");
            DB::statement("COMMENT ON COLUMN configuracoes_fiscais.ambiente IS 'PRODUCAO para emissão real, HOMOLOGACAO para testes'");
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'pgsql') {
            DB::statement('DROP POLICY IF EXISTS tenant_isolation_config_fiscal ON configuracoes_fiscais');
            DB::statement('DROP TYPE IF EXISTS ambiente_fiscal');
        }
        Schema::dropIfExists('configuracoes_fiscais');
    }
};
