<?php

use App\Http\Controllers\Web\ClothesController;
use App\Http\Controllers\Web\MaterialController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('clothes', [ClothesController::class, 'index'])->name('clothes.index');
Route::post('clothes', [ClothesController::class, 'store'])->name('clothes.store');
Route::get('clothes/{id}', [ClothesController::class, 'show'])->name('clothes.show');
Route::put('clothes/{id}', [ClothesController::class, 'update'])->name('clothes.update');
Route::delete('clothes/{id}', [ClothesController::class, 'destroy'])->name('clothes.delete');
Route::delete('clothes', [ClothesController::class, 'bulkDestroy'])->name('clothes.bulk-delete');

Route::get('materials', [MaterialController::class, 'index'])->name('material.index');
Route::post('materials', [MaterialController::class, 'store'])->name('material.store');
Route::delete('materials/{id}', [MaterialController::class, 'delete'])->name('material.delete');

require __DIR__.'/settings.php';
