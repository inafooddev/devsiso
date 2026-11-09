import React from 'react';
import {
    CalendarDaysIcon as CalendarDaysOutline,
    ClipboardDocumentCheckIcon as ClipboardDocumentCheckOutline,
} from '@heroicons/react/24/outline';
import {
    CalendarDaysIcon as CalendarDaysSolid,
    ClipboardDocumentCheckIcon as ClipboardDocumentCheckSolid,
} from '@heroicons/react/24/solid';

export type JksTabType = 'jadwal' | 'pengajuan';

interface BottomNavProps {
    activeTab: JksTabType;
    onTabSwitch: (tab: JksTabType) => void;
    pendingApprovalsCount?: number;
}

export default function BottomNav({
    activeTab,
    onTabSwitch,
    pendingApprovalsCount = 0,
}: BottomNavProps) {
    const tabs: {
        id: JksTabType;
        label: string;
        outlineIcon: React.ComponentType<{ className?: string }>;
        solidIcon: React.ComponentType<{ className?: string }>;
        badge?: number;
    }[] = [
        {
            id: 'jadwal',
            label: 'Jadwal',
            outlineIcon: CalendarDaysOutline,
            solidIcon: CalendarDaysSolid,
        },
        {
            id: 'pengajuan',
            label: 'Pengajuan',
            outlineIcon: ClipboardDocumentCheckOutline,
            solidIcon: ClipboardDocumentCheckSolid,
            badge: pendingApprovalsCount,
        },
    ];

    return (
        <div
            className="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/80 shadow-[0_-8px_30px_rgba(0,0,0,0.04)]"
            style={{ paddingBottom: 'env(safe-area-inset-bottom, 0px)' }}
        >
            <div className="max-w-2xl md:max-w-4xl mx-auto flex items-center justify-around px-2 pt-2 pb-2">
                {tabs.map((tab) => {
                    const isActive = activeTab === tab.id;
                    const Icon = isActive ? tab.solidIcon : tab.outlineIcon;
                    return (
                        <button
                            key={tab.id}
                            type="button"
                            onClick={() => onTabSwitch(tab.id)}
                            className={`flex flex-col items-center justify-center gap-1 w-full py-0.5 transition-colors active:scale-95 ${
                                isActive
                                    ? 'text-indigo-600'
                                    : 'text-slate-400 hover:text-slate-600'
                            }`}
                        >
                            <div className="relative flex items-center justify-center">
                                <Icon className="w-6 h-6 transition-transform" />
                                {Boolean(tab.badge && tab.badge > 0) && (
                                    <span className="absolute -top-1.5 left-4 px-1.5 py-0.5 min-w-[18px] rounded-full text-3xs leading-none font-extrabold bg-amber-500 text-white flex items-center justify-center shadow-sm ring-2 ring-white pointer-events-none">
                                        {tab.badge && tab.badge > 999 ? '999+' : tab.badge}
                                    </span>
                                )}
                            </div>
                            <span
                                className={`text-2xs sm:text-xs tracking-wide leading-none ${
                                    isActive ? 'font-bold' : 'font-medium'
                                }`}
                            >
                                {tab.label}
                            </span>
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
