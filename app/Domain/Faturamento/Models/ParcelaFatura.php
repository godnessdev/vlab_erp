<?php

namespace App\Domain\Faturamento\Models;

use App\Domain\Faturamento\Enums\StatusPagamentoParcela;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParcelaFatura extends Model
{
    use HasUuids;

    protected $table = 'parcela_faturas';

    protected $fillable = [
        'fatura_id',
        'numero_parcela',
        'data_vencimento',
        'valor_parcela',
        'valor_juros',
        'valor_total_parcela',
        'status_pagamento',
        'data_pagamento',
        'valor_pago',
        'forma_pagamento',
        'observacoes_pagamento',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor_parcela' => 'decimal:2',
        'valor_juros' => 'decimal:2',
        'valor_total_parcela' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'status_pagamento' => StatusPagamentoParcela::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($parcela) {
            if (empty($parcela->numero_parcela)) {
                $parcela->numero_parcela = static::proximoNumero($parcela->fatura_id);
            }
        });

        static::saving(function ($parcela) {
            $parcela->calcularValorTotal();
            $parcela->atualizarStatusAtraso();
        });
    }

    // Relacionamentos
    public function fatura(): BelongsTo
    {
        return $this->belongsTo(Fatura::class);
    }

    // Métodos de negócio
    public function calcularValorTotal(): void
    {
        $this->valor_total_parcela = $this->valor_parcela + ($this->valor_juros ?? 0);
    }

    public function atualizarStatusAtraso(): void
    {
        if ($this->status_pagamento === StatusPagamentoParcela::PENDENTE) {
            if ($this->data_vencimento < now()->toDateString()) {
                $this->status_pagamento = StatusPagamentoParcela::ATRASADO;
            }
        }
    }

    public function marcarComoPaga(float $valorPago, ?string $formaPagamento = null, ?\DateTime $dataPagamento = null): void
    {
        $this->status_pagamento = StatusPagamentoParcela::PAGO;
        $this->valor_pago = $valorPago;
        $this->forma_pagamento = $formaPagamento;
        $this->data_pagamento = $dataPagamento ?? now();
        $this->save();
    }

    public function cancelar(): void
    {
        if ($this->status_pagamento->podeCancelar()) {
            $this->status_pagamento = StatusPagamentoParcela::CANCELADO;
            $this->save();
        }
    }

    public function isVencida(): bool
    {
        return $this->data_vencimento < now()->toDateString()
            && $this->status_pagamento !== StatusPagamentoParcela::PAGO;
    }

    public function getDiasAtraso(): int
    {
        if (! $this->isVencida()) {
            return 0;
        }

        return now()->diffInDays($this->data_vencimento);
    }

    public function isPaga(): bool
    {
        return $this->status_pagamento === StatusPagamentoParcela::PAGO;
    }

    public function isPendente(): bool
    {
        return in_array($this->status_pagamento, [
            StatusPagamentoParcela::PENDENTE,
            StatusPagamentoParcela::ATRASADO,
        ]);
    }

    public function calcularJurosMora(float $percentualJurosDia = 0.03): float
    {
        if (! $this->isVencida()) {
            return 0;
        }

        $diasAtraso = $this->getDiasAtraso();

        return $this->valor_parcela * ($percentualJurosDia / 100) * $diasAtraso;
    }

    public function calcularMulta(float $percentualMulta = 2.0): float
    {
        if (! $this->isVencida()) {
            return 0;
        }

        return $this->valor_parcela * ($percentualMulta / 100);
    }

    public function getValorComJurosEMulta(float $percentualJurosDia = 0.03, float $percentualMulta = 2.0): float
    {
        $valorBase = $this->valor_total_parcela;
        $juros = $this->calcularJurosMora($percentualJurosDia);
        $multa = $this->calcularMulta($percentualMulta);

        return $valorBase + $juros + $multa;
    }

    public function getDiasParaVencimento(): int
    {
        return now()->diffInDays($this->data_vencimento, false);
    }

    public function isProximaAoVencimento(int $diasAntecedencia = 3): bool
    {
        $diasParaVencer = $this->getDiasParaVencimento();

        return $diasParaVencer >= 0 && $diasParaVencer <= $diasAntecedencia;
    }

    // Método estático para próximo número
    public static function proximoNumero(string $faturaId): int
    {
        $ultimaParcela = static::where('fatura_id', $faturaId)
            ->orderByDesc('numero_parcela')
            ->first();

        return $ultimaParcela ? $ultimaParcela->numero_parcela + 1 : 1;
    }

    // Scopes
    public function scopeVencidas($query)
    {
        return $query->where('data_vencimento', '<', now()->toDateString())
            ->where('status_pagamento', '!=', StatusPagamentoParcela::PAGO);
    }

    public function scopePendentes($query)
    {
        return $query->whereIn('status_pagamento', [
            StatusPagamentoParcela::PENDENTE,
            StatusPagamentoParcela::ATRASADO,
        ]);
    }

    public function scopePagas($query)
    {
        return $query->where('status_pagamento', StatusPagamentoParcela::PAGO);
    }

    public function scopeProximasAoVencimento($query, int $dias = 3)
    {
        return $query->whereBetween('data_vencimento', [
            now()->toDateString(),
            now()->addDays($dias)->toDateString(),
        ])->where('status_pagamento', StatusPagamentoParcela::PENDENTE);
    }

    public function scopePorPeriodo($query, \DateTime $inicio, \DateTime $fim)
    {
        return $query->whereBetween('data_vencimento', [
            $inicio->format('Y-m-d'),
            $fim->format('Y-m-d'),
        ]);
    }

    // Accessors
    public function getNumeroParcelaFormatadoAttribute(): string
    {
        return str_pad($this->numero_parcela, 2, '0', STR_PAD_LEFT);
    }

    public function getValorTotalFormatadoAttribute(): string
    {
        return 'R$ '.number_format($this->valor_total_parcela, 2, ',', '.');
    }

    public function getDataVencimentoFormatadaAttribute(): string
    {
        return $this->data_vencimento->format('d/m/Y');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status_pagamento->getLabel();
    }
}
