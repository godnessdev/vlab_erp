<?php

namespace App\Domain\Identidade\Enums;

enum StatusPessoa: string
{
    case ATIVO = 'ATIVO';
    case INATIVO = 'INATIVO';

    public function label(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::INATIVO => 'Inativo',
        };
    }

    public function isAtivo(): bool
    {
        return $this === self::ATIVO;
    }

    public function isInativo(): bool
    {
        return $this === self::INATIVO;
    }
}
