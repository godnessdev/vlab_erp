<?php

namespace App\Domain\Faturamento\Models;

use App\Domain\Faturamento\Enums\StatusFatura;
use App\Models\Empresa;
use App\Models\Usuario;
use Database\Factories\FaturaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fatura extends Model
{
    protected static function newFactory()
    {
        return FaturaFactory::new();
    }

    use HasFactory, HasUuids;

    protected $table = 'faturas';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'numero_fatura',
        'data_emissao',
        'data_vencimento',
        'mes_referencia',
        'valor_servicos',
        'valor_deducoes',
        'valor_descontos',
        'base_calculo_iss',
        'aliquota_iss',
        'valor_iss',
        'valor_retencoes',
        'valor_total',
        'valor_liquido',
        'status',
        'regras_cobranca',
        'observacoes',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
        'mes_referencia' => 'date',
        'valor_servicos' => 'decimal:2',
        'valor_deducoes' => 'decimal:2',
        'valor_descontos' => 'decimal:2',
        'base_calculo_iss' => 'decimal:2',
        'aliquota_iss' => 'decimal:2',
        'valor_iss' => 'decimal:2',
        'valor_retencoes' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'valor_liquido' => 'decimal:2',
        'status' => StatusFatura::class,
        'regras_cobranca' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fatura) {
            if (empty($fatura->numero_fatura)) {
                $fatura->numero_fatura = static::gerarProximoNumero($fatura->empresa_id);
            }
        });
    }

    // Relacionamentos
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cliente_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemFatura::class)->orderBy('sequencia');
    }

    public function parcelas(): HasMany
    {
        return $this->hasMany(ParcelaFatura::class)->orderBy('numero_parcela');
    }

    // Métodos de negócio
    public function isEditavel(): bool
    {
        return $this->status->podeEditar();
    }

    public function podeCancelar(): bool
    {
        return $this->status->podeCancelar();
    }

    public function isVencida(): bool
    {
        return $this->data_vencimento < now()->toDateString()
            && $this->status !== StatusFatura::PAGA;
    }

    public function getDiasAtraso(): int
    {
        if (! $this->isVencida()) {
            return 0;
        }

        return now()->diffInDays($this->data_vencimento);
    }

    public function getPercentualDesconto(): float
    {
        if ($this->valor_servicos == 0) {
            return 0;
        }

        return ($this->valor_descontos / $this->valor_servicos) * 100;
    }

    public function getAliquotaISSEfetiva(): float
    {
        if ($this->base_calculo_iss == 0) {
            return 0;
        }

        return ($this->valor_iss / $this->base_calculo_iss) * 100;
    }

    public function calcularTotais(): void
    {
        $this->valor_total = $this->valor_servicos - $this->valor_deducoes - $this->valor_descontos;
        $this->valor_liquido = $this->valor_total - $this->valor_retencoes;
        $this->base_calculo_iss = $this->valor_servicos - $this->valor_deducoes;
        $this->valor_iss = $this->base_calculo_iss * ($this->aliquota_iss / 100);
    }

    public function getQuantidadeParcelas(): int
    {
        return $this->parcelas()->count();
    }

    public function getParcelasVencidas(): int
    {
        return $this->parcelas()
            ->where('data_vencimento', '<', now()->toDateString())
            ->where('status_pagamento', '!=', 'PAGO')
            ->count();
    }

    public function getValorPago(): float
    {
        return $this->parcelas()
            ->where('status_pagamento', 'PAGO')
            ->sum('valor_pago') ?? 0;
    }

    public function getValorPendente(): float
    {
        return $this->valor_liquido - $this->getValorPago();
    }

    public function getPercentualPago(): float
    {
        if ($this->valor_liquido == 0) {
            return 0;
        }

        return ($this->getValorPago() / $this->valor_liquido) * 100;
    }

    // Método estático para gerar próximo número
    public static function gerarProximoNumero(string $empresaId): string
    {
        $ultimaFatura = static::where('empresa_id', $empresaId)
            ->orderByDesc('numero_fatura')
            ->first();

        if (! $ultimaFatura) {
            return str_pad('1', 6, '0', STR_PAD_LEFT);
        }

        $ultimoNumero = intval($ultimaFatura->numero_fatura);

        return str_pad($ultimoNumero + 1, 6, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->whereIn('status', [StatusFatura::ABERTA, StatusFatura::ENVIADA]);
    }

    public function scopeVencidas($query)
    {
        return $query->where('data_vencimento', '<', now()->toDateString())
            ->where('status', '!=', StatusFatura::PAGA);
    }

    public function scopePorMesReferencia($query, int $ano, int $mes)
    {
        return $query->whereYear('mes_referencia', $ano)
            ->whereMonth('mes_referencia', $mes);
    }

    public function scopePorCliente($query, string $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    // Mutators
    public function setNumeroFaturaAttribute($value)
    {
        $this->attributes['numero_fatura'] = str_pad($value, 6, '0', STR_PAD_LEFT);
    }

    // Accessors
    public function getNumeroFaturaFormatadoAttribute(): string
    {
        return 'FAT-'.$this->numero_fatura;
    }
}
