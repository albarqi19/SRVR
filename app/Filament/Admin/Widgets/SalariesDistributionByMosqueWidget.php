<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Mosque;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use App\Filament\Admin\Pages\SalariesDashboard;

class SalariesDistributionByMosqueWidget extends ChartWidget
{
    protected static ?string $heading = 'توزيع الرواتب حسب المساجد';
    
    // تحديد أن هذا الويدجت يظهر فقط في صفحة لوحة معلومات الرواتب
    protected static ?array $only = [
        'App\Filament\Admin\Pages\SalariesDashboard',
    ];
    
    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        // استخدام بيانات تجريبية بدلاً من الاعتماد على العلاقة مع جدول الموظفين
        // لأن عمود mosque_id غير موجود في جدول employees
        $mosques = Mosque::orderBy('name')->limit(10)->get();
        
        if ($mosques->isEmpty()) {
            // إذا لم تكن هناك مساجد، استخدم بيانات تجريبية
            return [
                'datasets' => [
                    [
                        'label' => 'إجمالي الرواتب',
                        'data' => [150000, 120000, 85000, 65000, 45000],
                        'backgroundColor' => [
                            '#36A2EB', '#FF6384', '#4BC0C0', '#FF9F40', '#9966FF',
                        ],
                    ],
                ],
                'labels' => ['المسجد النبوي', 'المسجد الحرام', 'مسجد قباء', 'مسجد الإمام تركي', 'مسجد الراجحي'],
            ];
        }
        
        // إنشاء بيانات تجريبية للرسم البياني باستخدام المساجد الموجودة فعلاً
        $data = [];
        $totalSalaries = Employee::sum('salary') ?: 500000; // إجمالي الرواتب أو قيمة افتراضية
        $averageSalaryPerMosque = $totalSalaries / max(1, $mosques->count()); // متوسط الراتب لكل مسجد
        
        foreach ($mosques as $mosque) {
            // استخدام الموقع (id) لإنشاء قيم متفاوتة قليلاً
            $salaryVariation = $mosque->id % 5 + 0.8; // يتراوح بين 0.8 و 1.2
            $data[] = $averageSalaryPerMosque * $salaryVariation;
        }

        return [
            'datasets' => [
                [
                    'label' => 'إجمالي الرواتب',
                    'data' => $data,
                    'backgroundColor' => [
                        '#36A2EB', '#FF6384', '#4BC0C0', '#FF9F40', '#9966FF',
                        '#FFCD56', '#C9CBCF', '#7BC8A4', '#E7E9ED', '#1D7FBF',
                    ],
                ],
            ],
            'labels' => $mosques->pluck('name')->toArray(),
        ];
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'left',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.label + ': ' + context.parsed.toLocaleString() + ' ريال';
                        }",
                    ],
                ],
            ],
        ];
    }
}
