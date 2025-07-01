# Test Student Transfer API
Write-Host "Testing Student Transfer API..." -ForegroundColor Green

$baseUrl = "http://127.0.0.1:8000/api"

# أولاً نحتاج تسجيل دخول المشرف للحصول على Token
try {
    Write-Host "`n1. Testing Supervisor Login..." -ForegroundColor Yellow
    
    $loginData = @{
        identity_number = "1234567899"
        password = "demo123"
    } | ConvertTo-Json
    
    $loginHeaders = @{
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/auth/supervisor/login" -Method POST -Body $loginData -Headers $loginHeaders
    
    if ($loginResponse.success) {
        Write-Host "Login Success! Token received." -ForegroundColor Green
        $token = $loginResponse.data.token
        
        # Headers مع التوكن
        $authHeaders = @{
            "Content-Type" = "application/json"
            "Accept" = "application/json"
            "Authorization" = "Bearer $token"
        }
        
        Write-Host "`n2. Testing Student Transfer Request..." -ForegroundColor Yellow
        
        # بيانات طلب النقل (يجب تعديلها حسب البيانات الموجودة في قاعدة البيانات)
        $transferData = @{
            student_id = 1
            current_circle_id = 1
            requested_circle_id = 2
            current_circle_group_id = 1
            requested_circle_group_id = 3
            transfer_reason = "تحسين مستوى الطالب"
            notes = "الطالب متميز ويحتاج لحلقة أعلى"
        } | ConvertTo-Json
        
        try {
            $transferResponse = Invoke-RestMethod -Uri "$baseUrl/supervisors/student-transfer" -Method POST -Body $transferData -Headers $authHeaders
            
            if ($transferResponse.success) {
                Write-Host "Transfer Request Success!" -ForegroundColor Green
                Write-Host "Request ID: $($transferResponse.data.request_id)" -ForegroundColor Green
                Write-Host "Status: $($transferResponse.data.status)" -ForegroundColor Green
            } else {
                Write-Host "Transfer Request Failed: $($transferResponse.message)" -ForegroundColor Red
                if ($transferResponse.errors) {
                    Write-Host "Errors: $($transferResponse.errors | ConvertTo-Json)" -ForegroundColor Red
                }
            }
        } catch {
            Write-Host "Transfer Request Error: $($_.Exception.Message)" -ForegroundColor Red
            if ($_.Exception.Response) {
                $errorStream = $_.Exception.Response.GetResponseStream()
                $reader = New-Object System.IO.StreamReader($errorStream)
                $errorContent = $reader.ReadToEnd()
                Write-Host "Error Details: $errorContent" -ForegroundColor Red
            }
        }
        
        Write-Host "`n3. Testing Get Transfer Requests..." -ForegroundColor Yellow
        
        try {
            $getRequestsResponse = Invoke-RestMethod -Uri "$baseUrl/supervisors/transfer-requests" -Method GET -Headers $authHeaders
            
            if ($getRequestsResponse.success) {
                Write-Host "Get Transfer Requests Success!" -ForegroundColor Green
                Write-Host "Total Requests: $($getRequestsResponse.data.Count)" -ForegroundColor Green
            } else {
                Write-Host "Get Transfer Requests Failed: $($getRequestsResponse.message)" -ForegroundColor Red
            }
        } catch {
            Write-Host "Get Transfer Requests Error: $($_.Exception.Message)" -ForegroundColor Red
        }
        
    } else {
        Write-Host "Login Failed: $($loginResponse.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "Login Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Make sure the server is running and the supervisor credentials are correct." -ForegroundColor Yellow
}

Write-Host "`nTest completed!" -ForegroundColor Cyan
