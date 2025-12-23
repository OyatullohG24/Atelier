<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClothesMaterialService;
use Illuminate\Http\Request;

class ClothesMaterialController extends Controller
{
    public function __construct(protected ClothesMaterialService $clothes_material_service) {}

    public function store(Request $request)
    {
        $response = $this->clothes_material_service->createClothesMaterial($request);
        return response()->json([
            'status' => true,
            'message' => "Yaratildi",
            'data' => $response
        ]);
    }
}
