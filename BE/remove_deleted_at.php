<?php

$dir = new RecursiveDirectoryIterator('app');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$count = 0;
foreach ($files as $file) {
    $filePath = $file[0];
    $content = file_get_contents($filePath);
    
    // We want to remove `->whereNull('deleted_at')` and `->whereNull('table.deleted_at')`
    // but what if it's `$query->whereNull('deleted_at')` on its own line?
    // If we just remove the `->whereNull('deleted_at')`, it will leave `$query;` which is a no-op but syntactically valid.
    // If it's chained like `$query->where('x')->whereNull('deleted_at')->get()`, removing it yields `$query->where('x')->get()`, perfectly valid.
    
    $pattern1 = '/->whereNull\(\s*[\'"]deleted_at[\'"]\s*\)/';
    $pattern2 = '/->whereNull\(\s*[\'"][a-zA-Z0-9_]+\.deleted_at[\'"]\s*\)/';
    $pattern3 = '/->whereNotNull\(\s*[\'"]deleted_at[\'"]\s*\)/';
    $pattern4 = '/->whereNotNull\(\s*[\'"][a-zA-Z0-9_]+\.deleted_at[\'"]\s*\)/';
    
    $newContent = preg_replace([$pattern1, $pattern2, $pattern3, $pattern4], '', $content);
    
    if ($content !== $newContent) {
        file_put_contents($filePath, $newContent);
        echo "Updated: $filePath\n";
        $count++;
    }
}

echo "Total files updated: $count\n";

