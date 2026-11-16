import React, { useState, useEffect } from 'react';
import { JksItem, SalesmanOption, getCsrfHeaders } from '../constants';
import { XMarkIcon, PencilSquareIcon, CheckIcon, ArrowPathIcon } from '@heroicons/react/24/outline';

interface EditScheduleSheetProps {
    item: JksItem | null;
    salesmans: SalesmanOption[];
    distributorCode: string;
    onClose: () => void;
    onSuccess: (message: string) => void;
    onError: (message: string) => void;
}

const HARI_OPTIONS = [
    { key: 'h1', label: 'Senin' },
    { key: 'h2', label: 'Selasa' },
    { key: 'h3', label: 'Rabu' },
    { key: 'h4', label: 'Kamis' },
    { key: 'h5', label: 'Jumat' },
    { key: 'h6', label: 'Sabtu' },
    { key: 'h7', label: 'Minggu' },
] as const;

const MINGGU_OPTIONS = [
    { key: 'w1', label: 'Minggu 1 (M1)' },
    { key: 'w2', label: 'Minggu 2 (M2)' },
    { key: 'w3', label: 'Minggu 3 (M3)' },
    { key: 'w4', label: 'Minggu 4 (M4)' },
] as const;

export default function EditScheduleSheet({
    item,
    salesmans,
    distributorCode,
    onClose,
    onSuccess,
    onError,
}: EditScheduleSheetProps) {
    const [salesmanCode, setSalesmanCode] = useState('');
    const [hari, setHari] = useState<Record<string, boolean>>({
        h1: false, h2: false, h3: false, h4: false, h5: false, h6: false, h7: false
    });
    const [minggu, setMinggu] = useState<Record<string, boolean>>({
        w1: false, w2: false, w3: false, w4: false
    });
    const [reason, setReason] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        if (item) {
            setSalesmanCode(item.salesman_code || '');
            setHari({
                h1: item.h1 === 'Y',
                h2: item.h2 === 'Y',
                h3: item.h3 === 'Y',
                h4: item.h4 === 'Y',
                h5: item.h5 === 'Y',
                h6: item.h6 === 'Y',
                h7: item.h7 === 'Y',
            });
            setMinggu({
                w1: item.w1 === 'Y',
                w2: item.w2 === 'Y',
                w3: item.w3 === 'Y',
                w4: item.w4 === 'Y',
            });
            setReason('');
        }
    }, [item]);

    if (!item) return null;

    const toggleHari = (key: string) => {
        setHari(prev => ({ ...prev, [key]: !prev[key] }));
    };

    const toggleMinggu = (key: string) => {
        setMinggu(prev => ({ ...prev, [key]: !prev[key] }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!salesmanCode) {
            onError('Silakan pilih salesman.');
            return;
        }

        if (reason.trim().length < 5) {
            onError('Alasan perubahan jadwal wajib diisi minimal 5 karakter.');
            return;
        }

        setIsSubmitting(true);

        try {
            const res = await fetch('/mobile/jks-salesman/update-schedule', {
                method: 'POST',
                credentials: 'same-origin',
                headers: getCsrfHeaders(),
                body: JSON.stringify({
                    id: item.id,
                    distributor_code: distributorCode || item.distributor_code,
                    salesman_code: salesmanCode,
                    h1: hari.h1 ? 'Y' : 'T',
                    h2: hari.h2 ? 'Y' : 'T',
                    h3: hari.h3 ? 'Y' : 'T',
                    h4: hari.h4 ? 'Y' : 'T',
                    h5: hari.h5 ? 'Y' : 'T',
                    h6: hari.h6 ? 'Y' : 'T',
                    h7: hari.h7 ? 'Y' : 'T',
                    w1: minggu.w1 ? 'Y' : 'T',
                    w2: minggu.w2 ? 'Y' : 'T',
                    w3: minggu.w3 ? 'Y' : 'T',
                    w4: minggu.w4 ? 'Y' : 'T',
                    reason: reason.trim(),
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                const firstValidationError = data.errors
                    ? (Object.values(data.errors)[0] as string[])?.[0]
                    : null;
                throw new Error(firstValidationError || data.error || data.message || 'Gagal menyimpan perubahan jadwal.');
            }

            onSuccess(data.message || 'Perubahan jadwal berhasil diproses!');
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
            <div className="relative bg-white rounded-t-[32px] shadow-2xl w-full max-h-[92vh] flex flex-col animate-slide-up overflow-hidden z-10">
                {/* Header */}
                <div className="flex items-center justify-between p-4 px-5 border-b border-slate-100 bg-slate-50/70">
                    <div className="flex items-center gap-2.5 min-w-0">
                        <div className="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <PencilSquareIcon className="w-5 h-5" />
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-sm font-black text-slate-800 truncate" title={item.customer_name}>
                                Modifikasi Jadwal
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

                {/* Form Content */}
                <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-5 space-y-5">
                    {/* 1. Pilih Salesman */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            Salesman Penanggung Jawab
                        </label>
                        <select
                            value={salesmanCode}
                            onChange={(e) => setSalesmanCode(e.target.value)}
                            className="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-xs md:text-sm text-slate-800 font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                        >
                            {salesmans.map((s) => (
                                <option key={s.salesman_code} value={s.salesman_code}>
                                    {s.salesman_name} ({s.salesman_code})
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* 2. Hari Kunjungan */}
                    <div>
                        <div className="flex items-center justify-between mb-2">
                            <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block">
                                Hari Kunjungan
                            </label>
                            <span className="text-xs text-slate-400">Pilih 1 atau lebih</span>
                        </div>
                        <div className="grid grid-cols-4 gap-2">
                            {HARI_OPTIONS.map((h) => {
                                const isChecked = hari[h.key];
                                return (
                                    <button
                                        key={h.key}
                                        type="button"
                                        onClick={() => toggleHari(h.key)}
                                        className={`py-2.5 px-2 rounded-2xl text-xs font-bold transition-all border text-center ${
                                            isChecked
                                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200'
                                                : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
                                        }`}
                                    >
                                        {h.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* 3. Minggu Kunjungan */}
                    <div>
                        <div className="flex items-center justify-between mb-2">
                            <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block">
                                Siklus Minggu
                            </label>
                            <span className="text-xs text-slate-400">Pilih 1 atau lebih</span>
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                            {MINGGU_OPTIONS.map((w) => {
                                const isChecked = minggu[w.key];
                                return (
                                    <button
                                        key={w.key}
                                        type="button"
                                        onClick={() => toggleMinggu(w.key)}
                                        className={`py-2.5 px-3 rounded-2xl text-xs font-bold transition-all border text-center ${
                                            isChecked
                                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200'
                                                : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
                                        }`}
                                    >
                                        {w.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* 4. Alasan Modifikasi */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            Alasan Perubahan <span className="text-rose-500">*</span>
                        </label>
                        <textarea
                            value={reason}
                            onChange={(e) => setReason(e.target.value)}
                            placeholder="Tuliskan alasan perubahan jadwal (minimal 5 karakter)..."
                            rows={3}
                            className="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all resize-none"
                        />
                        <div className="flex justify-between items-center mt-1 text-xs text-slate-400">
                            <span>Wajib diisi untuk audit trail</span>
                            <span className={reason.trim().length >= 5 ? 'text-emerald-600 font-bold' : 'text-slate-400'}>
                                {reason.trim().length}/5 karakter
                            </span>
                        </div>
                    </div>

                    {/* Submit Button */}
                    <div className="pt-2">
                        <button
                            type="submit"
                            disabled={isSubmitting || reason.trim().length < 5}
                            className={`w-full py-3.5 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-md ${
                                isSubmitting || reason.trim().length < 5
                                    ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'
                                    : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-200 active:scale-[0.98]'
                            }`}
                        >
                            {isSubmitting ? (
                                <>
                                    <ArrowPathIcon className="w-4 h-4 animate-spin" />
                                    Menyimpan Perubahan...
                                </>
                            ) : (
                                <>
                                    <CheckIcon className="w-4 h-4 stroke-[2.5]" />
                                    Simpan Perubahan Jadwal
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
