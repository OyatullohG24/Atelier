<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Material\MaterialStoreRequest;
use App\Services\MaterialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function __construct(protected MaterialService $material_service) {}

    public function index(Request $request)
    {
        try {
            $materials = $this->material_service->getAll();

            return Inertia::render('material', [
                'materials' => $materials,
                'materials_count' => $materials->count(),
                'filters' => [
                    'search' => $request->get('search'),
                ],
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show($id)
    {
        try {
            return $this->material_service->getOne($id);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Log::info('Foydalanuvchi tizimga kirdi', [
            //     'request' => $request->all()
            // ]);
            $this->material_service->updateMaterial($id, $request);
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function store(MaterialStoreRequest $request)
    {
        try {
            $this->material_service->createMaterial($request);

            return redirect()->back()->with('success', 'Material created successfully');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete($id)
    {
        try {
            $this->material_service->deleteMaterial($id);

            return redirect()->back()->with('success', 'Material deleted successfully');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
