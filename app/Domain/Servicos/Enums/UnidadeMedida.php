<?php

namespace App\Domain\Servicos\Enums;

enum UnidadeMedida: string
{
    case HORA = 'HORA';
    case DIA = 'DIA';
    case PROJETO = 'PROJETO';
    case UNIDADE = 'UNIDADE';
    case MES = 'MES';
    case PERCENTUAL = 'PERCENTUAL';

    public function label(): string
    {
        return match ($this) {
            self::HORA => 'Hora',
            self::DIA => 'Dia',
            self::PROJETO => 'Projeto',
            self::UNIDADE => 'Unidade',
            self::MES => 'Mês',
            self::PERCENTUAL => 'Percentual',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
