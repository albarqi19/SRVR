<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Filament\Admin\Widgets\MosquesByRegionWidget;
use App\Filament\Admin\Widgets\CirclesByTypeWidget;
use App\Filament\Admin\Widgets\CirclesPerMosqueWidget;
use App\Filament\Admin\Widgets\OccupancyRatesWidget;
use App\Filament\Admin\Widgets\TeachersByMosqueWidget;

class FilamentWidgetsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Livewire::component('widget-mosques-by-region', MosquesByRegionWidget::class);
        Livewire::component('widget-circles-by-type', CirclesByTypeWidget::class);
        Livewire::component('widget-circles-per-mosque', CirclesPerMosqueWidget::class);
        Livewire::component('widget-occupancy-rates', OccupancyRatesWidget::class);
        Livewire::component('widget-teachers-by-mosque', TeachersByMosqueWidget::class);
    }
}