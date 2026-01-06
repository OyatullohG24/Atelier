<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Clothes;
use App\Services\ClothesService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClothesController extends Controller
{
    public function __construct(protected ClothesService $clothes_service) {}

    public function index(Request $request)
    {
        $clothes = $this->clothes_service->getAll($request->all());

        return Inertia::render('clothes', [
            'clothes' => $clothes,
            'clothes_count' => $clothes->count(),
            'filters' => [
                'search' => $request->get('search'),
            ],
        ]);
    }

    public function show($id)
    {
        return $this->clothes_service->getOne($id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'clothes_name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'clothes_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        $this->clothes_service->createClothes($request);

        return redirect()->back()->with('success', 'Clothes created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'clothes_name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'clothes_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        // Axios uchun JSON response
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Clothes updated successfully',
                'data' => $this->clothes_service->updateClothes($id, $request),
            ]);
        }
    }

    public function destroy(Clothes $clothes)
    {
        // Delete image
        if ($clothes->image && \Storage::disk('public')->exists($clothes->image)) {
            \Storage::disk('public')->delete($clothes->image);
        }

        $clothes->delete();

        return redirect()->back()->with('success', 'Clothes deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:clothes,id',
        ]);
        $this->clothes_service->bulkDestroyClothes($request);

        return redirect()->back()->with('success', 'Clothes deleted successfully');
    }
}
