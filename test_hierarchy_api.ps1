# Test Hierarchy API
Write-Host "Testing Hierarchy API..." -ForegroundColor Green

$baseUrl = "http://127.0.0.1:8000/api"

try {
    Write-Host "`n1. Testing Full Hierarchy..." -ForegroundColor Yellow
    $response1 = Invoke-RestMethod -Uri "$baseUrl/hierarchy" -Method GET
    Write-Host "Success! Total Mosques: $($response1.total_mosques)" -ForegroundColor Green
    Write-Host "Total Quran Schools: $($response1.total_quran_schools)" -ForegroundColor Green
    Write-Host "Total Sub Circles: $($response1.total_sub_circles)" -ForegroundColor Green
    
    Write-Host "`n2. Testing Mosque Hierarchy (Mosque ID: 1)..." -ForegroundColor Yellow
    $response2 = Invoke-RestMethod -Uri "$baseUrl/hierarchy/mosque/1" -Method GET
    Write-Host "Success! Mosque: $($response2.data.mosque_name)" -ForegroundColor Green
    Write-Host "Quran Schools: $($response2.data.total_quran_schools)" -ForegroundColor Green
    
    Write-Host "`n3. Testing Quran School Sub Circles (Circle ID: 1)..." -ForegroundColor Yellow
    $response3 = Invoke-RestMethod -Uri "$baseUrl/hierarchy/quran-school/1" -Method GET
    Write-Host "Success! School: $($response3.data.quran_school_name)" -ForegroundColor Green
    Write-Host "Sub Circles: $($response3.data.total_sub_circles)" -ForegroundColor Green
    
    Write-Host "`n4. Testing Hierarchy Stats..." -ForegroundColor Yellow
    $response4 = Invoke-RestMethod -Uri "$baseUrl/hierarchy/stats" -Method GET
    Write-Host "Success! Stats retrieved" -ForegroundColor Green
    Write-Host "Active Schools: $($response4.data.active_quran_schools)" -ForegroundColor Green
    
    Write-Host "`nAll tests passed!" -ForegroundColor Green
    
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
