<?php

namespace App\Repositories\Interfaces;

use App\Models\Material;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MaterialRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?Material;

    public function findByCode(string $code): ?Material;

    public function getByType(string $type): Collection;

    public function create(array $data): Material;

    public function update(Material $material, array $data): bool;

    public function delete(Material $material): bool;

    public function codeExists(string $code, ?int $excludeId = null): bool;

    public function getStatistics(): array;
}
