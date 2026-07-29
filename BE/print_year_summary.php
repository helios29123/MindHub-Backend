<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InstructorWithdrawalController;

$user = User::where('email', 'instructor1@mindhub.test')->first();
auth()->login($user);

$reportCtrl = app(ReportController::class);
$withCtrl = app(InstructorWithdrawalController::class);

$reqYear = Request::create('/api/instructor/revenues/summary', 'GET', ['preset' => 'year']);
$reqYear->setUserResolver(fn() => $user);

echo "=== GET /api/instructor/revenues/summary (preset=year) ===\n";
echo json_encode(json_decode($reportCtrl->revenueSummary($reqYear)->getContent()), JSON_PRETTY_PRINT) . "\n";

echo "=== GET /api/instructor/withdrawals/summary ===\n";
$reqW = Request::create('/api/instructor/withdrawals/summary', 'GET');
$reqW->setUserResolver(fn() => $user);
echo json_encode(json_decode($withCtrl->summary($reqW)->getContent()), JSON_PRETTY_PRINT) . "\n";
