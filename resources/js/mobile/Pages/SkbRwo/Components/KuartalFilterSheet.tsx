import React from 'react';
import { XMarkIcon, CheckIcon } from '@heroicons/react/24/outline';

interface KuartalFilterSheetProps {
    isOpen: boolean;
    onClose: () => void;
    selectedKuartal: string;
    onSelect: (kuartal: string) => void;
}

export default function KuartalFilterSheet({ isOpen, onClose, selectedKuartal, onSelect }: KuartalFilterSheetProps) {
    if (!isOpen) return null;

    const options = [
        { value: 'Semua', label: 'Semua Kuartal' },
        { value: '1', label: 'Kuartal 1 (Q1)' },
        { value: '2', label: 'Kuartal 2 (Q2)' },
        { value: '3', label: 'Kuartal 3 (Q3)' },
        { value: '4', label: 'Kuartal 4 (Q4)' },
    ];

    return (
        <div className="fixed inset-0 z-50 flex flex-col justify-end">
            <div 
                className="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onClick={onClose}
            ></div>
            <div className="relative bg-white rounded-t-3xl shadow-xl w-full max-h-[85vh] flex flex-col animate-slide-up">
                {/* Header */}
                <div className="flex items-center justify-between p-4 border-b border-slate-100">
                    <h2 className="text-[15px] font-black text-slate-800 tracking-wide">Pilih Kuartal</h2>
                    <button 
                        onClick={onClose}
                        className="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors"
                    >
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>
                
                {/* Content */}
                <div className="flex-1 overflow-y-auto p-4 flex flex-col gap-2">
                    {options.map((opt) => (
                        <button
                            key={opt.value}
                            onClick={() => {
                                onSelect(opt.value);
                                onClose();
                            }}
                            className={`w-full flex items-center justify-between p-4 rounded-xl border transition-all ${
                                selectedKuartal === opt.value
                                    ? 'border-indigo-500 bg-indigo-50/50'
                                    : 'border-slate-200 bg-white hover:bg-slate-50'
                            }`}
                        >
                            <span className={`text-[14px] font-bold ${
                                selectedKuartal === opt.value ? 'text-indigo-700' : 'text-slate-700'
                            }`}>
                                {opt.label}
                            </span>
                            {selectedKuartal === opt.value && (
                                <div className="w-6 h-6 rounded-full bg-indigo-500 text-white flex items-center justify-center shrink-0">
                                    <CheckIcon className="w-4 h-4 stroke-[3]" />
                                </div>
                            )}
                        </button>
                    ))}
                </div>
                
                {/* Safe Area Padding */}
                <div style={{ height: 'env(safe-area-inset-bottom, 0px)' }} />
            </div>
        </div>
    );
}
