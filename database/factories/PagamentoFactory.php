<?php

namespace Database\Factories;

use App\Domain\Financeiro\Pagamento;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PagamentoFactory extends Factory
{
    protected $model = Pagamento::class;

    public function definition(): array
    {
        return [
            'conta_pagar_id' => Str::uuid(),
            'data_pagamento' => $this->faker->date('Y-m-d'),
            'valor_pago' => $this->faker->randomFloat(2, 100, 10000),
            'valor_juros_pago' => 0,
            'valor_multa_paga' => 0,
            'valor_desconto_obtido' => 0,
            'forma_pagamento' => 'BOLETO',
            'numero_transacao' => $this->faker->numerify('TRX#####'),
            'banco_destino' => $this->faker->numerify('###'),
            'agencia_destino' => $this->faker->numerify('####'),
            'conta_destino' => $this->faker->numerify('########'),
            'comprovante_url' => $this->faker->url(),
            'conciliado' => false,
            'observacoes' => $this->faker->sentence(),
        ];
    }
}
