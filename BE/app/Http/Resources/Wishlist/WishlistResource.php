<?php
namespace App\Http\Resources\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class WishlistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'created_at' => $this->created_at ? (string) $this->created_at : null,
            'course' => $this->whenLoaded('course', function (): array {
                return [
                    'id' => $this->course->id,
                    'title' => $this->course->title,
                    'slug' => $this->course->slug,
                    'thumbnail_url' => $this->course->thumbnail_url,
                    'price' => $this->course->price,
                    'sale_price' => $this->course->sale_price,
                    'level' => $this->course->level,
                    'language' => $this->course->language,
                    'status' => $this->course->status,
                ];
            }),
        ];
    }
}