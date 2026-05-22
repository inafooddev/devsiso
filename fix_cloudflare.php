<?php

$appPath = __DIR__ . '/bootstrap/app.php';
$providerPath = __DIR__ . '/app/Providers/AppServiceProvider.php';

echo "=== Laravel Cloudflare SSL Mixed Content Fixer ===\n";

// 1. Fix bootstrap/app.php
if (file_exists($appPath)) {
    $appContent = file_get_contents($appPath);
    if (strpos($appContent, 'trustProxies') === false) {
        $pattern = '/->withMiddleware\(function\s*\(Middleware\s*\$middleware\)\s*:\s*void\s*\{/';
        if (preg_match($pattern, $appContent)) {
            $replacement = "->withMiddleware(function (Middleware \$middleware): void {\n        \$middleware->trustProxies(at: '*');";
            $appContent = preg_replace($pattern, $replacement, $appContent);
            file_put_contents($appPath, $appContent);
            echo "SUCCESS: Added trustProxies(at: '*') to bootstrap/app.php\n";
        } else {
            echo "ERROR: Could not find withMiddleware block in bootstrap/app.php\n";
        }
    } else {
        echo "INFO: trustProxies already exists in bootstrap/app.php\n";
    }
} else {
    echo "ERROR: bootstrap/app.php not found.\n";
}

// 2. Fix AppServiceProvider.php
if (file_exists($providerPath)) {
    $providerContent = file_get_contents($providerPath);
    $modified = false;

    // Add import if not present
    if (strpos($providerContent, 'use Illuminate\Support\Facades\URL;') === false) {
        $importPattern = '/use Illuminate\\\Support\\\ServiceProvider;/';
        if (preg_match($importPattern, $providerContent)) {
            $providerContent = preg_replace(
                $importPattern,
                "use Illuminate\Support\ServiceProvider;\nuse Illuminate\Support\Facades\URL;",
                $providerContent
            );
            $modified = true;
            echo "SUCCESS: Imported URL facade in AppServiceProvider\n";
        }
    } else {
        echo "INFO: URL facade already imported in AppServiceProvider\n";
    }

    // Add forceScheme logic if not present or upgrade to robust version
    $robustForceScheme = "if ((isset(\$_SERVER['HTTP_X_FORWARDED_PROTO']) && \$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || 
            (isset(\$_SERVER['HTTP_HOST']) && \$_SERVER['HTTP_HOST'] === 'master.my.id') ||
            (isset(\$_SERVER['SERVER_NAME']) && \$_SERVER['SERVER_NAME'] === 'master.my.id')) {
            URL::forceScheme('https');
        }";

    if (strpos($providerContent, 'forceScheme') === false) {
        $bootPattern = '/public function boot\(\):\s*void\s*\{/';
        if (preg_match($bootPattern, $providerContent)) {
            $providerContent = preg_replace(
                $bootPattern,
                "public function boot(): void\n    {\n        " . $robustForceScheme . "\n",
                $providerContent
            );
            $modified = true;
            echo "SUCCESS: Added robust forceScheme('https') logic to AppServiceProvider::boot()\n";
        } else {
            echo "ERROR: Could not find boot() method in AppServiceProvider\n";
        }
    } else {
        // Upgrade if it's the old version (doesn't contain master.my.id)
        if (strpos($providerContent, 'master.my.id') === false) {
            $oldPattern = '/if\s*\(isset\(\$_SERVER\[\'HTTP_X_FORWARDED_PROTO\'\]\)\s*&&\s*\$_SERVER\[\'HTTP_X_FORWARDED_PROTO\'\]\s*===\s*\'https\'\)\s*\{\s*URL::forceScheme\(\'https\'\);\s*\}/';
            if (preg_match($oldPattern, $providerContent)) {
                $providerContent = preg_replace($oldPattern, $robustForceScheme, $providerContent);
                $modified = true;
                echo "SUCCESS: Upgraded forceScheme logic to robust version in AppServiceProvider\n";
            } else {
                echo "WARNING: forceScheme exists but could not auto-upgrade (format mismatch).\n";
            }
        } else {
            echo "INFO: Robust forceScheme already exists in AppServiceProvider\n";
        }
    }

    if ($modified) {
        file_put_contents($providerPath, $providerContent);
    }
} else {
    echo "ERROR: AppServiceProvider.php not found.\n";
}
echo "=================================================\n";
