<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QcEskalinkExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $monthFilter;
    protected $search;
    protected $regionFilter;

    public function __construct($monthFilter, $search, $regionFilter)
    {
        $this->monthFilter = $monthFilter;
        $this->search = $search;
        $this->regionFilter = $regionFilter;
    }

    public function collection()
    {
        $startOfMonth = \Carbon\Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');
        $endOfMonth = \Carbon\Carbon::parse($this->monthFilter)->endOfMonth()->format('Y-m-d');

        // Subquery for CORE
        $coreQuery = DB::table('nominal_qc_dist')
            ->select('distributor_code', 'qty', 'discount_4', 'discount_8', 'neto', 'nominal_surat', 'file_surat')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);

        // Subquery for ESKA
        $eskaQuery = DB::table('selling_out_eskalink')
            ->select(
                'branch_code as distributor_code',
                DB::raw('COUNT(*) as total_row'),
                DB::raw('SUM(qty3_pcs) as qty'),
                DB::raw('SUM(gross_amount) as gross_amount'),
                DB::raw('SUM(line_discount_4) as discount_4'),
                DB::raw('SUM(line_discount_8) as discount_8'),
                DB::raw('SUM(dpp) as dpp'),
                DB::raw('SUM(tax) as tax'),
                DB::raw('SUM(nett_amount) as neto')
            )
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->groupBy('branch_code');

        // Main Query
        $query = DB::table('distributor_implementasi_eskalink as d')
            ->leftJoin('master_distributors as md', 'd.distributor_code', '=', 'md.distributor_code')
            ->leftJoinSub($coreQuery, 'core', 'd.eskalink_code', '=', 'core.distributor_code')
            ->leftJoinSub($eskaQuery, 'eska', 'd.eskalink_code', '=', 'eska.distributor_code')
            ->where('d.implementasi', 'Y')
            ->where('md.is_active', true)
            ->where('md.region_code', '<>', 'HOINA')
            ->select(
                'md.region_name',
                'md.area_name',
                'd.eskalink_code as distributor_code',
                'md.distributor_name',
                
                // ROW
                DB::raw('COALESCE(eska.total_row, 0) as row_core'),
                DB::raw('COALESCE(eska.total_row, 0) as row_eska'),
                DB::raw('0 as row_selisih'),
                
                // QTY
                DB::raw('COALESCE(core.qty, 0) as qty_core'),
                DB::raw('COALESCE(eska.qty, 0) as qty_eska'),
                DB::raw('COALESCE(core.qty, 0) - COALESCE(eska.qty, 0) as qty_selisih'),
                
                // GROSS AMOUNT
                DB::raw('COALESCE(eska.gross_amount, 0) as gross_core'),
                DB::raw('COALESCE(eska.gross_amount, 0) as gross_eska'),
                DB::raw('0 as gross_selisih'),
                
                // DISCOUNT 4
                DB::raw('COALESCE(core.discount_4, 0) as disc4_core'),
                DB::raw('COALESCE(eska.discount_4, 0) as disc4_eska'),
                DB::raw('COALESCE(core.discount_4, 0) - COALESCE(eska.discount_4, 0) as disc4_selisih'),
                
                // DISCOUNT 8
                DB::raw('COALESCE(core.discount_8, 0) as disc8_core'),
                DB::raw('COALESCE(eska.discount_8, 0) as disc8_eska'),
                DB::raw('COALESCE(core.discount_8, 0) - COALESCE(eska.discount_8, 0) as disc8_selisih'),
                
                // DPP
                DB::raw('COALESCE(eska.dpp, 0) as dpp_core'),
                DB::raw('COALESCE(eska.dpp, 0) as dpp_eska'),
                DB::raw('0 as dpp_selisih'),
                
                // TAX
                DB::raw('COALESCE(eska.tax, 0) as tax_core'),
                DB::raw('COALESCE(eska.tax, 0) as tax_eska'),
                DB::raw('0 as tax_selisih'),
                
                // NETO
                DB::raw('COALESCE(core.neto, 0) as neto_core'),
                DB::raw('COALESCE(eska.neto, 0) as neto_eska'),
                DB::raw('COALESCE(core.neto, 0) - COALESCE(eska.neto, 0) as neto_selisih'),
                
                // SURAT
                DB::raw('COALESCE(core.nominal_surat, 0) as surat_nominal'),
                DB::raw('COALESCE(core.nominal_surat, 0) - COALESCE(core.neto, 0) as surat_selisih')
            );

        if ($this->search) {
            $query->where(function($q) {
                $q->where('md.distributor_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('d.eskalink_code', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->regionFilter) {
            $query->where('md.region_code', $this->regionFilter);
        }

        return $query->orderBy('md.region_name')
              ->orderBy('md.area_name')
              ->orderBy('md.distributor_name')
              ->get();
    }

    public function headings(): array
    {
        return [
            ['REGION', 'AREA', 'DIST CODE', 'DISTRIBUTOR NAME', 'ROW', '', '', 'QTY', '', '', 'GROSS AMOUNT', '', '', 'LINE DISCOUNT 4', '', '', 'LINE DISCOUNT 8', '', '', 'DPP', '', '', 'TAX', '', '', 'NETO', '', '', 'SURAT', ''],
            ['', '', '', '', 'CORE (INPUT)', 'ESKA (SLO)', 'SELISIH', 'CORE (INPUT)', 'ESKA (SLO)', 'SELISIH', 'CORE (INPUT)', 'ESKA (SLO)', 'SELISIH', 'CORE (INPUT)', 'ESKA (SLO)', 'SELISIH', 'CORE (INPUT)', 'ESKA (SLO)', 'SELISIH', 'CORE (INPUT)', 'ESKA (SLO)', 'SELISIH', 'CORE (INPUT)', 'ESKA (SLO)', 'SELISIH', 'CORE (INPUT)', 'ESKA (SLO)', 'SELISIH', 'NOMINAL', 'SELISIH'],
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_name,
            $row->area_name,
            $row->distributor_code,
            $row->distributor_name,
            
            $row->row_core,
            $row->row_eska,
            $row->row_selisih,
            
            $row->qty_core,
            $row->qty_eska,
            $row->qty_selisih,
            
            $row->gross_core,
            $row->gross_eska,
            $row->gross_selisih,
            
            $row->disc4_core,
            $row->disc4_eska,
            $row->disc4_selisih,
            
            $row->disc8_core,
            $row->disc8_eska,
            $row->disc8_selisih,
            
            $row->dpp_core,
            $row->dpp_eska,
            $row->dpp_selisih,
            
            $row->tax_core,
            $row->tax_eska,
            $row->tax_selisih,
            
            $row->neto_core,
            $row->neto_eska,
            $row->neto_selisih,
            
            $row->surat_nominal,
            $row->surat_selisih,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:D2');
        $sheet->mergeCells('E1:G1');
        $sheet->mergeCells('H1:J1');
        $sheet->mergeCells('K1:M1');
        $sheet->mergeCells('N1:P1');
        $sheet->mergeCells('Q1:S1');
        $sheet->mergeCells('T1:V1');
        $sheet->mergeCells('W1:Y1');
        $sheet->mergeCells('Z1:AB1');
        $sheet->mergeCells('AC1:AD1');

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A1:AD2')->applyFromArray($styleArray);

        // Auto size columns
        foreach(range('A','D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        return [];
    }
}
