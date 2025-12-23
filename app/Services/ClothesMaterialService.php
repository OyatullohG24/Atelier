<?php

namespace App\Services;

use App\Repositories\Interfaces\ClothesMaterialRepositoryInterface;

class ClothesMaterialService
{
    public function __construct(protected ClothesMaterialRepositoryInterface $clothes_material_repository) {}

    public function getAll()
    {
        try {
            return $this->clothes_material_repository->all();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getOne($id)
    {
        try {
            return $this->clothes_material_repository->find($id);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function createClothesMaterial($request)
    {
        try {
            foreach ($request->materials as $material) {
                $data[] = [
                    'clothes_id' => $request->clothes_id,
                    'material_id' => $material['material_id'],
                    'amount' => $material['amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            return $this->clothes_material_repository->insertMany($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateClothesMaterial($id, $request)
    {
        try {
            $clothesMaterial = $this->getOne($id);
            $data = [
                'clothes_id' => $request->clothes_id ?? $clothesMaterial->clothes_id,
                'material_id' => $request->material_id ?? $clothesMaterial->material_id,
                'amount' => $request->amount ?? $clothesMaterial->amount
            ];
            return $this->clothes_material_repository->update($clothesMaterial, $data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteClothesMaterial($id)
    {
        try {
            return $this->clothes_material_repository->delete($this->getOne($id));
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
