import React from 'react';
import {
    MapPinIcon, ShieldCheckIcon, IdentificationIcon,
    InformationCircleIcon, ClipboardDocumentCheckIcon, ChartPieIcon
} from '@heroicons/react/24/outline';
import { ShieldExclamationIcon } from '@heroicons/react/24/solid';

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
    [key: string]: any; // Allow other properties like no_hp, etc.
}

interface StoreCardProps {
    item: SkbRwoItem;
    showProgress?: boolean;
    showSkbAction?: boolean;
    showActualAction?: boolean;
    onOpenDetail: (item: SkbRwoItem) => void;
    onOpenActual?: (item: SkbRwoItem) => void;
    onOpenSkb: (item: SkbRwoItem) => void;
}

export default function StoreCard({ item, showProgress, showSkbAction = true, showActualAction = false, onOpenDetail, onOpenActual, onOpenSkb }: StoreCardProps) {
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

    if (showProgress) {
        if (percent >= 100) {
            borderClass = "border-y border-r border-slate-100 border-l-4 border-l-emerald-500";
            statusColorText = "text-emerald-600";
            statusColorBg = "bg-emerald-500";
            statusColorBadge = "bg-emerald-50 border-emerald-200 text-emerald-700";
        } else if (percent >= 80) {
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
    const rewardPct = targetAmount >= 90000000 ? '2.5%' : (targetAmount >= 30000000 ? '2%' : '1.5%');
    
    return (
        <div className={`bg-white p-3 rounded-2xl shadow-sm flex flex-col gap-3 animate-fade-in relative overflow-hidden group ${borderClass}`}>
            {/* Baris Pertama: Nama Toko & Badges */}
            <div className="flex gap-2 items-start justify-between">
                <div className="flex-1 min-w-0">
                    <h3 className="text-sm font-black text-slate-800 leading-tight group-hover:text-indigo-600 transition-colors truncate">
                        {item.customer_name}
                    </h3>
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
                            <span className="text-[10px] font-black text-slate-600">Rp {new Intl.NumberFormat('id-ID').format(gap > 0 ? gap : 0)}</span>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <div className="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden flex-1">
                            <div className={`h-full rounded-full transition-all duration-1000 ${statusColorBg}`} style={{ width: `${Math.min(percent, 100)}%` }}></div>
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
