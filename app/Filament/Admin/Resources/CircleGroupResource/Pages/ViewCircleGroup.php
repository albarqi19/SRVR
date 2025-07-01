<?php

namespace App\Filament\Admin\Resources\CircleGroupResource\Pages;

use App\Filament\Admin\Resources\CircleGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
class ViewCircleGroup extends ViewRecord
{
    protected static string $resource = CircleGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل'),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->requiresConfirmation()
                ->modalHeading('حذف الحلقة الفرعية')
                ->modalDescription('هل أنت متأكد من حذف هذه الحلقة الفرعية؟ سيتم نقل جميع الطلاب إلى الحلقة الرئيسية.')
                ->before(function ($record) {
                    // نقل الطلاب إلى الحلقة الرئيسية قبل الحذف
                    \App\Models\Student::where('circle_group_id', $record->id)
                        ->update(['circle_group_id' => null]);
                }),
        ];
    }
    
    public function getTitle(): string
    {
        return 'عرض الحلقة الفرعية: ' . $this->record->name;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات الحلقة الفرعية')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم الحلقة الفرعية')
                            ->icon('heroicon-o-user-group'),
                            
                        TextEntry::make('quranCircle.name')
                            ->label('المدرسة القرآنية الرئيسية')
                            ->icon('heroicon-o-academic-cap'),
                            
                        TextEntry::make('teacher.name')
                            ->label('المعلم')
                            ->default('غير محدد')
                            ->icon('heroicon-o-user'),
                            
                        TextEntry::make('status')
                            ->label('حالة الحلقة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'نشطة' => 'success',
                                'معلقة' => 'warning',
                                'ملغاة' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(2),
                    
                Section::make('تفاصيل إضافية')
                    ->schema([
                        TextEntry::make('description')
                            ->label('وصف الحلقة')
                            ->default('لا يوجد وصف'),
                            
                        TextEntry::make('meeting_days')
                            ->label('أيام اللقاء')
                            ->badge()
                            ->separator(',')
                            ->default('غير محدد'),
                            
                        TextEntry::make('additional_info')
                            ->label('معلومات إضافية')
                            ->default('لا توجد معلومات إضافية'),
                    ])->columns(1),
                    
                Section::make('إحصائيات')
                    ->schema([
                        TextEntry::make('students_count')
                            ->label('عدد الطلاب')
                            ->state(fn ($record) => $record->students()->count())
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-users'),
                            
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('d/m/Y - H:i')
                            ->icon('heroicon-o-calendar'),
                            
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('d/m/Y - H:i')
                            ->icon('heroicon-o-clock'),
                    ])->columns(3),
            ]);
    }
}
