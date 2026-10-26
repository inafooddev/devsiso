<?php

namespace App\Services\Security;

use Illuminate\Http\Request;

class ThreatDetector
{
    /**
     * Pola path sensitif yang sering dipindai oleh bot scanner.
     */
    protected static array $sensitivePathPatterns = [
        '/\.env/i' => ['severity' => 'critical', 'label' => 'Akses Berbahaya ke File Konfigurasi .env'],
        '/\.git(\/|$)/i' => ['severity' => 'critical', 'label' => 'Akses Berbahaya ke Repositori .git'],
        '/\.aws/i' => ['severity' => 'critical', 'label' => 'Akses ke Kredensial AWS'],
        '/\/cgi-bin\//i' => ['severity' => 'critical', 'label' => 'Eksploitasi PHP-CGI RCE (CVE-2024-4577)'],
        '/\/eval-stdin\.php/i' => ['severity' => 'critical', 'label' => 'Eksploitasi RCE PHP Unit / CGI'],
        '/\/wp-login\.php/i' => ['severity' => 'medium', 'label' => 'Scanning WordPress Login di Server Laravel'],
        '/\/wp-admin/i' => ['severity' => 'medium', 'label' => 'Scanning WordPress Admin di Server Laravel'],
        '/\/xmlrpc\.php/i' => ['severity' => 'medium', 'label' => 'Scanning WordPress XMLRPC'],
        '/\/phpmyadmin/i' => ['severity' => 'high', 'label' => 'Scanning Database Admin (phpMyAdmin)'],
        '/\/pma/i' => ['severity' => 'high', 'label' => 'Scanning Database Admin (PMA)'],
        '/\/adminer/i' => ['severity' => 'high', 'label' => 'Scanning Database Admin (Adminer)'],
        '/\/(shell|wso|c99|alfa)\.php/i' => ['severity' => 'critical', 'label' => 'Mencari Web Shell Bekas Injeksi'],
        '/\.vscode/i' => ['severity' => 'low', 'label' => 'Akses Folder Sensitif .vscode'],
        '/\.htaccess/i' => ['severity' => 'high', 'label' => 'Akses File Sensitif Apache .htaccess'],
        '/\/config(\.inc)?\.php\.bak/i' => ['severity' => 'high', 'label' => 'Mencari File Backup Konfigurasi'],
        '/\/id_rsa(\.pub)?/i' => ['severity' => 'critical', 'label' => 'Mencoba Mengunduh Kunci SSH Pribadi'],
    ];

    /**
     * User-Agent yang biasanya diasosiasikan dengan bot penyerang / automated scanner.
     */
    protected static array $badUserAgents = [
        'sqlmap' => ['severity' => 'critical', 'label' => 'Scanner Otomatis SQLMap Terdeteksi'],
        'nikto' => ['severity' => 'critical', 'label' => 'Vulnerability Scanner Nikto'],
        'acunetix' => ['severity' => 'critical', 'label' => 'Acunetix Scanner'],
        'havij' => ['severity' => 'critical', 'label' => 'Havij Automated Injector'],
        'masscan' => ['severity' => 'high', 'label' => 'Masscan Port / Path Scanner'],
        'wpscan' => ['severity' => 'medium', 'label' => 'WPScan Attack Tool'],
        'dirbuster' => ['severity' => 'high', 'label' => 'DirBuster Brute-Forcer'],
        'gobuster' => ['severity' => 'high', 'label' => 'GoBuster Path Brute-Forcer'],
    ];

    /**
     * Pola injeksi sederhana pada query string atau URI (SQLi / XSS probes).
     */
    protected static array $injectionPatterns = [
        '/union[\s\+]+select/i' => ['severity' => 'critical', 'type' => 'sql_injection_attempt', 'label' => 'Pola UNION SELECT Injeksi SQL'],
        '/information_schema/i' => ['severity' => 'high', 'type' => 'sql_injection_attempt', 'label' => 'Pola Probe Metadata Database'],
        '/\b(sleep|benchmark)\s*\(\s*[0-9]+\s*\)/i' => ['severity' => 'critical', 'type' => 'sql_injection_attempt', 'label' => 'Time-based Blind SQL Injection Attempt'],
        '/<script[\s>]/i' => ['severity' => 'high', 'type' => 'xss_attempt', 'label' => 'Script Tag Injection (XSS)'],
        '/javascript\s*:/i' => ['severity' => 'medium', 'type' => 'xss_attempt', 'label' => 'URI Scheme Injection (XSS)'],
        '/base64_decode\s*\(/i' => ['severity' => 'critical', 'type' => 'sensitive_probe', 'label' => 'Eksekusi Obfuscated Code (base64_decode)'],
    ];

    /**
     * Analisis request HTTP masuk.
     * Mengembalikan array informasi ancaman jika terdeteksi, atau null jika aman.
     */
    public static function analyze(Request $request): ?array
    {
        $uri = $request->getRequestUri();
        $path = $request->path();
        $userAgent = $request->header('User-Agent', '');

        // 1. Cek User Agent Buruk
        foreach (self::$badUserAgents as $signature => $info) {
            if (stripos($userAgent, $signature) !== false) {
                return [
                    'threat_type' => 'malicious_bot',
                    'severity' => $info['severity'],
                    'label' => $info['label'],
                    'payload' => "User-Agent: {$userAgent}",
                    'auto_ban' => $info['severity'] === 'critical',
                ];
            }
        }

        // 2. Cek Path Sensitif / Probing
        foreach (self::$sensitivePathPatterns as $pattern => $info) {
            if (preg_match($pattern, $uri) || preg_match($pattern, $path)) {
                return [
                    'threat_type' => 'sensitive_probe',
                    'severity' => $info['severity'],
                    'label' => $info['label'],
                    'payload' => "URI: {$uri}",
                    'auto_ban' => $info['severity'] === 'critical',
                ];
            }
        }

        // 3. Cek Injeksi pada Query String
        $queryString = $request->getQueryString() ?? '';
        if (! empty($queryString)) {
            $decodedQuery = urldecode($queryString);
            foreach (self::$injectionPatterns as $pattern => $info) {
                if (preg_match($pattern, $decodedQuery)) {
                    return [
                        'threat_type' => $info['type'],
                        'severity' => $info['severity'],
                        'label' => $info['label'],
                        'payload' => "Query: {$queryString}",
                        'auto_ban' => $info['severity'] === 'critical',
                    ];
                }
            }
        }

        return null;
    }
}
