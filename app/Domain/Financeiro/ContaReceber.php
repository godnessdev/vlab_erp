<?php

namespace App\Domain\Financeiro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContaReceber extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use HasFactory, HasUuids, SoftDeletes;

    public static function newFactory()
    {
        return \Database\Factories\ContaReceberFactory::new();
    }

    protected $table = 'contas_receber';

    protected $fillable = [
        'empresa_id',
        'fatura_id',
        'nfse_id',
        'numero_conta',
        'cliente_id',
        'valor_original',
        'valor_juros',
        'valor_multa',
        'valor_desconto',
        'valor_total',
        'valor_retencoes',
        'valor_liquido_esperado',
        'data_vencimento',
        'data_emissao',
        'status',
        'forma_cobranca',
        'observacoes',
        'data_criacao',
        'data_atualizacao',
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'valor_juros' => 'decimal:2',
        'valor_multa' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'valor_retencoes' => 'decimal:2',
        'valor_liquido_esperado' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_emissao' => 'date',
        'status' => StatusContaReceberEnum::class,
        'forma_cobranca' => FormaCobrancaEnum::class,
    ];
}
