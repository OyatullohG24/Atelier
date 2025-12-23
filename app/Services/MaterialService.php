<?php

namespace App\Services;

use App\Repositories\Interfaces\MaterialRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class MaterialService
{
    public function __construct(protected MaterialRepositoryInterface $material_repository) {}

    public function getAll()
    {
        try {
            return $this->material_repository->all();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getOne($id)
    {
        try {
            return $this->material_repository->find($id);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function createMaterial($request)
    {
        try {
            $data = [
                'type' => $request->type,
                'color_code' => $request->color_code,
                'measurement' => $request->measurement,
                'name' => $request->material_name,
                'image' => $request->file('material_image')->store('material', 'public'),
                'code' => $request->code
            ];
            return $this->material_repository->create($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateMaterial($id, $request)
    {
        try {
            $material = $this->getOne($id);
            if ($request->file('material_image')) {
                Storage::disk('public')->delete($material->image);
                $path = $request->file('material_image')->store('material', 'public');
            } else {
                $path = $material->image;
            }
            $data = [
                'type' => $request->type,
                'color_code' => $request->color_code,
                'measurement' => $request->measurement,
                'name' => $request->clothes_name,
                'image' => $path,
                'code' => $request->code
            ];
            return $this->material_repository->update($material, $data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteMaterial($id)
    {
        try {
            return $this->material_repository->delete($this->getOne($id));
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
