<?php

namespace App\Domain\Faturamento\Enums;

enum StatusPagamentoParcela: string
{
    case PENDENTE = 'PENDENTE';
    case PAGO = 'PAGO';
    case ATRASADO = 'ATRASADO';
    case CANCELADO = 'CANCELADO';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDENTE => 'Pendente',
            self::PAGO => 'Pago',
            self::ATRASADO => 'Atrasado',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function getDescricao(): string
    {
        return match ($this) {
            self::PENDENTE => 'Parcela aguardando pagamento',
            self::PAGO => 'Parcela paga pelo cliente',
            self::ATRASADO => 'Parcela com vencimento em atraso',
            self::CANCELADO => 'Parcela cancelada',
        };
    }

    public function getCor(): string
    {
        return match ($this) {
            self::PENDENTE => 'yellow',
            self::PAGO => 'green',
            self::ATRASADO => 'red',
            self::CANCELADO => 'gray',
        };
    }

    public function isAtivo(): bool
    {
        return ! $this->isFinal();
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::PAGO, self::CANCELADO => true,
            default => false,
        };
    }

    public function podeMarcarComoPago(): bool
    {
        return match ($this) {
            self::PENDENTE, self::ATRASADO => true,
            default => false,
        };
    }

    public function podeCancelar(): bool
    {
        return match ($this) {
            self::PENDENTE, self::ATRASADO => true,
            default => false,
        };
    }

    public function getProximosStatus(): array
    {
        return match ($this) {
            self::PENDENTE => [self::PAGO, self::ATRASADO, self::CANCELADO],
            self::ATRASADO => [self::PAGO, self::CANCELADO],
            self::PAGO => [], // Estado final
            self::CANCELADO => [], // Estado final
        };
    }

    public static function getStatusAtraso(?\DateTime $dataVencimento = null): self
    {
        if (! $dataVencimento) {
            return self::PENDENTE;
        }

        $hoje = new \DateTime;

        return $dataVencimento < $hoje ? self::ATRASADO : self::PENDENTE;
    }

    public static function getOptions(): array
    {
        return array_map(
            fn ($case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'description' => $case->getDescricao(),
                'color' => $case->getCor(),
                'is_active' => $case->isAtivo(),
                'is_final' => $case->isFinal(),
            ],
            self::cases()
        );
    }
}
