<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Material\MaterialStoreRequest;
use App\Http\Resources\MaterialResource;
use App\Services\MaterialService;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function __construct(protected MaterialService $material_service) {}

    public function index(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => "Malumotlar olindi",
            'data' => MaterialResource::collection($this->material_service->getAll())
        ]);
    }

    public function store(MaterialStoreRequest $request)
    {
        return response()->json([
            'status' => true,
            'message' => "Malumotlar olindi",
            'data' => MaterialResource::make($this->material_service->createMaterial($request))
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'status' => true,
            'message' => "Malumotlar olindi",
            'data' => MaterialResource::make($this->material_service->getOne($id))
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->material_service->updateMaterial($id, $request);
        return response()->json([
            'status' => true,
            'message' => "Malumotlar olindi",
            'data' => MaterialResource::make($this->material_service->getOne($id))
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            'status' =>  $this->material_service->deleteMaterial($id),
            'message' => "Malumot o'chirildi.",
            'data' => []
        ]);
    }
}
