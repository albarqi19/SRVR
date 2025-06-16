#!/usr/bin/env pwsh
# اختبار شامل لـ API المشرف

Write-Host "=== اختبار شامل لـ API المشرف ===" -ForegroundColor Green
Write-Host ""

# 1. فحص عدد الطلاب في قاعدة البيانات مباشرة
Write-Host "1. فحص عدد الطلاب في قاعدة البيانات:" -ForegroundColor Yellow
try {
    $studentsCount = php -r @"
        require 'vendor/autoload.php';
        `$app = require_once 'bootstrap/app.php';
        `$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        `$count = \App\Models\Student::count();
        echo 'عدد الطلاب الإجمالي: ' . `$count . PHP_EOL;
        
        `$activeCount = \App\Models\Student::where('is_active', true)->count();
        echo 'عدد الطلاب النشطين: ' . `$activeCount . PHP_EOL;
        
        `$withCircleCount = \App\Models\Student::whereNotNull('quran_circle_id')->count();
        echo 'عدد الطلاب المرتبطين بحلقات: ' . `$withCircleCount . PHP_EOL;
        
        // عرض بعض الطلاب
        `$students = \App\Models\Student::with('quranCircle')->limit(5)->get();
        echo 'أول 5 طلاب:' . PHP_EOL;
        foreach (`$students as `$student) {
            echo '- ' . `$student->name . ' (ID: ' . `$student->id . ')';
            if (`$student->quranCircle) {
                echo ' - حلقة: ' . `$student->quranCircle->name;
            } else {
                echo ' - لا يوجد حلقة';
            }
            echo PHP_EOL;
        }
"@
    Write-Host $studentsCount -ForegroundColor Cyan
} catch {
    Write-Host "خطأ في فحص قاعدة البيانات: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# 2. فحص المشرفين الموجودين
Write-Host "2. فحص المشرفين الموجودين:" -ForegroundColor Yellow
try {
    $supervisorsInfo = php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        \$supervisors = \App\Models\User::role('supervisor')->get();
        echo 'عدد المشرفين: ' . \$supervisors->count() . PHP_EOL;
        
        foreach (\$supervisors as \$supervisor) {
            echo '- مشرف: ' . \$supervisor->name . ' (ID: ' . \$supervisor->id . ')' . PHP_EOL;
        }
        
        // فحص تعيينات المشرفين على الحلقات
        \$assignments = \App\Models\CircleSupervisor::with(['supervisor', 'quranCircle'])->get();
        echo 'عدد تعيينات المشرفين: ' . \$assignments->count() . PHP_EOL;
        
        foreach (\$assignments as \$assignment) {
            echo '- المشرف: ' . \$assignment->supervisor->name . ' -> الحلقة: ' . \$assignment->quranCircle->name . PHP_EOL;
        }
    "
    Write-Host $supervisorsInfo -ForegroundColor Cyan
} catch {
    Write-Host "خطأ في فحص المشرفين: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# 3. اختبار API المشرف الشامل
Write-Host "3. اختبار API المشرف الشامل:" -ForegroundColor Yellow

$supervisorIds = @(1, 2, 3)  # جرب عدة معرفات

foreach ($supervisorId in $supervisorIds) {
    Write-Host "اختبار المشرف رقم: $supervisorId" -ForegroundColor Magenta
    
    try {
        $response = curl.exe -X GET "http://localhost:8000/api/supervisor/comprehensive-overview?supervisor_id=$supervisorId" -H "Accept: application/json" -s
        
        if ($response) {
            $jsonResponse = $response | ConvertFrom-Json
            
            if ($jsonResponse.success) {
                Write-Host "✅ نجح API للمشرف $supervisorId" -ForegroundColor Green
                
                # عرض معلومات المشرف
                Write-Host "معلومات المشرف:" -ForegroundColor White
                Write-Host "- الاسم: $($jsonResponse.data.supervisor.name)" -ForegroundColor Gray
                Write-Host "- البريد: $($jsonResponse.data.supervisor.email)" -ForegroundColor Gray
                
                # عرض إحصائيات عامة
                Write-Host "الإحصائيات العامة:" -ForegroundColor White
                $stats = $jsonResponse.data.summary
                Write-Host "- المساجد: $($stats.total_mosques)" -ForegroundColor Gray
                Write-Host "- الحلقات: $($stats.total_circles)" -ForegroundColor Gray
                Write-Host "- الحلقات الفرعية: $($stats.total_circle_groups)" -ForegroundColor Gray
                Write-Host "- المعلمين: $($stats.total_teachers)" -ForegroundColor Gray
                Write-Host "- الطلاب: $($stats.total_students)" -ForegroundColor Gray
                
                # فحص تفاصيل المساجد والطلاب
                if ($jsonResponse.data.mosques_data) {
                    Write-Host "تفاصيل المساجد والطلاب:" -ForegroundColor White
                    
                    foreach ($mosque in $jsonResponse.data.mosques_data.PSObject.Properties) {
                        $mosqueData = $mosque.Value
                        Write-Host "  📍 مسجد: $($mosqueData.mosque.name)" -ForegroundColor Yellow
                        Write-Host "     - إجمالي الطلاب: $($mosqueData.mosque_summary.total_students)" -ForegroundColor Gray
                        
                        if ($mosqueData.circles) {
                            foreach ($circle in $mosqueData.circles) {
                                Write-Host "     🔵 حلقة: $($circle.circle_info.name)" -ForegroundColor Cyan
                                Write-Host "        - عدد الطلاب: $($circle.circle_summary.students_count)" -ForegroundColor Gray
                                
                                if ($circle.students -and $circle.students.Count -gt 0) {
                                    Write-Host "        - الطلاب:" -ForegroundColor Gray
                                    foreach ($student in $circle.students) {
                                        Write-Host "          👤 $($student.name)" -ForegroundColor White
                                    }
                                } else {
                                    Write-Host "        - لا يوجد طلاب في هذه الحلقة" -ForegroundColor Red
                                }
                                
                                if ($circle.circle_groups) {
                                    foreach ($group in $circle.circle_groups) {
                                        Write-Host "        🔸 حلقة فرعية: $($group.group_info.name)" -ForegroundColor Green
                                        Write-Host "           - عدد الطلاب: $($group.group_summary.students_count)" -ForegroundColor Gray
                                        
                                        if ($group.students -and $group.students.Count -gt 0) {
                                            Write-Host "           - الطلاب:" -ForegroundColor Gray
                                            foreach ($student in $group.students) {
                                                Write-Host "             👤 $($student.name)" -ForegroundColor White
                                            }
                                        } else {
                                            Write-Host "           - لا يوجد طلاب في هذه الحلقة الفرعية" -ForegroundColor Red
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                # خلاصة التحليل
                Write-Host ""
                Write-Host "📊 خلاصة التحليل:" -ForegroundColor Yellow
                $totalStudentsInAPI = $jsonResponse.data.summary.total_students
                if ($totalStudentsInAPI -eq 0) {
                    Write-Host "⚠️  عدد الطلاب في API = 0" -ForegroundColor Red
                    Write-Host "💡 هذا يعني أن الطلاب غير مرتبطين بالحلقات التي يشرف عليها هذا المشرف" -ForegroundColor Yellow
                } else {
                    Write-Host "✅ عدد الطلاب في API = $totalStudentsInAPI" -ForegroundColor Green
                }
                
            } else {
                Write-Host "❌ فشل API للمشرف $supervisorId" -ForegroundColor Red
                Write-Host "الرسالة: $($jsonResponse.message)" -ForegroundColor Red
            }
        } else {
            Write-Host "❌ لم يتم الحصول على استجابة من API للمشرف $supervisorId" -ForegroundColor Red
        }
        
    } catch {
        Write-Host "❌ خطأ في اختبار المشرف $supervisorId : $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Write-Host ""
}

# 4. فحص العلاقة بين الطلاب والحلقات والمشرفين
Write-Host "4. فحص العلاقة بين الطلاب والحلقات والمشرفين:" -ForegroundColor Yellow
try {
    $relationshipInfo = php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        // فحص الطلاب مع الحلقات
        \$studentsWithCircles = \App\Models\Student::with('quranCircle')->whereNotNull('quran_circle_id')->get();
        echo 'الطلاب المرتبطين بحلقات: ' . \$studentsWithCircles->count() . PHP_EOL;
        
        foreach (\$studentsWithCircles as \$student) {
            echo '- طالب: ' . \$student->name . ' -> حلقة: ' . \$student->quranCircle->name . ' (ID: ' . \$student->quran_circle_id . ')' . PHP_EOL;
        }
        
        // فحص الحلقات مع المشرفين
        \$circlesWithSupervisors = \App\Models\QuranCircle::with('circleSupervisors.supervisor')->get();
        echo PHP_EOL . 'الحلقات مع المشرفين:' . PHP_EOL;
        
        foreach (\$circlesWithSupervisors as \$circle) {
            echo '- حلقة: ' . \$circle->name . ' (ID: ' . \$circle->id . ')' . PHP_EOL;
            if (\$circle->circleSupervisors->count() > 0) {
                foreach (\$circle->circleSupervisors as \$assignment) {
                    echo '  مشرف: ' . \$assignment->supervisor->name . ' (ID: ' . \$assignment->supervisor_id . ')' . PHP_EOL;
                }
            } else {
                echo '  لا يوجد مشرفين لهذه الحلقة' . PHP_EOL;
            }
            
            // عدد الطلاب في هذه الحلقة
            \$studentsCount = \App\Models\Student::where('quran_circle_id', \$circle->id)->count();
            echo '  عدد الطلاب: ' . \$studentsCount . PHP_EOL;
        }
    "
    Write-Host $relationshipInfo -ForegroundColor Cyan
} catch {
    Write-Host "خطأ في فحص العلاقات: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== انتهى الاختبار الشامل ===" -ForegroundColor Green
