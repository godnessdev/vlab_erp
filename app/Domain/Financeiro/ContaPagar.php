<?php

namespace App\Domain\Financeiro;

use Database\Factories\ContaPagarFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContaPagar extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use HasFactory, HasUuids, SoftDeletes;

    public static function newFactory()
    {
        return ContaPagarFactory::new();
    }

    protected $table = 'contas_pagar';

    protected $fillable = [
        'empresa_id',
        'fornecedor_id',
        'numero_conta',
        'descricao',
        'categoria',
        'valor_original',
        'valor_juros',
        'valor_multa',
        'valor_desconto',
        'valor_total',
        'data_vencimento',
        'data_emissao',
        'status',
        'centro_custo',
        'numero_documento',
        'observacoes',
        'data_criacao',
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'valor_juros' => 'decimal:2',
        'valor_multa' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_emissao' => 'date',
        'status' => StatusContaPagarEnum::class,
        'categoria' => CategoriaContaPagarEnum::class,
    ];
}
