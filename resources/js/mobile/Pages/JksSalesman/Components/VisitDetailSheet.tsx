import React, { useState, useEffect } from 'react';
import { JksItem, VisitSummaryItem } from '../constants';
import {
    XMarkIcon,
    BuildingStorefrontIcon,
    CalendarIcon,
    ShoppingBagIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationCircleIcon,
    ClockIcon,
    ArrowPathIcon,
} from '@heroicons/react/24/outline';

interface VisitDetailSheetProps {
    item: JksItem | null;
    visit?: VisitSummaryItem;
    distributorCode: string;
    bulan: string;
    onClose: () => void;
}

interface HistoryItem {
    ID: string | number;
    TANGGAL: string;
    salesman_code?: string;
    salesman_name?: string;
    flag_pjp?: string;
    flag_visit?: string;
    flag_ec?: string;
    order_val?: number;
    order_qty?: number;
    total_item?: number;
    time_in?: string;
    time_out?: string;
    reason_type?: string;
    reason_code?: string;
    reason_desc?: string;
    reason_note?: string;
}

export default function VisitDetailSheet({
    item,
    visit,
    distributorCode,
    bulan,
    onClose,
}: VisitDetailSheetProps) {
    const [history, setHistory] = useState<HistoryItem[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!item) return;

        const targetCode = item.customer_code || item.uniq_kode;
        const uniqParam = item.uniq_kode ? `&uniq_kode=${encodeURIComponent(item.uniq_kode)}` : '';
        setIsLoading(true);
        setError(null);

        fetch(`/mobile/jks-salesman/visit-detail/${encodeURIComponent(targetCode)}?distributor=${encodeURIComponent(distributorCode)}&bulan=${encodeURIComponent(bulan)}${uniqParam}`)
            .then(async (res) => {
                if (!res.ok) throw new Error('Gagal memuat riwayat kunjungan.');
                return res.json();
            })
            .then((data) => {
                setHistory(data.history || []);
                setIsLoading(false);
            })
            .catch((err) => {
                setError(err.message || 'Terjadi kesalahan sistem.');
                setIsLoading(false);
            });
    }, [item, distributorCode, bulan]);

    if (!item) return null;

    const totalVisit = visit?.total_visit || 0;
    const totalEc = visit?.total_ec || 0;
    const totalOrderVal = visit?.total_order_val || 0;
    const strikeRate = totalVisit > 0 ? Math.round((totalEc / totalVisit) * 100) : 0;

    return (
        <div className="fixed inset-0 z-50 flex flex-col justify-end">
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity animate-fade-in"
                onClick={onClose}
            />

            {/* Modal Container */}
            <div className="relative bg-white rounded-t-[32px] shadow-2xl w-full max-h-[85vh] flex flex-col animate-slide-up overflow-hidden z-10">
                {/* Header */}
                <div className="flex items-center justify-between p-4 px-5 border-b border-slate-100 bg-slate-50/70">
                    <div className="flex items-center gap-2.5 min-w-0">
                        <div className="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center shrink-0">
                            <BuildingStorefrontIcon className="w-5 h-5" />
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-sm font-black text-slate-900 truncate" title={item.customer_name}>
                                {item.customer_name}
                            </h3>
                            <p className="text-xs font-bold text-slate-400 font-mono">
                                {item.customer_code || item.uniq_kode}
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

                {/* Body Content */}
                <div className="flex-1 overflow-y-auto p-5 space-y-4">
                    {/* Profil Toko & Navigasi */}
                    <div className="bg-slate-50 border border-slate-100 rounded-2xl p-3.5 space-y-2">
                        <div className="text-xs text-slate-600 leading-relaxed">
                            <span className="font-bold text-slate-800">Alamat:</span> {item.customer_address || '-'}
                            {(item.kecamatan || item.desa) && (
                                <span className="text-slate-400 block text-xs mt-0.5">
                                    {[item.desa, item.kecamatan].filter(Boolean).join(', ')}
                                </span>
                            )}
                        </div>

                        <div className="flex items-center justify-between text-xs pt-1 border-t border-slate-200/60">
                            <div>
                                <span className="text-slate-400">Pilar:</span>{' '}
                                <strong className="text-indigo-600">{item.pilar || '-'}</strong>
                            </div>
                            <div>
                                <span className="text-slate-400">Target:</span>{' '}
                                <strong className="text-slate-800">
                                    {item.target ? `Rp ${Number(item.target).toLocaleString('id-ID')}` : '-'}
                                </strong>
                            </div>
                        </div>

                        {item.latitude && item.longitude && Number(item.latitude) !== 0 && (
                            <div className="pt-1">
                                <a
                                    href={`https://www.google.com/maps/dir/?api=1&destination=${item.latitude},${item.longitude}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="w-full inline-flex items-center justify-center gap-2 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-slate-800 transition-colors"
                                >
                                    📍 Buka Rute di Google Maps
                                </a>
                            </div>
                        )}
                    </div>

                    {/* Stat Cards Grid */}
                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div className="bg-slate-50 border border-slate-100 rounded-2xl p-3 text-center">
                            <span className="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kunjungan</span>
                            <span className="text-lg font-black text-slate-800 leading-tight mt-0.5 block">{totalVisit}x</span>
                        </div>
                        <div className="bg-emerald-50/60 border border-emerald-100 rounded-2xl p-3 text-center">
                            <span className="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Order (EC)</span>
                            <span className="text-lg font-black text-emerald-700 leading-tight mt-0.5 block">{totalEc}x</span>
                        </div>
                        <div className="bg-indigo-50/60 border border-indigo-100 rounded-2xl p-3 text-center">
                            <span className="text-xs font-bold text-indigo-600 uppercase tracking-wider block">Strike Rate</span>
                            <span className="text-lg font-black text-indigo-700 leading-tight mt-0.5 block">{strikeRate}%</span>
                        </div>
                        <div className="bg-purple-50/60 border border-purple-100 rounded-2xl p-3 text-center">
                            <span className="text-xs font-bold text-purple-600 uppercase tracking-wider block">Omset Toko</span>
                            <span className="text-xs font-black text-purple-800 leading-tight mt-1.5 block truncate">
                                Rp {totalOrderVal.toLocaleString('id-ID')}
                            </span>
                        </div>
                    </div>

                    {/* Section Title */}
                    <div className="flex items-center justify-between pt-1">
                        <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">
                            Riwayat Kunjungan Bulan Ini
                        </h4>
                        <span className="text-xs font-bold text-slate-400">
                            {history.length} Log Terdata
                        </span>
                    </div>

                    {/* Loading State */}
                    {isLoading && (
                        <div className="py-12 flex flex-col items-center justify-center text-center">
                            <ArrowPathIcon className="w-8 h-8 text-indigo-600 animate-spin mb-2" />
                            <p className="text-xs font-bold text-slate-500">Memuat log kunjungan...</p>
                        </div>
                    )}

                    {/* Error State */}
                    {error && (
                        <div className="p-4 bg-rose-50 border border-rose-100 rounded-2xl text-center text-rose-700 text-xs font-medium">
                            {error}
                        </div>
                    )}

                    {/* Empty State */}
                    {!isLoading && !error && history.length === 0 && (
                        <div className="py-10 border border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center text-center p-4">
                            <ClockIcon className="w-10 h-10 text-slate-300 mb-2" />
                            <h5 className="text-xs font-black text-slate-700 uppercase tracking-wide">Belum Ada Kunjungan</h5>
                            <p className="text-xs text-slate-400 mt-1 max-w-xs">
                                Toko ini belum tercatat melakukan kunjungan lapangan pada periode bulan ini.
                            </p>
                        </div>
                    )}

                    {/* Timeline Log List */}
                    {!isLoading && !error && history.length > 0 && (
                        <div className="space-y-3">
                            {history.map((log, idx) => {
                                const isEc = log.flag_ec === 'Y';
                                const isRoute = log.flag_pjp === 'R';
                                const hasOrder = Number(log.order_val) > 0;

                                return (
                                    <div
                                        key={log.ID || idx}
                                        className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-2.5"
                                    >
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <CalendarIcon className="w-4 h-4 text-slate-400" />
                                                <span className="text-xs font-black text-slate-800">
                                                    {log.TANGGAL ? new Date(log.TANGGAL).toLocaleDateString('id-ID', {
                                                        weekday: 'short',
                                                        day: 'numeric',
                                                        month: 'short',
                                                        year: 'numeric'
                                                    }) : '-'}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-1.5">
                                                <span className={`px-2 py-0.5 rounded-md text-xs font-bold ${
                                                    isRoute ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-amber-50 text-amber-700 border border-amber-100'
                                                }`}>
                                                    {isRoute ? 'Rute PJP' : 'Non-Rute'}
                                                </span>
                                                <span className={`px-2 py-0.5 rounded-md text-xs font-bold flex items-center gap-1 ${
                                                    isEc ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-500'
                                                }`}>
                                                    {isEc ? <CheckCircleIcon className="w-3 h-3" /> : <XCircleIcon className="w-3 h-3" />}
                                                    {isEc ? 'Order (EC)' : 'No Order'}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Waktu & Sales */}
                                        <div className="text-xs text-slate-500 flex items-center justify-between border-t border-slate-50 pt-2">
                                            <span>
                                                Jam: <strong className="text-slate-700">{log.time_in || '-'}</strong> s/d <strong className="text-slate-700">{log.time_out || '-'}</strong>
                                            </span>
                                            {log.salesman_name && (
                                                <span className="text-slate-400 truncate max-w-[150px]">
                                                    Oleh: <strong className="text-slate-700">{log.salesman_name}</strong>
                                                </span>
                                            )}
                                        </div>

                                        {/* Nilai Order jika ada */}
                                        {hasOrder && (
                                            <div className="bg-emerald-50/50 rounded-xl p-2.5 flex items-center justify-between border border-emerald-100/60">
                                                <div className="flex items-center gap-1.5 text-emerald-800">
                                                    <ShoppingBagIcon className="w-4 h-4 text-emerald-600" />
                                                    <span className="text-xs font-bold">Total Nilai Pesanan:</span>
                                                </div>
                                                <span className="text-xs font-black text-emerald-700">
                                                    Rp {Number(log.order_val).toLocaleString('id-ID')}
                                                </span>
                                            </div>
                                        )}

                                        {/* Catatan / Alasan jika ada */}
                                        {(log.reason_desc || log.reason_note) && (
                                            <div className="bg-amber-50/60 rounded-xl p-2.5 border border-amber-100 text-xs text-amber-900 leading-relaxed">
                                                <div className="flex items-center gap-1 font-bold mb-0.5 text-amber-800">
                                                    <ExclamationCircleIcon className="w-3.5 h-3.5" />
                                                    Alasan: {log.reason_desc || 'Catatan'}
                                                </div>
                                                {log.reason_note && (
                                                    <p className="text-amber-700 pl-4">{log.reason_note}</p>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="p-4 border-t border-slate-100 bg-white">
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-full py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98]"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    );
}
