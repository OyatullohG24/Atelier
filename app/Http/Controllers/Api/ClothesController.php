<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clothes\ClothesStoreRequest;
use App\Http\Resources\ClothesResource;
use App\Services\ClothesService;
use Illuminate\Http\Request;

class ClothesController extends Controller
{
    public function __construct(protected ClothesService $clothes_service) {}

    public function index(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => "Malumotlar olindi",
            'data' => ClothesResource::collection($this->clothes_service->getAll())
        ]);
    }

    public function store(ClothesStoreRequest $request)
    {
        return response()->json([
            'status' => true,
            'message' => "Malumotlar olindi",
            'data' => ClothesResource::make($this->clothes_service->createClothes($request))
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'status' => true,
            'message' => "Malumotlar olindi",
            'data' => ClothesResource::make($this->clothes_service->getOne($id))
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->clothes_service->updateClothes($id, $request);
        return response()->json([
            'status' => true,
            'message' => "Malumotlar olindi",
            'data' => ClothesResource::make($this->clothes_service->getOne($id))
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            'status' =>  $this->clothes_service->deleteClothes($id),
            'message' => "Malumot o'chirildi.",
            'data' => []
        ]);
    }
}
