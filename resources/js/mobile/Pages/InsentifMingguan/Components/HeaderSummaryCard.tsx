import React from 'react';
import { formatRupiah, formatMinusRupiah, getMonthName } from '../constants';
import { BanknotesIcon, ArrowTrendingUpIcon, ShieldCheckIcon, ShieldExclamationIcon } from '@heroicons/react/24/solid';

interface HeaderSummaryCardProps {
    title: string;
    periodMonth: string;
    thp: number;
    kotor: number;
    pph: number;
    isSpv?: boolean;
    tabungan30?: number;
    transfer70?: number;
    sfaPct?: number;
    sfaPenaltyApplied?: boolean;
    subtitle?: string;
}

export default function HeaderSummaryCard({
    title,
    periodMonth,
    thp,
    kotor,
    pph,
    isSpv = false,
    tabungan30 = 0,
    transfer70 = 0,
    sfaPct,
    sfaPenaltyApplied = false,
    subtitle,
}: HeaderSummaryCardProps) {
    return (
        <div className="mx-4 mt-2 mb-3 bg-gradient-to-br from-indigo-700 via-indigo-800 to-slate-900 rounded-3xl p-5 text-white shadow-xl shadow-indigo-950/20 relative overflow-hidden border border-indigo-500/20">
            {/* Background glowing circles */}
            <div className="absolute -top-12 -right-12 w-36 h-36 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none" />
            <div className="absolute -bottom-8 -left-8 w-28 h-28 bg-emerald-500/15 rounded-full blur-xl pointer-events-none" />

            {/* Top Bar: Title & Periode */}
            <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2">
                    <span className="p-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/10">
                        <BanknotesIcon className="w-5 h-5 text-emerald-400" />
                    </span>
                    <div>
                        <h2 className="text-xs font-semibold uppercase tracking-wider text-indigo-200">
                            {title}
                        </h2>
                        {subtitle && <p className="text-[11px] text-white/70 font-medium">{subtitle}</p>}
                    </div>
                </div>

                <span className="text-[11px] px-2.5 py-1 rounded-full bg-white/10 backdrop-blur-sm text-indigo-100 font-medium border border-white/10">
                    {getMonthName(periodMonth)}
                </span>
            </div>

            {/* Main Value: THP / Transfer */}
            <div className="my-2">
                <span className="text-xs text-white/70 font-medium block">
                    {isSpv ? 'Estimasi Transfer (70% THP)' : 'Estimasi Take Home Pay (THP)'}
                </span>
                <div className="flex items-baseline gap-2 mt-0.5">
                    <span className="text-3xl font-extrabold tracking-tight text-white">
                        {formatRupiah(isSpv ? transfer70 : thp)}
                    </span>
                    {thp > 0 && (
                        <span className="inline-flex items-center gap-0.5 text-xs font-bold text-emerald-400 bg-emerald-500/20 px-2 py-0.5 rounded-full">
                            <ArrowTrendingUpIcon className="w-3.5 h-3.5" /> Berjalan
                        </span>
                    )}
                </div>
            </div>

            {/* SFA Pill indicator if applicable */}
            {sfaPct !== undefined && (
                <div className="mt-3 mb-2 flex items-center justify-between bg-black/20 backdrop-blur-sm px-3 py-2 rounded-xl border border-white/5">
                    <div className="flex items-center gap-2">
                        {sfaPenaltyApplied ? (
                            <ShieldExclamationIcon className="w-4 h-4 text-rose-400" />
                        ) : (
                            <ShieldCheckIcon className="w-4 h-4 text-emerald-400" />
                        )}
                        <span className="text-xs font-medium text-white/90">
                            Kepatuhan SFA: <strong className={sfaPenaltyApplied ? 'text-rose-400' : 'text-emerald-400'}>{sfaPct}%</strong>
                        </span>
                    </div>
                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded-md ${sfaPenaltyApplied ? 'bg-rose-500/30 text-rose-300' : 'bg-emerald-500/30 text-emerald-300'}`}>
                        {sfaPenaltyApplied ? 'Penalti 75%' : 'Aman (≥95%)'}
                    </span>
                </div>
            )}

            {/* Breakdown bottom cards */}
            <div className="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-white/10 text-xs">
                {isSpv ? (
                    <>
                        <div className="bg-white/5 rounded-xl p-2.5">
                            <span className="text-[11px] text-white/60 block">Tabungan (30%)</span>
                            <span className="font-bold text-amber-300 text-sm">{formatRupiah(tabungan30)}</span>
                        </div>
                        <div className="bg-white/5 rounded-xl p-2.5">
                            <span className="text-[11px] text-white/60 block">Total Insentif SPV</span>
                            <span className="font-bold text-white text-sm">{formatRupiah(kotor)}</span>
                        </div>
                    </>
                ) : (
                    <>
                        <div className="bg-white/5 rounded-xl p-2.5">
                            <span className="text-[11px] text-white/60 block">Total Kotor</span>
                            <span className="font-bold text-white text-sm">{formatRupiah(kotor)}</span>
                        </div>
                        <div className="bg-white/5 rounded-xl p-2.5">
                            <span className="text-[11px] text-white/60 block">Pajak (PPh 5%)</span>
                            <span className="font-bold text-rose-300 text-sm">{formatMinusRupiah(pph)}</span>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
}
