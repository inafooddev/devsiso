import React from 'react';
import {
    MapPinIcon, IdentificationIcon,
} from '@heroicons/react/24/outline';

export default function StoreCard({ item, index }) {
    return (
        <div className="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex gap-4 animate-fade-in relative overflow-hidden group">
            {/* Numbering Circle */}
            <div className="flex-shrink-0 flex items-center mt-1">
                <div className="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center border border-indigo-100">
                    <span className="text-xs font-black text-indigo-600">{index + 1}</span>
                </div>
            </div>

            {/* Konten Utama */}
            <div className="flex flex-col gap-3 flex-1 min-w-0">
                {/* Baris Pertama: Nama Toko */}
                <div className="flex gap-2 items-start justify-between">
                <div className="flex-1 min-w-0">
                    <h3 className="text-sm font-black text-slate-800 leading-tight group-hover:text-indigo-600 transition-colors truncate">
                        {item.customer_name}
                    </h3>
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
                        <p className="text-[11px] leading-snug truncate">{item.address}</p>
                    </div>
                </div>
            </div>
        </div>
    );
}
