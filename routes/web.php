<?php

use App\Http\Controllers\Web\ClothesController;
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


// Route::resource('clothes', ClothesController::class);
Route::get('clothes', [ClothesController::class, 'index'])->name('clothes.index');
Route::post('clothes', [ClothesController::class, 'store'])->name('clothes.store');
Route::get('clothes/{id}', [ClothesController::class, 'show'])->name('clothes.show');
Route::put('clothes/{id}', [ClothesController::class, 'bulkDestroy'])->name('clothes.bulk-delete');
Route::delete('clothes/{id}', [ClothesController::class, 'bulkDestroy'])->name('clothes.bulk-delete');
Route::delete('clothes', [ClothesController::class, 'bulkDestroy'])->name('clothes.bulk-delete');

require __DIR__ . '/settings.php';
