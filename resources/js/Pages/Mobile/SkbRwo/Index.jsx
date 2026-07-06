import React, { useState, useRef, useMemo, useEffect } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import {
    MagnifyingGlassIcon, XMarkIcon, MapPinIcon, ShieldCheckIcon,
    BuildingStorefrontIcon as BuildingStorefrontOutline, ClipboardDocumentListIcon,
    CalendarIcon, DocumentCheckIcon, ChartBarIcon, ArrowLeftIcon
} from '@heroicons/react/24/outline';
import { ShieldExclamationIcon } from '@heroicons/react/24/solid';

import StoreCard from './Components/StoreCard';
import SkbModal from './Components/SkbModal';
import DetailModal from './Components/DetailModal';

export default function Index({ listPotensi = [], listSkb = [], listPlan = [], sessionSupervisorCode, sessionSupervisorName }) {
    const [showLogoutModal, setShowLogoutModal] = useState(false);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const [loginSupervisorCode, setLoginSupervisorCode] = useState('');
    const [isLoginLoading, setIsLoginLoading] = useState(false);
    const [toast, setToast] = useState(null);
    const toastTimerRef = useRef(null);

    const showToast = (message, type = 'success') => {
        if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
        setToast({ message, type });
        toastTimerRef.current = setTimeout(() => setToast(null), type === 'error' ? 5000 : 3000);
    };

    useEffect(() => {
        return () => {
            if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
        };
    }, []);

    const handleLogin = (e) => {
        e.preventDefault();
        if (!loginSupervisorCode || isLoginLoading) return;
        setIsLoginLoading(true);
        router.post('/mobile/skb-rwo/login', { supervisor_code: loginSupervisorCode }, {
            onError: (errors) => {
                setIsLoginLoading(false);
                if (errors.supervisor_code) showToast(errors.supervisor_code, 'error');
            },
            onSuccess: () => setIsLoginLoading(false)
        });
    };

    const handleLogout = () => setShowLogoutModal(true);
    const confirmLogout = () => {
        if (isLoggingOut) return;
        setIsLoggingOut(true);
        router.post('/mobile/skb-rwo/logout', {}, {
            onSuccess: () => { setIsLoggingOut(false); setShowLogoutModal(false); },
            onError: () => setIsLoggingOut(false),
        });
    };

    const [activeTab, setActiveTab] = useState('summary'); // 'summary', 'potensi', 'skb', 'plan'
    const [search, setSearch] = useState('');
    const [displayLimit, setDisplayLimit] = useState(30);
    const [selectedDate, setSelectedDate] = useState(new Date());

    const formatYMD = (date) => {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };

    const getDaysInMonth = (date) => {
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
    }, [activeTab, selectedDate.getMonth(), selectedDate.getFullYear()]);

    // States for Modals
    const [detailModalData, setDetailModalData] = useState(null);
    const [skbModalData, setSkbModalData] = useState(null);

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        if (document.activeElement) document.activeElement.blur();
    };
    const clearSearch = () => setSearch('');
    const handleTabSwitch = (tab) => {
        setActiveTab(tab);
        setDisplayLimit(30);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const displayedData = useMemo(() => {
        let result = [];
        if (activeTab === 'potensi') result = listPotensi;
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
    }, [listPotensi, listSkb, listPlan, activeTab, search, selectedDate]);

    const openDetailModal = (item) => setDetailModalData(item);
    const closeDetailModal = () => setDetailModalData(null);

    const openSkbModal = (item) => setSkbModalData(item);
    const closeSkbModal = () => setSkbModalData(null);

    // Summary calculations
    const totalToko = listPotensi.length;
    const totalTarget = listPotensi.reduce((acc, curr) => acc + (Number(curr.total_target) || 0), 0);
    const skbApprove = listPotensi.filter(item => item.status_skb === 'Sudah' && item.is_approved == 1).length;
    const skbReject = listPotensi.filter(item => item.status_skb === 'Sudah' && item.is_approved == 0).length;

    if (!sessionSupervisorCode) {
        return (
            <div className="w-full min-h-screen bg-gradient-to-br from-indigo-50 via-slate-50 to-indigo-100/50 flex items-center justify-center p-6">
                <Head title="Login Supervisor - Reward Outlet" />
                {toast && (
                    <div onClick={() => setToast(null)} className={`fixed top-4 left-1/2 -translate-x-1/2 z-[100] px-4 py-2 rounded-xl shadow-lg flex items-center gap-2 text-sm font-bold text-white transition-all cursor-pointer ${toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}`}>
                        {toast.type === 'success' ? <ShieldCheckIcon className="w-5 h-5" /> : <ShieldExclamationIcon className="w-5 h-5" />}
                        {toast.message}
                    </div>
                )}
                <div className="w-full max-w-sm bg-white/90 backdrop-blur-lg border border-slate-200/50 rounded-3xl shadow-xl p-6 flex flex-col items-center animate-fade-in relative">
                    <div className="w-14 h-14 rounded-2xl bg-indigo-600/10 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-600/10 mb-6 animate-bounce-slow">
                        <BuildingStorefrontOutline className="w-8 h-8" />
                    </div>
                    <h2 className="text-sm md:text-base font-black uppercase tracking-wider text-slate-900 leading-tight text-center mb-6">Reward Outlet</h2>
                    <form onSubmit={handleLogin} className="w-full flex flex-col gap-4 relative animate-fade-in">
                        <div className="relative">
                            <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Kode Supervisor</label>
                            <input
                                type="text"
                                value={loginSupervisorCode}
                                onChange={(e) => setLoginSupervisorCode(e.target.value.toUpperCase())}
                                placeholder="Ketik Kode Supervisor..."
                                disabled={isLoginLoading}
                                className="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-800 bg-slate-50 uppercase disabled:opacity-60 font-bold"
                            />
                        </div>
                        <button
                            type="submit"
                            disabled={!loginSupervisorCode || isLoginLoading}
                            className={`w-full py-3 rounded-xl text-white font-bold text-sm uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2 ${!loginSupervisorCode || isLoginLoading ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/30 active:scale-[0.98]'}`}
                        >
                            {isLoginLoading ? (
                                <><div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Memproses...</>
                            ) : 'Masuk'}
                        </button>
                    </form>
                    <div className="mt-8 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">
                        PT INAFOOD © {new Date().getFullYear()}
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="w-full min-h-screen bg-slate-50 text-slate-800 flex flex-col relative pb-24">
            <Head title="Reward Outlet" />
            {toast && (
                <div
                    onClick={() => { if (toastTimerRef.current) clearTimeout(toastTimerRef.current); setToast(null); }}
                    className={`fixed top-4 left-1/2 -translate-x-1/2 z-[100] px-4 py-2 rounded-xl shadow-lg flex items-center gap-2 text-sm font-bold text-white transition-all cursor-pointer ${toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}`}
                >
                    {toast.type === 'success' ? <ShieldCheckIcon className="w-5 h-5" /> : <ShieldExclamationIcon className="w-5 h-5" />}
                    {toast.message}
                </div>
            )}

            {/* Header */}
            <div className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm shrink-0">
                <header className="px-4 py-3 flex items-center justify-between" style={{ paddingTop: 'calc(0.75rem + env(safe-area-inset-top, 0px))' }}>
                    <div className="flex items-center gap-2.5">
                        <Link href="/mobile/portal" className="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition-colors border border-slate-200 shrink-0">
                            <ArrowLeftIcon className="w-4 h-4 stroke-[2.5]" />
                        </Link>
                        <div className="w-8 h-8 rounded-xl bg-indigo-600/10 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-600/10 shrink-0 hidden sm:flex">
                            <BuildingStorefrontOutline className="w-5 h-5" />
                        </div>
                        <div>
                            <h1 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-900 leading-tight">Reward Outlet</h1>
                        </div>
                    </div>
                    {sessionSupervisorCode && (
                        <div className="flex items-center gap-2">
                            <div className="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1.5 rounded-xl shadow-inner">
                                <div className="w-5 h-5 rounded-lg bg-indigo-600 text-white text-[9px] font-black flex items-center justify-center uppercase shrink-0">
                                    {sessionSupervisorCode.charAt(0)}
                                </div>
                                <span className="text-[10px] font-black text-slate-700 leading-none truncate max-w-[120px]">{sessionSupervisorName || sessionSupervisorCode}</span>
                            </div>
                            <button
                                type="button"
                                onClick={handleLogout}
                                className="p-1.5 rounded-xl text-rose-500 bg-rose-50 hover:bg-rose-100 transition-colors border border-rose-100 shrink-0"
                                title="Keluar"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </div>
                    )}
                </header>

                {activeTab !== 'summary' && (
                    <div className="px-4 pb-3 flex items-center gap-2">
                        <form onSubmit={handleSearchSubmit} className="relative flex-1 flex items-center">
                            <button type="submit" className="absolute left-3 text-slate-400 hover:text-indigo-600">
                                <MagnifyingGlassIcon className="w-5 h-5" />
                            </button>
                            <input value={search} onChange={(e) => { setSearch(e.target.value); setDisplayLimit(30); }}
                                   type="search"
                                   placeholder="Cari Toko/Kode..."
                                   className="block w-full pl-10 pr-8 py-2 text-sm md:text-base border border-slate-200 rounded-xl bg-slate-50 focus:border-indigo-500 outline-none text-slate-800" />
                            {search && (
                                <button type="button" onClick={clearSearch} className="absolute right-3 text-slate-400 hover:text-slate-600">
                                    <XMarkIcon className="w-4 h-4" />
                                </button>
                            )}
                        </form>
                    </div>
                )}

                {activeTab === 'plan' && (
                    <div className="bg-white pt-2 pb-2 border-t border-slate-100 flex flex-col gap-3 px-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-base font-black text-slate-800">
                                {selectedDate.toLocaleString('en-US', { month: 'long', year: 'numeric' })}
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
                                            {day.toLocaleString('en-US', { weekday: 'short' })}
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

            <main className="flex-1 flex flex-col pt-4">
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
                                    <h4 className="text-sm font-black text-slate-700">No Schedule</h4>
                                    <p className="text-xs text-slate-400 mt-2 font-medium">Looks like no schedule has been found.</p>
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

            {/* Logout Modal */}
            {showLogoutModal && (
                <div className="fixed inset-0 z-[60] bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
                    <div className="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl animate-zoom-in">
                        <div className="w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-4 mx-auto">
                            <ShieldExclamationIcon className="w-6 h-6" />
                        </div>
                        <h3 className="text-base font-black text-slate-800 text-center uppercase tracking-wider mb-2">Akhiri Sesi?</h3>
                        <p className="text-xs text-slate-500 text-center font-medium leading-relaxed mb-6">
                            Anda akan keluar dari sesi supervisor saat ini. Anda perlu masuk kembali untuk melihat data.
                        </p>
                        <div className="flex gap-3">
                            <button onClick={() => setShowLogoutModal(false)} disabled={isLoggingOut} className="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98] disabled:opacity-60">Batal</button>
                            <button onClick={confirmLogout} disabled={isLoggingOut} className="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white shadow-md shadow-rose-500/20 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-60">
                                {isLoggingOut ? <><div className="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Keluar...</> : 'Ya, Keluar'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Detail Modal */}
            <DetailModal data={detailModalData} onClose={closeDetailModal} showToast={showToast} />

            {/* Aksi SKB Modal */}
            <SkbModal data={skbModalData} onClose={closeSkbModal} showToast={showToast} />

            {/* Bottom Navigation Menu */}
            <div className="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200 px-2 pb-[env(safe-area-inset-bottom,12px)] pt-2 shadow-[0_-4px_20px_-5px_rgba(0,0,0,0.08)]">
                <div className="flex items-center justify-around max-w-md mx-auto relative">
                    <button
                        onClick={() => handleTabSwitch('summary')}
                        className={`flex flex-col items-center justify-center gap-1 px-4 py-2 w-full transition-all duration-300 relative ${activeTab === 'summary' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                    >
                        <ChartBarIcon className={`w-6 h-6 transition-all duration-300 ${activeTab === 'summary' ? 'stroke-[2.5] scale-110 mb-0.5' : ''}`} />
                        <span className={`text-[10px] font-bold uppercase tracking-wider ${activeTab === 'summary' ? 'font-black' : ''}`}>Summary</span>
                        {activeTab === 'summary' && <div className="absolute -top-2 w-8 h-1 bg-indigo-600 rounded-b-full"></div>}
                    </button>
                    <button
                        onClick={() => handleTabSwitch('potensi')}
                        className={`flex flex-col items-center justify-center gap-1 px-4 py-2 w-full transition-all duration-300 relative ${activeTab === 'potensi' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                    >
                        <BuildingStorefrontOutline className={`w-6 h-6 transition-all duration-300 ${activeTab === 'potensi' ? 'stroke-[2.5] scale-110 mb-0.5' : ''}`} />
                        <span className={`text-[10px] font-bold uppercase tracking-wider ${activeTab === 'potensi' ? 'font-black' : ''}`}>Potensi</span>
                        {activeTab === 'potensi' && <div className="absolute -top-2 w-8 h-1 bg-indigo-600 rounded-b-full"></div>}
                    </button>
                    <button
                        onClick={() => handleTabSwitch('skb')}
                        className={`flex flex-col items-center justify-center gap-1 px-4 py-2 w-full transition-all duration-300 relative ${activeTab === 'skb' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                    >
                        <ClipboardDocumentListIcon className={`w-6 h-6 transition-all duration-300 ${activeTab === 'skb' ? 'stroke-[2.5] scale-110 mb-0.5' : ''}`} />
                        <span className={`text-[10px] font-bold uppercase tracking-wider ${activeTab === 'skb' ? 'font-black' : ''}`}>SKB</span>
                        {activeTab === 'skb' && <div className="absolute -top-2 w-8 h-1 bg-indigo-600 rounded-b-full"></div>}
                    </button>
                    <button
                        onClick={() => handleTabSwitch('plan')}
                        className={`flex flex-col items-center justify-center gap-1 px-4 py-2 w-full transition-all duration-300 relative ${activeTab === 'plan' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                    >
                        <CalendarIcon className={`w-6 h-6 transition-all duration-300 ${activeTab === 'plan' ? 'stroke-[2.5] scale-110 mb-0.5' : ''}`} />
                        <span className={`text-[10px] font-bold uppercase tracking-wider ${activeTab === 'plan' ? 'font-black' : ''}`}>Visit</span>
                        {activeTab === 'plan' && <div className="absolute -top-2 w-8 h-1 bg-indigo-600 rounded-b-full"></div>}
                    </button>
                </div>
            </div>
        </div>
    );
}
