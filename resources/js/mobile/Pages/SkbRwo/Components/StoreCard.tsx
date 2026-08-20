import React from 'react';
import {
    MapPinIcon, ShieldCheckIcon, IdentificationIcon,
    InformationCircleIcon, ClipboardDocumentCheckIcon, ChartPieIcon,
    ArrowTopRightOnSquareIcon
} from '@heroicons/react/24/outline';
import { ShieldExclamationIcon } from '@heroicons/react/24/solid';
import { formatDistance } from '../utils/geo';

import { INCENTIVE_THRESHOLDS } from '../constants';

export interface SkbRwoItem {
    customer_code: string;
    customer_name: string;
    address?: string;
    status_skb: 'Sudah' | 'Belum';
    status_data_lengkap: 'Lengkap' | 'Belum';
    is_approved: boolean | number | null;
    skb_reason?: string | null;
    reason?: string | null;
    skb_foto?: string | null;
    total_target?: number;
    total_achievement?: number;
    month_1_value?: number;
    month_2_value?: number;
    month_3_value?: number;
    latitude?: string | null;
    longitude?: string | null;
    [key: string]: any;
}

export interface StoreCardProps {
    item: SkbRwoItem;
    showProgress?: boolean;
    showSkbAction?: boolean;
    showActualAction?: boolean;
    showDirection?: boolean;
    distance?: number | null;
    onOpenDetail?: (item: SkbRwoItem) => void;
    onOpenActual?: (item: SkbRwoItem) => void;
    onOpenSkb?: (item: SkbRwoItem) => void;
}

export const getProratedTarget = (totalTarget: number, kuartalStr?: string | null): number => {
    if (!totalTarget || !kuartalStr) return totalTarget;
    
    const currentMonth = new Date().getMonth() + 1; // 1-12
    const kuartal = Number(kuartalStr);
    
    if (isNaN(kuartal) || kuartal < 1 || kuartal > 4) return totalTarget;
    
    const firstMonthOfQ = (kuartal - 1) * 3 + 1;
    const lastMonthOfQ = firstMonthOfQ + 2;
    
    let multiplier = 3;
    if (currentMonth < firstMonthOfQ) {
        multiplier = 3;
    } else if (currentMonth > lastMonthOfQ) {
        multiplier = 3;
    } else {
        multiplier = currentMonth - firstMonthOfQ + 1; // 1, 2, or 3
    }
    
    return (totalTarget / 3) * multiplier;
};

export default function StoreCard({ item, showProgress, showSkbAction = true, showActualAction = false, showDirection = false, distance = null, onOpenDetail, onOpenActual, onOpenSkb }: StoreCardProps) {
    const isApproved = item.is_approved === 1 || item.is_approved === true;
    const isRejected = item.is_approved === 0 || item.is_approved === false;
    
    const target = item.total_target || 0;
    const achievement = item.total_achievement || 0;
    const percent = target > 0 ? (achievement / target) * 100 : 0;
    const gap = target - achievement;

    let borderClass = "border border-slate-100";
    let statusColorText = "text-slate-600";
    let statusColorBg = "bg-slate-500";
    let statusColorBadge = "bg-slate-100 text-slate-700";

    const proratedTarget = getProratedTarget(target, item.kuartal);
    const proratedPercent = proratedTarget > 0 ? (achievement / proratedTarget) * 100 : 0;

    if (showProgress) {
        if (proratedPercent >= 100) {
            borderClass = "border-y border-r border-slate-100 border-l-4 border-l-emerald-500";
            statusColorText = "text-emerald-600";
            statusColorBg = "bg-emerald-500";
            statusColorBadge = "bg-emerald-50 border-emerald-200 text-emerald-700";
        } else if (proratedPercent >= 80) {
            borderClass = "border-y border-r border-slate-100 border-l-4 border-l-amber-500";
            statusColorText = "text-amber-600";
            statusColorBg = "bg-amber-500";
            statusColorBadge = "bg-amber-50 border-amber-200 text-amber-700";
        } else {
            borderClass = "border-y border-r border-slate-100 border-l-4 border-l-rose-500";
            statusColorText = "text-rose-600";
            statusColorBg = "bg-rose-500";
            statusColorBadge = "bg-rose-50 border-rose-200 text-rose-700";
        }
    }
    
    const targetAmount = Number(item.total_target) || 0;
    const rewardPct = targetAmount >= INCENTIVE_THRESHOLDS.TIER_1.minTarget 
        ? INCENTIVE_THRESHOLDS.TIER_1.rewardPct 
        : (targetAmount >= INCENTIVE_THRESHOLDS.TIER_2.minTarget ? INCENTIVE_THRESHOLDS.TIER_2.rewardPct : INCENTIVE_THRESHOLDS.DEFAULT.rewardPct);
    
    return (
        <div className={`bg-white p-3 rounded-2xl shadow-sm flex flex-col gap-3 animate-fade-in relative overflow-hidden group ${borderClass}`}>
            {/* Baris Pertama: Nama Toko & Badges */}
            <div className="flex gap-2 items-start justify-between">
                <div className="flex-1 min-w-0">
                    <h3 className="text-sm font-black text-slate-800 leading-tight group-hover:text-indigo-600 transition-colors truncate">
                        {item.customer_name}
                    </h3>
                    {distance !== null && distance !== undefined && (
                        <span className="inline-flex items-center gap-0.5 mt-0.5 px-1.5 py-0.5 bg-emerald-50 border border-emerald-200 rounded-md text-[9px] font-bold text-emerald-600">
                            <MapPinIcon className="w-2.5 h-2.5" />
                            {formatDistance(distance)}
                        </span>
                    )}
                </div>
                
                <div className="flex items-center justify-end gap-1.5 shrink-0">
                    {showProgress && (
                        <div className={`px-1.5 py-0.5 flex items-center gap-1 rounded-md border bg-indigo-50 border-indigo-200 text-indigo-700 shrink-0`}>
                            <span className="text-[9px] font-bold uppercase tracking-wider">{rewardPct}</span>
                        </div>
                    )}
                    <div className={`px-1.5 py-0.5 flex items-center gap-1 rounded-md border ${item.status_skb === 'Sudah' ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'} shrink-0`}>
                        {item.status_skb === 'Sudah' ? 
                            <ShieldCheckIcon className="w-3 h-3 text-emerald-500" /> : 
                            <ShieldExclamationIcon className="w-3 h-3 text-amber-500" />
                        }
                        <span className={`text-[9px] font-bold uppercase tracking-wider ${item.status_skb === 'Sudah' ? 'text-emerald-600' : 'text-amber-600'}`}>SKB</span>
                    </div>
                    <div className={`px-1.5 py-0.5 flex items-center gap-1 rounded-md border ${item.status_data_lengkap === 'Lengkap' ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'} shrink-0`}>
                        {item.status_data_lengkap === 'Lengkap' ? 
                            <ShieldCheckIcon className="w-3 h-3 text-emerald-500" /> : 
                            <ShieldExclamationIcon className="w-3 h-3 text-amber-500" />
                        }
                        <span className={`text-[9px] font-bold uppercase tracking-wider ${item.status_data_lengkap === 'Lengkap' ? 'text-emerald-600' : 'text-amber-600'}`}>Data</span>
                    </div>
                </div>
            </div>

            {/* Baris Kedua: Kode & Alamat */}
            <div className="flex flex-col gap-1.5 -mt-1">
                <div className="flex items-center gap-1.5">
                    <IdentificationIcon className="w-4 h-4 text-slate-400" />
                    <p className="text-xs font-bold text-indigo-600">{item.customer_code}</p>
                </div>
                <div className="flex items-start gap-1 text-slate-500 w-full min-w-0">
                    <MapPinIcon className="w-3.5 h-3.5 mt-0.5 shrink-0" />
                    <p className="text-[11px] leading-snug truncate">{item.address || '-'}</p>
                </div>
            </div>

            {showProgress && (
                <div className="pt-3 flex flex-col gap-2">
                    <div className="grid grid-cols-3 gap-2">
                        <div className="flex flex-col">
                            <span className="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Target</span>
                            <span className="text-[10px] font-black text-slate-700">Rp {new Intl.NumberFormat('id-ID').format(target)}</span>
                        </div>
                        <div className="flex flex-col items-center">
                            <span className="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Actual</span>
                            <span className={`text-[10px] font-black ${statusColorText}`}>Rp {new Intl.NumberFormat('id-ID').format(achievement)}</span>
                        </div>
                        <div className="flex flex-col items-end text-right">
                            <span className="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Gap</span>
                            <span className={`text-[10px] font-black ${gap > 0 ? 'text-rose-500' : 'text-slate-600'}`}>Rp {new Intl.NumberFormat('id-ID').format(gap > 0 ? gap : 0)}</span>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <div className="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden flex-1 relative">
                            <div className={`h-full rounded-full transition-all duration-1000 ${statusColorBg}`} style={{ width: `${Math.min(percent, 100)}%` }}></div>
                            <div className="absolute top-0 bottom-0 left-1/3 w-[1.5px] bg-slate-500/70 z-10"></div>
                            <div className="absolute top-0 bottom-0 left-2/3 w-[1.5px] bg-slate-500/70 z-10"></div>
                        </div>
                        <span className={`px-2 py-0.5 rounded-md text-[9px] font-black tracking-wider shrink-0 ${statusColorBadge}`}>
                            {percent.toFixed(1)}%
                        </span>
                    </div>
                </div>
            )}
            
            {/* Baris Ketiga: Action Buttons */}
            <div className="pt-2 border-t border-slate-100 flex gap-2 overflow-x-auto no-scrollbar">
                <button onClick={() => onOpenDetail(item)} className="flex-1 py-2 px-1 flex items-center justify-center gap-1.5 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 font-bold text-[10px] sm:text-[11px] uppercase tracking-wider transition-colors border border-indigo-100 whitespace-nowrap min-w-[80px]">
                    <InformationCircleIcon className="w-3.5 h-3.5" /> Detail
                </button>
                {showActualAction && onOpenActual && (
                    <button onClick={() => onOpenActual(item)} className="flex-1 py-2 px-1 flex items-center justify-center gap-1.5 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 font-bold text-[10px] sm:text-[11px] uppercase tracking-wider transition-colors border border-emerald-100 whitespace-nowrap min-w-[80px]">
                        <ChartPieIcon className="w-3.5 h-3.5" /> Actual
                    </button>
                )}
                {showSkbAction && (
                    isApproved ? (
                        <button onClick={() => onOpenSkb(item)} className="flex-1 py-2 px-1 flex items-center justify-center gap-1.5 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 font-bold text-[10px] sm:text-[11px] uppercase tracking-wider transition-colors border border-emerald-100 whitespace-nowrap min-w-[90px]">
                            <ShieldCheckIcon className="w-3.5 h-3.5" /> Lihat SKB
                        </button>
                    ) : (
                        <button onClick={() => onOpenSkb(item)} className="flex-1 py-2 px-1 flex items-center justify-center gap-1.5 rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 font-bold text-[10px] sm:text-[11px] uppercase tracking-wider shadow-sm shadow-indigo-200 transition-colors whitespace-nowrap min-w-[90px]">
                            <ClipboardDocumentCheckIcon className="w-3.5 h-3.5" /> Form SKB
                        </button>
                    )
                )}
                {showDirection && item.latitude && item.longitude && (
                    <a
                        href={`https://www.google.com/maps/dir/?api=1&destination=${item.latitude},${item.longitude}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex-1 py-2 px-1 flex items-center justify-center gap-1.5 rounded-lg text-sky-600 bg-sky-50 hover:bg-sky-100 font-bold text-[10px] sm:text-[11px] uppercase tracking-wider transition-colors border border-sky-100 whitespace-nowrap min-w-[90px]"
                    >
                        <ArrowTopRightOnSquareIcon className="w-3.5 h-3.5" /> Arah
                    </a>
                )}
            </div>

            {/* Baris Keempat: Alasan Penolakan (khusus SKB Rejected) */}
            {isRejected && (item.skb_reason || item.reason) ? (
                <div className="mt-1 bg-rose-50 p-3 rounded-xl border border-rose-100 flex items-start gap-2 shadow-sm">
                    <ShieldExclamationIcon className="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
                    <div className="flex flex-col">
                        <span className="text-[10px] font-bold text-rose-700 uppercase tracking-wider mb-0.5">Alasan Penolakan</span>
                        <span className="text-[11px] text-rose-600 leading-relaxed font-medium">{item.skb_reason || item.reason}</span>
                    </div>
                </div>
            ) : null}
        </div>
    );
}
