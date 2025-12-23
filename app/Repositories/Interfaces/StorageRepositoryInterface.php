<?php

namespace App\Repositories\Interfaces;


use App\Models\Storage;

interface StorageRepositoryInterface
{
    public function all();
    public function find(int $id): ?Storage;
    public function create(array $data): Storage;
    public function update(Storage $storage, array $data): bool;
    public function delete(Storage $storage): bool;
}
