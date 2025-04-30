<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Employee;
use App\Models\CircleIncentive;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Filament\Admin\Pages\SalariesDashboard;

class TotalMonthlySalariesWidget extends BaseWidget
{
    // تحديد أن هذا الويدجت يظهر فقط في صفحة لوحة معلومات الرواتب
    protected static ?array $only = [
        'App\Filament\Admin\Pages\SalariesDashboard',
    ];
    
    protected int|string|array $columnSpan = 'full';
    
    // استخدام دالة للحصول على العنوان بدلاً من خاصية ثابتة
    protected function getHeading(): ?string
    {
        return 'إحصائيات الرواتب الشهرية';
    }
    
    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // حساب إجمالي الرواتب الشهرية باستخدام عمود salary الحقيقي
        $totalMonthlySalaries = Employee::sum('salary');
        
        // عدد الموظفين الذين لديهم رواتب
        $employeesCount = Employee::where('salary', '>', 0)->count();
        
        // حساب إجمالي المكافآت والحوافز للشهر الحالي
        // استخدام allocation_date بدلا من date
        $totalIncentives = CircleIncentive::whereMonth('allocation_date', $currentMonth)
            ->whereYear('allocation_date', $currentYear)
            ->sum('amount');

        return [
            Stat::make('إجمالي الرواتب الشهرية', number_format($totalMonthlySalaries) . ' ريال')
                ->description('الرواتب الأساسية لجميع الموظفين')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([
                    $totalMonthlySalaries * 0.85, 
                    $totalMonthlySalaries * 0.9, 
                    $totalMonthlySalaries * 0.95, 
                    $totalMonthlySalaries * 0.98, 
                    $totalMonthlySalaries * 0.99, 
                    $totalMonthlySalaries
                ]),

            Stat::make('إجمالي المكافآت والحوافز', number_format($totalIncentives) . ' ريال')
                ->description('للشهر الحالي')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([
                    $totalIncentives * 0.7, 
                    $totalIncentives * 0.8, 
                    $totalIncentives * 0.85, 
                    $totalIncentives * 0.9, 
                    $totalIncentives * 0.95, 
                    $totalIncentives
                ]),

            Stat::make('عدد الموظفين', number_format($employeesCount))
                ->description('الموظفون الذين يتقاضون رواتب')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning')
                ->chart([
                    $employeesCount * 0.8, 
                    $employeesCount * 0.85, 
                    $employeesCount * 0.9, 
                    $employeesCount * 0.95, 
                    $employeesCount * 0.98, 
                    $employeesCount
                ]),
        ];
    }
}
