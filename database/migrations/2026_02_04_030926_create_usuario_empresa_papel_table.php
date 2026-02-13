<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Criar enum para status
        DB::statement("DO $$ BEGIN
            CREATE TYPE status_vinculo AS ENUM ('ATIVO', 'INATIVO', 'SUSPENSO');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        Schema::create('usuario_empresa_papel', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('usuario_id')->notNullable();
            $table->uuid('empresa_id')->notNullable();
            $table->uuid('papel_id')->notNullable();
            $table->date('data_inicio')->notNullable();
            $table->date('data_fim')->nullable();
            $table->timestamps();
            
            // Chaves estrangeiras
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('papel_id')->references('id')->on('papeis')->onDelete('cascade');
            
            // Índices conforme especificação
            $table->index('usuario_id', 'idx_uep_usuario_id');
            $table->index('empresa_id', 'idx_uep_empresa_id');
            $table->index('papel_id', 'idx_uep_papel_id');
            $table->index(['usuario_id', 'empresa_id'], 'idx_uep_usuario_empresa');
            
            // Constraint única composta
            $table->unique(['usuario_id', 'empresa_id', 'papel_id'], 'uk_usuario_empresa_papel');
        });

        // Adicionar coluna enum após criação da tabela
        DB::statement('ALTER TABLE usuario_empresa_papel ADD COLUMN status status_vinculo DEFAULT \'ATIVO\'');
        
        // Adicionar constraint de validação
        DB::statement('ALTER TABLE usuario_empresa_papel ADD CONSTRAINT chk_data_vigencia CHECK (data_inicio <= data_fim OR data_fim IS NULL)');

        // Habilitar RLS
        DB::statement('ALTER TABLE usuario_empresa_papel ENABLE ROW LEVEL SECURITY');
        
        // Política RLS
        DB::statement("
            CREATE POLICY tenant_isolation_uep ON usuario_empresa_papel
            FOR ALL TO app_role
            USING (empresa_id = current_setting('app.tenant_id', true)::uuid)
        ");

        // Comentários
        DB::statement("COMMENT ON TABLE usuario_empresa_papel IS 'Relacionamento entre usuário, empresa e papel com controle temporal'");
        DB::statement("COMMENT ON COLUMN usuario_empresa_papel.data_inicio IS 'Data de início da vigência do papel'");
        DB::statement("COMMENT ON COLUMN usuario_empresa_papel.data_fim IS 'Data de fim da vigência (NULL = indefinido)'");
    }

    public function down(): void
    {
        DB::statement('DROP POLICY IF EXISTS tenant_isolation_uep ON usuario_empresa_papel');
        Schema::dropIfExists('usuario_empresa_papel');
        DB::statement('DROP TYPE IF EXISTS status_vinculo');
    }
};
