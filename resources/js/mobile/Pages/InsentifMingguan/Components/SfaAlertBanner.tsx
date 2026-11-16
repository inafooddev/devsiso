import React from 'react';
import { ExclamationTriangleIcon, CheckCircleIcon } from '@heroicons/react/24/solid';

interface SfaAlertBannerProps {
    sfaPct: number;
    penaltyApplied: boolean;
    pc: number;
    ac: number;
}

export default function SfaAlertBanner({ sfaPct, penaltyApplied, pc, ac }: SfaAlertBannerProps) {
    if (penaltyApplied) {
        return (
            <div className="mx-4 mb-3 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3 shadow-sm">
                <div className="p-1.5 bg-rose-100 rounded-xl text-rose-600 shrink-0 mt-0.5">
                    <ExclamationTriangleIcon className="w-5 h-5" />
                </div>
                <div className="flex-1 text-xs">
                    <div className="flex items-center justify-between">
                        <span className="font-bold text-rose-800">Peringatan Kepatuhan SFA!</span>
                        <span className="font-extrabold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full">
                            {sfaPct}%
                        </span>
                    </div>
                    <p className="text-rose-700 mt-1 leading-relaxed">
                        Kepatuhan SFA Anda di bawah <strong>95%</strong> (Kunjungan AC: {ac} / Rencana PC: {pc}).
                        Seluruh insentif Anda saat ini <strong>dipotong penalti 75%</strong> dan hanya cair 25%.
                    </p>
                    <p className="text-rose-600 mt-1 font-semibold text-[11px]">
                        👉 Tingkatkan kehadiran dan kunjungan SFA hingga minimal 95% sebelum tutup periode!
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="mx-4 mb-3 p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between shadow-sm text-xs">
            <div className="flex items-center gap-2.5">
                <div className="p-1 bg-emerald-100 rounded-lg text-emerald-600">
                    <CheckCircleIcon className="w-4 h-4" />
                </div>
                <div>
                    <span className="font-bold text-emerald-900 block">Kepatuhan SFA Aman</span>
                    <span className="text-emerald-700 text-[11px]">
                        {ac} dari {pc} kunjungan terlaksana ({sfaPct}%)
                    </span>
                </div>
            </div>
            <span className="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg text-[11px]">
                Bebas Penalti
            </span>
        </div>
    );
}
