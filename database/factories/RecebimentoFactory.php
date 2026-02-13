<?php

namespace Database\Factories;

use App\Domain\Financeiro\Recebimento;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RecebimentoFactory extends Factory
{
    protected $model = Recebimento::class;

    public function definition(): array
    {
        return [
            'conta_receber_id' => Str::uuid(),
            'data_recebimento' => $this->faker->date('Y-m-d'),
            'valor_recebido' => $this->faker->randomFloat(2, 100, 10000),
            'valor_juros_recebido' => 0,
            'valor_multa_recebida' => 0,
            'valor_desconto_concedido' => 0,
            'forma_recebimento' => 'BOLETO',
            'numero_transacao' => $this->faker->numerify('TRX#####'),
            'banco_origem' => $this->faker->numerify('###'),
            'agencia_origem' => $this->faker->numerify('####'),
            'conta_origem' => $this->faker->numerify('########'),
            'comprovante_url' => $this->faker->url(),
            'conciliado' => false,
            'data_conciliacao' => null,
            'observacoes' => $this->faker->sentence(),
        ];
    }
}
