import React, { useEffect } from 'react';
import { SpvData } from '../types';
import { formatRupiah, formatNumber } from '../constants';
import { XMarkIcon, UserIcon, BuildingOffice2Icon, SparklesIcon } from '@heroicons/react/24/solid';

interface SpvDetailSheetProps {
    isOpen: boolean;
    onClose: () => void;
    spv: SpvData | null;
}

export default function SpvDetailSheet({ isOpen, onClose, spv }: SpvDetailSheetProps) {
    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    if (!isOpen || !spv) return null;

    const cabangList = Object.values(spv.cabangs || {});
    const isSoQualified = spv.pencapaian_persen >= 80;
    const isVacant = spv.supervisor_name?.toLowerCase().includes('vacant');

    return (
        <div className="fixed top-16 md:top-20 inset-x-0 bottom-0 z-[100] flex flex-col justify-end">
            {/* Clickable Backdrop */}
            <div
                className="fixed top-16 md:top-20 inset-x-0 bottom-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                onClick={onClose}
            />

            {/* Sheet Container - Flush with Header (Navbar) */}
            <div className="relative z-10 w-full max-w-2xl md:max-w-4xl mx-auto bg-white h-full shadow-2xl overflow-hidden flex flex-col animate-slide-up border-x border-gray-200/80">
                {/* Header */}
                <div className="px-5 py-3.5 border-b border-gray-200/80 flex items-center justify-between bg-slate-50 shrink-0">
                    <div className="flex items-center gap-3 min-w-0">
                        <div
                            className={`w-10 h-10 rounded-2xl text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0 ${
                                isVacant ? 'bg-rose-500' : 'bg-teal-600'
                            }`}
                        >
                            <UserIcon className="w-5 h-5" />
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-sm font-bold text-gray-900 leading-tight truncate">
                                {spv.supervisor_name}
                            </h3>
                            <p className="text-xs text-gray-500 mt-0.5 truncate">
                                Kode: {spv.supervisor_code} • {cabangList.length} Cabang Binaan
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-700 shadow-sm transition-colors shrink-0"
                    >
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 p-4 overflow-y-auto space-y-4">
                    {/* Big Summary Card: Split 70% Transfer / 30% Tabungan */}
                    <div className="bg-gradient-to-br from-teal-700 to-slate-900 p-4 rounded-2xl text-white shadow-md">
                        <span className="text-[11px] text-white/70 block uppercase font-medium">
                            Estimasi Transfer Berjalan (70% THP)
                        </span>
                        <span className="text-2xl font-extrabold text-white mt-0.5 block">
                            {formatRupiah(spv.transfer_70)}
                        </span>

                        <div className="mt-3 pt-3 border-t border-white/10 grid grid-cols-2 gap-2 text-xs">
                            <div className="bg-white/10 p-2 rounded-xl">
                                <span className="text-white/70 text-[10px] block">30% Tabungan</span>
                                <span className="font-bold text-amber-300">{formatRupiah(spv.tabungan_30)}</span>
                            </div>
                            <div className="bg-white/10 p-2 rounded-xl">
                                <span className="text-white/70 text-[10px] block">Total Insentif SPV</span>
                                <span className="font-bold text-white">{formatRupiah(spv.total_all_insentif)}</span>
                            </div>
                        </div>
                    </div>

                    {/* 4 Pilar SPV Breakdown */}
                    <div className="space-y-2 text-xs">
                        <h4 className="font-bold text-gray-700 text-xs uppercase tracking-wider px-1">
                            Rincian 4 Komponen Insentif SPV
                        </h4>

                        {/* 1. Sell Out */}
                        <div className="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div className="flex items-center justify-between">
                                <span className="font-bold text-gray-800">1. Sell Out (SO) Gabungan</span>
                                <span className="font-extrabold text-sm text-indigo-700">
                                    {formatRupiah(spv.ins_so)}
                                </span>
                            </div>
                            <div className="mt-1.5 flex items-center justify-between text-[11px] text-gray-500">
                                <span>Tgt: {formatRupiah(spv.total_target_reguler)}</span>
                                <span>Real: {formatRupiah(spv.total_aktual_so)}</span>
                                <span
                                    className={`font-bold px-1.5 py-0.5 rounded ${
                                        spv.pencapaian_persen >= 100
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : spv.pencapaian_persen >= 80
                                            ? 'bg-amber-100 text-amber-700'
                                            : 'bg-rose-100 text-rose-700'
                                    }`}
                                >
                                    {formatNumber(spv.pencapaian_persen)}%
                                </span>
                            </div>
                        </div>

                        {/* 2. VTKP */}
                        <div className="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div className="flex items-center justify-between">
                                <div>
                                    <span className="font-bold text-gray-800 block">2. VTKP Produk Fokus Pcs</span>
                                    <span className={`text-[11px] font-semibold ${isSoQualified ? 'text-purple-600' : 'text-rose-600'}`}>
                                        {isSoQualified ? 'Syarat SO ≥ 80% Terpenuhi' : 'Hangus (Pencapaian SO < 80%)'}
                                    </span>
                                </div>
                                <span className="font-extrabold text-sm text-purple-700">
                                    {formatRupiah(spv.total_insentif_vtkp)}
                                </span>
                            </div>
                        </div>

                        {/* 3. RWO */}
                        <div className="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                            <div>
                                <span className="font-bold text-gray-800 block">3. Reward Outlet (RWO)</span>
                                <span className="text-[11px] text-gray-500">
                                    {spv.total_rwo_achieve} dari {spv.total_rwo_peserta} toko ({formatNumber(spv.rwo_achieve_pct)}%)
                                </span>
                            </div>
                            <span className="font-extrabold text-sm text-amber-700">
                                {formatRupiah(spv.insentif_rwo)}
                            </span>
                        </div>

                        {/* 4. IPT */}
                        <div className="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                            <div>
                                <span className="font-bold text-gray-800 block">4. IPT Gabungan Wilayah</span>
                                <span className="text-[11px] text-gray-500">
                                    Rasio: {spv.ipt} ({formatNumber(spv.total_ipt_sku)} SKU / {formatNumber(spv.total_ipt_ec)} EC)
                                </span>
                            </div>
                            <span className="font-extrabold text-sm text-sky-700">
                                {formatRupiah(spv.insentif_ipt)}
                            </span>
                        </div>
                    </div>

                    {/* Cabang & VTKP Breakdown */}
                    <div className="space-y-2.5 text-xs">
                        <h4 className="font-bold text-gray-700 text-xs uppercase tracking-wider px-1 flex items-center gap-1.5">
                            <BuildingOffice2Icon className="w-4 h-4 text-teal-600" />
                            <span>Rincian Per Cabang & Produk Fokus (VTKP)</span>
                        </h4>
                        <div className="space-y-2.5">
                            {cabangList.map((cab, idx) => {
                                const cabTargetSo = cab.distributors?.[0]?.target_so || 0;
                                const cabActualSo = (cab.distributors || []).reduce(
                                    (acc: number, d: any) => acc + (Number(d.aktual_so) || 0),
                                    0
                                );
                                const cabAchPct = cabTargetSo > 0 ? (cabActualSo / cabTargetSo) * 100 : 0;
                                const vtkpEntries = Object.entries(cab.vtkp_achievements || {});

                                return (
                                    <div key={idx} className="p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <span className="font-extrabold text-gray-900 block">
                                                    Cabang {cab.cabang}
                                                </span>
                                                <span className="text-[11px] text-gray-500">{cab.area_name}</span>
                                            </div>
                                            <span
                                                className={`text-[11px] font-bold px-2 py-0.5 rounded-lg ${
                                                    cabAchPct >= 100
                                                        ? 'bg-emerald-100 text-emerald-700'
                                                        : cabAchPct >= 80
                                                        ? 'bg-amber-100 text-amber-700'
                                                        : 'bg-rose-100 text-rose-700'
                                                }`}
                                            >
                                                SO: {formatNumber(cabAchPct)}%
                                            </span>
                                        </div>

                                        {/* Target vs Aktual SO Cabang */}
                                        <div className="grid grid-cols-2 gap-2 bg-white p-2 rounded-xl border border-gray-100 text-[11px]">
                                            <div>
                                                <span className="text-gray-400 block">Target SO Cabang</span>
                                                <span className="font-bold text-gray-800">{formatRupiah(cabTargetSo)}</span>
                                            </div>
                                            <div>
                                                <span className="text-gray-400 block">Aktual SO Cabang</span>
                                                <span className="font-bold text-teal-700">{formatRupiah(cabActualSo)}</span>
                                            </div>
                                        </div>

                                        {/* Distributors */}
                                        {cab.distributors && cab.distributors.length > 0 && (
                                            <div className="space-y-1">
                                                {cab.distributors.map((d: any, dIdx: number) => (
                                                    <div
                                                        key={dIdx}
                                                        className="text-[11px] text-gray-600 flex items-center justify-between px-1"
                                                    >
                                                        <span className="truncate pr-2">{d.distributor_name}</span>
                                                        <span className="font-semibold shrink-0">{formatRupiah(d.aktual_so)}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* VTKP per Produk Fokus */}
                                        {vtkpEntries.length > 0 && (
                                            <div className="pt-2 border-t border-gray-200/70 space-y-1.5">
                                                <div className="flex items-center justify-between text-[11px] font-bold text-purple-800">
                                                    <span className="flex items-center gap-1">
                                                        <SparklesIcon className="w-3.5 h-3.5 text-purple-600" />
                                                        VTKP Cabang {cab.cabang}
                                                    </span>
                                                    <span>{formatRupiah(cab.total_insentif_vtkp)}</span>
                                                </div>
                                                <div className="space-y-1">
                                                    {vtkpEntries.map(([headerName, ach], hIdx) => (
                                                        <div
                                                            key={hIdx}
                                                            className="bg-white px-2.5 py-1.5 rounded-lg border border-gray-100 flex items-center justify-between text-[11px]"
                                                        >
                                                            <div>
                                                                <span className="font-bold text-gray-800">{headerName}</span>
                                                                <span className="text-gray-400 ml-1.5">
                                                                    Tgt: {formatNumber(ach.target)} | Real: {formatNumber(ach.real)} ({formatNumber(ach.growth)}%)
                                                                </span>
                                                            </div>
                                                            <span className="font-bold text-purple-700 shrink-0 ml-2">
                                                                {formatRupiah(ach.insentif)}
                                                            </span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <div className="p-4 border-t border-gray-100 bg-gray-50 flex justify-end shrink-0">
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-full py-2.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition-all"
                    >
                        Tutup Rincian
                    </button>
                </div>
            </div>
        </div>
    );
}
