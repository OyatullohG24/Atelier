<?php

namespace App\Repositories;

use App\Models\Clothes;
use App\Repositories\Interfaces\ClothesRepositoryInterface;

class ClothesRepository implements ClothesRepositoryInterface
{
    public function __construct(protected Clothes $clothes) {}

    public function all()
    {
        return $this->clothes->get();
    }

    public function create(array $data): Clothes
    {
        return $this->clothes->create($data);
    }

    public function find(int $id): ?Clothes
    {
        return $this->clothes->find($id);
    }

    public function update(Clothes $clothes, array $data): bool
    {
        return $clothes->update($data);
    }

    public function delete(Clothes $clothes): bool
    {
        return $clothes->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return $this->clothes->whereIn('id', $ids)->delete();
    }
}
