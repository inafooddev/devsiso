<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Log Insiden Keamanan
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->string('threat_type', 50)->index(); // sensitive_probe, brute_force, malicious_bot, sql_injection_attempt, xss_attempt, other
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low')->index();
            $table->string('method', 10)->default('GET');
            $table->text('url');
            $table->text('payload')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action_taken', 50)->default('logged'); // logged, blocked, auto_banned
            $table->timestamps();

            // Index komposit untuk query analitik cepat
            $table->index(['severity', 'created_at']);
            $table->index(['created_at']);
        });

        // 2. Tabel IP Terblokir (Blacklist)
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('reason', 255)->nullable();
            $table->string('threat_type', 50)->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_auto_ban')->default(false);
            $table->timestamp('expires_at')->nullable()->index(); // null = permanen
            $table->timestamps();
        });

        // 3. Tabel IP Whitelist (Pengecualian / IP Aman)
        Schema::create('ip_whitelists', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('label', 100); // e.g. "Kantor Pusat", "VPN IT", "Local Dev"
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_whitelists');
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('security_incidents');
    }
};
