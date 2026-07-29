$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$body = @{
    email = "instructor1@mindhub.test"
    password = "12345678"
} | ConvertTo-Json

try {
    $loginRes = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/auth/login" -Method POST -WebSession $session -ContentType "application/json" -Body $body
    Write-Host "LOGIN_STATUS: $($loginRes.StatusCode)"
    Write-Host "LOGIN_RESPONSE: $($loginRes.Content)"
} catch {
    $stream = $_.Exception.Response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $responseBody = $reader.ReadToEnd()
    Write-Host "LOGIN_ERROR_STATUS: $($_.Exception.Response.StatusCode)"
    Write-Host "LOGIN_ERROR_BODY: $responseBody"
}
