<x-filament-panels::page>
    <x-filament-widgets::widgets
        :columns="[
            'lg' => 4,
            'md' => 2,
            'sm' => 1,
        ]"
        :widgets="[
            \App\Filament\Admin\Widgets\TotalMonthlySalariesWidget::class,
            \App\Filament\Admin\Widgets\SalariesDistributionByMosqueWidget::class,
            \App\Filament\Admin\Widgets\SalariesYearlyTrendWidget::class,
            \App\Filament\Admin\Widgets\BonusesAndIncentivesWidget::class,
        ]"
    />
</x-filament-panels::page>
