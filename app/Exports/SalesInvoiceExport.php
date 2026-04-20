<?php

namespace App\Exports;

use App\Models\SalesInvoiceDistributor;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class SalesInvoiceExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithCustomValueBinder
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */

    public function bindValue(Cell $cell, $value)
    {
        // Kolom ke-18 (R) adalah "Product Name"
        if ($cell->getColumn() === 'R') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        // Kolom lain biarkan seperti biasa
        return parent::bindValue($cell, $value);
    }


    public function query()
    {
        $query = SalesInvoiceDistributor::query()
            ->join('master_distributors', 'sales_invoice_distributor.distributor_code', '=', 'master_distributors.distributor_code')
            ->select(
                'master_distributors.region_name',
                'master_distributors.area_name',
                'master_distributors.supervisor_name',
                'master_distributors.branch_name',
                // [PERBAIKAN] Beri nama alias untuk menghindari konflik kolom
                'master_distributors.distributor_name as master_distributor_name',
                'sales_invoice_distributor.*'
            );

        // Terapkan filter
        if (!empty($this->filters['regionFilter'])) {
            $query->where('master_distributors.region_code', $this->filters['regionFilter']);
        }
        if (!empty($this->filters['areaFilter'])) {
            $query->where('master_distributors.area_code', $this->filters['areaFilter']);
        }
        if (!empty($this->filters['distributorFilter'])) {
            $query->where('sales_invoice_distributor.distributor_code', $this->filters['distributorFilter']);
        }
        if (!empty($this->filters['monthFilter'])) {
            $query->whereMonth('sales_invoice_distributor.invoice_date', $this->filters['monthFilter']);
        }
        if (!empty($this->filters['yearFilter'])) {
            $query->whereYear('sales_invoice_distributor.invoice_date', $this->filters['yearFilter']);
        }

        return $query->orderBy('sales_invoice_distributor.invoice_date');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Region Name',
            'Area Name',
            'Supervisor Name',
            'Branch Name',
            'Distributor Code',
            'Distributor Name',
            'Invoice Type',
            'Invoice No',
            'Invoice Date',
            'Order No',
            'Order Date',
            'Salesman Code',
            'Salesman Name',
            'Customer Code',
            'Customer Name',
            'Address',
            'Product Code',
            'Product Name',
            'Carton Qty',
            'Pack Qty',
            'Pcs Qty',
            'Quantity',
            'Unit',
            'Bonus',
            'Is Bonus',
            'Unit Price',
            'Gross Amount',
            'Discount 1',
            'Discount 2',
            'Discount 3',
            'Discount 4',
            'Discount 5',
            'Discount 6',
            'Discount 7',
            'Discount 8',
            'Total Discount',
            'DPP',
            'Tax',
            'Net Amount',
        ];
    }

    /**
     * @param mixed $invoice
     * @return array
     */
    public function map($invoice): array
    {
        return [
            $invoice->region_name,
            $invoice->area_name,
            $invoice->supervisor_name,
            $invoice->branch_name,
            $invoice->distributor_code,
            // [PERBAIKAN] Gunakan nama alias yang sudah didefinisikan di query
            $invoice->master_distributor_name,
            $invoice->invoice_type,
            $invoice->invoice_no,
            Date::dateTimeToExcel(\Carbon\Carbon::parse($invoice->invoice_date)),
            $invoice->order_no,
            $invoice->order_date ? Date::dateTimeToExcel(\Carbon\Carbon::parse($invoice->order_date)) : null,
            $invoice->salesman_code,
            $invoice->salesman_name,
            $invoice->customer_code,
            $invoice->customer_name,
            $invoice->address,
            $invoice->product_code,
            $invoice->product_name,
            $invoice->carton_qty,
            $invoice->pack_qty,
            $invoice->pcs_qty,
            $invoice->quantity,
            $invoice->unit,
            $invoice->bonus,
            $invoice->is_bonus ? 'Ya' : 'Tidak',
            $invoice->unit_price,
            $invoice->gross_amount,
            $invoice->discount1,
            $invoice->discount2,
            $invoice->discount3,
            $invoice->discount4,
            $invoice->discount5,
            $invoice->discount6,
            $invoice->discount7,
            $invoice->discount8,
            $invoice->total_discount,
            $invoice->dpp,
            $invoice->tax,
            $invoice->net_amount,
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'K' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'S' => '#,##0',
            'T' => '#,##0',
            'U' => '#,##0',
            'V' => '#,##0',
            'X' => '#,##0',
            'Z' => '#,##0',
            'AA' => '#,##0',
            'AB' => '#,##0',
            'AC' => '#,##0',
            'AD' => '#,##0',
            'AE' => '#,##0',
            'AF' => '#,##0',
            'AG' => '#,##0',
            'AH' => '#,##0',
            'AI' => '#,##0',
            'AJ' => '#,##0',
            'AK' => '#,##0',
            'AL' => '#,##0',
            'AM' => '#,##0',
        ];
    }
}
