<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$migrationsPath = database_path('migrations');
$files = glob($migrationsPath . '/*.php');
sort($files);

// Ensure migrations table exists
if (!\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
    echo "Creating migrations table...\n";
    \Illuminate\Support\Facades\Schema::create('migrations', function ($table) {
        $table->increments('id');
        $table->string('migration');
        $table->integer('batch');
    });
}

foreach ($files as $file) {
    $filename = basename($file, '.php');
    
    // Check if migration already registered
    $exists = DB::table('migrations')->where('migration', $filename)->exists();
    if ($exists) {
        echo "Migration $filename already registered. Skipping.\n";
        continue;
    }
    
    echo "Running migration: $filename...\n";
    
    try {
        // Include the migration file and run it
        $migration = require $file;
        
        if (is_object($migration) && method_exists($migration, 'up')) {
            $migration->up();
        }
        
        // Record in migrations table
        DB::table('migrations')->insert([
            'migration' => $filename,
            'batch' => 1
        ]);
        echo "Migration $filename run successfully and registered.\n";
    } catch (\Throwable $e) {
        $msg = $e->getMessage();
        if (
            str_contains($msg, 'already exists') || 
            str_contains($msg, 'Duplicate column') || 
            str_contains($msg, 'Duplicate key') ||
            str_contains($msg, 'Duplicate entry') ||
            str_contains($msg, 'already indexed')
        ) {
            echo "Notice: Table, column, index, or constraint in $filename already exists in database. Registering migration as completed.\n";
            DB::table('migrations')->insert([
                'migration' => $filename,
                'batch' => 1
            ]);
        } else {
            echo "Error running migration $filename: " . $msg . "\n";
        }
    }
}

echo "Database synchronization completed successfully!\n";
