<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'file_name',
        'total_rows',
        'processed_rows',
        'status', // pending, processing, completed, failed
        'log_lines',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data asli.
     *
     * @var array
     */
    protected $casts = [
        'log_lines' => 'array',
    ];

    /**
     * Menambahkan baris log baru ke batch.
     *
     * @param string $type ('info', 'success', 'error')
     * @param string $message
     */
    public function addLog(string $type, string $message)
    {
        $logs = $this->log_lines ?? [];
        $logs[] = [
            'type' => $type,
            'message' => $message,
        ];
        // Menggunakan update agar tidak memicu event model lain secara tidak sengaja
        $this->update(['log_lines' => $logs]);
    }

    /**
     * Memperbarui status batch dan menambahkan log awal jika ada.
     *
     * @param string $status
     * @param string|null $initialMessage
     */
    public function updateStatus(string $status, string $initialMessage = null)
    {
        $this->update(['status' => $status]);
        if ($initialMessage) {
            $this->addLog('info', $initialMessage);
        }
    }
}

