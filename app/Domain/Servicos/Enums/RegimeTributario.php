<?php

namespace App\Domain\Servicos\Enums;

enum RegimeTributario: string
{
    case SIMPLES_NACIONAL = 'SIMPLES_NACIONAL';
    case LUCRO_PRESUMIDO = 'LUCRO_PRESUMIDO';
    case LUCRO_REAL = 'LUCRO_REAL';
    case LUCRO_ARBITRADO = 'LUCRO_ARBITRADO';

    public function label(): string
    {
        return match($this) {
            self::SIMPLES_NACIONAL => 'Simples Nacional',
            self::LUCRO_PRESUMIDO => 'Lucro Presumido',
            self::LUCRO_REAL => 'Lucro Real',
            self::LUCRO_ARBITRADO => 'Lucro Arbitrado',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public function aliquotaISSSimplificada(): float
    {
        return match($this) {
            self::SIMPLES_NACIONAL => 2.0, // Varia conforme anexo
            self::LUCRO_PRESUMIDO => 5.0, // Padrão geral
            self::LUCRO_REAL => 5.0,      // Padrão geral
            self::LUCRO_ARBITRADO => 5.0, // Padrão geral
        };
    }
}
