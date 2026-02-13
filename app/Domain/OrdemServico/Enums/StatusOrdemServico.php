<?php

namespace App\Domain\OrdemServico\Enums;

enum StatusOrdemServico: string
{
    case ABERTA = 'ABERTA';
    case EM_ANDAMENTO = 'EM_ANDAMENTO';
    case PAUSADA = 'PAUSADA';
    case CONCLUIDA = 'CONCLUIDA';
    case FATURADA = 'FATURADA';
    case CANCELADA = 'CANCELADA';

    public function getLabel(): string
    {
        return match($this) {
            self::ABERTA => 'Aberta',
            self::EM_ANDAMENTO => 'Em Andamento',
            self::PAUSADA => 'Pausada',
            self::CONCLUIDA => 'Concluída',
            self::FATURADA => 'Faturada',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function getDescricao(): string
    {
        return match($this) {
            self::ABERTA => 'Ordem criada, aguardando início da execução',
            self::EM_ANDAMENTO => 'Ordem em execução pelos prestadores',
            self::PAUSADA => 'Ordem temporariamente pausada',
            self::CONCLUIDA => 'Ordem concluída, pronta para faturamento',
            self::FATURADA => 'Ordem faturada para o cliente',
            self::CANCELADA => 'Ordem cancelada',
        };
    }

    public function getCor(): string
    {
        return match($this) {
            self::ABERTA => 'blue',
            self::EM_ANDAMENTO => 'green',
            self::PAUSADA => 'yellow',
            self::CONCLUIDA => 'purple',
            self::FATURADA => 'indigo',
            self::CANCELADA => 'red',
        };
    }

    public function podeTransicionarPara(StatusOrdemServico $novoStatus): bool
    {
        return match($this) {
            self::ABERTA => in_array($novoStatus, [self::EM_ANDAMENTO, self::CANCELADA]),
            self::EM_ANDAMENTO => in_array($novoStatus, [self::PAUSADA, self::CONCLUIDA, self::CANCELADA]),
            self::PAUSADA => in_array($novoStatus, [self::EM_ANDAMENTO, self::CANCELADA]),
            self::CONCLUIDA => in_array($novoStatus, [self::FATURADA]),
            self::FATURADA => false, // Estado final
            self::CANCELADA => false, // Estado final
        };
    }

    public function isAtiva(): bool
    {
        return !in_array($this, [self::FATURADA, self::CANCELADA]);
    }

    public function isPodeAlterar(): bool
    {
        return in_array($this, [self::ABERTA, self::EM_ANDAMENTO, self::PAUSADA]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::FATURADA, self::CANCELADA]);
    }

    public function getProximosStatus(): array
    {
        return match($this) {
            self::ABERTA => [self::EM_ANDAMENTO, self::CANCELADA],
            self::EM_ANDAMENTO => [self::PAUSADA, self::CONCLUIDA, self::CANCELADA],
            self::PAUSADA => [self::EM_ANDAMENTO, self::CANCELADA],
            self::CONCLUIDA => [self::FATURADA],
            self::FATURADA => [], // Estado final
            self::CANCELADA => [], // Estado final
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'description' => $case->getDescricao(),
                'color' => $case->getCor(),
            ],
            self::cases()
        );
    }

    public static function getStatusAtivos(): array
    {
        return array_filter(
            self::cases(),
            fn($status) => $status->isAtiva()
        );
    }
}
