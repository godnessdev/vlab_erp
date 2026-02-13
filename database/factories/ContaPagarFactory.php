<?php

namespace Database\Factories;

use App\Domain\Financeiro\ContaPagar;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContaPagarFactory extends Factory
{
    protected $model = ContaPagar::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Str::uuid(),
            'fornecedor_id' => Str::uuid(),
            'numero_conta' => $this->faker->unique()->numerify('CP#####'),
            'descricao' => $this->faker->sentence(),
            'categoria' => 'FORNECEDOR',
            'valor_original' => $this->faker->randomFloat(2, 100, 10000),
            'valor_juros' => 0,
            'valor_multa' => 0,
            'valor_desconto' => 0,
            'valor_total' => $this->faker->randomFloat(2, 100, 10000),
            'data_vencimento' => $this->faker->date('Y-m-d'),
            'data_emissao' => $this->faker->date('Y-m-d'),
            'status' => 'ABERTA',
            'centro_custo' => $this->faker->word(),
            'numero_documento' => $this->faker->numerify('DOC#####'),
            'observacoes' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
