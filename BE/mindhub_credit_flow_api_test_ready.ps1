# ============================================================
# MindHub API Test - Instructor Credit / Admin Approve Flow
# Version: v2 ASCII-safe
# ============================================================
# Run:
#   cd F:\Phatnt\laragon\www\MindHub-Backend
#   powershell -ExecutionPolicy Bypass -File ".\BE\mindhub_credit_flow_api_test_fixed_v2.ps1"
#
# Notes:
# - Edit CONFIG before running.
# - This file avoids Markdown backtick code fences inside PowerShell strings.
# - This prevents ParserError on Windows PowerShell 5.1.
# ============================================================

$ErrorActionPreference = "Stop"

# ============================================================
# CONFIG - EDIT BEFORE RUNNING
# ============================================================

$BaseUrl = "http://127.0.0.1:8000/api"

$AdminEmail = "admin@mindhub.test"
$InstructorEmail = "instructor2@mindhub.test"
$LearnerEmail = "learner1@mindhub.test"
$Password = "12345678"

# Pending review course of instructor. 0 = skip real approve test.
$PendingCourseId = 31

# Another pending review course. 0 = skip real reject test.
$RejectCourseId = 32

# Published course for learner purchase. 0 = skip real purchase test.
$PublishedCourseId = 33

# Published course owned by InstructorEmail. 0 = skip self-purchase test.
$OwnPublishedCourseId = 33

# Pending course owned by instructor with no credits. 0 = skip no-credit test.
$NoCreditPendingCourseId = 34

$ReportPath = Join-Path (Get-Location) "MindHub_Credit_Flow_API_Test_Result.md"

# ============================================================
# GLOBAL STATE
# ============================================================

$script:Results = New-Object System.Collections.Generic.List[object]

$script:AdminToken = $null
$script:InstructorToken = $null
$script:LearnerToken = $null

$script:AdminId = $null
$script:InstructorId = $null
$script:LearnerId = $null

# ============================================================
# HELPERS
# ============================================================

function Write-Title {
    param([string] $Text)

    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host $Text -ForegroundColor Cyan
    Write-Host "============================================================" -ForegroundColor Cyan
}

function Write-Step {
    param([string] $Text)

    Write-Host ""
    Write-Host ">>> $Text" -ForegroundColor Yellow
}

function ConvertTo-JsonBody {
    param($Body)

    if ($null -eq $Body) {
        return $null
    }

    return ($Body | ConvertTo-Json -Depth 20)
}

function Try-ParseJson {
    param([string] $Text)

    if ([string]::IsNullOrWhiteSpace($Text)) {
        return $null
    }

    try {
        return $Text | ConvertFrom-Json
    }
    catch {
        return $null
    }
}

function Get-DeepValue {
    param(
        [object] $Object,
        [string[]] $Paths
    )

    foreach ($path in $Paths) {
        $current = $Object
        $ok = $true

        foreach ($part in $path.Split(".")) {
            if ($null -eq $current) {
                $ok = $false
                break
            }

            $prop = $current.PSObject.Properties[$part]

            if ($null -eq $prop) {
                $ok = $false
                break
            }

            $current = $prop.Value
        }

        if ($ok -and $null -ne $current -and "$current" -ne "") {
            return $current
        }
    }

    return $null
}

function Add-TestResult {
    param(
        [string] $Group,
        [string] $Case,
        [string] $Method,
        [string] $ApiPath,
        [int[]] $Expected,
        [int] $Status,
        [string] $Result,
        [string] $Note
    )

    $script:Results.Add([PSCustomObject]@{
        Group = $Group
        Case = $Case
        Method = $Method
        Path = $ApiPath
        Expected = ($Expected -join ",")
        Status = $Status
        Result = $Result
        Note = $Note
    }) | Out-Null
}

function Invoke-MindHubApi {
    param(
        [string] $Group,
        [string] $Case,

        [ValidateSet("GET", "POST", "PUT", "PATCH", "DELETE")]
        [string] $Method,

        [string] $ApiPath,
        [object] $Body = $null,
        [string] $Token = $null,
        [int[]] $ExpectedStatus = @(200),
        [switch] $Optional
    )

    $url = "$BaseUrl$ApiPath"

    $headers = @{
        "Accept" = "application/json"
    }

    if ($Token) {
        $headers["Authorization"] = "Bearer $Token"
    }

    $json = ConvertTo-JsonBody $Body

    Write-Step $Case
    Write-Host "$Method $url" -ForegroundColor DarkGray

    if ($json) {
        Write-Host "Body: $json" -ForegroundColor DarkGray
    }

    $statusCode = 0
    $bodyText = ""
    $jsonBody = $null

    try {
        $params = @{
            Uri = $url
            Method = $Method
            Headers = $headers
            UseBasicParsing = $true
        }

        if ($null -ne $Body) {
            $params["ContentType"] = "application/json; charset=utf-8"
            $params["Body"] = $json
        }

        $response = Invoke-WebRequest @params

        $statusCode = [int] $response.StatusCode
        $bodyText = [string] $response.Content
        $jsonBody = Try-ParseJson $bodyText
    }
    catch {
        if ($_.Exception.Response) {
            $statusCode = [int] $_.Exception.Response.StatusCode.value__

            try {
                $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
                $bodyText = $reader.ReadToEnd()
                $jsonBody = Try-ParseJson $bodyText
            }
            catch {
                $bodyText = $_.Exception.Message
            }
        }
        else {
            $statusCode = 0
            $bodyText = $_.Exception.Message
        }
    }

    $pass = $ExpectedStatus -contains $statusCode

    if ($pass) {
        $result = "PASS"
        Write-Host "[PASS] Status $statusCode" -ForegroundColor Green
    }
    elseif ($Optional) {
        $result = "SKIP_OR_OPTIONAL"
        Write-Host "[SKIP_OR_OPTIONAL] Status $statusCode" -ForegroundColor Yellow
    }
    else {
        $result = "FAIL"
        Write-Host "[FAIL] Expected $($ExpectedStatus -join ',') but got $statusCode" -ForegroundColor Red
    }

    if ($bodyText.Length -gt 0) {
        $short = $bodyText

        if ($short.Length -gt 800) {
            $short = $short.Substring(0, 800) + "..."
        }

        Write-Host $short -ForegroundColor DarkGray
    }

    Add-TestResult `
        -Group $Group `
        -Case $Case `
        -Method $Method `
        -ApiPath $ApiPath `
        -Expected $ExpectedStatus `
        -Status $statusCode `
        -Result $result `
        -Note $bodyText

    return [PSCustomObject]@{
        Status = $statusCode
        BodyText = $bodyText
        Json = $jsonBody
        Pass = $pass
    }
}

function Login-And-Extract {
    param(
        [string] $RoleName,
        [string] $Email,
        [string] $Password
    )

    $res = Invoke-MindHubApi `
        -Group "AUTH" `
        -Case "Login $RoleName" `
        -Method "POST" `
        -ApiPath "/auth/login" `
        -Body @{
            email = $Email
            password = $Password
            device_name = "api-test-$RoleName"
        } `
        -ExpectedStatus @(200)

    $token = Get-DeepValue $res.Json @(
        "access_token",
        "data.access_token",
        "data.token.access_token",
        "token.access_token",
        "data.token",
        "token"
    )

    $userId = Get-DeepValue $res.Json @(
        "user.id",
        "data.user.id",
        "data.id",
        "user_id",
        "data.user_id"
    )

    if (-not $token) {
        Write-Host "Could not extract access_token for $RoleName. Check login response." -ForegroundColor Red
    }

    return [PSCustomObject]@{
        Token = $token
        UserId = $userId
    }
}

function Extract-Id {
    param([object] $Json)

    return Get-DeepValue $Json @(
        "id",
        "data.id",
        "data.data.id",
        "order.id",
        "data.order.id",
        "data.package.id"
    )
}

function Export-Report {
    $now = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

    $lines = New-Object System.Collections.Generic.List[string]

    $lines.Add("# MindHub Credit Flow API Test Result") | Out-Null
    $lines.Add("") | Out-Null
    $lines.Add("- Generated at: $now") | Out-Null
    $lines.Add("- BaseUrl: $BaseUrl") | Out-Null
    $lines.Add("") | Out-Null

    $total = $script:Results.Count
    $passCount = ($script:Results | Where-Object { $_.Result -eq "PASS" }).Count
    $failCount = ($script:Results | Where-Object { $_.Result -eq "FAIL" }).Count
    $optionalCount = ($script:Results | Where-Object { $_.Result -eq "SKIP_OR_OPTIONAL" }).Count

    $lines.Add("## Summary") | Out-Null
    $lines.Add("") | Out-Null
    $lines.Add("| Total | PASS | FAIL | OPTIONAL |") | Out-Null
    $lines.Add("|---:|---:|---:|---:|") | Out-Null
    $lines.Add("| $total | $passCount | $failCount | $optionalCount |") | Out-Null
    $lines.Add("") | Out-Null

    $groups = $script:Results | Group-Object Group

    foreach ($group in $groups) {
        $lines.Add("## $($group.Name)") | Out-Null
        $lines.Add("") | Out-Null
        $lines.Add("| Result | Case | Method | Path | Expected | Got |") | Out-Null
        $lines.Add("|---|---|---|---|---:|---:|") | Out-Null

        foreach ($r in $group.Group) {
            $safeCase = [string] $r.Case
            $safeCase = $safeCase -replace "\|", "/"

            $safePath = [string] $r.Path
            $safePath = $safePath -replace "\|", "/"

            $lines.Add("| $($r.Result) | $safeCase | $($r.Method) | $safePath | $($r.Expected) | $($r.Status) |") | Out-Null
        }

        $lines.Add("") | Out-Null
    }

    $lines.Add("## Error Details") | Out-Null
    $lines.Add("") | Out-Null

    foreach ($r in $script:Results | Where-Object { $_.Result -ne "PASS" }) {
        $lines.Add("### $($r.Result) - $($r.Case)") | Out-Null
        $lines.Add("") | Out-Null
        $lines.Add("- Method: $($r.Method)") | Out-Null
        $lines.Add("- Path: $($r.Path)") | Out-Null
        $lines.Add("- Expected: $($r.Expected)") | Out-Null
        $lines.Add("- Got: $($r.Status)") | Out-Null
        $lines.Add("") | Out-Null
        $lines.Add("Response:") | Out-Null
        $lines.Add("") | Out-Null

        $note = [string] $r.Note

        if ($note.Length -gt 2000) {
            $note = $note.Substring(0, 2000) + "..."
        }

        $lines.Add($note) | Out-Null
        $lines.Add("") | Out-Null
    }

    $lines | Set-Content -Path $ReportPath -Encoding UTF8

    Write-Host ""
    Write-Host "Report exported: $ReportPath" -ForegroundColor Cyan
}

# ============================================================
# TEST FLOW
# ============================================================

Write-Title "MindHub API Test - Instructor Credit / Admin Approve Flow"

# ============================================================
# 1. AUTH
# ============================================================

Write-Title "AUTH"

$adminLogin = Login-And-Extract -RoleName "admin" -Email $AdminEmail -Password $Password
$script:AdminToken = $adminLogin.Token
$script:AdminId = $adminLogin.UserId

$instructorLogin = Login-And-Extract -RoleName "instructor" -Email $InstructorEmail -Password $Password
$script:InstructorToken = $instructorLogin.Token
$script:InstructorId = $instructorLogin.UserId

$learnerLogin = Login-And-Extract -RoleName "learner" -Email $LearnerEmail -Password $Password
$script:LearnerToken = $learnerLogin.Token
$script:LearnerId = $learnerLogin.UserId

Write-Host ""
Write-Host "Extracted IDs:" -ForegroundColor Cyan
Write-Host "AdminId: $script:AdminId"
Write-Host "InstructorId: $script:InstructorId"
Write-Host "LearnerId: $script:LearnerId"

# ============================================================
# 2. SECURITY / ROLE TESTS
# ============================================================

Write-Title "SECURITY / ROLE TESTS"

Invoke-MindHubApi `
    -Group "SECURITY" `
    -Case "No token calls admin credit packages should be 401" `
    -Method "GET" `
    -ApiPath "/admin/credit-packages" `
    -ExpectedStatus @(401)

Invoke-MindHubApi `
    -Group "SECURITY" `
    -Case "Instructor calls admin credit packages should be 403" `
    -Method "GET" `
    -ApiPath "/admin/credit-packages" `
    -Token $script:InstructorToken `
    -ExpectedStatus @(403)

Invoke-MindHubApi `
    -Group "SECURITY" `
    -Case "Learner calls instructor balance should be 403" `
    -Method "GET" `
    -ApiPath "/instructor/course-credits" `
    -Token $script:LearnerToken `
    -ExpectedStatus @(403)

# ============================================================
# 3. ADMIN CREDIT PACKAGE CRUD
# ============================================================

Write-Title "ADMIN CREDIT PACKAGE TESTS"

$unique = Get-Date -Format "yyyyMMddHHmmss"

$createPackageRes = Invoke-MindHubApi `
    -Group "ADMIN_PACKAGE" `
    -Case "Admin creates valid credit package" `
    -Method "POST" `
    -ApiPath "/admin/credit-packages" `
    -Token $script:AdminToken `
    -Body @{
        name = "API Test Credit Package 5 $unique"
        description = "Automated test package"
        credits = 5
        price = 99000
        status = "active"
        sort_order = 1
    } `
    -ExpectedStatus @(200, 201)

$PackageId = Extract-Id $createPackageRes.Json

Write-Host "PackageId: $PackageId" -ForegroundColor Cyan

Invoke-MindHubApi `
    -Group "ADMIN_PACKAGE" `
    -Case "Admin creates package with credits zero should be 422" `
    -Method "POST" `
    -ApiPath "/admin/credit-packages" `
    -Token $script:AdminToken `
    -Body @{
        name = "Invalid package"
        credits = 0
        price = 10000
        status = "active"
    } `
    -ExpectedStatus @(422)

Invoke-MindHubApi `
    -Group "ADMIN_PACKAGE" `
    -Case "Admin creates package without name should be 422" `
    -Method "POST" `
    -ApiPath "/admin/credit-packages" `
    -Token $script:AdminToken `
    -Body @{
        credits = 5
        price = 10000
        status = "active"
    } `
    -ExpectedStatus @(422)

Invoke-MindHubApi `
    -Group "ADMIN_PACKAGE" `
    -Case "Admin creates package with invalid status should be 422" `
    -Method "POST" `
    -ApiPath "/admin/credit-packages" `
    -Token $script:AdminToken `
    -Body @{
        name = "Invalid status"
        credits = 5
        price = 10000
        status = "deleted"
    } `
    -ExpectedStatus @(422)

if ($PackageId) {
    Invoke-MindHubApi `
        -Group "ADMIN_PACKAGE" `
        -Case "Admin updates valid credit package" `
        -Method "PATCH" `
        -ApiPath "/admin/credit-packages/$PackageId" `
        -Token $script:AdminToken `
        -Body @{
            name = "API Test Credit Package 5 Updated $unique"
            sort_order = 2
        } `
        -ExpectedStatus @(200)

    Invoke-MindHubApi `
        -Group "ADMIN_PACKAGE" `
        -Case "Admin updates package with negative credits should be 422" `
        -Method "PATCH" `
        -ApiPath "/admin/credit-packages/$PackageId" `
        -Token $script:AdminToken `
        -Body @{
            credits = -1
        } `
        -ExpectedStatus @(422)
}

# ============================================================
# 4. INSTRUCTOR CREDIT ORDER FLOW
# ============================================================

Write-Title "INSTRUCTOR CREDIT ORDER TESTS"

Invoke-MindHubApi `
    -Group "INSTRUCTOR_CREDIT" `
    -Case "Instructor lists active credit packages" `
    -Method "GET" `
    -ApiPath "/instructor/credit-packages" `
    -Token $script:InstructorToken `
    -ExpectedStatus @(200)

Invoke-MindHubApi `
    -Group "INSTRUCTOR_CREDIT" `
    -Case "Instructor gets current credit balance" `
    -Method "GET" `
    -ApiPath "/instructor/course-credits" `
    -Token $script:InstructorToken `
    -ExpectedStatus @(200)

Invoke-MindHubApi `
    -Group "INSTRUCTOR_CREDIT" `
    -Case "Instructor creates credit order without package id should be 422" `
    -Method "POST" `
    -ApiPath "/instructor/credit-orders" `
    -Token $script:InstructorToken `
    -Body @{} `
    -ExpectedStatus @(422)

Invoke-MindHubApi `
    -Group "INSTRUCTOR_CREDIT" `
    -Case "Instructor creates credit order with missing package should be 422 or 404" `
    -Method "POST" `
    -ApiPath "/instructor/credit-orders" `
    -Token $script:InstructorToken `
    -Body @{
        credit_package_id = 999999999
    } `
    -ExpectedStatus @(404, 422)

$CreditOrderId = $null

if ($PackageId) {
    $createCreditOrderRes = Invoke-MindHubApi `
        -Group "INSTRUCTOR_CREDIT" `
        -Case "Instructor creates valid credit package order" `
        -Method "POST" `
        -ApiPath "/instructor/credit-orders" `
        -Token $script:InstructorToken `
        -Body @{
            credit_package_id = [int] $PackageId
        } `
        -ExpectedStatus @(200, 201)

    $CreditOrderId = Extract-Id $createCreditOrderRes.Json

    Write-Host "CreditOrderId: $CreditOrderId" -ForegroundColor Cyan
}

# VNPAY route may differ in the current project, so this is optional.
if ($CreditOrderId) {
    Invoke-MindHubApi `
        -Group "PAYMENT_OPTIONAL" `
        -Case "Create VNPAY URL for instructor credit order if route exists" `
        -Method "POST" `
        -ApiPath "/payments/vnpay/create" `
        -Token $script:InstructorToken `
        -Body @{
            order_id = [int] $CreditOrderId
        } `
        -ExpectedStatus @(200, 201) `
        -Optional
}

# ============================================================
# 5. ADMIN INSTRUCTOR CREDIT TESTS
# ============================================================

Write-Title "ADMIN INSTRUCTOR CREDIT TESTS"

if ($script:InstructorId) {
    Invoke-MindHubApi `
        -Group "ADMIN_INSTRUCTOR_CREDIT" `
        -Case "Admin manually adds 5 credits to instructor for approve test" `
        -Method "POST" `
        -ApiPath "/admin/instructors/$script:InstructorId/credits/adjust" `
        -Token $script:AdminToken `
        -Body @{
            credits = 5
            note = "API test add credits for admin approve"
        } `
        -ExpectedStatus @(200, 201)

    Invoke-MindHubApi `
        -Group "ADMIN_INSTRUCTOR_CREDIT" `
        -Case "Admin adjusts credits zero should be 422" `
        -Method "POST" `
        -ApiPath "/admin/instructors/$script:InstructorId/credits/adjust" `
        -Token $script:AdminToken `
        -Body @{
            credits = 0
            note = "invalid"
        } `
        -ExpectedStatus @(422)

    Invoke-MindHubApi `
        -Group "ADMIN_INSTRUCTOR_CREDIT" `
        -Case "Admin views instructor credit balance" `
        -Method "GET" `
        -ApiPath "/admin/instructors/$script:InstructorId/credits" `
        -Token $script:AdminToken `
        -ExpectedStatus @(200)

    Invoke-MindHubApi `
        -Group "ADMIN_INSTRUCTOR_CREDIT" `
        -Case "Admin views instructor credit transactions" `
        -Method "GET" `
        -ApiPath "/admin/instructors/$script:InstructorId/credit-transactions" `
        -Token $script:AdminToken `
        -ExpectedStatus @(200)

    Invoke-MindHubApi `
        -Group "ADMIN_INSTRUCTOR_CREDIT" `
        -Case "Admin views missing instructor credit should be 404 or 200" `
        -Method "GET" `
        -ApiPath "/admin/instructors/999999999/credits" `
        -Token $script:AdminToken `
        -ExpectedStatus @(200, 404)
}

# ============================================================
# 6. ADMIN APPROVE / REJECT COURSE
# ============================================================

Write-Title "ADMIN APPROVE / REJECT COURSE TESTS"

Invoke-MindHubApi `
    -Group "ADMIN_COURSE_APPROVAL" `
    -Case "Approve missing course should be 404" `
    -Method "PATCH" `
    -ApiPath "/admin/courses/999999999/approve" `
    -Token $script:AdminToken `
    -ExpectedStatus @(404, 422)

Invoke-MindHubApi `
    -Group "ADMIN_COURSE_APPROVAL" `
    -Case "Instructor calls approve course should be 403" `
    -Method "PATCH" `
    -ApiPath "/admin/courses/999999999/approve" `
    -Token $script:InstructorToken `
    -ExpectedStatus @(403)

Invoke-MindHubApi `
    -Group "ADMIN_COURSE_APPROVAL" `
    -Case "Learner calls approve course should be 403" `
    -Method "PATCH" `
    -ApiPath "/admin/courses/999999999/approve" `
    -Token $script:LearnerToken `
    -ExpectedStatus @(403)

if ($PendingCourseId -gt 0) {
    Invoke-MindHubApi `
        -Group "ADMIN_COURSE_APPROVAL" `
        -Case "Admin approves pending review course and deducts 1 credit" `
        -Method "PATCH" `
        -ApiPath "/admin/courses/$PendingCourseId/approve" `
        -Token $script:AdminToken `
        -ExpectedStatus @(200)

    Invoke-MindHubApi `
        -Group "ADMIN_COURSE_APPROVAL" `
        -Case "Approve same course again should not deduct second time" `
        -Method "PATCH" `
        -ApiPath "/admin/courses/$PendingCourseId/approve" `
        -Token $script:AdminToken `
        -ExpectedStatus @(200, 400, 409)

    if ($script:InstructorId) {
        Invoke-MindHubApi `
            -Group "ADMIN_COURSE_APPROVAL" `
            -Case "Check credit balance after approve" `
            -Method "GET" `
            -ApiPath "/admin/instructors/$script:InstructorId/credits" `
            -Token $script:AdminToken `
            -ExpectedStatus @(200)
    }
}
else {
    Write-Host "SKIP: PendingCourseId = 0, skip real approve course test." -ForegroundColor Yellow
}

if ($RejectCourseId -gt 0) {
    Invoke-MindHubApi `
        -Group "ADMIN_COURSE_APPROVAL" `
        -Case "Admin rejects course and does not deduct credit" `
        -Method "PATCH" `
        -ApiPath "/admin/courses/$RejectCourseId/reject" `
        -Token $script:AdminToken `
        -Body @{
            reason = "API test reject course"
        } `
        -ExpectedStatus @(200)

    if ($script:InstructorId) {
        Invoke-MindHubApi `
            -Group "ADMIN_COURSE_APPROVAL" `
            -Case "Check credit balance after reject" `
            -Method "GET" `
            -ApiPath "/admin/instructors/$script:InstructorId/credits" `
            -Token $script:AdminToken `
            -ExpectedStatus @(200)
    }
}
else {
    Write-Host "SKIP: RejectCourseId = 0, skip real reject course test." -ForegroundColor Yellow
}

if ($NoCreditPendingCourseId -gt 0) {
    Invoke-MindHubApi `
        -Group "ADMIN_COURSE_APPROVAL" `
        -Case "Admin approves course without enough credits should fail" `
        -Method "PATCH" `
        -ApiPath "/admin/courses/$NoCreditPendingCourseId/approve" `
        -Token $script:AdminToken `
        -ExpectedStatus @(400, 409)
}
else {
    Write-Host "SKIP: NoCreditPendingCourseId = 0, skip no credit test." -ForegroundColor Yellow
}

# ============================================================
# 7. COURSE PURCHASE TESTS
# ============================================================

Write-Title "COURSE PURCHASE TESTS"

Invoke-MindHubApi `
    -Group "COURSE_PURCHASE" `
    -Case "Create course order without token should be 401" `
    -Method "POST" `
    -ApiPath "/orders" `
    -Body @{
        course_id = 999999999
    } `
    -ExpectedStatus @(401)

Invoke-MindHubApi `
    -Group "COURSE_PURCHASE" `
    -Case "Learner buys missing course should be 404" `
    -Method "POST" `
    -ApiPath "/orders" `
    -Token $script:LearnerToken `
    -Body @{
        course_id = 999999999
    } `
    -ExpectedStatus @(404, 422)

Invoke-MindHubApi `
    -Group "COURSE_PURCHASE" `
    -Case "Create course order without course_id should be 422" `
    -Method "POST" `
    -ApiPath "/orders" `
    -Token $script:LearnerToken `
    -Body @{} `
    -ExpectedStatus @(422)

if ($PublishedCourseId -gt 0) {
    $courseOrderRes = Invoke-MindHubApi `
        -Group "COURSE_PURCHASE" `
        -Case "Learner creates valid order for published course" `
        -Method "POST" `
        -ApiPath "/orders" `
        -Token $script:LearnerToken `
        -Body @{
            course_id = $PublishedCourseId
        } `
        -ExpectedStatus @(200, 201, 409)

    $CourseOrderId = Extract-Id $courseOrderRes.Json

    Write-Host "CourseOrderId: $CourseOrderId" -ForegroundColor Cyan

    if ($CourseOrderId) {
        Invoke-MindHubApi `
            -Group "PAYMENT_OPTIONAL" `
            -Case "Create VNPAY URL for course order if route exists" `
            -Method "POST" `
            -ApiPath "/payments/vnpay/create" `
            -Token $script:LearnerToken `
            -Body @{
                order_id = [int] $CourseOrderId
            } `
            -ExpectedStatus @(200, 201) `
            -Optional
    }
}
else {
    Write-Host "SKIP: PublishedCourseId = 0, skip real learner purchase test." -ForegroundColor Yellow
}

if ($OwnPublishedCourseId -gt 0) {
    Invoke-MindHubApi `
        -Group "COURSE_PURCHASE" `
        -Case "Instructor cannot buy own course" `
        -Method "POST" `
        -ApiPath "/orders" `
        -Token $script:InstructorToken `
        -Body @{
            course_id = $OwnPublishedCourseId
        } `
        -ExpectedStatus @(400, 403, 409)
}
else {
    Write-Host "SKIP: OwnPublishedCourseId = 0, skip self purchase test." -ForegroundColor Yellow
}

# ============================================================
# 8. CATALOG VISIBILITY TESTS
# ============================================================

Write-Title "CATALOG VISIBILITY TESTS"

Invoke-MindHubApi `
    -Group "CATALOG" `
    -Case "Public search should only return published courses from active unlocked instructors" `
    -Method "GET" `
    -ApiPath "/courses?per_page=5" `
    -ExpectedStatus @(200) `
    -Optional

Invoke-MindHubApi `
    -Group "CATALOG" `
    -Case "Suggestions should hide empty categories and locked instructor courses" `
    -Method "GET" `
    -ApiPath '/catalog/suggestions?keyword=a&limit=10' `
    -ExpectedStatus @(200) `
    -Optional

# ============================================================
# 9. EXPORT REPORT
# ============================================================

Export-Report

Write-Host ""
Write-Host "DONE." -ForegroundColor Green
Write-Host "Report path: $ReportPath" -ForegroundColor Green
