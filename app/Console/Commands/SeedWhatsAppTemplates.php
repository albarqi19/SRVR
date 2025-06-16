<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppTemplate;

class SeedWhatsAppTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:seed-templates {--force : Force update existing templates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء القوالب الافتراضية لرسائل WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء إنشاء قوالب WhatsApp الافتراضية...');
        
        $templates = $this->getDefaultTemplates();
        $created = 0;
        $updated = 0;
        $skipped = 0;
        
        foreach ($templates as $template) {
            $existing = WhatsAppTemplate::where('template_key', $template['template_key'])->first();
            
            if ($existing) {
                if ($this->option('force')) {
                    $existing->update($template);
                    $updated++;
                    $this->line("✅ تم تحديث: {$template['template_name']}");
                } else {
                    $skipped++;
                    $this->line("⏭️  تم تخطي: {$template['template_name']} (موجود بالفعل)");
                }
            } else {
                WhatsAppTemplate::create($template);
                $created++;
                $this->line("✨ تم إنشاء: {$template['template_name']}");
            }
        }
        
        $this->newLine();
        $this->info("📊 تقرير العملية:");
        $this->line("• تم إنشاء: {$created} قالب");
        $this->line("• تم تحديث: {$updated} قالب");
        $this->line("• تم تخطي: {$skipped} قالب");
        $this->line("• المجموع: " . count($templates) . " قالب");
        
        $this->newLine();
        $this->info('🎉 تم إكمال العملية بنجاح!');
        
        return 0;
    }
    
    /**
     * Get default WhatsApp templates
     */
    private function getDefaultTemplates(): array
    {
        return [
            // قوالب المعلمين
            [
                'template_key' => 'teacher_welcome',
                'template_name' => 'ترحيب المعلم الجديد',
                'content' => "مرحباً الأستاذ {teacher_name} 📚\n\nتم إضافتك بنجاح في نظام مركز القرآن الكريم\nالمسجد: {mosque_name}\n\nبارك الله فيك وجعل عملك في خدمة كتاب الله في ميزان حسناتك 🤲",
                'description' => 'رسالة ترحيب للمعلمين الجدد عند إضافتهم للنظام',
                'variables' => ['teacher_name', 'mosque_name'],
                'category' => 'teacher',
                'is_active' => true,
            ],
            [
                'template_key' => 'teacher_login',
                'template_name' => 'إشعار تسجيل دخول المعلم',
                'content' => "🔐 تسجيل دخول جديد\n\nالأستاذ: {teacher_name}\nالمسجد: {mosque_name}\nالوقت: {login_time}\n\nمرحباً بك في نظام مركز القرآن الكريم 📚",
                'description' => 'إشعار تسجيل دخول المعلم للنظام',
                'variables' => ['teacher_name', 'mosque_name', 'login_time'],
                'category' => 'teacher',
                'is_active' => true,
            ],
            [
                'template_key' => 'teacher_assignment',
                'template_name' => 'تكليف المعلم بحلقة',
                'content' => "تكليف جديد 📋\n\nالأستاذ الفاضل: {teacher_name}\nتم تكليفك بحلقة: {circle_name}\nالمسجد: {mosque_name}\n\nنسأل الله أن يبارك في جهودكم ويجعلها في ميزان حسناتكم 🤲",
                'description' => 'إشعار تكليف المعلم بحلقة جديدة',
                'variables' => ['teacher_name', 'circle_name', 'mosque_name'],
                'category' => 'teacher',
                'is_active' => true,
            ],
            
            // قوالب الطلاب
            [
                'template_key' => 'student_welcome',
                'template_name' => 'ترحيب الطالب الجديد',
                'content' => "مرحباً {student_name} 🌟\n\nتم تسجيلك بنجاح في حلقة {circle_name}\n\nنسأل الله أن يبارك في حفظك ويجعلك من حملة كتابه الكريم 📖✨",
                'description' => 'رسالة ترحيب للطلاب الجدد',
                'variables' => ['student_name', 'circle_name'],
                'category' => 'student',
                'is_active' => true,
            ],
            [
                'template_key' => 'attendance_confirmation',
                'template_name' => 'تأكيد الحضور',
                'content' => "تم تسجيل حضور {student_name} ✅\n\n📅 التاريخ: {date}\n🕌 الحلقة: {circle_name}\n\nبارك الله فيك على المواظبة والحرص 🌟",
                'description' => 'تأكيد حضور الطالب',
                'variables' => ['student_name', 'date', 'circle_name'],
                'category' => 'attendance',
                'is_active' => true,
            ],
            [
                'template_key' => 'absence_notification',
                'template_name' => 'إشعار الغياب',
                'content' => "تنبيه غياب ⚠️\n\nالطالب: {student_name}\n📅 التاريخ: {date}\n🕌 الحلقة: {circle_name}\n\nنتطلع لحضورك في الجلسة القادمة بإذن الله 🤲",
                'description' => 'إشعار غياب الطالب',
                'variables' => ['student_name', 'date', 'circle_name'],
                'category' => 'attendance',
                'is_active' => true,
            ],
            
            // قوالب التسميع
            [
                'template_key' => 'session_completion',
                'template_name' => 'إكمال جلسة التسميع',
                'content' => "تم إكمال جلسة التسميع ✅\n\nالطالب: {student_name}\nنوع الجلسة: {session_type}\nالمحتوى: {content}\nالتقدير: {grade}\n\nأحسنت، بارك الله فيك وزادك علماً وحفظاً 🌟📚",
                'description' => 'إشعار إكمال جلسة التسميع',
                'variables' => ['student_name', 'session_type', 'content', 'grade'],
                'category' => 'session',
                'is_active' => true,
            ],
            [
                'template_key' => 'session_reminder',
                'template_name' => 'تذكير جلسة التسميع',
                'content' => "تذكير جلسة التسميع ⏰\n\nالطالب: {student_name}\nالوقت: {time}\nالحلقة: {circle_name}\n\nلا تنس حضور جلسة التسميع، بارك الله فيك 🤲",
                'description' => 'تذكير بموعد جلسة التسميع',
                'variables' => ['student_name', 'time', 'circle_name'],
                'category' => 'session',
                'is_active' => true,
            ],
            
            // قوالب أولياء الأمور
            [
                'template_key' => 'parent_notification',
                'template_name' => 'إشعار ولي الأمر',
                'content' => "{greeting} 🌹\n\nتحديث حول الطالب: {student_name}\n\n{message}\n\nجزاكم الله خيراً على متابعتكم وحرصكم 🤲\nمركز القرآن الكريم",
                'description' => 'إشعار عام لأولياء الأمور',
                'variables' => ['greeting', 'student_name', 'message'],
                'category' => 'parent',
                'is_active' => true,
            ],
            
            // قوالب الاختبارات
            [
                'template_key' => 'exam_notification',
                'template_name' => 'إشعار الاختبار',
                'content' => "إشعار اختبار 📝\n\nالطالب: {student_name}\nنوع الاختبار: {exam_type}\n📅 التاريخ: {exam_date}\n🕐 الوقت: {exam_time}\n\nندعو لك بالتوفيق والنجاح 🤲✨",
                'description' => 'إشعار موعد الاختبار',
                'variables' => ['student_name', 'exam_type', 'exam_date', 'exam_time'],
                'category' => 'exam',
                'is_active' => true,
            ],
            
            // قوالب التقارير
            [
                'template_key' => 'progress_report',
                'template_name' => 'تقرير التقدم',
                'content' => "تقرير التقدم الأسبوعي 📊\n\nالطالب: {student_name}\n\n📈 الحضور: {attendance}%\n📚 الآيات المحفوظة: {memorized_verses}\n📖 السورة الحالية: {current_surah}\n\nواصل تقدمك الممتاز، بارك الله فيك 🌟",
                'description' => 'تقرير التقدم الأسبوعي للطالب',
                'variables' => ['student_name', 'attendance', 'memorized_verses', 'current_surah'],
                'category' => 'report',
                'is_active' => true,
            ],
            
            // قوالب الإعلانات
            [
                'template_key' => 'general_announcement',
                'template_name' => 'إعلان عام',
                'content' => "📢 {title}\n\n{content}\n\nــــــــــــــــــــــــــــ\n{sender}\nمركز القرآن الكريم",
                'description' => 'إعلان عام من إدارة المركز',
                'variables' => ['title', 'content', 'sender'],
                'category' => 'announcement',
                'is_active' => true,
            ],
            
            // قوالب المناسبات
            [
                'template_key' => 'birthday_greeting',
                'template_name' => 'تهنئة عيد الميلاد',
                'content' => "🎉 كل عام وأنت بخير 🎂\n\nنبارك لـ {name}\nبمناسبة عيد ميلادك\n\nأعاده الله عليك بالخير والبركة\nوجعل عامك الجديد مليئاً بالإنجازات 🌟\n\nمركز القرآن الكريم 🤲",
                'description' => 'تهنئة بمناسبة عيد الميلاد',
                'variables' => ['name'],
                'category' => 'occasion',
                'is_active' => true,
            ],
        ];
    }
}
