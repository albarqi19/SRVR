<?php

namespace App\Filament\Admin\Widgets;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\CircleIncentive;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Filament\Admin\Pages\SalariesDashboard;

class BonusesAndIncentivesWidget extends BaseWidget
{
    protected static ?string $heading = 'الرواتب الإضافية والحوافز';
    
    // تحديد أن هذا الويدجت يظهر فقط في صفحة لوحة معلومات الرواتب
    protected static ?array $only = [
        'App\Filament\Admin\Pages\SalariesDashboard',
    ];
    
    protected int|string|array $columnSpan = 'full';
    
    protected function getTableQuery(): Builder
    {
        return CircleIncentive::query()
            ->whereYear('allocation_date', Carbon::now()->year)
            ->orderByDesc('allocation_date');
    }
    
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('quran_circle_id')
                ->label('رقم الحلقة')
                ->searchable()
                ->sortable(),
                
            TextColumn::make('sponsor_name')
                ->label('اسم الراعي/الكفيل')
                ->searchable()
                ->sortable(),
                
            TextColumn::make('amount')
                ->label('المبلغ')
                ->money('SAR')
                ->sortable(),
                
            TextColumn::make('remaining_amount')
                ->label('المبلغ المتبقي')
                ->money('SAR')
                ->sortable(),
                
            TextColumn::make('allocation_date')
                ->label('تاريخ التخصيص')
                ->date('d-m-Y')
                ->sortable(),
                
            TextColumn::make('notes')
                ->label('ملاحظات')
                ->limit(30)
                ->tooltip(fn (CircleIncentive $record): ?string => $record->notes),
        ];
    }
    
    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('month')
                ->label('الشهر')
                ->options([
                    '1' => 'يناير',
                    '2' => 'فبراير',
                    '3' => 'مارس',
                    '4' => 'أبريل',
                    '5' => 'مايو',
                    '6' => 'يونيو',
                    '7' => 'يوليو',
                    '8' => 'أغسطس',
                    '9' => 'سبتمبر',
                    '10' => 'أكتوبر',
                    '11' => 'نوفمبر',
                    '12' => 'ديسمبر',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['value'],
                            fn (Builder $query, $month): Builder => $query->whereMonth('allocation_date', $month)
                        );
                }),
                
            Tables\Filters\SelectFilter::make('year')
                ->label('السنة')
                ->options(function() {
                    $years = [];
                    $currentYear = date('Y');
                    for($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
                        $years[$y] = $y;
                    }
                    return $years;
                })
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['value'],
                            fn (Builder $query, $year): Builder => $query->whereYear('allocation_date', $year)
                        );
                }),
        ];
    }
}
