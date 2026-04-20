<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use \Firebase\JWT\JWT; // Import library JWT yang baru diinstal

class MetabaseDashboard extends Component
{
    public $iframeUrl;

    // Ambil variabel dari file .env untuk keamanan
    // Pastikan Anda menambahkan ini ke file .env Anda:
    // METABASE_SITE_URL=http://192.168.1.92:3000
    // METABASE_SECRET_KEY=e4148caca8573374846599767b43434f5b65d9d0167cce09b60abb61024e124a
    
    public function mount()
    {
        $metabaseSiteUrl = config('services.metabase.site_url', 'http://192.168.1.92:3000');
        $metabaseSecretKey = config('services.metabase.secret_key', 'e4148caca8573374846599767b43434f5b65d9d0167cce09b60abb61024e124a');

        // Sesuaikan ID dashboard yang ingin Anda tampilkan
        $dashboardId = 111; 

        $payload = [
            "resource" => ["dashboard" => $dashboardId],
            "params"   => new \stdClass(), // Gunakan objek kosong
            "exp"      => time() + (10 * 60) // Token berlaku 10 menit
        ];

        // Generate token di PHP
        $token = JWT::encode($payload, $metabaseSecretKey, 'HS256');

        // Buat URL Iframe
        $this->iframeUrl = $metabaseSiteUrl . "/embed/dashboard/" . $token . "#background=false&bordered=false&titled=false";
    }

    public function render()
    {
        return view('livewire.dashboard.metabase-dashboard')
               ->layout('layouts.app');
    }
}