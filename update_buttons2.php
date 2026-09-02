<?php
$dir = __DIR__ . '/resources/views/livewire/jobs';
$files = [
    $dir . '/sellout-per-cabang-sqlserver.blade.php',
    $dir . '/update-ao-percabang.blade.php',
    $dir . '/update-salesmans.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Gunakan regex untuk mengganti button
    $pattern = '/<button wire:click="startProcess".*?class="btn btn-sm btn-primary rounded-xl normal-?case shadow-sm shadow-primary\/20">/s';
    $replace = <<<EOT
<button wire:click="startProcess" 
                        wire:loading.attr="disabled" 
                        wire:target="startProcess"
                        {{ in_array(\$batchStatus, ['pending', 'processing']) ? 'disabled' : '' }}
                        class="btn btn-sm btn-primary rounded-xl normal-case shadow-sm shadow-primary/20 {{ in_array(\$batchStatus, ['pending', 'processing']) ? 'opacity-50 cursor-not-allowed' : '' }}">
EOT;
    
    $newContent = preg_replace($pattern, $replace, $content);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Updated button in " . basename($file) . "\n";
    }
}
