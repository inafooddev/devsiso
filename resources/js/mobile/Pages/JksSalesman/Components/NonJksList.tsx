import React, { useState, useMemo, useEffect, useRef } from 'react';
import {
    DistributorOption,
    SalesmanOption,
    NonJksOutletItem,
} from '../constants';
import AddNonJksSheet from './AddNonJksSheet';
import NonJksDetailSheet from './NonJksDetailSheet';
import {
    BuildingStorefrontIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    PlusCircleIcon,
    ChatBubbleBottomCenterTextIcon,
    InformationCircleIcon,
    ArrowPathIcon,
    CheckCircleIcon,
} from '@heroicons/react/24/outline';

interface NonJksListProps {
    distributors: DistributorOption[];
    initialDistributor?: string;
    bulan: string;
    onScheduleSubmitted: (message: string) => void;
    onShowToast: (message: string, type?: 'success' | 'error') => void;
}

type NonJksFilterMode = 'ALL' | 'AVAILABLE' | 'REMARKED' | 'PENDING';

const PAGE_SIZE = 40;

export default function NonJksList({
    distributors = [],
    initialDistributor = '',
    bulan,
    onScheduleSubmitted,
    onShowToast,
}: NonJksListProps) {
    const [selectedDistributor, setSelectedDistributor] = useState<string>(initialDistributor || '');
    const [items, setItems] = useState<NonJksOutletItem[]>([]);
    const [salesmans, setSalesmans] = useState<SalesmanOption[]>([]);
    const [historyLabels, setHistoryLabels] = useState<string[]>([]);
    const [isLoading, setIsLoading] = useState<boolean>(false);

    const [searchQuery, setSearchQuery] = useState<string>('');
    const [filterMode, setFilterMode] = useState<NonJksFilterMode>('ALL');
    const [displayLimit, setDisplayLimit] = useState<number>(PAGE_SIZE);

    // Multi-select state (outlet IDs)
    const [selectedIds, setSelectedIds] = useState<number[]>([]);

    // Modal states
    const [addingOutlets, setAddingOutlets] = useState<NonJksOutletItem[]>([]);
    const [detailOutlet, setDetailOutlet] = useState<NonJksOutletItem | null>(null);

    // In-memory cache per distributor
    const cacheRef = useRef<
        Record<
            string,
            {
                items: NonJksOutletItem[];
                salesmans: SalesmanOption[];
                historyLabels: string[];
            }
        >
    >({});

    const fetchNonJksData = async (distCode: string, forceRefresh = false) => {
        if (!distCode) {
            setItems([]);
            setSalesmans([]);
            setSelectedIds([]);
            return;
        }

        if (!forceRefresh && cacheRef.current[distCode]) {
            const cached = cacheRef.current[distCode];
            setItems(cached.items);
            setSalesmans(cached.salesmans);
            setHistoryLabels(cached.historyLabels);
            return;
        }

        setIsLoading(true);
        try {
            const params = new URLSearchParams({
                distributor: distCode,
                bulan,
            });
            const res = await fetch(`/mobile/jks-salesman/non-jks?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (res.ok) {
                const data = await res.json();
                const fetchedItems: NonJksOutletItem[] = data.items || [];
                const fetchedSalesmans: SalesmanOption[] = data.salesmans || [];
                const fetchedLabels: string[] = data.history_labels || [];

                cacheRef.current[distCode] = {
                    items: fetchedItems,
                    salesmans: fetchedSalesmans,
                    historyLabels: fetchedLabels,
                };
                setItems(fetchedItems);
                setSalesmans(fetchedSalesmans);
                setHistoryLabels(fetchedLabels);
            }
        } catch (err) {
            console.error('Gagal memuat data Outlet Non-JKS:', err);
            onShowToast('Gagal memuat daftar Outlet Non-JKS.', 'error');
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        if (selectedDistributor) {
            fetchNonJksData(selectedDistributor);
        }
    }, []);

    const handleDistributorChange = (distCode: string) => {
        setSelectedDistributor(distCode);
        setSelectedIds([]);
        setSearchQuery('');
        setFilterMode('ALL');
        setDisplayLimit(PAGE_SIZE);
        fetchNonJksData(distCode);
    };

    // Hitung ringkasan jumlah per mode filter
    const modeCounts = useMemo(() => {
        let available = 0;
        let remarked = 0;
        let pending = 0;

        for (const item of items) {
            if (item.pending_approval_id) pending++;
            else available++;
            if (item.remark && item.remark.trim().length > 0) remarked++;
        }

        return {
            ALL: items.length,
            AVAILABLE: available,
            REMARKED: remarked,
            PENDING: pending,
        };
    }, [items]);

    // Filter berdasarkan mode & kata kunci pencarian
    const filteredItems = useMemo(() => {
        let result = items;

        if (filterMode === 'AVAILABLE') {
            result = result.filter((i) => !i.pending_approval_id);
        } else if (filterMode === 'REMARKED') {
            result = result.filter((i) => Boolean(i.remark && i.remark.trim().length > 0));
        } else if (filterMode === 'PENDING') {
            result = result.filter((i) => Boolean(i.pending_approval_id));
        }

        const q = searchQuery.trim().toLowerCase();
        if (q.length > 0) {
            result = result.filter(
                (i) =>
                    i.customer_name.toLowerCase().includes(q) ||
                    i.customer_code.toLowerCase().includes(q) ||
                    (i.customer_eska && i.customer_eska.toLowerCase().includes(q)) ||
                    (i.alamat && i.alamat.toLowerCase().includes(q)) ||
                    (i.remark && i.remark.toLowerCase().includes(q))
            );
        }

        return result;
    }, [items, filterMode, searchQuery]);

    const displayedItems = useMemo(() => {
        return filteredItems.slice(0, displayLimit);
    }, [filteredItems, displayLimit]);

    // Daftar item yang sedang tampil dan bisa dipilih (belum pending)
    const selectableVisibleIds = useMemo(() => {
        return displayedItems.filter((i) => !i.pending_approval_id).map((i) => i.id);
    }, [displayedItems]);

    const isAllVisibleSelected =
        selectableVisibleIds.length > 0 &&
        selectableVisibleIds.every((id) => selectedIds.includes(id));

    const toggleSelectOne = (id: number) => {
        setSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
        );
    };

    const toggleSelectAllVisible = () => {
        if (isAllVisibleSelected) {
            setSelectedIds((prev) => prev.filter((id) => !selectableVisibleIds.includes(id)));
        } else {
            setSelectedIds((prev) => Array.from(new Set([...prev, ...selectableVisibleIds])));
        }
    };

    const handleBulkAddClick = () => {
        const chosen = items.filter((i) => selectedIds.includes(i.id) && !i.pending_approval_id);
        if (chosen.length === 0) return;
        setAddingOutlets(chosen);
    };

    const handleAddSuccess = (message: string, submittedOutletIds: number[], approvalId: number) => {
        // Tandai langsung item yang diajukan sebagai pending_approval_id di state & cache
        const updated = items.map((item) =>
            submittedOutletIds.includes(item.id)
                ? { ...item, pending_approval_id: approvalId }
                : item
        );
        setItems(updated);
        if (selectedDistributor && cacheRef.current[selectedDistributor]) {
            cacheRef.current[selectedDistributor].items = updated;
        }
        setSelectedIds((prev) => prev.filter((id) => !submittedOutletIds.includes(id)));
        onScheduleSubmitted(message);
    };

    const handleRemarkSaved = (outletId: number, newRemark: string, message: string) => {
        const updated = items.map((item) =>
            item.id === outletId ? { ...item, remark: newRemark } : item
        );
        setItems(updated);
        if (selectedDistributor && cacheRef.current[selectedDistributor]) {
            cacheRef.current[selectedDistributor].items = updated;
        }
        onShowToast(message, 'success');
    };

    const filterTabs: { key: NonJksFilterMode; label: string; count: number }[] = [
        { key: 'ALL', label: 'Semua', count: modeCounts.ALL },
        { key: 'AVAILABLE', label: 'Siap Ajukan', count: modeCounts.AVAILABLE },
        { key: 'REMARKED', label: 'Ada Remark', count: modeCounts.REMARKED },
        { key: 'PENDING', label: 'Pending', count: modeCounts.PENDING },
    ];

    return (
        <div className="space-y-2.5 pb-16">
            {/* 1. Card Filter Distributor, Search & Filter Status Chips */}
            <div className="bg-white rounded-2xl border border-slate-200/80 p-3 shadow-sm space-y-2.5">
                {/* Pilih Distributor */}
                <div>
                    <select
                        value={selectedDistributor}
                        onChange={(e) => handleDistributorChange(e.target.value)}
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 outline-none truncate"
                    >
                        <option value="">-- Pilih Distributor --</option>
                        {distributors.map((d) => (
                            <option key={d.distributor_code} value={d.distributor_code}>
                                {d.distributor_name}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Search Input & Segmented Filter (Hanya tampil jika distributor sudah dipilih) */}
                {selectedDistributor && (
                    <>
                        <div className="relative">
                            <MagnifyingGlassIcon className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => {
                                    setSearchQuery(e.target.value);
                                    setDisplayLimit(PAGE_SIZE);
                                }}
                                placeholder="Cari nama toko, kode toko, alamat, atau remark..."
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

                        {/* 4 Kolom Filter Mode */}
                        <div className="grid grid-cols-4 gap-1 bg-slate-100/90 p-1 rounded-xl border border-slate-200/60">
                            {filterTabs.map((t) => {
                                const isActive = filterMode === t.key;
                                return (
                                    <button
                                        key={t.key}
                                        type="button"
                                        onClick={() => {
                                            setFilterMode(t.key);
                                            setDisplayLimit(PAGE_SIZE);
                                        }}
                                        className={`py-1.5 px-1 rounded-lg text-center transition-all flex flex-col items-center justify-center ${
                                            isActive
                                                ? 'bg-indigo-600 text-white shadow-sm'
                                                : 'text-slate-600 hover:bg-white/60'
                                        }`}
                                    >
                                        <span className="text-xs font-black leading-tight">
                                            {t.count.toLocaleString('id-ID')}
                                        </span>
                                        <span className="text-3xs sm:text-2xs font-semibold leading-tight mt-0.5 truncate max-w-full">
                                            {t.label}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>
                    </>
                )}
            </div>

            {/* 2. Sub-Header Counter & Pilih Semua */}
            {selectedDistributor && !isLoading && filteredItems.length > 0 && (
                <div className="px-1 flex items-center justify-between text-2xs sm:text-xs text-slate-500">
                    <span>
                        Menampilkan <strong className="text-slate-800 font-bold">{filteredItems.length}</strong> Outlet NOO
                    </span>
                    {selectableVisibleIds.length > 0 && (
                        <button
                            type="button"
                            onClick={toggleSelectAllVisible}
                            className="font-bold text-indigo-600 hover:underline flex items-center gap-1"
                        >
                            <CheckCircleIcon className="w-3.5 h-3.5" />
                            <span>
                                {isAllVisibleSelected
                                    ? 'Batal Pilih Halaman Ini'
                                    : `Pilih Semua (${selectableVisibleIds.length})`}
                            </span>
                        </button>
                    )}
                </div>
            )}

            {/* 3. Daftar Outlet Non-JKS */}
            {!selectedDistributor ? (
                <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-2">
                    <div className="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 mb-2.5">
                        <BuildingStorefrontIcon className="w-6 h-6" />
                    </div>
                    <h4 className="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
                        Pilih Distributor Terlebih Dahulu
                    </h4>
                    <p className="text-xs text-slate-400 mt-1 max-w-xs leading-relaxed">
                        Pilih distributor di atas untuk menampilkan daftar outlet aktif yang belum masuk ke rute kunjungan JKS.
                    </p>
                </div>
            ) : isLoading ? (
                <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-2">
                    <ArrowPathIcon className="w-6 h-6 text-indigo-600 animate-spin mb-2" />
                    <p className="text-xs font-bold text-slate-700">Memuat daftar Outlet NOO...</p>
                </div>
            ) : displayedItems.length > 0 ? (
                <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
                    {displayedItems.map((item, idx) => {
                        const isPending = Boolean(item.pending_approval_id);
                        const isSelected = selectedIds.includes(item.id);
                        const hasRemark = Boolean(item.remark && item.remark.trim().length > 0);
                        const hasPrc = Boolean(item.customer_eska);

                        return (
                            <div
                                key={item.id}
                                className={`px-3 py-2.5 flex items-start justify-between gap-2.5 transition-colors ${
                                    isSelected ? 'bg-indigo-50/50' : 'hover:bg-slate-50/80'
                                }`}
                            >
                                {/* Kolom 1: Checkbox + Nomor Urut */}
                                <div className="flex items-center gap-2 pt-0.5 shrink-0">
                                    <input
                                        type="checkbox"
                                        checked={isSelected}
                                        disabled={isPending}
                                        onChange={() => toggleSelectOne(item.id)}
                                        className="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-40 cursor-pointer"
                                    />
                                    <span className="text-2xs sm:text-xs font-bold text-slate-400 font-mono w-5 text-right">
                                        {idx + 1}.
                                    </span>
                                </div>

                                {/* Kolom 2: Informasi Toko, Omset, Alamat & Remark */}
                                <div
                                    className="flex-1 min-w-0 cursor-pointer"
                                    onClick={() => setDetailOutlet(item)}
                                >
                                    {/* Baris 1: Nama Toko + Badge Status/PRC */}
                                    <div className="flex items-center gap-1.5 flex-wrap">
                                        <h4
                                            className="text-xs sm:text-sm font-extrabold text-slate-900 truncate max-w-full"
                                            title={item.customer_name}
                                        >
                                            {item.customer_name}
                                        </h4>
                                        {isPending && (
                                            <span className="px-1.5 py-0.5 rounded-full text-3xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Pending JKS
                                            </span>
                                        )}
                                        {!hasPrc && (
                                            <span className="px-1.5 py-0.5 rounded text-3xs font-bold bg-rose-50 text-rose-600 border border-rose-200/70">
                                                No PRC
                                            </span>
                                        )}
                                    </div>

                                    {/* Baris 2: Kode Toko + Avg Omset + Alamat */}
                                    <div className="flex items-center gap-1.5 text-2xs sm:text-xs text-slate-500 mt-0.5 flex-wrap">
                                        <span className="font-mono font-semibold text-slate-400">
                                            #{item.customer_code}
                                        </span>
                                        <span className="text-slate-300">•</span>
                                        <span className="font-mono font-bold text-emerald-700">
                                            Avg: Rp {Math.round(item.avg_value_net).toLocaleString('id-ID')}
                                        </span>
                                    </div>

                                    <p className="text-2xs text-slate-400 truncate mt-0.5">
                                        {item.alamat}
                                    </p>

                                    {/* Baris 3 (Jika Ada Remark): Tampilkan Catatan */}
                                    {hasRemark && (
                                        <div className="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50/80 border border-amber-200/70 text-3xs sm:text-2xs text-amber-800 max-w-full">
                                            <ChatBubbleBottomCenterTextIcon className="w-3 h-3 text-amber-600 shrink-0" />
                                            <span className="italic truncate">{item.remark}</span>
                                        </div>
                                    )}
                                </div>

                                {/* Kolom 3: Tombol Aksi (+ JKS & Detail/Remark) */}
                                <div className="flex items-center gap-1 shrink-0 pt-0.5">
                                    {!isPending && (
                                        <button
                                            type="button"
                                            onClick={() => setAddingOutlets([item])}
                                            className="px-2 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 text-3xs sm:text-2xs font-bold active:scale-95 transition-all flex items-center gap-1"
                                            title="Tambah ke Jadwal JKS"
                                        >
                                            <PlusCircleIcon className="w-3.5 h-3.5 stroke-[2.2]" />
                                            <span className="hidden sm:inline">JKS</span>
                                        </button>
                                    )}

                                    <button
                                        type="button"
                                        onClick={() => setDetailOutlet(item)}
                                        className={`w-7 h-7 rounded-lg active:scale-95 transition-all flex items-center justify-center border ${
                                            hasRemark
                                                ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border-amber-200'
                                                : 'bg-slate-100 hover:bg-slate-200 text-slate-600 border-slate-200'
                                        }`}
                                        title="Detail Omset 6 Bulan & Remark"
                                    >
                                        {hasRemark ? (
                                            <ChatBubbleBottomCenterTextIcon className="w-3.5 h-3.5 stroke-[2.2]" />
                                        ) : (
                                            <InformationCircleIcon className="w-3.5 h-3.5 stroke-[2.2]" />
                                        )}
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>
            ) : (
                <div className="bg-white rounded-2xl border border-slate-100 p-8 text-center shadow-sm flex flex-col items-center justify-center mt-2">
                    <div className="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mb-2">
                        <BuildingStorefrontIcon className="w-6 h-6" />
                    </div>
                    <h4 className="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
                        Tidak Ada Outlet Ditemukan
                    </h4>
                    <p className="text-xs text-slate-400 mt-1 max-w-xs leading-relaxed">
                        Tidak ada data Outlet Non-JKS yang sesuai dengan pencarian atau filter Anda.
                    </p>
                </div>
            )}

            {/* 4. Tombol Load More (DOM Chunking) */}
            {filteredItems.length > displayLimit && (
                <div className="pt-2 pb-4 flex justify-center">
                    <button
                        type="button"
                        onClick={() => setDisplayLimit((prev) => prev + PAGE_SIZE)}
                        className="px-5 py-2 bg-white border border-slate-200 text-slate-700 hover:text-indigo-600 text-xs font-bold rounded-xl shadow-sm transition-all active:scale-95 flex items-center gap-1.5"
                    >
                        <ArrowPathIcon className="w-3.5 h-3.5 text-indigo-600" />
                        <span>Muat Lebih Banyak ({filteredItems.length - displayLimit} toko lagi)</span>
                    </button>
                </div>
            )}

            {/* 5. Floating Bulk Action Bar (Muncul saat >= 1 toko dicentang) */}
            {selectedIds.length > 0 && (
                <div className="fixed bottom-16 left-0 right-0 z-30 px-3.5 pointer-events-none">
                    <div className="max-w-2xl md:max-w-4xl mx-auto bg-slate-900 text-white rounded-2xl p-3 shadow-xl flex items-center justify-between gap-3 pointer-events-auto border border-slate-700">
                        <div className="flex items-center gap-2 min-w-0">
                            <span className="px-2 py-0.5 rounded-lg bg-indigo-500 text-white text-xs font-black">
                                {selectedIds.length}
                            </span>
                            <span className="text-xs font-bold truncate">
                                Outlet Dipilih
                            </span>
                            <button
                                type="button"
                                onClick={() => setSelectedIds([])}
                                className="text-2xs text-slate-300 hover:text-white underline ml-1"
                            >
                                Reset
                            </button>
                        </div>

                        <button
                            type="button"
                            onClick={handleBulkAddClick}
                            className="px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-extrabold flex items-center gap-1.5 shadow-sm active:scale-95 transition-all shrink-0"
                        >
                            <PlusCircleIcon className="w-4 h-4 stroke-[2.5]" />
                            <span>Tambah ke JKS</span>
                        </button>
                    </div>
                </div>
            )}

            {/* Modal 1: Tambah ke JKS (Single / Bulk) */}
            <AddNonJksSheet
                outlets={addingOutlets}
                salesmans={salesmans}
                distributorCode={selectedDistributor}
                bulan={bulan}
                onClose={() => setAddingOutlets([])}
                onSuccess={handleAddSuccess}
                onError={(msg) => onShowToast(msg, 'error')}
            />

            {/* Modal 2: Detail Omset 6 Bulan & Form Remark */}
            <NonJksDetailSheet
                item={detailOutlet}
                historyLabels={historyLabels}
                onClose={() => setDetailOutlet(null)}
                onAddJks={(outlet) => setAddingOutlets([outlet])}
                onRemarkSaved={handleRemarkSaved}
                onError={(msg) => onShowToast(msg, 'error')}
            />
        </div>
    );
}
