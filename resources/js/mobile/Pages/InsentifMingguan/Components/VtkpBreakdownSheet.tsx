import React, { useEffect } from 'react';
import { VtkpDetailItem } from '../types';
import { formatRupiah, formatNumber } from '../constants';
import { XMarkIcon, SparklesIcon } from '@heroicons/react/24/solid';

interface VtkpBreakdownSheetProps {
    isOpen: boolean;
    onClose: () => void;
    salesmanName: string;
    details: VtkpDetailItem[];
    totalInsentif: number;
}

export default function VtkpBreakdownSheet({
    isOpen,
    onClose,
    salesmanName,
    details = [],
    totalInsentif,
}: VtkpBreakdownSheetProps) {
    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    if (!isOpen) return null;

    return (
        <div className="fixed top-16 md:top-20 inset-x-0 bottom-0 z-[110] flex flex-col justify-end">
            {/* Clickable Backdrop */}
            <div
                className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                onClick={onClose}
            />

            {/* Sheet Content - Flush with Header */}
            <div className="relative z-10 w-full max-w-2xl md:max-w-4xl mx-auto bg-white h-full shadow-2xl overflow-hidden flex flex-col animate-slide-up border-x border-gray-200/80">
                {/* Header */}
                <div className="px-5 py-3.5 border-b border-gray-200/80 flex items-center justify-between bg-purple-50/70 shrink-0">
                    <div className="flex items-center gap-2.5 min-w-0">
                        <div className="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0">
                            <SparklesIcon className="w-4 h-4" />
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-sm font-bold text-gray-900 truncate">Rincian VTKP Produk Fokus</h3>
                            <p className="text-[11px] text-gray-500 font-medium truncate">{salesmanName}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-700 shadow-sm transition-colors shrink-0"
                    >
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 p-4 overflow-y-auto space-y-3 divide-y divide-gray-100">
                    {details.length === 0 ? (
                        <p className="text-center text-xs text-gray-400 py-8">
                            Tidak ada rincian produk fokus untuk wilayah ini.
                        </p>
                    ) : (
                        details.map((item, index) => (
                            <div key={index} className="pt-3 first:pt-0">
                                <div className="flex items-center justify-between mb-1.5">
                                    <span className="font-bold text-gray-900 text-xs">{item.nama_header}</span>
                                    <span className="font-extrabold text-xs text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-100">
                                        {formatRupiah(item.insentif)}
                                    </span>
                                </div>
                                <div className="grid grid-cols-3 gap-2 text-[11px] bg-gray-50 p-2.5 rounded-xl text-center border border-gray-100">
                                    <div>
                                        <span className="text-gray-500 block">Target Qty</span>
                                        <span className="font-bold text-gray-800">{formatNumber(item.target)} Ctn</span>
                                    </div>
                                    <div>
                                        <span className="text-gray-500 block">Realisasi</span>
                                        <span className="font-bold text-gray-800">{formatNumber(item.real)} Ctn</span>
                                    </div>
                                    <div>
                                        <span className="text-gray-500 block">Growth</span>
                                        <span className={`font-bold ${item.growth >= 0 ? 'text-emerald-600' : 'text-rose-600'}`}>
                                            {item.growth >= 0 ? `+${formatNumber(item.growth)}%` : `${formatNumber(item.growth)}%`}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {/* Footer Total */}
                <div className="p-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between shrink-0">
                    <div>
                        <span className="text-[11px] text-gray-500 block">Total Insentif VTKP</span>
                        <span className="text-base font-extrabold text-purple-700">
                            {formatRupiah(totalInsentif)}
                        </span>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="px-5 py-2 text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm transition-all"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    );
}
