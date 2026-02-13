<?php

namespace App\Domain\Identidade\Factories;

use App\Domain\Identidade\Models\DadoEspecificoPapel;
use App\Domain\Identidade\Models\Papel;
use Illuminate\Database\Eloquent\Factories\Factory;

class DadoEspecificoPapelFactory extends Factory
{
    protected $model = DadoEspecificoPapel::class;

    public function definition(): array
    {
        $chaves = [
            'limite_credito',
            'categoria',
            'desconto_padrao',
            'especialidade',
            'valor_hora',
            'disponibilidade',
            'cargo',
            'salario',
            'departamento',
            'categoria_fornecimento',
            'prazo_entrega_padrao',
            'forma_pagamento',
            'crc',
            'especialidades',
        ];

        $chave = $this->faker->randomElement($chaves);

        return [
            'papel_id' => Papel::factory(),
            'chave' => $chave,
            'valor' => $this->gerarValorPorChave($chave),
        ];
    }

    public function limiteCredito(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'limite_credito',
                'valor' => $this->faker->randomFloat(2, 1000, 100000),
            ];
        });
    }

    public function categoria(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'categoria',
                'valor' => $this->faker->randomElement(['BRONZE', 'PRATA', 'OURO', 'PLATINUM']),
            ];
        });
    }

    public function valorHora(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'valor_hora',
                'valor' => $this->faker->randomFloat(2, 50, 500),
            ];
        });
    }

    public function cargo(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'cargo',
                'valor' => $this->faker->jobTitle(),
            ];
        });
    }

    public function salario(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'salario',
                'valor' => $this->faker->randomFloat(2, 1500, 20000),
            ];
        });
    }

    public function dadosComplexos(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'dados_complexos',
                'valor' => [
                    'configuracoes' => [
                        'notificacoes' => $this->faker->boolean(),
                        'tema' => $this->faker->randomElement(['claro', 'escuro']),
                        'idioma' => 'pt-br',
                    ],
                    'preferencias' => [
                        'formato_data' => 'd/m/Y',
                        'fuso_horario' => 'America/Sao_Paulo',
                        'moeda' => 'BRL',
                    ],
                    'historico' => [
                        'ultimo_login' => $this->faker->dateTime(),
                        'total_acessos' => $this->faker->numberBetween(1, 1000),
                    ],
                ],
            ];
        });
    }

    public function array(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'lista_especialidades',
                'valor' => $this->faker->randomElements([
                    'FISCAL', 'TRABALHISTA', 'SOCIETARIO', 'TRIBUTARIO',
                    'CONTABIL', 'FINANCEIRO', 'AUDITORIA'
                ], $this->faker->numberBetween(1, 4)),
            ];
        });
    }

    public function string(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'observacoes',
                'valor' => $this->faker->text(200),
            ];
        });
    }

    public function numerico(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'pontuacao',
                'valor' => $this->faker->numberBetween(0, 100),
            ];
        });
    }

    public function booleano(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'chave' => 'ativo',
                'valor' => $this->faker->boolean(),
            ];
        });
    }

    private function gerarValorPorChave(string $chave): mixed
    {
        return match ($chave) {
            'limite_credito', 'valor_hora', 'salario' => $this->faker->randomFloat(2, 100, 50000),
            'categoria' => $this->faker->randomElement(['BRONZE', 'PRATA', 'OURO', 'PLATINUM']),
            'desconto_padrao' => $this->faker->randomFloat(2, 0, 20),
            'especialidade' => $this->faker->randomElement(['CONSULTORIA', 'DESENVOLVIMENTO', 'DESIGN', 'MARKETING']),
            'disponibilidade' => $this->faker->randomElement(['FULL_TIME', 'PART_TIME', 'FREELANCER']),
            'cargo' => $this->faker->jobTitle(),
            'departamento' => $this->faker->randomElement(['TI', 'FINANCEIRO', 'RH', 'VENDAS', 'MARKETING']),
            'categoria_fornecimento' => $this->faker->randomElement(['MATERIAL', 'SERVICO', 'EQUIPAMENTO']),
            'prazo_entrega_padrao' => $this->faker->numberBetween(1, 30),
            'forma_pagamento' => $this->faker->randomElement(['À VISTA', '30 DIAS', '45 DIAS', '60 DIAS']),
            'crc' => $this->faker->numerify('CRC-##-######'),
            'especialidades' => $this->faker->randomElements(['FISCAL', 'TRABALHISTA', 'SOCIETARIO'], 2),
            default => $this->faker->words(3, true),
        };
    }
}
