<?php

namespace App\Repositories\Learning;

use App\Models\LessonAsset;

class LessonAssetRepository
{
    public function findById(int $id): ?LessonAsset
    {
        return LessonAsset::with(['lesson.course'])->find($id);
    }
}
