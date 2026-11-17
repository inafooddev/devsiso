import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { getMonthName } from '../constants';
import { XMarkIcon, FunnelIcon, CalendarDaysIcon, MapPinIcon } from '@heroicons/react/24/solid';

interface FilterSheetProps {
    isOpen: boolean;
    onClose: () => void;
    currentBulan: string;
    listBulan: string[];
    currentRegion: string;
    listRegions: string[];
    currentArea: string;
    listAreas: string[];
    accessLevel: string;
}

export default function FilterSheet({
    isOpen,
    onClose,
    currentBulan,
    listBulan = [],
    currentRegion,
    listRegions = [],
    currentArea,
    listAreas = [],
    accessLevel,
}: FilterSheetProps) {
    const [selectedBulan, setSelectedBulan] = useState(currentBulan);
    const [selectedRegion, setSelectedRegion] = useState(currentRegion);
    const [selectedArea, setSelectedArea] = useState(currentArea);

    useEffect(() => {
        setSelectedBulan(currentBulan);
        setSelectedRegion(currentRegion);
        setSelectedArea(currentArea);
    }, [currentBulan, currentRegion, currentArea, isOpen]);

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

    const handleApply = () => {
        router.get(
            '/mobile/insentif',
            {
                bulan: selectedBulan,
                region: selectedRegion,
                area: selectedArea,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => onClose(),
            }
        );
    };

    return (
        <div className="fixed inset-0 z-50 flex flex-col justify-end">
            {/* Clickable Backdrop */}
            <div
                className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                onClick={onClose}
            />

            {/* Sheet Box */}
            <div className="relative z-10 w-full max-w-2xl md:max-w-4xl mx-auto bg-white rounded-t-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-slide-up border-x border-gray-200/80">
                {/* Drag Handle (Mobile) */}
                <div className="pt-2.5 pb-1 flex justify-center bg-slate-50" onClick={onClose}>
                    <div className="w-10 h-1.5 bg-gray-300 rounded-full" />
                </div>

                {/* Header */}
                <div className="px-5 py-3.5 border-b border-gray-200/80 flex items-center justify-between bg-slate-50 shrink-0">
                    <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                            <FunnelIcon className="w-4 h-4" />
                        </div>
                        <div>
                            <h3 className="text-sm font-bold text-gray-900">Filter Monitoring Insentif</h3>
                            <p className="text-[11px] text-gray-500 font-medium">Pilih periode dan wilayah</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-700 shadow-sm transition-colors"
                    >
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>

                {/* Body Form */}
                <div className="p-5 overflow-y-auto space-y-4 text-xs">
                    {/* Pilih Bulan */}
                    <div>
                        <label className="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <CalendarDaysIcon className="w-4 h-4 text-indigo-500" />
                            Pilih Periode Bulan
                        </label>
                        <div className="grid grid-cols-2 gap-2">
                            {listBulan.map((b) => (
                                <button
                                    key={b}
                                    type="button"
                                    onClick={() => setSelectedBulan(b)}
                                    className={`py-2 px-3 rounded-xl border text-center font-bold transition-all text-xs ${
                                        selectedBulan === b
                                            ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm'
                                            : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100'
                                    }`}
                                >
                                    {getMonthName(b)}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Pilih Region */}
                    {listRegions.length > 0 && (
                        <div>
                            <label className="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <MapPinIcon className="w-4 h-4 text-indigo-500" />
                                Pilih Region
                            </label>
                            <select
                                value={selectedRegion}
                                onChange={(e) => {
                                    setSelectedRegion(e.target.value);
                                    setSelectedArea('');
                                }}
                                disabled={['region', 'area'].includes(accessLevel) && listRegions.length === 1}
                                className="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            >
                                {listRegions.map((r) => (
                                    <option key={r} value={r}>
                                        {r}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}

                    {/* Pilih Area */}
                    {listAreas.length > 0 && selectedRegion === currentRegion && (
                        <div>
                            <label className="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <MapPinIcon className="w-4 h-4 text-emerald-500" />
                                Pilih Area (Opsional)
                            </label>
                            <select
                                value={selectedArea}
                                onChange={(e) => setSelectedArea(e.target.value)}
                                disabled={accessLevel === 'area' && listAreas.length === 1}
                                className="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            >
                                <option value="">-- Semua Area --</option>
                                {listAreas.map((a) => (
                                    <option key={a} value={a}>
                                        {a}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>

                {/* Footer Actions */}
                <div className="p-4 border-t border-gray-100 bg-gray-50 flex items-center gap-2">
                    <button
                        type="button"
                        onClick={onClose}
                        className="flex-1 py-2.5 text-xs font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-100 rounded-xl transition-all"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        onClick={handleApply}
                        className="flex-1 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition-all"
                    >
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    );
}
