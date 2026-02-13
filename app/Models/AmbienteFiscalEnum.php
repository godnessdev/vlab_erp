<?php

namespace App\Models;

enum AmbienteFiscalEnum: string
{
    case PRODUCAO = 'PRODUCAO';
    case HOMOLOGACAO = 'HOMOLOGACAO';

    public function getLabel(): string
    {
        return match($this) {
            self::PRODUCAO => 'Produção',
            self::HOMOLOGACAO => 'Homologação',
        };
    }

    public function getDescricao(): string
    {
        return match($this) {
            self::PRODUCAO => 'Ambiente de produção para emissão real de documentos fiscais',
            self::HOMOLOGACAO => 'Ambiente de testes para validação e homologação',
        };
    }

    public function getCor(): string
    {
        return match($this) {
            self::PRODUCAO => 'success',
            self::HOMOLOGACAO => 'warning',
        };
    }

    public function getIcone(): string
    {
        return match($this) {
            self::PRODUCAO => 'check-circle',
            self::HOMOLOGACAO => 'test-tube',
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value, 
                'label' => $case->getLabel(), 
                'cor' => $case->getCor(),
                'icone' => $case->getIcone()
            ],
            self::cases()
        );
    }
}
