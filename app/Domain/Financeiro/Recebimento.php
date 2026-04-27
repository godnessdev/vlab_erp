<?php

namespace App\Domain\Financeiro;

use Database\Factories\RecebimentoFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recebimento extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use HasFactory, HasUuids, SoftDeletes;

    public static function newFactory()
    {
        return RecebimentoFactory::new();
    }

    protected $table = 'recebimentos';

    protected $fillable = [
        'conta_receber_id',
        'data_recebimento',
        'valor_recebido',
        'valor_juros_recebido',
        'valor_multa_recebida',
        'valor_desconto_concedido',
        'forma_recebimento',
        'numero_transacao',
        'banco_origem',
        'agencia_origem',
        'conta_origem',
        'comprovante_url',
        'conciliado',
        'data_conciliacao',
        'observacoes',
    ];

    protected $casts = [
        'valor_recebido' => 'decimal:2',
        'valor_juros_recebido' => 'decimal:2',
        'valor_multa_recebida' => 'decimal:2',
        'valor_desconto_concedido' => 'decimal:2',
        'data_recebimento' => 'date',
        'forma_recebimento' => FormaCobrancaEnum::class,
        'conciliado' => 'boolean',
        'data_conciliacao' => 'date',
    ];
}
