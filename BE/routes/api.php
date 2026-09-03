<?php
require __DIR__ . '/api/auth.php';

require __DIR__ . '/api/user.php';

// Sau này làm module nào thì mở thêm dòng tương ứng
require __DIR__ . '/api/catalog.php';
require __DIR__ . '/api/course.php';
require __DIR__ . '/api/instructor.php';
require __DIR__ . '/api/interaction.php';
require __DIR__ . '/api/admin.php';
require __DIR__ . '/api/marketing.php';
require __DIR__ . '/api/wishlist.php';
require __DIR__ . '/api/payment.php';
require __DIR__ . '/api/learning.php';

require __DIR__ . '/api/report.php';

use App\Http\Controllers\Webhook\BunnyWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/bunny/video-encoded', [BunnyWebhookController::class, 'handle']);
