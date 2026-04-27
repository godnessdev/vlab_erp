<?php

namespace Database\Factories;

use App\Domain\Faturamento\Enums\StatusFatura;
use App\Domain\Faturamento\Models\Fatura;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FaturaFactory extends Factory
{
    protected $model = Fatura::class;

    public function definition(): array
    {
        $valorServicos = $this->faker->randomFloat(2, 500, 10000);

        return [
            'id' => (string) Str::uuid(),
            'empresa_id' => (string) Str::uuid(),  // ou \App\Models\Empresa::factory() se quiser relação real
            'cliente_id' => (string) Str::uuid(),  // ou \App\Models\Usuario::factory() ou Pessoa
            'status' => StatusFatura::ABERTA->value,  // Usa o enum diretamente (mais seguro)
            'valor_servicos' => $valorServicos,
            'mes_referencia' => now()->startOfMonth()->format('Y-m-d'),  // Competência correta
            'base_calculo_iss' => $valorServicos,  // Geralmente = valor_servicos
            'aliquota_iss' => 0.02,
            'valor_iss' => fn (array $attr) => round($attr['base_calculo_iss'] * $attr['aliquota_iss'], 2),
            'valor_total' => fn (array $attr) => round($attr['valor_servicos'] + $attr['valor_iss'], 2),
            'valor_liquido' => fn (array $attr) => round($attr['valor_servicos'] - $attr['valor_iss'], 2),  // ou sua regra real
            'data_emissao' => now(),
            'data_vencimento' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
            // Adicione outros campos NOT NULL da sua migration se existirem (ex: numero_fatura, competencia, etc.)
            'numero_fatura' => $this->faker->numerify('FAT-######'),
        ];
    }
}
