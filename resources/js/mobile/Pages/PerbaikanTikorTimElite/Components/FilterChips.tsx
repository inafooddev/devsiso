import React from 'react';

interface FilterChipsProps {
    selectedStatusFilter: string;
    setSelectedStatusFilter: (status: string) => void;
}

export default function FilterChips({ selectedStatusFilter, setSelectedStatusFilter }: FilterChipsProps) {
    return (
        <div className="flex items-center justify-center gap-2 overflow-x-auto pb-1 mt-2 hide-scrollbar">
            <button 
                onClick={() => setSelectedStatusFilter('')} 
                className={`shrink-0 px-3 py-1.5 rounded-sm text-[0.7rem] font-bold uppercase tracking-wider transition-colors ${!selectedStatusFilter ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'}`}
            >
                Semua
            </button>
            <button 
                onClick={() => setSelectedStatusFilter('approved')} 
                className={`shrink-0 px-3 py-1.5 rounded-sm text-[0.7rem] font-bold uppercase tracking-wider transition-colors ${selectedStatusFilter === 'approved' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-emerald-100'}`}
            >
                Approved
            </button>
            <button 
                onClick={() => setSelectedStatusFilter('rejected')} 
                className={`shrink-0 px-3 py-1.5 rounded-sm text-[0.7rem] font-bold uppercase tracking-wider transition-colors ${selectedStatusFilter === 'rejected' ? 'bg-rose-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-rose-100'}`}
            >
                Reject
            </button>
            <button 
                onClick={() => setSelectedStatusFilter('pending')} 
                className={`shrink-0 px-3 py-1.5 rounded-sm text-[0.7rem] font-bold uppercase tracking-wider transition-colors ${selectedStatusFilter === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-amber-100'}`}
            >
                Pending
            </button>
        </div>
    );
}
