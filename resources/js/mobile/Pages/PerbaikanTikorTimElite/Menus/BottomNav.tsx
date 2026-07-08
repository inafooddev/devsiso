import React from 'react';
import { router } from '@inertiajs/react';
import { HomeIcon as HomeOutline, BuildingStorefrontIcon as BuildingStorefrontOutline, ChartPieIcon as ChartPieOutline, CalendarDaysIcon as CalendarDaysOutline } from '@heroicons/react/24/outline';
import { BuildingStorefrontIcon as BuildingStorefrontSolid, ChartPieIcon as ChartPieSolid, CalendarDaysIcon as CalendarDaysSolid } from '@heroicons/react/24/solid';

interface BottomNavProps {
    activeTab: string;
    handleTabSwitch: (tab: string) => void;
}

export default function BottomNav({ activeTab, handleTabSwitch }: BottomNavProps) {
    return (
        <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-[0_-10px_40px_rgba(0,0,0,0.04)] z-40 pb-safe">
            <div className="flex items-center justify-around px-1 pt-2 pb-2">
                <button
                    onClick={() => router.visit('/mobile/home')}
                    className="flex flex-col items-center justify-center gap-1 w-full transition-colors text-slate-400 hover:text-slate-600"
                >
                    <HomeOutline className="w-6 h-6" />
                    <span className="text-xs tracking-wide font-medium">Home</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('laporan')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'laporan' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'laporan' ? <ChartPieSolid className="w-6 h-6" /> : <ChartPieOutline className="w-6 h-6" />}
                    <span className={`text-xs tracking-wide ${activeTab === 'laporan' ? 'font-bold' : 'font-medium'}`}>Summary</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('visit')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'visit' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'visit' ? <CalendarDaysSolid className="w-6 h-6" /> : <CalendarDaysOutline className="w-6 h-6" />}
                    <span className={`text-xs tracking-wide ${activeTab === 'visit' ? 'font-bold' : 'font-medium'}`}>Visit</span>
                </button>
                <button
                    onClick={() => handleTabSwitch('toko')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'toko' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'toko' ? <BuildingStorefrontSolid className="w-6 h-6" /> : <BuildingStorefrontOutline className="w-6 h-6" />}
                    <span className={`text-xs tracking-wide ${activeTab === 'toko' ? 'font-bold' : 'font-medium'}`}>Customer</span>
                </button>
            </div>
        </div>
    );
}
