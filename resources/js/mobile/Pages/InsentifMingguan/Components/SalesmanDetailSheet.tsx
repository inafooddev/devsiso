import React, { useState, useEffect } from 'react';
import { SalesmanSeData } from '../types';
import { formatRupiah, formatMinusRupiah, formatNumber, SFA_MIN_COMPLIANCE } from '../constants';
import {
    XMarkIcon,
    UserIcon,
    ShieldCheckIcon,
    ShieldExclamationIcon,
    SparklesIcon,
} from '@heroicons/react/24/solid';
import VtkpBreakdownSheet from './VtkpBreakdownSheet';

interface SalesmanDetailSheetProps {
    isOpen: boolean;
    onClose: () => void;
    salesman: SalesmanSeData | null;
}

export default function SalesmanDetailSheet({ isOpen, onClose, salesman }: SalesmanDetailSheetProps) {
    const [isVtkpDetailOpen, setIsVtkpDetailOpen] = useState(false);

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

    if (!isOpen || !salesman) return null;

    const isSfaWarning = salesman.sfa.sfa_pct < SFA_MIN_COMPLIANCE;
    const isValueQualified = salesman.value.ach_pct >= 60;

    return (
        <>
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
                            <div className="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                <UserIcon className="w-5 h-5" />
                            </div>
                            <div className="min-w-0">
                                <h3 className="text-sm font-bold text-gray-900 leading-tight truncate">
                                    {salesman.salesman_name}
                                </h3>
                                <p className="text-xs text-gray-500 mt-0.5 truncate">
                                    {salesman.salesman_code} • {salesman.cabang} ({salesman.distributor})
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

                    {/* Scrollable Content */}
                    <div className="flex-1 p-4 overflow-y-auto space-y-3.5">
                        {/* THP Summary Box */}
                        <div className="bg-gradient-to-br from-indigo-700 to-indigo-950 p-4 rounded-2xl text-white shadow-md relative overflow-hidden">
                            <span className="text-[11px] text-white/70 block uppercase font-medium">
                                Estimasi Take Home Pay (THP)
                            </span>
                            <span className="text-2xl font-extrabold text-white mt-0.5 block">
                                {formatRupiah(salesman.totals.thp)}
                            </span>

                            <div className="mt-3 pt-3 border-t border-white/10 grid grid-cols-3 gap-2 text-xs">
                                <div>
                                    <span className="text-white/60 text-[10px] block">Total Kotor</span>
                                    <span className="font-bold">{formatRupiah(salesman.totals.kotor)}</span>
                                </div>
                                <div>
                                    <span className="text-white/60 text-[10px] block">Setelah SFA</span>
                                    <span className="font-bold text-amber-300">{formatRupiah(salesman.totals.estimasi)}</span>
                                </div>
                                <div>
                                    <span className="text-white/60 text-[10px] block">PPh 5%</span>
                                    <span className="font-bold text-rose-300">{formatMinusRupiah(salesman.totals.pph)}</span>
                                </div>
                            </div>
                        </div>

                        {/* SFA Warning Banner if penalized */}
                        {isSfaWarning ? (
                            <div className="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2.5 text-xs text-rose-800">
                                <ShieldExclamationIcon className="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
                                <div>
                                    <span className="font-bold">Penalti Kepatuhan SFA Aktif (-75%)!</span>
                                    <p className="text-[11px] text-rose-700 mt-0.5 leading-relaxed">
                                        SFA saat ini <strong>{salesman.sfa.sfa_pct}%</strong> (&lt; 95%). Total insentif kotor{' '}
                                        <strong>{formatRupiah(salesman.totals.kotor)}</strong> terpotong penalti 75% menjadi{' '}
                                        <strong>{formatRupiah(salesman.totals.estimasi)}</strong>.
                                    </p>
                                </div>
                            </div>
                        ) : (
                            <div className="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2 text-xs text-emerald-800">
                                <ShieldCheckIcon className="w-4 h-4 text-emerald-600 shrink-0" />
                                <span className="font-semibold">
                                    Kepatuhan SFA Aman: {salesman.sfa.sfa_pct}% (Bebas Penalti)
                                </span>
                            </div>
                        )}

                        {/* 5 Pillars Breakdown Cards */}
                        <div className="space-y-2 text-xs">
                            <h4 className="font-bold text-gray-700 text-xs uppercase tracking-wider px-1">
                                Rincian 5 Komponen Insentif SE
                            </h4>

                            {/* 1. Value Reguler */}
                            <div className="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div className="flex items-center justify-between">
                                    <span className="font-bold text-gray-800">1. Value Reguler (SO)</span>
                                    <span className="font-extrabold text-sm text-indigo-700">
                                        {formatRupiah(salesman.value.insentif)}
                                    </span>
                                </div>
                                <div className="mt-1.5 flex items-center justify-between text-[11px] text-gray-500">
                                    <span>Target: {formatRupiah(salesman.value.target)}</span>
                                    <span>Real: {formatRupiah(salesman.value.actual)}</span>
                                    <span
                                        className={`font-bold px-1.5 py-0.5 rounded ${
                                            salesman.value.ach_pct >= 100
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : salesman.value.ach_pct >= 60
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-rose-100 text-rose-700'
                                        }`}
                                    >
                                        {salesman.value.ach_pct}%
                                    </span>
                                </div>
                            </div>

                            {/* 2. VTKP Produk Fokus */}
                            <div className="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <span className="font-bold text-gray-800 block">2. VTKP Produk Fokus</span>
                                        <span className={`text-[11px] font-medium ${isValueQualified ? 'text-gray-500' : 'text-rose-600'}`}>
                                            {isValueQualified
                                                ? `${salesman.vtkp.details?.length || 0} kelompok produk fokus`
                                                : 'Gugur syarat (Value SO < 60%)'}
                                        </span>
                                    </div>
                                    <span className="font-extrabold text-sm text-purple-700">
                                        {formatRupiah(salesman.vtkp.total_insentif)}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setIsVtkpDetailOpen(true)}
                                    className="mt-2 w-full py-1.5 px-3 bg-purple-50 hover:bg-purple-100 text-[11px] font-bold text-purple-700 rounded-lg flex items-center justify-center gap-1.5 border border-purple-200/60 transition-colors"
                                >
                                    <SparklesIcon className="w-3.5 h-3.5" /> Lihat Rincian Per Kelompok Produk &rarr;
                                </button>
                            </div>

                            {/* 3. Effective Call */}
                            <div className="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                                <div>
                                    <span className="font-bold text-gray-800 block">3. Kunjungan Efektif (EC)</span>
                                    <span className="text-[11px] text-gray-500">
                                        {salesman.ec.persen_ec}% EC ({salesman.ec.ec}/{salesman.ec.ac}) • {salesman.ec.ec_harian} toko/hr • RO: {salesman.ec.ro}
                                    </span>
                                </div>
                                <span className="font-extrabold text-sm text-amber-700 shrink-0 ml-2">
                                    {formatRupiah(salesman.ec.insentif)}
                                </span>
                            </div>

                            {/* 4. IPT */}
                            <div className="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                                <div>
                                    <span className="font-bold text-gray-800 block">4. Item Per Transaksi (IPT)</span>
                                    <span className="text-[11px] text-gray-500">
                                        Rasio IPT: {salesman.ipt.ipt_val} ({formatNumber(salesman.ipt.sku)} SKU / {formatNumber(salesman.ipt.ec)} EC)
                                    </span>
                                </div>
                                <span className="font-extrabold text-sm text-sky-700 shrink-0 ml-2">
                                    {formatRupiah(salesman.ipt.insentif)}
                                </span>
                            </div>

                            {/* 5. Kepatuhan SFA */}
                            <div className="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                                <div>
                                    <span className="font-bold text-gray-800 block">5. Kepatuhan SFA</span>
                                    <span className="text-[11px] text-gray-500">
                                        Realisasi AC: {salesman.sfa.ac} / Rencana PC: {salesman.sfa.pc}
                                    </span>
                                </div>
                                <span
                                    className={`font-bold text-xs px-2 py-0.5 rounded-md ${
                                        isSfaWarning ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'
                                    }`}
                                >
                                    {salesman.sfa.sfa_pct}%
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Footer */}
                    <div className="p-4 border-t border-gray-100 bg-gray-50 flex justify-end shrink-0">
                        <button
                            type="button"
                            onClick={onClose}
                            className="w-full py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-all"
                        >
                            Tutup Rincian
                        </button>
                    </div>
                </div>
            </div>

            {/* Sub-modal: VTKP detail */}
            <VtkpBreakdownSheet
                isOpen={isVtkpDetailOpen}
                onClose={() => setIsVtkpDetailOpen(false)}
                salesmanName={salesman.salesman_name}
                details={salesman.vtkp.details || []}
                totalInsentif={salesman.vtkp.total_insentif}
            />
        </>
    );
}
