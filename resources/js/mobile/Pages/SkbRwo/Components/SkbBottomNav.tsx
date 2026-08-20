import React from 'react';
import { ChartPieIcon, CalendarIcon, ChartBarIcon, BuildingStorefrontIcon as BuildingStorefrontOutline, SignalIcon } from '@heroicons/react/24/outline';
import { ChartPieIcon as ChartPieSolid, CalendarIcon as CalendarSolid, ChartBarIcon as ChartBarSolid, BuildingStorefrontIcon as BuildingStorefrontSolid, SignalIcon as SignalSolid } from '@heroicons/react/24/solid';
import { TabType } from '../constants';

interface SkbBottomNavProps {
    activeTab: TabType;
    onTabSwitch: (tab: TabType) => void;
}

export default function SkbBottomNav({ activeTab, onTabSwitch }: SkbBottomNavProps) {
    return (
        <div 
            className="fixed bottom-0 left-0 right-0 z-40 bg-white/85 backdrop-blur-2xl border-t border-slate-200/80"
            style={{ paddingBottom: 'env(safe-area-inset-bottom, 0px)' }}
        >
            <div className="flex items-center justify-around px-1 pt-2 pb-2">
                <button
                    onClick={() => onTabSwitch('summary')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'summary' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'summary' ? <ChartPieSolid className="w-[22px] h-[22px]" /> : <ChartPieIcon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'summary' ? 'font-bold' : 'font-medium'}`}>Dashboard</span>
                </button>
                <button
                    onClick={() => onTabSwitch('plan')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'plan' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'plan' ? <CalendarSolid className="w-[22px] h-[22px]" /> : <CalendarIcon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'plan' ? 'font-bold' : 'font-medium'}`}>Visit</span>
                </button>
                <button
                    onClick={() => onTabSwitch('monitoring')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'monitoring' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'monitoring' ? <ChartBarSolid className="w-[22px] h-[22px]" /> : <ChartBarIcon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'monitoring' ? 'font-bold' : 'font-medium'}`}>Monitoring</span>
                </button>
                <button
                    onClick={() => onTabSwitch('potensi')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'potensi' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'potensi' ? <BuildingStorefrontSolid className="w-[22px] h-[22px]" /> : <BuildingStorefrontOutline className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'potensi' ? 'font-bold' : 'font-medium'}`}>SKB</span>
                </button>
                <button
                    onClick={() => onTabSwitch('radar')}
                    className={`flex flex-col items-center justify-center gap-1 w-full transition-colors ${activeTab === 'radar' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {activeTab === 'radar' ? <SignalSolid className="w-[22px] h-[22px]" /> : <SignalIcon className="w-[22px] h-[22px]" />}
                    <span className={`text-[10px] tracking-wide ${activeTab === 'radar' ? 'font-bold' : 'font-medium'}`}>Nearby</span>
                </button>
            </div>
        </div>
    );
}
