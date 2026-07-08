import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { 
    BuildingStorefrontIcon, 
    MapPinIcon, 
    SparklesIcon,
    ArrowRightIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    UserCircleIcon,
    ArrowRightOnRectangleIcon
} from '@heroicons/react/24/solid';

export default function Home() {
    return (
        <div className="min-h-screen bg-[#F8FAFC] font-sans pb-10 selection:bg-indigo-500 selection:text-white">
            <Head title="SISO Workspace" />

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
                            <Link href="/mobile/profile" className="w-10 h-10 rounded-full bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center text-white hover:bg-white/20 transition-colors">
                                <UserCircleIcon className="w-6 h-6" />
                            </Link>
                            <Link href="/mobile/logout" method="post" as="button" className="w-10 h-10 rounded-full bg-rose-500/20 backdrop-blur-md border border-rose-500/30 flex items-center justify-center text-rose-200 hover:bg-rose-500/40 hover:text-white transition-colors">
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
                    <Link href="/mobile/skb-rwo" className="group block h-full">
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
                                <span className="text-xs font-bold text-slate-400">Akses Modul</span>
                                <div className="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                                    <ArrowRightIcon className="w-3 h-3 text-slate-300 group-hover:text-indigo-600 transition-colors" />
                                </div>
                            </div>
                        </div>
                    </Link>

                    {/* Card 2: Perbaikan Tikor */}
                    <Link href="/mobile/perbaikan-tikor-tim-elite" className="group block h-full">
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
                                <span className="text-xs font-bold text-slate-400">Khusus Elite</span>
                                <div className="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-emerald-50 transition-colors">
                                    <ArrowRightIcon className="w-3 h-3 text-slate-300 group-hover:text-emerald-600 transition-colors" />
                                </div>
                            </div>
                        </div>
                    </Link>
                    
                    {/* Card 3: Call Plan */}
                    <Link href="/mobile/call-plan" className="group block h-full">
                        <div className="bg-white p-5 rounded-[28px] h-full shadow-lg shadow-sky-100/50 hover:shadow-xl border border-slate-100 transition-all duration-300 transform active:scale-95 flex flex-col justify-between relative overflow-hidden group-hover:-translate-y-1">
                            {/* Decorative gradient blob */}
                            <div className="absolute -right-6 -top-6 w-24 h-24 bg-sky-50 rounded-full blur-2xl group-hover:bg-sky-100 transition-colors"></div>
                            
                            <div className="relative z-10 mb-6">
                                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center shadow-md shadow-sky-200 text-white mb-4 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                    <CalendarDaysIcon className="w-6 h-6" />
                                </div>
                                <h2 className="text-sm font-black text-slate-800 uppercase tracking-wide leading-snug">
                                    Call Plan
                                </h2>
                            </div>
                            
                            <div className="relative z-10 flex items-center justify-between mt-auto">
                                <span className="text-xs font-bold text-slate-400">Akses Modul</span>
                                <div className="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-sky-50 transition-colors">
                                    <ArrowRightIcon className="w-3 h-3 text-slate-300 group-hover:text-sky-600 transition-colors" />
                                </div>
                            </div>
                        </div>
                    </Link>

                    {/* Card 4: Report */}
                    <div onClick={(e) => e.preventDefault()} className="group block h-full cursor-default">
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
