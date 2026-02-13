<?php

namespace Database\Factories;

use App\Domain\Fiscal\Nfse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NfseFactory extends Factory
{
    protected $model = Nfse::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Str::uuid(),
            'rps_id' => Str::uuid(),
            'numero_nfse' => $this->faker->unique()->numberBetween(1000, 9999),
            'codigo_verificacao' => strtoupper(Str::random(7)), // garantir 7 caracteres
            'data_emissao' => now(),
            'data_autorizacao' => now(),
            'municipio_prestacao' => $this->faker->randomElement([
                '3550308', // São Paulo
                '3304557', // Rio de Janeiro
                '2927408', // Salvador
                '3106200', // Belo Horizonte
                '4106902', // Curitiba
                '4314902', // Porto Alegre
                '5300108', // Brasília
            ]),
            'url_visualizacao' => $this->faker->url,
            'status' => 'AUTORIZADA',
            'motivo_cancelamento' => null,
            'data_cancelamento' => null,
            'xml_autorizacao' => '<xml>autorizacao</xml>',
            'xml_cancelamento' => null,
            'hash_xml' => Str::random(32),
            'versao_schema' => '1.00',
            'base_calculo_ibs' => 1000,
            'aliquota_ibs' => 0.02,
            'valor_ibs' => 20,
            'valor_ibs_retido' => 0,
            'base_calculo_cbs' => 1000,
            'aliquota_cbs' => 0.01,
            'valor_cbs' => 10,
        ];
    }
}
