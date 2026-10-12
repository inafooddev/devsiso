<?php

namespace App\Exports;

use App\Livewire\Rwo\PlanKunjungan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlanKunjunganExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = PlanKunjungan::buildBaseQueryFromFilters($this->filters);
        PlanKunjungan::applyQuickFilters($query, $this->filters);

        $query->select(
            'j.tanggal',
            'j.kode_region',
            'j.nama_region',
            'j.kode_area',
            'j.nama_area',
            'j.kode_team',
            'j.nama_team',
            'j.distributor_code',
            'j.distributor_name',
            'j.custno',
            'l.uniq_kd',
            'j.custname',
            'j.addres',
            'p.jenis_reward',
            'skb.customer_code as skb_customer_code',
            'skb.is_approved as skb_is_approved',
            'skb.reason as skb_reason',
            'r.no_hp',
            'r.nama_pemilik_toko',
            'r.latitude',
            'r.longitude',
            'r.foto_toko2 as tampak_depan',
            'r.foto_toko3 as tampak_dalam'
        );

        $query->orderBy('j.tanggal', 'desc')
              ->orderBy('j.nama_region', 'asc')
              ->orderBy('j.nama_area', 'asc')
              ->orderBy('j.nama_team', 'asc');

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Region',
            'Area',
            'Distributor',
            'Nama Team',
            'Cust No (JKS)',
            'Uniq KD',
            'Cust Name',
            'Alamat',
            'Status SKB',
            'Ket. Reject SKB',
            'Status Data',
            'No HP',
            'Pemilik Toko',
            'Maps (Lat, Lon)',
            'Tampak Depan',
            'Tampak Dalam',
        ];
    }

    public function map($row): array
    {
        $noHp = trim((string) ($row->no_hp ?? ''));
        $pemilik = trim((string) ($row->nama_pemilik_toko ?? ''));
        $lat = trim((string) ($row->latitude ?? ''));
        $lng = trim((string) ($row->longitude ?? ''));
        $hasNoHp = $noHp !== '';
        $hasPemilik = $pemilik !== '';
        $hasMaps = $lat !== '' && $lng !== '';
        $hasDepan = trim((string) ($row->tampak_depan ?? '')) !== '';
        $hasDalam = trim((string) ($row->tampak_dalam ?? '')) !== '';

        $filledCount = ($hasNoHp ? 1 : 0)
                     + ($hasPemilik ? 1 : 0)
                     + ($hasMaps ? 1 : 0)
                     + ($hasDepan ? 1 : 0)
                     + ($hasDalam ? 1 : 0);
        $isLengkap = ($filledCount === 5);

        $skbReason = '-';
        if (($row->jenis_reward ?? 'CB') === 'BB') {
            $statusSkb = 'Non-SKB (BB)';
        } elseif (!empty($row->skb_customer_code)) {
            if ($row->skb_is_approved === true) {
                $statusSkb = 'Approved';
            } elseif ($row->skb_is_approved === false) {
                $statusSkb = 'Rejected';
                $skbReason = trim((string) ($row->skb_reason ?? '')) ?: '-';
            } else {
                $statusSkb = 'Pending';
            }
        } else {
            $statusSkb = 'Belum SKB';
        }

        return [
            $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-',
            trim(($row->kode_region ? $row->kode_region . ' - ' : '') . ($row->nama_region ?? '')),
            trim(($row->kode_area ? $row->kode_area . ' - ' : '') . ($row->nama_area ?? '')),
            trim(($row->distributor_code ? $row->distributor_code . ' - ' : '') . ($row->distributor_name ?? '')),
            $row->nama_team,
            $row->custno,
            $row->uniq_kd ?: '-',
            $row->custname,
            $row->addres ?: '-',
            $statusSkb,
            $skbReason,
            $isLengkap ? 'Lengkap (5/5)' : "Belum ({$filledCount}/5)",
            $hasNoHp ? $noHp : 'Belum',
            $hasPemilik ? $pemilik : 'Belum',
            $hasMaps ? "{$lat}, {$lng}" : 'Belum',
            $hasDepan ? 'Sudah' : 'Belum',
            $hasDalam ? 'Sudah' : 'Belum',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}


