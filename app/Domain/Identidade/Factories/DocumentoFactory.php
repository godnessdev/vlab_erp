<?php

namespace App\Domain\Identidade\Factories;

use App\Domain\Identidade\Enums\TipoDocumento;
use App\Domain\Identidade\Models\Documento;
use App\Domain\Identidade\Models\Pessoa;
use App\Domain\Identidade\Validators\DocumentoValidator;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentoFactory extends Factory
{
    protected $model = Documento::class;

    public function definition(): array
    {
        $tipo = $this->faker->randomElement(TipoDocumento::cases());
        $validator = new DocumentoValidator;

        return [
            'pessoa_id' => Pessoa::factory(),
            'tipo' => $tipo,
            'valor' => $this->gerarValorPorTipo($tipo, $validator),
            'data_emissao' => $this->faker->optional(0.8)->dateTimeBetween('-20 years', 'now'),
            'orgao_emissor' => $this->faker->optional(0.6)->randomElement(['SSP', 'IFP', 'DETRAN', 'PC']),
            'valido' => $this->faker->boolean(90),
        ];
    }

    public function cpf(): static
    {
        return $this->state(function (array $attributes) {
            $validator = new DocumentoValidator;

            return [
                'tipo' => TipoDocumento::CPF,
                'valor' => $validator->gerarCpfValido(),
                'data_emissao' => null,
                'orgao_emissor' => null,
                'valido' => true,
            ];
        });
    }

    public function cnpj(): static
    {
        return $this->state(function (array $attributes) {
            $validator = new DocumentoValidator;

            return [
                'tipo' => TipoDocumento::CNPJ,
                'valor' => $validator->gerarCnpjValido(),
                'data_emissao' => $this->faker->dateTimeBetween('-30 years', 'now'),
                'orgao_emissor' => 'JUNTA COMERCIAL',
                'valido' => true,
            ];
        });
    }

    public function rg(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoDocumento::RG,
                'valor' => $this->gerarRg(),
                'data_emissao' => $this->faker->dateTimeBetween('-20 years', 'now'),
                'orgao_emissor' => $this->faker->randomElement(['SSP', 'IFP', 'DETRAN', 'PC']),
                'valido' => true,
            ];
        });
    }

    public function ie(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoDocumento::IE,
                'valor' => $this->gerarIe(),
                'data_emissao' => $this->faker->dateTimeBetween('-10 years', 'now'),
                'orgao_emissor' => 'SECRETARIA DA FAZENDA',
                'valido' => true,
            ];
        });
    }

    public function im(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoDocumento::IM,
                'valor' => $this->gerarIm(),
                'data_emissao' => $this->faker->dateTimeBetween('-10 years', 'now'),
                'orgao_emissor' => 'SECRETARIA MUNICIPAL',
                'valido' => true,
            ];
        });
    }

    public function valido(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'valido' => true,
            ];
        });
    }

    public function invalido(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'valido' => false,
            ];
        });
    }

    public function vencido(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoDocumento::RG,
                'data_emissao' => $this->faker->dateTimeBetween('-15 years', '-11 years'),
                'valido' => false,
            ];
        });
    }

    private function gerarValorPorTipo(TipoDocumento $tipo, DocumentoValidator $validator): string
    {
        return match ($tipo) {
            TipoDocumento::CPF => $validator->gerarCpfValido(),
            TipoDocumento::CNPJ => $validator->gerarCnpjValido(),
            TipoDocumento::RG => $this->gerarRg(),
            TipoDocumento::IE => $this->gerarIe(),
            TipoDocumento::IM => $this->gerarIm(),
        };
    }

    private function gerarRg(): string
    {
        return $this->faker->numerify('#########');
    }

    private function gerarIe(): string
    {
        return $this->faker->numerify('###########');
    }

    private function gerarIm(): string
    {
        return $this->faker->numerify('########');
    }
}
