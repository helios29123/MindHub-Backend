<?php
namespace App\Http\Resources\Marketing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorCouponDeleteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->resource['id'],
            'deleted' => true,
        ];
    }
}