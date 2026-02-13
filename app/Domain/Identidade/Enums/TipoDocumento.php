<?php

namespace App\Domain\Identidade\Enums;

enum TipoDocumento: string
{
    case CPF = 'CPF';
    case CNPJ = 'CNPJ';
    case RG = 'RG';
    case IE = 'IE';
    case IM = 'IM';

    public function label(): string
    {
        return match ($this) {
            self::CPF => 'CPF',
            self::CNPJ => 'CNPJ',
            self::RG => 'RG',
            self::IE => 'Inscrição Estadual',
            self::IM => 'Inscrição Municipal',
        };
    }

    public function getMask(): string
    {
        return match ($this) {
            self::CPF => '000.000.000-00',
            self::CNPJ => '00.000.000/0000-00',
            self::RG => '00.000.000-0',
            self::IE => '',
            self::IM => '',
        };
    }

    public function isPrincipal(): bool
    {
        return in_array($this, [self::CPF, self::CNPJ]);
    }

    public function isObrigatorio(): bool
    {
        return in_array($this, [self::CPF, self::CNPJ]);
    }

    public function getMaxLength(): int
    {
        return match ($this) {
            self::CPF => 11,
            self::CNPJ => 14,
            self::RG => 12,
            self::IE => 20,
            self::IM => 20,
        };
    }
}
