<?php

namespace App\Domain\Financeiro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FluxoCaixa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use HasFactory, HasUuids, SoftDeletes;

    public static function newFactory()
    {
        return \Database\Factories\FluxoCaixaFactory::new();
    }

    protected $table = 'fluxo_caixa';

    protected $fillable = [
        'empresa_id',
        'data_referencia',
        'tipo_movimento',
        'categoria',
        'valor',
        'descricao',
        'conta_receber_id',
        'conta_pagar_id',
        'realizado',
        'data_realizacao',
        'saldo_acumulado',
    ];

    protected $casts = [
        'data_referencia' => 'date',
        'tipo_movimento' => TipoMovimentoEnum::class,
        'categoria' => CategoriaFluxoCaixaEnum::class,
        'valor' => 'decimal:2',
        'realizado' => 'boolean',
        'data_realizacao' => 'date',
        'saldo_acumulado' => 'decimal:2',
    ];
}
