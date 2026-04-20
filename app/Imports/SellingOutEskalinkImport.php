<?php

namespace App\Imports;

use App\Models\SellingOutEskalink;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow; 
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class SellingOutEskalinkImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
{
    // Static array to keep track of which (branch + month) combinations have been cleaned up in this import session.
    // Format: ['BRANCH001_2023-11' => true, 'BRANCH002_2023-11' => true]
    protected static $cleanedPeriods = [];

    /**
     * Start reading data from row 3
     */
    public function startRow(): int
    {
        return 3;
    }

    /**
     * Helper to safely parse dates from Excel
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value);
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return new \DateTime(date('Y-m-d', $timestamp));
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
    * Mapping data from Excel to Model using Column Index
    */
    public function model(array $row)
    {
        // 1. Extract Keys for Cleanup: Branch Code (Col 4) and Invoice Date (Col 21)
        $branchCode  = $row[4] ?? null; 
        $invoiceDateRaw = $row[21] ?? null;
        $invoiceDate = $this->parseDate($invoiceDateRaw);

        // 2. Perform Cleanup Logic (Only once per Branch+Month)
        if ($branchCode && $invoiceDate) {
            // Convert to Carbon for easier formatting
            $date = Carbon::instance($invoiceDate);
            $month = $date->format('m');
            $year = $date->format('Y');
            
            // Create a unique key for this combination
            $cleanKey = "{$branchCode}_{$year}-{$month}";

            // If we haven't cleaned this period yet, do it now
            if (!isset(self::$cleanedPeriods[$cleanKey])) {
                SellingOutEskalink::where('branch_code', $branchCode)
                    ->whereMonth('invoice_date', $month)
                    ->whereYear('invoice_date', $year)
                    ->delete();

                // Mark as cleaned so we don't delete again for subsequent rows in this file
                self::$cleanedPeriods[$cleanKey] = true;
            }
        }

        // 3. Return the model for insertion
        return new SellingOutEskalink([
            'region_code'   => $row[0] ?? null,  
            'region_name'   => $row[1] ?? null,  
            'entity_code'   => $row[2] ?? null,  
            'entity_name'   => $row[3] ?? null,  
            'branch_code'   => $row[4] ?? null,  
            'branch_name'   => $row[5] ?? null,  
            'area_code'     => $row[6] ?? null,  
            'area_name'     => $row[7] ?? null,  
            'sales_code'    => $row[8] ?? null,  
            'sales_name'    => $row[9] ?? null,  
            'cust_code_prc' => $row[10] ?? null, 
            'cust_code_dist'=> $row[11] ?? null, 
            'cust_name'     => $row[12] ?? null, 
            'cust_address'  => $row[13] ?? null, 
            'cust_city'     => $row[14] ?? null, 
            'sub_channel'   => $row[15] ?? null, 
            'type_outlet'   => $row[16] ?? null, 
            'ord_no'        => $row[17] ?? null, 
            'ord_date'      => $this->parseDate($row[18] ?? null), 
            'invoice_no'    => $row[19] ?? null, 
            'invoice_type'  => $row[20] ?? null, 
            'invoice_date'  => $this->parseDate($row[21] ?? null), 
            'prd_brand'     => $row[22] ?? null, 
            'product_group_1'=> $row[23] ?? null, 
            'product_group_2'=> $row[24] ?? null, 
            'product_group_3'=> $row[25] ?? null, 
            'prd_code'      => $row[26] ?? null, 
            'prd_name'      => $row[27] ?? null, 
            'qty1_car'      => $row[28] ?? 0,    
            'qty2_pck'      => $row[29] ?? 0,    
            'qty3_pcs'      => $row[30] ?? 0,    
            'qty4_pcs'      => $row[31] ?? 0,    
            'qty5_pcs'      => $row[32] ?? 0,    
            'flag_bonus'    => $row[33] ?? null, 
            'gross_amount'  => $row[34] ?? 0,    
            'line_discount_1'=> $row[35] ?? 0,   
            'line_discount_2'=> $row[36] ?? 0,   
            'line_discount_3'=> $row[37] ?? 0,   
            'line_discount_4'=> $row[38] ?? 0,   
            'line_discount_5'=> $row[39] ?? 0,   
            'line_discount_6'=> $row[40] ?? 0,   
            'line_discount_7'=> $row[41] ?? 0,   
            'line_discount_8'=> $row[42] ?? 0,   
            'total_line_discount'=> $row[43] ?? 0, 
            'dpp'           => $row[44] ?? 0,    
            'tax'           => $row[45] ?? 0,    
            'nett_amount'   => $row[46] ?? 0,    
            'category_item' => $row[47] ?? null, 
            'vtkp'          => $row[48] ?? null, 
            'npd'           => $row[49] ?? null, 
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}