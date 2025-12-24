<?php

namespace App\Http\Controllers\Web;

use App\Models\Clothes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ClothesService;
use Inertia\Inertia;

class ClothesController extends Controller
{
    public function __construct(protected ClothesService $clothes_service) {}

    public function index()
    {
        $clothes = $this->clothes_service->getAll();
        return Inertia::render('clothes', [
            'clothes' => $clothes,
            'clothes_count' => $clothes->count()
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

    public function update(Request $request, Clothes $clothes)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($clothes->image && \Storage::disk('public')->exists($clothes->image)) {
                \Storage::disk('public')->delete($clothes->image);
            }
            $imagePath = $request->file('image')->store('clothes', 'public');
            $validated['image'] = $imagePath;
        } else {
            // Don't update image field if no new image uploaded
            unset($validated['image']);
        }

        $clothes->update($validated);

        return redirect()->back()->with('success', 'Clothes updated successfully');
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
