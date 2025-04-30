<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TeacherResource\Pages;
use App\Filament\Admin\Resources\TeacherResource\RelationManagers;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\QuranCircle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    // تعيين أيقونة مناسبة للمعلمين
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    // إضافة الترجمات العربية
    protected static ?string $modelLabel = 'معلم';
    protected static ?string $pluralModelLabel = 'المعلمين';
    
    // تعيين مجموعة التنقل في القائمة
    protected static ?string $navigationGroup = 'التعليمية';
    
    // ترتيب ظهور المورد في القائمة
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // قسم البيانات الشخصية
                Forms\Components\Section::make('البيانات الشخصية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('identity_number')
                            ->label('رقم الهوية')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nationality')
                            ->label('الجنسية')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                // قسم بيانات العمل
                Forms\Components\Section::make('بيانات العمل')
                    ->schema([
                        Forms\Components\Select::make('mosque_id')
                            ->label('المسجد')
                            ->relationship('mosque', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('اسم المسجد')
                                    ->required(),
                                Forms\Components\TextInput::make('neighborhood')
                                    ->label('الحي')
                                    ->required(),
                            ]),
                        Forms\Components\Select::make('quran_circle_id')
                            ->label('الحلقة القرآنية')
                            ->relationship('quranCircle', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('job_title')
                            ->label('المسمى الوظيفي')
                            ->required(),
                        Forms\Components\Select::make('task_type')
                            ->label('نوع المهمة')
                            ->options([
                                'معلم بمكافأة' => 'معلم بمكافأة',
                                'معلم محتسب' => 'معلم محتسب',
                                'مشرف' => 'مشرف',
                                'مساعد مشرف' => 'مساعد مشرف',
                            ])
                            ->required(),
                        Forms\Components\Select::make('circle_type')
                            ->label('نوع الحلقة')
                            ->options([
                                'حلقة فردية' => 'حلقة فردية',
                                'حلقة جماعية' => 'حلقة جماعية',
                            ])
                            ->required(),
                        Forms\Components\Select::make('work_time')
                            ->label('وقت العمل')
                            ->options([
                                'العصر' => 'فترة العصر',
                                'المغرب' => 'فترة المغرب',
                                'العشاء' => 'فترة العشاء',
                                'العصر والمغرب' => 'فترة العصر والمغرب',
                                'المغرب والعشاء' => 'فترة المغرب والعشاء',
                                'العصر والعشاء' => 'فترة العصر والعشاء',
                                'جميع الفترات' => 'جميع الفترات',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('تاريخ بداية العمل'),
                        Forms\Components\TextInput::make('system_number')
                            ->label('الرقم في النظام')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                // قسم البيانات المالية والتقييم
                Forms\Components\Section::make('البيانات المالية والتقييم')
                    ->schema([
                        Forms\Components\TextInput::make('iban')
                            ->label('رقم الآيبان')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('ratel_activated')
                            ->label('تفعيل راتل')
                            ->onColor('success')
                            ->offColor('danger'),
                        Forms\Components\TextInput::make('absence_count')
                            ->label('عدد أيام الغياب')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('evaluation')
                            ->label('التقييم')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('identity_number')
                    ->label('رقم الهوية')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nationality')
                    ->label('الجنسية')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('task_type')
                    ->label('نوع المهمة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'معلم بمكافأة' => 'success',
                        'معلم محتسب' => 'info',
                        'مشرف' => 'primary',
                        'مساعد مشرف' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('mosque.name')
                    ->label('المسجد')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('circle_type')
                    ->label('نوع الحلقة')
                    ->badge(),
                Tables\Columns\TextColumn::make('work_time')
                    ->label('وقت العمل'),
                Tables\Columns\TextColumn::make('quranCircle.name')
                    ->label('الحلقة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('ratel_activated')
                    ->label('راتل مفعل')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
                Tables\Columns\TextColumn::make('evaluation')
                    ->label('التقييم')
                    ->numeric()
                    ->sortable()
                    ->suffix('%')
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'primary',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاريخ البداية')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('task_type')
                    ->label('نوع المهمة')
                    ->options([
                        'معلم بمكافأة' => 'معلم بمكافأة',
                        'معلم محتسب' => 'معلم محتسب',
                        'مشرف' => 'مشرف',
                        'مساعد مشرف' => 'مساعد مشرف',
                    ]),
                Tables\Filters\SelectFilter::make('mosque_id')
                    ->label('المسجد')
                    ->relationship('mosque', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('circle_type')
                    ->label('نوع الحلقة')
                    ->options([
                        'حلقة فردية' => 'حلقة فردية',
                        'حلقة جماعية' => 'حلقة جماعية',
                    ]),
                Tables\Filters\Filter::make('ratel_activated')
                    ->label('راتل مفعل')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('ratel_activated', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
