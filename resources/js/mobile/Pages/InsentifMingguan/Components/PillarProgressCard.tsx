import React, { useState } from 'react';
import { formatRupiah, formatNumber } from '../constants';
import {
    ChevronDownIcon,
    ChevronUpIcon,
    CurrencyDollarIcon,
    SparklesIcon,
    ShoppingBagIcon,
    TruckIcon,
    DevicePhoneMobileIcon,
    EyeIcon,
} from '@heroicons/react/24/outline';
import { SalesmanSeData } from '../types';

interface PillarProgressCardProps {
    data: SalesmanSeData;
    onOpenVtkpDetail?: () => void;
}

export default function PillarProgressCard({ data, onOpenVtkpDetail }: PillarProgressCardProps) {
    const [expandedPillar, setExpandedPillar] = useState<string | null>(null);

    const toggleExpand = (pillar: string) => {
        setExpandedPillar(expandedPillar === pillar ? null : pillar);
    };

    return (
        <div className="mx-4 space-y-3">
            <div className="flex items-center justify-between px-1">
                <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider">
                    Progress 5 Pilar Insentif SE
                </h3>
                <span className="text-[11px] text-gray-400 font-medium">Monitoring Berjalan</span>
            </div>

            {/* 1. Value Reguler */}
            <div className="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm transition-all">
                <div
                    className="flex items-center justify-between cursor-pointer"
                    onClick={() => toggleExpand('value')}
                >
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <CurrencyDollarIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-xs text-gray-500 font-medium block">1. Value Reguler</span>
                            <span className="text-sm font-bold text-gray-900">
                                {formatRupiah(data.value.insentif)}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <span
                            className={`text-xs font-extrabold px-2 py-0.5 rounded-lg ${
                                data.value.ach_pct >= 100
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : data.value.ach_pct >= 60
                                    ? 'bg-amber-50 text-amber-700'
                                    : 'bg-rose-50 text-rose-700'
                            }`}
                        >
                            {data.value.ach_pct}%
                        </span>
                        {expandedPillar === 'value' ? (
                            <ChevronUpIcon className="w-4 h-4 text-gray-400" />
                        ) : (
                            <ChevronDownIcon className="w-4 h-4 text-gray-400" />
                        )}
                    </div>
                </div>

                {/* Progress bar */}
                <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden mt-3">
                    <div
                        className={`h-full rounded-full transition-all duration-500 ${
                            data.value.ach_pct >= 100 ? 'bg-emerald-500' : data.value.ach_pct >= 60 ? 'bg-amber-500' : 'bg-rose-500'
                        }`}
                        style={{ width: `${Math.min(data.value.ach_pct, 100)}%` }}
                    />
                </div>

                {/* Collapsible detail */}
                {expandedPillar === 'value' && (
                    <div className="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Target Reguler</span>
                            <span className="font-bold text-gray-800">{formatRupiah(data.value.target)}</span>
                        </div>
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Realisasi (Actual)</span>
                            <span className="font-bold text-gray-800">{formatRupiah(data.value.actual)}</span>
                        </div>
                        <div className="col-span-2 text-[11px] text-gray-500 bg-indigo-50/50 p-2 rounded-xl border border-indigo-100">
                            💡 <em>Syarat minimal target Rp 250 Jt. Pencapaian &ge; 60% wajib agar insentif VTKP & EC cair.</em>
                        </div>
                    </div>
                )}
            </div>

            {/* 2. VTKP Produk Fokus */}
            <div className="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm transition-all">
                <div
                    className="flex items-center justify-between cursor-pointer"
                    onClick={() => toggleExpand('vtkp')}
                >
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <SparklesIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-xs text-gray-500 font-medium block">2. VTKP Produk Fokus</span>
                            <span className="text-sm font-bold text-gray-900">
                                {formatRupiah(data.vtkp.total_insentif)}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="text-xs font-semibold px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700">
                            {data.vtkp.details?.length || 0} Grup
                        </span>
                        {expandedPillar === 'vtkp' ? (
                            <ChevronUpIcon className="w-4 h-4 text-gray-400" />
                        ) : (
                            <ChevronDownIcon className="w-4 h-4 text-gray-400" />
                        )}
                    </div>
                </div>

                {expandedPillar === 'vtkp' && (
                    <div className="mt-3 pt-3 border-t border-gray-100 space-y-2 text-xs">
                        {data.vtkp.details && data.vtkp.details.length > 0 ? (
                            data.vtkp.details.map((v, i) => (
                                <div key={i} className="flex items-center justify-between p-2 rounded-xl bg-gray-50">
                                    <div>
                                        <span className="font-bold text-gray-800 block">{v.nama_header}</span>
                                        <span className="text-[11px] text-gray-500">
                                            Tgt: {formatNumber(v.target)} | Real: {formatNumber(v.real)} ({v.growth}%)
                                        </span>
                                    </div>
                                    <span className="font-extrabold text-purple-700">
                                        {formatRupiah(v.insentif)}
                                    </span>
                                </div>
                            ))
                        ) : (
                            <p className="text-gray-400 text-center py-2">Tidak ada data produk fokus</p>
                        )}

                        {onOpenVtkpDetail && (
                            <button
                                type="button"
                                onClick={onOpenVtkpDetail}
                                className="w-full mt-2 py-2 text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-xl flex items-center justify-center gap-1.5 transition-colors"
                            >
                                <EyeIcon className="w-4 h-4" /> Lihat Rincian Tabel VTKP
                            </button>
                        )}
                    </div>
                )}
            </div>

            {/* 3. Effective Call (EC) */}
            <div className="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm transition-all">
                <div
                    className="flex items-center justify-between cursor-pointer"
                    onClick={() => toggleExpand('ec')}
                >
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <TruckIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-xs text-gray-500 font-medium block">3. Kunjungan Efektif (EC)</span>
                            <span className="text-sm font-bold text-gray-900">
                                {formatRupiah(data.ec.insentif)}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="text-xs font-semibold px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700">
                            {data.ec.persen_ec}% EC
                        </span>
                        {expandedPillar === 'ec' ? (
                            <ChevronUpIcon className="w-4 h-4 text-gray-400" />
                        ) : (
                            <ChevronDownIcon className="w-4 h-4 text-gray-400" />
                        )}
                    </div>
                </div>

                {expandedPillar === 'ec' && (
                    <div className="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Kunjungan Rata-rata Harian</span>
                            <span className="font-bold text-gray-800">{data.ec.ec_harian} Toko / Hari</span>
                        </div>
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Realisasi EC / AC</span>
                            <span className="font-bold text-gray-800">{data.ec.ec} / {data.ec.ac}</span>
                        </div>
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Total Toko (RO)</span>
                            <span className="font-bold text-gray-800">{data.ec.ro} Toko ({data.ec.frekuensi})</span>
                        </div>
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Pencapaian % EC</span>
                            <span className="font-bold text-amber-700">{data.ec.persen_ec}%</span>
                        </div>
                    </div>
                )}
            </div>

            {/* 4. IPT */}
            <div className="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm transition-all">
                <div
                    className="flex items-center justify-between cursor-pointer"
                    onClick={() => toggleExpand('ipt')}
                >
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                            <ShoppingBagIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-xs text-gray-500 font-medium block">4. Item Per Transaksi (IPT)</span>
                            <span className="text-sm font-bold text-gray-900">
                                {formatRupiah(data.ipt.insentif)}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="text-xs font-semibold px-2 py-0.5 rounded-lg bg-sky-50 text-sky-700">
                            IPT: {data.ipt.ipt_val}
                        </span>
                        {expandedPillar === 'ipt' ? (
                            <ChevronUpIcon className="w-4 h-4 text-gray-400" />
                        ) : (
                            <ChevronDownIcon className="w-4 h-4 text-gray-400" />
                        )}
                    </div>
                </div>

                {expandedPillar === 'ipt' && (
                    <div className="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Total SKU Terjual</span>
                            <span className="font-bold text-gray-800">{formatNumber(data.ipt.sku)} SKU</span>
                        </div>
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Total Transaksi (EC)</span>
                            <span className="font-bold text-gray-800">{formatNumber(data.ipt.ec)}</span>
                        </div>
                        <div className="col-span-2 text-[11px] text-gray-500 bg-sky-50/50 p-2 rounded-xl border border-sky-100">
                            Rasio: {data.ipt.ipt_val} SKU/Transaksi. Syarat mutlak RO &ge; 250 toko.
                        </div>
                    </div>
                )}
            </div>

            {/* 5. Kepatuhan SFA */}
            <div className="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm transition-all">
                <div
                    className="flex items-center justify-between cursor-pointer"
                    onClick={() => toggleExpand('sfa')}
                >
                    <div className="flex items-center gap-3">
                        <div className={`w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${data.sfa.penalty_applied ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'}`}>
                            <DevicePhoneMobileIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-xs text-gray-500 font-medium block">5. Kepatuhan SFA</span>
                            <span className="text-sm font-bold text-gray-900">
                                {data.sfa.sfa_pct}% Absen / Visit
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className={`text-xs font-bold px-2 py-0.5 rounded-lg ${data.sfa.penalty_applied ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'}`}>
                            {data.sfa.penalty_applied ? 'Kena Penalti' : 'Aman'}
                        </span>
                        {expandedPillar === 'sfa' ? (
                            <ChevronUpIcon className="w-4 h-4 text-gray-400" />
                        ) : (
                            <ChevronDownIcon className="w-4 h-4 text-gray-400" />
                        )}
                    </div>
                </div>

                {expandedPillar === 'sfa' && (
                    <div className="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Rencana Kunjungan (PC)</span>
                            <span className="font-bold text-gray-800">{data.sfa.pc}</span>
                        </div>
                        <div className="bg-gray-50 p-2 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Realisasi SFA (AC)</span>
                            <span className="font-bold text-gray-800">{data.sfa.ac}</span>
                        </div>
                        <div className="col-span-2 text-[11px] text-gray-600 p-2 rounded-xl bg-gray-50">
                            {data.sfa.penalty_applied ? (
                                <span className="text-rose-600 font-semibold">
                                    ⚠️ Kepatuhan di bawah 95%! Insentif terpotong 75% (hanya 25% yang cair).
                                </span>
                            ) : (
                                <span className="text-emerald-700 font-medium">
                                    ✅ Kepatuhan &ge; 95%. Seluruh hak insentif cair penuh.
                                </span>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
