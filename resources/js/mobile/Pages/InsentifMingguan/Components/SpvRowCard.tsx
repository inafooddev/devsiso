import React from 'react';
import { SpvData } from '../types';
import { formatRupiah, formatCompactRupiah, formatNumber } from '../constants';
import { ChevronRightIcon, BuildingOffice2Icon } from '@heroicons/react/24/solid';

interface SpvRowCardProps {
    spv: SpvData;
    onClick: () => void;
}

export default function SpvRowCard({ spv, onClick }: SpvRowCardProps) {
    const cabangEntries = Object.values(spv.cabangs || {});
    const cabangNames = cabangEntries.map((c) => c.cabang).join(', ');
    const areaName = cabangEntries[0]?.area_name || '-';
    const isVacant = spv.supervisor_name?.toLowerCase().includes('vacant');

    return (
        <div
            onClick={onClick}
            className="bg-white rounded-2xl p-3.5 border border-gray-200/80 shadow-sm active:scale-[0.99] transition-all cursor-pointer hover:border-teal-300"
        >
            {/* Row 1: Identity & Transfer 70% */}
            <div className="flex items-start justify-between gap-2.5">
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-1.5">
                        <h4
                            className={`text-xs sm:text-sm font-extrabold truncate ${
                                isVacant ? 'text-rose-600' : 'text-gray-900'
                            }`}
                        >
                            {spv.supervisor_name}
                        </h4>
                        <span className="text-[10px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded shrink-0">
                            {spv.supervisor_code}
                        </span>
                    </div>
                    <p className="text-[11px] text-gray-500 truncate mt-0.5 flex items-center gap-1 font-medium">
                        <BuildingOffice2Icon className="w-3.5 h-3.5 text-teal-600 shrink-0" />
                        <span className="truncate">
                            <strong className="text-gray-700">{areaName}</strong> • {cabangNames || '-'}
                        </span>
                    </p>
                </div>

                <div className="text-right shrink-0">
                    <span className="text-[10px] text-gray-400 font-semibold uppercase tracking-wide block">
                        Transfer (70%)
                    </span>
                    <span
                        className={`text-sm font-extrabold block leading-tight ${
                            spv.transfer_70 > 0 ? 'text-teal-700' : 'text-gray-400'
                        }`}
                    >
                        {formatRupiah(spv.transfer_70)}
                    </span>
                    <span className="text-[10px] text-amber-600 font-semibold block leading-tight">
                        Tab 30%: {formatCompactRupiah(spv.tabungan_30)}
                    </span>
                </div>
            </div>

            {/* Row 2: Compact SO Progress Bar & Real vs Target */}
            <div className="mt-2.5 bg-gray-50/80 rounded-xl p-2 border border-gray-100">
                <div className="flex items-center justify-between text-[11px] mb-1">
                    <span className="text-gray-500 font-medium">
                        Real SO: <strong className="text-gray-800">{formatCompactRupiah(spv.total_aktual_so)}</strong>
                        <span className="text-gray-400"> / {formatCompactRupiah(spv.total_target_reguler)}</span>
                    </span>
                    <span
                        className={`font-extrabold ${
                            spv.pencapaian_persen >= 100
                                ? 'text-emerald-600'
                                : spv.pencapaian_persen >= 80
                                ? 'text-amber-600'
                                : 'text-rose-600'
                        }`}
                    >
                        {formatNumber(spv.pencapaian_persen)}%
                    </span>
                </div>
                <div className="w-full bg-gray-200/80 h-1.5 rounded-full overflow-hidden">
                    <div
                        className={`h-full rounded-full ${
                            spv.pencapaian_persen >= 100
                                ? 'bg-emerald-500'
                                : spv.pencapaian_persen >= 80
                                ? 'bg-amber-500'
                                : 'bg-rose-500'
                        }`}
                        style={{ width: `${Math.min(spv.pencapaian_persen, 100)}%` }}
                    />
                </div>
            </div>

            {/* Row 3: Metrics Chips & Detail Chevron */}
            <div className="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between gap-2 text-xs">
                <div className="flex items-center flex-wrap gap-1.5">
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200/60">
                        RWO {formatNumber(spv.rwo_achieve_pct)}%
                    </span>
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 border border-sky-200/60">
                        IPT {spv.ipt}
                    </span>
                    {spv.ins_so > 0 && (
                        <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700">
                            SO ✓
                        </span>
                    )}
                    {spv.total_insentif_vtkp > 0 && (
                        <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-50 text-purple-700">
                            VTKP ✓
                        </span>
                    )}
                </div>

                <div className="flex items-center gap-0.5 text-[11px] font-bold text-teal-600 shrink-0">
                    <span>Rincian</span>
                    <ChevronRightIcon className="w-3.5 h-3.5" />
                </div>
            </div>
        </div>
    );
}
