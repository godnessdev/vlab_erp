<?php

namespace App\Domain\OrdemServico\Enums;

enum PrioridadeOrdem: string
{
    case MUITO_BAIXA = 'MUITO_BAIXA';
    case BAIXA = 'BAIXA';
    case NORMAL = 'NORMAL';
    case ALTA = 'ALTA';
    case URGENTE = 'URGENTE';

    public function getLabel(): string
    {
        return match ($this) {
            self::MUITO_BAIXA => 'Muito Baixa',
            self::BAIXA => 'Baixa',
            self::NORMAL => 'Normal',
            self::ALTA => 'Alta',
            self::URGENTE => 'Urgente',
        };
    }

    public function getDescricao(): string
    {
        return match ($this) {
            self::MUITO_BAIXA => 'Prioridade muito baixa - execução quando não houver outras demandas',
            self::BAIXA => 'Prioridade baixa - pode ser executada quando houver disponibilidade',
            self::NORMAL => 'Prioridade normal - execução dentro do prazo padrão',
            self::ALTA => 'Prioridade alta - requer atenção prioritária',
            self::URGENTE => 'Prioridade urgente - execução imediata necessária',
        };
    }

    public function getCor(): string
    {
        return match ($this) {
            self::MUITO_BAIXA => 'gray',
            self::BAIXA => 'blue',
            self::NORMAL => 'green',
            self::ALTA => 'yellow',
            self::URGENTE => 'red',
        };
    }

    public function getPeso(): int
    {
        return match ($this) {
            self::MUITO_BAIXA => 1,
            self::BAIXA => 2,
            self::NORMAL => 3,
            self::ALTA => 4,
            self::URGENTE => 5,
        };
    }

    public function getPrazoAjuste(): int
    {
        // Retorna dias de ajuste no prazo baseado na prioridade
        return match ($this) {
            self::MUITO_BAIXA => 3, // +3 dias
            self::BAIXA => 2, // +2 dias
            self::NORMAL => 0, // sem ajuste
            self::ALTA => -1, // -1 dia
            self::URGENTE => -3, // -3 dias
        };
    }

    public function isCritica(): bool
    {
        return match ($this) {
            self::ALTA, self::URGENTE => true,
            default => false,
        };
    }

    public function getAjusteCronograma(): float
    {
        return match ($this) {
            self::MUITO_BAIXA => 2.0, // aumenta tempo em 100%
            self::BAIXA => 1.5, // aumenta tempo em 50%
            self::NORMAL => 1.0, // tempo normal
            self::ALTA => 0.8, // reduz tempo em 20%
            self::URGENTE => 0.5, // reduz tempo em 50%
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn ($case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'description' => $case->getDescricao(),
                'color' => $case->getCor(),
                'peso' => $case->getPeso(),
            ],
            self::cases()
        );
    }

    public static function ordenarPorPrioridade(array $itens, string $campo = 'prioridade'): array
    {
        usort($itens, function ($a, $b) use ($campo) {
            $prioridadeA = self::from($a[$campo] ?? self::NORMAL->value);
            $prioridadeB = self::from($b[$campo] ?? self::NORMAL->value);

            return $prioridadeB->getPeso() <=> $prioridadeA->getPeso();
        });

        return $itens;
    }
}
