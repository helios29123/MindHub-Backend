<?php

namespace App\Console\Commands;

use Database\Seeders\FullCourseCatalogAndVideoSeeder;
use Illuminate\Console\Command;

class CourseSeedAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:seed-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed all 10 full courses with ~200 real Bunny CDN videos and categories';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Bắt đầu nạp 10 khóa học & 200 video bài giảng Bunny Stream CDN...');
        
        $seeder = new FullCourseCatalogAndVideoSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        // Also auto-sync embeddings
        $this->call('courses:sync-embeddings');

        $this->info('✅ Hoàn tất toàn bộ quy trình nạp 200 bài giảng & đồng bộ AI Vector Search!');
        return Command::SUCCESS;
    }
}
