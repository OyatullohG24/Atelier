<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StorageResource;
use App\Services\StorageService;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function __construct(protected StorageService $storage_service) {}

    public function index(Request $request)
    {
        $response = $this->storage_service->getAll();
        return response()->json([
            'status' => true,
            'message' => "Malumotlar olindi",
            'data' => StorageResource::collection($response)
        ]);
    }

    public function store(Request $request) 
    {
        return $this->storage_service->createStorage($request);
    }
}
