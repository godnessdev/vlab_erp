<?php

namespace Database\Factories;

use App\Domain\Servicos\Enums\StatusServico;
use App\Domain\Servicos\Enums\UnidadeMedida;
use App\Domain\Servicos\Models\Servico;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Servicos\Models\Servico>
 */
class ServicoFactory extends Factory
{
    protected $model = Servico::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'empresa_id' => Empresa::factory(),
            'descricao' => fake()->sentence(3),
            'unidade_medida' => fake()->randomElement(UnidadeMedida::cases()),
            'preco_base' => fake()->randomFloat(2, 50, 1000),
            'aliquota_iss_default' => fake()->randomFloat(2, 2, 5),
            'classificacao_fiscal' => fake()->numerify('####-#/##'),
            'observacoes' => fake()->optional()->paragraph(),
            'status' => StatusServico::ATIVO,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the service is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusServico::INATIVO,
        ]);
    }

    /**
     * Indicate that the service is for a specific company.
     */
    public function forCompany(string $empresaId): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => $empresaId,
        ]);
    }

    /**
     * Create service with specific unit of measure.
     */
    public function withUnit(UnidadeMedida $unidade): static
    {
        return $this->state(fn (array $attributes) => [
            'unidade_medida' => $unidade,
        ]);
    }

    /**
     * Create service with specific price.
     */
    public function withPrice(float $preco): static
    {
        return $this->state(fn (array $attributes) => [
            'preco_base' => $preco,
        ]);
    }
}
