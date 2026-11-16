import React, { useState } from 'react';
import { SpvData } from '../types';
import { formatRupiah, formatNumber } from '../constants';
import {
    ChevronDownIcon,
    ChevronUpIcon,
    CurrencyDollarIcon,
    SparklesIcon,
    ShoppingBagIcon,
    BuildingStorefrontIcon,
    BuildingOffice2Icon,
    BanknotesIcon,
} from '@heroicons/react/24/outline';

interface SpvPillarProgressCardProps {
    data: SpvData;
    onOpenDetail?: () => void;
}

export default function SpvPillarProgressCard({ data, onOpenDetail }: SpvPillarProgressCardProps) {
    const [expandedPillar, setExpandedPillar] = useState<string | null>(null);

    const toggleExpand = (pillar: string) => {
        setExpandedPillar(expandedPillar === pillar ? null : pillar);
    };

    const isSoQualified = data.pencapaian_persen >= 80;
    const cabangList = Object.values(data.cabangs || {});

    return (
        <div className="mx-4 space-y-3">
            <div className="flex items-center justify-between px-1">
                <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider">
                    Progress 4 Pilar Insentif SPV
                </h3>
                <span className="text-[11px] text-gray-400 font-medium">Monitoring Berjalan</span>
            </div>

            {/* 1. Sell Out (SO) Reguler */}
            <div className="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm transition-all">
                <div
                    className="flex items-center justify-between cursor-pointer"
                    onClick={() => toggleExpand('so')}
                >
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <CurrencyDollarIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-xs text-gray-500 font-medium block">1. Sell Out (SO) Gabungan</span>
                            <span className="text-sm font-bold text-gray-900">
                                {formatRupiah(data.ins_so)}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <span
                            className={`text-xs font-extrabold px-2 py-0.5 rounded-lg ${
                                data.pencapaian_persen >= 100
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : data.pencapaian_persen >= 90
                                    ? 'bg-amber-50 text-amber-700'
                                    : 'bg-rose-50 text-rose-700'
                            }`}
                        >
                            {formatNumber(data.pencapaian_persen)}%
                        </span>
                        {expandedPillar === 'so' ? (
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
                            data.pencapaian_persen >= 100
                                ? 'bg-emerald-500'
                                : data.pencapaian_persen >= 90
                                ? 'bg-amber-500'
                                : 'bg-rose-500'
                        }`}
                        style={{ width: `${Math.min(data.pencapaian_persen, 100)}%` }}
                    />
                </div>

                {expandedPillar === 'so' && (
                    <div className="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
                        <div className="bg-gray-50 p-2.5 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Total Target SO SPV</span>
                            <span className="font-bold text-gray-800">{formatRupiah(data.total_target_reguler)}</span>
                        </div>
                        <div className="bg-gray-50 p-2.5 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Total Aktual SO</span>
                            <span className="font-bold text-gray-800">{formatRupiah(data.total_aktual_so)}</span>
                        </div>
                        <div className="col-span-2 text-[11px] text-gray-500 bg-indigo-50/50 p-2 rounded-xl border border-indigo-100">
                            💡 <em>Pencapaian minimal 90% untuk mendapat insentif SO. Syarat mutlak &ge; 80% agar insentif VTKP cair.</em>
                        </div>
                    </div>
                )}
            </div>

            {/* 2. VTKP Produk Fokus Pcs */}
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
                            <span className="text-xs text-gray-500 font-medium block">2. VTKP Produk Fokus (Pcs)</span>
                            <span className="text-sm font-bold text-gray-900">
                                {formatRupiah(data.total_insentif_vtkp)}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className={`text-xs font-semibold px-2 py-0.5 rounded-lg ${isSoQualified ? 'bg-purple-50 text-purple-700' : 'bg-rose-50 text-rose-700'}`}>
                            {isSoQualified ? 'Syarat SO Lolos' : 'Hangus (SO < 80%)'}
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
                        <div className="bg-purple-50/50 p-2.5 rounded-xl border border-purple-100 text-[11px] text-purple-900">
                            Dihitung per selisih pcs (Real - Target) di setiap cabang binaan:
                            <br />• Growth &ge; 30%: <strong>Rp 600 / Pcs</strong>
                            <br />• Growth &ge; 20%: <strong>Rp 400 / Pcs</strong>
                            <br />• Growth &ge; 10%: <strong>Rp 250 / Pcs</strong>
                        </div>

                        {cabangList.map((cab, idx) => (
                            <div key={idx} className="bg-gray-50 p-2.5 rounded-xl">
                                <div className="flex items-center justify-between font-bold text-gray-800 mb-1">
                                    <span>Cabang: {cab.cabang}</span>
                                    <span className="text-purple-700">{formatRupiah(cab.total_insentif_vtkp)}</span>
                                </div>
                                <div className="space-y-1">
                                    {Object.entries(cab.vtkp_achievements || {}).map(([headerName, ach], hIdx) => (
                                        <div key={hIdx} className="flex items-center justify-between text-[11px] text-gray-600 pl-2 border-l-2 border-purple-200">
                                            <span>{headerName} ({formatNumber(ach.growth)}%)</span>
                                            <span className="font-semibold">{formatRupiah(ach.insentif)}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* 3. Reward Outlet (RWO) */}
            <div className="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm transition-all">
                <div
                    className="flex items-center justify-between cursor-pointer"
                    onClick={() => toggleExpand('rwo')}
                >
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <BuildingStorefrontIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-xs text-gray-500 font-medium block">3. Reward Outlet (RWO)</span>
                            <span className="text-sm font-bold text-gray-900">
                                {formatRupiah(data.insentif_rwo)}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="text-xs font-semibold px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700">
                            {formatNumber(data.rwo_achieve_pct)}% Capai
                        </span>
                        {expandedPillar === 'rwo' ? (
                            <ChevronUpIcon className="w-4 h-4 text-gray-400" />
                        ) : (
                            <ChevronDownIcon className="w-4 h-4 text-gray-400" />
                        )}
                    </div>
                </div>

                {expandedPillar === 'rwo' && (
                    <div className="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
                        <div className="bg-gray-50 p-2.5 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Total Potensi Peserta</span>
                            <span className="font-bold text-gray-800">{data.total_rwo_peserta} Toko</span>
                        </div>
                        <div className="bg-gray-50 p-2.5 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Toko Capai Target</span>
                            <span className="font-bold text-gray-800">{data.total_rwo_achieve} Toko</span>
                        </div>
                        <div className="col-span-2 text-[11px] text-gray-500 bg-amber-50/50 p-2 rounded-xl border border-amber-100">
                            Pencapaian: &ge;90% (Rp 900k), &ge;80% (Rp 700k), &ge;70% (Rp 500k).
                        </div>
                    </div>
                )}
            </div>

            {/* 4. IPT Gabungan */}
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
                            <span className="text-xs text-gray-500 font-medium block">4. IPT Gabungan Wilayah</span>
                            <span className="text-sm font-bold text-gray-900">
                                {formatRupiah(data.insentif_ipt)}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="text-xs font-semibold px-2 py-0.5 rounded-lg bg-sky-50 text-sky-700">
                            IPT: {data.ipt}
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
                        <div className="bg-gray-50 p-2.5 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Total SKU Terjual</span>
                            <span className="font-bold text-gray-800">{formatNumber(data.total_ipt_sku)} SKU</span>
                        </div>
                        <div className="bg-gray-50 p-2.5 rounded-xl">
                            <span className="text-[11px] text-gray-500 block">Total Kunjungan (EC)</span>
                            <span className="font-bold text-gray-800">{formatNumber(data.total_ipt_ec)} EC</span>
                        </div>
                        <div className="col-span-2 text-[11px] text-gray-500 bg-sky-50/50 p-2 rounded-xl border border-sky-100">
                            Rata-rata gabungan SKU/EC: &ge;12 (Rp 600k), &ge;8 (Rp 500k), &ge;7 (Rp 250k), &ge;5 (Rp 150k).
                        </div>
                    </div>
                )}
            </div>

            {/* 5. Cabang Binaan Card */}
            <div className="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm transition-all">
                <div
                    className="flex items-center justify-between cursor-pointer"
                    onClick={() => toggleExpand('cabangs')}
                >
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                            <BuildingOffice2Icon className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-xs text-gray-500 font-medium block">Cabang & Depo Binaan</span>
                            <span className="text-sm font-bold text-gray-900">
                                {cabangList.length} Cabang Aktif
                            </span>
                        </div>
                    </div>
                    {expandedPillar === 'cabangs' ? (
                        <ChevronUpIcon className="w-4 h-4 text-gray-400" />
                    ) : (
                        <ChevronDownIcon className="w-4 h-4 text-gray-400" />
                    )}
                </div>

                {expandedPillar === 'cabangs' && (
                    <div className="mt-3 pt-3 border-t border-gray-100 space-y-2 text-xs">
                        {cabangList.map((cab, idx) => (
                            <div key={idx} className="bg-gray-50 p-2.5 rounded-xl flex items-center justify-between">
                                <div>
                                    <span className="font-bold text-gray-800 block">{cab.cabang}</span>
                                    <span className="text-[11px] text-gray-500">{cab.area_name}</span>
                                </div>
                                <div className="text-right">
                                    <span className="text-[11px] text-gray-400 block">Insentif VTKP</span>
                                    <span className="font-bold text-purple-700">{formatRupiah(cab.total_insentif_vtkp)}</span>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* 6. Payment Split Box: 70% Transfer / 30% Tabungan */}
            <div className="bg-gradient-to-r from-teal-50 to-emerald-50 rounded-2xl p-4 border border-teal-200/80 shadow-sm">
                <div className="flex items-center gap-2 mb-2 text-teal-900 font-bold text-xs">
                    <BanknotesIcon className="w-4 h-4 text-teal-600" />
                    <span>Sistem Pencairan Insentif SPV (70 / 30)</span>
                </div>
                <div className="grid grid-cols-2 gap-2 text-xs">
                    <div className="bg-white/80 p-2.5 rounded-xl border border-teal-100">
                        <span className="text-[11px] text-emerald-800 block font-semibold">70% Transfer (THP)</span>
                        <span className="text-sm font-extrabold text-emerald-700">{formatRupiah(data.transfer_70)}</span>
                    </div>
                    <div className="bg-white/80 p-2.5 rounded-xl border border-amber-100">
                        <span className="text-[11px] text-amber-800 block font-semibold">30% Tabungan</span>
                        <span className="text-sm font-extrabold text-amber-700">{formatRupiah(data.tabungan_30)}</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
