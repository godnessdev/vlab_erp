<?php

namespace App\Domain\Fiscal\Repositories;

interface FaturaRepository
{
    // Métodos esperados pelo service/controller
    public function find($id);

    public function save($fatura);
    // Adicione outros métodos conforme necessário
}
