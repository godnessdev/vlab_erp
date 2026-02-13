<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('xml_nfse', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('nfse_id')->unique();
            $table->string('versao_schema', 10);
            $table->text('xml_assinado');
            $table->text('xml_original')->nullable();
            $table->string('hash_sha256', 64);
            $table->integer('tamanho_bytes');
            $table->boolean('comprimido')->default(false);
            $table->timestamp('data_armazenamento');
            $table->index('nfse_id', 'idx_xml_nfse_id');
            $table->index('hash_sha256', 'idx_xml_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xml_nfse');
    }
};
