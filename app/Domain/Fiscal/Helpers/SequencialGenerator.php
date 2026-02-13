<?php

namespace App\Domain\Fiscal\Helpers;

class SequencialGenerator
{
    public function gerar($tipo = null)
    {
        // Stub para testes
        return rand(1000, 9999);
    }

    public function obterProximoNumeroRps($empresaId)
    {
        // Retorna um número sequencial fake para testes
        return rand(1000, 9999);
    }

    public function obterSerieAtiva($empresaId)
    {
        // Retorna uma série padrão para testes
        return 'A';
    }
}