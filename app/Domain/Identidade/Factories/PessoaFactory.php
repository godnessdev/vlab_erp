<?php

namespace App\Domain\Identidade\Factories;

use App\Domain\Identidade\Enums\StatusPessoa;
use App\Domain\Identidade\Enums\TipoPessoa;
use App\Domain\Identidade\Models\Pessoa;
use App\Domain\Identidade\Validators\DocumentoValidator;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaFactory extends Factory
{
    protected $model = Pessoa::class;

    public function definition(): array
    {
        $tipo = $this->faker->randomElement(TipoPessoa::cases());

        return [
            'tipo' => $tipo,
            'nome_razao_social' => $tipo === TipoPessoa::FISICA
                ? $this->faker->name()
                : $this->faker->company(),
            'nome_fantasia' => $tipo === TipoPessoa::JURIDICA
                ? $this->faker->optional()->companySuffix()
                : null,
            'data_nascimento_constituicao' => $tipo === TipoPessoa::FISICA
                ? $this->faker->dateTimeBetween('-80 years', '-18 years')
                : $this->faker->dateTimeBetween('-50 years', '-1 year'),
            'status' => StatusPessoa::ATIVO,
        ];
    }

    public function fisica(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoPessoa::FISICA,
                'nome_razao_social' => $this->faker->name(),
                'nome_fantasia' => null,
                'data_nascimento_constituicao' => $this->faker->dateTimeBetween('-80 years', '-18 years'),
            ];
        });
    }

    public function juridica(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoPessoa::JURIDICA,
                'nome_razao_social' => $this->faker->company(),
                'nome_fantasia' => $this->faker->optional()->companySuffix(),
                'data_nascimento_constituicao' => $this->faker->dateTimeBetween('-50 years', '-1 year'),
            ];
        });
    }

    public function inativa(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => StatusPessoa::INATIVO,
            ];
        });
    }

    public function comCpf(): static
    {
        return $this->fisica()
            ->afterCreating(function (Pessoa $pessoa) {
                $validator = new DocumentoValidator;
                $pessoa->documentos()->create([
                    'tipo' => 'CPF',
                    'valor' => $validator->gerarCpfValido(),
                    'valido' => true,
                ]);
            });
    }

    public function comCnpj(): static
    {
        return $this->juridica()
            ->afterCreating(function (Pessoa $pessoa) {
                $validator = new DocumentoValidator;
                $pessoa->documentos()->create([
                    'tipo' => 'CNPJ',
                    'valor' => $validator->gerarCnpjValido(),
                    'valido' => true,
                ]);
            });
    }

    public function comEndereco(): static
    {
        return $this->afterCreating(function (Pessoa $pessoa) {
            $pessoa->enderecos()->create([
                'tipo' => 'PRINCIPAL',
                'logradouro' => $this->faker->streetName(),
                'numero' => $this->faker->buildingNumber(),
                'complemento' => $this->faker->optional()->secondaryAddress(),
                'bairro' => $this->faker->cityPrefix(),
                'cidade' => $this->faker->city(),
                'estado' => $this->faker->stateAbbr(),
                'cep' => $this->faker->postcode(),
                'pais' => 'BR',
            ]);
        });
    }

    public function comContatos(): static
    {
        return $this->afterCreating(function (Pessoa $pessoa) {
            // Email principal
            $pessoa->contatos()->create([
                'tipo' => 'EMAIL',
                'valor' => $this->faker->unique()->safeEmail(),
                'principal' => true,
                'verificado' => $this->faker->boolean(80),
            ]);

            // Celular principal
            $pessoa->contatos()->create([
                'tipo' => 'CELULAR',
                'valor' => $this->faker->phoneNumber(),
                'principal' => true,
                'verificado' => $this->faker->boolean(60),
            ]);
        });
    }

    public function completa(): static
    {
        return $this->when(
            $this->faker->boolean(),
            fn ($factory) => $factory->comCpf(),
            fn ($factory) => $factory->comCnpj()
        )
            ->comEndereco()
            ->comContatos();
    }
}
