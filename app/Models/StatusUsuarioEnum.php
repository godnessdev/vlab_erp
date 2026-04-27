<?php

namespace App\Models;

enum StatusUsuarioEnum: string
{
    case ATIVO = 'ATIVO';
    case INATIVO = 'INATIVO';
    case BLOQUEADO = 'BLOQUEADO';
    case PENDENTE = 'PENDENTE';

    public function getLabel(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::INATIVO => 'Inativo',
            self::BLOQUEADO => 'Bloqueado',
            self::PENDENTE => 'Pendente',
        };
    }

    public function getDescricao(): string
    {
        return match ($this) {
            self::ATIVO => 'Usuário ativo e pode acessar o sistema',
            self::INATIVO => 'Usuário inativo, sem acesso ao sistema',
            self::BLOQUEADO => 'Usuário bloqueado por segurança',
            self::PENDENTE => 'Usuário pendente de ativação',
        };
    }

    public function getCor(): string
    {
        return match ($this) {
            self::ATIVO => 'success',
            self::INATIVO => 'secondary',
            self::BLOQUEADO => 'danger',
            self::PENDENTE => 'warning',
        };
    }

    public function getIcone(): string
    {
        return match ($this) {
            self::ATIVO => 'check-circle',
            self::INATIVO => 'x-circle',
            self::BLOQUEADO => 'lock',
            self::PENDENTE => 'clock',
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn ($case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'cor' => $case->getCor(),
                'icone' => $case->getIcone(),
            ],
            self::cases()
        );
    }
}
