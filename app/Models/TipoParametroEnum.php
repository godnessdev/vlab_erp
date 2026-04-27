<?php

namespace App\Models;

enum TipoParametroEnum: string
{
    case STRING = 'STRING';
    case INTEGER = 'INTEGER';
    case DECIMAL = 'DECIMAL';
    case BOOLEAN = 'BOOLEAN';
    case JSON = 'JSON';
    case ARRAY = 'ARRAY';

    public function getLabel(): string
    {
        return match ($this) {
            self::STRING => 'Texto',
            self::INTEGER => 'Número Inteiro',
            self::DECIMAL => 'Número Decimal',
            self::BOOLEAN => 'Verdadeiro/Falso',
            self::JSON => 'Objeto JSON',
            self::ARRAY => 'Lista de Valores',
        };
    }

    public function getDescricao(): string
    {
        return match ($this) {
            self::STRING => 'Valor textual simples',
            self::INTEGER => 'Número inteiro sem casas decimais',
            self::DECIMAL => 'Número com casas decimais',
            self::BOOLEAN => 'Valor booleano (true/false)',
            self::JSON => 'Estrutura de dados em formato JSON',
            self::ARRAY => 'Lista de valores em array',
        };
    }

    public function getValidationRule(): string
    {
        return match ($this) {
            self::STRING => 'string',
            self::INTEGER => 'integer',
            self::DECIMAL => 'numeric',
            self::BOOLEAN => 'boolean',
            self::JSON => 'json',
            self::ARRAY => 'array',
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->getLabel(), 'validation' => $case->getValidationRule()],
            self::cases()
        );
    }
}
