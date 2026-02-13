<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificado_digital', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->string('alias', 100);
            $table->binary('arquivo_pfx');
            $table->string('senha', 255);
            $table->string('subject', 500);
            $table->string('issuer', 500);
            $table->string('serial_number', 50);
            $table->timestamp('data_validade_inicio');
            $table->timestamp('data_validade_fim');
            $table->boolean('ativo')->default(true);
            $table->timestamp('data_criacao');
            $table->index('empresa_id', 'idx_certificado_empresa_id');
            $table->index('data_validade_fim', 'idx_certificado_validade');
            $table->index('ativo', 'idx_certificado_ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificado_digital');
    }
};
