# Automated GET Routes test script for Instructor API
$BaseUrl = "http://localhost/api"
$Headers = @{
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

# In a real environment, you would log in first to set session or token:
# $LoginResponse = Invoke-RestMethod -Uri "$BaseUrl/login" -Method Post -Body '{"email":"instructor1@mindhub.test","password":"password"}'
# $Headers["Authorization"] = "Bearer " + $LoginResponse.token

$Routes = @(
    "/instructor/dashboard",
    "/instructor/dashboard/revenue-chart",
    "/instructor/dashboard/enrollment-chart",
    "/instructor/dashboard/top-courses",
    "/instructor/dashboard/alerts",
    "/instructor/dashboard/incomplete-courses",
    "/instructor/course-options",
    "/instructor/categories",
    "/instructor/courses/summary",
    "/instructor/courses",
    "/instructor/learners/summary",
    "/instructor/learners",
    "/instructor/questions/summary",
    "/instructor/questions/course-options",
    "/instructor/questions/lesson-options",
    "/instructor/questions",
    "/instructor/coupons/summary",
    "/instructor/coupons",
    "/instructor/coupons/course-options",
    "/instructor/revenues/summary",
    "/instructor/revenues",
    "/instructor/revenues/chart",
    "/instructor/revenues/enrollment-chart",
    "/instructor/revenues/top-courses",
    "/instructor/revenues/course-breakdown",
    "/instructor/withdrawals/summary",
    "/instructor/withdrawals",
    "/instructor/payout-accounts",
    "/instructor/payout-accounts/default",
    "/instructor/profile",
    "/instructor/account-center",
    "/instructor/notifications",
    "/instructor/notifications/unread-count"
)

Write-Host "Starting Automated GET Routes Test for Instructor API..." -ForegroundColor Cyan
$PassCount = 0
$FailCount = 0

foreach ($Route in $Routes) {
    $Uri = "$BaseUrl$Route"
    try {
        $Response = Invoke-WebRequest -Uri $Uri -Method Get -Headers $Headers -TimeoutSec 5 -ErrorAction Stop
        $StatusCode = $Response.StatusCode
        Write-Host "[PASS] Get $Route - Status: $StatusCode" -ForegroundColor Green
        $PassCount++
    } catch {
        $StatusCode = $_.Exception.Response.StatusCode
        if ($null -eq $StatusCode) {
            $StatusCode = "No Connection / Timeout"
        }
        Write-Host "[FAIL] Get $Route - Status: $StatusCode" -ForegroundColor Red
        $FailCount++
    }
}

Write-Host "`n=== Test Summary ===" -ForegroundColor Cyan
Write-Host "Total Passed: $PassCount" -ForegroundColor Green
Write-Host "Total Failed: $FailCount" -ForegroundColor Red
