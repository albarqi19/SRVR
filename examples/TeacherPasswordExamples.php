<?php
/**
 * أمثلة على استخدام دوال إرسال كلمة المرور عبر واتساب للمعلمين
 * 
 * يمكنك استخدام هذه الأمثلة في أي مكان في النظام
 */

use App\Models\Teacher;
use App\Helpers\WhatsAppHelper;

class TeacherPasswordExamples
{
    /**
     * مثال 1: إرسال رسالة ترحيب مع كلمة المرور عند إنشاء معلم جديد
     */
    public static function sendWelcomeToNewTeacher($teacherId)
    {
        $teacher = Teacher::find($teacherId);
        
        if ($teacher) {
            // الطريقة الأولى: استخدام دالة مساعدة من نموذج المعلم
            $sent = $teacher->sendWelcomeWithPassword();
            
            // الطريقة الثانية: استخدام WhatsAppHelper مباشرة
            // $sent = WhatsAppHelper::sendTeacherWelcomeWithPassword($teacher);
            
            return $sent;
        }
        
        return false;
    }

    /**
     * مثال 2: إعادة إرسال كلمة المرور الحالية
     */
    public static function resendCurrentPassword($teacherId)
    {
        $teacher = Teacher::find($teacherId);
        
        if ($teacher) {
            // إعادة إرسال كلمة المرور الأصلية المحفوظة
            return $teacher->resendPassword();
        }
        
        return false;
    }

    /**
     * مثال 3: إرسال كلمة مرور جديدة مؤقتة
     */
    public static function sendTemporaryPassword($teacherId, $temporaryPassword)
    {
        $teacher = Teacher::find($teacherId);
        
        if ($teacher) {
            // إرسال كلمة مرور مؤقتة
            return $teacher->sendPasswordViaWhatsApp($temporaryPassword);
        }
        
        return false;
    }

    /**
     * مثال 4: إرسال رسالة مخصصة مع كلمة المرور
     */
    public static function sendCustomPasswordMessage($teacherId, $password, $customMessage = null)
    {
        $teacher = Teacher::find($teacherId);
        
        if ($teacher) {
            if ($customMessage) {
                // إرسال رسالة مخصصة
                return WhatsAppHelper::sendCustomPasswordMessage($teacher, $password, $customMessage);
            } else {
                // استخدام القالب الافتراضي
                return WhatsAppHelper::sendCustomPasswordMessage($teacher, $password);
            }
        }
        
        return false;
    }

    /**
     * مثال 5: إرسال كلمة مرور جديدة وتحديثها في قاعدة البيانات
     */
    public static function resetAndSendNewPassword($teacherId)
    {
        $teacher = Teacher::find($teacherId);
        
        if ($teacher) {
            // توليد كلمة مرور جديدة
            $newPassword = Teacher::generateRandomPassword();
            
            // تحديث كلمة المرور في قاعدة البيانات
            $teacher->password = $newPassword; // سيحفظ في plain_password و password تلقائياً
            $teacher->must_change_password = true; // يجب تغيير كلمة المرور عند تسجيل الدخول
            $teacher->save();
            
            // إرسال كلمة المرور الجديدة عبر واتساب
            return $teacher->sendPasswordViaWhatsApp($newPassword);
        }
        
        return false;
    }

    /**
     * مثال 6: إرسال كلمة المرور لمجموعة من المعلمين
     */
    public static function sendPasswordToMultipleTeachers($teacherIds)
    {
        $results = [];
        
        foreach ($teacherIds as $teacherId) {
            $teacher = Teacher::find($teacherId);
            
            if ($teacher && $teacher->phone && $teacher->plain_password) {
                $sent = $teacher->sendWelcomeWithPassword();
                $results[$teacherId] = $sent;
            } else {
                $results[$teacherId] = false;
            }
        }
        
        return $results;
    }

    /**
     * مثال 7: إرسال كلمة المرور مع التحقق من الشروط
     */
    public static function sendPasswordWithValidation($teacherId)
    {
        $teacher = Teacher::find($teacherId);
        
        if (!$teacher) {
            return ['success' => false, 'message' => 'المعلم غير موجود'];
        }
        
        if (!$teacher->phone) {
            return ['success' => false, 'message' => 'رقم الهاتف غير موجود'];
        }
        
        if (!$teacher->plain_password) {
            return ['success' => false, 'message' => 'كلمة المرور غير متوفرة'];
        }
        
        $sent = $teacher->sendWelcomeWithPassword();
        
        if ($sent) {
            return ['success' => true, 'message' => 'تم إرسال كلمة المرور بنجاح'];
        } else {
            return ['success' => false, 'message' => 'فشل في إرسال كلمة المرور'];
        }
    }
}

/**
 * أمثلة على الاستخدام في Controllers أو أي مكان آخر:
 * 
 * // إرسال رسالة ترحيب للمعلم الجديد
 * TeacherPasswordExamples::sendWelcomeToNewTeacher(1);
 * 
 * // إعادة إرسال كلمة المرور
 * TeacherPasswordExamples::resendCurrentPassword(1);
 * 
 * // إرسال كلمة مرور مؤقتة
 * TeacherPasswordExamples::sendTemporaryPassword(1, '123456');
 * 
 * // إعادة تعيين كلمة مرور جديدة وإرسالها
 * TeacherPasswordExamples::resetAndSendNewPassword(1);
 * 
 * // إرسال رسالة مخصصة
 * $customMessage = "أهلاً {$teacher->name}, كلمة المرور الجديدة هي: {$password}";
 * TeacherPasswordExamples::sendCustomPasswordMessage(1, '654321', $customMessage);
 */
