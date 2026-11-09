import React, { useState, useMemo, useEffect, useRef } from 'react';
import { ApprovalItem, DistributorOption } from '../constants';
import {
    ClipboardDocumentCheckIcon,
    UserIcon,
    PencilSquareIcon,
    TrashIcon,
    PlusCircleIcon,
    ArrowsRightLeftIcon,
    ChevronDownIcon,
    CalendarDaysIcon,
    ArrowPathIcon,
} from '@heroicons/react/24/outline';

interface ApprovalCounts {
    pending: number;
    approved: number;
    rejected: number;
}

interface ApprovalListProps {
    approvals: ApprovalItem[];
    initialCounts?: ApprovalCounts;
    distributors: DistributorOption[];
    selectedDistributorCode?: string;
}

type FilterStatusType = 'PENDING' | 'APPROVED' | 'REJECTED';

interface SalesmanGroup {
    key: string;
    salesman_name: string;
    salesman_code: string;
    distributor_name: string;
    distributor_code: string;
    total_items: number;
    pending_count: number;
    approved_count: number;
    rejected_count: number;
    action_counts: {
        edit: number;
        hapus: number;
        tambah: number;
        other: number;
    };
    items: ApprovalItem[];
}

const GROUP_PAGE_SIZE = 25;
const STORE_PAGE_SIZE = 30;

export default function ApprovalList({
    approvals = [],
    initialCounts,
    distributors = [],
}: ApprovalListProps) {
    // Default filter status: PENDING, Default filter distributor: ALL (Semua Distributor)
    const [filterStatus, setFilterStatus] = useState<FilterStatusType>('PENDING');
    const [filterDistributor, setFilterDistributor] = useState<string>('ALL');

    // Data items & counts aktif (diambil on-demand per status & distributor agar ringan)
    const [activeItems, setActiveItems] = useState<ApprovalItem[]>(approvals);
    const [counts, setCounts] = useState<ApprovalCounts>(
        initialCounts || {
            pending: approvals.filter((a) => a.status === 'PENDING').length,
            approved: approvals.filter((a) => a.status === 'APPROVED' || a.status === 'AUTO_APPROVED').length,
            rejected: approvals.filter((a) => a.status === 'REJECTED').length,
        }
    );
    const [isLoading, setIsLoading] = useState<boolean>(false);

    // Default accordion: tertutup (null), dan hanya bisa buka 1 (single-expand)
    const [expandedKey, setExpandedKey] = useState<string | null>(null);

    // DOM Chunking: membatasi jumlah grup salesman & jumlah toko di dalam accordion yang terbuka agar DOM selalu < 50 elemen
    const [groupDisplayLimit, setGroupDisplayLimit] = useState<number>(GROUP_PAGE_SIZE);
    const [storeDisplayLimit, setStoreDisplayLimit] = useState<number>(STORE_PAGE_SIZE);

    // In-memory cache agar perpindahan tab yang sudah pernah dibuka berlangsung instan (0ms)
    const cacheRef = useRef<Record<string, { items: ApprovalItem[]; counts: ApprovalCounts }>>({
        PENDING_ALL: {
            items: approvals,
            counts: initialCounts || {
                pending: approvals.filter((a) => a.status === 'PENDING').length,
                approved: 0,
                rejected: 0,
            },
        },
    });

    // Sinkronkan ulang & invalidate cache jika props `approvals` diperbarui dari parent (misal seusai submit jadwal baru)
    useEffect(() => {
        const updatedCounts = initialCounts || counts;
        cacheRef.current = {
            PENDING_ALL: {
                items: approvals,
                counts: updatedCounts,
            },
        };
        if (filterStatus === 'PENDING' && filterDistributor === 'ALL') {
            setActiveItems(approvals);
            if (initialCounts) setCounts(initialCounts);
        }
    }, [approvals, initialCounts]);

    // Fetch data on-demand saat user mengganti tab status atau filter distributor
    const fetchApprovalData = async (status: FilterStatusType, distributor: string) => {
        const cacheKey = `${status}_${distributor}`;

        if (cacheRef.current[cacheKey]) {
            setActiveItems(cacheRef.current[cacheKey].items);
            setCounts(cacheRef.current[cacheKey].counts);
            return;
        }

        setIsLoading(true);
        try {
            const params = new URLSearchParams({
                status,
                distributor,
            });
            const response = await fetch(`/mobile/jks-salesman/approvals?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (response.ok) {
                const data = await response.json();
                const fetchedItems: ApprovalItem[] = data.items || [];
                const fetchedCounts: ApprovalCounts = data.counts || counts;

                cacheRef.current[cacheKey] = {
                    items: fetchedItems,
                    counts: fetchedCounts,
                };
                setActiveItems(fetchedItems);
                setCounts(fetchedCounts);
            }
        } catch (err) {
            console.error('Gagal memuat data pengajuan:', err);
        } finally {
            setIsLoading(false);
        }
    };

    // Kelompokkan pengajuan per Salesman
    const salesmanGroups = useMemo<SalesmanGroup[]>(() => {
        const map = new Map<string, SalesmanGroup>();

        for (const item of activeItems) {
            const sName = (item.salesman_name && item.salesman_name !== '-')
                ? item.salesman_name
                : 'Tanpa Salesman';
            const sCode = item.salesman_name || '-';
            const groupKey = `${item.distributor_code}_${sName}`;

            if (!map.has(groupKey)) {
                map.set(groupKey, {
                    key: groupKey,
                    salesman_name: sName,
                    salesman_code: sCode,
                    distributor_name: item.distributor_name || item.distributor_code,
                    distributor_code: item.distributor_code,
                    total_items: 0,
                    pending_count: 0,
                    approved_count: 0,
                    rejected_count: 0,
                    action_counts: { edit: 0, hapus: 0, tambah: 0, other: 0 },
                    items: [],
                });
            }

            const group = map.get(groupKey)!;
            group.total_items++;
            group.items.push(item);

            if (item.status === 'PENDING') group.pending_count++;
            else if (item.status === 'APPROVED' || item.status === 'AUTO_APPROVED') group.approved_count++;
            else if (item.status === 'REJECTED') group.rejected_count++;

            if (item.action_type === 'EDIT_INDIVIDU') group.action_counts.edit++;
            else if (item.action_type === 'DELETE_INDIVIDU' || item.action_type === 'DELETE_MASSAL') group.action_counts.hapus++;
            else if (item.action_type === 'TAMBAH_JADWAL') group.action_counts.tambah++;
            else group.action_counts.other++;
        }

        // Urutkan: jumlah toko terbanyak di atas, lalu abjad nama salesman
        return Array.from(map.values()).sort((a, b) => {
            if (a.total_items !== b.total_items) {
                return b.total_items - a.total_items;
            }
            return a.salesman_name.localeCompare(b.salesman_name);
        });
    }, [activeItems]);

    const displayedGroups = useMemo(() => {
        return salesmanGroups.slice(0, groupDisplayLimit);
    }, [salesmanGroups, groupDisplayLimit]);

    // Single-expand toggle: jika klik yang sedang terbuka maka tutup, jika klik yang lain maka buka yang baru & otomatis tutup yang lama
    const toggleGroup = (key: string) => {
        setStoreDisplayLimit(STORE_PAGE_SIZE);
        setExpandedKey((prev) => (prev === key ? null : key));
    };

    const handleStatusChange = (status: FilterStatusType) => {
        if (status === filterStatus) return;
        setFilterStatus(status);
        setExpandedKey(null);
        setGroupDisplayLimit(GROUP_PAGE_SIZE);
        setStoreDisplayLimit(STORE_PAGE_SIZE);
        fetchApprovalData(status, filterDistributor);
    };

    const handleDistributorChange = (distCode: string) => {
        setFilterDistributor(distCode);
        setExpandedKey(null);
        setGroupDisplayLimit(GROUP_PAGE_SIZE);
        setStoreDisplayLimit(STORE_PAGE_SIZE);
        fetchApprovalData(filterStatus, distCode);
    };

    const getActionBadge = (actionType: string, deleteType?: 'SOFT' | 'HARD' | null) => {
        switch (actionType) {
            case 'EDIT_INDIVIDU':
                return (
                    <span className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-3xs sm:text-2xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                        <PencilSquareIcon className="w-3 h-3 shrink-0" />
                        <span>Edit</span>
                    </span>
                );
            case 'DELETE_INDIVIDU':
            case 'DELETE_MASSAL':
                return (
                    <span className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-3xs sm:text-2xs font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                        <TrashIcon className="w-3 h-3 shrink-0" />
                        <span>Hapus{deleteType ? ` (${deleteType})` : ''}</span>
                    </span>
                );
            case 'TAMBAH_JADWAL':
                return (
                    <span className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-3xs sm:text-2xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <PlusCircleIcon className="w-3 h-3 shrink-0" />
                        <span>Tambah</span>
                    </span>
                );
            case 'TUKAR_HARI':
            case 'TUKAR_MINGGU':
            case 'TUKAR_SALESMAN':
                return (
                    <span className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-3xs sm:text-2xs font-bold bg-cyan-50 text-cyan-700 border border-cyan-200/60">
                        <ArrowsRightLeftIcon className="w-3 h-3 shrink-0" />
                        <span>Tukar</span>
                    </span>
                );
            default:
                return (
                    <span className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-3xs sm:text-2xs font-bold bg-slate-100 text-slate-700">
                        {actionType}
                    </span>
                );
        }
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'PENDING':
                return (
                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-3xs sm:text-2xs font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                        <span className="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse shrink-0" />
                        <span>Pending</span>
                    </span>
                );
            case 'APPROVED':
                return (
                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-3xs sm:text-2xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                        <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" />
                        <span>Disetujui</span>
                    </span>
                );
            case 'AUTO_APPROVED':
                return (
                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-3xs sm:text-2xs font-bold bg-sky-50 text-sky-700 border border-sky-200/80">
                        <span className="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0" />
                        <span>Auto</span>
                    </span>
                );
            case 'REJECTED':
                return (
                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-3xs sm:text-2xs font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                        <span className="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0" />
                        <span>Ditolak</span>
                    </span>
                );
            default:
                return (
                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-3xs sm:text-2xs font-bold bg-slate-100 text-slate-700">
                        {status}
                    </span>
                );
        }
    };

    const statusTabs: {
        key: FilterStatusType;
        label: string;
        count: number;
        activeClass: string;
        inactiveClass: string;
    }[] = [
        {
            key: 'PENDING',
            label: 'Pending',
            count: counts.pending,
            activeClass: 'bg-amber-500 text-white shadow-sm',
            inactiveClass: 'text-amber-700 hover:bg-amber-50/80',
        },
        {
            key: 'APPROVED',
            label: 'Disetujui',
            count: counts.approved,
            activeClass: 'bg-emerald-600 text-white shadow-sm',
            inactiveClass: 'text-emerald-700 hover:bg-emerald-50/80',
        },
        {
            key: 'REJECTED',
            label: 'Ditolak',
            count: counts.rejected,
            activeClass: 'bg-rose-600 text-white shadow-sm',
            inactiveClass: 'text-rose-700 hover:bg-rose-50/80',
        },
    ];

    return (
        <div className="space-y-3 pb-8">
            {/* 1. Card Filter: Distributor & Segmented Status Tabs (Pending, Disetujui, Ditolak) */}
            <div className="bg-white rounded-2xl border border-slate-200/80 p-3 shadow-sm space-y-2.5">
                {/* Filter Distributor (Default: Semua Distributor) */}
                {distributors.length > 0 && (
                    <div className="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 pb-2.5 border-b border-slate-100">
                        <label className="text-2xs font-bold uppercase tracking-wider text-slate-500 shrink-0">
                            Filter Distributor
                        </label>
                        <select
                            value={filterDistributor}
                            onChange={(e) => handleDistributorChange(e.target.value)}
                            className="w-full sm:flex-1 min-w-0 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 outline-none truncate"
                        >
                            <option value="ALL">Semua Distributor</option>
                            {distributors.map((d) => (
                                <option key={d.distributor_code} value={d.distributor_code}>
                                    {d.distributor_name}
                                </option>
                            ))}
                        </select>
                    </div>
                )}

                {/* Segmented Status Filter (3 Kolom: Pending, Disetujui, Ditolak) */}
                <div className="grid grid-cols-3 gap-1.5 bg-slate-100/90 p-1 rounded-xl border border-slate-200/60">
                    {statusTabs.map((tab) => {
                        const isActive = filterStatus === tab.key;
                        return (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => handleStatusChange(tab.key)}
                                className={`py-1.5 px-2 rounded-lg text-center transition-all flex flex-col items-center justify-center ${
                                    isActive ? tab.activeClass : tab.inactiveClass
                                }`}
                            >
                                <span className="text-xs sm:text-sm font-black leading-tight">
                                    {tab.count.toLocaleString('id-ID')}
                                </span>
                                <span className="text-3xs sm:text-2xs font-semibold tracking-wide leading-tight mt-0.5 truncate max-w-full">
                                    {tab.label}
                                </span>
                            </button>
                        );
                    })}
                </div>
            </div>

            {/* 2. Sub-Header: Ringkasan Grup Salesman */}
            <div className="px-1 flex items-center justify-between text-2xs sm:text-xs text-slate-500">
                <span>
                    Menampilkan <strong className="text-slate-800 font-bold">{salesmanGroups.length}</strong> Salesman •{' '}
                    <strong className="text-slate-800 font-bold">{activeItems.length.toLocaleString('id-ID')}</strong> Toko Diajukan
                </span>
            </div>

            {/* 3. Daftar Accordion per Salesman (Single-Expand, Default Close, DOM Chunked) */}
            {isLoading ? (
                <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-2">
                    <ArrowPathIcon className="w-6 h-6 text-indigo-600 animate-spin mb-2" />
                    <p className="text-xs font-bold text-slate-700">Memuat data pengajuan...</p>
                </div>
            ) : displayedGroups.length > 0 ? (
                <div className="space-y-2.5">
                    {displayedGroups.map((group) => {
                        const isExpanded = expandedKey === group.key;
                        const visibleStores = isExpanded ? group.items.slice(0, storeDisplayLimit) : [];
                        const remainingStoresCount = group.items.length - visibleStores.length;

                        return (
                            <div
                                key={group.key}
                                className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden transition-all"
                            >
                                {/* Accordion Header Salesman */}
                                <button
                                    type="button"
                                    onClick={() => toggleGroup(group.key)}
                                    className="w-full px-3.5 py-3 flex items-center justify-between gap-2.5 text-left hover:bg-slate-50/80 active:bg-slate-100/60 transition-colors"
                                >
                                    {/* Avatar Salesman */}
                                    <div className="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                                        <UserIcon className="w-4 h-4 stroke-[2.2]" />
                                    </div>

                                    {/* Informasi Utama Salesman (2 Baris Terstruktur) */}
                                    <div className="min-w-0 flex-1">
                                        {/* Baris Atas: Nama Salesman (Kiri) + Badge Ringkasan (Kanan, Tidak Terpotong) */}
                                        <div className="flex items-center justify-between gap-2">
                                            <h4 className="text-xs sm:text-sm font-extrabold text-slate-900 truncate min-w-0 flex-1">
                                                {group.salesman_name}
                                            </h4>

                                            <div className="flex items-center gap-1 shrink-0">
                                                {group.pending_count > 0 && (
                                                    <span className="px-1.5 py-0.5 rounded-md text-3xs sm:text-2xs font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                                        {group.pending_count} Pending
                                                    </span>
                                                )}
                                                <span className="px-1.5 py-0.5 rounded-md text-3xs sm:text-2xs font-bold bg-slate-100 text-slate-600 border border-slate-200/60">
                                                    {group.total_items} Toko
                                                </span>
                                            </div>
                                        </div>

                                        {/* Baris Bawah: Nama PT Distributor + Ringkasan Aksi di Tablet */}
                                        <div className="flex items-center justify-between gap-2 mt-0.5">
                                            <p className="text-2xs sm:text-xs text-slate-500 font-medium truncate">
                                                {group.distributor_name}
                                            </p>

                                            {/* Ringkasan jenis aksi tambahan khusus layar Tablet (sm/md ke atas) */}
                                            <div className="hidden sm:flex items-center gap-1.5 shrink-0 text-3xs font-semibold text-slate-400">
                                                {group.action_counts.edit > 0 && (
                                                    <span className="text-indigo-600">{group.action_counts.edit} Edit</span>
                                                )}
                                                {group.action_counts.hapus > 0 && (
                                                    <span className="text-rose-600">{group.action_counts.hapus} Hapus</span>
                                                )}
                                                {group.action_counts.tambah > 0 && (
                                                    <span className="text-emerald-600">{group.action_counts.tambah} Tambah</span>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Chevron Toggle Indicator */}
                                    <div
                                        className={`w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0 transition-transform duration-200 ${
                                            isExpanded ? 'rotate-180 bg-indigo-50 text-indigo-600' : ''
                                        }`}
                                    >
                                        <ChevronDownIcon className="w-4 h-4 stroke-[2.2]" />
                                    </div>
                                </button>

                                {/* Isi Accordion: Daftar Toko yang Diajukan (Dirender hanya saat dibuka & di-chunk per 30 toko) */}
                                {isExpanded && (
                                    <div className="border-t border-slate-100 divide-y divide-slate-100 bg-slate-50/30">
                                        {visibleStores.map((item, idx) => {
                                            const hasSchedule = item.hari !== '-' || item.minggu !== '-';
                                            const scheduleLabel = [
                                                item.hari !== '-' ? item.hari : '',
                                                item.minggu !== '-' ? `(${item.minggu})` : '',
                                            ]
                                                .filter(Boolean)
                                                .join(' ');

                                            return (
                                                <div
                                                    key={item.id}
                                                    className="px-3.5 py-2.5 bg-white hover:bg-slate-50/80 transition-colors space-y-1"
                                                >
                                                    {/* Baris 1: Nomor Urut + Nama Toko (Kiri) & Badge Aksi + Status (Kanan) */}
                                                    <div className="flex items-start justify-between gap-2">
                                                        <div className="flex items-baseline gap-1.5 min-w-0 flex-1">
                                                            <span className="text-2xs sm:text-xs font-bold text-slate-400 font-mono shrink-0 w-5 text-right">
                                                                {idx + 1}.
                                                            </span>
                                                            <div className="min-w-0 flex-1 flex items-baseline gap-1.5">
                                                                <h5
                                                                    className="text-xs sm:text-sm font-bold text-slate-900 truncate"
                                                                    title={item.store_name}
                                                                >
                                                                    {item.store_name}
                                                                </h5>
                                                                {item.store_code !== '-' && (
                                                                    <span className="hidden sm:inline text-2xs font-mono text-slate-400 shrink-0">
                                                                        #{item.store_code}
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </div>

                                                        <div className="flex items-center gap-1 shrink-0">
                                                            {getActionBadge(item.action_type, item.delete_type)}
                                                            {getStatusBadge(item.status)}
                                                        </div>
                                                    </div>

                                                    {/* Baris 2: Jadwal Kunjungan & Alasan Pengajuan (Lega, Tidak Terpotong oleh Tanggal) */}
                                                    {(hasSchedule || item.reason) && (
                                                        <div className="pl-6 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-2xs sm:text-xs leading-snug">
                                                            {hasSchedule && (
                                                                <span className="inline-flex items-center gap-1 font-bold text-indigo-600 shrink-0">
                                                                    <CalendarDaysIcon className="w-3.5 h-3.5 shrink-0" />
                                                                    <span>{scheduleLabel}</span>
                                                                </span>
                                                            )}

                                                            {hasSchedule && item.reason && (
                                                                <span className="text-slate-300">•</span>
                                                            )}

                                                            {item.reason && (
                                                                <span className="text-slate-600 italic break-words">
                                                                    "{item.reason}"
                                                                </span>
                                                            )}
                                                        </div>
                                                    )}

                                                    {/* Baris 3: Metadata Pemohon & Timestamp */}
                                                    <div className="pl-6 flex items-center justify-between gap-2 text-3xs sm:text-2xs text-slate-400 pt-0.5">
                                                        <span className="truncate" title={item.maker_name}>
                                                            Oleh: <strong className="font-semibold text-slate-500">{item.maker_name}</strong>
                                                        </span>
                                                        <span className="font-mono shrink-0">
                                                            {item.created_at}
                                                        </span>
                                                    </div>

                                                    {/* Baris 4 (Khusus Jika Ditolak): Alasan Penolakan */}
                                                    {item.status === 'REJECTED' && item.reject_reason && (
                                                        <div className="ml-6 mt-1 bg-rose-50/80 border border-rose-200/70 rounded-lg px-2.5 py-1.5 text-2xs text-rose-800">
                                                            <span className="font-bold text-rose-700">Alasan Ditolak: </span>
                                                            <span>{item.reject_reason}</span>
                                                        </div>
                                                    )}
                                                </div>
                                            );
                                        })}

                                        {/* Tombol Load More Toko di Dalam Salesman jika > 30 toko */}
                                        {remainingStoresCount > 0 && (
                                            <div className="p-2.5 bg-slate-50 flex justify-center">
                                                <button
                                                    type="button"
                                                    onClick={() => setStoreDisplayLimit((prev) => prev + STORE_PAGE_SIZE)}
                                                    className="px-4 py-1.5 bg-white border border-slate-200 rounded-xl text-2xs sm:text-xs font-bold text-indigo-600 hover:bg-indigo-50 shadow-sm transition-all active:scale-95 flex items-center gap-1.5"
                                                >
                                                    <ArrowPathIcon className="w-3.5 h-3.5" />
                                                    <span>Tampilkan Lebih Banyak ({remainingStoresCount} toko lagi)</span>
                                                </button>
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                        );
                    })}

                    {/* Tombol Load More Grup Salesman jika > 25 salesman */}
                    {salesmanGroups.length > groupDisplayLimit && (
                        <div className="pt-2 pb-4 flex justify-center">
                            <button
                                type="button"
                                onClick={() => setGroupDisplayLimit((prev) => prev + GROUP_PAGE_SIZE)}
                                className="px-5 py-2 bg-white border border-slate-200 text-slate-700 hover:text-indigo-600 text-xs font-bold rounded-xl shadow-sm transition-all active:scale-95 flex items-center gap-1.5"
                            >
                                <ArrowPathIcon className="w-3.5 h-3.5 text-indigo-600" />
                                <span>Muat Salesman Lainnya ({salesmanGroups.length - groupDisplayLimit} salesman lagi)</span>
                            </button>
                        </div>
                    )}
                </div>
            ) : (
                <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-3">
                    <div className="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mb-2.5">
                        <ClipboardDocumentCheckIcon className="w-6 h-6" />
                    </div>
                    <h4 className="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
                        Tidak Ada Pengajuan
                    </h4>
                    <p className="text-2xs sm:text-xs text-slate-400 mt-1 max-w-xs leading-relaxed">
                        {filterStatus === 'PENDING'
                            ? 'Tidak ada pengajuan yang sedang menunggu persetujuan.'
                            : 'Belum ada riwayat pengajuan jadwal untuk filter status ini.'}
                    </p>
                </div>
            )}
        </div>
    );
}
