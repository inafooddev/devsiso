<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\ReportReaktivasiTokoExport;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Writer\XLSX\Options as XlsxOptions;
use Carbon\Carbon;

class ReaktivasiTokoExportController extends Controller
{
    /**
     * Export Report Reaktivasi Toko (Detail)
     * Supports high-performance streaming for XLSX and CSV.
     */
    public function export(Request $request)
    {
        // Allow adequate execution time for high volume data (All Region = ~68k rows)
        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '512M');

        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'region' => trim((string) $request->query('region', '')),
            'area' => trim((string) $request->query('area', '')),
            'supervisor' => trim((string) $request->query('supervisor', '')),
            'distributor' => trim((string) $request->query('distributor', '')),
            'status' => trim((string) $request->query('status', '')),
            'type' => trim((string) $request->query('type', '')),
            'bulan' => $request->query('bulan') ? str_pad((int) $request->query('bulan'), 2, '0', STR_PAD_LEFT) : date('m'),
            'tahun' => (int) ($request->query('tahun') ?: date('Y')),
        ];

        $format = strtolower($request->query('format', 'xlsx'));
        $timestamp = date('Ymd_His');
        $export = new ReportReaktivasiTokoExport($filters, $user);

        // Activity log
        if (class_exists(\App\Helpers\ActivityLogger::class)) {
            $regionLabel = !empty($filters['region']) ? $filters['region'] : 'ALL REGION';
            \App\Helpers\ActivityLogger::log(
                'Export Report Reaktivasi Toko',
                "Mengekspor data detail reaktivasi toko (Format: {$format}, Periode: {$filters['bulan']}/{$filters['tahun']}, Region: {$regionLabel})"
            );
        }

        if ($format === 'csv') {
            return $this->exportCsvStream($export, "Report_Reaktivasi_Toko_{$timestamp}.csv");
        }

        return $this->exportXlsxStream($export, "Report_Reaktivasi_Toko_{$timestamp}.xlsx");
    }

    /**
     * Stream native XLSX using FastExcel / OpenSpout with optimized styling and column widths
     */
    protected function exportXlsxStream(ReportReaktivasiTokoExport $export, string $fileName)
    {
        $generator = function () use ($export) {
            foreach ($export->query()->cursor() as $row) {
                yield $export->mapAssoc($row);
            }
        };

        // Header Style: Dark Gray Slate, Bold, White Font, Size 11
        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('4A5568')
            ->setFontSize(11);

        $fastExcel = new FastExcel($generator());
        $fastExcel->headerStyle($headerStyle);

        // Configure static column widths to prevent text clipping and '###' number overflow
        $fastExcel->configureOptionsUsing(function ($options) {
            if ($options instanceof XlsxOptions) {
                // Col 1: No
                $options->setColumnWidth(7, 1);
                // Col 2-5: Region, Area, Distributor, Supervisor
                $options->setColumnWidth(16, 2);
                $options->setColumnWidth(18, 3);
                $options->setColumnWidth(28, 4);
                $options->setColumnWidth(22, 5);
                // Col 6-7: Uniq KD, Cust No
                $options->setColumnWidth(14, 6);
                $options->setColumnWidth(14, 7);
                // Col 8-9: Nama Toko, Alamat
                $options->setColumnWidth(25, 8);
                $options->setColumnWidth(35, 9);
                // Col 10-13: Type, Class, Status, Last Transaksi
                $options->setColumnWidth(10, 10);
                $options->setColumnWidth(12, 11);
                $options->setColumnWidth(14, 12);
                $options->setColumnWidth(15, 13);
                // Col 14-16: Total Transaksi, Avg 6 Bulan, Pencapaian Bln Ini
                $options->setColumnWidth(18, 14);
                $options->setColumnWidth(16, 15);
                $options->setColumnWidth(18, 16);
                // Col 17-28: Bulan Jan - Des
                $options->setColumnWidthForRange(14, 17, 28);
            }
        });

        return $fastExcel->download($fileName);
    }

    /**
     * Stream CSV with UTF-8 BOM directly to php://output
     */
    protected function exportCsvStream(ReportReaktivasiTokoExport $export, string $fileName): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($export) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM so Excel opens it with proper encoding
            fputs($handle, "\xEF\xBB\xBF");

            // Write Headings
            fputcsv($handle, $export->headings(), ';');

            // Write Rows via Cursor Streaming
            foreach ($export->query()->cursor() as $row) {
                fputcsv($handle, $export->map($row), ';');
            }

            fclose($handle);
        }, $fileName, $headers);
    }
}
