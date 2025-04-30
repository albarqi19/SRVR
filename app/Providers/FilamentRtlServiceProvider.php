<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\App;

class FilamentRtlServiceProvider extends ServiceProvider
{
    /**
     * تسجيل أي خدمات للتطبيق
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * تهيئة أي خدمات للتطبيق
     *
     * @return void
     */
    public function boot()
    {
        // تعيين اللغة العربية كلغة افتراضية لـ Filament
        App::setLocale('ar');
        
        // إضافة أنماط RTL إلى Filament عبر React Hook
        Filament::registerRenderHook(
            'panels::before',
            fn (): string => '<link rel="stylesheet" href="' . asset('css/filament-rtl.css') . '" />',
        );
    }
}