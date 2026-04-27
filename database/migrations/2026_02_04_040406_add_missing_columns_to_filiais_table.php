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
        // Verificar se as colunas existem antes de adicionar
        if (! Schema::hasColumn('filiais', 'codigo')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->string('codigo', 10)->after('empresa_id')->comment('Código único da filial dentro da empresa');
            });
        }

        if (! Schema::hasColumn('filiais', 'tipo')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->enum('tipo', ['MATRIZ', 'FILIAL'])->after('nome');
            });
        }

        if (! Schema::hasColumn('filiais', 'cnpj')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->string('cnpj', 18)->unique()->after('tipo');
            });
        }

        if (! Schema::hasColumn('filiais', 'inscricao_estadual')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->string('inscricao_estadual', 50)->nullable()->after('cnpj');
            });
        }

        if (! Schema::hasColumn('filiais', 'inscricao_municipal')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->string('inscricao_municipal', 50)->nullable()->after('inscricao_estadual');
            });
        }

        if (! Schema::hasColumn('filiais', 'email')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->string('email', 255)->nullable()->after('inscricao_municipal');
            });
        }

        if (! Schema::hasColumn('filiais', 'telefone')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->string('telefone', 20)->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn('filiais', 'ativo')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->boolean('ativo')->default(true)->after('telefone');
            });
        }

        if (! Schema::hasColumn('filiais', 'endereco')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->jsonb('endereco')->nullable()->after('ativo')->comment('Dados de endereço da filial');
            });
        }

        if (! Schema::hasColumn('filiais', 'configuracao_fiscal')) {
            Schema::table('filiais', function (Blueprint $table) {
                $table->jsonb('configuracao_fiscal')->nullable()->after('endereco')->comment('Configurações fiscais específicas da filial');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filiais', function (Blueprint $table) {
            $table->dropColumn([
                'codigo',
                'tipo',
                'cnpj',
                'inscricao_estadual',
                'inscricao_municipal',
                'email',
                'telefone',
                'ativo',
                'endereco',
                'configuracao_fiscal',
            ]);
        });
    }
};
