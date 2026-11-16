import React from 'react';
import { InsentifTabType } from '../constants';
import {
    ChartBarIcon as ChartBarIconSolid,
    UsersIcon as UsersIconSolid,
    InformationCircleIcon as InformationCircleIconSolid,
} from '@heroicons/react/24/solid';
import {
    ChartBarIcon as ChartBarIconOutline,
    UsersIcon as UsersIconOutline,
    InformationCircleIcon as InformationCircleIconOutline,
} from '@heroicons/react/24/outline';

interface InsentifBottomNavProps {
    activeTab: InsentifTabType;
    onTabChange: (tab: InsentifTabType) => void;
    teamCount?: number;
    warningCount?: number;
}

export default function InsentifBottomNav({
    activeTab,
    onTabChange,
    teamCount = 0,
    warningCount = 0,
}: InsentifBottomNavProps) {
    return (
        <nav className="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-200/80 shadow-lg safe-bottom">
            <div className="max-w-2xl md:max-w-4xl mx-auto flex items-center justify-around h-16 px-4">
                {/* 1. Tab Ringkasan */}
                <button
                    type="button"
                    onClick={() => onTabChange('ringkasan')}
                    className={`flex flex-col items-center justify-center flex-1 h-full transition-all ${
                        activeTab === 'ringkasan'
                            ? 'text-indigo-600 font-bold scale-105'
                            : 'text-gray-400 hover:text-gray-600 font-medium'
                    }`}
                >
                    {activeTab === 'ringkasan' ? (
                        <ChartBarIconSolid className="w-5 h-5 mb-0.5" />
                    ) : (
                        <ChartBarIconOutline className="w-5 h-5 mb-0.5" />
                    )}
                    <span className="text-[11px]">Ringkasan</span>
                </button>

                {/* 2. Tab Tim Sales */}
                <button
                    type="button"
                    onClick={() => onTabChange('tim')}
                    className={`flex flex-col items-center justify-center flex-1 h-full relative transition-all ${
                        activeTab === 'tim'
                            ? 'text-indigo-600 font-bold scale-105'
                            : 'text-gray-400 hover:text-gray-600 font-medium'
                    }`}
                >
                    <div className="relative">
                        {activeTab === 'tim' ? (
                            <UsersIconSolid className="w-5 h-5 mb-0.5" />
                        ) : (
                            <UsersIconOutline className="w-5 h-5 mb-0.5" />
                        )}
                        {warningCount > 0 && (
                            <span className="absolute -top-1 -right-2.5 w-4 h-4 bg-rose-500 text-white rounded-full text-[9px] flex items-center justify-center font-bold">
                                {warningCount > 9 ? '9+' : warningCount}
                            </span>
                        )}
                    </div>
                    <span className="text-[11px]">Tim Sales</span>
                </button>

                {/* 3. Tab Aturan */}
                <button
                    type="button"
                    onClick={() => onTabChange('aturan')}
                    className={`flex flex-col items-center justify-center flex-1 h-full transition-all ${
                        activeTab === 'aturan'
                            ? 'text-indigo-600 font-bold scale-105'
                            : 'text-gray-400 hover:text-gray-600 font-medium'
                    }`}
                >
                    {activeTab === 'aturan' ? (
                        <InformationCircleIconSolid className="w-5 h-5 mb-0.5" />
                    ) : (
                        <InformationCircleIconOutline className="w-5 h-5 mb-0.5" />
                    )}
                    <span className="text-[11px]">Aturan & Tier</span>
                </button>
            </div>
        </nav>
    );
}
