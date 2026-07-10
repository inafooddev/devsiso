import React, { useMemo } from 'react';
import { SkbRwoItem, getProratedTarget } from './StoreCard';
import { 
    ChartPieIcon, ChartBarIcon, 
    CheckBadgeIcon, DocumentCheckIcon,
    ShieldCheckIcon, ShieldExclamationIcon,
    BuildingStorefrontIcon,
    CheckCircleIcon, XCircleIcon, ClockIcon, AdjustmentsHorizontalIcon
} from '@heroicons/react/24/outline';

interface SummaryDashboardProps {
    data: SkbRwoItem[];
    filterKuartal: string;
    onOpenFilter: () => void;
}

export default function SummaryDashboard({ data, filterKuartal, onOpenFilter }: SummaryDashboardProps) {
    const stats = useMemo(() => {
        const totalToko = data.length;
        const totalTarget = data.reduce((sum, item) => sum + (Number(item.total_target) || 0), 0);
        const totalActual = data.reduce((sum, item) => sum + (Number(item.total_achievement) || 0), 0);
        const totalGap = Math.max(0, totalTarget - totalActual);
        const achievementPercent = totalTarget > 0 ? (totalActual / totalTarget) * 100 : 0;
        
        const skbApprove = data.filter(i => i.is_approved === 1 || i.is_approved === true).length;
        const skbReject = data.filter(i => i.is_approved === 0 || i.is_approved === false).length;
        const skbPending = data.filter(i => i.status_skb === 'Sudah' && i.is_approved === null).length;
        const skbBelum = data.filter(i => i.status_skb === 'Belum').length;

        const lengkapCount = data.filter(i => i.status_data_lengkap === 'Lengkap').length;
        const belumLengkapCount = totalToko - lengkapCount;

        let hijauCount = 0;
        let kuningCount = 0;
        let merahCount = 0;

        data.forEach(i => {
            const tgt = Number(i.total_target) || 0;
            const act = Number(i.total_achievement) || 0;
            const proratedTgt = getProratedTarget(tgt, i.kuartal);
            const pct = proratedTgt > 0 ? (act / proratedTgt) * 100 : 0;
            if (pct >= 100) hijauCount++;
            else if (pct >= 80) kuningCount++;
            else merahCount++;
        });

        return {
            totalToko, totalTarget, totalActual, totalGap, achievementPercent,
            skbApprove, skbReject, skbPending, skbBelum,
            lengkapCount, belumLengkapCount,
            hijauCount, kuningCount, merahCount
        };
    }, [data]);

    const formatCurrency = (val: number) => {
        if (val >= 1000000000) return `Rp ${(val / 1000000000).toFixed(2)} M`;
        if (val >= 1000000) return `Rp ${(val / 1000000).toFixed(1)} Jt`;
        return `Rp ${new Intl.NumberFormat('id-ID').format(val)}`;
    };

    const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val);

    return (
        <div className="px-4 flex flex-col gap-5 animate-fade-in pb-8">
            {/* Header Keseluruhan */}
            <div className="flex items-center justify-between mb-1 px-1">
                <div className="flex items-center gap-2 text-slate-800">
                    <ChartBarIcon className="w-5 h-5 text-indigo-600" />
                    <h2 className="text-sm font-black uppercase tracking-wider">Dashboard Analisa</h2>
                </div>
                <div className="flex items-center gap-1.5">
                    <button onClick={onOpenFilter} className="flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 rounded-lg shadow-sm text-slate-600 hover:text-indigo-600 hover:bg-slate-50 transition-colors">
                        <AdjustmentsHorizontalIcon className="w-3.5 h-3.5" />
                        <span className="text-[10px] font-bold uppercase tracking-wider">{filterKuartal === 'Semua' ? 'Kuartal' : `Q${filterKuartal}`}</span>
                    </button>
                    <div className="bg-indigo-50 border border-indigo-100 text-indigo-700 px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <BuildingStorefrontIcon className="w-3.5 h-3.5" />
                        <span className="text-[10px] font-black">{stats.totalToko} Toko</span>
                    </div>
                </div>
            </div>

            {/* Achievement Card */}
            <div className="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 flex flex-col relative overflow-hidden group">
                <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500"></div>
                
                <div className="flex justify-between items-start mb-4">
                    <div>
                        <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Pencapaian (Actual)</h3>
                        <p className="text-2xl font-black text-slate-800 tracking-tight">{formatCurrency(stats.totalActual)}</p>
                    </div>
                    <div className="bg-indigo-50 rounded-xl px-3 py-2 border border-indigo-100 text-center">
                        <span className="text-[10px] block font-bold text-indigo-400 uppercase tracking-wider mb-0.5">Persentase</span>
                        <span className="text-lg font-black text-indigo-600 leading-none">{stats.achievementPercent.toFixed(1)}%</span>
                    </div>
                </div>

                <div className="relative pt-1 mb-5">
                    <div className="overflow-hidden h-3 text-xs flex rounded-full bg-slate-100 shadow-inner">
                        <div style={{ width: `${Math.min(stats.achievementPercent, 100)}%` }} className="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-1000 ease-out"></div>
                    </div>
                </div>

                <div className="flex border-t border-slate-100 pt-3">
                    <div className="flex-1">
                        <h4 className="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Target</h4>
                        <p className="text-xs font-black text-slate-700">{formatCurrency(stats.totalTarget)}</p>
                    </div>
                    <div className="w-px bg-slate-100 mx-3"></div>
                    <div className="flex-1 text-right">
                        <h4 className="text-[9px] font-bold text-rose-400 uppercase tracking-wider mb-0.5">Sisa Gap</h4>
                        <p className="text-xs font-black text-rose-600">{formatCurrency(stats.totalGap)}</p>
                    </div>
                </div>
            </div>

            {/* Kinerja Toko (Warna Status) */}
            <div className="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div className="flex items-center gap-2 mb-4">
                    <ChartPieIcon className="w-4 h-4 text-slate-400" />
                    <h3 className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Pengkolaman</h3>
                </div>
                
                {/* Stacked Bar */}
                <div className="w-full flex h-4 rounded-full overflow-hidden bg-slate-100 mb-4 shadow-inner">
                    <div style={{ width: `${stats.totalToko ? (stats.hijauCount/stats.totalToko)*100 : 0}%` }} className="bg-emerald-500 h-full transition-all duration-1000"></div>
                    <div style={{ width: `${stats.totalToko ? (stats.kuningCount/stats.totalToko)*100 : 0}%` }} className="bg-amber-400 h-full transition-all duration-1000"></div>
                    <div style={{ width: `${stats.totalToko ? (stats.merahCount/stats.totalToko)*100 : 0}%` }} className="bg-rose-500 h-full transition-all duration-1000"></div>
                </div>

                <div className="grid grid-cols-3 gap-2">
                    <div className="bg-emerald-50 rounded-xl p-2.5 border border-emerald-100 flex flex-col items-center">
                        <span className="text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-0.5">Hijau</span>
                        <span className="text-sm font-black text-emerald-600">{stats.hijauCount}</span>
                        <span className="text-[9px] font-bold text-emerald-500">{stats.totalToko ? ((stats.hijauCount/stats.totalToko)*100).toFixed(0) : 0}%</span>
                    </div>
                    <div className="bg-amber-50 rounded-xl p-2.5 border border-amber-100 flex flex-col items-center">
                        <span className="text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-0.5">Kuning</span>
                        <span className="text-sm font-black text-amber-600">{stats.kuningCount}</span>
                        <span className="text-[9px] font-bold text-amber-500">{stats.totalToko ? ((stats.kuningCount/stats.totalToko)*100).toFixed(0) : 0}%</span>
                    </div>
                    <div className="bg-rose-50 rounded-xl p-2.5 border border-rose-100 flex flex-col items-center">
                        <span className="text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-0.5">Merah</span>
                        <span className="text-sm font-black text-rose-600">{stats.merahCount}</span>
                        <span className="text-[9px] font-bold text-rose-500">{stats.totalToko ? ((stats.merahCount/stats.totalToko)*100).toFixed(0) : 0}%</span>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {/* Status SKB */}
                <div className="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div className="flex items-center gap-2 mb-4">
                        <ShieldCheckIcon className="w-4 h-4 text-slate-400" />
                        <h3 className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status SKB</h3>
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center shrink-0 border border-emerald-100">
                                <CheckCircleIcon className="w-4 h-4 text-emerald-500" />
                            </div>
                            <div>
                                <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Approve</p>
                                <p className="text-xs font-black text-slate-700">{stats.skbApprove}</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center shrink-0 border border-amber-100">
                                <ClockIcon className="w-4 h-4 text-amber-500" />
                            </div>
                            <div>
                                <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Pending</p>
                                <p className="text-xs font-black text-slate-700">{stats.skbPending}</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 rounded-full bg-rose-50 flex items-center justify-center shrink-0 border border-rose-100">
                                <XCircleIcon className="w-4 h-4 text-rose-500" />
                            </div>
                            <div>
                                <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Reject</p>
                                <p className="text-xs font-black text-slate-700">{stats.skbReject}</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center shrink-0 border border-slate-200">
                                <ShieldExclamationIcon className="w-4 h-4 text-slate-400" />
                            </div>
                            <div>
                                <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Belum</p>
                                <p className="text-xs font-black text-slate-700">{stats.skbBelum}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Status Kelengkapan Data */}
                <div className="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div className="flex items-center justify-between mb-4">
                        <div className="flex items-center gap-2">
                            <DocumentCheckIcon className="w-4 h-4 text-slate-400" />
                            <h3 className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kelengkapan Data</h3>
                        </div>
                    </div>
                    <div className="flex flex-col gap-4">
                        <div>
                            <div className="flex justify-between text-[10px] font-bold uppercase tracking-wider mb-1.5">
                                <span className="text-emerald-600">Lengkap ({stats.lengkapCount})</span>
                                <span className="text-slate-400">{stats.totalToko ? ((stats.lengkapCount/stats.totalToko)*100).toFixed(0) : 0}%</span>
                            </div>
                            <div className="w-full bg-slate-100 rounded-full h-2 shadow-inner">
                                <div className="bg-emerald-500 h-2 rounded-full transition-all duration-1000" style={{ width: `${stats.totalToko ? (stats.lengkapCount/stats.totalToko)*100 : 0}%` }}></div>
                            </div>
                        </div>
                        <div>
                            <div className="flex justify-between text-[10px] font-bold uppercase tracking-wider mb-1.5">
                                <span className="text-rose-500">Belum Lengkap ({stats.belumLengkapCount})</span>
                                <span className="text-slate-400">{stats.totalToko ? ((stats.belumLengkapCount/stats.totalToko)*100).toFixed(0) : 0}%</span>
                            </div>
                            <div className="w-full bg-slate-100 rounded-full h-2 shadow-inner">
                                <div className="bg-rose-400 h-2 rounded-full transition-all duration-1000" style={{ width: `${stats.totalToko ? (stats.belumLengkapCount/stats.totalToko)*100 : 0}%` }}></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    );
}
