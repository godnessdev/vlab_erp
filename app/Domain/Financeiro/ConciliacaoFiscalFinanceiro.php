<?php

namespace App\Domain\Financeiro;

use Database\Factories\ConciliacaoFiscalFinanceiroFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConciliacaoFiscalFinanceiro extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use HasFactory, HasUuids, SoftDeletes;

    public static function newFactory()
    {
        return ConciliacaoFiscalFinanceiroFactory::new();
    }

    protected $table = 'conciliacoes_fiscal_financeiro';

    protected $fillable = [
        'conta_receber_id',
        'nfse_id',
        'data_conciliacao',
        'valor_nfse',
        'valor_conta_receber',
        'valor_retencoes_nfse',
        'valor_liquido_nfse',
        'discrepancia_valor',
        'status_conciliacao',
        'observacoes_discrepancia',
        'usuario_conciliacao',
    ];

    protected $casts = [
        'valor_nfse' => 'decimal:2',
        'valor_conta_receber' => 'decimal:2',
        'valor_retencoes_nfse' => 'decimal:2',
        'valor_liquido_nfse' => 'decimal:2',
        'discrepancia_valor' => 'decimal:2',
        'data_conciliacao' => 'datetime',
        'status_conciliacao' => StatusConciliacaoEnum::class,
    ];
}
