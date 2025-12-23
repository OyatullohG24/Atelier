<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Clothes for binding
use App\Repositories\Interfaces\ClothesRepositoryInterface;
use App\Repositories\ClothesRepository;
// ClothesMaterial binding
use App\Repositories\Interfaces\ClothesMaterialRepositoryInterface;
use App\Repositories\ClothesMaterialRepository;
// Material binding
use App\Repositories\Interfaces\MaterialRepositoryInterface;
use App\Repositories\MaterialRepository;
// Storage binding
use App\Repositories\Interfaces\StorageRepositoryInterface;
use App\Repositories\StorageRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Clothes binding
        $this->app->bind(
            ClothesRepositoryInterface::class,
            ClothesRepository::class
        );

        // ClothesMaterial binding
        $this->app->bind(
            ClothesMaterialRepositoryInterface::class,
            ClothesMaterialRepository::class
        );

        // Material binding
        $this->app->bind(
            MaterialRepositoryInterface::class,
            MaterialRepository::class
        );

        // Storage binding
        $this->app->bind(
            StorageRepositoryInterface::class,
            StorageRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
