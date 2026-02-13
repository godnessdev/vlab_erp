<?php

namespace App\Domain\Fiscal\Enums;

enum StatusNfseEnum: string
{
    case AUTORIZADA = 'AUTORIZADA';
    case CANCELADA = 'CANCELADA';
    case SUBSTITUIDA = 'SUBSTITUIDA';
}
