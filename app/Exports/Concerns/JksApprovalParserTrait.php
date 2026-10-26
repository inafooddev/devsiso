<?php

namespace App\Exports\Concerns;

use Carbon\Carbon;

trait JksApprovalParserTrait
{
    protected array $hariMap = [
        'h1' => 'Senin',
        'h2' => 'Selasa',
        'h3' => 'Rabu',
        'h4' => 'Kamis',
        'h5' => 'Jumat',
        'h6' => 'Sabtu',
        'h7' => 'Minggu',
    ];

    protected array $weekMap = [
        'w1' => 'Minggu 1',
        'w2' => 'Minggu 2',
        'w3' => 'Minggu 3',
        'w4' => 'Minggu 4',
    ];

    protected function parseHari($payload, $reason = ''): string
    {
        // 1. Dari payload update (EDIT_INDIVIDU)
        if (isset($payload['update']) && is_array($payload['update'])) {
            $days = [];
            foreach ($this->hariMap as $k => $name) {
                if (($payload['update'][$k] ?? '') === 'Y') {
                    $days[] = $name;
                }
            }
            if (!empty($days)) return implode(', ', $days);
        }

        // 2. Dari payload hari array (TAMBAH_JADWAL)
        if (isset($payload['hari']) && is_array($payload['hari'])) {
            $days = [];
            foreach ($payload['hari'] as $h) {
                $days[] = $this->hariMap[$h] ?? $h;
            }
            if (!empty($days)) return implode(', ', $days);
        }

        // 3. Dari TUKAR_HARI
        if (isset($payload['hari_asal']) && isset($payload['hari_tujuan'])) {
            $asal = $this->hariMap[$payload['hari_asal']] ?? $payload['hari_asal'];
            $tujuan = $this->hariMap[$payload['hari_tujuan']] ?? $payload['hari_tujuan'];
            return "{$asal} -> {$tujuan}";
        }

        // 4. Fallback Regex dari reason
        if ($reason && preg_match('/Hari:\s*\[([^\]]+)\]/', $reason, $m)) {
            return trim($m[1]);
        }
        if ($reason && preg_match('/Hari:\s*([^,|]+)/', $reason, $m)) {
            return trim($m[1]);
        }

        return '-';
    }

    protected function parseMinggu($payload, $reason = ''): string
    {
        // 1. Dari payload update (EDIT_INDIVIDU)
        if (isset($payload['update']) && is_array($payload['update'])) {
            $weeks = [];
            foreach ($this->weekMap as $k => $name) {
                if (($payload['update'][$k] ?? '') === 'Y') {
                    $weeks[] = $name;
                }
            }
            if (!empty($weeks)) return implode(', ', $weeks);
        }

        // 2. Dari payload minggu array (TAMBAH_JADWAL)
        if (isset($payload['minggu']) && is_array($payload['minggu'])) {
            $weeks = [];
            foreach ($payload['minggu'] as $w) {
                $weeks[] = $this->weekMap[$w] ?? $w;
            }
            if (!empty($weeks)) return implode(', ', $weeks);
        }

        // 3. Dari TUKAR_MINGGU
        if (isset($payload['minggu_asal']) && isset($payload['minggu_tujuan'])) {
            $asal = $this->weekMap[$payload['minggu_asal']] ?? $payload['minggu_asal'];
            $tujuan = $this->weekMap[$payload['minggu_tujuan']] ?? $payload['minggu_tujuan'];
            return "{$asal} -> {$tujuan}";
        }

        // 4. Fallback Regex dari reason
        if ($reason && preg_match('/Minggu:\s*\[([^\]]+)\]/', $reason, $m)) {
            return trim($m[1]);
        }
        if ($reason && preg_match('/Minggu:\s*([^,|]+)/', $reason, $m)) {
            return trim($m[1]);
        }

        return '-';
    }

    protected function parseSalesman($row, $payload, $salesmanMap): array
    {
        $code = '';
        $name = '';

        if ($row->action_type === 'TUKAR_SALESMAN') {
            $asalCode = $payload['salesman_asal'] ?? '-';
            $tujuanCode = $payload['salesman_tujuan'] ?? '-';
            $asalName = $salesmanMap[$asalCode] ?? $asalCode;
            $tujuanName = $salesmanMap[$tujuanCode] ?? $tujuanCode;
            return [
                'code' => "{$asalCode} -> {$tujuanCode}",
                'name' => "{$asalName} -> {$tujuanName}",
            ];
        }

        if (isset($payload['salesman_code'])) {
            $code = $payload['salesman_code'];
        } elseif (isset($payload['update']['salesman_code'])) {
            $code = $payload['update']['salesman_code'];
        } elseif (isset($payload['records'][0]['salesman_code'])) {
            $code = $payload['records'][0]['salesman_code'];
        }

        if ($code && isset($salesmanMap[$code])) {
            $name = $salesmanMap[$code];
        }

        // Fallback regex jika nama salesman ada di reason
        if (!$name && $row->reason && preg_match('/SE:\s*([^|)\],]+)/', $row->reason, $m)) {
            $name = trim($m[1]);
        }

        return [
            'code' => $code ?: '-',
            'name' => $name ?: '-',
        ];
    }

    protected function parseBulan($row, $payload, $jksMap): string
    {
        $raw = null;
        if (isset($payload['bulan'])) {
            $raw = $payload['bulan'];
        } elseif (isset($payload['records'][0]['bulan'])) {
            $raw = $payload['records'][0]['bulan'];
        } elseif (isset($payload['id']) && isset($jksMap[$payload['id']])) {
            $raw = $jksMap[$payload['id']]->bulan ?? null;
        }

        if ($raw) {
            try {
                return Carbon::parse($raw)->translatedFormat('F Y');
            } catch (\Exception $e) {
                return $raw;
            }
        }

        // Fallback ke created_at
        try {
            return Carbon::parse($row->created_at)->translatedFormat('F Y');
        } catch (\Exception $e) {
            return '-';
        }
    }

    protected function parseTokoList($row, $payload, $jksMap): array
    {
        $tokos = [];

        // 1. Dari TAMBAH_JADWAL
        if (!empty($payload['tokos']) && is_array($payload['tokos'])) {
            foreach ($payload['tokos'] as $t) {
                $tokos[] = [
                    'code' => $t['code'] ?? ($payload['customer_code'] ?? '-'),
                    'name' => $t['name'] ?? '-',
                ];
            }
            if (!empty($tokos)) return $tokos;
        }

        // 2. Dari DELETE_INDIVIDU records
        if (!empty($payload['records']) && is_array($payload['records'])) {
            foreach ($payload['records'] as $r) {
                $tokos[] = [
                    'code' => $r['customer_code'] ?? '-',
                    'name' => $r['customer_name'] ?? '-',
                ];
            }
            if (!empty($tokos)) return $tokos;
        }

        // 3. Dari EDIT_INDIVIDU atau single id
        if (isset($payload['id'])) {
            $id = $payload['id'];
            $code = '-';
            if (isset($jksMap[$id])) {
                $code = $jksMap[$id]->customer_code ?? '-';
            }
            $name = '-';
            if ($row->reason && preg_match('/Edit Jadwal Toko:\s*([^(]+?)\s*\(SE:/', $row->reason, $m)) {
                $name = trim($m[1]);
            } elseif ($row->reason && preg_match('/Jadwal Toko:\s*([^|]+)/', $row->reason, $m)) {
                $name = trim($m[1]);
            }
            return [[
                'code' => $code,
                'name' => $name,
            ]];
        }

        // 4. Dari DELETE_MASSAL ids
        if (!empty($payload['ids']) && is_array($payload['ids'])) {
            foreach ($payload['ids'] as $id) {
                if (isset($jksMap[$id])) {
                    $tokos[] = [
                        'code' => $jksMap[$id]->customer_code ?? '-',
                        'name' => $jksMap[$id]->customer_name ?? '-',
                    ];
                }
            }
            if (!empty($tokos)) return $tokos;
            
            return [[
                'code' => 'MASSAL (' . count($payload['ids']) . ' ID)',
                'name' => 'Data jadwal terhapus (lihat rincian alasan)',
            ]];
        }

        return [];
    }

    public function parseCleanReason($row, array $payload): string
    {
        if (!empty($payload['update']['reason'])) {
            return trim($payload['update']['reason']);
        }
        if (!empty($payload['reason'])) {
            return trim($payload['reason']);
        }
        if (!empty($row->reason)) {
            if (preg_match('/Alasan:\s*(.+)$/i', $row->reason, $m)) {
                return trim($m[1]);
            }
            return trim($row->reason);
        }
        return '-';
    }

    public function parseScheduleFromRecord($record): array
    {
        if (!$record) {
            return ['hari' => '-', 'minggu' => '-'];
        }

        $days = [];
        foreach ($this->hariMap as $k => $name) {
            if (($record->$k ?? '') === 'Y') {
                $days[] = $name;
            }
        }

        $weeks = [];
        foreach ($this->weekMap as $k => $name) {
            if (($record->$k ?? '') === 'Y') {
                $weeks[] = $name;
            }
        }

        return [
            'hari' => !empty($days) ? implode(', ', $days) : '-',
            'minggu' => !empty($weeks) ? implode(', ', $weeks) : '-',
        ];
    }
}
