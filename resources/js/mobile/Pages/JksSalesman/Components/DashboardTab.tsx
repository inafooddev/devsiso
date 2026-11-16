import React, { useState, useMemo, useEffect, useRef } from 'react';
import {
    DistributorOption,
    SalesmanDashboardRow,
    DashboardTotals,
} from '../constants';
import {
    ChartBarIcon,
    BuildingStorefrontIcon,
    UserIcon,
    MapPinIcon,
    ExclamationTriangleIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    ChevronDownIcon,
    CalendarDaysIcon,
    ArrowPathIcon,
    ArrowTopRightOnSquareIcon,
} from '@heroicons/react/24/outline';

interface DashboardTabProps {
    distributors: DistributorOption[];
    initialDistributor?: string;
    bulan: string;
    onOpenSalesmanSchedule: (distributorCode: string, salesmanCode: string) => void;
}

type StandproFilterType = 'ALL' | 'STANDAR' | 'BELUM';

const PAGE_SIZE = 25;

const EMPTY_TOTALS: DashboardTotals = {
    total_se: 0,
    standar_se: 0,
    belum_standar_se: 0,
    total_ro: 0,
    total_jks: 0,
    total_non_rute: 0,
    total_non_gps: 0,
    total_penambahan: 0,
    total_deleted: 0,
};

export default function DashboardTab({
    distributors = [],
    bulan,
    onOpenSalesmanSchedule,
}: DashboardTabProps) {
    const [selectedDistributor, setSelectedDistributor] = useState<string>('ALL');
    const [standproFilter, setStandproFilter] = useState<StandproFilterType>('ALL');
    const [searchQuery, setSearchQuery] = useState<string>('');

    const [rows, setRows] = useState<SalesmanDashboardRow[]>([]);
    const [totals, setTotals] = useState<DashboardTotals>(EMPTY_TOTALS);
    const [isLoading, setIsLoading] = useState<boolean>(false);

    // Single-expand accordion (default tertutup)
    const [expandedKey, setExpandedKey] = useState<string | null>(null);
    const [displayLimit, setDisplayLimit] = useState<number>(PAGE_SIZE);

    // In-memory cache per distributor
    const cacheRef = useRef<
        Record<string, { rows: SalesmanDashboardRow[]; totals: DashboardTotals }>
    >({});

    const fetchDashboardData = async (distCode: string) => {
        const cacheKey = `${distCode}_${bulan}`;
        if (cacheRef.current[cacheKey]) {
            setRows(cacheRef.current[cacheKey].rows);
            setTotals(cacheRef.current[cacheKey].totals);
            return;
        }

        setIsLoading(true);
        try {
            const params = new URLSearchParams({
                distributor: distCode,
                bulan,
            });
            const res = await fetch(`/mobile/jks-salesman/dashboard?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (res.ok) {
                const data = await res.json();
                const fetchedRows: SalesmanDashboardRow[] = data.items || [];
                const fetchedTotals: DashboardTotals = data.totals || EMPTY_TOTALS;

                cacheRef.current[cacheKey] = {
                    rows: fetchedRows,
                    totals: fetchedTotals,
                };
                setRows(fetchedRows);
                setTotals(fetchedTotals);
            }
        } catch (err) {
            console.error('Gagal memuat data dashboard JKS:', err);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchDashboardData(selectedDistributor);
    }, []);

    const handleDistributorChange = (distCode: string) => {
        setSelectedDistributor(distCode);
        setExpandedKey(null);
        setDisplayLimit(PAGE_SIZE);
        fetchDashboardData(distCode);
    };

    // Filter baris berdasarkan Standar 300 Toko & Kata Kunci Pencarian
    const filteredRows = useMemo(() => {
        let result = rows;

        if (standproFilter === 'STANDAR') {
            result = result.filter((r) => r.total_jks >= 300);
        } else if (standproFilter === 'BELUM') {
            result = result.filter((r) => r.total_jks < 300);
        }

        const q = searchQuery.trim().toLowerCase();
        if (q.length > 0) {
            result = result.filter(
                (r) =>
                    r.salesman_name.toLowerCase().includes(q) ||
                    r.salesman_code.toLowerCase().includes(q) ||
                    r.distributor_name.toLowerCase().includes(q) ||
                    r.distributor_code.toLowerCase().includes(q)
            );
        }

        return result;
    }, [rows, standproFilter, searchQuery]);

    const displayedRows = useMemo(() => {
        return filteredRows.slice(0, displayLimit);
    }, [filteredRows, displayLimit]);

    const toggleExpand = (key: string) => {
        setExpandedKey((prev) => (prev === key ? null : key));
    };

    // Helper warna beban kunjungan harian (Ideal: 20 - 35 toko/hari)
    const getDailyLoadBadgeClass = (val: number) => {
        if (val === 0) return 'bg-slate-100 text-slate-400 border-slate-200/60';
        if (val < 20) return 'bg-rose-50 text-rose-700 border-rose-200 font-extrabold';
        if (val > 35) return 'bg-amber-50 text-amber-700 border-amber-200 font-extrabold';
        return 'bg-emerald-50 text-emerald-700 border-emerald-200 font-extrabold';
    };

    const standarPercent =
        totals.total_se > 0 ? Math.round((totals.standar_se / totals.total_se) * 100) : 0;

    const standproTabs: {
        key: StandproFilterType;
        label: string;
        count: number;
        activeClass: string;
        inactiveClass: string;
    }[] = [
        {
            key: 'ALL',
            label: 'Semua SE',
            count: totals.total_se,
            activeClass: 'bg-indigo-600 text-white shadow-sm',
            inactiveClass: 'text-slate-600 hover:bg-white/60',
        },
        {
            key: 'STANDAR',
            label: 'Standar (≥300)',
            count: totals.standar_se,
            activeClass: 'bg-emerald-600 text-white shadow-sm',
            inactiveClass: 'text-emerald-700 hover:bg-emerald-50/80',
        },
        {
            key: 'BELUM',
            label: 'Belum (<300)',
            count: totals.belum_standar_se,
            activeClass: 'bg-rose-600 text-white shadow-sm',
            inactiveClass: 'text-rose-700 hover:bg-rose-50/80',
        },
    ];

    return (
        <div className="space-y-3 pb-10">
            {/* 1. Card Filter Distributor, Search & Standar 300 Toko */}
            <div className="bg-white rounded-2xl border border-slate-200/80 p-3 shadow-sm space-y-2.5">
                {/* Dropdown Distributor (Default: Semua Distributor) */}
                <div>
                    <select
                        value={selectedDistributor}
                        onChange={(e) => handleDistributorChange(e.target.value)}
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 outline-none truncate"
                    >
                        <option value="ALL">Semua Distributor</option>
                        {distributors.map((d) => (
                            <option key={d.distributor_code} value={d.distributor_code}>
                                {d.distributor_name}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Search Input */}
                <div className="relative">
                    <MagnifyingGlassIcon className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        type="text"
                        value={searchQuery}
                        onChange={(e) => {
                            setSearchQuery(e.target.value);
                            setDisplayLimit(PAGE_SIZE);
                        }}
                        placeholder="Cari nama/kode salesman atau distributor..."
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-8 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 outline-none"
                    />
                    {searchQuery && (
                        <button
                            type="button"
                            onClick={() => {
                                setSearchQuery('');
                                setDisplayLimit(PAGE_SIZE);
                            }}
                            className="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                        >
                            <XMarkIcon className="w-4 h-4" />
                        </button>
                    )}
                </div>

                {/* Segmented Pill Standar 300 Toko */}
                <div className="grid grid-cols-3 gap-1.5 bg-slate-100/90 p-1 rounded-xl border border-slate-200/60">
                    {standproTabs.map((tab) => {
                        const isActive = standproFilter === tab.key;
                        return (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => {
                                    setStandproFilter(tab.key);
                                    setExpandedKey(null);
                                    setDisplayLimit(PAGE_SIZE);
                                }}
                                className={`py-1.5 px-1.5 rounded-lg text-center transition-all flex flex-col items-center justify-center ${
                                    isActive ? tab.activeClass : tab.inactiveClass
                                }`}
                            >
                                <span className="text-xs sm:text-sm font-black leading-tight">
                                    {tab.count.toLocaleString('id-ID')}
                                </span>
                                <span className="text-3xs sm:text-2xs font-semibold leading-tight mt-0.5 truncate max-w-full">
                                    {tab.label}
                                </span>
                            </button>
                        );
                    })}
                </div>
            </div>

            {/* 2. 4 Kartu KPI Eksekutif (2x2 Grid Compact) */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                {/* KPI 1: Total JKS (Plan Rute) */}
                <div className="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div className="flex items-center justify-between gap-1 mb-1">
                        <span className="text-2xs font-bold uppercase tracking-wider text-slate-400">
                            Plan JKS
                        </span>
                        <CalendarDaysIcon className="w-4 h-4 text-indigo-600 shrink-0" />
                    </div>
                    <div className="text-base sm:text-lg font-black text-indigo-700 leading-tight">
                        {totals.total_jks.toLocaleString('id-ID')}
                    </div>
                    <div className="text-3xs sm:text-2xs font-semibold text-slate-500 mt-1 truncate">
                        Standar ≥300: <strong className="text-emerald-600">{standarPercent}% SE</strong>
                    </div>
                </div>

                {/* KPI 2: Total RO & Mutasi */}
                <div className="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div className="flex items-center justify-between gap-1 mb-1">
                        <span className="text-2xs font-bold uppercase tracking-wider text-slate-400">
                            Total RO
                        </span>
                        <BuildingStorefrontIcon className="w-4 h-4 text-slate-600 shrink-0" />
                    </div>
                    <div className="text-base sm:text-lg font-black text-slate-800 leading-tight">
                        {totals.total_ro.toLocaleString('id-ID')}
                    </div>
                    <div className="flex items-center gap-1.5 text-3xs sm:text-2xs font-bold mt-1">
                        <span className="text-emerald-600">+{totals.total_penambahan} Add</span>
                        <span className="text-slate-300">•</span>
                        <span className="text-rose-600">-{totals.total_deleted} Del</span>
                    </div>
                </div>

                {/* KPI 3: Toko Non-Rute */}
                <div className="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div className="flex items-center justify-between gap-1 mb-1">
                        <span className="text-2xs font-bold uppercase tracking-wider text-slate-400">
                            Non-Rute
                        </span>
                        <ExclamationTriangleIcon className="w-4 h-4 text-amber-500 shrink-0" />
                    </div>
                    <div className="text-base sm:text-lg font-black text-amber-600 leading-tight">
                        {totals.total_non_rute.toLocaleString('id-ID')}
                    </div>
                    <div className="text-3xs sm:text-2xs font-medium text-slate-400 mt-1 truncate">
                        Tanpa jadwal / dobel
                    </div>
                </div>

                {/* KPI 4: Toko Non-GPS */}
                <div className="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div className="flex items-center justify-between gap-1 mb-1">
                        <span className="text-2xs font-bold uppercase tracking-wider text-slate-400">
                            Non-GPS
                        </span>
                        <MapPinIcon className="w-4 h-4 text-rose-500 shrink-0" />
                    </div>
                    <div className="text-base sm:text-lg font-black text-rose-600 leading-tight">
                        {totals.total_non_gps.toLocaleString('id-ID')}
                    </div>
                    <div className="text-3xs sm:text-2xs font-medium text-slate-400 mt-1 truncate">
                        Belum ada titik koordinat
                    </div>
                </div>
            </div>

            {/* 3. Sub-Header Counter */}
            <div className="px-1 flex items-center justify-between text-2xs sm:text-xs text-slate-500">
                <span>
                    Menampilkan <strong className="text-slate-800 font-bold">{filteredRows.length}</strong> Salesman
                </span>
                <span className="text-3xs sm:text-2xs text-slate-400">
                    Ketuk SE untuk rincian hari Sen–Sab
                </span>
            </div>

            {/* 4. Daftar Rekap Salesman (Single-Expand Accordion) */}
            {isLoading ? (
                <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-2">
                    <ArrowPathIcon className="w-6 h-6 text-indigo-600 animate-spin mb-2" />
                    <p className="text-xs font-bold text-slate-700">Memuat ringkasan dashboard JKS...</p>
                </div>
            ) : displayedRows.length > 0 ? (
                <div className="space-y-2.5">
                    {displayedRows.map((row) => {
                        const isExpanded = expandedKey === row.key;
                        const isStandar = row.total_jks >= 300;

                        return (
                            <div
                                key={row.key}
                                className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden transition-all"
                            >
                                {/* Accordion Header Salesman */}
                                <button
                                    type="button"
                                    onClick={() => toggleExpand(row.key)}
                                    className="w-full px-3.5 py-3 flex items-center justify-between gap-2.5 text-left hover:bg-slate-50/80 active:bg-slate-100/60 transition-colors"
                                >
                                    {/* Avatar Status Standar 300 */}
                                    <div
                                        className={`w-9 h-9 rounded-xl border flex items-center justify-center shrink-0 ${
                                            isStandar
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-600'
                                                : 'bg-rose-50 border-rose-200 text-rose-600'
                                        }`}
                                    >
                                        <UserIcon className="w-4 h-4 stroke-[2.2]" />
                                    </div>

                                    {/* Info Salesman & Badge Ringkasan */}
                                    <div className="min-w-0 flex-1">
                                        {/* Baris 1: Nama Salesman + Badge JKS & RO */}
                                        <div className="flex items-center justify-between gap-2">
                                            <h4 className="text-xs sm:text-sm font-extrabold text-slate-900 truncate min-w-0 flex-1">
                                                {row.salesman_name}
                                            </h4>

                                            <div className="flex items-center gap-1 shrink-0">
                                                <span
                                                    className={`px-1.5 py-0.5 rounded-md text-3xs sm:text-2xs font-extrabold border ${
                                                        isStandar
                                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                            : 'bg-rose-50 text-rose-700 border-rose-200'
                                                    }`}
                                                >
                                                    JKS: {row.total_jks}
                                                </span>
                                                <span className="px-1.5 py-0.5 rounded-md text-3xs sm:text-2xs font-bold bg-slate-100 text-slate-600 border border-slate-200/60">
                                                    RO: {row.total_ro}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Baris 2: Nama Distributor + Non-Rute & Non-GPS */}
                                        <div className="flex items-center justify-between gap-2 mt-0.5">
                                            <p className="text-2xs sm:text-xs text-slate-500 font-medium truncate">
                                                {row.distributor_name}
                                            </p>

                                            <div className="flex items-center gap-1.5 shrink-0 text-3xs font-semibold">
                                                {row.non_rute > 0 && (
                                                    <span className="text-amber-600">
                                                        {row.non_rute} Non-Rute
                                                    </span>
                                                )}
                                                {row.non_gps > 0 && (
                                                    <span className="text-rose-600">
                                                        {row.non_gps} Non-GPS
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Chevron */}
                                    <div
                                        className={`w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0 transition-transform duration-200 ${
                                            isExpanded ? 'rotate-180 bg-indigo-50 text-indigo-600' : ''
                                        }`}
                                    >
                                        <ChevronDownIcon className="w-4 h-4 stroke-[2.2]" />
                                    </div>
                                </button>

                                {/* Isi Accordion: Rincian KPI & Matriks Beban Harian Senin - Sabtu */}
                                {isExpanded && (
                                    <div className="border-t border-slate-100 p-3.5 bg-slate-50/40 space-y-3">
                                        {/* Baris 1: Mini Summary + Mutasi */}
                                        <div className="grid grid-cols-4 gap-1.5 text-center">
                                            <div className="bg-white rounded-xl p-2 border border-slate-200/70">
                                                <span className="text-3xs font-bold uppercase text-slate-400 block">
                                                    Total RO
                                                </span>
                                                <span className="text-xs font-black text-slate-800">
                                                    {row.total_ro}
                                                </span>
                                            </div>
                                            <div className="bg-white rounded-xl p-2 border border-slate-200/70">
                                                <span className="text-3xs font-bold uppercase text-slate-400 block">
                                                    Plan JKS
                                                </span>
                                                <span
                                                    className={`text-xs font-black ${
                                                        isStandar ? 'text-emerald-600' : 'text-rose-600'
                                                    }`}
                                                >
                                                    {row.total_jks}
                                                </span>
                                            </div>
                                            <div className="bg-white rounded-xl p-2 border border-slate-200/70">
                                                <span className="text-3xs font-bold uppercase text-slate-400 block">
                                                    Penambahan
                                                </span>
                                                <span className="text-xs font-black text-emerald-600">
                                                    +{row.penambahan}
                                                </span>
                                            </div>
                                            <div className="bg-white rounded-xl p-2 border border-slate-200/70">
                                                <span className="text-3xs font-bold uppercase text-slate-400 block">
                                                    Delete
                                                </span>
                                                <span className="text-xs font-black text-rose-600">
                                                    -{row.deleted}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Baris 2: Matriks Beban Harian (Senin s/d Sabtu: Ganjil, Genap, Non-GPS) */}
                                        <div>
                                            <div className="flex items-center justify-between mb-1.5">
                                                <span className="text-2xs font-bold uppercase tracking-wider text-slate-500">
                                                    Distribusi Kunjungan Harian (Senin – Sabtu)
                                                </span>
                                                <span className="text-3xs text-slate-400">
                                                    Ideal: 20–35 Toko/Hari
                                                </span>
                                            </div>

                                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                {row.days.map((d) => (
                                                    <div
                                                        key={d.label}
                                                        className="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-sm space-y-1.5"
                                                    >
                                                        <div className="flex items-center justify-between border-b border-slate-100 pb-1">
                                                            <span className="text-xs font-extrabold text-slate-800">
                                                                {d.label}
                                                            </span>
                                                            {d.non_gps > 0 && (
                                                                <span className="text-3xs font-bold text-rose-600">
                                                                    {d.non_gps} Non-GPS
                                                                </span>
                                                            )}
                                                        </div>

                                                        <div className="grid grid-cols-2 gap-1.5 text-center">
                                                            <div
                                                                className={`rounded-lg py-1 px-1.5 border ${getDailyLoadBadgeClass(
                                                                    d.gjl
                                                                )}`}
                                                            >
                                                                <span className="text-3xs uppercase block opacity-75">
                                                                    Ganjil
                                                                </span>
                                                                <span className="text-xs font-black block">
                                                                    {d.gjl}
                                                                </span>
                                                            </div>

                                                            <div
                                                                className={`rounded-lg py-1 px-1.5 border ${getDailyLoadBadgeClass(
                                                                    d.gnp
                                                                )}`}
                                                            >
                                                                <span className="text-3xs uppercase block opacity-75">
                                                                    Genap
                                                                </span>
                                                                <span className="text-xs font-black block">
                                                                    {d.gnp}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>

                                        {/* Baris 3: Tombol Pintas ke Tab Jadwal */}
                                        <button
                                            type="button"
                                            onClick={() =>
                                                onOpenSalesmanSchedule(
                                                    row.distributor_code,
                                                    row.salesman_code
                                                )
                                            }
                                            className="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm active:scale-[0.98] transition-all"
                                        >
                                            <ArrowTopRightOnSquareIcon className="w-4 h-4 stroke-[2.2]" />
                                            <span>Lihat Jadwal Rute {row.salesman_name}</span>
                                        </button>
                                    </div>
                                )}
                            </div>
                        );
                    })}

                    {/* Load More Salesman */}
                    {filteredRows.length > displayLimit && (
                        <div className="pt-2 pb-4 flex justify-center">
                            <button
                                type="button"
                                onClick={() => setDisplayLimit((prev) => prev + PAGE_SIZE)}
                                className="px-5 py-2 bg-white border border-slate-200 text-slate-700 hover:text-indigo-600 text-xs font-bold rounded-xl shadow-sm transition-all active:scale-95 flex items-center gap-1.5"
                            >
                                <ArrowPathIcon className="w-3.5 h-3.5 text-indigo-600" />
                                <span>
                                    Muat Salesman Lainnya ({filteredRows.length - displayLimit} SE lagi)
                                </span>
                            </button>
                        </div>
                    )}
                </div>
            ) : (
                <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-2">
                    <div className="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mb-2">
                        <ChartBarIcon className="w-6 h-6" />
                    </div>
                    <h4 className="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
                        Tidak Ada Data Salesman
                    </h4>
                    <p className="text-xs text-slate-400 mt-1 max-w-xs leading-relaxed">
                        Tidak ditemukan data ringkasan JKS salesman untuk filter yang dipilih.
                    </p>
                </div>
            )}
        </div>
    );
}
