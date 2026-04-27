<?php

namespace App\Domain\Fiscal\Calculos;

class CalculadoraRetencoes
{
    public function calcular($dados)
    {
        // Stub para testes
        return $dados;
    }

    public function calcularRetencoes($dados)
    {
        // Stub para testes
        return [
            'valorDeducoes' => 0,
            'valorPIS' => 0,
            'valorCOFINS' => 0,
            'valorINSS' => 0,
            'valorIR' => 0,
        ];
    }
}
