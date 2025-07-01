@echo off
echo ============================================
echo اختبار API نقل الطلاب - خطوة بخطوة
echo ============================================

echo.
echo الخطوة 1: تسجيل الدخول للحصول على Token
curl.exe -X POST "https://inviting-pleasantly-barnacle.ngrok-free.app/api/login" ^
-H "ngrok-skip-browser-warning: true" ^
-H "Content-Type: application/json" ^
-d "{\"email\": \"demo@garb.sa\", \"password\": \"demo123\"}" ^
-o login_response.json

echo.
echo الخطوة 2: عرض استجابة تسجيل الدخول
type login_response.json

echo.
echo الخطوة 3: اختبار API نقل الطلاب (بدون token - سيفشل)
curl.exe -X POST "https://inviting-pleasantly-barnacle.ngrok-free.app/api/supervisors/student-transfer" ^
-H "ngrok-skip-browser-warning: true" ^
-H "Content-Type: application/json" ^
-d "{\"student_id\": 45, \"transfer_reason\": \"test\", \"requested_circle_id\": 1, \"notes\": \"test transfer\"}"

echo.
echo ============================================
echo انتهى الاختبار
echo ============================================
pause
