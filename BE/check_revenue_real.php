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

echo "==================================================\n";
echo "TESTING API RESPONSES FOR YEAR 2026 (OR NO DATE RANGE):\n";
echo "==================================================\n";

$presets = [
    'month' => ['preset' => 'month'],
    'year' => ['preset' => 'year'],
    'custom_june' => ['preset' => 'custom', 'date_from' => '2026-06-01', 'date_to' => '2026-06-30'],
    'all_2026' => ['preset' => 'custom', 'date_from' => '2026-01-01', 'date_to' => '2026-12-31'],
];

foreach ($presets as $name => $params) {
    echo "\n*** PRESET / PARAMS: {$name} ***\n";
    $req = Request::create('/api/instructor/revenues/summary', 'GET', $params);
    $req->setUserResolver(fn() => $user);

    echo "--- GET /api/instructor/revenues/summary ---\n";
    $res = $reportCtrl->revenueSummary($req);
    echo $res->getContent() . "\n";

    echo "--- GET /api/instructor/revenues/top-courses ---\n";
    $resTop = $reportCtrl->topCoursesByRevenue($req);
    echo $resTop->getContent() . "\n";

    echo "--- GET /api/instructor/revenues/course-breakdown ---\n";
    $resBreak = $reportCtrl->courseBreakdown($req);
    echo $resBreak->getContent() . "\n";

    echo "--- GET /api/instructor/revenues/details ---\n";
    $resDet = $reportCtrl->revenueDetails($req);
    echo $resDet->getContent() . "\n";
}

echo "\n--- GET /api/instructor/withdrawals/summary ---\n";
$reqW = Request::create('/api/instructor/withdrawals/summary', 'GET');
$reqW->setUserResolver(fn() => $user);
$resW = $withCtrl->summary($reqW);
echo $resW->getContent() . "\n";
