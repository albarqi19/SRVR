<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;

class SalariesDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string $view = 'filament.admin.pages.salaries-dashboard';
    
    protected static ?string $navigationLabel = 'الرواتب والمالية';
    
    protected static ?string $title = 'لوحة معلومات الرواتب والمالية';
    
    protected static ?int $navigationSort = 3; // ترتيب الصفحة في القائمة
    
    protected static ?string $navigationGroup = 'لوحة المعلومات';

    public function getHeading(): string|Htmlable
    {
        return 'لوحة معلومات الرواتب والمالية';
    }
}
