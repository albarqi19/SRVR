# Recitation Sessions API Test with cURL in PowerShell
# Run this file with: .\curl_api_test.ps1

$BASE_URL = "http://127.0.0.1:8000/api"

Write-Host "=== Recitation Sessions API Test with cURL ===" -ForegroundColor Green
Write-Host ""

Write-Host "1. Getting All Sessions..." -ForegroundColor Yellow
curl.exe -X GET "$BASE_URL/recitation/sessions" `
  -H "Accept: application/json"
Write-Host ""
Write-Host ""

Write-Host "2. Getting Session by ID (testing with ID=50)..." -ForegroundColor Yellow
curl.exe -X GET "$BASE_URL/recitation/sessions/50" `
  -H "Accept: application/json"
Write-Host ""
Write-Host ""

Write-Host "3. Adding Error to Session (testing with session_id=50)..." -ForegroundColor Yellow
$errorData = @'
{
  "error_type": "تجويد",
  "surah_number": 1,
  "verse_number": 3,
  "description": "عدم وضوح في النطق"
}
'@

curl.exe -X POST "$BASE_URL/recitation/errors" `
  -H "Accept: application/json" `
  -H "Content-Type: application/json; charset=utf-8" `
  -d $errorData
Write-Host ""
Write-Host ""

Write-Host "4. Getting Error Statistics..." -ForegroundColor Yellow
curl.exe -X GET "$BASE_URL/recitation/errors/stats/student/1" `
  -H "Accept: application/json"
Write-Host ""
Write-Host ""

Write-Host "5. Getting Session Statistics..." -ForegroundColor Yellow
curl.exe -X GET "$BASE_URL/recitation/sessions/stats/student/1" `
  -H "Accept: application/json"
Write-Host ""
Write-Host ""

Write-Host "=== Test Complete ===" -ForegroundColor Green
