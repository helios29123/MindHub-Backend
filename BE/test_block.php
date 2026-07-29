<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\User;
use App\Services\Admin\AdminUserService;

// Bootstrap HTTP kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    $admin = User::where('role', 'admin')->first();
    $target = User::where('role', 'learner')->first();
    if (!$admin || !$target) {
        echo "Admin or target learner not found.\n";
        exit;
    }
    echo "Admin: " . $admin->email . " | ID: " . $admin->id . "\n";
    echo "Target: " . $target->email . " | ID: " . $target->id . " | Status: " . $target->status . "\n";
    
    $userService = $app->make(AdminUserService::class);
    $updated = $userService->block($target, $admin);
    echo "Blocked successfully! New status: " . $updated->status . "\n";
    
    $updated = $userService->unblock($target, $admin);
    echo "Unblocked successfully! New status: " . $updated->status . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
