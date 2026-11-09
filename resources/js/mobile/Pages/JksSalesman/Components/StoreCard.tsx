import React from 'react';
import { JksItem, DAY_NAMES } from '../constants';
import {
    MapPinIcon,
    UserIcon,
    CalendarIcon,
    PencilSquareIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';

interface StoreCardProps {
    item: JksItem;
    onEdit: (item: JksItem) => void;
    onDetail: (item: JksItem) => void;
}

export default function StoreCard({ item, onEdit, onDetail }: StoreCardProps) {
    const hasCoordinates = Boolean(
        item.latitude &&
        item.longitude &&
        !isNaN(Number(item.latitude)) &&
        !isNaN(Number(item.longitude)) &&
        Number(item.latitude) !== 0 &&
        Number(item.longitude) !== 0
    );

    // Dapatkan daftar hari terjadwal
    const scheduledDays: string[] = [];
    (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7'] as const).forEach((h) => {
        if (item[h] === 'Y') {
            scheduledDays.push(DAY_NAMES[h] || h);
        }
    });

    // Dapatkan daftar minggu terjadwal
    const scheduledWeeks: string[] = [];
    if (item.w1 === 'Y') scheduledWeeks.push('M1');
    if (item.w2 === 'Y') scheduledWeeks.push('M2');
    if (item.w3 === 'Y') scheduledWeeks.push('M3');
    if (item.w4 === 'Y') scheduledWeeks.push('M4');

    const isNonRoute = scheduledDays.length === 0 || scheduledWeeks.length === 0;

    return (
        <div className="bg-white rounded-3xl p-4 md:p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-slate-100 hover:border-indigo-200 transition-all duration-200 flex flex-col gap-3 group">
            {/* 1. Header: Store Name, Pilar & GPS Badge */}
            <div className="flex items-start justify-between gap-2">
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-1.5 flex-wrap mb-1">
                        {item.pilar && (
                            <span className="px-2 py-0.5 rounded-md text-xs font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {item.pilar}
                            </span>
                        )}
                        {hasCoordinates ? (
                            <span className="px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center gap-1">
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                                GPS
                            </span>
                        ) : (
                            <span className="px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-600 border border-rose-100 flex items-center gap-1">
                                <ExclamationTriangleIcon className="w-3 h-3" />
                                Non GPS
                            </span>
                        )}
                    </div>
                    <h3 className="text-sm md:text-base font-black text-slate-900 leading-snug group-hover:text-indigo-600 transition-colors truncate" title={item.customer_name}>
                        {item.customer_name}
                    </h3>
                    <p className="text-xs font-bold text-slate-400 font-mono mt-0.5">
                        {item.customer_code || item.uniq_kode}
                    </p>
                </div>
            </div>

            {/* 2. Address Line */}
            <div className="flex items-start gap-1.5 text-xs text-slate-500">
                <MapPinIcon className="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                <p className="line-clamp-1 leading-relaxed text-xs">
                    {item.customer_address || '-'}
                    {(item.kecamatan || item.desa) && (
                        <span className="text-slate-400 font-medium">
                            {' '}({[item.desa, item.kecamatan].filter(Boolean).join(', ')})
                        </span>
                    )}
                </p>
            </div>

            {/* 3. Salesman Line */}
            <div className="flex items-center gap-1.5 text-xs text-slate-600 bg-slate-50 rounded-xl px-3 py-1.5 border border-slate-100">
                <UserIcon className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                <span className="text-xs font-medium text-slate-400">Salesman:</span>
                <span className="text-xs font-bold text-slate-800 truncate" title={item.salesman_name}>
                    {item.salesman_name} ({item.salesman_code})
                </span>
            </div>

            {/* 4. Jadwal Box (Paling Menonjol untuk Cek Jadwal) */}
            <div className={`p-3 rounded-2xl border flex items-center justify-between gap-2 ${
                isNonRoute
                    ? 'bg-amber-50/70 border-amber-200 text-amber-900'
                    : 'bg-indigo-50/50 border-indigo-100 text-indigo-900'
            }`}>
                <div className="flex items-center gap-2">
                    <CalendarIcon className={`w-5 h-5 shrink-0 ${isNonRoute ? 'text-amber-500' : 'text-indigo-600'}`} />
                    <div>
                        <span className="text-xs font-bold uppercase tracking-wider block opacity-70">
                            Jadwal Kunjungan
                        </span>
                        <span className="text-xs font-black block leading-tight">
                            {scheduledDays.length > 0 ? scheduledDays.join(', ') : 'Belum Ada Hari (Non-Rute)'}
                        </span>
                    </div>
                </div>

                <div className="text-right">
                    <span className="text-xs font-bold uppercase tracking-wider block opacity-70">
                        Minggu
                    </span>
                    <span className="text-xs font-black block leading-tight">
                        {scheduledWeeks.length > 0 ? scheduledWeeks.join(' / ') : 'Non-Rute'}
                    </span>
                </div>
            </div>

            {/* 5. Dua Tombol Utama: EDIT dan DETAIL */}
            <div className="flex items-center gap-2 pt-1">
                {/* Button Edit */}
                <button
                    type="button"
                    onClick={() => onEdit(item)}
                    className="flex-1 inline-flex items-center justify-center gap-1.5 h-10 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider shadow-sm shadow-indigo-200 active:scale-[0.98] transition-all"
                >
                    <PencilSquareIcon className="w-4 h-4 stroke-[2.2]" />
                    Edit Jadwal
                </button>

                {/* Button Detail */}
                <button
                    type="button"
                    onClick={() => onDetail(item)}
                    className="flex-1 inline-flex items-center justify-center gap-1.5 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase tracking-wider active:scale-[0.98] transition-all border border-slate-200"
                >
                    <InformationCircleIcon className="w-4 h-4 stroke-[2.2]" />
                    Detail
                </button>
            </div>
        </div>
    );
}
