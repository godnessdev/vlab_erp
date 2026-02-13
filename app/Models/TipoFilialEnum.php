<?php

namespace App\Models;

enum TipoFilialEnum: string
{
    case MATRIZ = 'MATRIZ';
    case FILIAL = 'FILIAL';

    public function getLabel(): string
    {
        return match($this) {
            self::MATRIZ => 'Matriz',
            self::FILIAL => 'Filial',
        };
    }

    public function getDescricao(): string
    {
        return match($this) {
            self::MATRIZ => 'Filial principal da empresa',
            self::FILIAL => 'Filial secundária da empresa',
        };
    }

    public static function getOptions(): array
    {
        return [
            self::MATRIZ->value => self::MATRIZ->getLabel(),
            self::FILIAL->value => self::FILIAL->getLabel(),
        ];
    }
}
