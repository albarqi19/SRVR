# المتطلبات التقنية التفصيلية - نظام المعلم متعدد الحلقات

## هيكل قاعدة البيانات المطلوب

### 1. جدول teacher_circle_assignments (الجدول الرئيسي الجديد) - نسخة مبسطة
```sql
CREATE TABLE teacher_circle_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id BIGINT UNSIGNED NOT NULL,
    quran_circle_id BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (quran_circle_id) REFERENCES quran_circles(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_teacher_circle (teacher_id, quran_circle_id),
    INDEX idx_teacher_active (teacher_id, is_active),
    INDEX idx_circle_active (quran_circle_id, is_active),
    INDEX idx_date_range (start_date, end_date)
);
```

### 2. تحديثات على الجداول الموجودة

#### جدول teachers
- **إزالة**: `quran_circle_id` (سيتم نقله إلى الجدول الجديد)
- **إضافة**: `max_circles` TINYINT DEFAULT 3 (الحد الأقصى للحلقات)
- **إضافة**: `is_multi_circle_enabled` BOOLEAN DEFAULT FALSE

#### جدول quran_circles  
- **إضافة**: `max_teachers` TINYINT DEFAULT 1 (الحد الأقصى للمعلمين)
- **إضافة**: `requires_primary_teacher` BOOLEAN DEFAULT TRUE

## النماذج (Models) المطلوب تطويرها

### 1. Teacher Model - التحديثات المطلوبة
```php
// العلاقات الجديدة
public function circleAssignments()
{
    return $this->hasMany(TeacherCircleAssignment::class);
}

public function activeCircles()
{
    return $this->belongsToMany(QuranCircle::class, 'teacher_circle_assignments')
                ->wherePivot('is_active', true)
                ->withPivot(['role', 'start_date', 'end_date', 'salary_percentage']);
}

public function primaryCircle()
{
    return $this->belongsToMany(QuranCircle::class, 'teacher_circle_assignments')
                ->wherePivot('role', 'primary')
                ->wherePivot('is_active', true)
                ->withPivot(['start_date', 'salary_percentage']);
}

// Accessors جديدة
public function getPrimaryCircleAttribute()
{
    return $this->primaryCircle()->first();
}

public function getTotalSalaryAttribute()
{
    return $this->circleAssignments()
                ->where('is_active', true)
                ->sum('salary_percentage') * $this->base_salary / 100;
}

// Scopes جديدة
public function scopeWithMultipleCircles($query)
{
    return $query->whereHas('circleAssignments', function($q) {
        $q->where('is_active', true);
    }, '>', 1);
}

public function scopeInCircle($query, $circleId)
{
    return $query->whereHas('circleAssignments', function($q) use ($circleId) {
        $q->where('quran_circle_id', $circleId)
          ->where('is_active', true);
    });
}
```

### 2. QuranCircle Model - التحديثات المطلوبة
```php
// العلاقات الجديدة
public function teacherAssignments()
{
    return $this->hasMany(TeacherCircleAssignment::class);
}

public function activeTeachers()
{
    return $this->belongsToMany(Teacher::class, 'teacher_circle_assignments')
                ->wherePivot('is_active', true)
                ->withPivot(['role', 'start_date', 'end_date']);
}

public function primaryTeacher()
{
    return $this->belongsToMany(Teacher::class, 'teacher_circle_assignments')
                ->wherePivot('role', 'primary')
                ->wherePivot('is_active', true);
}

// Accessors جديدة
public function getPrimaryTeacherAttribute()
{
    return $this->primaryTeacher()->first();
}

public function getTeachersCountAttribute()
{
    return $this->activeTeachers()->count();
}
```

### 3. TeacherCircleAssignment Model - نموذج جديد
```php
class TeacherCircleAssignment extends Model
{
    protected $fillable = [
        'teacher_id', 'quran_circle_id', 'role', 'is_active',
        'start_date', 'end_date', 'salary_percentage', 'weekly_hours', 'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'salary_percentage' => 'decimal:2',
    ];

    // العلاقات
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function circle()
    {
        return $this->belongsTo(QuranCircle::class, 'quran_circle_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('role', 'primary');
    }

    public function scopeInDateRange($query, $date = null)
    {
        $date = $date ?: now();
        return $query->where('start_date', '<=', $date)
                     ->where(function($q) use ($date) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', $date);
                     });
    }

    // Boot method للتحكم في القواعد
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($assignment) {
            // التأكد من عدم تعارض الأوقات
            static::validateTimeConflicts($assignment);
            
            // التأكد من عدم تجاوز الحد الأقصى للحلقات
            static::validateMaxCircles($assignment);
        });
    }
}
```

## واجهات Filament المطلوب تطويرها

### 1. TeacherResource - التحديثات
```php
// في Form
Select::make('circle_assignments')
    ->multiple()
    ->relationship('activeCircles', 'name')
    ->preload()
    ->searchable()
    ->createOptionForm([
        Select::make('quran_circle_id')
            ->relationship('circles', 'name')
            ->required(),
        Select::make('role')
            ->options([
                'primary' => 'معلم أساسي',
                'assistant' => 'معلم مساعد', 
                'substitute' => 'معلم بديل'
            ])
            ->default('primary'),
        DatePicker::make('start_date')
            ->required()
            ->default(now()),
        TextInput::make('salary_percentage')
            ->numeric()
            ->suffix('%')
            ->maxValue(100),
    ]),

// في Table
TextColumn::make('activeCircles.name')
    ->badge()
    ->separator(',')
    ->label('الحلقات'),

TextColumn::make('circles_count')
    ->counts('activeCircles')
    ->label('عدد الحلقات'),
```

### 2. TeacherCircleAssignmentResource - مورد جديد
```php
class TeacherCircleAssignmentResource extends Resource
{
    protected static ?string $model = TeacherCircleAssignment::class;
    protected static ?string $navigationLabel = 'تكليفات المعلمين';
    protected static ?string $navigationGroup = 'إدارة المعلمين';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('teacher_id')
                ->relationship('teacher', 'name')
                ->searchable()
                ->required(),
                
            Select::make('quran_circle_id')
                ->relationship('circle', 'name')
                ->searchable()
                ->required(),
                
            Select::make('role')
                ->options([
                    'primary' => 'معلم أساسي',
                    'assistant' => 'معلم مساعد',
                    'substitute' => 'معلم بديل'
                ])
                ->required()
                ->default('primary'),
                
            Toggle::make('is_active')
                ->default(true),
                
            DatePicker::make('start_date')
                ->required()
                ->default(now()),
                
            DatePicker::make('end_date'),
                
            TextInput::make('salary_percentage')
                ->numeric()
                ->suffix('%')
                ->minValue(0)
                ->maxValue(100),
                
            TextInput::make('weekly_hours')
                ->numeric()
                ->minValue(1)
                ->maxValue(40),
                
            Textarea::make('notes')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('teacher.name')
                ->searchable()
                ->sortable(),
                
            TextColumn::make('circle.name')
                ->searchable()
                ->sortable(),
                
            BadgeColumn::make('role')
                ->enum([
                    'primary' => 'أساسي',
                    'assistant' => 'مساعد',
                    'substitute' => 'بديل'
                ])
                ->colors([
                    'primary' => 'success',
                    'assistant' => 'warning',
                    'substitute' => 'secondary'
                ]),
                
            IconColumn::make('is_active')
                ->boolean(),
                
            TextColumn::make('start_date')
                ->date()
                ->sortable(),
                
            TextColumn::make('salary_percentage')
                ->suffix('%'),
        ])
        ->filters([
            SelectFilter::make('role')
                ->options([
                    'primary' => 'أساسي',
                    'assistant' => 'مساعد', 
                    'substitute' => 'بديل'
                ]),
                
            TernaryFilter::make('is_active'),
            
            Filter::make('date_range')
                ->form([
                    DatePicker::make('from'),
                    DatePicker::make('until'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                        )
                        ->when(
                            $data['until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                        );
                })
        ]);
    }
}
```

## API Endpoints المطلوبة

### 1. Teacher API - تحديثات
```php
// GET /api/teachers/{id}/circles
public function circles(Teacher $teacher)
{
    return TeacherCircleAssignmentResource::collection(
        $teacher->circleAssignments()->active()->with('circle')->get()
    );
}

// POST /api/teachers/{id}/assign-circle
public function assignCircle(Teacher $teacher, Request $request)
{
    $validated = $request->validate([
        'quran_circle_id' => 'required|exists:quran_circles,id',
        'role' => 'required|in:primary,assistant,substitute',
        'start_date' => 'required|date',
        'salary_percentage' => 'nullable|numeric|min:0|max:100',
    ]);

    $assignment = $teacher->circleAssignments()->create($validated);
    
    return new TeacherCircleAssignmentResource($assignment);
}
```

### 2. Circle API - تحديثات  
```php
// GET /api/circles/{id}/teachers
public function teachers(QuranCircle $circle)
{
    return TeacherResource::collection(
        $circle->activeTeachers()->with('circleAssignments')->get()
    );
}
```

## اختبارات مطلوبة

### 1. Unit Tests
```php
// TeacherTest
public function test_teacher_can_have_multiple_circles()
public function test_teacher_primary_circle_accessor()
public function test_teacher_total_salary_calculation()

// TeacherCircleAssignmentTest
public function test_assignment_validation_rules()
public function test_time_conflict_detection()
public function test_max_circles_limit()
```

### 2. Feature Tests
```php
// TeacherManagementTest
public function test_admin_can_assign_teacher_to_multiple_circles()
public function test_admin_can_view_teacher_circle_assignments()
public function test_assignment_date_validation()
```

## قواعد العمل (Business Rules)

### 1. قواعد التكليف
- المعلم يمكن أن يكون في عدة حلقات بأدوار مختلفة
- لا يمكن للمعلم أن يكون معلماً أساسياً في أكثر من حلقة واحدة
- يجب ألا تتعارض أوقات الحلقات للمعلم الواحد
- الحد الأقصى للحلقات للمعلم الواحد قابل للتخصيص

### 2. قواعد الراتب
- راتب المعلم = مجموع (راتب_أساسي × نسبة_الراتب_من_كل_حلقة)
- يجب ألا يتجاوز إجمالي النسب 100%
- المعلم الأساسي يحصل على نسبة أعلى من المساعد

### 3. قواعد التقييم
- تقييم منفصل لكل حلقة
- التقييم الإجمالي = متوسط مرجح للتقييمات

## خطة الترحيل (Migration Strategy)

### المرحلة 1: إعداد الجداول الجديدة
1. إنشاء جدول `teacher_circle_assignments`
2. نقل البيانات الحالية من `teachers.quran_circle_id`
3. التحقق من سلامة البيانات

### المرحلة 2: تحديث الكود
1. تحديث Models
2. تحديث واجهات Filament
3. تحديث API endpoints

### المرحلة 3: الاختبار والنشر
1. اختبار البيانات المنقولة
2. اختبار الوظائف الجديدة
3. النشر المرحلي

## توقيتات محددة للمراحل

### الأسبوع الأول (5 أيام)
- **اليوم 1-2**: المرحلة 1 (قاعدة البيانات)
- **اليوم 3-4**: المرحلة 2 (النماذج)
- **اليوم 5**: بداية المرحلة 3

### الأسبوع الثاني (5 أيام)
- **اليوم 1-3**: استكمال المرحلة 3 (الواجهات)
- **اليوم 4**: المرحلة 4 (منطق العمل)
- **اليوم 5**: المرحلة 5 (API)

### الأسبوع الثالث (3 أيام)
- **اليوم 1-2**: المرحلة 6 (الاختبار)
- **اليوم 3**: النشر والتشغيل
