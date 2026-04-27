<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\RegimeTributarioEnum;
use App\Models\StatusEmpresaEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'razao_social' => fake()->company(),
            'nome_fantasia' => fake()->companySuffix(),
            'cnpj' => fake()->numerify('##.###.###/0001-##'),
            'inscricao_estadual' => fake()->numerify('#########'),
            'inscricao_municipal' => fake()->numerify('#######'),
            'regime_tributario' => fake()->randomElement(RegimeTributarioEnum::cases()),
            'data_constituicao' => fake()->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
            'email' => fake()->companyEmail(),
            'telefone' => fake()->phoneNumber(),
            'status' => StatusEmpresaEnum::ATIVO,
            'dados_endereco' => [
                'endereco' => fake()->streetAddress(),
                'numero' => fake()->buildingNumber(),
                'complemento' => fake()->optional()->secondaryAddress(),
                'bairro' => fake()->citySuffix(),
                'cidade' => fake()->city(),
                'estado' => fake()->stateAbbr(),
                'cep' => fake()->numerify('#####-###'),
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the company is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusEmpresaEnum::INATIVO,
        ]);
    }

    /**
     * Set specific tax regime.
     */
    public function withRegime(RegimeTributarioEnum $regime): static
    {
        return $this->state(fn (array $attributes) => [
            'regime_tributario' => $regime,
        ]);
    }
}
