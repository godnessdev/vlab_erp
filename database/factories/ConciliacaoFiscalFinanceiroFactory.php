<?php

namespace Database\Factories;

use App\Domain\Financeiro\ConciliacaoFiscalFinanceiro;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ConciliacaoFiscalFinanceiroFactory extends Factory
{
    protected $model = ConciliacaoFiscalFinanceiro::class;

    public function definition(): array
    {
        return [
            'conta_receber_id' => Str::uuid(),
            'nfse_id' => Str::uuid(),
            'data_conciliacao' => $this->faker->dateTime('Y-m-d H:i:s'),
            'valor_nfse' => $this->faker->randomFloat(2, 100, 10000),
            'valor_conta_receber' => $this->faker->randomFloat(2, 100, 10000),
            'valor_retencoes_nfse' => 0,
            'valor_liquido_nfse' => $this->faker->randomFloat(2, 100, 10000),
            'discrepancia_valor' => 0,
            'status_conciliacao' => 'CONCILIADO',
            'observacoes_discrepancia' => $this->faker->sentence(),
            'usuario_conciliacao' => Str::uuid(),
        ];
    }
}
