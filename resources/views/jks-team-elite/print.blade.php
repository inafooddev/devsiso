<!DOCTYPE html>
<html lang="id" data-theme="light" class="bg-white text-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Jadwal Kunjungan Sales - Print</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            color-scheme: light !important;
        }
        @page {
            size: A4 landscape;
            margin-top: 10mm;
            margin-left: 10mm;
            margin-right: 10mm;
            margin-bottom: 20mm; /* Beri ruang ekstra di bawah khusus untuk nomor halaman */
        }
        @media print {
            html, body { 
                background-color: #ffffff !important; 
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important; 
                padding: 0 !important; 
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print { display: none !important; }
            .print-container {
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                background-color: #ffffff !important;
                background: #ffffff !important;
            }
            .team-container {
                page-break-after: always;
                background-color: #ffffff !important;
                background: #ffffff !important;
            }
            .team-container:last-child {
                page-break-after: auto;
            }
            tr {
                page-break-inside: auto;
            }
            thead {
                display: table-header-group;
            }
        }
        html, body {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #000000 !important;
            padding: 0;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        body {
            padding: 2rem;
            background-color: #ffffff !important;
        }
        .print-container {
            max-width: 297mm;
            margin: 0 auto;
            background-color: #ffffff !important;
            background: #ffffff !important;
            padding: 15mm;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .team-container {
            background-color: #ffffff !important;
            background: #ffffff !important;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 4px 8px;
        }
        .info-table td.info-label {
            font-weight: bold;
            width: 15%;
        }
        .info-table td.info-value {
            width: 35%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 4px;
        }
        .data-table th {
            background-color: #5B9BD5 !important;
            color: white !important;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
        }
        .data-table td {
            vertical-align: middle;
        }
        .avoid-break {
            /* Fix Chrome black box bug by using inline-block instead of page-break-inside: avoid */
            display: inline-block;
            width: 100%;
            background-color: white !important;
            break-inside: avoid;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 20px;
            background-color: white !important;
            color: black !important;
        }
        .footer-table th, .footer-table td {
            border: 1px solid #000;
            padding: 4px;
            background-color: white !important;
            color: black !important;
        }
        .footer-table td.relative {
            position: relative;
        }
        .stamp-approve {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-10deg);
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0.8;
            pointer-events: none;
            color: #000;
        }
        body.status-approve .stamp-approve {
            display: flex;
        }
        .footer-table th {
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div class="mb-6 text-center no-print">
        <button onclick="openPrintModal()" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 transition-colors">
            🖨️ Print Dokumen
        </button>
        <button onclick="window.close()" class="px-6 py-2 bg-gray-500 text-white font-bold rounded-lg shadow hover:bg-gray-600 transition-colors ml-3">
            Tutup Tab
        </button>
    </div>

    <!-- Print Modal -->
    <div id="printModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center hidden no-print">
        <div class="bg-white rounded-lg shadow-xl p-6 w-96 text-left">
            <h3 class="text-xl font-bold mb-2 text-gray-800">Pilih Status Cetak</h3>
            <p class="text-sm text-gray-600 mb-6">Apakah dokumen ini sudah disetujui (Approve) atau masih kosong?</p>
            <div class="flex flex-col space-y-3">
                <button onclick="executePrint('approve')" class="w-full px-4 py-3 bg-green-600 text-white font-bold rounded-lg shadow hover:bg-green-700 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    Cetak dengan status APPROVE
                </button>
                <button onclick="executePrint('kosong')" class="w-full px-4 py-3 bg-gray-500 text-white font-bold rounded-lg shadow hover:bg-gray-600">
                    Cetak Kosong (Tanpa Stamp)
                </button>
                <button onclick="closePrintModal()" class="w-full px-4 py-2 mt-2 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <div class="print-container">
        @forelse($groupedRecords as $teamCode => $teamRecords)
            @php
                $firstRecord = $teamRecords->first();
            @endphp
            <div class="team-container">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 15px;">
                    <div>
                        <h1 style="font-size: 18px; font-weight: bold; margin: 0; padding: 0;">Jadwal Kunjungan Sales</h1>
                        <h2 style="font-size: 12px; font-weight: bold; color: #6b7280; margin: 2px 0 0 0; padding: 0;">PT. INTIM HARMONIS FOODS</h2>
                    </div>
                    @if($loop->count > 1)
                        <div style="font-size: 11px; font-weight: bold; color: #374151;">
                            Tim {{ $loop->iteration }} dari {{ $loop->count }}
                        </div>
                    @endif
                </div>

                <table class="info-table">
                    <tbody>
                        <tr>
                            <td class="info-label">Region</td>
                            <td class="info-value">{{ $firstRecord->nama_region ?? '-' }}</td>
                            <td class="info-label">Nama</td>
                            <td class="info-value">{{ $firstRecord->nama_team ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Area</td>
                            <td class="info-value">{{ $firstRecord->nama_area ?? '-' }}</td>
                            <td class="info-label">Periode</td>
                            <td class="info-value">{{ \Carbon\Carbon::parse($filterStartDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($filterEndDate)->format('d/m/Y') }}</td>
                        </tr>
                    </tbody>
                </table>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 8%;">Hari</th>
                            <th rowspan="2" style="width: 8%;">tanggal</th>
                            <th rowspan="2" style="width: 20%;">Nama Distributor</th>
                            <th colspan="2">Tujuan</th>
                            <th rowspan="2" style="width: 8%;">Pilar</th>
                            <th rowspan="2" style="width: 10%;">Target</th>
                            <th rowspan="2" style="width: 12%;">Keterangan</th>
                        </tr>
                        <tr>
                            <th style="width: 10%;">Kode Outlet</th>
                            <th style="width: 24%;">Nama Outlet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $recordsByDate = collect($teamRecords)->groupBy('tanggal');
                        @endphp
                        @foreach($recordsByDate as $tanggal => $dailyRecords)
                            @foreach($dailyRecords as $record)
                                <tr>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($record->tanggal)->locale('id')->isoFormat('dddd') }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($record->tanggal)->format('d-M-y') }}</td>
                                    <td>{{ $record->distributor_name }}</td>
                                    <td class="text-center">{{ $record->custno }}</td>
                                    <td>{{ $record->custname }}</td>
                                    <td class="text-center">{{ $record->pilar ?? '-' }}</td>
                                    <td class="text-center">{{ $record->target ? number_format($record->target, 0, ',', '.') : '-' }}</td>
                                    <td></td> <!-- Keterangan kosong -->
                                </tr>
                            @endforeach
                            {{-- Subtotal Row --}}
                            @php
                                $rwo = $dailyRecords->where('pilar', '1. RWO')->count();
                                $pnr = $dailyRecords->where('pilar', '2. PNR')->count();
                                $ngvo = $dailyRecords->where('pilar', '3. NGVO')->count();
                                $gro = $dailyRecords->where('pilar', '4. GRO')->count();
                                $summary = [];
                                if ($rwo > 0) $summary[] = "RWO: $rwo";
                                if ($pnr > 0) $summary[] = "PNR: $pnr";
                                if ($ngvo > 0) $summary[] = "NGVO: $ngvo";
                                if ($gro > 0) $summary[] = "GRO: $gro";
                                
                                $totalTarget = $dailyRecords->sum(function($item) {
                                    return is_numeric($item->target) ? $item->target : 0;
                                });
                            @endphp
                            <tr style="background-color: #f3f4f6; font-weight: bold;">
                                <td colspan="3" class="text-right" style="padding-right: 15px;">
                                    Subtotal {{ \Carbon\Carbon::parse($tanggal)->format('d-M-y') }} (Total: {{ count($dailyRecords) }} Toko) :
                                </td>
                                <td colspan="3" class="text-center" style="font-size: 11px;">
                                    {{ implode(', ', $summary) }}
                                </td>
                                <td class="text-center">
                                    {{ $totalTarget > 0 ? number_format($totalTarget, 0, ',', '.') : '-' }}
                                </td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="avoid-break">
                    <table class="footer-table">
                        <tr>
                            <th style="width: 33.33%;">Dibuat</th>
                            <th style="width: 33.33%;">Diverifikasi</th>
                            <th style="width: 33.33%;">Disetujui</th>
                        </tr>
                        <tr>
                            <td class="text-center" style="font-weight: bold;">Sales</td>
                            <td class="text-center" style="font-weight: bold;">Sales Planner Manager</td>
                            <td class="text-center" style="font-weight: bold;">General Manager</td>
                        </tr>
                        <tr>
                            <td style="height: 70px;" class="relative">
                                <div class="stamp-approve">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#000" stroke-width="6"/>
                                        <circle cx="50" cy="50" r="35" fill="none" stroke="#000" stroke-width="2"/>
                                        <path d="M28 52 L43 65 L72 32" fill="none" stroke="#000" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div style="font-size: 14px; font-weight: 900; letter-spacing: 1.5px; margin-top: 4px; color: #000;">APPROVE</div>
                                </div>
                            </td>
                            <td style="height: 70px;" class="relative">
                                <div class="stamp-approve">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#000" stroke-width="6"/>
                                        <circle cx="50" cy="50" r="35" fill="none" stroke="#000" stroke-width="2"/>
                                        <path d="M28 52 L43 65 L72 32" fill="none" stroke="#000" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div style="font-size: 14px; font-weight: 900; letter-spacing: 1.5px; margin-top: 4px; color: #000;">APPROVE</div>
                                </div>
                            </td>
                            <td style="height: 70px;" class="relative">
                                <div class="stamp-approve">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#000" stroke-width="6"/>
                                        <circle cx="50" cy="50" r="35" fill="none" stroke="#000" stroke-width="2"/>
                                        <path d="M28 52 L43 65 L72 32" fill="none" stroke="#000" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div style="font-size: 14px; font-weight: 900; letter-spacing: 1.5px; margin-top: 4px; color: #000;">APPROVE</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-left">Nama : {{ strtoupper($firstRecord->nama_team ?? '-') }}</td>
                            <td class="text-left">Nama : TAUFIK HIDAYAT ROHMAN</td>
                            <td class="text-left">Nama : LUTFIANDRY</td>
                        </tr>
                        <tr>
                            <td class="text-left">Tgl :</td>
                            <td class="text-left">Tgl :</td>
                            <td class="text-left">Tgl :</td>
                        </tr>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center py-10">
                <p>Tidak ada data ditemukan untuk kriteria filter ini.</p>
            </div>
        @endforelse
    </div>

    <script>
        // Auto open print modal when page is fully loaded
        window.addEventListener('load', function() {
            setTimeout(() => {
                openPrintModal();
            }, 500);
        });
        
        function openPrintModal() {
            document.getElementById('printModal').classList.remove('hidden');
        }
        
        function closePrintModal() {
            document.getElementById('printModal').classList.add('hidden');
        }
        
        function executePrint(status) {
            if (status === 'approve') {
                document.body.classList.add('status-approve');
            } else {
                document.body.classList.remove('status-approve');
            }
            closePrintModal();
            // Beri jeda agar DOM ter-update sebelum dialog print muncul
            setTimeout(() => {
                window.print();
            }, 100);
        }
    </script>
</body>
</html>
