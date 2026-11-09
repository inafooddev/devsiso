import React, { useState, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import MobileLayout from '../../Layouts/MobileLayout';
import CompactStoreRow from './Components/CompactStoreRow';
import EditScheduleSheet from './Components/EditScheduleSheet';
import DeleteScheduleSheet from './Components/DeleteScheduleSheet';
import VisitDetailSheet from './Components/VisitDetailSheet';
import ApprovalList from './Components/ApprovalList';
import BottomNav, { JksTabType } from './Components/BottomNav';
import {
    JksItem,
    VisitSummaryItem,
    KpiSummary,
    DistributorOption,
    SalesmanOption,
    ApprovalItem,
    DAY_SHORT_NAMES,
    WEEK_OPTIONS,
    DayKeyType,
    WeekFilterType,
    PAGINATION_LIMIT,
} from './constants';
import {
    ShieldCheckIcon,
    ShieldExclamationIcon,
    BuildingStorefrontIcon,
    ArrowPathIcon,
    UserIcon,
} from '@heroicons/react/24/outline';

interface IndexProps {
    user: any;
    distributors: DistributorOption[];
    salesmans: SalesmanOption[];
    currentFilters: {
        distributor: string;
        salesman: string;
        bulan: string;
    };
    jksList: JksItem[];
    visitSummary: Record<string, VisitSummaryItem>;
    kpiSummary: KpiSummary;
    approvals?: ApprovalItem[];
    approvalCounts?: {
        pending: number;
        approved: number;
        rejected: number;
    };
    pendingApprovalsCount?: number;
}

const DEFAULT_DAY: DayKeyType = 'h1'; // Default Hari Senin
const DEFAULT_WEEK: WeekFilterType = 'w1'; // Default Minggu 1 (M1)

export default function Index({
    user,
    distributors = [],
    salesmans = [],
    currentFilters,
    jksList = [],
    visitSummary = {},
    approvals = [],
    approvalCounts,
    pendingApprovalsCount = 0,
}: IndexProps) {
    const [activeTab, setActiveTab] = useState<JksTabType>('jadwal');
    const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);
    const [selectedDay, setSelectedDay] = useState<DayKeyType>(DEFAULT_DAY);
    const [selectedWeek, setSelectedWeek] = useState<WeekFilterType>(DEFAULT_WEEK);
    const [displayLimit, setDisplayLimit] = useState(PAGINATION_LIMIT);

    // Modal Sheet States
    const [editingItem, setEditingItem] = useState<JksItem | null>(null);
    const [deletingItem, setDeletingItem] = useState<JksItem | null>(null);
    const [detailItem, setDetailItem] = useState<JksItem | null>(null);

    const showToast = (message: string, type: 'success' | 'error' = 'success') => {
        setToast({ message, type });
        setTimeout(() => setToast(null), 3500);
    };

    // Handler Ganti Distributor
    const handleDistributorChange = (distributorCode: string) => {
        setDisplayLimit(PAGINATION_LIMIT);
        router.get(
            '/mobile/jks-salesman',
            {
                distributor: distributorCode,
                salesman: '', // reset salesman saat ganti distributor
                bulan: currentFilters.bulan,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onStart: () => showToast('Memuat data distributor...', 'success'),
            }
        );
    };

    // Handler Ganti Salesman
    const handleSalesmanChange = (salesmanCode: string) => {
        setDisplayLimit(PAGINATION_LIMIT);
        router.get(
            '/mobile/jks-salesman',
            {
                distributor: currentFilters.distributor,
                salesman: salesmanCode,
                bulan: currentFilters.bulan,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onStart: () => showToast('Memuat data salesman...', 'success'),
            }
        );
    };

    // Filter Logic Cepat & Rapat di Client
    const filteredData = useMemo(() => {
        let result = [...jksList];

        // 1. Filter Hari (Pill Luar: Sen - Sab, Non-Rute)
        if (selectedDay === 'non_rute') {
            result = result.filter((item) => {
                const hasDay = item.h1 === 'Y' || item.h2 === 'Y' || item.h3 === 'Y' || item.h4 === 'Y' || item.h5 === 'Y' || item.h6 === 'Y';
                const hasWeek = item.w1 === 'Y' || item.w2 === 'Y' || item.w3 === 'Y' || item.w4 === 'Y';
                return !hasDay || !hasWeek || item.h7 === 'Y';
            });
        } else {
            result = result.filter((item) => (item as any)[selectedDay] === 'Y');

            // 2. Filter Week (Pill Luar: M1, M2, M3, M4) - hanya berlaku jika bukan Non-Rute
            result = result.filter((item) => item[selectedWeek] === 'Y');
        }

        return result;
    }, [jksList, selectedDay, selectedWeek]);

    const displayedData = useMemo(() => {
        return filteredData.slice(0, displayLimit);
    }, [filteredData, displayLimit]);

    // Callback Sukses Edit / Hapus
    const handleActionSuccess = (msg: string) => {
        showToast(msg, 'success');
        router.reload({
            only: ['jksList', 'kpiSummary', 'approvals', 'approvalCounts', 'pendingApprovalsCount'],
            preserveScroll: true,
        });
    };

    return (
        <MobileLayout
            user={user}
            title={activeTab === 'jadwal' ? 'JKS Salesman' : 'Pengajuan JKS'}
            backUrl="/mobile/home"
            bottomNavigation={
                <BottomNav
                    activeTab={activeTab}
                    onTabSwitch={setActiveTab}
                    pendingApprovalsCount={pendingApprovalsCount}
                />
            }
        >
            <Head title="JKS Salesman (Call Plan)" />

            {/* Floating Toast Notification */}
            {toast && (
                <div
                    onClick={() => setToast(null)}
                    className={`fixed top-20 left-4 right-4 z-[100] px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold text-white transition-all cursor-pointer animate-slide-down ${
                        toast.type === 'success'
                            ? 'bg-emerald-500 shadow-emerald-500/20'
                            : 'bg-rose-500 shadow-rose-500/20'
                    }`}
                >
                    {toast.type === 'success' ? (
                        <ShieldCheckIcon className="w-5 h-5 shrink-0" />
                    ) : (
                        <ShieldExclamationIcon className="w-5 h-5 shrink-0" />
                    )}
                    <span className="flex-1">{toast.message}</span>
                </div>
            )}

            {/* Sticky Header: Dropdown Distributor, Salesman & Segmented Pill Filters (Hanya di Tab Jadwal) */}
            {activeTab === 'jadwal' && (
                <div className="sticky top-16 md:top-20 z-30 bg-white/95 backdrop-blur-md pt-2.5 pb-2 border-b border-slate-200/80 shadow-sm space-y-2">
                    {/* 1. Baris Pilih Distributor & Salesman */}
                    <div className="px-3.5 grid grid-cols-2 gap-2">
                        {/* Pilih Distributor */}
                        <div>
                            <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
                                Distributor
                            </label>
                            <select
                                value={currentFilters.distributor || ''}
                                onChange={(e) => handleDistributorChange(e.target.value)}
                                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs text-slate-800 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 outline-none truncate"
                            >
                                <option value="">-- Pilih Distributor --</option>
                                {distributors.map((d) => (
                                    <option key={d.distributor_code} value={d.distributor_code}>
                                        {d.distributor_name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Pilih Salesman (Wajib 1 Salesman) */}
                        <div>
                            <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
                                Salesman (SE)
                            </label>
                            <select
                                value={currentFilters.salesman || ''}
                                onChange={(e) => handleSalesmanChange(e.target.value)}
                                disabled={!currentFilters.distributor}
                                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs text-slate-800 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 outline-none truncate disabled:opacity-50 disabled:bg-slate-100"
                            >
                                <option value="">-- Pilih Salesman --</option>
                                {salesmans.map((s) => (
                                    <option key={s.salesman_code} value={s.salesman_code}>
                                        {s.salesman_name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* 2. Pill Filter Hari Penuh (7 Kolom Full-Width tanpa label) */}
                    <div className="px-3.5">
                        <div className="grid grid-cols-7 gap-1 bg-slate-100/90 p-0.5 rounded-xl border border-slate-200/60">
                            {Object.entries(DAY_SHORT_NAMES).map(([key, label]) => {
                                const isSelected = selectedDay === key;
                                return (
                                    <button
                                        key={key}
                                        type="button"
                                        onClick={() => {
                                            setSelectedDay(key as DayKeyType);
                                            setDisplayLimit(PAGINATION_LIMIT);
                                        }}
                                        className={`py-1.5 rounded-lg text-xs font-bold text-center transition-all ${
                                            isSelected
                                                ? 'bg-indigo-600 text-white shadow-sm'
                                                : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'
                                        }`}
                                    >
                                        {label}
                                    </button>
                                );
                            })}
                            <button
                                type="button"
                                onClick={() => {
                                    setSelectedDay('non_rute');
                                    setDisplayLimit(PAGINATION_LIMIT);
                                }}
                                className={`py-1.5 px-0.5 rounded-lg text-xs font-bold text-center transition-all truncate ${
                                    selectedDay === 'non_rute'
                                        ? 'bg-amber-600 text-white shadow-sm'
                                        : 'text-amber-700 hover:bg-amber-100/60'
                                }`}
                            >
                                Non
                            </button>
                        </div>
                    </div>

                    {/* 3. Pill Filter Minggu (4 Kolom Full-Width tanpa label M1-M4) */}
                    <div className="px-3.5">
                        <div className="grid grid-cols-4 gap-1 bg-slate-100/90 p-0.5 rounded-xl border border-slate-200/60">
                            {WEEK_OPTIONS.map((w) => {
                                const isSelected = selectedDay !== 'non_rute' && selectedWeek === w.key;
                                return (
                                    <button
                                        key={w.key}
                                        type="button"
                                        onClick={() => {
                                            if (selectedDay === 'non_rute') {
                                                setSelectedDay(DEFAULT_DAY);
                                            }
                                            setSelectedWeek(w.key);
                                            setDisplayLimit(PAGINATION_LIMIT);
                                        }}
                                        className={`py-1.5 rounded-lg text-xs font-bold text-center transition-all ${
                                            isSelected
                                                ? 'bg-slate-900 text-white shadow-sm'
                                                : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'
                                        }`}
                                    >
                                        {w.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </div>
            )}

            {/* Main Content Area */}
            <main className="flex-1 px-3.5 pt-2.5 pb-10 space-y-2">
                {activeTab === 'jadwal' ? (
                    <>
                        {/* Counter Bar Rapat */}
                        <div className="px-1 flex items-center justify-between text-xs text-slate-500">
                            <span className="font-medium text-xs">
                                Menampilkan <strong className="text-slate-800">{filteredData.length}</strong> Toko
                            </span>
                            {(selectedDay !== DEFAULT_DAY || selectedWeek !== DEFAULT_WEEK) && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSelectedDay(DEFAULT_DAY);
                                        setSelectedWeek(DEFAULT_WEEK);
                                        setDisplayLimit(PAGINATION_LIMIT);
                                    }}
                                    className="text-xs font-bold text-indigo-600 hover:underline"
                                >
                                    Reset Filter
                                </button>
                            )}
                        </div>

                        {/* List Toko (Sistem Tabel Simple & Rapat: Kolom 1 Nama & Alamat, Kolom 2 Aksi Edit Hapus Detail) */}
                        {displayedData.length > 0 ? (
                            <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
                                {displayedData.map((item, idx) => (
                                    <CompactStoreRow
                                        key={item.id}
                                        item={item}
                                        index={idx}
                                        onEdit={(selected) => setEditingItem(selected)}
                                        onDelete={(selected) => setDeletingItem(selected)}
                                        onDetail={(selected) => setDetailItem(selected)}
                                    />
                                ))}
                            </div>
                        ) : !currentFilters.distributor ? (
                            <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-3">
                                <div className="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 mb-2.5">
                                    <BuildingStorefrontIcon className="w-6 h-6" />
                                </div>
                                <h4 className="text-xs font-black text-slate-800 uppercase tracking-wide">
                                    Pilih Distributor Terlebih Dahulu
                                </h4>
                                <p className="text-xs text-slate-400 mt-1 max-w-xs leading-relaxed">
                                    Silakan pilih distributor pada menu di atas untuk memuat daftar salesman dan jadwal kunjungan.
                                </p>
                            </div>
                        ) : !currentFilters.salesman ? (
                            <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-3">
                                <div className="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 mb-2.5">
                                    <UserIcon className="w-6 h-6" />
                                </div>
                                <h4 className="text-xs font-black text-slate-800 uppercase tracking-wide">
                                    Pilih 1 Salesman (SE)
                                </h4>
                                <p className="text-xs text-slate-400 mt-1 max-w-xs leading-relaxed">
                                    Pilih 1 salesman untuk memuat jadwal kunjungan toko (otomatis tampil hari Senin & M1).
                                </p>
                            </div>
                        ) : (
                            <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-3">
                                <div className="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mb-2">
                                    <BuildingStorefrontIcon className="w-6 h-6" />
                                </div>
                                <h4 className="text-xs font-black text-slate-800 uppercase tracking-wide">
                                    Tidak Ada Toko Ditemukan
                                </h4>
                                <p className="text-xs text-slate-400 mt-1 max-w-xs leading-relaxed">
                                    {selectedDay === 'non_rute'
                                        ? 'Tidak ada toko Non-Rute untuk salesman ini.'
                                        : `Tidak ada jadwal toko untuk salesman ini pada hari ${DAY_SHORT_NAMES[selectedDay]} (${selectedWeek.toUpperCase()}).`}
                                </p>
                            </div>
                        )}

                        {/* Pagination: Muat Lebih Banyak */}
                        {filteredData.length > displayLimit && (
                            <div className="pt-2 pb-4 flex justify-center">
                                <button
                                    type="button"
                                    onClick={() => setDisplayLimit((prev) => prev + PAGINATION_LIMIT)}
                                    className="px-5 py-2 bg-white border border-slate-200 text-slate-700 hover:text-indigo-600 text-xs font-bold rounded-xl shadow-sm transition-all active:scale-95 flex items-center gap-1.5"
                                >
                                    <ArrowPathIcon className="w-3.5 h-3.5 text-indigo-600" />
                                    Muat Lebih Banyak ({filteredData.length - displayLimit} tersisa)
                                </button>
                            </div>
                        )}
                    </>
                ) : (
                    <ApprovalList
                        approvals={approvals}
                        initialCounts={approvalCounts}
                        distributors={distributors}
                        selectedDistributorCode={currentFilters.distributor}
                    />
                )}
            </main>

            {/* Modal 1: Edit Jadwal (Pindah SE, Hari, Minggu, Alasan) */}
            <EditScheduleSheet
                item={editingItem}
                salesmans={salesmans}
                distributorCode={currentFilters.distributor}
                onClose={() => setEditingItem(null)}
                onSuccess={handleActionSuccess}
                onError={(err) => showToast(err, 'error')}
            />

            {/* Modal 2: Hapus Jadwal (Soft vs Hard Delete) */}
            <DeleteScheduleSheet
                item={deletingItem}
                distributorCode={currentFilters.distributor}
                onClose={() => setDeletingItem(null)}
                onSuccess={handleActionSuccess}
                onError={(err) => showToast(err, 'error')}
            />

            {/* Modal 3: Detail Lengkap Toko & Riwayat Realisasi Kunjungan */}
            <VisitDetailSheet
                item={detailItem}
                visit={detailItem ? visitSummary[detailItem.uniq_kode] : undefined}
                distributorCode={currentFilters.distributor}
                bulan={currentFilters.bulan}
                onClose={() => setDetailItem(null)}
            />
        </MobileLayout>
    );
}
