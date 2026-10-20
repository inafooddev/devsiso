import React, { useState, useEffect } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import { 
    BuildingStorefrontIcon, 
    MapPinIcon, 
    SparklesIcon,
    ArrowRightIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    UserCircleIcon,
    ArrowRightOnRectangleIcon,
    ClipboardDocumentCheckIcon,
    ArrowPathIcon
} from '@heroicons/react/24/solid';

export default function Home() {
    const { auth } = usePage().props as any;
    const [loadingModule, setLoadingModule] = useState<string | null>(null);

    const userRolesRaw = auth?.user?.roles || (auth?.user?.role ? [auth.user.role] : []);
    const userRoles: string[] = Array.isArray(userRolesRaw)
        ? userRolesRaw.map((r: any) => (typeof r === 'object' && r !== null ? r.name : String(r)).toLowerCase())
        : [String(userRolesRaw).toLowerCase()];

    const allowedAuditRoles = ['audit', 'manaudit', 'admin', 'super-admin'];
    const canAccessAudit = userRoles.some((r: string) => allowedAuditRoles.includes(r));

    useEffect(() => {
        const unbindFinish = router.on('finish', () => {
            setLoadingModule(null);
        });
        const unbindError = router.on('error', () => {
            setLoadingModule(null);
        });

        return () => {
            unbindFinish();
            unbindError();
        };
    }, []);

    const handleNavigate = (url: string, moduleName: string) => {
        if (loadingModule) return;
        setLoadingModule(moduleName);
        router.visit(url, {
            onError: () => setLoadingModule(null),
            onCancel: () => setLoadingModule(null),
        });
    };

    return (
        <div className="min-h-screen bg-[#F8FAFC] font-sans pb-10 selection:bg-indigo-500 selection:text-white relative">
            <Head title="SISO Workspace" />

            {/* Global Navigation Loading Overlay */}
            {loadingModule && (
                <div className="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-sm animate-fade-in px-6">
                    <div className="bg-white rounded-3xl p-6 shadow-2xl flex flex-col items-center max-w-[280px] w-full text-center border border-slate-100 animate-scale-up">
                        <div className="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 mb-4 shadow-sm relative">
                            <svg className="w-8 h-8 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <h3 className="text-base font-black text-slate-800 tracking-tight">Memuat Modul...</h3>
                        <p className="text-xs font-bold text-indigo-600 mt-1">{loadingModule}</p>
                        <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-4">
                            <div className="bg-indigo-600 h-full rounded-full animate-pulse w-3/4 mx-auto"></div>
                        </div>
                        <p className="text-[11px] text-slate-400 mt-3 font-medium">Sedang menyiapkan data, mohon tunggu...</p>
                    </div>
                </div>
            )}

            {/* Header / Hero Section */}
            <div className="bg-slate-900 pt-12 pb-24 rounded-b-[2.5rem] relative overflow-hidden shadow-2xl">
                {/* Abstract Background Shapes */}
                <div className="absolute top-[-20%] right-[-10%] w-64 h-64 bg-indigo-500/30 rounded-full blur-3xl mix-blend-screen"></div>
                <div className="absolute bottom-[-20%] left-[-10%] w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl mix-blend-screen"></div>
                <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5 mix-blend-overlay pointer-events-none"></div>
                
                <div className="relative z-10 px-6 max-w-lg mx-auto">
                    {/* Top Navbar */}
                    <div className="flex items-center justify-end mb-6">
                        <div className="flex items-center gap-3">
                            <button 
                                onClick={() => handleNavigate('/mobile/profile', 'Profil Pengguna')}
                                disabled={Boolean(loadingModule)}
                                className="w-10 h-10 rounded-full bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center text-white hover:bg-white/20 transition-colors"
                            >
                                <UserCircleIcon className="w-6 h-6" />
                            </button>
                            <Link 
                                href="/mobile/logout" 
                                method="post" 
                                as="button" 
                                onClick={() => setLoadingModule('Keluar Akun')}
                                className="w-10 h-10 rounded-full bg-rose-500/20 backdrop-blur-md border border-rose-500/30 flex items-center justify-center text-rose-200 hover:bg-rose-500/40 hover:text-white transition-colors"
                            >
                                <ArrowRightOnRectangleIcon className="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                    
                    {/* Rename dari Mobile Portal menjadi SISO Workspace */}
                    <h1 className="text-3xl md:text-4xl font-black text-white tracking-tight leading-tight mb-2">
                        SISO <br/>
                        <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">
                            Workspace
                        </span>
                    </h1>
                    <p className="text-sm text-slate-400 font-medium max-w-xs leading-relaxed">
                        Pilih modul aplikasi untuk mengelola operasional lapangan Anda.
                    </p>
                </div>
            </div>

            {/* Menu Container */}
            <div className="px-5 -mt-14 relative z-20 max-w-lg mx-auto">
                <div className="grid grid-cols-2 gap-4">
                    
                    {/* Card 1: SKB RWO */}
                    <div 
                        role="button"
                        tabIndex={0}
                        onClick={() => handleNavigate('/mobile/skb-rwo', 'Reward Outlet')}
                        className="group block h-full cursor-pointer select-none"
                    >
                        <div className="bg-white p-5 rounded-[28px] h-full shadow-lg shadow-indigo-100/50 hover:shadow-xl border border-slate-100 transition-all duration-300 transform active:scale-95 flex flex-col justify-between relative overflow-hidden group-hover:-translate-y-1">
                            {/* Decorative gradient blob */}
                            <div className="absolute -right-6 -top-6 w-24 h-24 bg-indigo-50 rounded-full blur-2xl group-hover:bg-indigo-100 transition-colors"></div>
                            
                            <div className="relative z-10 mb-6">
                                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-md shadow-indigo-200 text-white mb-4 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                    <BuildingStorefrontIcon className="w-6 h-6" />
                                </div>
                                <h2 className="text-sm font-black text-slate-800 uppercase tracking-wide leading-snug">
                                    Reward Outlet
                                </h2>
                            </div>
                            
                            <div className="relative z-10 flex items-center justify-between mt-auto">
                                <span className="text-xs font-bold text-slate-400">
                                    {loadingModule === 'Reward Outlet' ? 'Memuat...' : 'Akses Modul'}
                                </span>
                                <div className="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                                    {loadingModule === 'Reward Outlet' ? (
                                        <ArrowPathIcon className="w-3.5 h-3.5 text-indigo-600 animate-spin" />
                                    ) : (
                                        <ArrowRightIcon className="w-3 h-3 text-slate-300 group-hover:text-indigo-600 transition-colors" />
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Card 2: Perbaikan Tikor */}
                    <div 
                        role="button"
                        tabIndex={0}
                        onClick={() => handleNavigate('/mobile/perbaikan-tikor-tim-elite', 'Perbaikan Tikor')}
                        className="group block h-full cursor-pointer select-none"
                    >
                        <div className="bg-white p-5 rounded-[28px] h-full shadow-lg shadow-emerald-100/50 hover:shadow-xl border border-slate-100 transition-all duration-300 transform active:scale-95 flex flex-col justify-between relative overflow-hidden group-hover:-translate-y-1">
                            {/* Decorative gradient blob */}
                            <div className="absolute -right-6 -top-6 w-24 h-24 bg-emerald-50 rounded-full blur-2xl group-hover:bg-emerald-100 transition-colors"></div>
                            
                            <div className="relative z-10 mb-6">
                                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-md shadow-emerald-200 text-white mb-4 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                    <MapPinIcon className="w-6 h-6" />
                                </div>
                                <h2 className="text-sm font-black text-slate-800 uppercase tracking-wide leading-snug">
                                    Perbaikan Tikor
                                </h2>
                            </div>
                            
                            <div className="relative z-10 flex items-center justify-between mt-auto">
                                <span className="text-xs font-bold text-slate-400">
                                    {loadingModule === 'Perbaikan Tikor' ? 'Memuat...' : 'Khusus Elite'}
                                </span>
                                <div className="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-emerald-50 transition-colors">
                                    {loadingModule === 'Perbaikan Tikor' ? (
                                        <ArrowPathIcon className="w-3.5 h-3.5 text-emerald-600 animate-spin" />
                                    ) : (
                                        <ArrowRightIcon className="w-3 h-3 text-slate-300 group-hover:text-emerald-600 transition-colors" />
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Card Audit Toko - Only for audit, manaudit, admin roles */}
                    {canAccessAudit && (
                        <div 
                            role="button"
                            tabIndex={0}
                            onClick={() => handleNavigate('/mobile/audit', 'Audit Toko')}
                            className="group block h-full cursor-pointer select-none"
                        >
                            <div className="bg-white p-5 rounded-[28px] h-full shadow-lg shadow-purple-100/50 hover:shadow-xl border border-slate-100 transition-all duration-300 transform active:scale-95 flex flex-col justify-between relative overflow-hidden group-hover:-translate-y-1">
                                {/* Decorative gradient blob */}
                                <div className="absolute -right-6 -top-6 w-24 h-24 bg-purple-50 rounded-full blur-2xl group-hover:bg-purple-100 transition-colors"></div>
                                
                                <div className="relative z-10 mb-6">
                                    <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-md shadow-purple-200 text-white mb-4 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                        <ClipboardDocumentCheckIcon className="w-6 h-6" />
                                    </div>
                                    <h2 className="text-sm font-black text-slate-800 uppercase tracking-wide leading-snug">
                                        Audit Toko
                                    </h2>
                                </div>
                                
                                <div className="relative z-10 flex items-center justify-between mt-auto">
                                    <span className="text-xs font-bold text-slate-400">
                                        {loadingModule === 'Audit Toko' ? 'Memuat...' : 'Akses Modul'}
                                    </span>
                                    <div className="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-purple-50 transition-colors">
                                        {loadingModule === 'Audit Toko' ? (
                                            <ArrowPathIcon className="w-3.5 h-3.5 text-purple-600 animate-spin" />
                                        ) : (
                                            <ArrowRightIcon className="w-3 h-3 text-slate-300 group-hover:text-purple-600 transition-colors" />
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                    
                    {/* Card 3: Call Plan - Disabled for now */}
                    <div className="group block h-full cursor-default select-none">
                        <div className="bg-white p-5 rounded-[28px] h-full shadow-lg shadow-sky-100/50 hover:shadow-xl border border-slate-100 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
                            {/* Decorative gradient blob */}
                            <div className="absolute -right-6 -top-6 w-24 h-24 bg-sky-50 rounded-full blur-2xl group-hover:bg-sky-100 transition-colors"></div>
                            
                            <div className="relative z-10 mb-6">
                                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center shadow-md shadow-sky-200 text-white mb-4">
                                    <CalendarDaysIcon className="w-6 h-6" />
                                </div>
                                <h2 className="text-sm font-black text-slate-800 uppercase tracking-wide leading-snug">
                                    Call Plan
                                </h2>
                            </div>
                            
                            <div className="relative z-10 flex items-center justify-between mt-auto">
                                <span className="text-xs font-bold text-slate-400">Segera Hadir</span>
                                <div className="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center">
                                    <ArrowRightIcon className="w-3 h-3 text-slate-300" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Card 4: Report */}
                    <div className="group block h-full cursor-default select-none">
                        <div className="bg-white p-5 rounded-[28px] h-full shadow-lg shadow-amber-100/50 hover:shadow-xl border border-slate-100 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
                            {/* Decorative gradient blob */}
                            <div className="absolute -right-6 -top-6 w-24 h-24 bg-amber-50 rounded-full blur-2xl group-hover:bg-amber-100 transition-colors"></div>
                            
                            <div className="relative z-10 mb-6">
                                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md shadow-amber-200 text-white mb-4">
                                    <ChartBarIcon className="w-6 h-6" />
                                </div>
                                <h2 className="text-sm font-black text-slate-800 uppercase tracking-wide leading-snug">
                                    Modul Report
                                </h2>
                            </div>
                            
                            <div className="relative z-10 flex items-center justify-between mt-auto">
                                <span className="text-xs font-bold text-slate-400">Segera Hadir</span>
                                <div className="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center">
                                    <ArrowRightIcon className="w-3 h-3 text-slate-300" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

            {/* Footer Text */}
            <div className="mt-16 text-center">
                <p className="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center justify-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                    System Online
                </p>
            </div>
        </div>
    );
}
