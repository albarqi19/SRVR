#!/bin/bash
# Recitation Sessions API Test with cURL
# Run this file with: bash curl_api_test.sh

BASE_URL="http://localhost:8000/api"

echo "=== Recitation Sessions API Test with cURL ==="
echo ""

echo "1. Testing Health Check..."
curl.exe -X GET "$BASE_URL/health" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
echo ""
echo ""

echo "2. Creating New Recitation Session..."
curl.exe -X POST "$BASE_URL/recitation-sessions" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "teacher_id": 1,
    "quran_circle_id": 1,
    "session_date": "2024-01-15",
    "start_surah_number": 1,
    "end_surah_number": 1,
    "start_verse": 1,
    "end_verse": 7,
    "recitation_type": "مراجعة صغرى",
    "duration_minutes": 30,
    "grade": 85,
    "evaluation": "جيد جداً",
    "teacher_notes": "تحسن ملحوظ في التجويد"
  }' \
  --verbose
echo ""
echo ""

echo "3. Getting All Sessions..."
curl.exe -X GET "$BASE_URL/recitation-sessions" \
  -H "Accept: application/json"
echo ""
echo ""

echo "4. Getting Session by ID (assuming ID=1)..."
curl.exe -X GET "$BASE_URL/recitation-sessions/1" \
  -H "Accept: application/json"
echo ""
echo ""

echo "5. Adding Error to Session (assuming session_id=1)..."
curl.exe -X POST "$BASE_URL/recitation-sessions/1/errors" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "error_type": "تجويد",
    "surah_number": 1,
    "verse_number": 3,
    "description": "عدم وضوح في النطق"
  }'
echo ""
echo ""

echo "=== Test Complete ==="
