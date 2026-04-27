<?php

namespace App\Models;

enum StatusEmpresaEnum: string
{
    case ATIVO = 'ATIVO';
    case INATIVO = 'INATIVO';
    case SUSPENSO = 'SUSPENSO';

    public function getLabel(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::INATIVO => 'Inativo',
            self::SUSPENSO => 'Suspenso',
        };
    }

    public function getDescricao(): string
    {
        return match ($this) {
            self::ATIVO => 'Empresa ativa e operacional',
            self::INATIVO => 'Empresa inativa, sem operações',
            self::SUSPENSO => 'Empresa suspensa temporariamente',
        };
    }

    public function getCor(): string
    {
        return match ($this) {
            self::ATIVO => 'success',
            self::INATIVO => 'danger',
            self::SUSPENSO => 'warning',
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->getLabel(), 'cor' => $case->getCor()],
            self::cases()
        );
    }
}
