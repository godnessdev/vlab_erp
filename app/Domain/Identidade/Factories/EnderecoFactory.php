<?php

namespace App\Domain\Identidade\Factories;

use App\Domain\Identidade\Enums\TipoEndereco;
use App\Domain\Identidade\Models\Endereco;
use App\Domain\Identidade\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnderecoFactory extends Factory
{
    protected $model = Endereco::class;

    public function definition(): array
    {
        return [
            'pessoa_id' => Pessoa::factory(),
            'tipo' => $this->faker->randomElement(TipoEndereco::cases()),
            'logradouro' => $this->faker->streetName(),
            'numero' => $this->faker->buildingNumber(),
            'complemento' => $this->faker->optional(0.3)->secondaryAddress(),
            'bairro' => $this->faker->cityPrefix(),
            'cidade' => $this->faker->city(),
            'estado' => $this->faker->stateAbbr(),
            'cep' => $this->formatarCep($this->faker->numberBetween(10000000, 99999999)),
            'pais' => 'BR',
            'latitude' => $this->faker->optional(0.5)->latitude(-33.75, 5.27), // Brasil
            'longitude' => $this->faker->optional(0.5)->longitude(-73.98, -34.79), // Brasil
        ];
    }

    public function principal(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoEndereco::PRINCIPAL,
            ];
        });
    }

    public function entrega(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoEndereco::ENTREGA,
            ];
        });
    }

    public function faturamento(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoEndereco::FATURAMENTO,
            ];
        });
    }

    public function comCoordenadas(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'latitude' => $this->faker->latitude(-33.75, 5.27),
                'longitude' => $this->faker->longitude(-73.98, -34.79),
            ];
        });
    }

    public function semCoordenadas(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'latitude' => null,
                'longitude' => null,
            ];
        });
    }

    public function sãoPaulo(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => $this->formatarCep($this->faker->numberBetween(1000000, 5999999)),
                'latitude' => $this->faker->latitude(-24.0, -23.2),
                'longitude' => $this->faker->longitude(-46.9, -46.3),
            ];
        });
    }

    public function rioDeJaneiro(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'cep' => $this->formatarCep($this->faker->numberBetween(20000000, 28999999)),
                'latitude' => $this->faker->latitude(-23.1, -22.7),
                'longitude' => $this->faker->longitude(-43.8, -43.1),
            ];
        });
    }

    private function formatarCep(int $cep): string
    {
        $cepString = str_pad($cep, 8, '0', STR_PAD_LEFT);
        return substr($cepString, 0, 5) . '-' . substr($cepString, 5, 3);
    }
}
