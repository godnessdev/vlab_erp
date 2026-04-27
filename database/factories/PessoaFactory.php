<?php

namespace Database\Factories;

use App\Models\Pessoa;
use Faker\Provider\pt_BR\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PessoaFactory extends Factory
{
    protected $model = Pessoa::class;

    public function definition()
    {
        $this->faker->addProvider(new Person($this->faker));

        return [
            'id' => (string) Str::uuid(),
            'tipo' => $this->faker->randomElement(['FISICA', 'JURIDICA']),
            'nome_razao_social' => $this->faker->name(),
            'nome_fantasia' => $this->faker->companySuffix(),
            'data_nascimento_constituicao' => $this->faker->date(),
            'status' => 'ATIVO',
            'data_criacao' => now(),
            'data_atualizacao' => now(),
            // Exemplo de campo documento, se existir:
            // 'documento' => $this->faker->cpf(false),
        ];
    }
}
