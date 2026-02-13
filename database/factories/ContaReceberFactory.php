<?php

namespace Database\Factories;

use App\Domain\Financeiro\ContaReceber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContaReceberFactory extends Factory
{
    protected $model = ContaReceber::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Str::uuid(),
            'fatura_id' => Str::uuid(),
            'nfse_id' => null,
            'numero_conta' => $this->faker->unique()->numerify('CR#####'),
            'cliente_id' => Str::uuid(),
            'valor_original' => $this->faker->randomFloat(2, 100, 10000),
            'valor_juros' => 0,
            'valor_multa' => 0,
            'valor_desconto' => 0,
            'valor_total' => $this->faker->randomFloat(2, 100, 10000),
            'valor_retencoes' => 0,
            'valor_liquido_esperado' => $this->faker->randomFloat(2, 100, 10000),
            'data_vencimento' => $this->faker->date('Y-m-d'),
            'data_emissao' => $this->faker->date('Y-m-d'),
            'status' => 'ABERTA',
            'forma_cobranca' => 'BOLETO',
            'observacoes' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
