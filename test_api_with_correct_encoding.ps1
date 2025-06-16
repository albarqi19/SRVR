# Test API with correct UTF-8 encoding
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

# Set the encoding for PowerShell
$PSDefaultParameterValues['*:Encoding'] = 'utf8'

# Create the JSON body as a hashtable first, then convert to JSON
$bodyData = @{
    student_id = 1
    teacher_id = 1
    quran_circle_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 5
    recitation_type = "حفظ"
    duration_minutes = 30
    grade = 8.5
    evaluation = "ممتاز"
    teacher_notes = "Test session"
    status = "جارية"
}

# Convert to JSON with UTF-8 encoding
$jsonBody = $bodyData | ConvertTo-Json -Depth 10

# Display the JSON for debugging
Write-Host "JSON Body to be sent:" -ForegroundColor Green
Write-Host $jsonBody

try {
    # Make the API call with proper UTF-8 encoding
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions" `
        -Method POST `
        -Headers @{
            "Accept" = "application/json"
            "Content-Type" = "application/json; charset=utf-8"
        } `
        -Body ([System.Text.Encoding]::UTF8.GetBytes($jsonBody)) `
        -Verbose
    
    Write-Host "Success:" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "HTTP Status: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
    Write-Host "Error Message: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        try {
            $stream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($stream, [System.Text.Encoding]::UTF8)
            $responseBody = $reader.ReadToEnd()
            Write-Host "Response Body: $responseBody" -ForegroundColor Yellow
            $reader.Close()
            $stream.Close()
        } catch {
            Write-Host "Could not read response body: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}
