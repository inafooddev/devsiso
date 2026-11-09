import React, { useState, useEffect } from 'react';
import { DistributorOption, SalesmanOption, WeekFilterType } from '../constants';
import { XMarkIcon, AdjustmentsHorizontalIcon, CheckIcon } from '@heroicons/react/24/outline';

interface FilterSheetProps {
    isOpen: boolean;
    onClose: () => void;
    distributors: DistributorOption[];
    salesmans: SalesmanOption[];
    currentDistributor: string;
    currentSalesman: string;
    currentWeekFilter: WeekFilterType;
    currentRouteFilter: 'all' | 'rute' | 'non_rute';
    onApply: (distributor: string, salesman: string, week: WeekFilterType, route: 'all' | 'rute' | 'non_rute') => void;
}

export default function FilterSheet({
    isOpen,
    onClose,
    distributors,
    salesmans,
    currentDistributor,
    currentSalesman,
    currentWeekFilter,
    currentRouteFilter,
    onApply,
}: FilterSheetProps) {
    const [localDistributor, setLocalDistributor] = useState(currentDistributor);
    const [localSalesman, setLocalSalesman] = useState(currentSalesman);
    const [localWeek, setLocalWeek] = useState<WeekFilterType>(currentWeekFilter);
    const [localRoute, setLocalRoute] = useState<'all' | 'rute' | 'non_rute'>(currentRouteFilter);

    useEffect(() => {
        if (isOpen) {
            setLocalDistributor(currentDistributor);
            setLocalSalesman(currentSalesman);
            setLocalWeek(currentWeekFilter);
            setLocalRoute(currentRouteFilter);
        }
    }, [isOpen, currentDistributor, currentSalesman, currentWeekFilter, currentRouteFilter]);

    if (!isOpen) return null;

    const handleApply = () => {
        onApply(localDistributor, localSalesman, localWeek, localRoute);
        onClose();
    };

    const handleReset = () => {
        setLocalWeek('w1');
        setLocalRoute('all');
        setLocalSalesman('');
    };

    return (
        <div className="fixed inset-0 z-50 flex flex-col justify-end">
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity animate-fade-in"
                onClick={onClose}
            />

            {/* Modal Sheet */}
            <div className="relative bg-white rounded-t-[32px] shadow-2xl w-full max-h-[90vh] flex flex-col animate-slide-up overflow-hidden z-10">
                {/* Header */}
                <div className="flex items-center justify-between p-4 px-5 border-b border-slate-100 bg-slate-50/70">
                    <div className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <AdjustmentsHorizontalIcon className="w-5 h-5" />
                        </div>
                        <h3 className="text-sm font-black text-slate-800 uppercase tracking-wide">
                            Filter Data JKS
                        </h3>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-600 shadow-sm border border-slate-200 transition-colors"
                    >
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>

                {/* Form Controls */}
                <div className="flex-1 overflow-y-auto p-5 space-y-5">
                    {/* Distributor Selector */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            Pilih Distributor
                        </label>
                        <select
                            value={localDistributor}
                            onChange={(e) => {
                                setLocalDistributor(e.target.value);
                                setLocalSalesman(''); // Reset salesman jika ganti distributor
                            }}
                            className="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-xs md:text-sm text-slate-800 font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                        >
                            {distributors.map((d) => (
                                <option key={d.distributor_code} value={d.distributor_code}>
                                    {d.distributor_name} ({d.distributor_code})
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Salesman Selector */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            Salesman (SE)
                        </label>
                        <select
                            value={localSalesman}
                            onChange={(e) => setLocalSalesman(e.target.value)}
                            className="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-xs md:text-sm text-slate-800 font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                        >
                            <option value="">-- Semua Salesman --</option>
                            {salesmans.map((s) => (
                                <option key={s.salesman_code} value={s.salesman_code}>
                                    {s.salesman_name} ({s.salesman_code})
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Siklus Minggu (W1-W4) */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            Siklus Minggu Kunjungan
                        </label>
                        <div className="grid grid-cols-4 gap-2">
                            {[
                                { id: 'w1', label: 'M1' },
                                { id: 'w2', label: 'M2' },
                                { id: 'w3', label: 'M3' },
                                { id: 'w4', label: 'M4' },
                            ].map((w) => {
                                const isSelected = localWeek === w.id;
                                return (
                                    <button
                                        key={w.id}
                                        type="button"
                                        onClick={() => setLocalWeek(w.id as WeekFilterType)}
                                        className={`py-2.5 px-2 rounded-2xl text-xs font-bold transition-all border text-center ${
                                            isSelected
                                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200'
                                                : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                                        }`}
                                    >
                                        {w.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Tipe Status Rute */}
                    <div>
                        <label className="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            Klasifikasi Toko
                        </label>
                        <div className="grid grid-cols-3 gap-2">
                            {[
                                { id: 'all', label: 'Semua Toko' },
                                { id: 'rute', label: 'Rute Aktif' },
                                { id: 'non_rute', label: 'Non-Rute' },
                            ].map((r) => {
                                const isSelected = localRoute === r.id;
                                return (
                                    <button
                                        key={r.id}
                                        type="button"
                                        onClick={() => setLocalRoute(r.id as any)}
                                        className={`py-2.5 px-2 rounded-2xl text-xs font-bold transition-all border text-center ${
                                            isSelected
                                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200'
                                                : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                                        }`}
                                    >
                                        {r.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* Footer Actions */}
                <div className="p-4 border-t border-slate-100 bg-white flex items-center gap-3">
                    <button
                        type="button"
                        onClick={handleReset}
                        className="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98]"
                    >
                        Reset
                    </button>
                    <button
                        type="button"
                        onClick={handleApply}
                        className="flex-1 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-md shadow-indigo-200 active:scale-[0.98] flex items-center justify-center gap-1.5"
                    >
                        <CheckIcon className="w-4 h-4 stroke-[2.5]" />
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    );
}
