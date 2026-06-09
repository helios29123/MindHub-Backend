<?php
namespace App\Http\Resources\Catalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class HomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'banners' => $this->resource['banners']->map(fn ($banner) => [
                'id' => $banner->id,
                'title' => $banner->title,
                'image_url' => $banner->image_url,
                'target_url' => $banner->target_url,
                'position' => $banner->position,
                'sort_order' => $banner->sort_order,
            ])->values(),
            'categories' => CategoryResource::collection($this->resource['categories']),
            'featured_courses' => CatalogCourseResource::collection($this->resource['featured_courses']),
            'latest_courses' => CatalogCourseResource::collection($this->resource['latest_courses']),
            'featured_instructors' => FeaturedInstructorResource::collection($this->resource['featured_instructors']),
        ];
    }
}
