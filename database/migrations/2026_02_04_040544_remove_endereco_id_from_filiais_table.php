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
        Schema::table('filiais', function (Blueprint $table) {
            // Verificar se a coluna existe antes de remover
            if (Schema::hasColumn('filiais', 'endereco_id')) {
                // Remover foreign key se existir
                $table->dropForeign(['endereco_id']);
                // Remover a coluna
                $table->dropColumn('endereco_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filiais', function (Blueprint $table) {
            $table->uuid('endereco_id')->nullable();
            $table->foreign('endereco_id')->references('id')->on('enderecos')->onDelete('restrict');
        });
    }
};
