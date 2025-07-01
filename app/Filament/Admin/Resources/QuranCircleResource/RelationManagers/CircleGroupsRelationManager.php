<?php

namespace App\Filament\Admin\Resources\QuranCircleResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Illuminate\Support\Facades\Log;

class CircleGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'circleGroups';
    
    protected static ?string $title = 'الحلقات الفرعية';
    
    protected static ?string $label = 'حلقة فرعية';
    
    protected static ?string $pluralLabel = 'الحلقات الفرعية';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الحلقة')
                    ->required()
                    ->maxLength(255),
                
                Select::make('teacher_id')
                    ->label('معلم الحلقة')
                    ->options(function (RelationManager $livewire): array {
                        // الحصول على الحلقة الرئيسية
                        $quranCircle = $livewire->getOwnerRecord();
                        
                        // إضافة logging للتشخيص
                        Log::info('CircleGroupsRelationManager - جلب المعلمين للحلقة: ' . $quranCircle->name);
                        
                        $options = [];
                        
                        // 1. جلب المعلمين المكلفين في هذه الحلقة (أولوية عالية)
                        $currentCircleTeachers = $quranCircle->activeTeachers;
                        Log::info('عدد المعلمين في الحلقة الحالية: ' . $currentCircleTeachers->count());
                        
                        foreach ($currentCircleTeachers as $teacher) {
                            $options[$teacher->id] = $teacher->name . ' (مكلف في هذه الحلقة)';
                            Log::info('تمت إضافة المعلم: ' . $teacher->name);
                        }
                        
                        // 2. جلب جميع المعلمين المكلفين في أي حلقة قرآنية أخرى
                        $allAssignedTeachers = Teacher::whereHas('circleAssignments', function ($query) use ($quranCircle) {
                            $query->where('is_active', true)
                                  ->where('quran_circle_id', '!=', $quranCircle->id);
                        })->with(['circleAssignments.circle'])->get();
                        
                        Log::info('عدد المعلمين في حلقات أخرى: ' . $allAssignedTeachers->count());
                        
                        foreach ($allAssignedTeachers as $teacher) {
                            if (!isset($options[$teacher->id])) {
                                // التحقق من تعارض الأوقات
                                $hasConflict = false;
                                foreach ($teacher->circleAssignments as $assignment) {
                                    if ($assignment->is_active && $assignment->circle) {
                                        if ($assignment->circle->time_period === $quranCircle->time_period) {
                                            $hasConflict = true;
                                            break;
                                        }
                                    }
                                }
                                
                                if ($hasConflict) {
                                    $options[$teacher->id] = $teacher->name . ' (تعارض في الوقت ⚠️)';
                                } else {
                                    $options[$teacher->id] = $teacher->name . ' (مكلف في حلقة أخرى)';
                                }
                                Log::info('تمت إضافة المعلم من حلقة أخرى: ' . $teacher->name);
                            }
                        }
                        
                        // 3. جلب معلمي نفس المسجد كخيارات إضافية
                        if ($quranCircle->mosque_id) {
                            $mosqueTeachers = Teacher::where('mosque_id', $quranCircle->mosque_id)
                                ->whereDoesntHave('circleAssignments', function ($query) {
                                    $query->where('is_active', true);
                                })
                                ->orderBy('name')
                                ->get();
                            
                            Log::info('عدد معلمي المسجد غير المكلفين: ' . $mosqueTeachers->count());
                            
                            foreach ($mosqueTeachers as $teacher) {
                                if (!isset($options[$teacher->id])) {
                                    $options[$teacher->id] = $teacher->name . ' (من نفس المسجد)';
                                    Log::info('تمت إضافة معلم المسجد: ' . $teacher->name);
                                }
                            }
                        }
                        
                        // 4. إذا لم توجد خيارات، أضف جميع المعلمين
                        if (empty($options)) {
                            Log::warning('لا توجد خيارات! إضافة جميع المعلمين...');
                            $allTeachers = Teacher::orderBy('name')->get();
                            foreach ($allTeachers as $teacher) {
                                $options[$teacher->id] = $teacher->name . ' (جميع المعلمين)';
                            }
                        }
                        
                        Log::info('إجمالي الخيارات المُرجعة: ' . count($options), $options);
                        
                        return $options;
                    })
                    ->searchable()
                    ->preload()
                    ->helperText('يتم عرض جميع المعلمين المكلفين مع تحذير في حال تعارض الأوقات')
                    ->required()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                $teacher = Teacher::find($value);
                                $quranCircle = $this->getOwnerRecord();
                                
                                if ($teacher && $quranCircle) {
                                    // التحقق من تعارض الأوقات
                                    $conflicts = $teacher->circleAssignments()
                                        ->where('is_active', true)
                                        ->whereHas('circle', function ($query) use ($quranCircle) {
                                            $query->where('time_period', $quranCircle->time_period)
                                                  ->where('id', '!=', $quranCircle->id);
                                        })
                                        ->with('circle')
                                        ->get();
                                    
                                    if ($conflicts->isNotEmpty()) {
                                        $conflictCircles = $conflicts->map(function ($assignment) {
                                            return $assignment->circle->name;
                                        })->join('، ');
                                        
                                        $fail("تعارض في الأوقات! المعلم {$teacher->name} مكلف بالفعل في الحلقات التالية في نفس الوقت: {$conflictCircles}");
                                    }
                                }
                            };
                        }
                    ]),
                
                Select::make('status')
                    ->label('حالة الحلقة')
                    ->options([
                        'نشطة' => 'نشطة',
                        'معلقة' => 'معلقة',
                        'ملغاة' => 'ملغاة',
                    ])
                    ->default('نشطة')
                    ->required(),
                
                Textarea::make('description')
                    ->label('وصف الحلقة')
                    ->rows(3)
                    ->maxLength(500),
                    
                TagsInput::make('meeting_days')
                    ->label('أيام اللقاء')
                    ->placeholder('أضف أيام اللقاء')
                    ->helperText('اضغط Enter بعد كتابة كل يوم')
                    ->separator(','),
                
                Textarea::make('additional_info')
                    ->label('معلومات إضافية')
                    ->rows(2)
                    ->maxLength(300),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الحلقة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('المعلم')
                    ->searchable()
                    ->sortable(),
                
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
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('meeting_days')
                    ->label('أيام اللقاء')
                    ->badge(),
                
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
                    
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('المعلم')
                    ->relationship('teacher', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة حلقة فرعية')
                    ->modalHeading('إضافة حلقة فرعية جديدة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->modalHeading('تعديل بيانات الحلقة الفرعية'),
                
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->modalHeading('عرض تفاصيل الحلقة الفرعية'),
                
                Tables\Actions\Action::make('viewStudents')
                    ->label('عرض الطلاب')
                    ->icon('heroicon-o-users')
                    ->url(fn ($record) => route('filament.admin.resources.students.index', [
                        'tableFilters[circle_group_id][value]' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
                
                // زر حذف مخصص - أسلوب مختلف
                Tables\Actions\Action::make('deleteCircleGroup')
                    ->label('❌ حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('⚠️ حذف الحلقة الفرعية')
                    ->modalDescription('سيتم حذف هذه الحلقة ونقل جميع طلابها للحلقة الرئيسية')
                    ->modalSubmitActionLabel('حذف')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function ($record, $livewire) {
                        // نقل الطلاب أولاً
                        $studentsCount = Student::where('circle_group_id', $record->id)->count();
                        
                        if ($studentsCount > 0) {
                            Student::where('circle_group_id', $record->id)
                                ->update(['circle_group_id' => null]);
                            
                            Log::info("تم نقل {$studentsCount} طالب من الحلقة الفرعية '{$record->name}'");
                        }
                        
                        // حذف الحلقة
                        $record->delete();
                        
                        // إشعار بالنجاح
                        Notification::make()
                            ->title('✅ تم الحذف بنجاح')
                            ->body("تم حذف الحلقة ونقل {$studentsCount} طالب للحلقة الرئيسية")
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('addStudents')
                    ->label('إضافة طلاب')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('إضافة طلاب للحلقة الفرعية')
                    ->form([
                        Forms\Components\Select::make('students')
                            ->label('الطلاب')
                            ->multiple()
                            ->options(function ($livewire) {
                                $quranCircle = $livewire->getOwnerRecord();
                                return Student::where('quran_circle_id', $quranCircle->id)
                                    ->whereNull('circle_group_id')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->preload()
                            ->searchable()
                            ->helperText('يتم عرض الطلاب غير المسجلين في أي حلقة فرعية فقط'),
                    ])
                    ->action(function (array $data, $record): void {
                        // إضافة الطلاب المحددين للحلقة الفرعية
                        if (!empty($data['students'])) {
                            Student::whereIn('id', $data['students'])
                                ->update(['circle_group_id' => $record->id]);
                                
                            // عرض رسالة نجاح العملية
                            Notification::make()
                                ->title('تمت إضافة الطلاب بنجاح')
                                ->success()
                                ->send();
                        }
                    }),
                
                Tables\Actions\Action::make('removeStudents')
                    ->label('إزالة طلاب')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->modalHeading('إزالة طلاب من الحلقة الفرعية')
                    ->form([
                        Forms\Components\Select::make('students')
                            ->label('الطلاب')
                            ->multiple()
                            ->options(function ($record) {
                                return Student::where('circle_group_id', $record->id)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->preload()
                            ->searchable()
                            ->helperText('حدد الطلاب المراد إزالتهم من الحلقة الفرعية'),
                    ])
                    ->action(function (array $data, $record): void {
                        // إزالة الطلاب المحددين من الحلقة الفرعية
                        if (!empty($data['students'])) {
                            Student::whereIn('id', $data['students'])
                                ->update(['circle_group_id' => null]);
                                
                            // عرض رسالة نجاح العملية
                            Notification::make()
                                ->title('تمت إزالة الطلاب بنجاح')
                                ->success()
                                ->send();
                        }
                    }),
                
                Tables\Actions\DeleteAction::make()
                    ->label('🗑️ حذف الحلقة')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(true) // إجبار الزر على الظهور
                    ->modalHeading('⚠️ تأكيد حذف الحلقة الفرعية')
                    ->modalDescription('هل أنت متأكد من حذف هذه الحلقة الفرعية؟ سيتم نقل جميع الطلاب المسجلين فيها إلى الحلقة الرئيسية تلقائياً.')
                    ->modalSubmitActionLabel('نعم، احذف الحلقة')
                    ->modalCancelActionLabel('إلغاء')
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalIconColor('danger')
                    ->before(function ($record, $livewire) {
                        // نقل جميع الطلاب في هذه الحلقة الفرعية إلى الحلقة الرئيسية
                        $studentsCount = Student::where('circle_group_id', $record->id)->count();
                        
                        if ($studentsCount > 0) {
                            // الحصول على الحلقة الرئيسية
                            $quranCircle = $livewire->getOwnerRecord();
                            
                            // نقل الطلاب للحلقة الرئيسية
                            Student::where('circle_group_id', $record->id)
                                ->update(['circle_group_id' => null]);
                            
                            // تسجيل العملية في اللوج
                            Log::info("تم نقل {$studentsCount} طالب من الحلقة الفرعية '{$record->name}' إلى الحلقة الرئيسية '{$quranCircle->name}'");
                        }
                    })
                    ->after(function ($record) {
                        // عرض رسالة نجاح
                        Notification::make()
                            ->title('تم حذف الحلقة الفرعية بنجاح')
                            ->body('تم نقل جميع الطلاب إلى الحلقة الرئيسية تلقائياً.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف الحلقات الفرعية المحددة')
                        ->modalDescription('هل أنت متأكد من حذف الحلقات الفرعية المحددة؟ سيتم نقل جميع الطلاب المسجلين فيها إلى الحلقة الرئيسية تلقائياً.')
                        ->before(function ($records, $livewire) {
                            // نقل جميع الطلاب في الحلقات الفرعية المحددة إلى الحلقة الرئيسية
                            $totalStudentsCount = 0;
                            $quranCircle = $livewire->getOwnerRecord();
                            
                            foreach ($records as $record) {
                                $studentsCount = Student::where('circle_group_id', $record->id)->count();
                                $totalStudentsCount += $studentsCount;
                                
                                if ($studentsCount > 0) {
                                    // نقل الطلاب للحلقة الرئيسية
                                    Student::where('circle_group_id', $record->id)
                                        ->update(['circle_group_id' => null]);
                                    
                                    // تسجيل العملية في اللوج
                                    Log::info("تم نقل {$studentsCount} طالب من الحلقة الفرعية '{$record->name}' إلى الحلقة الرئيسية '{$quranCircle->name}'");
                                }
                            }
                            
                            if ($totalStudentsCount > 0) {
                                Log::info("إجمالي الطلاب المنقولين: {$totalStudentsCount}");
                            }
                        })
                        ->after(function () {
                            // عرض رسالة نجاح
                            Notification::make()
                                ->title('تم حذف الحلقات الفرعية بنجاح')
                                ->body('تم نقل جميع الطلاب إلى الحلقة الرئيسية تلقائياً.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
