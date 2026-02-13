<?php

namespace App\Domain\Faturamento\Enums;

enum StatusFatura: string
{
    case ABERTA = 'ABERTA';
    case ENVIADA = 'ENVIADA';
    case PAGA = 'PAGA';
    case CANCELADA = 'CANCELADA';

    public function getLabel(): string
    {
        return match($this) {
            self::ABERTA => 'Aberta',
            self::ENVIADA => 'Enviada',
            self::PAGA => 'Paga',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function getDescricao(): string
    {
        return match($this) {
            self::ABERTA => 'Fatura criada, aguardando processamento',
            self::ENVIADA => 'Fatura enviada ao cliente, aguardando pagamento',
            self::PAGA => 'Fatura paga pelo cliente',
            self::CANCELADA => 'Fatura cancelada, não será cobrada',
        };
    }

    public function getCor(): string
    {
        return match($this) {
            self::ABERTA => 'blue',
            self::ENVIADA => 'yellow',
            self::PAGA => 'green',
            self::CANCELADA => 'red',
        };
    }

    public function isAtiva(): bool
    {
        return !$this->isFinal();
    }

    public function isFinal(): bool
    {
        return match($this) {
            self::PAGA, self::CANCELADA => true,
            default => false,
        };
    }

    public function podeTransicionarPara(StatusFatura $novoStatus): bool
    {
        return in_array($novoStatus, $this->getProximosStatus());
    }

    public function getProximosStatus(): array
    {
        return match($this) {
            self::ABERTA => [self::ENVIADA, self::CANCELADA],
            self::ENVIADA => [self::PAGA, self::CANCELADA],
            self::PAGA => [], // Estado final
            self::CANCELADA => [], // Estado final
        };
    }

    public function podeEditar(): bool
    {
        return match($this) {
            self::ABERTA => true,
            default => false,
        };
    }

    public function podeCancelar(): bool
    {
        return match($this) {
            self::ABERTA, self::ENVIADA => true,
            default => false,
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'description' => $case->getDescricao(),
                'color' => $case->getCor(),
                'is_active' => $case->isAtiva(),
                'is_final' => $case->isFinal(),
            ],
            self::cases()
        );
    }
}
