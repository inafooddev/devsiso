import React from 'react';
import { KpiSummary, JksItem } from '../constants';
import {
    BuildingStorefrontIcon,
    CheckBadgeIcon,
    ShoppingBagIcon,
    ChartPieIcon,
    MapPinIcon,
    ExclamationTriangleIcon,
    CalendarDaysIcon,
} from '@heroicons/react/24/outline';

interface KpiSummaryDashboardProps {
    kpi: KpiSummary;
    items: JksItem[];
}

export default function KpiSummaryDashboard({ kpi, items }: KpiSummaryDashboardProps) {
    // Hitung distribusi toko per hari (Senin s/d Sabtu)
    const dayCounts = {
        Senin: items.filter((i) => i.h1 === 'Y').length,
        Selasa: items.filter((i) => i.h2 === 'Y').length,
        Rabu: items.filter((i) => i.h3 === 'Y').length,
        Kamis: items.filter((i) => i.h4 === 'Y').length,
        Jumat: items.filter((i) => i.h5 === 'Y').length,
        Sabtu: items.filter((i) => i.h6 === 'Y').length,
    };

    return (
        <div className="space-y-4 px-4 pb-6 animate-fade-in">
            {/* Header Title */}
            <div>
                <h3 className="text-base font-black text-slate-800 tracking-tight">
                    Ringkasan Kinerja JKS
                </h3>
                <p className="text-xs text-slate-400 font-medium mt-0.5">
                    Statistik pencapaian rute dan realisasi kunjungan bulan ini.
                </p>
            </div>

            {/* 4 Main Stat Cards */}
            <div className="grid grid-cols-2 gap-3">
                {/* Total Toko */}
                <div className="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div className="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-2">
                        <BuildingStorefrontIcon className="w-5 h-5" />
                    </div>
                    <div>
                        <span className="text-xs font-bold text-slate-400 uppercase tracking-wider block">
                            Total Toko
                        </span>
                        <div className="text-2xl font-black text-slate-800 leading-tight">
                            {kpi.total_toko}
                        </div>
                        <span className="text-xs font-semibold text-indigo-600 mt-1 block">
                            {kpi.total_rute} Rute • {kpi.total_non_rute} Non-Rute
                        </span>
                    </div>
                </div>

                {/* Realisasi Kunjungan */}
                <div className="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div className="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2">
                        <CheckBadgeIcon className="w-5 h-5" />
                    </div>
                    <div>
                        <span className="text-xs font-bold text-slate-400 uppercase tracking-wider block">
                            Realisasi Visit
                        </span>
                        <div className="text-2xl font-black text-slate-800 leading-tight">
                            {kpi.total_visited}
                        </div>
                        <span className="text-xs font-semibold text-emerald-600 mt-1 block">
                            {kpi.total_toko - kpi.total_visited} Toko Belum Visit
                        </span>
                    </div>
                </div>

                {/* Effective Call & Strike Rate */}
                <div className="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div className="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mb-2">
                        <ChartPieIcon className="w-5 h-5" />
                    </div>
                    <div>
                        <span className="text-xs font-bold text-slate-400 uppercase tracking-wider block">
                            Effective Call (EC)
                        </span>
                        <div className="text-2xl font-black text-slate-800 leading-tight">
                            {kpi.total_ec}
                        </div>
                        <span className="text-xs font-semibold text-sky-600 mt-1 block">
                            Strike Rate: {kpi.strike_rate}%
                        </span>
                    </div>
                </div>

                {/* Akumulasi Nilai Order */}
                <div className="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div className="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-2">
                        <ShoppingBagIcon className="w-5 h-5" />
                    </div>
                    <div>
                        <span className="text-xs font-bold text-slate-400 uppercase tracking-wider block">
                            Total Omset Order
                        </span>
                        <div className="text-base font-black text-purple-800 leading-tight mt-1 truncate">
                            Rp {kpi.total_order_val.toLocaleString('id-ID')}
                        </div>
                        <span className="text-xs font-semibold text-slate-400 mt-1 block">
                            Dari kunjungan rute
                        </span>
                    </div>
                </div>
            </div>

            {/* Daily Schedule Distribution */}
            <div className="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-3">
                <div className="flex items-center gap-2">
                    <CalendarDaysIcon className="w-5 h-5 text-indigo-600" />
                    <h4 className="text-xs font-black text-slate-800 uppercase tracking-wider">
                        Distribusi Toko per Hari
                    </h4>
                </div>
                <div className="grid grid-cols-3 sm:grid-cols-6 gap-2 pt-1">
                    {Object.entries(dayCounts).map(([day, count]) => (
                        <div key={day} className="bg-slate-50 border border-slate-100 rounded-2xl p-2.5 text-center">
                            <span className="text-xs font-bold text-slate-400 uppercase block">{day}</span>
                            <span className="text-base font-black text-slate-800 block mt-0.5">{count}</span>
                            <span className="text-xs font-semibold text-slate-400 block">Toko</span>
                        </div>
                    ))}
                </div>
            </div>

            {/* GPS Health Alert Banner */}
            <div className={`p-4 rounded-3xl border flex items-center justify-between ${
                kpi.total_non_gps > 0
                    ? 'bg-amber-50/70 border-amber-200 text-amber-900'
                    : 'bg-emerald-50/70 border-emerald-200 text-emerald-900'
            }`}>
                <div className="flex items-center gap-3">
                    <div className={`w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 ${
                        kpi.total_non_gps > 0 ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600'
                    }`}>
                        {kpi.total_non_gps > 0 ? (
                            <ExclamationTriangleIcon className="w-5 h-5" />
                        ) : (
                            <MapPinIcon className="w-5 h-5" />
                        )}
                    </div>
                    <div>
                        <h5 className="text-xs font-black uppercase tracking-wide">
                            {kpi.total_non_gps > 0 ? `${kpi.total_non_gps} Toko Belum Ada GPS` : 'Semua Toko Memiliki GPS'}
                        </h5>
                        <p className="text-xs opacity-80 mt-0.5">
                            {kpi.total_non_gps > 0
                                ? 'Perlu perbaikan koordinat lokasi melalui modul Tikor.'
                                : 'Seluruh toko siap dinavigasikan melalui Google Maps.'}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
