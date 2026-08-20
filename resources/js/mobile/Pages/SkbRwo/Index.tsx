import React, { useState, useRef, useMemo, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    ShieldCheckIcon,
    DocumentCheckIcon,
    AdjustmentsHorizontalIcon,
    MapPinIcon,
    ArrowPathIcon
} from '@heroicons/react/24/outline';
import {
    ShieldExclamationIcon,
} from '@heroicons/react/24/solid';

import MobileLayout from '../../Layouts/MobileLayout';
import SearchBar from '../../Components/UI/SearchBar';
import ScrollCalendar from '../../Components/UI/ScrollCalendar';
import StoreCard, { SkbRwoItem, getProratedTarget } from './Components/StoreCard';
import SkbModal from './Components/SkbModal';
import SummaryDashboard from './Components/SummaryDashboard';
import DetailModal from './Components/DetailModal';
import KuartalFilterSheet from './Components/KuartalFilterSheet';
import SkbBottomNav from './Components/SkbBottomNav';
import RadarMap from './Components/RadarMap';
import { useSkbRwoFilter } from './hooks/useSkbRwoFilter';
import { useNearby } from './hooks/useNearby';
import { SkbStatusType, ProgressStatusType, PAGINATION_LIMIT } from './constants';

interface SkbRwoIndexProps {
    listPotensi?: SkbRwoItem[];
    listMonitoring?: SkbRwoItem[];
    listPlan?: SkbRwoItem[];
    sessionSupervisorCode?: string;
    sessionSupervisorName?: string;
}

export default function Index({
    listPotensi = [],
    listMonitoring = [],
    listPlan = [],
    sessionSupervisorCode = 'USER',
    sessionSupervisorName = 'User SSO'
}: SkbRwoIndexProps) {
    const [toast, setToast] = useState<{message: string, type: 'success'|'error'} | null>(null);
    const toastTimerRef = useRef<NodeJS.Timeout | null>(null);

    const showToast = (message: string, type: 'success' | 'error' = 'success') => {
        if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
        setToast({ message, type });
        toastTimerRef.current = setTimeout(() => setToast(null), type === 'error' ? 5000 : 3000);
    };

    useEffect(() => {
        return () => {
            if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
        };
    }, []);

    // Nearby GPS hook
    const { isNearbyActive, isLoadingGps, userLocation, gpsSupported, toggleNearby, getDistance } = useNearby();

    const {
        activeTab, handleTabSwitch,
        search, handleSearchChange, handleSearchSubmit,
        filterSkbStatus, setFilterSkbStatus,
        filterKuartal, setFilterKuartal,
        filterStatus, setFilterStatus,
        filterDistributor, setFilterDistributor,
        filterReward, setFilterReward,
        displayLimit, setDisplayLimit, handleLoadMore,
        selectedDate, setSelectedDate,
        displayedData, filteredSummaryData
    } = useSkbRwoFilter({ listPotensi, listMonitoring, listPlan, nearbyActive: isNearbyActive, userLocation });

    const [isKuartalSheetOpen, setIsKuartalSheetOpen] = useState(false);

    // States for Modals
    const [detailModalData, setDetailModalData] = useState<SkbRwoItem | null>(null);
    const [isDetailModalActual, setIsDetailModalActual] = useState(false);
    const [skbModalData, setSkbModalData] = useState<SkbRwoItem | null>(null);

    const openDetailModal = (item: SkbRwoItem) => {
        setDetailModalData(item);
        setIsDetailModalActual(activeTab === 'monitoring');
    };
    const openActualModal = (item: SkbRwoItem) => {
        setDetailModalData(item);
        setIsDetailModalActual(true);
    };
    const closeDetailModal = () => setDetailModalData(null);

    const openSkbModal = (item: SkbRwoItem) => setSkbModalData(item);
    const closeSkbModal = () => setSkbModalData(null);

    // Summary calculations
    const totalToko = listPotensi.length;
    const totalTarget = listPotensi.reduce((acc, curr) => acc + (Number(curr.total_target) || 0), 0);
    const skbApprove = listPotensi.filter(item => item.status_skb === 'Sudah' && (item.is_approved === 1 || item.is_approved === true)).length;
    const skbReject = listPotensi.filter(item => item.status_skb === 'Sudah' && (item.is_approved === 0 || item.is_approved === false)).length;

    const uniqueDistributors = useMemo(() => {
        const dists = new Map<string, string>();
        listMonitoring.forEach(item => {
            if (item.distributor_code && item.distributor_name) {
                dists.set(item.distributor_code, item.distributor_name);
            }
        });
        return Array.from(dists.entries()).map(([code, name]) => ({ code, name }));
    }, [listMonitoring]);

    const skbBottomMenu = <SkbBottomNav activeTab={activeTab} onTabSwitch={handleTabSwitch} />;

    // Nearby button — tampil di tab monitoring & potensi
    const nearbyButton = gpsSupported ? (
        <button
            type="button"
            onClick={() => toggleNearby(showToast)}
            title={isNearbyActive ? 'Matikan Nearby' : 'Aktifkan Nearby (±3 km)'}
            className={`w-7 h-7 flex items-center justify-center rounded-lg transition-colors ml-1 ${
                isNearbyActive
                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                    : 'text-slate-400 hover:text-indigo-600 hover:bg-slate-100'
            }`}
        >
            {isLoadingGps
                ? <ArrowPathIcon className="w-4 h-4 animate-spin" />
                : <MapPinIcon className="w-5 h-5" />
            }
        </button>
    ) : null;

    return (
        <MobileLayout user={{ name: sessionSupervisorName || sessionSupervisorCode }} title="Reward Outlet" backUrl="/mobile/home" bottomNavigation={skbBottomMenu}>
            <Head title="Reward Outlet" />
            {toast && (
                <div
                    onClick={() => { if (toastTimerRef.current) clearTimeout(toastTimerRef.current); setToast(null); }}
                    className={`fixed top-20 md:top-24 left-1/2 -translate-x-1/2 z-[100] px-4 py-2 rounded-xl shadow-lg flex items-center gap-2 text-sm font-bold text-white transition-all cursor-pointer ${toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}`}
                >
                    {toast.type === 'success' ? <ShieldCheckIcon className="w-5 h-5" /> : <ShieldExclamationIcon className="w-5 h-5" />}
                    {toast.message}
                </div>
            )}

            {/* Sticky Tabs/Filters */}
            <div className="sticky top-16 md:top-20 z-30 bg-slate-50/80 backdrop-blur-lg pt-3 pb-1">
                {activeTab !== 'summary' && activeTab !== 'radar' && (
                    <div className="px-4 pb-3 flex flex-col gap-2">
                        <SearchBar
                            value={search}
                            onChange={handleSearchChange}
                            placeholder="Cari Toko / Kode..."
                            onSubmit={handleSearchSubmit}
                            rightAction={(activeTab === 'monitoring' || activeTab === 'potensi') ? (
                                <div className="flex items-center gap-1">
                                    {nearbyButton}
                                    {activeTab === 'monitoring' && (
                                        <button
                                            type="button"
                                            onClick={() => setIsKuartalSheetOpen(true)}
                                            className="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-50 transition-colors ml-1"
                                        >
                                            <AdjustmentsHorizontalIcon className="w-5 h-5" />
                                        </button>
                                    )}
                                </div>
                            ) : undefined}
                        />
                        {activeTab === 'potensi' && (
                            <div className="flex items-center justify-between pb-1 pt-1">
                                <span className="text-[11px] font-black text-slate-700 tracking-wider ml-3">{filterSkbStatus} ({displayedData.length})</span>
                                <div className="flex items-center gap-1.5 overflow-x-auto no-scrollbar mr-3">
                                    {['Semua', 'Sudah', 'Belum'].map(status => (
                                        <button
                                            key={status}
                                            type="button"
                                            onClick={() => { setFilterSkbStatus(status as SkbStatusType); setDisplayLimit(PAGINATION_LIMIT); }}
                                            className={`px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider shrink-0 transition-colors border ${filterSkbStatus === status ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'}`}
                                        >
                                            {status}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}
                        {activeTab === 'monitoring' && (
                            <div className="flex items-center justify-between pb-1 pt-1">
                                <span className="text-[11px] font-black text-slate-700 tracking-wider ml-3">
                                    {isNearbyActive ? `📍 Nearby (${displayedData.length})` : `${filterStatus === 'Semua' ? 'Semua' : filterStatus} (${displayedData.length})`}
                                </span>
                                <div className="flex items-center gap-1.5 overflow-x-auto no-scrollbar mr-3">
                                    {['Semua', 'Hijau', 'Kuning', 'Merah'].map(status => (
                                        <button
                                            key={status}
                                            type="button"
                                            onClick={() => { setFilterStatus(status as ProgressStatusType); setDisplayLimit(PAGINATION_LIMIT); }}
                                            className={`px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider shrink-0 transition-colors border ${filterStatus === status ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'}`}
                                        >
                                            {status}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {activeTab === 'plan' && (
                    <ScrollCalendar
                        selectedDate={selectedDate}
                        setSelectedDate={setSelectedDate}
                        markedDates={listPlan.map(p => p.tanggal).filter(Boolean) as string[]}
                    />
                )}
            </div>

            <main className="flex-1 flex flex-col pt-1">
                {activeTab === 'summary' ? (
                    <SummaryDashboard
                        data={filteredSummaryData}
                        filterKuartal={filterKuartal}
                        onOpenFilter={() => setIsKuartalSheetOpen(true)}
                    />
                ) : activeTab === 'radar' ? (
                    <RadarMap data={listMonitoring} showToast={showToast} />
                ) : (
                <>
                <div className="grid grid-cols-1 gap-2.5 px-4">
                    {displayedData.length > 0 ? displayedData.slice(0, displayLimit).map((item) => (
                        <StoreCard
                            key={item.customer_code}
                            item={item}
                            showProgress={activeTab === 'monitoring' || activeTab === 'plan'}
                            showSkbAction={activeTab !== 'monitoring'}
                            showActualAction={activeTab === 'plan'}
                            showDirection={activeTab === 'monitoring'}
                            onOpenDetail={openDetailModal}
                            onOpenActual={openActualModal}
                            onOpenSkb={openSkbModal}
                            distance={getDistance(item)}
                        />
                    )) : (
                        <div className="bg-white border border-slate-100 rounded-3xl py-12 px-6 text-center shadow-sm flex-1 flex flex-col items-center justify-center col-span-full">
                            {activeTab === 'plan' ? (
                                <>
                                    <div className="mb-4 text-slate-300">
                                        <DocumentCheckIcon className="w-16 h-16 mx-auto stroke-1" />
                                    </div>
                                    <h4 className="text-sm font-black text-slate-700">Tidak Ada Jadwal</h4>
                                    <p className="text-xs text-slate-400 mt-2 font-medium">Belum ada jadwal kunjungan untuk tanggal ini.</p>
                                </>
                            ) : isNearbyActive ? (
                                <>
                                    <div className="w-16 h-16 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-400 mb-2">
                                        <MapPinIcon className="w-8 h-8" />
                                    </div>
                                    <h4 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-700">Tidak Ada Toko di Sekitar</h4>
                                    <p className="text-[10px] text-slate-400 mt-2 font-medium">Tidak ada toko dalam radius 3 km dari lokasi Anda.</p>
                                </>
                            ) : (
                                <>
                                    <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                        <ShieldExclamationIcon className="w-8 h-8" />
                                    </div>
                                    <h4 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-700">Tidak Ada Data</h4>
                                    <p className="text-[10px] text-slate-400 mt-2 font-medium">
                                        {activeTab === 'potensi' && 'Belum ada data potensi RWO.'}
                                    </p>
                                </>
                            )}
                        </div>
                    )}
                </div>

                {displayedData.length > displayLimit && (
                    <div className="mt-6 mb-2 text-center">
                        <button
                            onClick={handleLoadMore}
                            className="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 text-[11px] font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 mx-auto"
                        >
                            Muat Lebih Banyak
                        </button>
                    </div>
                )}
                </>
                )}
            </main>

            {/* Detail Modal */}
            <DetailModal data={detailModalData} isMonitoring={isDetailModalActual} onClose={closeDetailModal} showToast={showToast} />

            {/* Aksi SKB Modal */}
            <SkbModal data={skbModalData} onClose={closeSkbModal} showToast={showToast} />

            <KuartalFilterSheet
                isOpen={isKuartalSheetOpen}
                onClose={() => setIsKuartalSheetOpen(false)}
                filterKuartal={filterKuartal}
                setFilterKuartal={setFilterKuartal}
                filterDistributor={filterDistributor}
                setFilterDistributor={setFilterDistributor}
                filterReward={filterReward}
                setFilterReward={setFilterReward}
                distributors={uniqueDistributors}
            />
        </MobileLayout>
    );
}
