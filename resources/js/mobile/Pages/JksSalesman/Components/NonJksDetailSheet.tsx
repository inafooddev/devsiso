import React, { useState, useEffect } from 'react';
import { NonJksOutletItem, getCsrfHeaders } from '../constants';
import {
    XMarkIcon,
    BuildingStorefrontIcon,
    ChatBubbleBottomCenterTextIcon,
    ChartBarIcon,
    PlusCircleIcon,
    CheckIcon,
    ArrowPathIcon,
} from '@heroicons/react/24/outline';

interface NonJksDetailSheetProps {
    item: NonJksOutletItem | null;
    historyLabels: string[];
    onClose: () => void;
    onAddJks: (item: NonJksOutletItem) => void;
    onRemarkSaved: (outletId: number, newRemark: string, message: string) => void;
    onError: (message: string) => void;
}

export default function NonJksDetailSheet({
    item,
    historyLabels,
    onClose,
    onAddJks,
    onRemarkSaved,
    onError,
}: NonJksDetailSheetProps) {
    const [remarkText, setRemarkText] = useState('');
    const [isSaving, setIsSaving] = useState(false);

    useEffect(() => {
        if (item) {
            setRemarkText(item.remark || '');
        }
    }, [item]);

    if (!item) return null;

    const handleSaveRemark = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSaving(true);

        try {
            const res = await fetch('/mobile/jks-salesman/non-jks/remark', {
                method: 'POST',
                credentials: 'same-origin',
                headers: getCsrfHeaders(),
                body: JSON.stringify({
                    distributor_code: item.distributor_code,
                    customer_code: item.customer_code,
                    remark: remarkText.trim(),
                }),
            });

            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.error || data.message || 'Gagal menyimpan catatan.');
            }

            onRemarkSaved(item.id, data.remark ?? remarkText.trim(), data.message || 'Catatan berhasil disimpan!');
            onClose();
        } catch (err: any) {
            onError(err.message || 'Terjadi kesalahan sistem.');
        } finally {
            setIsSaving(false);
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
            <div className="relative bg-white rounded-t-[32px] shadow-2xl w-full max-w-2xl md:max-w-4xl mx-auto max-h-[90vh] flex flex-col animate-slide-up overflow-hidden z-10">
                {/* Header */}
                <div className="flex items-center justify-between p-4 px-5 border-b border-slate-100 bg-slate-50/70">
                    <div className="flex items-center gap-2.5 min-w-0">
                        <div className="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <BuildingStorefrontIcon className="w-5 h-5" />
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-sm font-black text-slate-900 truncate" title={item.customer_name}>
                                {item.customer_name}
                            </h3>
                            <p className="text-xs font-bold text-slate-400 font-mono truncate">
                                #{item.customer_code} • {item.distributor_name}
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

                {/* Body */}
                <div className="flex-1 overflow-y-auto p-5 space-y-4">
                    {/* 1. Informasi Toko & Kode PRC */}
                    <div className="bg-slate-50 border border-slate-100 rounded-2xl p-3.5 space-y-2.5">
                        <div className="text-xs text-slate-600 leading-relaxed">
                            <span className="font-bold text-slate-800">Alamat: </span>
                            {item.alamat || '-'}
                        </div>

                        <div className="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/70 text-xs">
                            <div>
                                <span className="text-2xs font-bold uppercase text-slate-400 block">
                                    Kode PRC (Eska)
                                </span>
                                {item.customer_eska ? (
                                    <span className="font-mono font-bold text-emerald-700">
                                        {item.customer_eska}
                                    </span>
                                ) : (
                                    <span className="text-2xs font-semibold text-rose-600 italic">
                                        Kosong (Diisi saat Approval)
                                    </span>
                                )}
                            </div>
                            <div className="text-right">
                                <span className="text-2xs font-bold uppercase text-slate-400 block">
                                    Rata-rata Omset 2026
                                </span>
                                <span className="font-mono font-black text-emerald-700 text-xs sm:text-sm">
                                    Rp {Math.round(item.avg_value_net).toLocaleString('id-ID')}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* 2. Histori Omset 6 Bulan Terakhir */}
                    <div>
                        <div className="flex items-center gap-1.5 mb-2">
                            <ChartBarIcon className="w-4 h-4 text-indigo-600" />
                            <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">
                                Histori Transaksi 6 Bulan Terakhir
                            </h4>
                        </div>
                        <div className="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            {historyLabels.map((label, idx) => {
                                const val = item.history?.[idx] || 0;
                                return (
                                    <div
                                        key={label}
                                        className={`rounded-xl p-2.5 border ${
                                            val > 0
                                                ? 'bg-emerald-50/50 border-emerald-200/70'
                                                : 'bg-slate-50 border-slate-100'
                                        }`}
                                    >
                                        <span className="text-2xs font-bold uppercase text-slate-400 block">
                                            {label}
                                        </span>
                                        <span
                                            className={`font-mono text-xs font-extrabold block mt-0.5 truncate ${
                                                val > 0 ? 'text-emerald-800' : 'text-slate-300'
                                            }`}
                                        >
                                            {val > 0 ? `Rp ${Math.round(val).toLocaleString('id-ID')}` : '-'}
                                        </span>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* 3. Form Catatan / Remark Outlet Non-JKS */}
                    <form onSubmit={handleSaveRemark} className="space-y-2.5 pt-1 border-t border-slate-100">
                        <div className="flex items-center gap-1.5">
                            <ChatBubbleBottomCenterTextIcon className="w-4 h-4 text-amber-600" />
                            <label className="text-xs font-black uppercase tracking-wider text-slate-700">
                                Catatan / Remark Outlet Non-JKS
                            </label>
                        </div>
                        <textarea
                            value={remarkText}
                            onChange={(e) => setRemarkText(e.target.value)}
                            placeholder="Tuliskan alasan mengapa outlet ini belum masuk JKS atau catatan lapangan lainnya..."
                            rows={3}
                            className="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all resize-none"
                        />

                        <div className="flex items-center gap-2 pt-1">
                            <button
                                type="submit"
                                disabled={isSaving}
                                className="flex-1 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1.5 active:scale-[0.98]"
                            >
                                {isSaving ? (
                                    <>
                                        <ArrowPathIcon className="w-4 h-4 animate-spin" />
                                        Menyimpan...
                                    </>
                                ) : (
                                    <>
                                        <CheckIcon className="w-4 h-4 stroke-[2.5]" />
                                        Simpan Remark
                                    </>
                                )}
                            </button>

                            {!item.pending_approval_id && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        onClose();
                                        onAddJks(item);
                                    }}
                                    className="flex-1 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1.5 shadow-sm active:scale-[0.98]"
                                >
                                    <PlusCircleIcon className="w-4 h-4 stroke-[2.2]" />
                                    Tambah ke JKS
                                </button>
                            )}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
