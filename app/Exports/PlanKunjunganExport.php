<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlanKunjunganExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DB::table('jks_team_elite as j')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('l.distributor_code', '=', 'j.distributor_code')
                     ->on('l.customer_code_prc', '=', 'j.custno');
            })
            ->leftJoin('reward_outlet as r', 'r.eskalink_code', '=', 'j.custno')
            ->where('l.pilar', '1. RWO');

        if (!empty($this->filters['dateStart']) && !empty($this->filters['dateEnd'])) {
            $query->whereBetween('j.tanggal', [$this->filters['dateStart'], $this->filters['dateEnd']]);
        } elseif (!empty($this->filters['dateStart'])) {
            $query->where('j.tanggal', '>=', $this->filters['dateStart']);
        } elseif (!empty($this->filters['dateEnd'])) {
            $query->where('j.tanggal', '<=', $this->filters['dateEnd']);
        }

        if (!empty($this->filters['selectedRegions'])) {
            $query->whereIn('j.nama_region', $this->filters['selectedRegions']);
        }
        if (!empty($this->filters['selectedAreas'])) {
            $query->whereIn('j.nama_area', $this->filters['selectedAreas']);
        }
        if (!empty($this->filters['selectedTeams'])) {
            $query->whereIn('j.nama_team', $this->filters['selectedTeams']);
        }

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
            'j.custname',
            'j.addres',
            'r.no_hp',
            'r.nama_pemilik_toko',
            'r.nik_ktp',
            'r.nama_ktp',
            'r.foto_ktp',
            'r.no_rekening',
            'r.nama_pemilik_norek',
            'r.latitude',
            'r.longitude',
            'r.foto_toko2 as tampak_depan',
            'r.foto_toko3 as tampak_dalam'
        );
        
        $query->orderBy('j.tanggal', 'desc');

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Region',
            'Area',
            'Nama Team',
            'Cust No',
            'Cust Name',
            'Alamat',
            'Status Lengkap',
            'No HP',
            'Pemilik Toko',
            'NIK KTP',
            'Nama KTP',
            'Foto KTP',
            'No Rekening',
            'Pemilik Rekening',
            'Latitude',
            'Longitude',
            'Tampak Depan',
            'Tampak Dalam'
        ];
    }

    public function map($row): array
    {
        $isLengkap = !empty($row->no_hp) && 
                     !empty($row->nama_pemilik_toko) && 
                     !empty($row->nik_ktp) && 
                     !empty($row->nama_ktp) && 
                     !empty($row->foto_ktp) && 
                     !empty($row->no_rekening) && 
                     !empty($row->nama_pemilik_norek) && 
                     !empty($row->latitude) && 
                     !empty($row->longitude) && 
                     !empty($row->tampak_depan) && 
                     !empty($row->tampak_dalam);

        return [
            $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-',
            $row->kode_region . ' - ' . $row->nama_region,
            $row->kode_area . ' - ' . $row->nama_area,
            $row->nama_team,
            $row->custno,
            $row->custname,
            $row->addres ?: '-',
            $isLengkap ? 'Lengkap' : 'Belum',
            !empty($row->no_hp) ? 'Sudah' : 'Belum',
            !empty($row->nama_pemilik_toko) ? 'Sudah' : 'Belum',
            !empty($row->nik_ktp) ? 'Sudah' : 'Belum',
            !empty($row->nama_ktp) ? 'Sudah' : 'Belum',
            !empty($row->foto_ktp) ? 'Sudah' : 'Belum',
            !empty($row->no_rekening) ? 'Sudah' : 'Belum',
            !empty($row->nama_pemilik_norek) ? 'Sudah' : 'Belum',
            !empty($row->latitude) ? 'Sudah' : 'Belum',
            !empty($row->longitude) ? 'Sudah' : 'Belum',
            !empty($row->tampak_depan) ? 'Sudah' : 'Belum',
            !empty($row->tampak_dalam) ? 'Sudah' : 'Belum'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
