<?php

namespace Database\Factories;

use App\Domain\OrdemServico\Enums\PrioridadeOrdem;
use App\Domain\OrdemServico\Enums\StatusOrdemServico;
use App\Domain\OrdemServico\Models\OrdemServico;
use App\Models\Empresa;
use App\Models\Papel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\OrdemServico\Models\OrdemServico>
 */
class OrdemServicoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrdemServico::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dataInicio = $this->faker->dateTimeBetween('-30 days', '+30 days');
        $dataFim = $this->faker->dateTimeBetween($dataInicio, '+60 days');

        return [
            'empresa_id' => Empresa::factory(),
            'cliente_id' => Papel::factory(),
            'titulo' => 'OS - ' . $this->faker->sentence(4),
            'descricao' => $this->faker->paragraph(3),
            'data_abertura' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'data_prevista_inicio' => $dataInicio,
            'data_prevista_conclusao' => $dataFim,
            'status' => $this->faker->randomElement(StatusOrdemServico::cases()),
            'prioridade' => $this->faker->randomElement(PrioridadeOrdem::cases()),
            'valor_total_estimado' => $this->faker->randomFloat(2, 100, 10000),
            'valor_total_executado' => $this->faker->randomFloat(2, 0, 8000),
            'observacoes' => $this->faker->optional(0.3)->paragraph(),
        ];
    }

    /**
     * Estado para ordem aberta
     */
    public function aberta(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusOrdemServico::ABERTA,
            'data_inicio_real' => null,
            'data_conclusao_real' => null,
            'valor_total_executado' => 0,
        ]);
    }

    /**
     * Estado para ordem em andamento
     */
    public function emAndamento(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusOrdemServico::EM_ANDAMENTO,
            'data_inicio_real' => $this->faker->dateTimeBetween('-15 days', 'now'),
            'data_conclusao_real' => null,
        ]);
    }

    /**
     * Estado para ordem concluída
     */
    public function concluida(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusOrdemServico::CONCLUIDA,
            'data_inicio_real' => $this->faker->dateTimeBetween('-30 days', '-10 days'),
            'data_conclusao_real' => $this->faker->dateTimeBetween('-10 days', 'now'),
        ]);
    }

    /**
     * Estado para ordem de alta prioridade
     */
    public function altaPrioridade(): static
    {
        return $this->state(fn (array $attributes) => [
            'prioridade' => PrioridadeOrdem::ALTA,
        ]);
    }

    /**
     * Estado para ordem urgente
     */
    public function urgente(): static
    {
        return $this->state(fn (array $attributes) => [
            'prioridade' => PrioridadeOrdem::URGENTE,
            'titulo' => '[URGENTE] ' . $attributes['titulo'],
        ]);
    }

    /**
     * Estado para ordem com valor alto
     */
    public function valorAlto(): static
    {
        return $this->state(fn (array $attributes) => [
            'valor_total_estimado' => $this->faker->randomFloat(2, 10000, 50000),
            'valor_total_executado' => $this->faker->randomFloat(2, 5000, 40000),
        ]);
    }
}
