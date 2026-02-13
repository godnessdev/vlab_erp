<?php

namespace App\Domain\OrdemServico\Enums;

enum TipoEventoHistorico: string
{
    case CRIACAO = 'CRIACAO';
    case ALTERACAO = 'ALTERACAO';
    case STATUS_CHANGE = 'STATUS_CHANGE';
    case APROVACAO = 'APROVACAO';
    case APONTAMENTO = 'APONTAMENTO';
    case ANEXO = 'ANEXO';

    public function getLabel(): string
    {
        return match($this) {
            self::CRIACAO => 'Criação',
            self::ALTERACAO => 'Alteração',
            self::STATUS_CHANGE => 'Mudança de Status',
            self::APROVACAO => 'Aprovação',
            self::APONTAMENTO => 'Apontamento',
            self::ANEXO => 'Anexo',
        };
    }

    public function getDescricao(): string
    {
        return match($this) {
            self::CRIACAO => 'Ordem de serviço foi criada',
            self::ALTERACAO => 'Dados da ordem foram alterados',
            self::STATUS_CHANGE => 'Status da ordem foi alterado',
            self::APROVACAO => 'Ordem foi aprovada ou rejeitada',
            self::APONTAMENTO => 'Apontamento de execução realizado',
            self::ANEXO => 'Anexo adicionado ou removido',
        };
    }

    public function getIcone(): string
    {
        return match($this) {
            self::CRIACAO => 'plus-circle',
            self::ALTERACAO => 'edit',
            self::STATUS_CHANGE => 'arrow-right',
            self::APROVACAO => 'check-circle',
            self::APONTAMENTO => 'clock',
            self::ANEXO => 'paperclip',
        };
    }

    public function getCor(): string
    {
        return match($this) {
            self::CRIACAO => 'blue',
            self::ALTERACAO => 'yellow',
            self::STATUS_CHANGE => 'purple',
            self::APROVACAO => 'green',
            self::APONTAMENTO => 'orange',
            self::ANEXO => 'gray',
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'description' => $case->getDescricao(),
                'icon' => $case->getIcone(),
                'color' => $case->getCor(),
            ],
            self::cases()
        );
    }
}
