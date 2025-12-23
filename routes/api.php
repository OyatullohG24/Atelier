<?php

use App\Http\Controllers\Api\ClothesController;
use App\Http\Controllers\Api\ClothesMaterialController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\StorageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('clothes', [ClothesController::class, 'index']);           // Barcha ro'yxat
Route::get('clothes/{id}', [ClothesController::class, 'show']);       // Bitta ko'rish
Route::post('clothes', [ClothesController::class, 'store']);          // Yangi qo'shish
Route::put('clothes/{id}', [ClothesController::class, 'update']);     // Yangilash (PUT)
Route::delete('clothes/{id}', [ClothesController::class, 'destroy']); // O'chirish

Route::get('material', [MaterialController::class, 'index']);           // Barcha ro'yxat
Route::get('material/{id}', [MaterialController::class, 'show']);       // Bitta ko'rish
Route::post('material', [MaterialController::class, 'store']);          // Yangi qo'shish
Route::put('material/{id}', [MaterialController::class, 'update']);     // Yangilash (PUT)
Route::delete('material/{id}', [MaterialController::class, 'destroy']); // O'chirish

Route::get('storage', [StorageController::class, 'index']);           // Barcha ro'yxat
Route::get('storage/{id}', [StorageController::class, 'show']);       // Bitta ko'rish
Route::post('storage', [StorageController::class, 'store']);          // Yangi qo'shish
Route::put('storage/{id}', [StorageController::class, 'update']);     // Yangilash (PUT)
Route::delete('storage/{id}', [StorageController::class, 'destroy']); // O'chirish


Route::post('clothes/material', [ClothesMaterialController::class, 'store']);          // Yangi qo'shish


Route::post('clothes/seller', [SellerController::class, 'sell']);          // Yangi qo'shish