<?php


namespace App\Services;

use App\Repositories\Interfaces\StorageRepositoryInterface;

class StorageService
{
    public function __construct(protected StorageRepositoryInterface $storage_repository) {}

    public function getAll()
    {
        try {
            return $this->storage_repository->all();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getOne($id)
    {
        try {
            return $this->storage_repository->find($id);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function createStorage($request)
    {
        try {
            $data = [
                'work_amount' => 0,
                'price' => $request->price,
                'color_code' => $request->color_code,
                'come_amount' => $request->come_amount,
                'measurement' => $request->measurement,
                'code' => $request->code,
                'type' => $request->type
            ];
            return $this->storage_repository->create($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateStorage($id, $request)
    {
        try {
            $storage = $this->getOne($id);
            $data = [
                'work_amount' => 0 ?? $storage->work_amount,
                'price' => $request->price ?? $storage->price,
                'color_code' => $request->color_code ?? $storage->color_code,
                'come_amount' => $request->come_amount ?? $storage->come_amount,
                'measurement' => $request->measurement ?? $storage->measurement,
                'code' => $request->code ?? $storage->code,
                'type' => $request->type ?? $storage->type
            ];
            return $this->storage_repository->update($storage, $data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteStorage($id)
    {
        try {
            return $this->storage_repository->delete($this->getOne($id));
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
