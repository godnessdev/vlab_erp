<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('papeis_sistema', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome', 50)->unique()->comment('Nome único do papel (ex: super_admin, admin_empresa)');
            $table->string('descricao', 255)->comment('Descrição do papel');
            $table->integer('nivel')->comment('Nível hierárquico do papel (1=mais alto)');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('nome');
            $table->index(['ativo', 'nivel']);
            $table->index('nivel');

            $table->comment('Papéis do sistema para controle de acesso (RBAC)');
        });

        // Tabela pivot para papel-permissões
        Schema::create('papel_permissoes', function (Blueprint $table) {
            $table->uuid('papel_id');
            $table->uuid('permissao_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('papel_id')->references('id')->on('papeis_sistema')->onDelete('cascade');
            $table->foreign('permissao_id')->references('id')->on('permissoes')->onDelete('cascade');

            // Constraint unique - chave primária composta
            $table->primary(['papel_id', 'permissao_id']);

            // Índices
            $table->index('papel_id');
            $table->index('permissao_id');

            $table->comment('Relacionamento entre papéis e permissões');
        });

        // Adicionar comentários para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("COMMENT ON TABLE papeis_sistema IS 'Papéis do sistema para controle de acesso (RBAC)'");
            DB::statement("COMMENT ON COLUMN papeis_sistema.nome IS 'Nome único do papel (ex: super_admin, admin_empresa)'");
            DB::statement("COMMENT ON COLUMN papeis_sistema.nivel IS 'Nível hierárquico do papel (1=mais alto)'");

            DB::statement("COMMENT ON TABLE papel_permissoes IS 'Relacionamento entre papéis e permissões'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papel_permissoes');
        Schema::dropIfExists('papeis_sistema');
    }
};
