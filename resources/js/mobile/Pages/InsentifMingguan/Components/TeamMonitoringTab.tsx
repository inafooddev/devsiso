import React, { useState, useMemo, useEffect } from 'react';
import { SalesmanSeData, SpvData, KacabData } from '../types';
import {
    TeamFilterChip,
    SpvFilterChip,
    PAGE_SIZE,
    formatRupiah,
    formatCompactRupiah,
    formatNumber,
} from '../constants';
import { useInsentifMingguanFilter } from '../hooks/useInsentifMingguanFilter';
import SalesmanRowCard from './SalesmanRowCard';
import SalesmanDetailSheet from './SalesmanDetailSheet';
import SpvRowCard from './SpvRowCard';
import SpvDetailSheet from './SpvDetailSheet';
import {
    MagnifyingGlassIcon,
    UsersIcon,
    UserGroupIcon,
    BuildingOffice2Icon,
    XMarkIcon,
    ArrowPathIcon,
} from '@heroicons/react/24/outline';

export type TeamModeType = 'SE' | 'SPV' | 'KACAB';

interface TeamMonitoringTabProps {
    salesmenList: SalesmanSeData[];
    spvList?: SpvData[];
    kacabList?: KacabData[];
    initialTeamMode?: TeamModeType;
    initialSeChip?: TeamFilterChip;
}

export default function TeamMonitoringTab({
    salesmenList = [],
    spvList = [],
    kacabList = [],
    initialTeamMode = 'SE',
    initialSeChip = 'all',
}: TeamMonitoringTabProps) {
    const [teamMode, setTeamMode] = useState<TeamModeType>(initialTeamMode);

    // SE filter hook
    const {
        search,
        setSearch,
        selectedCabang,
        setSelectedCabang,
        cabangOptions,
        selectedChip,
        setSelectedChip,
        sortBy,
        setSortBy,
        filteredSalesmen,
        chipCounts,
        resetFilters,
        hasActiveFilter,
    } = useInsentifMingguanFilter({ salesmenList, initialChip: initialSeChip });

    // Sync initial props when user jumps from Ringkasan KPI cards
    useEffect(() => {
        setTeamMode(initialTeamMode);
    }, [initialTeamMode]);

    useEffect(() => {
        setSelectedChip(initialSeChip);
    }, [initialSeChip]);

    const [seDisplayLimit, setSeDisplayLimit] = useState<number>(PAGE_SIZE);
    const [selectedSalesman, setSelectedSalesman] = useState<SalesmanSeData | null>(null);

    // SPV filter & sort state
    const [spvSearch, setSpvSearch] = useState('');
    const [spvChip, setSpvChip] = useState<SpvFilterChip>('all');
    const [spvSortBy, setSpvSortBy] = useState<'trf_desc' | 'so_desc' | 'rwo_desc' | 'name_asc'>('trf_desc');
    const [selectedSpv, setSelectedSpv] = useState<SpvData | null>(null);

    // Kacab filter state
    const [kacabSearch, setKacabSearch] = useState('');

    // Reset pagination when SE filters change
    useEffect(() => {
        setSeDisplayLimit(PAGE_SIZE);
    }, [search, selectedCabang, selectedChip, sortBy]);

    const displayedSalesmen = useMemo(() => {
        return filteredSalesmen.slice(0, seDisplayLimit);
    }, [filteredSalesmen, seDisplayLimit]);

    // Base SPV list filtered by search (name, code, cabang, area)
    const baseFilteredSpvs = useMemo(() => {
        if (!spvSearch.trim()) return spvList;
        const q = spvSearch.toLowerCase().trim();
        return spvList.filter((s) => {
            const cabangs = Object.values(s.cabangs || {});
            const matchCabang = cabangs.some(
                (c) =>
                    c.cabang?.toLowerCase().includes(q) ||
                    c.area_name?.toLowerCase().includes(q)
            );
            return (
                s.supervisor_name?.toLowerCase().includes(q) ||
                s.supervisor_code?.toLowerCase().includes(q) ||
                matchCabang
            );
        });
    }, [spvList, spvSearch]);

    const spvChipCounts = useMemo(() => {
        let hasIncentive = 0;
        let qualifiedSo = 0;
        let underSo = 0;
        for (const s of baseFilteredSpvs) {
            if (s.transfer_70 > 0) hasIncentive++;
            if (s.pencapaian_persen >= 80) qualifiedSo++;
            else underSo++;
        }
        return {
            all: baseFilteredSpvs.length,
            has_incentive: hasIncentive,
            qualified_so: qualifiedSo,
            under_so: underSo,
        };
    }, [baseFilteredSpvs]);

    const filteredSpvs = useMemo(() => {
        let res = [...baseFilteredSpvs];
        if (spvChip === 'has_incentive') {
            res = res.filter((s) => s.transfer_70 > 0);
        } else if (spvChip === 'qualified_so') {
            res = res.filter((s) => s.pencapaian_persen >= 80);
        } else if (spvChip === 'under_so') {
            res = res.filter((s) => s.pencapaian_persen < 80);
        }

        res.sort((a, b) => {
            if (spvSortBy === 'trf_desc') return b.transfer_70 - a.transfer_70;
            if (spvSortBy === 'so_desc') return b.pencapaian_persen - a.pencapaian_persen;
            if (spvSortBy === 'rwo_desc') return b.rwo_achieve_pct - a.rwo_achieve_pct;
            return (a.supervisor_name || '').localeCompare(b.supervisor_name || '');
        });

        return res;
    }, [baseFilteredSpvs, spvChip, spvSortBy]);

    const filteredKacabs = useMemo(() => {
        if (!kacabSearch.trim()) return kacabList;
        const q = kacabSearch.toLowerCase().trim();
        return kacabList.filter(
            (k) =>
                k.cabang?.toLowerCase().includes(q) ||
                k.nama_kacab?.toLowerCase().includes(q) ||
                k.area_name?.toLowerCase().includes(q) ||
                k.distributor_name?.toLowerCase().includes(q)
        );
    }, [kacabList, kacabSearch]);

    return (
        <div className="space-y-3 pb-8">
            {/* Sub Mode Switcher: SE vs SPV vs Kacab */}
            <div className="mx-4 bg-gray-200/75 p-1 rounded-2xl flex items-center gap-1 text-xs font-bold">
                <button
                    type="button"
                    onClick={() => setTeamMode('SE')}
                    className={`flex-1 py-2 px-2 rounded-xl transition-all flex items-center justify-center gap-1.5 ${
                        teamMode === 'SE'
                            ? 'bg-white text-indigo-700 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900'
                    }`}
                >
                    <UsersIcon className="w-4 h-4 shrink-0" />
                    <span className="truncate">SE ({salesmenList.length})</span>
                </button>
                <button
                    type="button"
                    onClick={() => setTeamMode('SPV')}
                    className={`flex-1 py-2 px-2 rounded-xl transition-all flex items-center justify-center gap-1.5 ${
                        teamMode === 'SPV'
                            ? 'bg-white text-teal-700 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900'
                    }`}
                >
                    <UserGroupIcon className="w-4 h-4 shrink-0" />
                    <span className="truncate">SPV ({spvList.length})</span>
                </button>
                {kacabList.length > 0 && (
                    <button
                        type="button"
                        onClick={() => setTeamMode('KACAB')}
                        className={`flex-1 py-2 px-2 rounded-xl transition-all flex items-center justify-center gap-1.5 ${
                            teamMode === 'KACAB'
                                ? 'bg-white text-purple-700 shadow-sm'
                                : 'text-gray-600 hover:text-gray-900'
                        }`}
                    >
                        <BuildingOffice2Icon className="w-4 h-4 shrink-0" />
                        <span className="truncate">Kacab ({kacabList.length})</span>
                    </button>
                )}
            </div>

            {/* =========================================================
                1. SE MODE CONTENT
               ========================================================= */}
            {teamMode === 'SE' && (
                <div className="space-y-3">
                    {/* Unified Control Box: Cabang Filter, Search & Sort */}
                    <div className="mx-4 bg-white rounded-2xl border border-gray-200/80 p-3 shadow-sm space-y-2.5">
                        {/* Cabang Dropdown (if > 1 cabang) */}
                        {cabangOptions.length > 1 && (
                            <div className="flex items-center gap-2">
                                <select
                                    value={selectedCabang}
                                    onChange={(e) => setSelectedCabang(e.target.value)}
                                    className="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-800 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 outline-none truncate"
                                >
                                    <option value="ALL">Semua Cabang ({salesmenList.length} SE)</option>
                                    {cabangOptions.map((cab) => (
                                        <option key={cab} value={cab}>
                                            Cabang: {cab}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        )}

                        {/* Search Bar & Sort */}
                        <div className="flex items-center gap-2">
                            <div className="relative flex-1">
                                <MagnifyingGlassIcon className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Cari nama, kode SE, atau distributor..."
                                    className="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-8 py-2 text-xs text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 outline-none"
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={() => setSearch('')}
                                        className="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <XMarkIcon className="w-4 h-4" />
                                    </button>
                                )}
                            </div>

                            <select
                                value={sortBy}
                                onChange={(e: any) => setSortBy(e.target.value)}
                                className="bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs font-bold text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 outline-none shrink-0"
                            >
                                <option value="thp_desc">THP Tertinggi</option>
                                <option value="sfa_asc">SFA Terendah</option>
                                <option value="ach_desc">SO Tertinggi</option>
                                <option value="name_asc">Nama (A-Z)</option>
                            </select>
                        </div>

                        {/* Quick Filter Chips */}
                        <div
                            className="flex items-center gap-1.5 overflow-x-auto pb-0.5 text-xs"
                            style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
                        >
                            <button
                                type="button"
                                onClick={() => setSelectedChip('all')}
                                className={`px-2.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 ${
                                    selectedChip === 'all'
                                        ? 'bg-indigo-600 text-white shadow-sm'
                                        : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100'
                                }`}
                            >
                                <span>Semua</span>
                                <span
                                    className={`text-[10px] px-1.5 py-0.5 rounded-full leading-none ${
                                        selectedChip === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200/80 text-gray-700'
                                    }`}
                                >
                                    {chipCounts.all}
                                </span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setSelectedChip('has_incentive')}
                                className={`px-2.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 ${
                                    selectedChip === 'has_incentive'
                                        ? 'bg-emerald-600 text-white shadow-sm'
                                        : 'bg-emerald-50/70 text-emerald-700 border border-emerald-200 hover:bg-emerald-100/70'
                                }`}
                            >
                                <span>💰 Dapat Insentif</span>
                                <span
                                    className={`text-[10px] px-1.5 py-0.5 rounded-full leading-none ${
                                        selectedChip === 'has_incentive'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-emerald-100 text-emerald-800'
                                    }`}
                                >
                                    {chipCounts.has_incentive}
                                </span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setSelectedChip('warning_sfa')}
                                className={`px-2.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 ${
                                    selectedChip === 'warning_sfa'
                                        ? 'bg-rose-600 text-white shadow-sm'
                                        : 'bg-rose-50/70 text-rose-700 border border-rose-200 hover:bg-rose-100/70'
                                }`}
                            >
                                <span>⚠️ SFA &lt;95%</span>
                                <span
                                    className={`text-[10px] px-1.5 py-0.5 rounded-full leading-none ${
                                        selectedChip === 'warning_sfa' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800'
                                    }`}
                                >
                                    {chipCounts.warning_sfa}
                                </span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setSelectedChip('achieved')}
                                className={`px-2.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 ${
                                    selectedChip === 'achieved'
                                        ? 'bg-sky-600 text-white shadow-sm'
                                        : 'bg-sky-50/70 text-sky-700 border border-sky-200 hover:bg-sky-100/70'
                                }`}
                            >
                                <span>🎯 SO &ge;100%</span>
                                <span
                                    className={`text-[10px] px-1.5 py-0.5 rounded-full leading-none ${
                                        selectedChip === 'achieved' ? 'bg-white/20 text-white' : 'bg-sky-100 text-sky-800'
                                    }`}
                                >
                                    {chipCounts.achieved}
                                </span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setSelectedChip('under_target')}
                                className={`px-2.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 ${
                                    selectedChip === 'under_target'
                                        ? 'bg-amber-600 text-white shadow-sm'
                                        : 'bg-amber-50/70 text-amber-700 border border-amber-200 hover:bg-amber-100/70'
                                }`}
                            >
                                <span>📉 SO &lt;60%</span>
                                <span
                                    className={`text-[10px] px-1.5 py-0.5 rounded-full leading-none ${
                                        selectedChip === 'under_target'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-amber-100 text-amber-800'
                                    }`}
                                >
                                    {chipCounts.under_target}
                                </span>
                            </button>
                        </div>
                    </div>

                    {/* Counter & Quick Reset */}
                    <div className="mx-5 flex items-center justify-between text-[11px] text-gray-500">
                        <span>
                            Menampilkan <strong className="text-gray-800">{displayedSalesmen.length}</strong> dari{' '}
                            <strong className="text-gray-800">{filteredSalesmen.length}</strong> Salesman
                        </span>
                        {hasActiveFilter && (
                            <button
                                type="button"
                                onClick={resetFilters}
                                className="text-indigo-600 font-bold hover:underline flex items-center gap-1"
                            >
                                <ArrowPathIcon className="w-3 h-3" /> Reset Filter
                            </button>
                        )}
                    </div>

                    {/* List of Salesmen */}
                    <div className="mx-4 space-y-2.5">
                        {displayedSalesmen.length === 0 ? (
                            <div className="bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm text-xs">
                                <UsersIcon className="w-10 h-10 text-gray-300 mx-auto mb-2" />
                                <p className="font-bold text-gray-700">Tidak ada salesman ditemukan</p>
                                <p className="text-gray-400 mt-1">
                                    Coba sesuaikan kata kunci pencarian atau tekan Reset Filter.
                                </p>
                                {hasActiveFilter && (
                                    <button
                                        type="button"
                                        onClick={resetFilters}
                                        className="mt-3 px-4 py-1.5 bg-indigo-50 text-indigo-700 rounded-xl font-bold"
                                    >
                                        Reset Semua Filter
                                    </button>
                                )}
                            </div>
                        ) : (
                            <>
                                {displayedSalesmen.map((salesman, idx) => (
                                    <SalesmanRowCard
                                        key={`${salesman.kd_dist}_${salesman.salesman_code}_${idx}`}
                                        salesman={salesman}
                                        onClick={() => setSelectedSalesman(salesman)}
                                    />
                                ))}

                                {/* Load More Button */}
                                {filteredSalesmen.length > seDisplayLimit && (
                                    <button
                                        type="button"
                                        onClick={() => setSeDisplayLimit((prev) => prev + PAGE_SIZE)}
                                        className="w-full py-3 bg-white hover:bg-gray-50 text-indigo-600 font-bold text-xs rounded-2xl border border-gray-200 shadow-sm transition-all active:scale-[0.99]"
                                    >
                                        Muat Lebih Banyak ({filteredSalesmen.length - seDisplayLimit} tersisa)
                                    </button>
                                )}
                            </>
                        )}
                    </div>
                </div>
            )}

            {/* =========================================================
                2. SPV MODE CONTENT
               ========================================================= */}
            {teamMode === 'SPV' && (
                <div className="space-y-3">
                    {/* Unified Control Box SPV: Search, Sort & Chips */}
                    <div className="mx-4 bg-white rounded-2xl border border-gray-200/80 p-3 shadow-sm space-y-2.5">
                        <div className="flex items-center gap-2">
                            <div className="relative flex-1">
                                <MagnifyingGlassIcon className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                                <input
                                    type="text"
                                    value={spvSearch}
                                    onChange={(e) => setSpvSearch(e.target.value)}
                                    placeholder="Cari nama SPV, kode, atau cabang..."
                                    className="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-8 py-2 text-xs text-gray-800 placeholder-gray-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-200 outline-none"
                                />
                                {spvSearch && (
                                    <button
                                        type="button"
                                        onClick={() => setSpvSearch('')}
                                        className="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <XMarkIcon className="w-4 h-4" />
                                    </button>
                                )}
                            </div>

                            <select
                                value={spvSortBy}
                                onChange={(e: any) => setSpvSortBy(e.target.value)}
                                className="bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs font-bold text-gray-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-200 outline-none shrink-0"
                            >
                                <option value="trf_desc">Transfer Tertinggi</option>
                                <option value="so_desc">SO Tertinggi</option>
                                <option value="rwo_desc">RWO Tertinggi</option>
                                <option value="name_asc">Nama (A-Z)</option>
                            </select>
                        </div>

                        {/* Quick Filter Chips SPV */}
                        <div
                            className="flex items-center gap-1.5 overflow-x-auto pb-0.5 text-xs"
                            style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
                        >
                            <button
                                type="button"
                                onClick={() => setSpvChip('all')}
                                className={`px-2.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 ${
                                    spvChip === 'all'
                                        ? 'bg-teal-600 text-white shadow-sm'
                                        : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100'
                                }`}
                            >
                                <span>Semua</span>
                                <span
                                    className={`text-[10px] px-1.5 py-0.5 rounded-full leading-none ${
                                        spvChip === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200/80 text-gray-700'
                                    }`}
                                >
                                    {spvChipCounts.all}
                                </span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setSpvChip('has_incentive')}
                                className={`px-2.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 ${
                                    spvChip === 'has_incentive'
                                        ? 'bg-emerald-600 text-white shadow-sm'
                                        : 'bg-emerald-50/70 text-emerald-700 border border-emerald-200 hover:bg-emerald-100/70'
                                }`}
                            >
                                <span>💰 Dapat Insentif</span>
                                <span
                                    className={`text-[10px] px-1.5 py-0.5 rounded-full leading-none ${
                                        spvChip === 'has_incentive'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-emerald-100 text-emerald-800'
                                    }`}
                                >
                                    {spvChipCounts.has_incentive}
                                </span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setSpvChip('qualified_so')}
                                className={`px-2.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 ${
                                    spvChip === 'qualified_so'
                                        ? 'bg-indigo-600 text-white shadow-sm'
                                        : 'bg-indigo-50/70 text-indigo-700 border border-indigo-200 hover:bg-indigo-100/70'
                                }`}
                            >
                                <span>✅ SO &ge;80% (Lolos VTKP)</span>
                                <span
                                    className={`text-[10px] px-1.5 py-0.5 rounded-full leading-none ${
                                        spvChip === 'qualified_so'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-indigo-100 text-indigo-800'
                                    }`}
                                >
                                    {spvChipCounts.qualified_so}
                                </span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setSpvChip('under_so')}
                                className={`px-2.5 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 ${
                                    spvChip === 'under_so'
                                        ? 'bg-rose-600 text-white shadow-sm'
                                        : 'bg-rose-50/70 text-rose-700 border border-rose-200 hover:bg-rose-100/70'
                                }`}
                            >
                                <span>⚠️ SO &lt;80% (Hangus VTKP)</span>
                                <span
                                    className={`text-[10px] px-1.5 py-0.5 rounded-full leading-none ${
                                        spvChip === 'under_so' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800'
                                    }`}
                                >
                                    {spvChipCounts.under_so}
                                </span>
                            </button>
                        </div>
                    </div>

                    {/* Counter SPV */}
                    <div className="mx-5 flex items-center justify-between text-[11px] text-gray-500">
                        <span>
                            Menampilkan <strong className="text-gray-800">{filteredSpvs.length}</strong> Supervisor
                        </span>
                        {(spvSearch.trim() !== '' || spvChip !== 'all') && (
                            <button
                                type="button"
                                onClick={() => {
                                    setSpvSearch('');
                                    setSpvChip('all');
                                }}
                                className="text-teal-600 font-bold hover:underline flex items-center gap-1"
                            >
                                <ArrowPathIcon className="w-3 h-3" /> Reset Filter
                            </button>
                        )}
                    </div>

                    {/* List of SPVs */}
                    <div className="mx-4 space-y-2.5">
                        {filteredSpvs.length === 0 ? (
                            <div className="bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm text-xs">
                                <UserGroupIcon className="w-10 h-10 text-gray-300 mx-auto mb-2" />
                                <p className="font-bold text-gray-700">Tidak ada supervisor ditemukan</p>
                            </div>
                        ) : (
                            filteredSpvs.map((spv, idx) => (
                                <SpvRowCard
                                    key={`${spv.supervisor_code}_${idx}`}
                                    spv={spv}
                                    onClick={() => setSelectedSpv(spv)}
                                />
                            ))
                        )}
                    </div>
                </div>
            )}

            {/* =========================================================
                3. KACAB MODE CONTENT
               ========================================================= */}
            {teamMode === 'KACAB' && (
                <div className="space-y-3">
                    <div className="mx-4 bg-white rounded-2xl border border-gray-200/80 p-3 shadow-sm">
                        <div className="relative">
                            <MagnifyingGlassIcon className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input
                                type="text"
                                value={kacabSearch}
                                onChange={(e) => setKacabSearch(e.target.value)}
                                placeholder="Cari cabang, nama kacab, atau distributor..."
                                className="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-8 py-2 text-xs text-gray-800 placeholder-gray-400 focus:bg-white focus:border-purple-500 focus:ring-1 focus:ring-purple-200 outline-none"
                            />
                            {kacabSearch && (
                                <button
                                    type="button"
                                    onClick={() => setKacabSearch('')}
                                    className="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    <XMarkIcon className="w-4 h-4" />
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="mx-4 space-y-2.5">
                        {filteredKacabs.length === 0 ? (
                            <div className="bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm text-xs">
                                <BuildingOffice2Icon className="w-10 h-10 text-gray-300 mx-auto mb-2" />
                                <p className="font-bold text-gray-700">Tidak ada data cabang ditemukan</p>
                            </div>
                        ) : (
                            filteredKacabs.map((kacab, idx) => (
                                <div
                                    key={`${kacab.cabang}_${idx}`}
                                    className="bg-white rounded-2xl p-3.5 border border-gray-200/80 shadow-sm"
                                >
                                    <div className="flex items-start justify-between gap-2.5">
                                        <div className="min-w-0 flex-1">
                                            <h4 className="text-xs sm:text-sm font-extrabold text-gray-900 truncate">
                                                Cabang: {kacab.cabang}
                                            </h4>
                                            <p className="text-[11px] text-gray-500 truncate mt-0.5 font-medium">
                                                Kacab: <strong className="text-gray-700">{kacab.nama_kacab || '-'}</strong> • {kacab.area_name}
                                            </p>
                                        </div>
                                        <div className="text-right shrink-0">
                                            <span className="text-[10px] text-gray-400 font-semibold uppercase block">
                                                Transfer (Net)
                                            </span>
                                            <span
                                                className={`text-sm font-extrabold block ${
                                                    kacab.trf > 0 ? 'text-purple-700' : 'text-gray-400'
                                                }`}
                                            >
                                                {formatRupiah(kacab.trf)}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="mt-2.5 bg-gray-50/80 rounded-xl p-2 border border-gray-100">
                                        <div className="flex items-center justify-between text-[11px] mb-1">
                                            <span className="text-gray-500 font-medium">
                                                Real SO: <strong className="text-gray-800">{formatCompactRupiah(kacab.sell_out)}</strong>
                                                <span className="text-gray-400"> / {formatCompactRupiah(kacab.target)}</span>
                                            </span>
                                            <span
                                                className={`font-extrabold ${
                                                    kacab.percentage >= 100
                                                        ? 'text-emerald-600'
                                                        : kacab.percentage >= 80
                                                        ? 'text-amber-600'
                                                        : 'text-rose-600'
                                                }`}
                                            >
                                                {formatNumber(kacab.percentage)}%
                                            </span>
                                        </div>
                                        <div className="w-full bg-gray-200/80 h-1.5 rounded-full overflow-hidden">
                                            <div
                                                className={`h-full rounded-full ${
                                                    kacab.percentage >= 100
                                                        ? 'bg-emerald-500'
                                                        : kacab.percentage >= 80
                                                        ? 'bg-amber-500'
                                                        : 'bg-rose-500'
                                                }`}
                                                style={{ width: `${Math.min(kacab.percentage, 100)}%` }}
                                            />
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>
            )}

            {/* Modal Detail Salesman (SE) */}
            <SalesmanDetailSheet
                isOpen={!!selectedSalesman}
                onClose={() => setSelectedSalesman(null)}
                salesman={selectedSalesman}
            />

            {/* Modal Detail Supervisor (SPV) */}
            <SpvDetailSheet
                isOpen={!!selectedSpv}
                onClose={() => setSelectedSpv(null)}
                spv={selectedSpv}
            />
        </div>
    );
}
