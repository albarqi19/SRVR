#!/bin/bash

echo "=== اختبار API حضور الطلاب باستخدام cURL ==="
echo ""

# بيانات الاختبار
DATA='{
  "teacherId": 1,
  "date": "2025-06-08",
  "time": "14:30:00",
  "students": [
    {
      "studentId": 1,
      "status": "حاضر",
      "notes": "حضر في الوقت المحدد"
    }
  ]
}'

echo "البيانات المُرسلة:"
echo "$DATA"
echo ""

echo "إرسال طلب إلى API..."
echo ""

# إرسال طلب cURL
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "$DATA" \
  http://127.0.0.1:8000/api/attendance/record-batch \
  -w "\n\nHTTP Status: %{http_code}\n" \
  -v

echo ""
echo "=== انتهى الاختبار ==="
