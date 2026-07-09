import React from 'react';
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/react/24/outline';

interface SearchBarProps {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    onSubmit?: () => void;
    onClear?: () => void;
}

export default function SearchBar({ value, onChange, placeholder = "Cari...", onSubmit, onClear }: SearchBarProps) {
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (onSubmit) {
            onSubmit();
        }
        if (document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
        }
    };

    return (
        <form onSubmit={handleSubmit} className="relative flex-1 flex items-center w-full group">
            {/* Search Icon */}
            <div className="absolute left-3.5 text-slate-400 group-focus-within:text-indigo-500 transition-colors pointer-events-none">
                <MagnifyingGlassIcon className="w-5 h-5" />
            </div>
            
            {/* Input Field */}
            <input
                type="search"
                value={value}
                onChange={(e) => onChange(e.target.value)}
                placeholder={placeholder}
                className="block w-full pl-11 pr-10 py-3 text-[15px] bg-white border border-slate-200/70 shadow-[0_4px_16px_rgb(0,0,0,0.04)] rounded-xl text-slate-800 placeholder-slate-400 focus:border-indigo-300 focus:ring-4 focus:ring-indigo-500/15 transition-all outline-none"
                style={{ WebkitAppearance: 'none' }} // removes default webkit search cancel button
            />
            
            {/* Clear Button */}
            <div className={`absolute right-2 transition-all duration-200 ${value ? 'opacity-100 scale-100' : 'opacity-0 scale-90 pointer-events-none'}`}>
                <button
                    type="button"
                    onClick={() => {
                        if (onClear) {
                            onClear();
                        } else {
                            onChange('');
                            if (onSubmit) onSubmit();
                        }
                    }}
                    className="w-7 h-7 flex items-center justify-center bg-slate-200 text-slate-500 hover:bg-slate-300 hover:text-slate-700 rounded-full transition-colors active:scale-95"
                >
                    <XMarkIcon className="w-4 h-4 stroke-[2.5]" />
                </button>
            </div>
        </form>
    );
}
