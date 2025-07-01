<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CircleGroupResource\Pages;
use App\Models\CircleGroup;
use App\Models\QuranCircle;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CircleGroupResource extends Resource
{
    protected static ?string $model = CircleGroup::class;

    // تعيين أيقونة مناسبة للحلقات الفرعية
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    // إضافة الترجمات العربية
    protected static ?string $modelLabel = 'حلقة فرعية';
    protected static ?string $pluralModelLabel = 'الحلقات الفرعية';
    
    // وضع المورد في مجموعة التنقل المناسبة
    protected static ?string $navigationGroup = 'إدارة المساجد والحلقات';
    
    // ترتيب المورد في القائمة
    protected static ?int $navigationSort = 13;
    
    /**
     * إظهار عدد العناصر في مربع العدد (Badge) في القائمة
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    /**
     * تحديد لون مربع العدد (Badge) في القائمة
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary'; // اللون الأزرق للحلقات الفرعية
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الحلقة الفرعية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الحلقة الفرعية')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Select::make('quran_circle_id')
                            ->label('المدرسة القرآنية الرئيسية')
                            ->relationship('quranCircle', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('اسم المدرسة القرآنية')
                                    ->required(),
                            ]),
                            
                        Forms\Components\Select::make('teacher_id')
                            ->label('معلم الحلقة')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('اسم المعلم')
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('رقم الهاتف')
                                    ->tel(),
                            ]),
                            
                        Forms\Components\Select::make('status')
                            ->label('حالة الحلقة')
                            ->options([
                                'نشطة' => 'نشطة',
                                'معلقة' => 'معلقة',
                                'ملغاة' => 'ملغاة',
                            ])
                            ->default('نشطة')
                            ->required(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('معلومات إضافية')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('وصف الحلقة')
                            ->rows(3)
                            ->maxLength(500),
                            
                        Forms\Components\TagsInput::make('meeting_days')
                            ->label('أيام اللقاء')
                            ->placeholder('أضف أيام اللقاء')
                            ->helperText('اضغط Enter بعد كتابة كل يوم')
                            ->separator(','),
                            
                        Forms\Components\Textarea::make('additional_info')
                            ->label('معلومات إضافية')
                            ->rows(2)
                            ->maxLength(300),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الحلقة الفرعية')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('quranCircle.name')
                    ->label('المدرسة القرآنية')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('المعلم')
                    ->searchable()
                    ->sortable()
                    ->default('غير محدد'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'نشطة' => 'success',
                        'معلقة' => 'warning',
                        'ملغاة' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->counts('students')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('meeting_days')
                    ->label('أيام اللقاء')
                    ->badge()
                    ->separator(','),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'نشطة' => 'نشطة',
                        'معلقة' => 'معلقة',
                        'ملغاة' => 'ملغاة',
                    ]),
                    
                Tables\Filters\SelectFilter::make('quran_circle_id')
                    ->label('المدرسة القرآنية')
                    ->relationship('quranCircle', 'name'),
                    
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('المعلم')
                    ->relationship('teacher', 'name'),
                    
                Tables\Filters\Filter::make('has_students')
                    ->label('لديها طلاب')
                    ->query(fn (Builder $query): Builder => $query->has('students')),
                    
                Tables\Filters\Filter::make('no_students')
                    ->label('بدون طلاب')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('students')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                    
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                    
                Tables\Actions\Action::make('viewStudents')
                    ->label('عرض الطلاب')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.students.index', [
                        'tableFilters[circle_group_id][value]' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
                    
                Tables\Actions\Action::make('manageStudents')
                    ->label('إدارة الطلاب')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->modalHeading('إدارة طلاب الحلقة الفرعية')
                    ->modalSubheading('يمكنك إضافة أو إزالة الطلاب من هذه الحلقة الفرعية')
                    ->form([
                        Forms\Components\Select::make('add_students')
                            ->label('إضافة طلاب')
                            ->multiple()
                            ->options(function ($record) {
                                if (!$record || !$record->quran_circle_id) {
                                    return [];
                                }
                                
                                return \App\Models\Student::where('quran_circle_id', $record->quran_circle_id)
                                    ->whereNull('circle_group_id')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->preload()
                            ->searchable()
                            ->helperText('الطلاب غير المسجلين في أي حلقة فرعية'),
                            
                        Forms\Components\Select::make('remove_students')
                            ->label('إزالة طلاب')
                            ->multiple()
                            ->options(function ($record) {
                                if (!$record) {
                                    return [];
                                }
                                
                                return \App\Models\Student::where('circle_group_id', $record->id)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->preload()
                            ->searchable()
                            ->helperText('الطلاب الحاليين في هذه الحلقة الفرعية'),
                    ])
                    ->action(function (array $data, $record): void {
                        // إضافة الطلاب الجدد
                        if (!empty($data['add_students'])) {
                            \App\Models\Student::whereIn('id', $data['add_students'])
                                ->update(['circle_group_id' => $record->id]);
                        }
                        
                        // إزالة الطلاب المحددين
                        if (!empty($data['remove_students'])) {
                            \App\Models\Student::whereIn('id', $data['remove_students'])
                                ->update(['circle_group_id' => null]);
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('تمت إدارة الطلاب بنجاح')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation()
                    ->modalHeading('حذف الحلقة الفرعية')
                    ->modalDescription('هل أنت متأكد من حذف هذه الحلقة الفرعية؟ سيتم نقل جميع الطلاب إلى الحلقة الرئيسية.')
                    ->before(function ($record) {
                        // نقل الطلاب إلى الحلقة الرئيسية قبل الحذف
                        \App\Models\Student::where('circle_group_id', $record->id)
                            ->update(['circle_group_id' => null]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف الحلقات الفرعية المحددة')
                        ->modalDescription('هل أنت متأكد من حذف الحلقات الفرعية المحددة؟ سيتم نقل جميع الطلاب إلى الحلقات الرئيسية.')
                        ->before(function ($records) {
                            // نقل جميع الطلاب إلى الحلقات الرئيسية قبل الحذف
                            foreach ($records as $record) {
                                \App\Models\Student::where('circle_group_id', $record->id)
                                    ->update(['circle_group_id' => null]);
                            }
                        }),
                        
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'نشطة']))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('suspend')
                        ->label('تعليق المحدد')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => 'معلقة']))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('cancel')
                        ->label('إلغاء المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['status' => 'ملغاة']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCircleGroups::route('/'),
            'create' => Pages\CreateCircleGroup::route('/create'),
            'view' => Pages\ViewCircleGroup::route('/{record}'),
            'edit' => Pages\EditCircleGroup::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['quranCircle', 'teacher', 'students']);
    }
}
