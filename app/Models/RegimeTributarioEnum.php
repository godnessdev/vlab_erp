<?php

namespace App\Models;

enum RegimeTributarioEnum: string
{
    case SIMPLES_NACIONAL = 'SIMPLES_NACIONAL';
    case LUCRO_PRESUMIDO = 'LUCRO_PRESUMIDO';
    case LUCRO_REAL = 'LUCRO_REAL';

    public function getLabel(): string
    {
        return match ($this) {
            self::SIMPLES_NACIONAL => 'Simples Nacional',
            self::LUCRO_PRESUMIDO => 'Lucro Presumido',
            self::LUCRO_REAL => 'Lucro Real',
        };
    }

    public function getDescricao(): string
    {
        return match ($this) {
            self::SIMPLES_NACIONAL => 'Regime tributário simplificado para micro e pequenas empresas',
            self::LUCRO_PRESUMIDO => 'Regime baseado em presunção de lucro sobre receita bruta',
            self::LUCRO_REAL => 'Regime baseado no lucro líquido efetivo apurado',
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->getLabel()],
            self::cases()
        );
    }
}
