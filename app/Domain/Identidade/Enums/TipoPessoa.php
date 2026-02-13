<?php

namespace App\Domain\Identidade\Enums;

enum TipoPessoa: string
{
    case FISICA = 'FISICA';
    case JURIDICA = 'JURIDICA';

    public function label(): string
    {
        return match ($this) {
            self::FISICA => 'Pessoa Física',
            self::JURIDICA => 'Pessoa Jurídica',
        };
    }

    public function documentoPrincipal(): TipoDocumento
    {
        return match ($this) {
            self::FISICA => TipoDocumento::CPF,
            self::JURIDICA => TipoDocumento::CNPJ,
        };
    }

    public function isJuridica(): bool
    {
        return $this === self::JURIDICA;
    }

    public function isFisica(): bool
    {
        return $this === self::FISICA;
    }
}
