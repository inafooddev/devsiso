<?php

namespace App\Exports;

use App\Models\DetailSellOut;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class DetailSellOutExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = DetailSellOut::query();

        // Terapkan filter
        if (!empty($this->filters['regionFilter'])) {
            $query->where('region_code', $this->filters['regionFilter']);
        }
        if (!empty($this->filters['areaFilter'])) {
            $query->where('entity_code', $this->filters['areaFilter']);
        }
        if (!empty($this->filters['distributorFilter'])) {
            // Asumsi entity_code adalah distributor_code di tabel detail_sell_out
            $query->whereIn('branch_code', $this->filters['distributorFilter']);
        }

        // Gunakan range tanggal (efisien untuk index/partisi)
        if (!empty($this->filters['monthFilter']) && !empty($this->filters['yearFilter'])) {
            $startDate = Carbon::create($this->filters['yearFilter'], $this->filters['monthFilter'], 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            $query->whereBetween('invoice_date', [$startDate, $endDate]);
        }

        return $query->orderBy('invoice_date');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Mengembalikan semua kolom dari tabel detail_sell_out
        return [
            'Region Code',
            'Region Name',
            'Entity Code',
            'Entity Name',
            'Branch Code',
            'Branch Name',
            'Area Code',
            'Area Name',
            'Sales Code',
            'Sales Name',
            'Cust Code Prc',
            'Cust Code Dist',
            'Cust Name',
            'Cust Address',
            'Cust City',
            'Sub Channel',
            'Type Outlet',
            'Order No',
            'Order Date',
            'Invoice No',
            'Invoice Type',
            'Invoice Date',
            'Prd Brand',
            'Product Group 1',
            'Product Group 2',
            'Product Group 3',
            'Prd Code',
            'Prd Name',
            'Qty 1 (Car)',
            'Qty 2 (Pck)',
            'Qty 3 (Pcs)',
            'Qty 4 (Pcs)',
            'Qty 5 (Pcs)',
            'Flag Bonus',
            'Gross Amount',
            'Disc 1',
            'Disc 2',
            'Disc 3',
            'Disc 4',
            'Disc 5',
            'Disc 6',
            'Disc 7',
            'Disc 8',
            'Total Line Disc',
            'DPP',
            'Tax',
            'Nett Amount',
            'Category Item',
            'VTKP',
            'NPD'
        ];
    }

    /**
     * @param DetailSellOut $row
     * @return array
     */
    public function map($row): array
    {
        // [PERBAIKAN] Logika yang lebih kuat untuk flag_bonus
        // Ambil nilai mentah dari database
        $originalFlag = $row->getOriginal('flag_bonus');

        // Normalisasi nilai jika itu string
        if (is_string($originalFlag)) {
            $originalFlag = strtoupper(trim($originalFlag));
        }

        // [PERBAIKAN] Cek terhadap semua kemungkinan nilai 'TRUE' ('Y', 1, true, 'TRUE')
        $isBonus = ($originalFlag === 'Y' || $originalFlag === 1 || $originalFlag === true || $originalFlag === 'TRUE');

        return [
            $row->region_code,
            $row->region_name,
            $row->entity_code,
            $row->entity_name,
            $row->branch_code,
            $row->branch_name,
            $row->area_code,
            $row->area_name,
            $row->sales_code,
            $row->sales_name,
            $row->cust_code_prc,
            $row->cust_code_dist,
            $row->cust_name,
            $row->cust_address,
            $row->cust_city,
            $row->sub_channel,
            $row->type_outlet,
            $row->ord_no,
            $row->ord_date,
            $row->invoice_no,
            $row->invoice_type,
            $row->invoice_date,
            $row->prd_brand,
            $row->product_group_1,
            $row->product_group_2,
            $row->product_group_3,
            $row->prd_code,
            $row->prd_name,
            $row->qty1_car,
            $row->qty2_pck,
            $row->qty3_pcs,
            $row->qty4_pcs,
            $row->qty5_pcs,

            // Gunakan hasil pengecekan yang sudah kuat
            $row->flag_bonus,

            $row->gross_amount,
            $row->line_discount_1,
            $row->line_discount_2,
            $row->line_discount_3,
            $row->line_discount_4,
            $row->line_discount_5,
            $row->line_discount_6,
            $row->line_discount_7,
            $row->line_discount_8,
            $row->total_line_discount,
            $row->dpp,
            $row->tax,
            $row->nett_amount,
            $row->category_item,
            $row->vtkp,
            $row->npd,
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'T' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'W' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'AD' => NumberFormat::FORMAT_NUMBER,
            'AE' => NumberFormat::FORMAT_NUMBER,
            'AF' => NumberFormat::FORMAT_NUMBER,
            'AG' => NumberFormat::FORMAT_NUMBER,
            'AH' => NumberFormat::FORMAT_NUMBER,
            'AJ' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AK' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AL' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AM' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AN' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AO' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AP' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AQ' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AR' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AS' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AT' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AU' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AV' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
