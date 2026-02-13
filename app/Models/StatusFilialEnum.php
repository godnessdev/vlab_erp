<?php

namespace App\Models;

enum StatusFilialEnum: string
{
    case ATIVO = 'ATIVO';
    case INATIVO = 'INATIVO';

    public function getLabel(): string
    {
        return match($this) {
            self::ATIVO => 'Ativo',
            self::INATIVO => 'Inativo',
        };
    }

    public function getDescricao(): string
    {
        return match($this) {
            self::ATIVO => 'Filial ativa e operacional',
            self::INATIVO => 'Filial inativa, sem operações',
        };
    }

    public function getCor(): string
    {
        return match($this) {
            self::ATIVO => 'success',
            self::INATIVO => 'danger',
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->getLabel(), 'cor' => $case->getCor()],
            self::cases()
        );
    }
}
