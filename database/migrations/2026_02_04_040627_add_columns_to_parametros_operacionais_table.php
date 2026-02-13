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
        Schema::table('parametros_operacionais', function (Blueprint $table) {
            // Adicionar novas colunas sem alterar o tipo de valor
            $table->string('tipo', 20)->default('string')->after('valor');
            $table->text('descricao')->nullable()->after('tipo');
            $table->boolean('publico')->default(false)->after('descricao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parametros_operacionais', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'descricao', 'publico']);
        });
    }
};
