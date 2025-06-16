<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecitationSession;
use App\Models\RecitationError;

class AddErrorsToExistingSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:add-errors-to-session {session_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إضافة أخطاء تجريبية إلى جلسة تلاوة موجودة';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessionId = $this->argument('session_id');
        
        $this->info("🚀 بدء إضافة أخطاء إلى الجلسة: {$sessionId}");
        
        // البحث عن الجلسة
        $session = RecitationSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            $this->error("❌ لم يتم العثور على الجلسة: {$sessionId}");
            return 1;
        }
        
        $this->info("✅ تم العثور على الجلسة:");
        $this->line("   📚 الطالب: {$session->student->name}");
        $this->line("   👨‍🏫 المعلم: {$session->teacher->name}");
        $this->line("   📖 نوع التلاوة: {$session->recitation_type}");
        $this->line("   🎯 الدرجة: {$session->grade}");
        
        // إضافة الأخطاء
        $this->addErrors($session);
        
        // عرض الأخطاء المضافة
        $this->displayAddedErrors($session);
        
        $this->info('🎉 تم إضافة الأخطاء بنجاح!');
        
        return 0;
    }
    
    private function addErrors($session)
    {
        $errors = [
            [
                'surah_number' => 2,
                'verse_number' => 10,
                'word_text' => 'أولئك',
                'error_type' => 'نطق',
                'correction_note' => 'نطق الهمزة خاطئ',
                'teacher_note' => 'يحتاج تدريب على الهمزة',
                'is_repeated' => true,
                'severity_level' => 'متوسط'
            ],
            [
                'surah_number' => 2,
                'verse_number' => 15,
                'word_text' => 'يستهزئ',
                'error_type' => 'تجويد',
                'correction_note' => 'عدم إظهار الهمزة بوضوح',
                'teacher_note' => 'مراجعة أحكام التجويد',
                'is_repeated' => false,
                'severity_level' => 'خفيف'
            ],
            [
                'surah_number' => 1,
                'verse_number' => 6,
                'word_text' => 'الصراط',
                'error_type' => 'ترتيل',
                'correction_note' => 'سرعة في القراءة',
                'teacher_note' => 'يجب التأني والترتيل',
                'is_repeated' => false,
                'severity_level' => 'خفيف'
            ],
            [
                'surah_number' => 1,
                'verse_number' => 7,
                'word_text' => 'المغضوب',
                'error_type' => 'نطق',
                'correction_note' => 'نطق الضاد غير صحيح',
                'teacher_note' => 'مراجعة مخارج الحروف',
                'is_repeated' => true,
                'severity_level' => 'شديد'
            ]
        ];
        
        $this->info('📝 إضافة الأخطاء...');
        
        foreach ($errors as $errorData) {
            $error = RecitationError::create([
                'recitation_session_id' => $session->id,
                'session_id' => $session->session_id,
                'surah_number' => $errorData['surah_number'],
                'verse_number' => $errorData['verse_number'],
                'word_text' => $errorData['word_text'],
                'error_type' => $errorData['error_type'],
                'correction_note' => $errorData['correction_note'],
                'teacher_note' => $errorData['teacher_note'],
                'is_repeated' => $errorData['is_repeated'],
                'severity_level' => $errorData['severity_level']
            ]);
            
            $this->line("   ✅ تم إضافة خطأ {$errorData['error_type']} في سورة {$errorData['surah_number']} آية {$errorData['verse_number']}");
        }
        
        // تحديث الجلسة لتشير إلى وجود أخطاء
        $session->update(['has_errors' => true]);
        
        $this->info('✅ تم تحديث حالة الجلسة إلى "بها أخطاء"');
    }
    
    private function displayAddedErrors($session)
    {
        $this->info("\n📊 أخطاء الجلسة المضافة:");
        $this->line("+" . str_repeat("-", 95) . "+");
        $this->line("| سورة | آية | الكلمة     | نوع الخطأ | شدة الخطأ | متكرر | ملاحظة التصحيح                          |");
        $this->line("+" . str_repeat("-", 95) . "+");
        
        $errors = $session->errors()->orderBy('surah_number')->orderBy('verse_number')->get();
        
        foreach ($errors as $error) {
            $repeated = $error->is_repeated ? 'نعم' : 'لا';
            $this->line(sprintf(
                "| %-4s | %-3s | %-10s | %-9s | %-8s | %-4s | %-40s |",
                $error->surah_number,
                $error->verse_number,
                mb_substr($error->word_text, 0, 10),
                mb_substr($error->error_type, 0, 9),
                mb_substr($error->severity_level, 0, 8),
                $repeated,
                mb_substr($error->correction_note, 0, 40)
            ));
        }
        
        $this->line("+" . str_repeat("-", 95) . "+");
        $this->info("📈 إجمالي الأخطاء: " . $errors->count());
        
        // إحصائيات الأخطاء
        $this->info("\n📊 إحصائيات الأخطاء:");
        $errorTypes = $errors->groupBy('error_type');
        foreach ($errorTypes as $type => $typeErrors) {
            $this->line("   🔸 {$type}: " . $typeErrors->count() . " أخطاء");
        }
        
        $severityLevels = $errors->groupBy('severity_level');
        foreach ($severityLevels as $level => $levelErrors) {
            $this->line("   🎯 {$level}: " . $levelErrors->count() . " أخطاء");
        }
        
        $repeatedErrors = $errors->where('is_repeated', true)->count();
        $this->line("   🔄 أخطاء متكررة: {$repeatedErrors} من " . $errors->count());
    }
}
