<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MaterialService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function __construct(protected MaterialService $material_service) {}

    public function index(Request $request)
    {
        $materials = $this->material_service->getAll();
        return Inertia::render('material', [
            'materials' => $materials,
            'materials_count' => $materials->count(),
            'filters' => [
                'search' => $request->get('search')
            ]
        ]);
    }
}
