<?php

namespace App\Domain\Faturamento\Models;

use App\Domain\OrdemServico\Models\OrdemServico;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ItemFatura extends Model
{
    use HasUuids;

    protected $table = 'item_faturas';

    protected $fillable = [
        'fatura_id',
        'ordem_servico_id',
        'item_ordem_id',
        'sequencia',
        'descricao',
        'quantidade',
        'unidade_medida',
        'preco_unitario',
        'desconto_percentual',
        'desconto_valor',
        'subtotal',
        'codigo_servico_municipal',
        'aliquota_iss_item',
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'preco_unitario' => 'decimal:2',
        'desconto_percentual' => 'decimal:2',
        'desconto_valor' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'aliquota_iss_item' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($item) {
            if (empty($item->sequencia)) {
                $item->sequencia = static::proximaSequencia($item->fatura_id);
            }
        });

        static::saving(function ($item) {
            $item->calcularSubtotal();
        });
    }

    // Relacionamentos
    public function fatura(): BelongsTo
    {
        return $this->belongsTo(Fatura::class);
    }

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class);
    }

    // Métodos de negócio
    public function calcularSubtotal(): void
    {
        $valorBruto = $this->quantidade * $this->preco_unitario;
        
        $desconto = 0;
        if ($this->desconto_percentual > 0) {
            $desconto = $valorBruto * ($this->desconto_percentual / 100);
        } else {
            $desconto = $this->desconto_valor ?? 0;
        }
        
        $this->subtotal = $valorBruto - $desconto;
    }

    public function getValorBruto(): float
    {
        return $this->quantidade * $this->preco_unitario;
    }

    public function getDescontoAplicado(): float
    {
        $valorBruto = $this->getValorBruto();
        
        if ($this->desconto_percentual > 0) {
            return $valorBruto * ($this->desconto_percentual / 100);
        }
        
        return $this->desconto_valor ?? 0;
    }

    public function getPercentualDesconto(): float
    {
        $valorBruto = $this->getValorBruto();
        
        if ($valorBruto == 0) {
            return 0;
        }
        
        return ($this->getDescontoAplicado() / $valorBruto) * 100;
    }

    public function getValorISSItem(): float
    {
        return $this->subtotal * ($this->aliquota_iss_item / 100);
    }

    public function temDesconto(): bool
    {
        return $this->desconto_percentual > 0 || $this->desconto_valor > 0;
    }

    // Método estático para próxima sequência
    public static function proximaSequencia(string $faturaId): int
    {
        $ultimoItem = static::where('fatura_id', $faturaId)
            ->orderByDesc('sequencia')
            ->first();

        return $ultimoItem ? $ultimoItem->sequencia + 1 : 1;
    }

    // Scopes
    public function scopePorFatura($query, string $faturaId)
    {
        return $query->where('fatura_id', $faturaId);
    }

    public function scopePorOrdemServico($query, string $ordemServicoId)
    {
        return $query->where('ordem_servico_id', $ordemServicoId);
    }

    public function scopeComDesconto($query)
    {
        return $query->where(function ($q) {
            $q->where('desconto_percentual', '>', 0)
              ->orWhere('desconto_valor', '>', 0);
        });
    }

    // Mutators
    public function setDescricaoAttribute($value)
    {
        $this->attributes['descricao'] = trim($value);
    }

    public function setUnidadeMedidaAttribute($value)
    {
        $this->attributes['unidade_medida'] = strtoupper(trim($value));
    }

    // Accessors
    public function getValorUnitarioFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->preco_unitario, 2, ',', '.');
    }

    public function getSubtotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->subtotal, 2, ',', '.');
    }

    public function getQuantidadeFormatadaAttribute(): string
    {
        return number_format($this->quantidade, 2, ',', '.') . ' ' . $this->unidade_medida;
    }
}
