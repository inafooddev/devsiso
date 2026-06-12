<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NominalQcDist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NominalQcDistController extends Controller
{
    public function index()
    {
        $data = NominalQcDist::all();
        return response()->json([
            'success' => true,
            'message' => 'List Data Nominal QC Dist',
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $data = NominalQcDist::find($id);
        
        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Data Nominal QC Dist',
                'data' => $data
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan',
        ], 404);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'distributor_code' => 'required|string',
            'qty' => 'required|numeric',
            'discount_4' => 'required|numeric',
            'discount_8' => 'required|numeric',
            'neto' => 'required|numeric',
            'nominal_surat' => 'required|numeric',
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $input = $request->except('file_surat');

        if ($request->hasFile('file_surat')) {
            $input['file_surat'] = $request->file('file_surat')->store('public/surat_qc');
        }

        $data = NominalQcDist::create($input);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $data
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = NominalQcDist::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'date',
            'distributor_code' => 'string',
            'qty' => 'numeric',
            'discount_4' => 'numeric',
            'discount_8' => 'numeric',
            'neto' => 'numeric',
            'nominal_surat' => 'numeric',
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $input = $request->except('file_surat');

        if ($request->hasFile('file_surat')) {
            if ($data->file_surat) {
                Storage::delete($data->file_surat);
            }
            $input['file_surat'] = $request->file('file_surat')->store('public/surat_qc');
        }

        $data->update($input);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $data = NominalQcDist::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        if ($data->file_surat) {
            Storage::delete($data->file_surat);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
