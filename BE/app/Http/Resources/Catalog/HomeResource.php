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
            'discounted_courses' => CatalogCourseResource::collection($this->resource['discounted_courses'] ?? []),
            'featured_instructors' => FeaturedInstructorResource::collection($this->resource['featured_instructors']),
            'faqs' => collect($this->resource['faqs'] ?? [])->map(fn ($faq) => [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'type' => $faq->type,
                'sort_order' => (int) $faq->sort_order,
            ])->values(),
            'testimonials' => collect($this->resource['testimonials'] ?? [])->map(fn ($rev) => [
                'id' => $rev->id,
                'rating' => (int) $rev->rating,
                'comment' => $rev->comment,
                'user_name' => $rev->order?->user?->full_name ?? 'Học viên MindHub',
                'user_role' => 'Học viên',
                'user_avatar' => $rev->order?->user?->avatar_url,
            ])->values(),
            'vouchers' => collect($this->resource['vouchers'] ?? [])->map(fn ($v) => [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'description' => $v->description,
                'discount_type' => $v->discount_type,
                'discount_value' => (float) $v->discount_value,
                'start_at' => $v->start_at,
                'end_at' => $v->end_at,
            ])->values(),
            'stats' => $this->resource['stats'] ?? [],
        ];
    }
}
