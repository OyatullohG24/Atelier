<?php

namespace App\Repositories;

use App\Models\ClothesMaterial;
use App\Repositories\Interfaces\ClothesMaterialRepositoryInterface;

class ClothesMaterialRepository implements ClothesMaterialRepositoryInterface
{
    public function __construct(protected ClothesMaterial $clothesMaterial) {}

    public function all()
    {
        return $this->clothesMaterial->query()->groupBy('clothes_id')->get();
    }

    public function create(array $data): ClothesMaterial
    {
        return $this->clothesMaterial->create($data);
    }

    public function find(int $id): ?ClothesMaterial
    {
        return $this->clothesMaterial->find($id);
    }

    public function update(ClothesMaterial $clothesMaterial, array $data): bool
    {
        return $clothesMaterial->update($data);
    }

    public function delete(ClothesMaterial $clothesMaterial): bool
    {
        return $clothesMaterial->delete();
    }

    // yoki alohida metod yarating
    public function insertMany(array $data)
    {
        return ClothesMaterial::insert($data);
    }
}
