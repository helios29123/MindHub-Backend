<?php
namespace App\Services\Report;
use App\Repositories\Report\InstructorReportRepository;
use Illuminate\Support\Facades\DB;
class InstructorReportService
{
    public function __construct(
        private readonly InstructorReportRepository $repository
    ) {}
    public function getCompletionRate(array $filters, $user)
    {
        if (!in_array($user->role, ['admin', 'instructor'])) {
            abort(403, $user->role === 'learner' ? 'Bạn không có quyền thực hiện thao tác này.' : 'Bạn không có quyền giảng viên.');
        }
        if (isset($filters['course_id']) && $user->role === 'instructor') {
            $isOwner = DB::table('courses')
                ->where('id', $filters['course_id'])
                ->where('instructor_id', $user->id)
                ->exists();
            if (!$isOwner) {
                abort(403, 'Bạn không có quyền xem dữ liệu khóa học này.');
            }
        }
        return $this->repository->getCompletionRates($filters, $user);
    }
}