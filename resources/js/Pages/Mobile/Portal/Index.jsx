import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { 
    BuildingStorefrontIcon, 
    MapPinIcon, 
    SparklesIcon,
    ArrowRightIcon
} from '@heroicons/react/24/solid';

export default function MobilePortal() {
    return (
        <div className="min-h-screen bg-[#F8FAFC] font-sans pb-10 selection:bg-indigo-500 selection:text-white">
            <Head title="Mobile Portal - SISO" />

            {/* Header / Hero Section */}
            <div className="bg-slate-900 px-6 pt-12 pb-24 rounded-b-[40px] relative overflow-hidden shadow-2xl">
                {/* Abstract Background Shapes */}
                <div className="absolute top-[-20%] right-[-10%] w-64 h-64 bg-indigo-500/30 rounded-full blur-3xl mix-blend-screen"></div>
                <div className="absolute bottom-[-20%] left-[-10%] w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl mix-blend-screen"></div>
                <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5 mix-blend-overlay pointer-events-none"></div>
                
                <div className="relative z-10">
                    <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/10 mb-6">
                        <SparklesIcon className="w-4 h-4 text-amber-400" />
                        <span className="text-[10px] font-bold text-white uppercase tracking-widest">SISO Workspace</span>
                    </div>
                    
                    <h1 className="text-3xl md:text-4xl font-black text-white tracking-tight leading-tight mb-2">
                        Mobile <br/>
                        <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">
                            Portal
                        </span>
                    </h1>
                    <p className="text-sm text-slate-400 font-medium max-w-[280px] leading-relaxed">
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
                                <h2 className="text-[13px] font-black text-slate-800 uppercase tracking-wide leading-snug">
                                    Reward <br/>Outlet
                                </h2>
                            </div>
                            
                            <div className="relative z-10 flex items-center justify-between mt-auto">
                                <span className="text-[10px] font-bold text-slate-400">Akses Modul</span>
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
                                <h2 className="text-[13px] font-black text-slate-800 uppercase tracking-wide leading-snug">
                                    Perbaikan <br/>Tikor
                                </h2>
                            </div>
                            
                            <div className="relative z-10 flex items-center justify-between mt-auto">
                                <span className="text-[10px] font-bold text-slate-400">Khusus Elite</span>
                                <div className="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-emerald-50 transition-colors">
                                    <ArrowRightIcon className="w-3 h-3 text-slate-300 group-hover:text-emerald-600 transition-colors" />
                                </div>
                            </div>
                        </div>
                    </Link>
                    
                </div>
            </div>

            {/* Footer Text */}
            <div className="mt-16 text-center">
                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                    System Online
                </p>
            </div>
        </div>
    );
}
