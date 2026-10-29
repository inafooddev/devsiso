<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RptVisitAnHController extends Controller
{
    /**
     * Whitelist kolom yang diizinkan untuk di-select dan di-sort agar aman dari SQL Injection.
     */
    protected array $allowedColumns = [
        'ID', 'RID', 'RNAME', 'EID', 'ENAME', 'BID', 'BNAME', 'MUID', 'MUNAME',
        'CUSTNO', 'CUSTNAME', 'CUSTADD1', 'CUSTADD2', 'CCITY', 'CCONTACT', 'CPHONE1',
        'CFAXNO', 'CUSTNO_PRC', 'TYPEOUT_PRC', 'INDUSID_PRC', 'CLASSID_PRC', 'GDISC_PRC',
        'TEAM', 'TEAMNAME', 'TANGGAL', 'TIME_GO', 'TIME_IN', 'TIME_OUT', 'TIME_BACK',
        'TIME_CONSUME', 'TIME_TRAVEL', 'DIST_TRAVEL', 'TIME_PAUSE', 'TIME_CONSUME_PAUSE',
        'ORDER_QTY', 'ORDER_VAL', 'JUAL_QTY', 'JUAL_VAL', 'TOTAL_ITEM',
        'FLAG_PJP', 'FLAG_VISIT', 'FLAG_EC', 'FLAG_PAUSE', 'FLAG_BUY', 'FLAG_BACK',
        'FLAG_ACTIVITY', 'FLAG_KIRIM', 'M_LA', 'M_LG', 'V_LA', 'V_LG',
        'NOTES', 'CBY', 'MBY', 'CDATE', 'MDATE', 'POLARUTE', 'TYPE', 'TYPENAME',
        'PHOTONAME', 'SEQ_ROUTE', 'FLAG_PHONE',
    ];

    /**
     * GET /api/rpt-visit-an-h
     *
     * Menampilkan data kunjungan sales (rpt_visit_an_h) dengan filter wajib tanggal
     * agar query tetap ringan dan efisien pada database besar (> 1.5 juta row).
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'tanggal'    => 'nullable|date_format:Y-m-d',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date'   => 'nullable|date_format:Y-m-d',
            'per_page'   => 'nullable|integer|min:1|max:5000',
            'page'       => 'nullable|integer|min:1',
            'rid'        => 'nullable|string|max:50',
            'eid'        => 'nullable|string|max:50',
            'bid'        => 'nullable|string|max:50',
            'muid'       => 'nullable|string|max:50',
            'team'       => 'nullable|string|max:50',
            'custno'     => 'nullable|string|max:50',
            'flag_visit' => 'nullable|string|max:5',
            'flag_ec'    => 'nullable|string|max:5',
            'flag_buy'   => 'nullable|string|max:5',
            'search'     => 'nullable|string|max:100',
            'fields'     => 'nullable|string',
            'sort_by'    => 'nullable|string',
            'sort_dir'   => 'nullable|in:asc,desc,ASC,DESC',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi parameter gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // 2. Proteksi & Penentuan Filter Tanggal (Wajib agar tidak full-scan 1.5M rows)
            $tanggal = $request->input('tanggal');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if ($tanggal) {
                // Filter tanggal tunggal
                $queryStartDate = $tanggal;
                $queryEndDate = $tanggal;
            } elseif ($startDate) {
                // Filter rentang tanggal
                $queryStartDate = $startDate;
                $queryEndDate = $endDate ?: $startDate;

                // Batasi rentang maksimal 31 hari agar tidak memberatkan server
                $daysDiff = Carbon::parse($queryStartDate)->diffInDays(Carbon::parse($queryEndDate), false);
                if ($daysDiff < 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parameter end_date tidak boleh lebih kecil dari start_date.',
                    ], 422);
                }
                if ($daysDiff > 31) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rentang tanggal maksimal adalah 31 hari agar query tetap ringan.',
                    ], 422);
                }
            } else {
                // Jika tidak ada tanggal yang dikirim, default ke hari ini
                $today = now()->toDateString();
                $queryStartDate = $today;
                $queryEndDate = $today;
            }

            // 3. Query Builder
            $query = DB::table('rpt_visit_an_h');

            // Apply filter tanggal
            if ($queryStartDate === $queryEndDate) {
                $query->where('TANGGAL', $queryStartDate);
            } else {
                $query->whereBetween('TANGGAL', [$queryStartDate, $queryEndDate]);
            }

            // 4. Filter Tambahan
            if ($request->filled('rid')) {
                $query->where('RID', $request->input('rid'));
            }
            if ($request->filled('eid')) {
                $query->where('EID', $request->input('eid'));
            }
            if ($request->filled('bid')) {
                $query->where('BID', $request->input('bid'));
            }
            if ($request->filled('muid')) {
                $query->where('MUID', $request->input('muid'));
            }
            if ($request->filled('team')) {
                $query->where('TEAM', $request->input('team'));
            }
            if ($request->filled('custno')) {
                $query->where('CUSTNO', $request->input('custno'));
            }
            if ($request->filled('flag_visit')) {
                $query->where('FLAG_VISIT', strtoupper($request->input('flag_visit')));
            }
            if ($request->filled('flag_ec')) {
                $query->where('FLAG_EC', strtoupper($request->input('flag_ec')));
            }
            if ($request->filled('flag_buy')) {
                $query->where('FLAG_BUY', strtoupper($request->input('flag_buy')));
            }

            // Filter Pencarian Teks (CUSTNAME, MUNAME, BNAME)
            if ($request->filled('search')) {
                $search = trim($request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('CUSTNAME', 'ILIKE', "%{$search}%")
                      ->orWhere('MUNAME', 'ILIKE', "%{$search}%")
                      ->orWhere('BNAME', 'ILIKE', "%{$search}%")
                      ->orWhere('CUSTNO', 'ILIKE', "%{$search}%");
                });
            }

            // 5. Pemilihan Kolom (Selective Fields)
            if ($request->filled('fields')) {
                $requestedFields = array_map('trim', explode(',', $request->input('fields')));
                $selectedFields = [];
                $upperAllowed = array_change_key_case(array_flip($this->allowedColumns), CASE_UPPER);

                foreach ($requestedFields as $field) {
                    $upperField = strtoupper($field);
                    if (isset($upperAllowed[$upperField])) {
                        $selectedFields[] = $upperField;
                    }
                }

                if (!empty($selectedFields)) {
                    $query->select($selectedFields);
                }
            }

            // 6. Sorting
            $sortBy = strtoupper($request->input('sort_by', 'ID'));
            if (!in_array($sortBy, $this->allowedColumns, true)) {
                $sortBy = 'ID';
            }
            $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortDir);

            // 7. Pagination
            $perPage = (int) $request->input('per_page', 50);
            $results = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data rpt_visit_an_h berhasil diambil.',
                'filters_applied' => [
                    'tanggal'    => $tanggal,
                    'start_date' => $queryStartDate,
                    'end_date'   => $queryEndDate,
                    'rid'        => $request->input('rid'),
                    'eid'        => $request->input('eid'),
                    'bid'        => $request->input('bid'),
                    'muid'       => $request->input('muid'),
                    'team'       => $request->input('team'),
                    'custno'     => $request->input('custno'),
                    'flag_visit' => $request->input('flag_visit'),
                    'flag_ec'    => $request->input('flag_ec'),
                    'flag_buy'   => $request->input('flag_buy'),
                    'search'     => $request->input('search'),
                ],
                'pagination' => [
                    'total'        => $results->total(),
                    'per_page'     => $results->perPage(),
                    'current_page' => $results->currentPage(),
                    'last_page'    => $results->lastPage(),
                    'from'         => $results->firstItem(),
                    'to'           => $results->lastItem(),
                ],
                'data' => $results->items(),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data kunjungan.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
