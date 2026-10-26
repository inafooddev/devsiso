<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\SecurityIncident;
use App\Models\BlockedIp;
use App\Models\IpWhitelist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class SecurityMonitoring extends Component
{
    use WithPagination;

    // ─── Tab Aktif ──────────────────────────────────────────────────────────
    public string $activeTab = 'incidents'; // 'incidents' | 'blocked' | 'whitelist'

    // ─── Filter & Pencarian Log Insiden ─────────────────────────────────────
    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $severityFilter = 'all'; // 'all', 'critical', 'high', 'medium', 'low'
    public string $threatFilter = 'all'; // 'all', 'sensitive_probe', 'brute_force', etc.
    public int $perPage = 15;

    // ─── Pengaturan Real-time & Auto-Defense ───────────────────────────────
    public bool $autoRefresh = true;
    public bool $autoBanEnabled = true;

    // ─── Modal Detail Insiden ───────────────────────────────────────────────
    public ?int $selectedIncidentId = null;

    // ─── Modal Manual Ban IP ────────────────────────────────────────────────
    public bool $showBanModal = false;
    public string $banModalIp = '';
    public string $banModalReason = '';
    public string $banModalDuration = '24_hours'; // '1_hour', '24_hours', '7_days', 'permanent'
    public ?string $banModalThreatType = null;

    // ─── Form Input Whitelist Baru ──────────────────────────────────────────
    public string $newWhitelistIp = '';
    public string $newWhitelistLabel = '';

    protected $queryString = [
        'activeTab' => ['except' => 'incidents'],
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'severityFilter' => ['except' => 'all'],
        'threatFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        if (empty($this->dateFrom)) {
            $this->dateFrom = now()->toDateString();
        }
        if (empty($this->dateTo)) {
            $this->dateTo = now()->toDateString();
        }

        $this->autoBanEnabled = (bool) Cache::get('security:auto_ban_enabled', true);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSeverityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingThreatFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * Toggle fitur otomatis ban untuk serangan kritis.
     */
    public function toggleAutoBan(): void
    {
        $this->autoBanEnabled = ! $this->autoBanEnabled;
        Cache::put('security:auto_ban_enabled', $this->autoBanEnabled, now()->addYear());
        session()->flash('message', 'Pengaturan Auto-Ban diperbarui: ' . ($this->autoBanEnabled ? 'AKTIF' : 'NONAKTIF'));
    }

    /**
     * Toggle auto refresh polling.
     */
    public function toggleAutoRefresh(): void
    {
        $this->autoRefresh = ! $this->autoRefresh;
    }

    /**
     * Buka modal untuk memblokir IP tertentu.
     */
    public function openBanModal(string $ip, ?string $threatType = null): void
    {
        $this->banModalIp = $ip;
        $this->banModalThreatType = $threatType;
        $this->banModalReason = $threatType ? "Terdeteksi melakukan {$threatType}" : 'Aktivitas mencurigakan';
        $this->banModalDuration = '24_hours';
        $this->showBanModal = true;
    }

    public function closeBanModal(): void
    {
        $this->showBanModal = false;
        $this->banModalIp = '';
        $this->banModalReason = '';
    }

    /**
     * Eksekusi pemblokiran IP.
     */
    public function confirmBan(): void
    {
        $this->validate([
            'banModalIp' => ['required', 'ip'],
            'banModalReason' => ['required', 'string', 'max:255'],
        ]);

        $expiresAt = match ($this->banModalDuration) {
            '1_hour' => now()->addHour(),
            '24_hours' => now()->addDay(),
            '7_days' => now()->addDays(7),
            'permanent' => null,
            default => now()->addDay(),
        };

        BlockedIp::blockIp(
            $this->banModalIp,
            $this->banModalReason,
            $this->banModalThreatType,
            Auth::id(),
            $expiresAt,
            false
        );

        $this->closeBanModal();
        session()->flash('message', "IP {$this->banModalIp} berhasil dimasukkan ke daftar blokir.");
    }

    /**
     * Buka blokir IP.
     */
    public function unbanIp(string $ip): void
    {
        BlockedIp::unblockIp($ip);
        session()->flash('message', "Blokir untuk IP {$ip} berhasil dicabut.");
    }

    /**
     * Tambahkan IP baru ke Whitelist.
     */
    public function addWhitelist(): void
    {
        $this->validate([
            'newWhitelistIp' => ['required', 'ip', 'unique:ip_whitelists,ip_address'],
            'newWhitelistLabel' => ['required', 'string', 'max:100'],
        ]);

        IpWhitelist::addIp($this->newWhitelistIp, $this->newWhitelistLabel, Auth::id());

        $this->newWhitelistIp = '';
        $this->newWhitelistLabel = '';
        session()->flash('message', 'IP berhasil ditambahkan ke daftar Whitelist.');
    }

    /**
     * Hapus IP dari Whitelist.
     */
    public function removeWhitelist(string $ip): void
    {
        IpWhitelist::removeIp($ip);
        session()->flash('message', "IP {$ip} dihapus dari Whitelist.");
    }

    /**
     * Modal detail insiden keamanan.
     */
    public function showIncidentDetail(int $id): void
    {
        $this->selectedIncidentId = $id;
    }

    public function closeIncidentDetail(): void
    {
        $this->selectedIncidentId = null;
    }

    /**
     * Dapatkan statistik KPI keamanan hari ini.
     */
    public function getKpiStatsProperty(): array
    {
        $today = now()->toDateString();

        return [
            'today_total' => SecurityIncident::whereDate('created_at', $today)->count(),
            'today_critical' => SecurityIncident::whereDate('created_at', $today)->where('severity', 'critical')->count(),
            'active_blocked' => BlockedIp::count(),
            'today_brute_force' => SecurityIncident::whereDate('created_at', $today)->where('threat_type', 'brute_force')->count(),
        ];
    }

    public function render()
    {
        // 1. Data Insiden (Tab 1)
        $incidentsQuery = SecurityIncident::query();

        if (! empty($this->dateFrom)) {
            $incidentsQuery->whereDate('created_at', '>=', $this->dateFrom);
        }
        if (! empty($this->dateTo)) {
            $incidentsQuery->whereDate('created_at', '<=', $this->dateTo);
        }
        if ($this->severityFilter !== 'all') {
            $incidentsQuery->where('severity', $this->severityFilter);
        }
        if ($this->threatFilter !== 'all') {
            $incidentsQuery->where('threat_type', $this->threatFilter);
        }
        if (! empty($this->search)) {
            $incidentsQuery->where(function ($q) {
                $q->where('ip_address', 'like', "%{$this->search}%")
                  ->orWhere('url', 'like', "%{$this->search}%")
                  ->orWhere('payload', 'like', "%{$this->search}%")
                  ->orWhere('user_agent', 'like', "%{$this->search}%");
            });
        }

        $incidents = $incidentsQuery->latest('id')->paginate($this->perPage);

        // 2. Data Blocked IPs (Tab 2)
        $blockedIps = BlockedIp::with('user')->latest('id')->get();

        // 3. Data Whitelist (Tab 3)
        $whitelists = IpWhitelist::with('user')->latest('id')->get();

        // 4. Detail Insiden terpilih
        $selectedIncident = $this->selectedIncidentId
            ? SecurityIncident::find($this->selectedIncidentId)
            : null;

        return view('livewire.settings.security-monitoring', [
            'incidents' => $incidents,
            'blockedIps' => $blockedIps,
            'whitelists' => $whitelists,
            'selectedIncident' => $selectedIncident,
            'kpiStats' => $this->kpiStats,
        ]);
    }
}
