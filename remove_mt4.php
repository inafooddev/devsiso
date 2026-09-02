<?php
$dir = __DIR__ . '/resources/views/livewire/jobs';
$files = [
    $dir . '/join-so-eska-non-eksa/index.blade.php',
    $dir . '/sellout-per-cabang-sqlserver.blade.php',
    $dir . '/update-ao-percabang.blade.php',
    $dir . '/update-salesmans.blade.php',
    $dir . '/update-sellin-per-cabang.blade.php',
    $dir . '/zv-so-per-toko-2026.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    $newContent = str_replace('w-full h-full mt-4', 'w-full h-full', $content);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Removed mt-4 from " . basename($file) . "\n";
    }
}
