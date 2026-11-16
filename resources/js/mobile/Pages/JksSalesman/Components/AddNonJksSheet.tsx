import React, { useState, useEffect } from 'react';
import { NonJksOutletItem, SalesmanOption, getCsrfHeaders } from '../constants';
import {
    XMarkIcon,
    PlusCircleIcon,
    CheckIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon,
    BuildingStorefrontIcon,
} from '@heroicons/react/24/outline';

interface AddNonJksSheetProps {
    outlets: NonJksOutletItem[];
    salesmans: SalesmanOption[];
    distributorCode: string;
    bulan: string;
    onClose: () => void;
    onSuccess: (message: string, submittedOutletIds: number[], approvalId: number) => void;
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

export default function AddNonJksSheet({
    outlets,
    salesmans,
    distributorCode,
    bulan,
    onClose,
    onSuccess,
    onError,
}: AddNonJksSheetProps) {
    const [salesmanCode, setSalesmanCode] = useState('');
    const [hari, setHari] = useState<Record<string, boolean>>({
        h1: false,
        h2: false,
        h3: false,
        h4: false,
        h5: false,
        h6: false,
        h7: false,
    });
    const [minggu, setMinggu] = useState<Record<string, boolean>>({
        w1: false,
        w2: false,
        w3: false,
        w4: false,
    });
    const [reason, setReason] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        if (outlets.length > 0) {
            setSalesmanCode('');
            setHari({
                h1: false,
                h2: false,
                h3: false,
                h4: false,
                h5: false,
                h6: false,
                h7: false,
            });
            setMinggu({
                w1: false,
                w2: false,
                w3: false,
                w4: false,
            });
            setReason('');
        }
    }, [outlets]);

    if (outlets.length === 0) return null;

    const selectedHariKeys = Object.keys(hari).filter((k) => hari[k]);
    const selectedMingguKeys = Object.keys(minggu).filter((k) => minggu[k]);
    const emptyPrcCount = outlets.filter((o) => !o.customer_eska).length;

    const toggleHari = (key: string) => {
        setHari((prev) => ({ ...prev, [key]: !prev[key] }));
    };

    const toggleMinggu = (key: string) => {
        setMinggu((prev) => ({ ...prev, [key]: !prev[key] }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!salesmanCode) {
            onError('Silakan pilih Salesman terlebih dahulu.');
            return;
        }
        if (selectedHariKeys.length === 0) {
            onError('Pilih minimal 1 hari kunjungan.');
            return;
        }
        if (selectedMingguKeys.length === 0) {
            onError('Pilih minimal 1 minggu kunjungan.');
            return;
        }

        setIsSubmitting(true);

        try {
            const res = await fetch('/mobile/jks-salesman/non-jks/add-schedule', {
                method: 'POST',
                credentials: 'same-origin',
                headers: getCsrfHeaders(),
                body: JSON.stringify({
                    distributor_code: distributorCode || outlets[0].distributor_code,
                    salesman_code: salesmanCode,
                    bulan,
                    outlet_ids: outlets.map((o) => o.id),
                    hari: selectedHariKeys,
                    minggu: selectedMingguKeys,
                    reason: reason.trim(),
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                const firstValidationError = data.errors
                    ? (Object.values(data.errors)[0] as string[])?.[0]
                    : null;
                throw new Error(
                    firstValidationError || data.error || data.message || 'Gagal mengajukan penambahan jadwal.'
                );
            }

            onSuccess(
                data.message || 'Pengajuan tambah ke JKS berhasil dikirim!',
                data.submitted_outlet_ids || outlets.map((o) => o.id),
                data.approval_id || 1
            );
            onClose();
        } catch (err: any) {
            onError(err.message || 'Terjadi kesalahan sistem.');
        } finally {
            setIsSubmitting(false);
        }
    };

    const isFormValid = Boolean(
        salesmanCode && selectedHariKeys.length > 0 && selectedMingguKeys.length > 0
    );

    return (
        <div className="fixed inset-0 z-50 flex flex-col justify-end">
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity animate-fade-in"
                onClick={onClose}
            />

            {/* Modal Sheet */}
            <div className="relative bg-white rounded-t-[32px] shadow-2xl w-full max-w-2xl md:max-w-4xl mx-auto max-h-[92vh] flex flex-col animate-slide-up overflow-hidden z-10">
                {/* Header */}
                <div className="flex items-center justify-between p-4 px-5 border-b border-slate-100 bg-slate-50/70">
                    <div className="flex items-center gap-2.5 min-w-0">
                        <div className="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <PlusCircleIcon className="w-5 h-5" />
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-sm font-black text-slate-800 truncate">
                                {outlets.length === 1
                                    ? 'Tambah Toko ke Jadwal JKS'
                                    : `Tambah Massal (${outlets.length} Toko) ke JKS`}
                            </h3>
                            <p className="text-xs font-bold text-slate-400 truncate">
                                {outlets.length === 1
                                    ? `${outlets[0].customer_name} (${outlets[0].customer_code})`
                                    : `Distributor: ${outlets[0].distributor_name || distributorCode}`}
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
                <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-5 space-y-4">
                    {/* 1. Ringkasan Toko & Peringatan PRC */}
                    <div className="bg-slate-50 border border-slate-200/80 rounded-2xl p-3 space-y-2">
                        {outlets.length === 1 ? (
                            <div className="flex items-center justify-between gap-2 text-xs">
                                <div className="min-w-0">
                                    <span className="text-2xs font-bold text-slate-400 uppercase block">
                                        Kode PRC (Eskalink)
                                    </span>
                                    {outlets[0].customer_eska ? (
                                        <span className="font-mono font-bold text-emerald-700">
                                            {outlets[0].customer_eska}
                                        </span>
                                    ) : (
                                        <span className="font-semibold text-amber-700 italic">
                                            Belum Ada PRC (Akan dilengkapi Admin saat Approval)
                                        </span>
                                    )}
                                </div>
                                <div className="text-right shrink-0">
                                    <span className="text-2xs font-bold text-slate-400 uppercase block">
                                        Avg Omset
                                    </span>
                                    <span className="font-mono font-black text-slate-800">
                                        Rp {Math.round(outlets[0].avg_value_net).toLocaleString('id-ID')}
                                    </span>
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-2">
                                <div className="flex items-center justify-between text-xs">
                                    <span className="font-bold text-slate-700 flex items-center gap-1.5">
                                        <BuildingStorefrontIcon className="w-4 h-4 text-indigo-600" />
                                        {outlets.length} Outlet Terpilih
                                    </span>
                                    {emptyPrcCount > 0 && (
                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-3xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <ExclamationTriangleIcon className="w-3 h-3" />
                                            {emptyPrcCount} Tanpa PRC
                                        </span>
                                    )}
                                </div>
                                <div className="max-h-28 overflow-y-auto flex flex-wrap gap-1.5 pt-1">
                                    {outlets.map((o) => (
                                        <span
                                            key={o.id}
                                            className="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-2xs font-semibold bg-white border border-slate-200 text-slate-700"
                                        >
                                            <span className="truncate max-w-[160px]">{o.customer_name}</span>
                                            <span className="font-mono text-3xs text-slate-400">
                                                ({o.customer_code})
                                            </span>
                                        </span>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* 2. Pilih Salesman */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                            Salesman Tujuan (SE) <span className="text-rose-500">*</span>
                        </label>
                        <select
                            value={salesmanCode}
                            onChange={(e) => setSalesmanCode(e.target.value)}
                            className="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-xs sm:text-sm text-slate-800 font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                        >
                            <option value="">-- Pilih Salesman --</option>
                            {salesmans.map((s) => (
                                <option key={s.salesman_code} value={s.salesman_code}>
                                    {s.salesman_name} ({s.salesman_code})
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* 3. Pilih Hari Kunjungan */}
                    <div>
                        <div className="flex items-center justify-between mb-1.5">
                            <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block">
                                Hari Kunjungan <span className="text-rose-500">*</span>
                            </label>
                            <span className="text-2xs text-slate-400">Pilih minimal 1 hari</span>
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
                                                ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm'
                                                : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
                                        }`}
                                    >
                                        {h.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* 4. Pilih Minggu Kunjungan */}
                    <div>
                        <div className="flex items-center justify-between mb-1.5">
                            <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block">
                                Siklus Minggu <span className="text-rose-500">*</span>
                            </label>
                            <span className="text-2xs text-slate-400">Pilih minimal 1 minggu</span>
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
                                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                                                : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
                                        }`}
                                    >
                                        {w.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* 5. Catatan / Alasan Pengajuan */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                            Alasan Pengajuan <span className="text-2xs font-normal text-slate-400">(Opsional)</span>
                        </label>
                        <textarea
                            value={reason}
                            onChange={(e) => setReason(e.target.value)}
                            placeholder="Contoh: Penambahan rute toko potensial Non-JKS..."
                            rows={2}
                            className="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all resize-none"
                        />
                    </div>

                    {/* Tombol Submit */}
                    <div className="pt-1">
                        <button
                            type="submit"
                            disabled={isSubmitting || !isFormValid}
                            className={`w-full py-3.5 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-md ${
                                isSubmitting || !isFormValid
                                    ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'
                                    : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-200 active:scale-[0.98]'
                            }`}
                        >
                            {isSubmitting ? (
                                <>
                                    <ArrowPathIcon className="w-4 h-4 animate-spin" />
                                    Mengirim Pengajuan...
                                </>
                            ) : (
                                <>
                                    <CheckIcon className="w-4 h-4 stroke-[2.5]" />
                                    Ajukan Tambah ke JKS ({outlets.length} Toko)
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
