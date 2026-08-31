<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $thumbnails = [
            1 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202401/courses_thumbnail/l5t4rsgcb6gqgf8hvvs8.jpg',
            2 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202405/courses_thumbnail/smc8ke0qldnezy1ete1u.jpg',
            3 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202408/courses_thumbnail/udqewhfrqn5cjnujcova.jpg',
            4 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202412/courses_thumbnail/wqw9amypfhttkgrpvidv.jpg',
            5 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202389/courses_thumbnail/l5uo0m3ic682p3gl26jg.jpg',
            6 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202391/courses_thumbnail/qlfyydulmdlvj8l1pg0v.jpg',
            7 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202393/courses_thumbnail/ld7or31uun6mvbzxd75s.jpg',
            8 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202395/courses_thumbnail/jz1s8pzosx1vzlbbxqfd.jpg',
            9 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202397/courses_thumbnail/zrfvoo3bbt4brvba8wdg.jpg',
            10 => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202399/courses_thumbnail/zhxwppkb6xhxgtwhd0jf.jpg'
        ];

        foreach ($thumbnails as $id => $url) {
            DB::table('courses')->where('id', $id)->update(['thumbnail_url' => $url]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 
    }
};
