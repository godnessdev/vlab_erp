<?php

namespace App\Domain\Fiscal\Repositories;

use App\Domain\Fiscal\Nfse;

class NfseRepository
{
    protected $model;

    public function __construct(Nfse $model)
    {
        $this->model = $model;
    }

    public function findById(string $id): ?Nfse
    {
        return $this->model->find($id);
    }

    public function findOrFail(string $id): Nfse
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Nfse
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $nfse = $this->findOrFail($id);
        return $nfse->update($data);
    }

    public function atualizarCancelamento(string $id, array $data): bool
    {
        $nfse = $this->findOrFail($id);
        return $nfse->update($data);
    }
}
