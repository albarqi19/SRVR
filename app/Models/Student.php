<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    /**
     * الخصائص التي يمكن تعبئتها بشكل جماعي.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identity_number',
        'name',
        'nationality',
        'birth_date',
        'phone',
        'quran_circle_id',
        'mosque_id',
        'neighborhood',
        'enrollment_date',
        'absence_count',
        'parts_count',
        'last_exam',
        'memorization_plan',
        'review_plan',
        'teacher_notes',
        'supervisor_notes',
        'center_notes',
        'guardian_name',
        'guardian_phone',
        'education_level',
        'is_active',
    ];

    /**
     * الخصائص التي يجب تحويلها.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'enrollment_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * الحلقة القرآنية التي ينتمي إليها الطالب
     */
    public function quranCircle(): BelongsTo
    {
        return $this->belongsTo(QuranCircle::class);
    }

    /**
     * المسجد الذي ينتمي إليه الطالب
     */
    public function mosque(): BelongsTo
    {
        return $this->belongsTo(Mosque::class);
    }

    /**
     * حساب عمر الطالب
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) return null;
        return $this->birth_date->age;
    }

    /**
     * حساب المدة التي قضاها الطالب في المركز
     */
    public function getEnrollmentPeriodAttribute(): ?string
    {
        if (!$this->enrollment_date) return null;
        
        $years = $this->enrollment_date->diffInYears(now());
        $months = $this->enrollment_date->diffInMonths(now()) % 12;
        
        if ($years > 0) {
            return $years . ' سنة و ' . $months . ' شهر';
        }
        
        return $months . ' شهر';
    }

    /**
     * الحصول على اسم المعلم المسؤول عن الطالب
     */
    public function getTeacherNameAttribute(): ?string
    {
        $circle = $this->quranCircle;
        
        if (!$circle) return null;
        
        if ($circle->is_individual) {
            return $circle->teacher ? $circle->teacher->name : null;
        }
        
        return null; // سيتم تحديثه لاحقًا عندما يتم ربط المعلمين بالحلقات
    }
}
