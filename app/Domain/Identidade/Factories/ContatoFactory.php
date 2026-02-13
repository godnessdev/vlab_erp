<?php

namespace App\Domain\Identidade\Factories;

use App\Domain\Identidade\Enums\TipoContato;
use App\Domain\Identidade\Models\Contato;
use App\Domain\Identidade\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContatoFactory extends Factory
{
    protected $model = Contato::class;

    public function definition(): array
    {
        $tipo = $this->faker->randomElement(TipoContato::cases());

        return [
            'pessoa_id' => Pessoa::factory(),
            'tipo' => $tipo,
            'valor' => $this->gerarValorPorTipo($tipo),
            'principal' => $this->faker->boolean(20),
            'verificado' => $this->faker->boolean(70),
        ];
    }

    public function email(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoContato::EMAIL,
                'valor' => $this->faker->unique()->safeEmail(),
                'principal' => true,
            ];
        });
    }

    public function telefoneFixo(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoContato::TELEFONE_FIXO,
                'valor' => $this->gerarTelefoneFixo(),
                'principal' => true,
            ];
        });
    }

    public function celular(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoContato::CELULAR,
                'valor' => $this->gerarCelular(),
                'principal' => true,
            ];
        });
    }

    public function whatsapp(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => TipoContato::WHATSAPP,
                'valor' => $this->gerarCelular(),
                'principal' => false,
            ];
        });
    }

    public function principal(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'principal' => true,
            ];
        });
    }

    public function verificado(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'verificado' => true,
            ];
        });
    }

    public function naoVerificado(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'verificado' => false,
            ];
        });
    }

    private function gerarValorPorTipo(TipoContato $tipo): string
    {
        return match ($tipo) {
            TipoContato::EMAIL => $this->faker->safeEmail(),
            TipoContato::TELEFONE_FIXO => $this->gerarTelefoneFixo(),
            TipoContato::CELULAR, TipoContato::WHATSAPP => $this->gerarCelular(),
        };
    }

    private function gerarTelefoneFixo(): string
    {
        $ddd = $this->faker->numberBetween(11, 99);
        $numero = $this->faker->numberBetween(20000000, 99999999);
        
        return sprintf('%02d%08d', $ddd, $numero);
    }

    private function gerarCelular(): string
    {
        $ddd = $this->faker->numberBetween(11, 99);
        $numero = $this->faker->numberBetween(900000000, 999999999);
        
        return sprintf('%02d%09d', $ddd, $numero);
    }
}
