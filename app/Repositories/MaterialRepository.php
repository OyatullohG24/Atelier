<?php

namespace App\Repositories;

use App\Models\Material;
use App\Repositories\Interfaces\MaterialRepositoryInterface;

class MaterialRepository implements MaterialRepositoryInterface
{
    public function __construct(protected Material $material) {}

    public function all()
    {
        return $this->material->get();
    }

    public function create(array $data): Material
    {
        return $this->material->create($data);
    }

    public function find(int $id): ?Material
    {
        return $this->material->find($id);
    }

    public function update(Material $material, array $data): bool
    {
        return $material->update($data);
    }

    public function delete(Material $material): bool
    {
        return $material->delete();
    }
}
