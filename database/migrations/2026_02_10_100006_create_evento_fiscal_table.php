<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evento_fiscal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('nfse_id');
            $table->enum('tipo_evento', ['CANCELAMENTO','SUBSTITUICAO']);
            $table->timestamp('data_evento');
            $table->text('motivo');
            $table->uuid('usuario_id');
            $table->uuid('nfse_substituta_id')->nullable();
            $table->string('protocolo_autorizacao', 50)->nullable();
            $table->text('xml_evento')->nullable();
            $table->index('nfse_id', 'idx_evento_nfse_id');
            $table->index('data_evento', 'idx_evento_data');
            $table->index('tipo_evento', 'idx_evento_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_fiscal');
    }
};
