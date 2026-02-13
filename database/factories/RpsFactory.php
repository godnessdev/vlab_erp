<?php

namespace Database\Factories;

use App\Domain\Fiscal\Rps;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RpsFactory extends Factory
{
    protected $model = Rps::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Str::uuid(),
            'fatura_id' => Str::uuid(),
            'numero_rps' => $this->faker->unique()->numberBetween(1000, 9999),
            'serie' => 'A',
            'data_emissao' => now(),
            'competencia' => now()->format('Y-m-d'),
            'valor_servicos' => 1000,
            'valor_deducoes' => 0,
            'valor_pis' => 0,
            'valor_cofins' => 0,
            'valor_inss' => 0,
            'valor_ir' => 0,
            'valor_csll' => 0,
            'base_calculo' => 1000,
            'aliquota' => 0.02,
            'valor_iss' => 20,
            'valor_iss_retido' => 0,
            'valor_ibs' => 20,
            'valor_cbs' => 10,
            'descricao' => $this->faker->sentence,
            'codigo_servico' => '101',
            'codigo_cnae' => '6201500',
            'item_lista_servico' => '1.01',
            'situacao' => 'NORMAL',
            'usar_layout_nacional' => true,
            'data_criacao' => now(),
        ];
    }
}
