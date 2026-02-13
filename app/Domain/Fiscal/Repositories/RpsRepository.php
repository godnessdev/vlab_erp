<?php

namespace App\Domain\Fiscal\Repositories;

use App\Domain\Fiscal\Rps;

class RpsRepository
{
    protected $model;

    public function __construct(Rps $model)
    {
        $this->model = $model;
    }

    public function findById(string $id): ?Rps
    {
        return $this->model->find($id);
    }

    public function findOrFail(string $id): Rps
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Rps
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $rps = $this->findOrFail($id);
        return $rps->update($data);
    }

    public function atualizarCancelamento(string $id, array $data): bool
    {
        $rps = $this->findOrFail($id);
        return $rps->update($data);
    }
}
