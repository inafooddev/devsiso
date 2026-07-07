import React from 'react';
import {
    MapPinIcon, ShieldCheckIcon, IdentificationIcon,
    InformationCircleIcon, ClipboardDocumentCheckIcon
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
    [key: string]: any; // Allow other properties like no_hp, etc.
}

interface StoreCardProps {
    item: SkbRwoItem;
    onOpenDetail: (item: SkbRwoItem) => void;
    onOpenSkb: (item: SkbRwoItem) => void;
}

export default function StoreCard({ item, onOpenDetail, onOpenSkb }: StoreCardProps) {
    const isApproved = item.is_approved === 1 || item.is_approved === true;
    const isRejected = item.is_approved === 0 || item.is_approved === false;
    
    return (
        <div className="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col gap-4 animate-fade-in relative overflow-hidden group">
            {/* Baris Pertama: Nama Toko & Badges */}
            <div className="flex gap-2 items-start justify-between">
                <div className="flex-1 min-w-0">
                    <h3 className="text-sm font-black text-slate-800 leading-tight group-hover:text-indigo-600 transition-colors truncate">
                        {item.customer_name}
                    </h3>
                </div>
                
                <div className="flex items-center justify-end gap-1.5 shrink-0">
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
            
            {/* Baris Ketiga: Action Buttons */}
            <div className="pt-3 border-t border-slate-100 flex gap-2">
                <button onClick={() => onOpenDetail(item)} className="flex-1 py-2 flex items-center justify-center gap-1.5 rounded-xl text-indigo-600 bg-indigo-50 hover:bg-indigo-100 font-bold text-[11px] uppercase tracking-wider transition-colors border border-indigo-100">
                    <InformationCircleIcon className="w-4 h-4" /> Detail
                </button>
                {isApproved ? (
                    <button onClick={() => onOpenSkb(item)} className="flex-1 py-2 flex items-center justify-center gap-1.5 rounded-xl text-emerald-600 bg-emerald-50 hover:bg-emerald-100 font-bold text-[11px] uppercase tracking-wider transition-colors border border-emerald-100">
                        <ShieldCheckIcon className="w-4 h-4" /> Lihat SKB
                    </button>
                ) : (
                    <button onClick={() => onOpenSkb(item)} className="flex-1 py-2 flex items-center justify-center gap-1.5 rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 font-bold text-[11px] uppercase tracking-wider shadow-sm shadow-indigo-200 transition-colors">
                        <ClipboardDocumentCheckIcon className="w-4 h-4" /> Form SKB
                    </button>
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
