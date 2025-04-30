<?php

namespace App\Models;

use App\Models\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Teacher extends Model
{
    use HasFactory, HasActivityLog;

    // اسم العرض للنموذج في سجل الأنشطة
    public static $displayName = 'معلم';
    
    // اسم الوحدة للنموذج في سجل الأنشطة
    public static $moduleName = 'المعلمين';

    /**
     * الحقول المستبعدة من تسجيل الأنشطة
     */
    protected $activityExcluded = [
        'updated_at', 
        'created_at', 
        'remember_token',
    ];

    /**
     * الخصائص التي يمكن تعبئتها بشكل جماعي.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identity_number',
        'name',
        'nationality',
        'mosque_id',
        'system_number',
        'phone',
        'job_title',
        'task_type',
        'iban',
        'circle_type',
        'work_time',
        'absence_count',
        'ratel_activated',
        'start_date',
        'evaluation',
        'quran_circle_id',
    ];

    /**
     * الخصائص التي يجب تحويلها.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ratel_activated' => 'boolean',
        'start_date' => 'date',
    ];

    /**
     * الحصول على المسجد الذي يعمل فيه المعلم
     */
    public function mosque(): BelongsTo
    {
        return $this->belongsTo(Mosque::class);
    }

    /**
     * الحصول على الحلقة التي يدرس فيها المعلم
     */
    public function quranCircle(): BelongsTo
    {
        return $this->belongsTo(QuranCircle::class);
    }

    /**
     * التحقق ما إذا كان المعلم لديه راتب (بمكافأة)
     */
    public function getHasSalaryAttribute(): bool
    {
        return $this->task_type === 'معلم بمكافأة' || $this->task_type === 'مشرف';
    }

    /**
     * التحقق ما إذا كان المعلم محتسب (متطوع)
     */
    public function getIsVolunteerAttribute(): bool
    {
        return $this->task_type === 'معلم محتسب';
    }

    /**
     * التحقق ما إذا كان مشرفًا
     */
    public function getIsSupervisorAttribute(): bool
    {
        return $this->task_type === 'مشرف' || $this->task_type === 'مساعد مشرف';
    }

    /**
     * الحصول على اسم الحي من خلال المسجد
     */
    public function getNeighborhoodAttribute(): ?string
    {
        return $this->mosque ? $this->mosque->neighborhood : null;
    }

    /**
     * الحوافز التي تلقاها هذا المعلم
     */
    public function incentives(): HasMany
    {
        return $this->hasMany(TeacherIncentive::class);
    }

    /**
     * سجلات الحضور الخاصة بهذا المعلم
     */
    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    /**
     * رواتب المعلم
     */
    public function salaries(): MorphMany
    {
        return $this->morphMany(Salary::class, 'payee');
    }

    /**
     * المهام المرتبطة بالمعلم
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * المهام قيد التنفيذ للمعلم
     */
    public function activeTasks()
    {
        return $this->tasks()->whereNotIn('status', ['مكتملة', 'ملغاة']);
    }

    /**
     * المهام المتأخرة للمعلم
     */
    public function overdueTasks()
    {
        return $this->tasks()->where('status', 'متأخرة')
                 ->orWhere(function($query) {
                     $query->where('due_date', '<', now())
                           ->whereNotIn('status', ['مكتملة', 'ملغاة']);
                 });
    }

    /**
     * الحصول على إجمالي الحوافز التي تلقاها المعلم في شهر محدد
     *
     * @param string $month الشهر (مثل "يناير 2025")
     * @return float إجمالي الحوافز
     */
    public function getTotalIncentivesForMonth($month): float
    {
        return $this->incentives()
            ->whereHas('circleIncentive', function ($query) use ($month) {
                $query->where('month', $month);
            })
            ->sum('amount');
    }

    /**
     * الحصول على عدد أيام الحضور في فترة محددة
     *
     * @param string $period الفترة ('الفجر', 'العصر', 'المغرب', 'العشاء')
     * @param \DateTime $startDate تاريخ البداية
     * @param \DateTime $endDate تاريخ النهاية
     * @return int عدد أيام الحضور
     */
    public function getAttendanceDaysCount($period, $startDate, $endDate): int
    {
        return Attendance::countEligibleDays(
            Teacher::class,
            $this->id,
            $period,
            $startDate,
            $endDate
        );
    }

    /**
     * الحصول على وصف النشاط لكل حدث
     */
    public function getActivityDescriptionForEvent(string $event): string
    {
        return match($event) {
            'created' => "تم إضافة معلم جديد: {$this->name}",
            'updated' => "تم تعديل بيانات المعلم: {$this->name}",
            'deleted' => "تم حذف المعلم: {$this->name}",
            default => parent::getActivityDescriptionForEvent($event),
        };
    }
}
