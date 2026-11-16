import React, { useState, useEffect } from 'react';
import { JksItem, getCsrfHeaders } from '../constants';
import { XMarkIcon, TrashIcon, ArrowPathIcon } from '@heroicons/react/24/outline';

interface DeleteScheduleSheetProps {
    item: JksItem | null;
    distributorCode: string;
    onClose: () => void;
    onSuccess: (message: string) => void;
    onError: (message: string) => void;
}

export default function DeleteScheduleSheet({
    item,
    distributorCode,
    onClose,
    onSuccess,
    onError,
}: DeleteScheduleSheetProps) {
    const [deleteType, setDeleteType] = useState<'soft' | 'hard'>('soft');
    const [reason, setReason] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        if (item) {
            setDeleteType('soft');
            setReason('');
        }
    }, [item]);

    if (!item) return null;

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (reason.trim().length < 5) {
            onError('Alasan penghapusan wajib diisi minimal 5 karakter.');
            return;
        }

        setIsSubmitting(true);

        try {
            const res = await fetch('/mobile/jks-salesman/delete-schedule', {
                method: 'POST',
                credentials: 'same-origin',
                headers: getCsrfHeaders(),
                body: JSON.stringify({
                    id: item.id,
                    distributor_code: distributorCode || item.distributor_code,
                    delete_type: deleteType,
                    reason: reason.trim(),
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                const firstValidationError = data.errors
                    ? (Object.values(data.errors)[0] as string[])?.[0]
                    : null;
                throw new Error(firstValidationError || data.error || data.message || 'Gagal memproses penghapusan jadwal.');
            }

            onSuccess(data.message || 'Jadwal berhasil dihapus!');
            onClose();
        } catch (err: any) {
            onError(err.message || 'Terjadi kesalahan sistem.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex flex-col justify-end">
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity animate-fade-in"
                onClick={onClose}
            />

            {/* Modal Sheet */}
            <div className="relative bg-white rounded-t-[32px] shadow-2xl w-full max-h-[90vh] flex flex-col animate-slide-up overflow-hidden z-10">
                {/* Header */}
                <div className="flex items-center justify-between p-4 px-5 border-b border-slate-100 bg-rose-50/50">
                    <div className="flex items-center gap-2.5 min-w-0">
                        <div className="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <TrashIcon className="w-5 h-5" />
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-sm font-black text-slate-800 truncate" title={item.customer_name}>
                                Hapus Jadwal Toko
                            </h3>
                            <p className="text-xs font-bold text-slate-400 font-mono truncate">
                                {item.customer_name} ({item.customer_code || item.uniq_kode})
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-600 shadow-sm border border-slate-200 transition-colors"
                    >
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-5 space-y-4">
                    {/* Metode Hapus */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            Pilih Metode Hapus
                        </label>
                        <div className="grid grid-cols-2 gap-2.5">
                            <button
                                type="button"
                                onClick={() => setDeleteType('soft')}
                                className={`p-3 rounded-2xl border text-left transition-all ${
                                    deleteType === 'soft'
                                        ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-200 text-amber-900'
                                        : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
                                }`}
                            >
                                <span className="font-bold text-xs block mb-1">Soft Delete</span>
                                <span className="text-xs text-slate-400 block leading-snug">
                                    Dikosongkan jadwalnya, masuk ke Non-Rute.
                                </span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setDeleteType('hard')}
                                className={`p-3 rounded-2xl border text-left transition-all ${
                                    deleteType === 'hard'
                                        ? 'bg-rose-50 border-rose-300 ring-2 ring-rose-200 text-rose-900'
                                        : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
                                }`}
                            >
                                <span className="font-bold text-xs block mb-1">Hard Delete</span>
                                <span className="text-xs text-slate-400 block leading-snug">
                                    Dihapus permanen dari database.
                                </span>
                            </button>
                        </div>
                    </div>

                    {/* Alasan Hapus */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            Alasan Penghapusan <span className="text-rose-500">*</span>
                        </label>
                        <textarea
                            value={reason}
                            onChange={(e) => setReason(e.target.value)}
                            placeholder="Tuliskan alasan penghapusan (minimal 5 karakter)..."
                            rows={3}
                            className="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-xs text-slate-800 placeholder-slate-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-100 outline-none transition-all resize-none"
                        />
                        <div className="flex justify-between items-center mt-1 text-xs text-slate-400">
                            <span>Wajib diisi untuk persetujuan</span>
                            <span className={reason.trim().length >= 5 ? 'text-emerald-600 font-bold' : 'text-slate-400'}>
                                {reason.trim().length}/5 karakter
                            </span>
                        </div>
                    </div>

                    {/* Submit */}
                    <div className="pt-2">
                        <button
                            type="submit"
                            disabled={isSubmitting || reason.trim().length < 5}
                            className={`w-full py-3.5 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-md ${
                                isSubmitting || reason.trim().length < 5
                                    ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'
                                    : 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-200 active:scale-[0.98]'
                            }`}
                        >
                            {isSubmitting ? (
                                <>
                                    <ArrowPathIcon className="w-4 h-4 animate-spin" />
                                    Memproses Hapus...
                                </>
                            ) : (
                                <>
                                    <TrashIcon className="w-4 h-4" />
                                    Konfirmasi Hapus Jadwal
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
