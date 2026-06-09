<?php
namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginationMeta
{
    public static function fromPaginator(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
