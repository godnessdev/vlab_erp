<?php

namespace App\Domain\Identidade\Factories;

use App\Domain\Identidade\Enums\StatusPapel;
use App\Domain\Identidade\Enums\TipoPapel;
use App\Domain\Identidade\Models\Papel;
use App\Domain\Identidade\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

class PapelFactory extends Factory
{
    protected $model = Papel::class;

    public function definition(): array
    {
        $dataInicio = $this->faker->dateTimeBetween('-2 years', 'now');
        
        return [
            'pessoa_id' => Pessoa::factory(),
            'empresa_id' => $this->faker->uuid(), // Será substituído quando implementarmos Empresa
            'tipo_papel' => $this->faker->randomElement(TipoPapel::cases()),
            'data_inicio' => $dataInicio,
            'data_fim' => $this->faker->optional(0.2)->dateTimeBetween($dataInicio, 'now'),
            'status' => StatusPapel::ATIVO,
        ];
    }

    public function cliente(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_papel' => TipoPapel::CLIENTE,
            ];
        });
    }

    public function prestador(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_papel' => TipoPapel::PRESTADOR,
            ];
        });
    }

    public function funcionario(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_papel' => TipoPapel::FUNCIONARIO,
            ];
        });
    }

    public function fornecedor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_papel' => TipoPapel::FORNECEDOR,
            ];
        });
    }

    public function contador(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_papel' => TipoPapel::CONTADOR,
            ];
        });
    }

    public function ativo(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => StatusPapel::ATIVO,
                'data_fim' => null,
            ];
        });
    }

    public function inativo(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => StatusPapel::INATIVO,
                'data_fim' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    public function encerrado(): static
    {
        return $this->state(function (array $attributes) {
            $dataInicio = $attributes['data_inicio'] ?? $this->faker->dateTimeBetween('-2 years', '-1 year');
            
            return [
                'data_inicio' => $dataInicio,
                'data_fim' => $this->faker->dateTimeBetween($dataInicio, 'now'),
                'status' => StatusPapel::INATIVO,
            ];
        });
    }

    public function vigente(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'data_inicio' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'data_fim' => null,
                'status' => StatusPapel::ATIVO,
            ];
        });
    }

    public function comDadosEspecificos(): static
    {
        return $this->afterCreating(function (Papel $papel) {
            $dados = match ($papel->tipo_papel) {
                TipoPapel::CLIENTE => [
                    'limite_credito' => $this->faker->randomFloat(2, 1000, 50000),
                    'categoria' => $this->faker->randomElement(['BRONZE', 'PRATA', 'OURO', 'PLATINUM']),
                    'desconto_padrao' => $this->faker->randomFloat(2, 0, 15),
                ],
                TipoPapel::PRESTADOR => [
                    'especialidade' => $this->faker->randomElement(['CONSULTORIA', 'DESENVOLVIMENTO', 'DESIGN', 'MARKETING']),
                    'valor_hora' => $this->faker->randomFloat(2, 50, 300),
                    'disponibilidade' => $this->faker->randomElement(['FULL_TIME', 'PART_TIME', 'FREELANCER']),
                ],
                TipoPapel::FUNCIONARIO => [
                    'cargo' => $this->faker->jobTitle(),
                    'salario' => $this->faker->randomFloat(2, 1500, 15000),
                    'departamento' => $this->faker->randomElement(['TI', 'FINANCEIRO', 'RH', 'VENDAS']),
                ],
                TipoPapel::FORNECEDOR => [
                    'categoria_fornecimento' => $this->faker->randomElement(['MATERIAL', 'SERVICO', 'EQUIPAMENTO']),
                    'prazo_entrega_padrao' => $this->faker->numberBetween(1, 30),
                    'forma_pagamento' => $this->faker->randomElement(['À VISTA', '30 DIAS', '45 DIAS']),
                ],
                TipoPapel::CONTADOR => [
                    'crc' => $this->faker->numerify('CRC-##-######'),
                    'especialidades' => $this->faker->randomElements(['FISCAL', 'TRABALHISTA', 'SOCIETARIO'], 2),
                    'valor_hora' => $this->faker->randomFloat(2, 80, 500),
                ],
            };

            foreach ($dados as $chave => $valor) {
                $papel->dadosEspecificos()->create([
                    'chave' => $chave,
                    'valor' => $valor,
                ]);
            }
        });
    }
}
