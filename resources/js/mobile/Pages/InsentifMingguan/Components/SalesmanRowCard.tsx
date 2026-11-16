import React from 'react';
import { SalesmanSeData } from '../types';
import { formatRupiah, formatCompactRupiah, SFA_MIN_COMPLIANCE } from '../constants';
import { ChevronRightIcon, ShieldExclamationIcon, ShieldCheckIcon } from '@heroicons/react/24/solid';

interface SalesmanRowCardProps {
    salesman: SalesmanSeData;
    onClick: () => void;
}

export default function SalesmanRowCard({ salesman, onClick }: SalesmanRowCardProps) {
    const isSfaWarning = salesman.sfa.sfa_pct < SFA_MIN_COMPLIANCE;
    const isVacant = salesman.salesman_name?.toLowerCase().includes('vacant');
    const hasPenaltyLoss = isSfaWarning && salesman.totals.kotor > 0;

    return (
        <div
            onClick={onClick}
            className="bg-white rounded-2xl p-3.5 border border-gray-200/80 shadow-sm active:scale-[0.99] transition-all cursor-pointer hover:border-indigo-300"
        >
            {/* Row 1: Identity & THP */}
            <div className="flex items-start justify-between gap-2.5">
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-1.5">
                        <h4
                            className={`text-xs sm:text-sm font-extrabold truncate ${
                                isVacant ? 'text-rose-600' : 'text-gray-900'
                            }`}
                        >
                            {salesman.salesman_name}
                        </h4>
                        <span className="text-[10px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded shrink-0">
                            {salesman.salesman_code}
                        </span>
                    </div>
                    <p className="text-[11px] text-gray-500 truncate mt-0.5 font-medium">
                        <strong className="text-gray-700">{salesman.cabang}</strong> • {salesman.distributor}
                    </p>
                </div>

                <div className="text-right shrink-0">
                    <span className="text-[10px] text-gray-400 font-semibold uppercase tracking-wide block">
                        Proyeksi THP
                    </span>
                    <span
                        className={`text-sm font-extrabold block leading-tight ${
                            salesman.totals.thp > 0 ? 'text-indigo-700' : 'text-gray-400'
                        }`}
                    >
                        {formatRupiah(salesman.totals.thp)}
                    </span>
                    {hasPenaltyLoss && (
                        <span className="text-[10px] text-rose-500 line-through block leading-tight">
                            {formatRupiah(salesman.totals.kotor * 0.95)}
                        </span>
                    )}
                </div>
            </div>

            {/* Row 2: Compact SO Progress Bar & Real vs Target */}
            <div className="mt-2.5 bg-gray-50/80 rounded-xl p-2 border border-gray-100">
                <div className="flex items-center justify-between text-[11px] mb-1">
                    <span className="text-gray-500 font-medium">
                        Real SO: <strong className="text-gray-800">{formatCompactRupiah(salesman.value.actual)}</strong>
                        <span className="text-gray-400"> / {formatCompactRupiah(salesman.value.target)}</span>
                    </span>
                    <span
                        className={`font-extrabold ${
                            salesman.value.ach_pct >= 100
                                ? 'text-emerald-600'
                                : salesman.value.ach_pct >= 60
                                ? 'text-amber-600'
                                : 'text-rose-600'
                        }`}
                    >
                        {salesman.value.ach_pct}%
                    </span>
                </div>
                <div className="w-full bg-gray-200/80 h-1.5 rounded-full overflow-hidden">
                    <div
                        className={`h-full rounded-full ${
                            salesman.value.ach_pct >= 100
                                ? 'bg-emerald-500'
                                : salesman.value.ach_pct >= 60
                                ? 'bg-amber-500'
                                : 'bg-rose-500'
                        }`}
                        style={{ width: `${Math.min(salesman.value.ach_pct, 100)}%` }}
                    />
                </div>
            </div>

            {/* Row 3: SFA Badge + Active Pillar Indicators + Detail Chevron */}
            <div className="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between gap-2 text-xs">
                <div className="flex items-center flex-wrap gap-1.5">
                    {/* SFA Badge */}
                    <span
                        className={`text-[10px] font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1 ${
                            isSfaWarning
                                ? 'bg-rose-50 text-rose-700 border border-rose-200'
                                : 'bg-emerald-50 text-emerald-700 border border-emerald-200/60'
                        }`}
                    >
                        {isSfaWarning ? (
                            <ShieldExclamationIcon className="w-3 h-3 text-rose-600" />
                        ) : (
                            <ShieldCheckIcon className="w-3 h-3 text-emerald-600" />
                        )}
                        SFA {salesman.sfa.sfa_pct}%
                    </span>

                    {/* Earned Pillar Mini Badges */}
                    {salesman.value.insentif > 0 && (
                        <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700">
                            Val ✓
                        </span>
                    )}
                    {salesman.vtkp.total_insentif > 0 && (
                        <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-50 text-purple-700">
                            VTKP ✓
                        </span>
                    )}
                    {salesman.ec.insentif > 0 && (
                        <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">
                            EC ✓
                        </span>
                    )}
                    {salesman.ipt.insentif > 0 && (
                        <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-sky-50 text-sky-700">
                            IPT ✓
                        </span>
                    )}
                </div>

                <div className="flex items-center gap-0.5 text-[11px] font-bold text-indigo-600 shrink-0">
                    <span>Rincian</span>
                    <ChevronRightIcon className="w-3.5 h-3.5" />
                </div>
            </div>
        </div>
    );
}
