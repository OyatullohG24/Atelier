<?php

namespace App\Repositories\Interfaces;


use App\Models\ClothesMaterial;

interface ClothesMaterialRepositoryInterface
{
    public function all();
    public function find(int $id): ?ClothesMaterial;
    public function create(array $data): ClothesMaterial;
    public function update(ClothesMaterial $clothesMaterial, array $data): bool;
    public function delete(ClothesMaterial $clothesMaterial): bool;
    public function insertMany(array $data);
}
