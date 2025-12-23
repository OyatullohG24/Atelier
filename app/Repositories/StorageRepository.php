<?php

namespace App\Repositories;

use App\Models\Storage;
use App\Repositories\Interfaces\StorageRepositoryInterface;

class StorageRepository implements StorageRepositoryInterface
{
    public function __construct(protected Storage $storage) {}

    public function all()
    {
        return $this->storage->get();
    }

    public function create(array $data): Storage
    {
        return $this->storage->create($data);
    }

    public function find(int $id): ?Storage
    {
        return $this->storage->find($id);
    }

    public function update(Storage $storage, array $data): bool
    {
        return $storage->update($data);
    }

    public function delete(Storage $storage): bool
    {
        return $storage->delete();
    }
}
