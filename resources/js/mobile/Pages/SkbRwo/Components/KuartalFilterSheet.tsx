import React, { useState, useEffect } from 'react';
import { XMarkIcon, ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline';

interface KuartalFilterSheetProps {
    isOpen: boolean;
    onClose: () => void;
    filterKuartal: string;
    setFilterKuartal: (val: string) => void;
    filterDistributor: string;
    setFilterDistributor: (val: string) => void;
    filterReward: string;
    setFilterReward: (val: string) => void;
    distributors?: { code: string; name: string }[];
}

export default function KuartalFilterSheet({ 
    isOpen, onClose, 
    filterKuartal, setFilterKuartal,
    filterDistributor, setFilterDistributor,
    filterReward, setFilterReward,
    distributors = [],
}: KuartalFilterSheetProps) {
    // Local state for applying filters only when "Terapkan" is clicked
    const [localKuartal, setLocalKuartal] = useState(filterKuartal);
    const [localReward, setLocalReward] = useState(filterReward);
    const [localDistributor, setLocalDistributor] = useState(filterDistributor);

    const safeLocalDistributor = String(localDistributor || '');

    // Sync local state when modal opens
    useEffect(() => {
        if (isOpen) {
            setLocalKuartal(filterKuartal);
            setLocalReward(filterReward);
            setLocalDistributor(filterDistributor === 'Semua' ? '' : filterDistributor);
        }
    }, [isOpen, filterKuartal, filterReward, filterDistributor]);

    if (!isOpen) return null;

    const handleApply = () => {
        setFilterKuartal(localKuartal);
        setFilterReward(localReward);
        setFilterDistributor(safeLocalDistributor.trim() === '' ? 'Semua' : safeLocalDistributor);
        onClose();
    };

    return (
        <div className="fixed inset-0 z-50 flex flex-col justify-end">
            <div 
                className="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onClick={onClose}
            ></div>
            <div className="relative bg-white rounded-t-3xl shadow-xl w-full max-h-[90vh] flex flex-col animate-slide-up">
                {/* Header */}
                <div className="flex items-center justify-between p-4 border-b border-slate-100">
                    <h2 className="text-[15px] font-black text-slate-800 tracking-wide">Filter Data</h2>
                    <button 
                        onClick={onClose}
                        className="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors"
                    >
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>
                
                {/* Content */}
                <div className="flex-1 overflow-y-auto p-5 flex flex-col gap-5">
                    
                    {/* Filter Kuartal */}
                    <div className="flex flex-col gap-2">
                        <label className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kuartal</label>
                        <div className="relative">
                            <select 
                                value={localKuartal}
                                onChange={(e) => setLocalKuartal(e.target.value)}
                                className="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl p-3 pr-10 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                            >
                                <option value="Semua">Semua Kuartal</option>
                                <option value="1">Kuartal 1 (Q1)</option>
                                <option value="2">Kuartal 2 (Q2)</option>
                                <option value="3">Kuartal 3 (Q3)</option>
                                <option value="4">Kuartal 4 (Q4)</option>
                            </select>
                            <ChevronDownIcon className="w-5 h-5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                        </div>
                    </div>

                    {/* Filter Reward */}
                    <div className="flex flex-col gap-2">
                        <label className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Percentage Reward</label>
                        <div className="relative">
                            <select 
                                value={localReward}
                                onChange={(e) => setLocalReward(e.target.value)}
                                className="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl p-3 pr-10 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                            >
                                <option value="Semua">Semua Reward</option>
                                <option value="1.5%">1.5%</option>
                                <option value="2%">2.0%</option>
                                <option value="2.5%">2.5%</option>
                            </select>
                            <ChevronDownIcon className="w-5 h-5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                        </div>
                    </div>

                    {/* Filter Distributor / Cabang - Input Pencarian Langsung */}
                    <div className="flex flex-col gap-2">
                        <label className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pencarian Distributor / Cabang</label>
                        <div className="relative">
                            <MagnifyingGlassIcon className="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input 
                                type="text" 
                                placeholder="Ketik nama distributor..." 
                                value={safeLocalDistributor}
                                onChange={(e) => setLocalDistributor(e.target.value)}
                                className="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl py-3 pl-10 pr-3 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                            />
                        </div>
                        <p className="text-[9px] text-slate-400 mt-1">*Ketik sebagian atau nama lengkap distributor</p>
                        
                        {/* Auto-suggest list */}
                        {safeLocalDistributor.trim().length > 0 && safeLocalDistributor !== 'Semua' && (
                            <div className="mt-1 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col max-h-48 overflow-y-auto">
                                {(distributors?.filter(d => String(d.name || '').toLowerCase().includes(safeLocalDistributor.toLowerCase())) || []).length > 0 ? (
                                    (distributors?.filter(d => String(d.name || '').toLowerCase().includes(safeLocalDistributor.toLowerCase())) || []).map(d => (
                                        <div 
                                            key={d.code}
                                            onClick={() => setLocalDistributor(d.name)}
                                            className="p-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0"
                                        >
                                            {d.name}
                                        </div>
                                    ))
                                ) : (
                                    <div className="p-3 text-xs text-slate-400 text-center">Distributor tidak ditemukan</div>
                                )}
                            </div>
                        )}
                    </div>

                </div>

                {/* Footer / Apply Button */}
                <div className="p-4 border-t border-slate-100">
                    <button 
                        onClick={handleApply}
                        className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[13px] uppercase tracking-wider py-3.5 rounded-xl transition-colors shadow-sm shadow-indigo-200"
                    >
                        Terapkan Filter
                    </button>
                </div>
                
                {/* Safe Area Padding */}
                <div style={{ height: 'env(safe-area-inset-bottom, 0px)' }} />
            </div>
        </div>
    );
}
