import React, { useState, useRef } from 'react';
import { Head, router } from '@inertiajs/react';
import { ShieldExclamationIcon, ShieldCheckIcon } from '@heroicons/react/24/solid';
import MobileLayout from '../../Layouts/MobileLayout';
import SearchBar from '../../Components/UI/SearchBar';

// Imported Components
import ScrollCalendar from '../../Components/UI/ScrollCalendar';
import SummaryKPI from './Components/SummaryKPI';
import FilterChips from './Components/FilterChips';
import StoreCard from './Components/StoreCard';
import MapModal from './Components/MapModal';
import PullToRefresh from './Components/PullToRefresh';
import BottomNav from './Menus/BottomNav';

interface IndexProps {
    tokoList: any[];
    riwayatPerbaikan: any[];
    listPlan?: any[];
    user: any;
}

export default function Index({ tokoList = [], riwayatPerbaikan = [], listPlan = [], user, filters }: IndexProps & { filters?: any }) {

    // --- State Management ---
    const [toast, setToast] = useState<{message: string, type: string} | null>(null);
    const toastTimerRef = useRef<NodeJS.Timeout | null>(null);
    const [activeTab, setActiveTab] = useState('laporan');
    const [selectedDate, setSelectedDate] = useState(new Date());
    const [search, setSearch] = useState(filters?.search || '');
    const [selectedStatusFilter, setSelectedStatusFilter] = useState('');
    const [detailOutlet, setDetailOutlet] = useState<any>(null);
    const [displayLimit, setDisplayLimit] = useState(30);

    // KPI
    const totalDiperbaiki = riwayatPerbaikan.length;
    const totalApproved = riwayatPerbaikan.filter(o => o.status_perbaikan?.toLowerCase() === 'approved').length;
    const totalRejected = riwayatPerbaikan.filter(o => o.status_perbaikan?.toLowerCase() === 'rejected').length;
    const totalPending = riwayatPerbaikan.filter(o => o.status_perbaikan?.toLowerCase() === 'pending').length;

    const showToast = (message: string, type = 'success') => {
        if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
        setToast({ message, type });
        toastTimerRef.current = setTimeout(() => setToast(null), type === 'error' ? 5000 : 3000);
    };

    const handleSearchSubmit = () => {
        router.get(window.location.pathname, { search: search }, {
            preserveState: true,
            replace: true,
            only: ['tokoList', 'riwayatPerbaikan', 'listPlan', 'filters'],
            onSuccess: () => setDisplayLimit(30)
        });
    };

    const clearSearch = () => {
        setSearch('');
        router.get(window.location.pathname, { search: '' }, {
            preserveState: true,
            replace: true,
            only: ['tokoList', 'riwayatPerbaikan', 'listPlan', 'filters'],
            onSuccess: () => setDisplayLimit(30)
        });
    };

    const handleTabSwitch = (tab: string) => {
        setActiveTab(tab);
        setSelectedStatusFilter('');
        setDisplayLimit(30);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const openDetail = (outlet: any) => {
        setDetailOutlet(outlet);
        window.history.pushState(null, '', '#detail');
    };

    const refreshData = () => {
        return new Promise<void>((resolve) => {
            router.reload({
                only: ['tokoList', 'riwayatPerbaikan', 'listPlan'],
                onFinish: () => resolve()
            });
        });
    };

    // Filter plan list by selected date
    const displayedPlan = listPlan.filter(plan => {
        if (!plan.visit_date) return false;
        const planDate = new Date(plan.visit_date);
        return planDate.getDate() === selectedDate.getDate() &&
               planDate.getMonth() === selectedDate.getMonth() &&
               planDate.getFullYear() === selectedDate.getFullYear();
    });

    const getDisplayedOutlets = () => {
        let result = activeTab === 'visit' ? displayedPlan : (activeTab === 'toko' ? tokoList : riwayatPerbaikan);

        if (activeTab === 'laporan' && selectedStatusFilter) {
            result = result.filter(o => o.status_perbaikan?.toLowerCase() === selectedStatusFilter);
        }

        return result;
    };

    const displayedOutlets = getDisplayedOutlets();

    return (
        <MobileLayout user={user} title="Perbaikan Geotag" backUrl="/mobile/home" bottomNavigation={<BottomNav activeTab={activeTab} handleTabSwitch={handleTabSwitch} />}>
            <Head title="Perbaikan Geotag" />
            
            <PullToRefresh onRefresh={refreshData}>
            <div className="flex flex-col relative pb-20 w-full animate-fade-in">
                {/* Toast System */}
                {toast && (
                    <div
                        onClick={() => { if (toastTimerRef.current) clearTimeout(toastTimerRef.current); setToast(null); }}
                        className={`fixed top-safe left-4 right-4 z-[100] px-4 py-3 rounded-2xl shadow-lg flex items-center gap-3 text-xs font-bold text-white transition-all cursor-pointer ${toast.type === 'success' ? 'bg-emerald-500 shadow-emerald-500/20' : 'bg-rose-500 shadow-rose-500/20'} mt-2 animate-slide-down`}
                    >
                        {toast.type === 'success' ? <ShieldCheckIcon className="w-6 h-6 shrink-0" /> : <ShieldExclamationIcon className="w-6 h-6 shrink-0" />}
                        <span className="flex-1">{toast.message}</span>
                    </div>
                )}

                {/* Tabs & Search Area */}
                <div className="sticky top-16 md:top-20 z-30 bg-gray-50/80 backdrop-blur-lg pt-2 pb-1 shrink-0">
                    <div className="px-4 py-3 pb-2 flex items-center gap-2">
                        <SearchBar 
                            value={search} 
                            onChange={(val) => { setSearch(val); setDisplayLimit(30); }} 
                            placeholder="Cari Customer / Kode..." 
                            onSubmit={handleSearchSubmit} 
                            onClear={clearSearch}
                        />
                    </div>

                    {activeTab === 'visit' && (
                        <ScrollCalendar 
                            selectedDate={selectedDate} 
                            setSelectedDate={setSelectedDate} 
                            markedDates={listPlan.filter(p => p.visit_date).map(p => {
                                const d = new Date(p.visit_date);
                                return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                            })} 
                        />
                    )}
                </div>

                {/* Main List */}
                <main className="flex-1 px-4 pt-4">
                    {activeTab === 'laporan' && (
                        <div className="mb-5 flex flex-col gap-3">
                            <SummaryKPI 
                                totalDiperbaiki={totalDiperbaiki} 
                                totalApproved={totalApproved} 
                                totalRejected={totalRejected} 
                                totalPending={totalPending} 
                            />
                            <FilterChips 
                                selectedStatusFilter={selectedStatusFilter} 
                                setSelectedStatusFilter={setSelectedStatusFilter} 
                            />
                        </div>
                    )}

                    <div className="flex flex-col gap-3">
                        {displayedOutlets.length > 0 ? displayedOutlets.slice(0, displayLimit).map((outlet, idx) => (
                            <StoreCard 
                                key={`${outlet.distributor_code}_${outlet.customer_code}_${idx}`} 
                                outlet={outlet} 
                                activeTab={activeTab} 
                                onPerbaikiClick={openDetail} 
                            />
                        )) : (
                            <div className="flex flex-col items-center justify-center py-10 px-4 text-center bg-white rounded-2xl border border-gray-100 shadow-sm mt-2">
                                <div className="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 className="text-sm font-bold text-gray-800 mb-1">Tidak ada data</h3>
                                <p className="text-xs text-gray-500 font-medium">Data toko tidak ditemukan atau belum ada data untuk tab ini.</p>
                            </div>
                        )}
                    </div>

                    {displayedOutlets.length > displayLimit && (
                        <div className="mt-6 mb-4 flex justify-center">
                            <button 
                                onClick={() => setDisplayLimit(prev => prev + 30)}
                                className="px-5 py-2.5 bg-gray-900 text-white rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-gray-800 transition-colors shadow-lg shadow-gray-900/20 active:scale-95 flex items-center gap-2"
                            >
                                Muat Lebih Banyak
                                <span className="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md text-xs font-bold">{displayedOutlets.length - displayLimit} tersisa</span>
                            </button>
                        </div>
                    )}
                </main>
            </div>
            </PullToRefresh>

            {detailOutlet && (
                <MapModal 
                    detailOutlet={detailOutlet}
                    setDetailOutlet={setDetailOutlet}
                    showToast={showToast}
                />
            )}
        </MobileLayout>
    );
}
