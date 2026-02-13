<?php

namespace Database\Factories;

use App\Domain\Financeiro\FluxoCaixa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FluxoCaixaFactory extends Factory
{
    protected $model = FluxoCaixa::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Str::uuid(),
            'data_referencia' => $this->faker->date('Y-m-d'),
            'tipo_movimento' => 'ENTRADA',
            'categoria' => $this->faker->randomElement(['RECEBIMENTO','PAGAMENTO','TRANSFERENCIA']), // valid CategoriaFluxoCaixaEnum value
            'valor' => $this->faker->randomFloat(2, 100, 10000),
            'descricao' => $this->faker->sentence(),
            'conta_receber_id' => null,
            'conta_pagar_id' => null,
            'realizado' => false,
            'data_realizacao' => null,
            'saldo_acumulado' => 0,
        ];
    }
}
