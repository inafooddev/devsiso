<?php
$dir = __DIR__ . '/resources/views/livewire/jobs';
$files = [
    $dir . '/sellout-per-cabang-sqlserver.blade.php',
    $dir . '/so-full-join.blade.php',
    $dir . '/update-ao-percabang.blade.php',
    $dir . '/update-salesmans.blade.php',
    $dir . '/update-sellin-per-cabang.blade.php',
    $dir . '/zv-so-per-toko-2026.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Gunakan regex untuk menghapus block <x-ui.tab-menu> sampai </x-ui.tab-menu> beserta newline setelahnya
    $newContent = preg_replace('/[ \t]*<x-ui\.tab-menu.*?>.*?<\/x-ui\.tab-menu>[\r\n]*/s', '', $content);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Removed tab menu from " . basename($file) . "\n";
    }
}
