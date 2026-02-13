<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('papeis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pessoa_id')->comment('Referência à pessoa');
            $table->uuid('empresa_id')->comment('Referência à empresa');
            $table->enum('tipo_papel', ['CLIENTE', 'PRESTADOR', 'FUNCIONARIO', 'FORNECEDOR', 'CONTADOR'])->comment('Tipo do papel');
            $table->date('data_inicio')->comment('Data início do papel');
            $table->date('data_fim')->nullable()->comment('Data fim (para histórico)');
            $table->enum('status', ['ATIVO', 'INATIVO'])->default('ATIVO');
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
            // empresa_id será criado quando implementarmos o domínio Empresa
            
            // Índice composto para garantir unicidade (sem WHERE clause para compatibilidade SQLite)
            $table->index(['pessoa_id', 'empresa_id', 'tipo_papel', 'status'], 'idx_papel_unique_check');
            
            // Índices
            $table->index('pessoa_id', 'idx_papel_pessoa_id');
            $table->index('empresa_id', 'idx_papel_empresa_id');
            $table->index(['tipo_papel', 'status'], 'idx_papel_tipo_status');
            $table->index('data_inicio');
            $table->index('data_fim');
            
            $table->comment('Entidade dinâmica que vincula pessoa a empresa com papel específico');
        });

        // Para PostgreSQL, criar índice único parcial
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX uk_papel_pessoa_empresa_tipo_ativo ON papeis (pessoa_id, empresa_id, tipo_papel) WHERE data_fim IS NULL AND status = \'ATIVO\'');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papeis');
    }
};
