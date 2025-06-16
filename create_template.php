<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// إنشاء القالب مباشرة
DB::table('whatsapp_templates')->updateOrInsert(
    ['template_key' => 'teacher_welcome_with_password'],
    [
        'template_name' => 'ترحيب المعلم مع كلمة المرور',
        'template_content' => "أهلا بالأستاذ {teacher_name} 📚

تم إضافتك بنجاح في منصة غرب لإدارة حلقات القرآن الكريم
المسجد: {mosque_name}

بارك الله فيك وجعل عملك في خدمة كتاب الله في ميزان حسناتك 🤲
يمكنك الدخول من هنا
appgarb.vercel.app
برقم الهوية الخاصة بك وكلمة المرور  التالية :
{password}",
        'description' => 'رسالة ترحيب للمعلمين الجدد مع تضمين كلمة المرور',
        'variables' => json_encode(['teacher_name', 'mosque_name', 'password', 'identity_number']),
        'category' => 'welcome',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]
);

echo "تم إنشاء القالب بنجاح!\n";
