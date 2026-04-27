<?php

namespace App\Domain\Fiscal\Integrations;

class AcbrLibIntegration
{
    public function emitirNfse($rps)
    {
        return ['numero_nfse' => '12345', 'codigo_verificacao' => 'ABC1234', 'xml' => '<xml>fake</xml>', 'url_visualizacao' => 'http://fake-nfse', 'data_autorizacao' => now()];
    }

    public function cancelarNfse($nfse, $motivo)
    {
        return ['xml' => '<xml>cancelamento</xml>', 'data_cancelamento' => now()];
    }
}
