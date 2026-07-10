import React, { useState, useRef, useMemo, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    MagnifyingGlassIcon, XMarkIcon, ShieldCheckIcon,
    BuildingStorefrontIcon as BuildingStorefrontOutline, ClipboardDocumentListIcon,
    CalendarIcon, DocumentCheckIcon, Squares2X2Icon, ArrowLeftIcon, HomeIcon, ChartBarIcon, AdjustmentsHorizontalIcon, ChartPieIcon
} from '@heroicons/react/24/outline';
import { 
    ShieldExclamationIcon,
    HomeIcon as HomeSolid,
    Squares2X2Icon as Squares2X2Solid,
    BuildingStorefrontIcon as BuildingStorefrontSolid,
    ClipboardDocumentListIcon as ClipboardDocumentListSolid,
    CalendarIcon as CalendarSolid,
    ChartBarIcon as ChartBarSolid,
    ChartPieIcon as ChartPieSolid
} from '@heroicons/react/24/solid';

import MobileLayout from '../../Layouts/MobileLayout';
import SearchBar from '../../Components/UI/SearchBar';
import ScrollCalendar from '../../Components/UI/ScrollCalendar';
import StoreCard, { SkbRwoItem, getProratedTarget } from './Components/StoreCard';
import SkbModal from './Components/SkbModal';
import SummaryDashboard from './Components/SummaryDashboard';
import DetailModal from './Components/DetailModal';
import KuartalFilterSheet from './Components/KuartalFilterSheet';

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

    const [activeTab, setActiveTab] = useState<'summary'|'potensi'|'plan'|'monitoring'>(() => {
        return (sessionStorage.getItem('skbRwoActiveTab') as 'summary'|'potensi'|'plan'|'monitoring') || 'summary';
    });
    
    useEffect(() => {
        sessionStorage.setItem('skbRwoActiveTab', activeTab);
    }, [activeTab]);

    const [search, setSearch] = useState('');
    const [filterSkbStatus, setFilterSkbStatus] = useState<'Semua'|'Sudah'|'Belum'>('Semua');
    const [filterKuartal, setFilterKuartal] = useState<string>(Math.ceil((new Date().getMonth() + 1) / 3).toString());
    const [filterStatus, setFilterStatus] = useState<string>('Semua');
    const [isKuartalSheetOpen, setIsKuartalSheetOpen] = useState(false);
    const [displayLimit, setDisplayLimit] = useState(30);
    const [selectedDate, setSelectedDate] = useState(new Date());

    const formatYMD = (date: Date) => {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };


    // States for Modals
    const [detailModalData, setDetailModalData] = useState<SkbRwoItem | null>(null);
    const [isDetailModalActual, setIsDetailModalActual] = useState(false);
    const [skbModalData, setSkbModalData] = useState<SkbRwoItem | null>(null);

    const handleSearchSubmit = () => {
        setDisplayLimit(30);
    };
    const clearSearch = () => {
        setSearch('');
        setDisplayLimit(30);
    };
    const handleTabSwitch = (tab: 'summary'|'potensi'|'plan'|'monitoring') => {
        setActiveTab(tab);
        setDisplayLimit(30);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const displayedData = useMemo(() => {
        let result: SkbRwoItem[] = [];
        if (activeTab === 'potensi') {
            result = listPotensi;
            if (filterSkbStatus !== 'Semua') {
                result = result.filter(item => item.status_skb === filterSkbStatus);
            }
        }
        else if (activeTab === 'monitoring') {
            result = listMonitoring;
            if (filterKuartal !== 'Semua') {
                result = result.filter(item => String(item.kuartal) === filterKuartal);
            }
            if (filterStatus !== 'Semua') {
                result = result.filter(item => {
                    const target = item.total_target || 0;
                    const achievement = item.total_achievement || 0;
                    const proratedTgt = getProratedTarget(target, item.kuartal);
                    const percent = proratedTgt > 0 ? (achievement / proratedTgt) * 100 : 0;
                    if (filterStatus === 'Hijau') return percent >= 100;
                    if (filterStatus === 'Kuning') return percent >= 80 && percent < 100;
                    if (filterStatus === 'Merah') return percent < 80;
                    return true;
                });
            }
        }
        else if (activeTab === 'plan') {
            const selectedYMD = formatYMD(selectedDate);
            result = listPlan.filter(item => item.tanggal === selectedYMD);
        }

        if (search) {
            const q = search.toLowerCase();
            result = result.filter(o => 
                String(o.customer_name || '').toLowerCase().includes(q) || 
                String(o.customer_code || '').toLowerCase().includes(q)
            );
        }
        return result;
    }, [listPotensi, listMonitoring, listPlan, activeTab, search, selectedDate, filterSkbStatus, filterKuartal, filterStatus]);

    const filteredSummaryData = useMemo(() => {
        let result = listMonitoring;
        if (filterKuartal !== 'Semua') {
            result = result.filter(item => String(item.kuartal) === filterKuartal);
        }
        return result;
    }, [listMonitoring, filterKuartal]);

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

    const skbBottomMenu = (
        <div 
            className="fixed bottom-0 left-0 right-0 z-40 bg-white/85 backdrop-blur-2xl border-t border-slate-200/80"
            style={{ paddingBottom: 'env(safe-area-inset-bottom, 0px)' }}
        >
            <div className="flex items-center justify-around px-1 pt-2 pb-2">
                <button
                    onClick={() => handleTabSwitch('summary')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'summary' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'summary' ? <ChartPieSolid className="w-[22px] h-[22px]" /> : <ChartPieIcon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'summary' ? 'font-bold' : 'font-medium'}`}>Dashboard</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('plan')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'plan' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'plan' ? <CalendarSolid className="w-[22px] h-[22px]" /> : <CalendarIcon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'plan' ? 'font-bold' : 'font-medium'}`}>Visit</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('monitoring')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'monitoring' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'monitoring' ? <ChartBarSolid className="w-[22px] h-[22px]" /> : <ChartBarIcon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'monitoring' ? 'font-bold' : 'font-medium'}`}>Monitoring</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('potensi')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'potensi' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'potensi' ? <BuildingStorefrontSolid className="w-[22px] h-[22px]" /> : <BuildingStorefrontOutline className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'potensi' ? 'font-bold' : 'font-medium'}`}>SKB</span>
                </button>
            </div>
        </div>
    );

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

            {/* Sticky Tabs/Filters (positioned below MobileLayout Navbar) */}
            <div className="sticky top-16 md:top-20 z-30 bg-slate-50/80 backdrop-blur-lg pt-3 pb-1">


                {activeTab !== 'summary' && (
                    <div className="px-4 pb-3 flex flex-col gap-2">
                        <SearchBar 
                            value={search} 
                            onChange={(val) => { setSearch(val); setDisplayLimit(30); }} 
                            placeholder="Cari Toko / Kode..." 
                            onSubmit={handleSearchSubmit}
                            rightAction={activeTab === 'monitoring' ? (
                                <button
                                    type="button"
                                    onClick={() => setIsKuartalSheetOpen(true)}
                                    className="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-50 transition-colors ml-1"
                                >
                                    <AdjustmentsHorizontalIcon className="w-5 h-5" />
                                </button>
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
                                            onClick={() => { setFilterSkbStatus(status as 'Semua'|'Sudah'|'Belum'); setDisplayLimit(30); }}
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
                                    {filterStatus === 'Semua' ? 'Semua' : filterStatus} ({displayedData.length})
                                </span>
                                <div className="flex items-center gap-1.5 overflow-x-auto no-scrollbar mr-3">
                                    {['Semua', 'Hijau', 'Kuning', 'Merah'].map(status => (
                                        <button
                                            key={status}
                                            type="button"
                                            onClick={() => { setFilterStatus(status); setDisplayLimit(30); }}
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
                            onOpenDetail={openDetailModal} 
                            onOpenActual={openActualModal}
                            onOpenSkb={openSkbModal} 
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
                            onClick={() => setDisplayLimit(prev => prev + 30)}
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
                selectedKuartal={filterKuartal}
                onSelect={(val) => {
                    setFilterKuartal(val);
                    setDisplayLimit(30);
                }}
            />
        </MobileLayout>
    );
}
