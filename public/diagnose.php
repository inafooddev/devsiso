<?php

/**
 * DevSiso Livewire & Environment Diagnostician
 * 
 * Tool ini digunakan untuk mendiagnosis masalah routing Livewire (seperti error 404 upload-file)
 * di lingkungan produksi secara real-time.
 * 
 * PENTING: Hapus file ini setelah debugging selesai untuk alasan keamanan!
 */

// 1. Bootstrapping Laravel Container
define('LARAVEL_START', microtime(true));

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
$bootstrapPath = __DIR__ . '/../bootstrap/app.php';

if (!file_exists($autoloadPath) || !file_exists($bootstrapPath)) {
    die("<h3>Error: Path Laravel tidak ditemukan. Pastikan file ini diletakkan di folder 'public/' Laravel Anda.</h3>");
}

require $autoloadPath;
$app = require_once $bootstrapPath;
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\URL;

$message = '';
$messageType = 'success';

// 2. Action Handler
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    try {
        if ($action === 'clear_cache') {
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            $message = "Semua cache Laravel (Route, Config, Cache, View) berhasil dibersihkan!";
        } elseif ($action === 'storage_link') {
            Artisan::call('storage:link');
            $message = "Symbolic link public/storage berhasil dibuat!";
        } elseif ($action === 'fix_tmp_dir') {
            $tmpPath = storage_path('app/livewire-tmp');
            if (!file_exists($tmpPath)) {
                mkdir($tmpPath, 0775, true);
            }
            chmod($tmpPath, 0775);
            $message = "Direktori livewire-tmp berhasil dibuat & diatur permisi menulisnya!";
        } elseif ($action === 'test_upload') {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $message = "Metode request harus POST.";
                $messageType = 'error';
            } elseif (empty($_FILES)) {
                $maxPost = ini_get('post_max_size');
                $message = "Gagal: Tidak ada file yang diterima. Pastikan ukuran file tidak melebihi post_max_size ({$maxPost}).";
                $messageType = 'error';
            } else {
                $file = $_FILES['test_file'] ?? null;
                if ($file) {
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $errMap = [
                            UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi upload_max_filesize di php.ini.',
                            UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi MAX_FILE_SIZE di form HTML.',
                            UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian.',
                            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diunggah.',
                            UPLOAD_ERR_NO_TMP_DIR => 'Folder temp PHP (upload_tmp_dir) tidak ditemukan/tidak dikonfigurasi.',
                            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk (Permisi folder temp PHP bermasalah).',
                            UPLOAD_ERR_EXTENSION => 'Unggahan dihentikan oleh ekstensi PHP.'
                        ];
                        $errText = $errMap[$file['error']] ?? 'Error tidak diketahui (Kode: ' . $file['error'] . ')';
                        $message = "Gagal Mengunggah: " . $errText;
                        $messageType = 'error';
                    } else {
                        // Test moving the file to livewire-tmp
                        $destDir = storage_path('app/livewire-tmp');
                        if (!file_exists($destDir)) {
                            @mkdir($destDir, 0775, true);
                        }
                        $destPath = $destDir . '/test_upload_' . time() . '_' . basename($file['name']);
                        if (@move_uploaded_file($file['tmp_name'], $destPath)) {
                            $message = "SUKSES! File berhasil diunggah ke temp PHP dan dipindahkan ke livewire-tmp/ (" . basename($destPath) . ")";
                            $messageType = 'success';
                            @unlink($destPath); // Clean up
                        } else {
                            $message = "Gagal memindahkan file ke folder livewire-tmp/ (Permisi menulis di storage/app/livewire-tmp salah).";
                            $messageType = 'error';
                        }
                    }
                } else {
                    $message = "File input 'test_file' tidak ditemukan.";
                    $messageType = 'error';
                }
            }
        } else {
            $message = "Aksi tidak dikenali.";
            $messageType = 'error';
        }
    } catch (\Exception $e) {
        $message = "Gagal menjalankan aksi: " . $e->getMessage();
        $messageType = 'error';
    }
}

// 3. Menjalankan Diagnostik
$diagnostics = [];

// A. Route Checking
$routeUpload = Route::getRoutes()->getByName('livewire.upload-file');
$routeUpdate = Route::getRoutes()->getByName('livewire.update');

$diagnostics['routes'] = [
    'title' => 'Routing Livewire',
    'status' => $routeUpload && $routeUpdate ? 'ok' : 'error',
    'details' => [
        'Route `livewire.upload-file` (Upload)' => $routeUpload 
            ? 'Terdaftar (' . implode(', ', $routeUpload->methods()) . ') ke URI: ' . $routeUpload->uri() 
            : 'TIDAK TERDAFTAR (Ini penyebab 404!)',
        'Route `livewire.update` (State Update)' => $routeUpdate 
            ? 'Terdaftar (' . implode(', ', $routeUpdate->methods()) . ') ke URI: ' . $routeUpdate->uri() 
            : 'TIDAK TERDAFTAR (Komponen Livewire tidak bisa berinteraksi!)',
        'Route Cache Status' => app()->routesAreCached() 
            ? 'Aktif (Cached - jika ada perubahan kode, Anda harus melakukan route:clear!)' 
            : 'Tidak Aktif (Dynamic - bagus untuk development/troubleshooting)'
    ]
];

// B. Network & Protocol Checking
$request = request();
$isSecure = $request->isSecure();
$detectedScheme = $request->getScheme();
$hostHeader = $_SERVER['HTTP_HOST'] ?? 'N/A';
$xForwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A';
$cfVisitor = $_SERVER['HTTP_CF_VISITOR'] ?? 'N/A';
$serverPort = $_SERVER['SERVER_PORT'] ?? 'N/A';

$trustProxiesOk = false;
if ($xForwardedProto === 'https' && $isSecure) {
    $trustProxiesOk = true;
}

$diagnostics['network'] = [
    'title' => 'Deteksi Protokol & Proxy (Cloudflare)',
    'status' => $isSecure ? 'ok' : 'warning',
    'details' => [
        'Laravel Secure Request?' => $isSecure ? 'YA (HTTPS)' : 'TIDAK (HTTP - Rawan Mixed Content)',
        'Skema URL yang Didengar' => $detectedScheme . '://',
        'Host Request' => $hostHeader,
        'Header X-Forwarded-Proto' => $xForwardedProto,
        'Header HTTP_CF_VISITOR' => $cfVisitor,
        'Server Port' => $serverPort,
        'Trusted Proxies Status' => $trustProxiesOk 
            ? 'Bagus (Laravel mendeteksi HTTPS dari Cloudflare proxy)' 
            : 'Perhatian (Header X-Forwarded-Proto didengar tapi Laravel tetap menganggap HTTP. Pastikan trustProxies diatur ke * di bootstrap/app.php)'
    ]
];

// C. App Configuration
$appUrl = config('app.url');
$diagnostics['config'] = [
    'title' => 'Konfigurasi Laravel (.env)',
    'status' => str_starts_with($appUrl, 'https') ? 'ok' : 'warning',
    'details' => [
        'APP_URL di .env' => $appUrl,
        'APP_ENV di .env' => config('app.env'),
        'Livewire Rules (Max Upload)' => config('livewire.temporary_file_upload.rules') ?? 'Default (12MB)',
        'Storage Disk Default' => config('filesystems.default')
    ]
];

// D. Upload Permissions & Storage Directories
$tmpPath = storage_path('app/livewire-tmp');
$tmpExists = file_exists($tmpPath);
$tmpWritable = $tmpExists && is_writable($tmpPath);
$storageLinkExists = file_exists(public_path('storage'));

$diagnostics['storage'] = [
    'title' => 'Direktori & Permisi File',
    'status' => $tmpExists && $tmpWritable ? 'ok' : 'error',
    'details' => [
        'Direktori `storage/app/livewire-tmp` Ada?' => $tmpExists ? 'Ya' : 'TIDAK ADA (Livewire tidak bisa mengunggah!)',
        'Direktori `livewire-tmp` Bisa Ditulis?' => $tmpWritable ? 'Ya (Writable)' : 'TIDAK (Permission Error! Pastikan chmod diatur)',
        'Symbolic Link `public/storage` Ada?' => $storageLinkExists ? 'Ya' : 'TIDAK (Jalankan php artisan storage:link)'
    ]
];

// E. PHP Settings
$maxUploadSize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');
$fileinfoEnabled = extension_loaded('fileinfo');
$gdEnabled = extension_loaded('gd');

$diagnostics['php'] = [
    'title' => 'Konfigurasi PHP Server',
    'status' => $fileinfoEnabled && $gdEnabled ? 'ok' : 'warning',
    'details' => [
        'upload_max_filesize (PHP.ini)' => $maxUploadSize,
        'post_max_size (PHP.ini)' => $postMaxSize,
        'Ekstensi PHP `fileinfo` Aktif?' => $fileinfoEnabled ? 'Ya' : 'TIDAK AKTIF (Ini bisa menyebabkan upload Livewire gagal/stuck!)',
        'Ekstensi PHP `gd` Aktif?' => $gdEnabled ? 'Ya' : 'TIDAK AKTIF (Kompresi gambar di server tidak akan berfungsi)'
    ]
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevSiso - Diagnostik Environment Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
        }
        .font-mono-custom {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="text-slate-200 py-10 px-4 md:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <header class="text-center mb-8">
            <span class="bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Troubleshooting Tool</span>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white mt-2 tracking-tight">DevSiso Environment Diagnostician</h1>
            <p class="text-slate-400 text-sm mt-1">Gunakan halaman ini untuk memecahkan masalah sinkronisasi RWO Mobile dan 404 upload-file.</p>
            <div class="inline-block bg-rose-500/10 text-rose-300 border border-rose-500/20 text-xs px-4 py-1.5 rounded-xl font-bold mt-4 animate-pulse">
                ⚠️ WARNING: Hapus file <code>public/diagnose.php</code> setelah Anda selesai memecahkan masalah!
            </div>
        </header>

        <!-- Notification Banner -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-2xl border <?php echo $messageType === 'success' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-300' : 'bg-rose-500/10 border-rose-500/20 text-rose-300'; ?> flex items-center justify-between shadow-lg">
                <span class="text-sm font-semibold"><?php echo htmlspecialchars($message); ?></span>
                <a href="diagnose.php" class="text-xs underline hover:opacity-80">Tutup</a>
            </div>
        <?php endif; ?>

        <!-- Quick Action Dashboard -->
        <section class="bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 shadow-2xl mb-8">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                Perbaikan Cepat (Quick Actions)
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="?action=clear_cache" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs py-3 px-4 rounded-2xl text-center shadow-lg transition-all hover:scale-[1.02] active:scale-[0.98] flex flex-col justify-center gap-1">
                    <span>1. Bersihkan Semua Cache</span>
                    <span class="text-[10px] font-normal text-indigo-200">php artisan route/config:clear</span>
                </a>
                <a href="?action=storage_link" class="bg-slate-800 hover:bg-slate-700 text-slate-100 border border-slate-700 font-bold text-xs py-3 px-4 rounded-2xl text-center shadow-lg transition-all hover:scale-[1.02] active:scale-[0.98] flex flex-col justify-center gap-1">
                    <span>2. Buat Ulang Storage Link</span>
                    <span class="text-[10px] font-normal text-slate-400">php artisan storage:link</span>
                </a>
                <a href="?action=fix_tmp_dir" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs py-3 px-4 rounded-2xl text-center shadow-lg transition-all hover:scale-[1.02] active:scale-[0.98] flex flex-col justify-center gap-1">
                    <span>3. Buat & Perbaiki folder `livewire-tmp`</span>
                    <span class="text-[10px] font-normal text-emerald-200">Setup write permission (0775)</span>
                </a>
            </div>
        </section>

        <!-- Direct Upload Test Form -->
        <section class="bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 shadow-2xl mb-8">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Uji Coba Unggah File PHP Langsung
            </h2>
            <form action="?action=test_upload" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-4">
                <input type="file" name="test_file" required class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 bg-slate-950/40 border border-slate-800 p-2 rounded-2xl" />
                <button type="submit" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs py-3 px-6 rounded-2xl text-center shadow-lg transition-all hover:scale-[1.02] active:scale-[0.98] whitespace-nowrap">
                    Unggah Sekarang
                </button>
            </form>
            <p class="text-[10px] text-slate-400 mt-2">Form ini menguji apakah server PHP/Nginx Anda menerima unggahan file dengan benar di luar sistem routing Livewire.</p>
        </section>

        <!-- Diagnostic Modules -->
        <div class="space-y-6">
            <?php foreach ($diagnostics as $key => $module): ?>
                <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                        <h3 class="text-md font-bold text-white flex items-center gap-2">
                            <?php if ($module['status'] === 'ok'): ?>
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></span>
                            <?php elseif ($module['status'] === 'warning'): ?>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-lg shadow-amber-500/50"></span>
                            <?php else: ?>
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-lg shadow-rose-500/50 animate-pulse"></span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($module['title']); ?>
                        </h3>
                        <span class="text-xs uppercase font-bold tracking-wider <?php echo $module['status'] === 'ok' ? 'text-emerald-400' : ($module['status'] === 'warning' ? 'text-amber-400' : 'text-rose-400'); ?>">
                            <?php echo $module['status'] === 'ok' ? 'NORMAL' : ($module['status'] === 'warning' ? 'PERHATIAN' : 'ERROR / KENDALA'); ?>
                        </span>
                    </div>
                    <div class="space-y-3 font-mono-custom text-xs">
                        <?php foreach ($module['details'] as $label => $val): ?>
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-1 sm:gap-4 py-1.5 border-b border-slate-800/40 last:border-0">
                                <span class="text-slate-400 font-semibold"><?php echo htmlspecialchars($label); ?></span>
                                <span class="text-right text-white break-all sm:max-w-md <?php echo str_contains($val, 'TIDAK') || str_contains($val, 'ERROR') ? 'text-rose-400 font-bold' : ''; ?>">
                                    <?php echo htmlspecialchars($val); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <footer class="mt-12 text-center text-xs text-slate-500 font-semibold uppercase tracking-wider">
            &copy; <?php echo date('Y'); ?> DevSiso &bull; Hapus file ini setelah selesai debugging.
        </footer>
    </div>
</body>
</html>
