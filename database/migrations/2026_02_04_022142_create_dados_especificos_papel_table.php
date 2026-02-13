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
        Schema::create('dados_especificos_papel', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('papel_id')->comment('Referência ao papel');
            $table->string('chave', 100)->comment('Nome da propriedade');
            $table->jsonb('valor')->comment('Valor flexível em JSONB');
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('papel_id')->references('id')->on('papeis')->onDelete('cascade');
            
            // Constraint único
            $table->unique(['papel_id', 'chave'], 'uk_dado_papel_chave');
            
            // Índices
            $table->index('papel_id', 'idx_dado_papel_id');
            $table->index('chave', 'idx_dado_chave');
            
            $table->comment('Extensões EAV para dados específicos por papel');
        });
        
        // Índice para busca em JSON (PostgreSQL usa GIN, SQLite usa índice normal)
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX gin_dado_valor ON dados_especificos_papel USING GIN (valor)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dados_especificos_papel');
    }
};
