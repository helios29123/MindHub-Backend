<?php
namespace App\Repositories\Moderation;
use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
class CourseModerationRepository
{
    public function paginatePendingCourses(array $filters): LengthAwarePaginator
    {
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 10);
        $search = trim((string) ($filters['search'] ?? ''));
        $sort = (string) ($filters['sort'] ?? 'newest');
        $query = Course::query()
            ->with([
                'instructor' => function ($query): void {
                    $query->select([
                        'id',
                        'full_name',
                        'email',
                        'status',
                    ]);
                },
                'categories'
            ])
            ->where('status', 'pending_review');
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhereHas('instructor', function (Builder $instructorQuery) use ($search): void {
                        $instructorQuery->where('full_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }
        if (!empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];
            $query->whereHas('categories', function (Builder $q) use ($categoryId): void {
                $q->where('categories.id', $categoryId);
            });
        }
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }
        match ($sort) {
            'oldest' => $query->orderBy('id'),
            'title_asc' => $query->orderBy('title'),
            'title_desc' => $query->orderByDesc('title'),
            default => $query->orderByDesc('id'),
        };
        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}