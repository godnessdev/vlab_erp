<?php

namespace App\Domain\Servicos\Enums;

enum StatusServico: string
{
    case ATIVO = 'ATIVO';
    case INATIVO = 'INATIVO';
    case DESCONTINUADO = 'DESCONTINUADO';

    public function label(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::INATIVO => 'Inativo',
            self::DESCONTINUADO => 'Descontinuado',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public function podeSerUsado(): bool
    {
        return $this === self::ATIVO;
    }
}
