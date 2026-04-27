<?php

namespace App\Domain\OrdemServico\Enums;

enum StatusItemOrdem: string
{
    case PENDENTE = 'PENDENTE';
    case EM_EXECUCAO = 'EM_EXECUCAO';
    case CONCLUIDO = 'CONCLUIDO';
    case CANCELADO = 'CANCELADO';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDENTE => 'Pendente',
            self::EM_EXECUCAO => 'Em Execução',
            self::CONCLUIDO => 'Concluído',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function getDescricao(): string
    {
        return match ($this) {
            self::PENDENTE => 'Item aguardando início da execução',
            self::EM_EXECUCAO => 'Item sendo executado',
            self::CONCLUIDO => 'Item totalmente executado',
            self::CANCELADO => 'Item cancelado',
        };
    }

    public function getCor(): string
    {
        return match ($this) {
            self::PENDENTE => 'gray',
            self::EM_EXECUCAO => 'blue',
            self::CONCLUIDO => 'green',
            self::CANCELADO => 'red',
        };
    }

    public function podeTransicionarPara(StatusItemOrdem $novoStatus): bool
    {
        return match ($this) {
            self::PENDENTE => in_array($novoStatus, [self::EM_EXECUCAO, self::CANCELADO]),
            self::EM_EXECUCAO => in_array($novoStatus, [self::CONCLUIDO, self::CANCELADO]),
            self::CONCLUIDO => false, // Estado final
            self::CANCELADO => false, // Estado final
        };
    }

    public function isAtivo(): bool
    {
        return in_array($this, [self::PENDENTE, self::EM_EXECUCAO]);
    }

    public function isExecutavel(): bool
    {
        return $this === self::EM_EXECUCAO;
    }

    public static function getOptions(): array
    {
        return array_map(
            fn ($case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'description' => $case->getDescricao(),
                'color' => $case->getCor(),
            ],
            self::cases()
        );
    }
}
