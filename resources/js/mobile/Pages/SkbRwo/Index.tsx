import React, { useState, useRef, useMemo, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    MagnifyingGlassIcon, XMarkIcon, ShieldCheckIcon,
    BuildingStorefrontIcon as BuildingStorefrontOutline, ClipboardDocumentListIcon,
    CalendarIcon, DocumentCheckIcon, Squares2X2Icon, ArrowLeftIcon, HomeIcon
} from '@heroicons/react/24/outline';
import { 
    ShieldExclamationIcon,
    HomeIcon as HomeSolid,
    Squares2X2Icon as Squares2X2Solid,
    BuildingStorefrontIcon as BuildingStorefrontSolid,
    ClipboardDocumentListIcon as ClipboardDocumentListSolid,
    CalendarIcon as CalendarSolid
} from '@heroicons/react/24/solid';

import MobileLayout from '../../Layouts/MobileLayout';
import SearchBar from '../../Components/UI/SearchBar';
import StoreCard, { SkbRwoItem } from './Components/StoreCard';
import SkbModal from './Components/SkbModal';
import DetailModal from './Components/DetailModal';

interface SkbRwoIndexProps {
    listPotensi?: SkbRwoItem[];
    listSkb?: SkbRwoItem[];
    listPlan?: SkbRwoItem[];
    sessionSupervisorCode?: string;
    sessionSupervisorName?: string;
}

export default function Index({ 
    listPotensi = [], 
    listSkb = [], 
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

    const [activeTab, setActiveTab] = useState<'summary'|'potensi'|'skb'|'plan'>('summary');
    const [search, setSearch] = useState('');
    const [filterSkbStatus, setFilterSkbStatus] = useState<'Semua'|'Sudah'|'Belum'>('Semua');
    const [displayLimit, setDisplayLimit] = useState(30);
    const [selectedDate, setSelectedDate] = useState(new Date());

    const formatYMD = (date: Date) => {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };

    const getDaysInMonth = (date: Date) => {
        const year = date.getFullYear();
        const month = date.getMonth();
        const days = [];
        const dateCount = new Date(year, month + 1, 0).getDate();
        for (let i = 1; i <= dateCount; i++) {
            days.push(new Date(year, month, i));
        }
        return days;
    };

    const daysInMonth = useMemo(() => getDaysInMonth(selectedDate), [selectedDate.getFullYear(), selectedDate.getMonth()]);

    const handlePrevMonth = () => setSelectedDate(new Date(selectedDate.getFullYear(), selectedDate.getMonth() - 1, 1));
    const handleNextMonth = () => setSelectedDate(new Date(selectedDate.getFullYear(), selectedDate.getMonth() + 1, 1));

    useEffect(() => {
        if (activeTab === 'plan') {
            setTimeout(() => {
                const el = document.getElementById('selected-date-btn');
                if (el) el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }, 50);
        }
    }, [activeTab, selectedDate]);

    // States for Modals
    const [detailModalData, setDetailModalData] = useState<SkbRwoItem | null>(null);
    const [skbModalData, setSkbModalData] = useState<SkbRwoItem | null>(null);

    const handleSearchSubmit = () => {
        setDisplayLimit(30);
    };
    const clearSearch = () => {
        setSearch('');
        setDisplayLimit(30);
    };
    const handleTabSwitch = (tab: 'summary'|'potensi'|'skb'|'plan') => {
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
        else if (activeTab === 'skb') result = listSkb;
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
    }, [listPotensi, listSkb, listPlan, activeTab, search, selectedDate, filterSkbStatus]);

    const openDetailModal = (item: SkbRwoItem) => setDetailModalData(item);
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
                    onClick={() => router.visit('/mobile/home')}
                    className="flex flex-col items-center justify-center gap-1 w-full transition-colors text-slate-400 hover:text-slate-600"
                >
                    <HomeIcon className="w-[22px] h-[22px]" />
                    <span className="text-[10px] tracking-wide font-medium">Home</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('summary')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'summary' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'summary' ? <Squares2X2Solid className="w-[22px] h-[22px]" /> : <Squares2X2Icon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'summary' ? 'font-bold' : 'font-medium'}`}>Summary</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('potensi')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'potensi' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'potensi' ? <BuildingStorefrontSolid className="w-[22px] h-[22px]" /> : <BuildingStorefrontOutline className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'potensi' ? 'font-bold' : 'font-medium'}`}>Potensi</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('skb')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'skb' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'skb' ? <ClipboardDocumentListSolid className="w-[22px] h-[22px]" /> : <ClipboardDocumentListIcon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'skb' ? 'font-bold' : 'font-medium'}`}>SKB</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('plan')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'plan' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'plan' ? <CalendarSolid className="w-[22px] h-[22px]" /> : <CalendarIcon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'plan' ? 'font-bold' : 'font-medium'}`}>Visit</span>
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
                                            className={`px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider shrink-0 transition-colors border ${filterSkbStatus === status ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'}`}
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
                    <div className="bg-white pt-2 pb-2 border-t border-slate-100 flex flex-col gap-3 px-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-base font-black text-slate-800">
                                {selectedDate.toLocaleString('id-ID', { month: 'long', year: 'numeric' })}
                            </h2>
                            <div className="flex gap-2">
                                <button onClick={handlePrevMonth} className="w-7 h-7 flex items-center justify-center rounded-full bg-slate-50 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-colors shadow-sm border border-slate-200/50">
                                    <span className="font-bold text-xs">&lt;</span>
                                </button>
                                <button onClick={handleNextMonth} className="w-7 h-7 flex items-center justify-center rounded-full bg-slate-50 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-colors shadow-sm border border-slate-200/50">
                                    <span className="font-bold text-xs">&gt;</span>
                                </button>
                            </div>
                        </div>
                        
                        <div className="flex overflow-x-auto gap-2.5 pb-2 no-scrollbar snap-x">
                            {daysInMonth.map(day => {
                                const isSelected = day.getDate() === selectedDate.getDate() && day.getMonth() === selectedDate.getMonth();
                                return (
                                    <button
                                        id={isSelected ? 'selected-date-btn' : undefined}
                                        key={day.toISOString()}
                                        onClick={() => setSelectedDate(day)}
                                        className={`flex flex-col items-center justify-center min-w-[3rem] p-2 rounded-xl transition-all snap-center border ${isSelected ? 'bg-indigo-600 border-indigo-600 shadow-md shadow-indigo-200/50' : 'bg-transparent border-transparent hover:bg-slate-50'}`}
                                    >
                                        <span className={`text-[9px] font-bold uppercase tracking-widest mb-0.5 ${isSelected ? 'text-indigo-100' : 'text-slate-400'}`}>
                                            {day.toLocaleString('id-ID', { weekday: 'short' })}
                                        </span>
                                        <span className={`text-base font-black ${isSelected ? 'text-white' : 'text-slate-700'}`}>
                                            {day.getDate()}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>

            <main className="flex-1 flex flex-col pt-1">
                {activeTab === 'summary' ? (
                    <div className="px-4 flex flex-col gap-4 animate-fade-in pb-6">
                        <div className="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center relative overflow-hidden">
                            <div className="absolute top-0 left-0 w-full h-1 bg-indigo-500"></div>
                            <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Toko (Potensi)</h3>
                            <p className="text-4xl font-black text-indigo-600 mb-2">{totalToko}</p>
                        </div>
                        
                        <div className="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center relative overflow-hidden">
                            <div className="absolute top-0 left-0 w-full h-1 bg-amber-500"></div>
                            <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Target</h3>
                            <p className="text-2xl font-black text-slate-800 mb-2">Rp {new Intl.NumberFormat('id-ID').format(totalTarget)}</p>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="bg-emerald-50 rounded-3xl p-5 shadow-sm border border-emerald-100 flex flex-col items-center justify-center text-center relative overflow-hidden">
                                <div className="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>
                                <h3 className="text-[10px] font-bold text-emerald-600/70 uppercase tracking-widest mb-1">SKB Approve</h3>
                                <p className="text-3xl font-black text-emerald-600">{skbApprove}</p>
                            </div>
                            <div className="bg-rose-50 rounded-3xl p-5 shadow-sm border border-rose-100 flex flex-col items-center justify-center text-center relative overflow-hidden">
                                <div className="absolute top-0 left-0 w-full h-1 bg-rose-500"></div>
                                <h3 className="text-[10px] font-bold text-rose-600/70 uppercase tracking-widest mb-1">SKB Reject</h3>
                                <p className="text-3xl font-black text-rose-600">{skbReject}</p>
                            </div>
                        </div>

                        {totalToko === 0 && (
                            <div className="bg-slate-50 border border-slate-200 rounded-2xl p-6 text-center shadow-sm">
                                <p className="text-sm font-bold text-slate-500">Belum ada data potensi RWO untuk kuartal ini.</p>
                            </div>
                        )}
                    </div>
                ) : (
                <>
                <div className="grid grid-cols-1 gap-4 px-4">
                    {displayedData.length > 0 ? displayedData.slice(0, displayLimit).map((item) => (
                        <StoreCard key={item.customer_code} item={item} onOpenDetail={openDetailModal} onOpenSkb={openSkbModal} />
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
                                        {activeTab === 'skb' && 'Belum ada data Surat Kesepakatan Bersama.'}
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
            <DetailModal data={detailModalData} onClose={closeDetailModal} showToast={showToast} />

            {/* Aksi SKB Modal */}
            <SkbModal data={skbModalData} onClose={closeSkbModal} showToast={showToast} />

        </MobileLayout>
    );
}
