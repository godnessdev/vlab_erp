<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Criar enum para status (somente PostgreSQL)
        if (config('database.default') === 'pgsql') {
            DB::statement("DO $$ BEGIN
                CREATE TYPE status_usuario AS ENUM ('ATIVO', 'INATIVO', 'BLOQUEADO', 'PENDENTE');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;");
        }

        Schema::create('usuarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pessoa_id')->notNullable()->unique();
            $table->string('email')->notNullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamp('ultimo_login')->nullable();
            $table->integer('tentativas_login_falhadas')->default(0);
            $table->string('remember_token')->nullable();
            $table->timestamps();

            // Chave estrangeira para pessoa
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');

            // Índices conforme especificação
            $table->index('email', 'idx_usuario_email');
        });

        // Adicionar coluna enum após criação da tabela (somente PostgreSQL)
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE usuarios ADD COLUMN status status_usuario DEFAULT \'ATIVO\'');

            // Criar índice para a coluna enum
            DB::statement('CREATE INDEX idx_usuario_status ON usuarios (status)');

            // Adicionar constraint de validação
            DB::statement('ALTER TABLE usuarios ADD CONSTRAINT chk_tentativas_login CHECK (tentativas_login_falhadas >= 0)');
        } else {
            // Para SQLite/outros bancos, usar string
            Schema::table('usuarios', function (Blueprint $table) {
                $table->string('status', 20)->default('ATIVO');
                $table->index('status', 'idx_usuario_status');
            });
        }

        // Comentários (somente PostgreSQL)
        if (config('database.default') === 'pgsql') {
            DB::statement("COMMENT ON TABLE usuarios IS 'Usuários do sistema com autenticação e controle de acesso'");
            DB::statement("COMMENT ON COLUMN usuarios.pessoa_id IS 'Referência para a pessoa física/jurídica'");
            DB::statement("COMMENT ON COLUMN usuarios.tentativas_login_falhadas IS 'Contador de tentativas de login malsucedidas'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        if (config('database.default') === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS status_usuario');
        }
    }
};
