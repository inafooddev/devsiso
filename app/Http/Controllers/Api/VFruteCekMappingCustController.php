<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VFruteCekMappingCustController extends Controller
{
    /**
     * Whitelist kolom yang diizinkan untuk di-select dan di-sort agar aman dari SQL Injection.
     */
    protected array $allowedColumns = [
        'region', 'area', 'distributor_code', 'slsno', 'custno',
        'uniq', 'custname', 'custadd1',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7',
        'm1', 'm2', 'm3', 'm4',
        'cek_map',
    ];

    /**
     * GET /api/v-frute-cek-mapping-cust
     *
     * Menampilkan data view v_frute_cek_mapping_cust dengan filter fleksibel.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'per_page'         => 'nullable|integer|min:1|max:5000',
            'page'             => 'nullable|integer|min:1',
            'region'           => 'nullable|string|max:50',
            'area'             => 'nullable|string|max:50',
            'distributor_code' => 'nullable|string|max:50',
            'slsno'            => 'nullable|string|max:50',
            'custno'           => 'nullable|string|max:50',
            'cek_map'          => 'nullable|string|max:50',
            'search'           => 'nullable|string|max:100',
            'fields'           => 'nullable|string',
            'sort_by'          => 'nullable|string',
            'sort_dir'         => 'nullable|in:asc,desc,ASC,DESC',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi parameter gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $query = DB::table('v_frute_cek_mapping_cust');

            // 2. Filter Dinamis
            if ($request->filled('region')) {
                $query->where('region', $request->input('region'));
            }
            if ($request->filled('area')) {
                $query->where('area', $request->input('area'));
            }
            if ($request->filled('distributor_code')) {
                $query->where('distributor_code', $request->input('distributor_code'));
            }
            if ($request->filled('slsno')) {
                $query->where('slsno', $request->input('slsno'));
            }
            if ($request->filled('custno')) {
                $query->where('custno', $request->input('custno'));
            }
            if ($request->filled('cek_map')) {
                $query->where('cek_map', strtolower($request->input('cek_map')));
            }

            // Filter Pencarian Teks
            if ($request->filled('search')) {
                $search = trim($request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('custname', 'ILIKE', "%{$search}%")
                      ->orWhere('custno', 'ILIKE', "%{$search}%")
                      ->orWhere('custadd1', 'ILIKE', "%{$search}%")
                      ->orWhere('uniq', 'ILIKE', "%{$search}%")
                      ->orWhere('slsno', 'ILIKE', "%{$search}%");
                });
            }

            // 3. Selective Fields
            if ($request->filled('fields')) {
                $requestedFields = array_map('trim', explode(',', $request->input('fields')));
                $selectedFields = [];
                $lowerAllowed = array_change_key_case(array_flip($this->allowedColumns), CASE_LOWER);

                foreach ($requestedFields as $field) {
                    $lowerField = strtolower($field);
                    if (isset($lowerAllowed[$lowerField])) {
                        $selectedFields[] = $lowerField;
                    }
                }

                if (!empty($selectedFields)) {
                    $query->select($selectedFields);
                }
            }

            // 4. Sorting
            $sortBy = strtolower($request->input('sort_by', 'custno'));
            if (!in_array($sortBy, $this->allowedColumns, true)) {
                $sortBy = 'custno';
            }
            $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortDir);

            // 5. Pagination
            $perPage = (int) $request->input('per_page', 100);
            $results = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data v_frute_cek_mapping_cust berhasil diambil.',
                'filters_applied' => [
                    'region'           => $request->input('region'),
                    'area'             => $request->input('area'),
                    'distributor_code' => $request->input('distributor_code'),
                    'slsno'            => $request->input('slsno'),
                    'custno'           => $request->input('custno'),
                    'cek_map'          => $request->input('cek_map'),
                    'search'           => $request->input('search'),
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
                'message' => 'Terjadi kesalahan saat memproses data mapping customer.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
