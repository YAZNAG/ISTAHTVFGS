<?php

namespace App\Providers;

use App\Models\Demande;
use App\Policies\DemandePolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix pour MySQL/MariaDB : longueur max index utf8mb4
        Schema::defaultStringLength(191);

        // Préchargement Vite
        Vite::prefetch(concurrency: 3);


        JsonResource::withoutWrapping();

        // Policies
        Gate::policy(Demande::class, DemandePolicy::class);
    }
}
