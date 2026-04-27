<?php

namespace App\Domain\Identidade\Enums;

enum TipoPapel: string
{
    case CLIENTE = 'CLIENTE';
    case PRESTADOR = 'PRESTADOR';
    case FUNCIONARIO = 'FUNCIONARIO';
    case FORNECEDOR = 'FORNECEDOR';
    case CONTADOR = 'CONTADOR';

    public function label(): string
    {
        return match ($this) {
            self::CLIENTE => 'Cliente',
            self::PRESTADOR => 'Prestador',
            self::FUNCIONARIO => 'Funcionário',
            self::FORNECEDOR => 'Fornecedor',
            self::CONTADOR => 'Contador',
        };
    }

    public function getPermissions(): array
    {
        return match ($this) {
            self::CLIENTE => ['view_services', 'create_orders'],
            self::PRESTADOR => ['manage_services', 'view_orders', 'manage_invoices'],
            self::FUNCIONARIO => ['access_internal_system'],
            self::FORNECEDOR => ['manage_supplies'],
            self::CONTADOR => ['access_fiscal', 'manage_taxes'],
        };
    }

    public function isInterno(): bool
    {
        return in_array($this, [self::FUNCIONARIO, self::CONTADOR]);
    }

    public function isExterno(): bool
    {
        return ! $this->isInterno();
    }
}
