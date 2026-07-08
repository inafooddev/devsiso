import React from 'react';
import { ClipboardDocumentListIcon as ClipboardDocumentListSolid, CheckCircleIcon, XCircleIcon, ClockIcon } from '@heroicons/react/24/solid';

interface SummaryKPIProps {
    totalDiperbaiki: number;
    totalApproved: number;
    totalRejected: number;
    totalPending: number;
}

export default function SummaryKPI({ totalDiperbaiki, totalApproved, totalRejected, totalPending }: SummaryKPIProps) {
    return (
        <div className="grid grid-cols-4 gap-2">
            <div className="bg-white border border-gray-100 rounded-2xl p-2 shadow-sm flex flex-col items-center text-center">
                <div className="w-6 h-6 rounded-full bg-indigo-50 flex items-center justify-center mb-1">
                    <ClipboardDocumentListSolid className="w-3.5 h-3.5 text-indigo-500" />
                </div>
                <p className="text-base font-black text-gray-800 leading-none">{totalDiperbaiki}</p>
                <p className="text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mt-1 truncate w-full">Total</p>
            </div>
            <div className="bg-white border border-gray-100 rounded-2xl p-2 shadow-sm flex flex-col items-center text-center">
                <div className="w-6 h-6 rounded-full bg-emerald-50 flex items-center justify-center mb-1">
                    <CheckCircleIcon className="w-3.5 h-3.5 text-emerald-500" />
                </div>
                <p className="text-base font-black text-gray-800 leading-none">{totalApproved}</p>
                <p className="text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mt-1 truncate w-full">Approved</p>
            </div>
            <div className="bg-white border border-gray-100 rounded-2xl p-2 shadow-sm flex flex-col items-center text-center">
                <div className="w-6 h-6 rounded-full bg-rose-50 flex items-center justify-center mb-1">
                    <XCircleIcon className="w-3.5 h-3.5 text-rose-500" />
                </div>
                <p className="text-base font-black text-gray-800 leading-none">{totalRejected}</p>
                <p className="text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mt-1 truncate w-full">Reject</p>
            </div>
            <div className="bg-white border border-gray-100 rounded-2xl p-2 shadow-sm flex flex-col items-center text-center">
                <div className="w-6 h-6 rounded-full bg-amber-50 flex items-center justify-center mb-1">
                    <ClockIcon className="w-3.5 h-3.5 text-amber-500" />
                </div>
                <p className="text-base font-black text-gray-800 leading-none">{totalPending}</p>
                <p className="text-[0.65rem] font-bold text-gray-500 uppercase tracking-wider mt-1 truncate w-full">Pending</p>
            </div>
        </div>
    );
}
