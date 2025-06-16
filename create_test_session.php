<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // إنشاء جلسة تسميع للاختبار
    $session = new App\Models\RecitationSession();
    $session->session_id = 'RS-20250608-104641-0001';
    $session->student_id = 1;
    $session->teacher_id = 1;
    $session->surah_number = 1;
    $session->verse_start = 1;
    $session->verse_end = 7;
    $session->pages_recited = 1;
    $session->quality_rating = 'جيد';
    $session->grade = 85;
    $session->has_errors = false;
    $session->notes = 'جلسة اختبار';
    $session->save();
    
    echo "تم إنشاء جلسة التسميع بنجاح - ID: " . $session->id . "\n";
    echo "Session ID: " . $session->session_id . "\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
