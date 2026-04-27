<?php

namespace App\Domain\Faturamento\Enums;

enum TipoParcelamento: string
{
    case A_VISTA = 'A_VISTA';
    case FIXO = 'FIXO';
    case VARIAVEL = 'VARIAVEL';
    case PERSONALIZADO = 'PERSONALIZADO';

    public function getLabel(): string
    {
        return match ($this) {
            self::A_VISTA => 'À Vista',
            self::FIXO => 'Parcelamento Fixo',
            self::VARIAVEL => 'Parcelamento Variável',
            self::PERSONALIZADO => 'Personalizado',
        };
    }

    public function getDescricao(): string
    {
        return match ($this) {
            self::A_VISTA => 'Pagamento único, sem parcelamento',
            self::FIXO => 'Parcelas iguais com intervalo fixo',
            self::VARIAVEL => 'Parcelas com valores e datas variáveis',
            self::PERSONALIZADO => 'Regras específicas definidas pelo usuário',
        };
    }

    public function getQuantidadeParcelas(): int
    {
        return match ($this) {
            self::A_VISTA => 1,
            self::FIXO => 0, // Será definido nas regras
            self::VARIAVEL => 0, // Será definido nas regras
            self::PERSONALIZADO => 0, // Será definido nas regras
        };
    }

    public function permiteJuros(): bool
    {
        return match ($this) {
            self::A_VISTA => false,
            default => true,
        };
    }

    public function permiteEntrada(): bool
    {
        return match ($this) {
            self::A_VISTA => false,
            default => true,
        };
    }

    public function getIntervaloPatrao(): int
    {
        // Intervalo padrão em dias
        return match ($this) {
            self::A_VISTA => 0,
            self::FIXO => 30, // Mensal
            self::VARIAVEL => 30, // Base mensal
            self::PERSONALIZADO => 0, // Definido pelo usuário
        };
    }

    public static function getEsquemaParcelamento(self $tipo, array $parametros = []): array
    {
        return match ($tipo) {
            self::A_VISTA => [
                'quantidade_parcelas' => 1,
                'intervalo_dias' => 0,
                'juros_parcelamento' => 0,
                'entrada_percentual' => 0,
            ],

            self::FIXO => [
                'quantidade_parcelas' => $parametros['quantidade_parcelas'] ?? 2,
                'intervalo_dias' => $parametros['intervalo_dias'] ?? 30,
                'juros_parcelamento' => $parametros['juros_parcelamento'] ?? 0,
                'entrada_percentual' => $parametros['entrada_percentual'] ?? 0,
            ],

            self::VARIAVEL => [
                'parcelas' => $parametros['parcelas'] ?? [],
                'juros_parcelamento' => $parametros['juros_parcelamento'] ?? 0,
            ],

            self::PERSONALIZADO => $parametros,
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn ($case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'description' => $case->getDescricao(),
                'permite_juros' => $case->permiteJuros(),
                'permite_entrada' => $case->permiteEntrada(),
                'intervalo_padrao' => $case->getIntervaloPatrao(),
            ],
            self::cases()
        );
    }
}
