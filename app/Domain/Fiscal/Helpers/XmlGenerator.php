<?php

namespace App\Domain\Fiscal\Helpers;

use App\Domain\Fiscal\Models\Rps;

class XmlGenerator
{
    /**
     * Gera XML ou INI para ACBrLib a partir de um RPS (stub para testes).
     */
    public function gerarXmlOuIni(Rps $rps, string $layout = 'NACIONAL'): string
    {
        // Stub simples para testes — em produção, use lógica real de geração
        return <<<XML
        <rps id="{$rps->id}">
            <numero>{$rps->numero_rps}</numero>
            <serie>{$rps->serie}</serie>
            <valor_servicos>{$rps->valor_servicos}</valor_servicos>
            <base_calculo>{$rps->base_calculo}</base_calculo>
            <aliquota>{$rps->aliquota}</aliquota>
            <valor_iss>{$rps->valor_iss}</valor_iss>
            <!-- Adicione mais campos conforme necessário -->
        </rps>
        XML;
    }

    /**
     * Gera XML/INI para lote (usado em LoteRpsService).
     */
    public function gerarXmlOuIniLote($lote, array $rpsList, bool $isNacional = true): string
    {
        // Stub para testes
        return '<lote numero="' . $lote->numero_lote . '">...</lote>';
    }
}
