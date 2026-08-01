<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Reset all root categories to simple alphabetic values
        $rootCategories = \Illuminate\Support\Facades\DB::table('categories')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
            
        foreach ($rootCategories as $idx => $cat) {
            $char = chr(97 + $idx); // 'a', 'b', 'c'...
            \Illuminate\Support\Facades\DB::table('categories')
                ->where('id', $cat->id)
                ->update(['sort_order' => $char]);
        }

        // 2. Reset all child categories per parent
        $parentIds = \Illuminate\Support\Facades\DB::table('categories')
            ->whereNotNull('parent_id')
            ->select('parent_id')
            ->distinct()
            ->pluck('parent_id');
            
        foreach ($parentIds as $parentId) {
            $children = \Illuminate\Support\Facades\DB::table('categories')
                ->where('parent_id', $parentId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
                
            foreach ($children as $idx => $cat) {
                $char = chr(97 + $idx); // 'a', 'b', 'c'...
                \Illuminate\Support\Facades\DB::table('categories')
                    ->where('id', $cat->id)
                    ->update(['sort_order' => $char]);
            }
        }
    }

    public function down(): void
    {
        // No-op rollback as it's just data restructuring
    }
};
