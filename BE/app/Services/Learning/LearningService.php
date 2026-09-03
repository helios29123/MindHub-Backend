<?php

namespace App\Services\Learning;

use App\Models\Enrollment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LearningService
{
    /**
     * Get the paginated list of purchased courses for a user.
     *
     * @param User $user
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getPurchasedCourses(User $user, array $params): LengthAwarePaginator
    {
        $perPage = min((int) ($params['per_page'] ?? 10), 100);

        $query = Enrollment::with(['course.instructor.instructorProfile'])
            ->where('user_id', $user->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->whereHas('course', function ($q) {
                $q;
            });

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get details of a lesson for the enrolled user and record progress.
     *
     * @param User $user
     * @param int $lessonId
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function getLessonDetails(User $user, int $lessonId): array
    {
        if (! $user->isActive()) throw new \App\Exceptions\BusinessException('Tài khoản hiện không thể học.',403);
        $lesson=\App\Models\Lesson::with(['assets','course','section'])->find($lessonId);
        if (! $lesson) throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.',404);
        if ($lesson->status!=='published' || ! $lesson->course || $lesson->course->status!=='published' || ! $lesson->section || $lesson->section->status!=='published')
            throw new \App\Exceptions\BusinessException('Nội dung chưa khả dụng.',403);
        $enrollment=Enrollment::query()->where('user_id',$user->id)->where('course_id',$lesson->course_id)
            ->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first();
        if (! $enrollment) throw new \App\Exceptions\BusinessException('Bạn chưa có quyền học hoặc quyền học đã hết hạn.',403);
        $progress=\App\Models\LessonProgress::firstOrCreate(
            ['enrollment_id'=>$enrollment->id,'lesson_id'=>$lessonId],
            ['status'=>'in_progress','started_at'=>now(),'last_accessed_at'=>now(),'learning_duration_seconds'=>0]
        );
        if (! $progress->wasRecentlyCreated) {
            $u=['last_accessed_at'=>now()];
            if ($progress->status==='not_started') { $u['status']='in_progress'; $u['started_at']=$progress->started_at??now(); }
            $progress->update($u);
        }
        $enrollment->update(['last_accessed_at'=>now()]);
        $vp=$lesson->lesson_type==='video' ? \App\Models\VideoProgress::query()->where('enrollment_id',$enrollment->id)->where('lesson_id',$lessonId)->first() : null;
        return ['course'=>$lesson->course,'lesson'=>$lesson,'progress'=>$progress,'current_second'=>(int)($vp?->current_second??0)];
    }

    /**
     * Get the outline (sections & lessons) of a purchased course along with the user's progress.
     *
     * @param User $user
     * @param int $courseId
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function getCourseOutline(User $user, int $courseId): array
    {
        if (! $user->isActive()) throw new \App\Exceptions\BusinessException('Tài khoản hiện không thể học.',403);
        $course=\App\Models\Course::query()->whereKey($courseId)->where('status','published')->first();
        if (! $course) throw new \App\Exceptions\BusinessException('Không tìm thấy khóa học.',404);
        $enrollment=Enrollment::query()->where('user_id',$user->id)->where('course_id',$courseId)
            ->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first();
        if (! $enrollment) throw new \App\Exceptions\BusinessException('Bạn chưa có quyền học hoặc quyền học đã hết hạn.',403);
        $course->load([
            'sections'=>fn($q)=>$q->where('status','published')->orderBy('sort_order')->orderBy('id'),
            'sections.lessons'=>fn($q)=>$q->where('status','published')->orderBy('sort_order')->orderBy('id'),
        ]);
        $ids=$course->sections->flatMap->lessons->pluck('id');
        $progresses=\App\Models\LessonProgress::query()->where('enrollment_id',$enrollment->id)->whereIn('lesson_id',$ids)->get()->keyBy('lesson_id');
        return ['sections'=>$course->sections,'progresses'=>$progresses];
    }

    /**
     * Save the learner's progress for a video lesson.
     *
     * @param User $user
     * @param int $lessonId
     * @param array $data
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function saveVideoProgress(User $user, int $lessonId, array $data): array
    {
        if (! $user->isActive()) throw new \App\Exceptions\BusinessException('Tài khoản hiện không thể học.',403);
        $lesson=\App\Models\Lesson::with(['course','section'])->find($lessonId);
        if (! $lesson) throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.',404);
        if ($lesson->lesson_type!=='video') throw new \App\Exceptions\BusinessException('Bài học không phải video.',422);
        if ($lesson->status!=='published' || ! $lesson->course || $lesson->course->status!=='published' || ! $lesson->section || $lesson->section->status!=='published')
            throw new \App\Exceptions\BusinessException('Nội dung chưa khả dụng.',403);
        $enrollment=Enrollment::query()->where('user_id',$user->id)->where('course_id',$lesson->course_id)
            ->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first();
        if (! $enrollment) throw new \App\Exceptions\BusinessException('Bạn chưa có quyền học hoặc quyền học đã hết hạn.',403);
        $sec=max(0,(int)$data['current_second']);
        if ($lesson->video_duration_seconds>0 && $sec>(int)$lesson->video_duration_seconds) throw new \App\Exceptions\BusinessException('Tiến độ video không hợp lệ.',422);
        $userTz = $data['timezone'] ?? $user->timezone ?? 'Asia/Ho_Chi_Minh';
        if (!empty($data['timezone']) && $user->timezone !== $data['timezone']) {
            $user->update(['timezone' => $data['timezone']]);
        }

        $nowInUserTz = now()->setTimezone($userTz);
        $activityDate = $nowInUserTz->format('Y-m-d');

        if (!empty($data['force_date'])) {
            try {
                $forceCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $data['force_date'], $userTz)->startOfDay();
                $userTodayCarbon = $nowInUserTz->copy()->startOfDay();
                $diffDays = $userTodayCarbon->diffInDays($forceCarbon, false);

                // Chỉ chấp nhận: hôm nay (0) hoặc hôm qua (-1) và trong vòng 24h
                if ($diffDays >= -1 && $diffDays <= 0) {
                    $forceEnd = $forceCarbon->copy()->endOfDay();
                    if ($nowInUserTz->diffInHours($forceEnd, false) <= 24) {
                        $activityDate = $forceCarbon->format('Y-m-d');
                    }
                }
            } catch (\Throwable $e) {
                // Giữ nguyên $activityDate theo $userToday
            }
        }

        $vp=\App\Models\VideoProgress::firstOrCreate(['enrollment_id'=>$enrollment->id,'lesson_id'=>$lessonId],['current_second'=>0]);
        $oldSec = (int)$vp->current_second;
        $newSec = max($oldSec, $sec);
        $vp->update(['current_second' => $newSec]);
        if ($sec > $oldSec) {
            $diff = $sec - $oldSec;
            if ($diff > 0 && $diff <= 30) {
                DB::table('learning_daily_activity')->upsert(
                    [
                        'enrollment_id' => $enrollment->id,
                        'activity_date' => $activityDate,
                        'video_learning_seconds' => $diff,
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                    ['enrollment_id', 'activity_date'],
                    ['video_learning_seconds' => DB::raw('learning_daily_activity.video_learning_seconds + ' . $diff), 'updated_at' => now()]
                );
                
                $progress=\App\Models\LessonProgress::firstOrCreate(['enrollment_id'=>$enrollment->id,'lesson_id'=>$lessonId],['status'=>'in_progress','started_at'=>now(),'last_accessed_at'=>now(),'learning_duration_seconds'=>0]);
                $progress->increment('learning_duration_seconds', $diff);
            }
        }
        $progress=\App\Models\LessonProgress::firstOrCreate(['enrollment_id'=>$enrollment->id,'lesson_id'=>$lessonId],['status'=>'in_progress','started_at'=>now(),'last_accessed_at'=>now(),'learning_duration_seconds'=>0]);
        if ($progress->status==='not_started') $progress->update(['status'=>'in_progress','started_at'=>$progress->started_at??now()]);
        $progress->update(['last_accessed_at'=>now()]); $enrollment->update(['last_accessed_at'=>now()]);
        return ['course'=>$lesson->course,'lesson'=>$lesson,'progress'=>$progress->fresh(),'current_second'=>(int)$vp->fresh()->current_second];
    }

    /**
     * Resume learning the most recently accessed lesson or the first lesson of the latest course.
     *
     * @param User $user
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function resumeLearning(User $user): array
    {
        if (! $user->isActive()) throw new \App\Exceptions\BusinessException('Tài khoản hiện không thể học.',403);
        $ens=Enrollment::query()->where('user_id',$user->id)->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->whereHas('course',fn($q)=>$q->where('status','published'))
            ->orderByDesc('last_accessed_at')->orderByDesc('enrolled_at')->orderByDesc('id')->get();
        if($ens->isEmpty()) throw new \App\Exceptions\BusinessException('Bạn chưa có khóa học còn quyền truy cập.',403);
        foreach($ens as $e){
            $p=\App\Models\LessonProgress::query()->where('enrollment_id',$e->id)->where('status','in_progress')
                ->whereHas('lesson',fn($q)=>$q->where('course_id',$e->course_id)->where('status','published')->whereHas('section',fn($s)=>$s->where('status','published')))
                ->with('lesson.course')->orderByDesc('updated_at')->first();
            if($p){$vp=\App\Models\VideoProgress::query()->where('enrollment_id',$e->id)->where('lesson_id',$p->lesson_id)->first();return ['course'=>$p->lesson->course,'lesson'=>$p->lesson,'progress'=>$p,'current_second'=>(int)($vp?->current_second??0)];}
            $done=\App\Models\LessonProgress::query()->where('enrollment_id',$e->id)->where('status','completed')->pluck('lesson_id');
            $base=\App\Models\Lesson::query()->where('lessons.course_id',$e->course_id)->where('lessons.status','published')->whereHas('section',fn($q)=>$q->where('status','published'))
                ->join('course_sections','lessons.course_section_id','=','course_sections.id')->orderBy('course_sections.sort_order')->orderBy('course_sections.id')->orderBy('lessons.sort_order')->orderBy('lessons.id')->select('lessons.*');
            $next=(clone $base)->whereNotIn('lessons.id',$done)->first() ?? (clone $base)->first();
            if($next){$p=\App\Models\LessonProgress::query()->where('enrollment_id',$e->id)->where('lesson_id',$next->id)->first();$vp=\App\Models\VideoProgress::query()->where('enrollment_id',$e->id)->where('lesson_id',$next->id)->first();return ['course'=>$next->course,'lesson'=>$next,'progress'=>$p,'current_second'=>(int)($vp?->current_second??0)];}
        }
        throw new \App\Exceptions\BusinessException('Khóa học hiện chưa có bài học khả dụng.',404);
    }

    /**
     * Mark a lesson as completed or in_progress and update course enrollment status accordingly.
     *
     * @param User $user
     * @param int $lessonId
     * @param array $data
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function completeLesson(User $user, int $lessonId, array $data): array
    {
        if (! $user->isActive()) throw new \App\Exceptions\BusinessException('Tài khoản hiện không thể học.',403);
        $lesson=\App\Models\Lesson::with(['course','section'])->find($lessonId);
        if (! $lesson) throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.',404);
        if ($lesson->status!=='published' || ! $lesson->course || $lesson->course->status!=='published' || ! $lesson->section || $lesson->section->status!=='published')
            throw new \App\Exceptions\BusinessException('Nội dung chưa khả dụng.',403);
        $enrollment=Enrollment::query()->where('user_id',$user->id)->where('course_id',$lesson->course_id)
            ->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first();
        if (! $enrollment) throw new \App\Exceptions\BusinessException('Bạn chưa có quyền học hoặc quyền học đã hết hạn.',403);
        return DB::transaction(function() use($lesson,$enrollment,$data){
            $e=Enrollment::query()->whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $p=\App\Models\LessonProgress::query()->where('enrollment_id',$e->id)->where('lesson_id',$lesson->id)->lockForUpdate()->first();
            if(!$p) $p=\App\Models\LessonProgress::create(['enrollment_id'=>$e->id,'lesson_id'=>$lesson->id,'status'=>'in_progress','started_at'=>now(),'last_accessed_at'=>now(),'learning_duration_seconds'=>0]);
            $completed=(bool)($data['completed']??true);
            if($completed && $p->status!=='completed'){
                if((!$p->started_at || $p->started_at->diffInSeconds(now())<5))
                    throw new \App\Exceptions\BusinessException('Bạn cần mở nội dung ít nhất 5 giây trước khi hoàn thành.',422);

                if($lesson->lesson_type === 'video') {
                    $vp = \App\Models\VideoProgress::query()->where('enrollment_id', $e->id)->where('lesson_id', $lesson->id)->first();
                    $duration = (int) $lesson->video_duration_seconds;
                    if ($vp && $duration > 0 && (int)$vp->current_second < $duration * 0.9) {
                        throw new \App\Exceptions\BusinessException('Bạn cần xem ít nhất 90% thời lượng video trước khi hoàn thành.', 422);
                    }
                }

                $p->update(['status'=>'completed','started_at'=>$p->started_at??now(),'completed_at'=>now(),'last_accessed_at'=>now()]);
            } elseif(!$completed && $p->status!=='completed') {
                $p->update(['status'=>'in_progress','started_at'=>$p->started_at??now(),'last_accessed_at'=>now()]);
            }
            $ids=\App\Models\Lesson::query()->where('course_id',$lesson->course_id)->where('status','published')->whereHas('section',fn($q)=>$q->where('status','published'))->pluck('id');
            $total=$ids->count(); $done=\App\Models\LessonProgress::query()->where('enrollment_id',$e->id)->whereIn('lesson_id',$ids)->where('status','completed')->count();
            $percent=$total>0?round(($done/$total)*100,2):0.00; $u=['progress_percent'=>$percent,'last_accessed_at'=>now()];
            if($total>0 && $done===$total && $e->status!==Enrollment::STATUS_COMPLETED){$u['status']=Enrollment::STATUS_COMPLETED;$u['completed_at']=now();}
            $e->update($u);
            $vp=\App\Models\VideoProgress::query()->where('enrollment_id',$e->id)->where('lesson_id',$lesson->id)->first();
            return ['course'=>$lesson->course,'lesson'=>$lesson,'progress'=>$p->fresh(),'current_second'=>(int)($vp?->current_second??0)];
        });
    }

    /**
     * Get the progress details (total, completed, percent) of a course for a user.
     *
     * @param User $user
     * @param int $courseId
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function getCourseProgress(User $user, int $courseId): array
    {
        if (! $user->isActive()) throw new \App\Exceptions\BusinessException('Tài khoản hiện không thể học.',403);
        $course=\App\Models\Course::query()->whereKey($courseId)->where('status','published')->first();
        if (! $course) throw new \App\Exceptions\BusinessException('Không tìm thấy khóa học.',404);
        $enrollment=Enrollment::query()->where('user_id',$user->id)->where('course_id',$courseId)
            ->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first();
        if (! $enrollment) throw new \App\Exceptions\BusinessException('Bạn chưa có quyền học hoặc quyền học đã hết hạn.',403);
        $ids=\App\Models\Lesson::query()->where('course_id',$courseId)->where('status','published')
            ->whereHas('section',fn($q)=>$q->where('status','published'))->pluck('id');
        $total=$ids->count();
        $done=\App\Models\LessonProgress::query()->where('enrollment_id',$enrollment->id)->whereIn('lesson_id',$ids)->where('status','completed')->count();
        $percent=$total>0?round(($done/$total)*100,2):0.00;
        $u=['progress_percent'=>$percent];
        if ($total>0 && $done===$total && $enrollment->status!==Enrollment::STATUS_COMPLETED) { $u['status']=Enrollment::STATUS_COMPLETED; $u['completed_at']=now(); }
        $enrollment->update($u); $fresh=$enrollment->fresh();
        return ['course_id'=>$courseId,'total_lessons'=>$total,'completed_lessons'=>$done,'progress_percent'=>(float)$percent,
            'course_completed'=>$fresh->status===Enrollment::STATUS_COMPLETED,'completed_at'=>$fresh->completed_at,
            'has_new_content'=>$fresh->status===Enrollment::STATUS_COMPLETED && $percent<100];
    }

    /**
     * Get the paginated learning logs (timeline) for the authenticated learner.
     *
     * @param User $user
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getLearningLogs(User $user, array $params): LengthAwarePaginator
    {
        $enrollmentIds = \App\Models\Enrollment::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [\App\Models\Enrollment::STATUS_ACTIVE, \App\Models\Enrollment::STATUS_COMPLETED])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->pluck('id');

        $perPage = min((int) ($params['per_page'] ?? 10), 100);

        // Get only course IDs where the user has active or completed enrollments
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->pluck('course_id');

        $query = \App\Models\LessonProgress::with(['lesson.course'])
            ->whereIn('enrollment_id', $enrollmentIds)
            ->whereHas('lesson', function ($q) use ($enrolledCourseIds) {
                $q->where('status', 'published')
                  ->whereIn('course_id', $enrolledCourseIds)
                  ->whereHas('course', function ($qc) {
                      $qc->where('status', 'published')
                         ;
                  });
            });

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $paginatedLogs = $query->orderByDesc('last_accessed_at')
            ->paginate($perPage);

        // Map video progress current_second for video lessons
        $lessonIds = $paginatedLogs->pluck('lesson_id')->unique();
        $videoProgresses = \App\Models\VideoProgress::whereIn('enrollment_id', $enrollmentIds)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy(fn ($vp) => $vp->enrollment_id . '_' . $vp->lesson_id);

        $paginatedLogs->getCollection()->transform(function ($progress) use ($videoProgresses) {
            $key = $progress->enrollment_id . '_' . $progress->lesson_id;
            $vp = $videoProgresses->get($key);
            $progress->current_second = $vp ? (int) $vp->current_second : 0;
            return $progress;
        });

        return $paginatedLogs;
    }

    /**
     * Get details of a lesson asset for download.
     *
     * @param User $user
     * @param int $assetId
     * @return \App\Models\LessonAsset
     * @throws \App\Exceptions\BusinessException
     */
    public function downloadAsset(User $user, int $assetId): \App\Models\LessonAsset
    {
        $asset = \App\Models\LessonAsset::find($assetId);

        if (!$asset) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $lesson = $asset->lesson;
        if (!$lesson) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $course = $lesson->course;
        if (!$course) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        if ($lesson->status !== 'published' || $course->status !== 'published') {
            throw new \App\Exceptions\BusinessException('Nội dung chưa khả dụng.', 403);
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->first();

        if (!$enrollment) {
            throw new \App\Exceptions\BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        return $asset;
    }

    /**
     * Suggest the next lesson in the course structure.
     *
     * @param User $user
     * @param int $lessonId
     * @return \App\Models\Lesson|null
     * @throws \App\Exceptions\BusinessException
     */
    public function nextLesson(User $user, int $lessonId): ?\App\Models\Lesson
    {
        if (! $user->isActive()) throw new \App\Exceptions\BusinessException('Tài khoản hiện không thể học.',403);
        $lesson=\App\Models\Lesson::with(['course','section'])->find($lessonId); if(!$lesson) throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.',404);
        $e=Enrollment::query()->where('user_id',$user->id)->where('course_id',$lesson->course_id)->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first();
        if(!$e) throw new \App\Exceptions\BusinessException('Bạn chưa có quyền học hoặc quyền học đã hết hạn.',403);
        $p=\App\Models\LessonProgress::query()->where('enrollment_id',$e->id)->where('lesson_id',$lessonId)->first();
        if(!$p || $p->status!=='completed') throw new \App\Exceptions\BusinessException('Bạn cần hoàn thành bài hiện tại trước khi dùng nút Tiếp theo.',422);
        $ls=\App\Models\Lesson::query()->where('lessons.course_id',$lesson->course_id)->where('lessons.status','published')->whereHas('section',fn($q)=>$q->where('status','published'))
            ->join('course_sections','lessons.course_section_id','=','course_sections.id')->orderBy('course_sections.sort_order')->orderBy('course_sections.id')->orderBy('lessons.sort_order')->orderBy('lessons.id')->select('lessons.*')->get();
        $i=$ls->search(fn($x)=>(int)$x->id===(int)$lessonId); return $i!==false && $i<$ls->count()-1 ? $ls[$i+1] : null;
    }
    public function getLessonNotes(int $lessonId, User $user)
    {
        $lesson=\App\Models\Lesson::find($lessonId); if(!$lesson) throw new \App\Exceptions\BusinessException('Không tìm thấy bài học.',404);
        $e=Enrollment::query()->where('user_id',$user->id)->where('course_id',$lesson->course_id)->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first();
        if(!$user->isActive() || !$e) throw new \App\Exceptions\BusinessException('Bạn không có quyền truy cập ghi chú.',403);
        return \App\Models\LessonNote::query()->where('enrollment_id',$e->id)->where('lesson_id',$lessonId)->orderBy('id')->get();
    }
    public function createLessonNote(int $lessonId, array $data, User $user): \App\Models\LessonNote
    {
        $lesson=\App\Models\Lesson::find($lessonId); if(!$lesson) throw new \App\Exceptions\BusinessException('Không tìm thấy bài học.',404);
        $e=Enrollment::query()->where('user_id',$user->id)->where('course_id',$lesson->course_id)->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first();
        if(!$user->isActive() || !$e) throw new \App\Exceptions\BusinessException('Bạn không có quyền tạo ghi chú.',403);
        $time=$lesson->lesson_type==='video'?($data['note_time_second']??null):null;
        if($time!==null && $lesson->video_duration_seconds>0 && (int)$time>(int)$lesson->video_duration_seconds) throw new \App\Exceptions\BusinessException('Thời điểm ghi chú vượt quá video.',422);
        return \App\Models\LessonNote::create(['enrollment_id'=>$e->id,'lesson_id'=>$lessonId,'content'=>$data['content'],'note_time_second'=>$time]);
    }
    public function updateLessonNote(int $noteId, array $data, User $user): \App\Models\LessonNote
    {
        $n=\App\Models\LessonNote::query()->whereKey($noteId)->whereHas('enrollment',fn($q)=>$q->where('user_id',$user->id)->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])->where(fn($x)=>$x->whereNull('expires_at')->orWhere('expires_at','>',now())))->with('lesson')->first();
        if(!$user->isActive() || !$n) throw new \App\Exceptions\BusinessException('Không tìm thấy ghi chú hoặc không có quyền sửa.',404);
        $u=['content'=>$data['content']??$n->content]; if($n->lesson?->lesson_type==='video' && array_key_exists('note_time_second',$data))$u['note_time_second']=$data['note_time_second']; elseif($n->lesson?->lesson_type!=='video')$u['note_time_second']=null;
        $n->update($u); return $n->fresh();
    }
    public function deleteLessonNote(int $noteId, User $user): bool
    {
        $n=\App\Models\LessonNote::query()->whereKey($noteId)->whereHas('enrollment',fn($q)=>$q->where('user_id',$user->id)->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])->where(fn($x)=>$x->whereNull('expires_at')->orWhere('expires_at','>',now())))->first();
        if(!$user->isActive() || !$n) throw new \App\Exceptions\BusinessException('Không tìm thấy ghi chú hoặc không có quyền xóa.',404); return (bool)$n->delete();
    }

    public function getLearningStreak(?User $user = null): array
    {
        $userTz = request()->query('timezone') ?? $user?->timezone ?? 'Asia/Ho_Chi_Minh';
        $today = now()->setTimezone($userTz)->format('Y-m-d');
        if (! $user) {
            $startOfWeek = now()->setTimezone($userTz)->startOfWeek(\Carbon\Carbon::MONDAY);
            $weekDays = [];
            $dayLabels = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
            for ($i = 0; $i < 7; $i++) {
                $dStr = (clone $startOfWeek)->addDays($i)->format('Y-m-d');
                $weekDays[] = [
                    'day' => $dayLabels[$i],
                    'date' => $dStr,
                    'active' => false,
                    'isToday' => ($dStr === $today),
                    'is_today' => ($dStr === $today),
                    'learning_seconds' => 0,
                    'learning_time' => '0 phút',
                ];
            }
            return [
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_active_days' => 0,
                'is_maintaining' => false,
                'status_label' => 'Chưa bắt đầu',
                'completed_days_in_week' => 0,
                'total_days_in_week' => 7,
                'week_days' => $weekDays,
                'encouragement' => [
                    'days_needed' => 7,
                    'next_milestone' => 7,
                    'badge_name' => 'Chiến binh Chăm chỉ',
                    'message' => 'Hãy bắt đầu bài học đầu tiên hôm nay để thiết lập chuỗi học tập!',
                ],
            ];
        }

        $progressDates = [];
        $dailyLearningSecondsMap = [];

        if (Schema::hasTable('lesson_progress') && Schema::hasTable('lessons') && Schema::hasTable('enrollments')) {
            $rawRecords = DB::table('lesson_progress as lp')
                ->join('lessons as l', 'l.id', '=', 'lp.lesson_id')
                ->join('enrollments as e', 'e.id', '=', 'lp.enrollment_id')
                ->where('e.user_id', $user->id)
                ->where('l.lesson_type', 'video')
                ->where('lp.status', 'completed')
                ->select('lp.completed_at', 'lp.last_accessed_at', 'lp.updated_at')
                ->get();

            foreach ($rawRecords as $rec) {
                $rawTime = $rec->completed_at ?? $rec->last_accessed_at ?? $rec->updated_at;
                if ($rawTime) {
                    $localDate = Carbon::parse($rawTime)->setTimezone($userTz)->format('Y-m-d');
                    $progressDates[] = $localDate;
                }
            }
        }

        if (Schema::hasTable('learning_daily_activity') && Schema::hasTable('enrollments')) {
            $activities = DB::table('learning_daily_activity as lda')
                ->join('enrollments as e', 'e.id', '=', 'lda.enrollment_id')
                ->where('e.user_id', $user->id)
                ->select('lda.activity_date', 'lda.video_learning_seconds')
                ->get();

            foreach ($activities as $act) {
                $d = Carbon::parse($act->activity_date)->format('Y-m-d');
                $sec = (int) ($act->video_learning_seconds ?? 0);
                $dailyLearningSecondsMap[$d] = ($dailyLearningSecondsMap[$d] ?? 0) + $sec;
            }
        }

        $allDatesSet = array_unique(array_filter($progressDates));
        rsort($allDatesSet);
        $yesterday = now()->setTimezone($userTz)->subDay()->format('Y-m-d');

        $currentStreak = 0;
        $checkDate = now()->setTimezone($userTz);
        $isTodayActive = in_array($today, $allDatesSet, true);
        $isYesterdayActive = in_array($yesterday, $allDatesSet, true);

        $isMaintaining = $isTodayActive || $isYesterdayActive;

        if (!$isTodayActive && $isYesterdayActive) {
            $checkDate = now()->setTimezone($userTz)->subDay();
        }

        if ($isMaintaining) {
            while (true) {
                $dStr = $checkDate->format('Y-m-d');
                if (in_array($dStr, $allDatesSet, true)) {
                    $currentStreak++;
                    $checkDate->subDay();
                } else {
                    break;
                }
            }
        }

        $longestStreak = $currentStreak;
        if (!empty($allDatesSet)) {
            $tempLongest = 1;
            $maxStreak = 1;
            $datesArr = array_values($allDatesSet);
            for ($i = 0; $i < count($datesArr) - 1; $i++) {
                $date1 = new \DateTime($datesArr[$i]);
                $date2 = new \DateTime($datesArr[$i + 1]);
                $diff = $date1->diff($date2)->days;
                if ($diff == 1) {
                    $tempLongest++;
                    if ($tempLongest > $maxStreak) {
                        $maxStreak = $tempLongest;
                    }
                } else {
                    $tempLongest = 1;
                }
            }
            $longestStreak = max($currentStreak, $maxStreak);
        }
        $totalActiveDays = count($allDatesSet);

        $startOfWeek = now()->setTimezone($userTz)->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekDays = [];
        $dayLabels = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
        $completedDaysCount = 0;

        for ($i = 0; $i < 7; $i++) {
            $currentDayDate = (clone $startOfWeek)->addDays($i);
            $dStr = $currentDayDate->format('Y-m-d');
            $isActive = in_array($dStr, $allDatesSet, true);
            $isToday = ($dStr === $today);

            if ($isActive) {
                $completedDaysCount++;
            }

            $daySec = $dailyLearningSecondsMap[$dStr] ?? 0;
            $learningTimeFormatted = $daySec >= 60 
                ? (floor($daySec / 60) . ' phút ' . ($daySec % 60 > 0 ? ($daySec % 60) . 's' : ''))
                : ($daySec > 0 ? $daySec . ' giây' : ($isActive ? 'Đã hoàn thành bài học' : '0 phút'));

            $weekDays[] = [
                'day' => $dayLabels[$i],
                'date' => $dStr,
                'active' => $isActive,
                'isToday' => $isToday,
                'is_today' => $isToday,
                'learning_seconds' => $daySec,
                'learning_time' => trim($learningTimeFormatted),
            ];
        }

        $daysNeeded = max(0, 7 - $currentStreak);
        $nextMilestone = 7;
        $badgeName = 'Chiến binh Chăm chỉ';

        $message = $daysNeeded > 0
            ? "Học thêm {$daysNeeded} ngày nữa để đạt mốc {$nextMilestone} ngày liên tiếp và mở khóa huy hiệu {$badgeName}!"
            : "Chúc mừng bạn đã hoàn thành xuất sắc chuỗi 7 ngày học liên tiếp trong tuần!";

        return [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'total_active_days' => $totalActiveDays,
            'is_maintaining' => $isMaintaining,
            'status_label' => $isMaintaining ? 'Đang duy trì' : 'Chưa bắt đầu',
            'completed_days_in_week' => $completedDaysCount,
            'total_days_in_week' => 7,
            'week_days' => $weekDays,
            'encouragement' => [
                'days_needed' => $daysNeeded,
                'next_milestone' => $nextMilestone,
                'badge_name' => $badgeName,
                'message' => $message,
            ],
        ];
    }
}

