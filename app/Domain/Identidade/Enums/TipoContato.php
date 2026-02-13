<?php

namespace App\Domain\Identidade\Enums;

enum TipoContato: string
{
    case EMAIL = 'EMAIL';
    case TELEFONE_FIXO = 'TELEFONE_FIXO';
    case CELULAR = 'CELULAR';
    case WHATSAPP = 'WHATSAPP';

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'E-mail',
            self::TELEFONE_FIXO => 'Telefone Fixo',
            self::CELULAR => 'Celular',
            self::WHATSAPP => 'WhatsApp',
        };
    }

    public function isEmail(): bool
    {
        return $this === self::EMAIL;
    }

    public function isTelefone(): bool
    {
        return in_array($this, [self::TELEFONE_FIXO, self::CELULAR, self::WHATSAPP]);
    }

    public function getMask(): string
    {
        return match ($this) {
            self::EMAIL => '',
            self::TELEFONE_FIXO => '(00) 0000-0000',
            self::CELULAR, self::WHATSAPP => '(00) 00000-0000',
        };
    }
}
