<?php

namespace App\Domain\Identidade\Enums;

enum TipoEndereco: string
{
    case PRINCIPAL = 'PRINCIPAL';
    case ENTREGA = 'ENTREGA';
    case FATURAMENTO = 'FATURAMENTO';

    public function label(): string
    {
        return match ($this) {
            self::PRINCIPAL => 'Principal',
            self::ENTREGA => 'Entrega',
            self::FATURAMENTO => 'Faturamento',
        };
    }

    public function isPrincipal(): bool
    {
        return $this === self::PRINCIPAL;
    }
}
