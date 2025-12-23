<?php

namespace App\Repositories\Interfaces;


use App\Models\Clothes;

interface ClothesRepositoryInterface
{
    public function all();
    public function find(int $id): ?Clothes;
    public function create(array $data): Clothes;
    public function update(Clothes $clothes, array $data): bool;
    public function delete(Clothes $clothes): bool;
}
