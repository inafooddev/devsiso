<?php

$dir = __DIR__ . '/database/migrations';
$files = glob($dir . '/*.php');

$pattern = '/([ \t]*)Schema::create\(\s*([^,]+)\s*,\s*(?:static\s+)?function[^{]*(\{(?:[^{}]++|(?3))*\})\s*\);/s';

$patchedCount = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    $newContent = preg_replace_callback($pattern, function ($matches) {
        $indent = $matches[1];
        $tableNameExpr = trim($matches[2]);
        $original = ltrim($matches[0]);
        
        $indentedOriginal = str_replace("\n", "\n    ", $original);
        
        return $indent . "if (!Schema::hasTable($tableNameExpr)) {\n" .
               $indent . "    " . $indentedOriginal . "\n" .
               $indent . "}";
    }, $content);
    
    if ($content !== $newContent) {
        if (strpos($content, "Schema::hasTable") === false) {
            file_put_contents($file, $newContent);
            echo "Patched: " . basename($file) . "\n";
            $patchedCount++;
        }
    }
}

echo "Total patched: $patchedCount\n";
