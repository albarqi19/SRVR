<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Mosque extends Model
{
    use HasFactory;

    /**
     * الخصائص التي يمكن تعبئتها بشكل جماعي.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'neighborhood',
        'location_lat',
        'location_long',
        'contact_number',
    ];

    /**
     * الحلقات المرتبطة بهذا المسجد
     */
    public function quranCircles(): HasMany
    {
        return $this->hasMany(QuranCircle::class);
    }

    /**
     * المهام المرتبطة بهذا المسجد
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * المهام النشطة المرتبطة بهذا المسجد
     */
    public function activeTasks()
    {
        return $this->tasks()->whereNotIn('status', ['مكتملة', 'ملغاة']);
    }

    /**
     * المهام المتأخرة المرتبطة بهذا المسجد
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
     * حساب عدد الحلق في المسجد
     */
    public function getCirclesCountAttribute(): int
    {
        return $this->quranCircles()->count();
    }
}
