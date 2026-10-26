<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Pembersihan Otomatis Log Keamanan (Dijalankan harian di server Ubuntu)
\Illuminate\Support\Facades\Schedule::command('security:prune-logs --days=30')->daily();
