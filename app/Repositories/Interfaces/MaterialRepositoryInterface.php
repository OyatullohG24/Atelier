<?php

namespace App\Repositories\Interfaces;


use App\Models\Material;

interface MaterialRepositoryInterface
{
    public function all();
    public function find(int $id): ?Material;
    public function create(array $data): Material;
    public function update(Material $material, array $data): bool;
    public function delete(Material $material): bool;
}
