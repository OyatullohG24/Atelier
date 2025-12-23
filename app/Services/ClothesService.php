<?php

namespace App\Services;

use App\Repositories\Interfaces\ClothesRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class ClothesService
{
    public function __construct(protected ClothesRepositoryInterface $clothes_repository) {}

    public function getAll()
    {
        try {
            return $this->clothes_repository->all();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getOne($id)
    {
        try {
            return $this->clothes_repository->find($id);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function createClothes($request)
    {
        try {
            $data = [
                'name' => $request->clothes_name,
                'image' => $request->file('clothes_image')->store('clothes', 'public'),
                'code' => $request->code
            ];
            return $this->clothes_repository->create($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateClothes($id, $request)
    {
        try {
            $clothes = $this->getOne($id);
            if ($request->file('clothes_image')) {
                Storage::disk('public')->delete($clothes->image);
                $path = $request->file('clothes_image')->store('clothes', 'public');
            } else {
                $path = $clothes->image;
            }
            $data = [
                'name' => $request->clothes_name ?? $clothes->name,
                'image' => $path,
                'code' => $request->code ?? $clothes->code
            ];
            return $this->clothes_repository->update($clothes, $data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteClothes($id)
    {
        try {
            return $this->clothes_repository->delete($this->getOne($id));
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
