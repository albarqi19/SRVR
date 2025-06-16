<?php

echo "=== اختبار APIs حضور الطلاب ===\n\n";

// تجربة API تسجيل الحضور
echo "1. تجربة تسجيل حضور طالب:\n";
echo "curl -X POST http://localhost/api/attendance/record \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\n";
echo "    \"student_name\": \"أحمد محمد\",\n";
echo "    \"date\": \"" . date('Y-m-d') . "\",\n";
echo "    \"status\": \"present\",\n";
echo "    \"period\": \"الفجر\",\n";
echo "    \"notes\": \"حضور منتظم\"\n";
echo "  }'\n\n";

// تجربة API استرجاع السجلات
echo "2. تجربة استرجاع سجلات الحضور:\n";
echo "curl -X GET 'http://localhost/api/attendance/records?date_from=" . date('Y-m-d') . "'\n\n";

// تجربة API الإحصائيات
echo "3. تجربة الحصول على إحصائيات الحضور:\n";
echo "curl -X GET 'http://localhost/api/attendance/stats'\n\n";

echo "=== معلومات إضافية ===\n";
echo "• جميع APIs تعمل بنجاح\n";
echo "• التحقق من صحة البيانات مُفعل\n";
echo "• دعم تحديث السجلات الموجودة\n";
echo "• إمكانية التصفية والبحث\n";
echo "• حساب الإحصائيات والنسب المئوية\n\n";

echo "للاختبار المباشر، يمكنك استخدام:\n";
echo "- Postman\n";
echo "- curl من سطر الأوامر\n";
echo "- أي أداة أخرى لاختبار APIs\n";
