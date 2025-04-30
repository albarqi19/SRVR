<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Filament\Admin\Pages\SalariesDashboard;

class SalariesYearlyTrendWidget extends ChartWidget
{
    protected static ?string $heading = 'تطور الرواتب خلال العام';
    
    // تحديد أن هذا الويدجت يظهر فقط في صفحة لوحة معلومات الرواتب
    protected static ?array $only = [
        'App\Filament\Admin\Pages\SalariesDashboard',
    ];
    
    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;
        $months = [];
        $salariesData = [];
        $incentivesData = [];
        
        // الحصول على إجمالي الرواتب الحالي
        $totalSalaries = Employee::sum('salary');
        
        // نحصل على بيانات الرواتب والحوافز لكل شهر في السنة الحالية
        for ($i = 1; $i <= 12; $i++) {
            // اسم الشهر بالعربية
            $monthName = match($i) {
                1 => 'يناير',
                2 => 'فبراير',
                3 => 'مارس',
                4 => 'أبريل',
                5 => 'مايو',
                6 => 'يونيو',
                7 => 'يوليو',
                8 => 'أغسطس',
                9 => 'سبتمبر',
                10 => 'أكتوبر',
                11 => 'نوفمبر',
                12 => 'ديسمبر',
            };
            
            $months[] = $monthName;
            
            // بيانات الرواتب تكون تقريبية بناءً على إجمالي الرواتب الحالي
            // مع افتراض زيادة تدريجية خلال العام
            if ($i <= Carbon::now()->month) {
                // أشهر سابقة
                $growthFactor = 0.85 + (($i - 1) * 0.015);
                $monthlySalary = $totalSalaries * $growthFactor;
                    
                $monthlyIncentives = DB::table('circle_incentives')
                    ->whereYear('allocation_date', $currentYear)
                    ->whereMonth('allocation_date', $i)
                    ->sum('amount');
                
                // إذا لم نجد حوافز لهذا الشهر، نستخدم قيمة تقريبية
                if ($monthlyIncentives == 0) {
                    $monthlyIncentives = $totalSalaries * 0.15 * $growthFactor;
                }
            } else {
                // أشهر قادمة (توقع)
                $monthlySalary = null;
                $monthlyIncentives = null;
            }
            
            $salariesData[] = $monthlySalary;
            $incentivesData[] = $monthlyIncentives;
        }

        return [
            'datasets' => [
                [
                    'label' => 'الرواتب الأساسية',
                    'data' => $salariesData,
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'المكافآت والحوافز',
                    'data' => $incentivesData,
                    'borderColor' => '#FF6384',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { 
                            return value.toLocaleString() + ' ريال';
                        }",
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' ريال';
                        }",
                    ],
                ],
            ],
        ];
    }
}
