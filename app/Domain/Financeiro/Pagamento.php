<?php

namespace App\Domain\Financeiro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pagamento extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use HasFactory, HasUuids, SoftDeletes;

    public static function newFactory()
    {
        return \Database\Factories\PagamentoFactory::new();
    }

    protected $table = 'pagamentos';

    protected $fillable = [
        'conta_pagar_id',
        'data_pagamento',
        'valor_pago',
        'valor_juros_pago',
        'valor_multa_paga',
        'valor_desconto_obtido',
        'forma_pagamento',
        'numero_transacao',
        'banco_destino',
        'agencia_destino',
        'conta_destino',
        'comprovante_url',
        'conciliado',
        'observacoes',
    ];

    protected $casts = [
        'valor_pago' => 'decimal:2',
        'valor_juros_pago' => 'decimal:2',
        'valor_multa_paga' => 'decimal:2',
        'valor_desconto_obtido' => 'decimal:2',
        'data_pagamento' => 'date',
        'forma_pagamento' => FormaCobrancaEnum::class,
        'conciliado' => 'boolean',
    ];
}
