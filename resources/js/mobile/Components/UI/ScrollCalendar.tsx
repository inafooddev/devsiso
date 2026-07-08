import React, { useRef, useEffect, useMemo } from 'react';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';

const getDaysInMonth = (date: Date) => {
    const year = date.getFullYear();
    const month = date.getMonth();
    const days = [];
    const dateObj = new Date(year, month, 1);
    while (dateObj.getMonth() === month) {
        days.push(new Date(dateObj));
        dateObj.setDate(dateObj.getDate() + 1);
    }
    return days;
};

interface ScrollCalendarProps {
    selectedDate: Date;
    setSelectedDate: (date: Date) => void;
    markedDates?: string[]; // Array of 'YYYY-MM-DD' strings
}

export default function ScrollCalendar({ selectedDate, setSelectedDate, markedDates = [] }: ScrollCalendarProps) {
    const daysInMonth = useMemo(() => getDaysInMonth(selectedDate), [selectedDate.getFullYear(), selectedDate.getMonth()]);
    const scrollContainerRef = useRef<HTMLDivElement>(null);

    // Optimized map for O(1) lookup
    const markedDatesSet = useMemo(() => new Set(markedDates), [markedDates]);

    useEffect(() => {
        if (scrollContainerRef.current) {
            const activeEl = scrollContainerRef.current.querySelector('#selected-date-btn');
            if (activeEl) {
                setTimeout(() => {
                    activeEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }, 100);
            }
        }
    }, [selectedDate]);

    const handlePrevMonth = () => {
        setSelectedDate(new Date(selectedDate.getFullYear(), selectedDate.getMonth() - 1, 1));
    };

    const handleNextMonth = () => {
        setSelectedDate(new Date(selectedDate.getFullYear(), selectedDate.getMonth() + 1, 1));
    };

    return (
        <div className="px-4 pb-2 animate-fade-in">
            <div className="flex items-center justify-between mb-2">
                <button onClick={handlePrevMonth} className="p-1 rounded-full hover:bg-gray-200">
                    <ArrowLeftIcon className="w-5 h-5 text-gray-600" />
                </button>
                <h3 className="text-sm font-bold text-gray-800">
                    {selectedDate.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}
                </h3>
                <button onClick={handleNextMonth} className="p-1 rounded-full hover:bg-gray-200 rotate-180">
                    <ArrowLeftIcon className="w-5 h-5 text-gray-600" />
                </button>
            </div>
            <div ref={scrollContainerRef} className="flex overflow-x-auto gap-2 pb-2 hide-scrollbar snap-x snap-mandatory">
                {daysInMonth.map((dateObj) => {
                    const date = dateObj.getDate();
                    const monthStr = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const dateStr = String(date).padStart(2, '0');
                    const fullDateStr = `${dateObj.getFullYear()}-${monthStr}-${dateStr}`;

                    const isSelected = date === selectedDate.getDate();
                    const today = new Date();
                    const isToday = today.getDate() === date && today.getMonth() === selectedDate.getMonth() && today.getFullYear() === selectedDate.getFullYear();
                    
                    const hasPlan = markedDatesSet.has(fullDateStr);

                    return (
                        <button
                            key={fullDateStr}
                            id={isSelected ? 'selected-date-btn' : undefined}
                            onClick={() => setSelectedDate(new Date(selectedDate.getFullYear(), selectedDate.getMonth(), date))}
                            className={`snap-center shrink-0 w-12 h-14 rounded-xl flex flex-col items-center justify-center transition-all ${
                                isSelected
                                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
                                    : isToday
                                        ? 'bg-indigo-50 border border-indigo-200 text-indigo-700'
                                        : 'bg-white border border-gray-100 text-gray-600 hover:bg-gray-50'
                            }`}
                        >
                            <span className={`text-[0.65rem] uppercase font-semibold ${isSelected ? 'text-indigo-100' : 'text-gray-400'}`}>
                                {dateObj.toLocaleDateString('id-ID', { weekday: 'short' })}
                            </span>
                            <span className="text-lg font-black leading-tight">{date}</span>
                            {hasPlan && (
                                <span className={`w-1.5 h-1.5 rounded-full mt-0.5 ${isSelected ? 'bg-white' : 'bg-indigo-500'}`} />
                            )}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
