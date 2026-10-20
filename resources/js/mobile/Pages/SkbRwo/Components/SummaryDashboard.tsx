import React, { useMemo } from 'react';
import { SkbRwoItem, getProratedTarget } from './StoreCard';
import { 
    ChartPieIcon, ChartBarIcon, 
    DocumentCheckIcon,
    ShieldCheckIcon, ShieldExclamationIcon,
    BuildingStorefrontIcon,
    CheckCircleIcon, XCircleIcon, ClockIcon, AdjustmentsHorizontalIcon,
    GiftIcon
} from '@heroicons/react/24/outline';

interface SummaryDashboardProps {
    data: SkbRwoItem[];
    filterKuartal: string;
    filterDistributor?: string;
    filterReward?: string;
    onOpenFilter: () => void;
    onResetFilter?: () => void;
}

export default function SummaryDashboard({ 
    data, 
    filterKuartal, 
    filterDistributor = 'Semua',
    filterReward = 'Semua',
    onOpenFilter,
    onResetFilter
}: SummaryDashboardProps) {
    const stats = useMemo(() => {
        const totalToko = data.length;
        const totalTarget = data.reduce((sum, item) => sum + (Number(item.total_target) || 0), 0);
        const totalActual = data.reduce((sum, item) => sum + (Number(item.total_achievement) || 0), 0);
        const totalGap = Math.max(0, totalTarget - totalActual);
        const achievementPercent = totalTarget > 0 ? (totalActual / totalTarget) * 100 : 0;
        
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

        // Program Reward (CB vs BB) breakdown
        const cbItems = data.filter(i => String(i.jenis_reward || '').toUpperCase().includes('CB'));
        const bbItems = data.filter(i => String(i.jenis_reward || '').toUpperCase().includes('BB'));
        const countCB = cbItems.length;
        const countBB = bbItems.length;
        const actualCB = cbItems.reduce((sum, item) => sum + (Number(item.total_achievement) || 0), 0);
        const targetCB = cbItems.reduce((sum, item) => sum + (Number(item.total_target) || 0), 0);
        const actualBB = bbItems.reduce((sum, item) => sum + (Number(item.total_achievement) || 0), 0);
        const targetBB = bbItems.reduce((sum, item) => sum + (Number(item.total_target) || 0), 0);

        // Status SKB (hanya filter yang CB)
        const skbApprove = cbItems.filter(i => i.is_approved === 1 || i.is_approved === true).length;
        const skbReject = cbItems.filter(i => i.is_approved === 0 || i.is_approved === false).length;
        const skbPending = cbItems.filter(i => i.status_skb === 'Sudah' && i.is_approved === null).length;
        const skbBelum = cbItems.filter(i => i.status_skb === 'Belum').length;

        // Status Kelengkapan Data (hanya filter yang CB)
        const lengkapCount = cbItems.filter(i => i.status_data_lengkap === 'Lengkap').length;
        const belumLengkapCount = countCB - lengkapCount;

        return {
            totalToko, totalTarget, totalActual, totalGap, achievementPercent,
            skbApprove, skbReject, skbPending, skbBelum,
            lengkapCount, belumLengkapCount,
            hijauCount, kuningCount, merahCount,
            countCB, countBB, actualCB, targetCB, actualBB, targetBB
        };
    }, [data]);

    const formatCurrency = (val: number) => {
        if (val >= 1000000000) return `Rp ${(val / 1000000000).toFixed(2)} M`;
        if (val >= 1000000) return `Rp ${(val / 1000000).toFixed(1)} Jt`;
        return `Rp ${new Intl.NumberFormat('id-ID').format(val)}`;
    };

    const hasActiveFilter = filterDistributor !== 'Semua' || filterReward !== 'Semua';

    return (
        <div className="px-4 flex flex-col gap-5 animate-fade-in pb-8">
            {/* Header Keseluruhan */}
            <div className="flex flex-col gap-2 mb-1 px-1">
                <div className="flex items-center justify-between">
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

                {/* Filter Aktif Badges */}
                {hasActiveFilter && (
                    <div className="flex flex-wrap items-center gap-1.5 pt-1">
                        <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Filter Aktif:</span>
                        {filterDistributor !== 'Semua' && (
                            <span className="inline-flex items-center gap-1 bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-md">
                                Dist: {filterDistributor}
                            </span>
                        )}
                        {filterReward !== 'Semua' && (
                            <span className="inline-flex items-center gap-1 bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-md">
                                Reward: {filterReward}
                            </span>
                        )}
                        {onResetFilter && (
                            <button 
                                onClick={onResetFilter}
                                className="text-[10px] font-bold text-rose-500 hover:text-rose-700 hover:underline ml-1"
                            >
                                Reset
                            </button>
                        )}
                    </div>
                )}
            </div>

            {/* Empty State jika 0 Toko */}
            {stats.totalToko === 0 ? (
                <div className="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm text-center flex flex-col items-center justify-center">
                    <div className="w-14 h-14 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-3">
                        <BuildingStorefrontIcon className="w-7 h-7" />
                    </div>
                    <h4 className="text-sm font-black text-slate-700">Tidak Ada Data Toko</h4>
                    <p className="text-xs text-slate-400 mt-1 max-w-xs">Tidak ditemukan toko dengan filter distributor atau reward yang sedang aktif.</p>
                    {onResetFilter && (
                        <button 
                            onClick={onResetFilter}
                            className="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors"
                        >
                            Reset Filter
                        </button>
                    )}
                </div>
            ) : (
                <>
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

                    {/* Distribusi Program Reward (CB vs BB) */}
                    <div className="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                        <div className="flex items-center justify-between mb-4">
                            <div className="flex items-center gap-2">
                                <GiftIcon className="w-4 h-4 text-slate-400" />
                                <h3 className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Program Reward</h3>
                            </div>
                            <span className="text-[10px] font-bold text-slate-400">
                                {stats.countCB} CB · {stats.countBB} BB
                            </span>
                        </div>

                        {/* Stacked Bar CB vs BB */}
                        <div className="w-full flex h-3.5 rounded-full overflow-hidden bg-slate-100 mb-4 shadow-inner">
                            <div style={{ width: `${stats.totalToko ? (stats.countCB / stats.totalToko) * 100 : 0}%` }} className="bg-indigo-500 h-full transition-all duration-1000" title={`Cashback: ${stats.countCB}`}></div>
                            <div style={{ width: `${stats.totalToko ? (stats.countBB / stats.totalToko) * 100 : 0}%` }} className="bg-amber-400 h-full transition-all duration-1000" title={`Bonus Barang: ${stats.countBB}`}></div>
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="bg-indigo-50/60 rounded-2xl p-3 border border-indigo-100 flex flex-col justify-between">
                                <div>
                                    <div className="flex items-center justify-between mb-1">
                                        <span className="text-[10px] font-black text-indigo-700 uppercase tracking-wider">Cashback (CB)</span>
                                        <span className="text-[9px] font-black text-indigo-600 bg-indigo-100/80 px-1.5 py-0.5 rounded">
                                            {stats.totalToko ? ((stats.countCB / stats.totalToko) * 100).toFixed(0) : 0}%
                                        </span>
                                    </div>
                                    <p className="text-lg font-black text-slate-800">{stats.countCB} <span className="text-xs font-bold text-slate-400">Toko</span></p>
                                </div>
                                <div className="mt-2 pt-2 border-t border-indigo-100/60 flex flex-col gap-0.5 text-[10px]">
                                    <div className="flex justify-between text-slate-500">
                                        <span>Actual</span>
                                        <span className="font-bold text-slate-700">{formatCurrency(stats.actualCB)}</span>
                                    </div>
                                </div>
                            </div>

                            <div className="bg-amber-50/60 rounded-2xl p-3 border border-amber-100 flex flex-col justify-between">
                                <div>
                                    <div className="flex items-center justify-between mb-1">
                                        <span className="text-[10px] font-black text-amber-700 uppercase tracking-wider">Bonus Barang (BB)</span>
                                        <span className="text-[9px] font-black text-amber-600 bg-amber-100/80 px-1.5 py-0.5 rounded">
                                            {stats.totalToko ? ((stats.countBB / stats.totalToko) * 100).toFixed(0) : 0}%
                                        </span>
                                    </div>
                                    <p className="text-lg font-black text-slate-800">{stats.countBB} <span className="text-xs font-bold text-slate-400">Toko</span></p>
                                </div>
                                <div className="mt-2 pt-2 border-t border-amber-100/60 flex flex-col gap-0.5 text-[10px]">
                                    <div className="flex justify-between text-slate-500">
                                        <span>Actual</span>
                                        <span className="font-bold text-slate-700">{formatCurrency(stats.actualBB)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Kinerja Toko (Pengkolaman) */}
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
                        {/* Status SKB (Khusus Program CB) */}
                        <div className="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                            <div className="flex items-center justify-between mb-4">
                                <div className="flex items-center gap-2">
                                    <ShieldCheckIcon className="w-4 h-4 text-slate-400" />
                                    <h3 className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status SKB</h3>
                                </div>
                                <span className="text-[9px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-md">
                                    Khusus CB ({stats.countCB})
                                </span>
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

                        {/* Status Kelengkapan Data (Khusus Program CB) */}
                        <div className="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                            <div className="flex items-center justify-between mb-4">
                                <div className="flex items-center gap-2">
                                    <DocumentCheckIcon className="w-4 h-4 text-slate-400" />
                                    <h3 className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kelengkapan Data</h3>
                                </div>
                                <span className="text-[9px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-md">
                                    Khusus CB ({stats.countCB})
                                </span>
                            </div>
                            <div className="flex flex-col gap-4">
                                <div>
                                    <div className="flex justify-between text-[10px] font-bold uppercase tracking-wider mb-1.5">
                                        <span className="text-emerald-600">Lengkap ({stats.lengkapCount})</span>
                                        <span className="text-slate-400">{stats.countCB ? ((stats.lengkapCount / stats.countCB) * 100).toFixed(0) : 0}%</span>
                                    </div>
                                    <div className="w-full bg-slate-100 rounded-full h-2 shadow-inner">
                                        <div className="bg-emerald-500 h-2 rounded-full transition-all duration-1000" style={{ width: `${stats.countCB ? (stats.lengkapCount / stats.countCB) * 100 : 0}%` }}></div>
                                    </div>
                                </div>
                                <div>
                                    <div className="flex justify-between text-[10px] font-bold uppercase tracking-wider mb-1.5">
                                        <span className="text-rose-500">Belum Lengkap ({stats.belumLengkapCount})</span>
                                        <span className="text-slate-400">{stats.countCB ? ((stats.belumLengkapCount / stats.countCB) * 100).toFixed(0) : 0}%</span>
                                    </div>
                                    <div className="w-full bg-slate-100 rounded-full h-2 shadow-inner">
                                        <div className="bg-rose-400 h-2 rounded-full transition-all duration-1000" style={{ width: `${stats.countCB ? (stats.belumLengkapCount / stats.countCB) * 100 : 0}%` }}></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
