<?php

namespace Tests\Feature\Final;

use App\Models\Notification;
use App\Models\User;
use App\Services\Instructor\InstructorUpgradeService;
use Tests\TestCase;

class InstructorUpgradeFlowTest extends TestCase
{
    public function test_full_instructor_upgrade_flow(): void
    {
        $this->seed(\Database\Seeders\InstructorUpgradeTestSeeder::class);
        $service = app(InstructorUpgradeService::class);

        // 1. Admin Index Report
        $report = $service->adminIndexReport([]);
        $this->assertIsArray($report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertEquals(4, $report['summary']['total']);
        $this->assertEquals(2, $report['summary']['pending']);
        $this->assertEquals(1, $report['summary']['approved']);
        $this->assertEquals(1, $report['summary']['rejected']);

        // Check dangdominh303 item properties
        $user1Item = collect($report['items'])->firstWhere('user.email', 'dangdominh303@gmail.com');
        $this->assertNotNull($user1Item);
        $this->assertEquals('pending', $user1Item['application_status']);
        $this->assertTrue($user1Item['is_resubmission']);
        $this->assertEquals('diamond', $user1Item['instructor_profile']['level']);
        $this->assertEquals('Techcombank', $user1Item['payout_account']['provider']);
        $this->assertNotNull($user1Item['user']['avatar_url']);

        // 2. Reject flow with note
        $targetUser = User::where('email', 'lehoangnam.dev@gmail.com')->first();
        $this->assertNotNull($targetUser);

        $rejectRes = $service->reject((int) $targetUser->id, 'Hồ sơ thiếu chứng chỉ chuyên môn cần thiết.');
        $this->assertEquals('rejected', $rejectRes['application_status']);
        $this->assertEquals('learner', $targetUser->fresh()->role);

        // 3. Resubmission flow (store calls update when disabled)
        $resubmitData = [
            'bio' => 'Lập trình viên Flutter 4 năm kinh nghiệm đã bổ sung chứng chỉ Google Certified Associate.',
            'expertise' => 'Lập trình Di động (Flutter, React Native)',
            'experience_years' => 4,
            'level' => 'gold',
            'bank_provider' => 'VPBank',
            'bank_account_number' => '999888777123',
            'bank_account_name' => 'LE HOANG NAM',
        ];

        $reapplyRes = $service->store($targetUser, $resubmitData);
        $this->assertEquals('pending', $reapplyRes['application_status']);
        $this->assertEquals('gold', $reapplyRes['instructor_profile']['level']);

        // 4. Approve flow
        $approveRes = $service->approve((int) $targetUser->id);
        $this->assertEquals('approved', $approveRes['application_status']);
        $this->assertEquals('instructor', $targetUser->fresh()->role);
    }
}
