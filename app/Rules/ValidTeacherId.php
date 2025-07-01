<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ValidTeacherId implements Rule
{
    private $errorMessage = '';
    private $foundUserId = null;

    /**
     * تحديد ما إذا كان validation rule يمر أم لا
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // أولاً: التحقق إذا كان teacher_id موجود في جدول users مباشرة
        $userExists = User::where('id', $value)->exists();
        
        if ($userExists) {
            $this->foundUserId = $value;
            return true;
        }

        // ثانياً: التحقق إذا كان teacher_id موجود في جدول teachers
        // ثم البحث عن user_id المرتبط
        $teacher = DB::table('teachers')->where('id', $value)->first();
        
        if (!$teacher) {
            $this->errorMessage = 'المعلم غير موجود في النظام';
            return false;
        }

        // البحث عن المستخدم المرتبط بهذا المعلم
        $user = User::where('email', 'teacher_' . $value . '@garb.com')
                   ->orWhere('name', $teacher->name)
                   ->first();

        if (!$user) {
            $this->errorMessage = 'المعلم موجود ولكن لا يوجد له حساب مستخدم. استخدم الأمر: php artisan create:user-for-teacher ' . $value;
            return false;
        }

        $this->foundUserId = $user->id;
        return true;
    }

    /**
     * احصل على رسالة validation error
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage ?: 'معرف المعلم غير صحيح';
    }

    /**
     * احصل على user_id الذي تم العثور عليه
     *
     * @return int|null
     */
    public function getFoundUserId()
    {
        return $this->foundUserId;
    }
}
