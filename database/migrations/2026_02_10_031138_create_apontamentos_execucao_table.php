<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apontamentos_execucao', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ordem_servico_id')->comment('Referência à ordem');
            $table->uuid('item_ordem_id')->nullable()->comment('Item específico (opcional)');
            $table->uuid('prestador_id')->comment('Prestador (via Papel)');
            $table->date('data_apontamento')->comment('Data do apontamento');
            $table->time('hora_inicio')->comment('Hora de início');
            $table->time('hora_fim')->comment('Hora de fim');
            $table->decimal('horas_trabalhadas', 4, 2)->comment('Horas calculadas');
            $table->text('descricao_atividade')->comment('Descrição da atividade');
            $table->string('local_execucao', 255)->nullable()->comment('Local onde foi executado');
            $table->json('anexos')->nullable()->comment('Array de paths de anexos');
            $table->boolean('aprovado')->default(false)->comment('Se foi aprovado');
            $table->uuid('aprovado_por')->nullable()->comment('Quem aprovou');
            $table->timestamp('data_aprovacao')->nullable()->comment('Quando foi aprovado');
            $table->text('observacoes')->nullable()->comment('Observações do apontamento');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico')->onDelete('cascade');
            $table->foreign('item_ordem_id')->references('id')->on('itens_ordem_servico')->onDelete('cascade');
            $table->foreign('prestador_id')->references('id')->on('papeis')->onDelete('cascade');
            $table->foreign('aprovado_por')->references('id')->on('usuarios')->onDelete('set null');

            // Índices conforme especificação
            $table->index('ordem_servico_id', 'idx_apontamento_ordem_id');
            $table->index('prestador_id', 'idx_apontamento_prestador_id');
            $table->index('data_apontamento', 'idx_apontamento_data');
            $table->index('aprovado', 'idx_apontamento_aprovado');
        });

        // Comentários para documentação apenas para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("COMMENT ON TABLE apontamentos_execucao IS 'Registro detalhado de execução por prestadores'");
            DB::statement("COMMENT ON COLUMN apontamentos_execucao.horas_trabalhadas IS 'Calculado automaticamente: hora_fim - hora_inicio'");
            DB::statement("COMMENT ON COLUMN apontamentos_execucao.anexos IS 'JSON array com paths de anexos (fotos, documentos)'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('apontamentos_execucao');
    }
};
