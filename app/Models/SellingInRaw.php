<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellingInRaw extends Model
{
    use HasFactory;

    protected $table = 'selling_in_raws';

    /**
     * Karena tabel ini menggunakan partisi PostgreSQL, PRIMARY KEY bersifat composite (id, invoice_date).
     * Laravel tidak mendukung composite PK secara native, sehingga kita tetap set $primaryKey = 'id'
     * untuk operasi Eloquent standar. Pastikan query yang melibatkan partisi selalu menyertakan
     * filter invoice_date agar PostgreSQL dapat melakukan partition pruning.
     */
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'integer';

    public $timestamps = true;

    /**
     * Kolom yang dapat diisi secara mass assignment.
     * Dikelompokkan: metadata pipeline → kolom NOT NULL → kolom nullable.
     */
    protected $fillable = [
        // Metadata pipeline
        'import_batch_id',
        'row_number',

        // Data Excel — wajib isi (NOT NULL)
        'invoice_date',
        'divisi',
        'wilayah',
        'kode_distributor',
        'distributor',
        'kode_barang',

        // Data Excel — opsional (nullable)
        'kode',
        'invoice_no',
        'jenis_penjualan',
        'nama_barang',
        'qty',
        'satuan',
        'harga_satuan',
        'subtotal',
        'qty_bonus',
        'nilai_bonus',
        'diskon_1',
        'diskon_2',
        'diskon_3',
        'dpp',
        'ppn',
        'total',
        'total_idr',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        // Metadata
        'import_batch_id' => 'integer',
        'row_number'      => 'integer',

        // Tanggal
        'invoice_date'    => 'date',

        // Numerik — sesuai precision di DB (numeric 15,4 dan 18,4)
        'qty'             => 'decimal:4',
        'harga_satuan'    => 'decimal:4',
        'subtotal'        => 'decimal:4',
        'qty_bonus'       => 'decimal:4',
        'nilai_bonus'     => 'decimal:4',
        'diskon_1'        => 'decimal:4',
        'diskon_2'        => 'decimal:4',
        'diskon_3'        => 'decimal:4',
        'dpp'             => 'decimal:4',
        'ppn'             => 'decimal:4',
        'total'           => 'decimal:4',
        'total_idr'       => 'decimal:4',

        // Timestamps
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════
    // RELASI
    // ═══════════════════════════════════════════════════════

    /**
     * Batch import yang menghasilkan baris ini.
     * Berguna untuk audit: "data ini dari file upload kapan?"
     */
    public function importBatch()
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    // ═══════════════════════════════════════════════════════
    // SCOPE — Shortcut query yang umum digunakan
    // ═══════════════════════════════════════════════════════

    /**
     * Filter berdasarkan bulan dan tahun invoice.
     * Memanfaatkan partition pruning PostgreSQL secara otomatis.
     *
     * Contoh: SellingInRaw::ofPeriod(2026, 8)->get();
     */
    public function scopeOfPeriod($query, int $year, int $month)
    {
        return $query
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month);
    }

    /**
     * Filter berdasarkan kode distributor.
     *
     * Contoh: SellingInRaw::ofDistributor('DIST001')->get();
     */
    public function scopeOfDistributor($query, string $kodeDistributor)
    {
        return $query->where('kode_distributor', $kodeDistributor);
    }

    /**
     * Filter berdasarkan batch import tertentu.
     *
     * Contoh: SellingInRaw::ofBatch(42)->get();
     */
    public function scopeOfBatch($query, int $batchId)
    {
        return $query->where('import_batch_id', $batchId);
    }
}
