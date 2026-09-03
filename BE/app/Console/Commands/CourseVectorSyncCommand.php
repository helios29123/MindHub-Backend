<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\CourseEmbedding;
use App\Services\Ai\EmbeddingService;
use Illuminate\Console\Command;

class CourseVectorSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'courses:sync-embeddings {--force : Force regenerate vectors for all courses}';

    /**
     * The console command description.
     */
    protected $description = 'Generate and synchronize AI dense vector embeddings for all published courses';

    /**
     * Execute the console command.
     */
    public function handle(EmbeddingService $embeddingService): int
    {
        $force = (bool) $this->option('force');
        $this->info('Starting AI Vector Embeddings synchronization...');

        $courses = Course::with(['categories', 'sections.lessons'])->get();
        $this->info("Found {$courses->count()} total courses.");

        $processed = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($courses->count());
        $bar->start();

        foreach ($courses as $course) {
            $payloadInfo = $embeddingService->buildCoursePayload($course);
            $existing = CourseEmbedding::where('course_id', $course->id)->first();

            if (!$force && $existing && $existing->payload_hash === $payloadInfo['hash']) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $vector = $embeddingService->generateEmbedding($payloadInfo['payload']);

            CourseEmbedding::updateOrCreate(
                ['course_id' => $course->id],
                [
                    'embedding_model' => 'text-embedding-004',
                    'dimensions' => count($vector),
                    'vector' => $vector,
                    'payload_hash' => $payloadInfo['hash'],
                    'content_summary' => $payloadInfo['summary'],
                ]
            );

            $processed++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Synchronization completed: {$processed} updated/created, {$skipped} skipped (unchanged).");

        return Command::SUCCESS;
    }
}
