import React, { useState, useRef } from 'react';
import { ArrowPathIcon } from '@heroicons/react/24/outline';

interface PullToRefreshProps {
    onRefresh: () => Promise<void>;
    children: React.ReactNode;
}

export default function PullToRefresh({ onRefresh, children }: PullToRefreshProps) {
    const [pullDistance, setPullDistance] = useState(0);
    const [isRefreshing, setIsRefreshing] = useState(false);
    const startYRef = useRef<number>(0);
    const MAX_PULL = 80;

    const handleTouchStart = (e: React.TouchEvent) => {
        if (window.scrollY <= 0) {
            startYRef.current = e.touches[0].clientY;
        } else {
            startYRef.current = 0;
        }
    };

    const handleTouchMove = (e: React.TouchEvent) => {
        if (startYRef.current === 0 || isRefreshing) return;
        
        const y = e.touches[0].clientY;
        const delta = y - startYRef.current;
        
        // Only pull if we are at the top and pulling down
        if (delta > 0 && window.scrollY <= 0) {
            // Dampen the pull
            setPullDistance(Math.min(delta * 0.4, MAX_PULL));
        }
    };

    const handleTouchEnd = async () => {
        if (pullDistance > MAX_PULL * 0.75 && !isRefreshing) {
            setIsRefreshing(true);
            setPullDistance(50); // Hold at 50px while refreshing
            try {
                await onRefresh();
            } finally {
                setIsRefreshing(false);
                setPullDistance(0);
            }
        } else {
            setPullDistance(0);
        }
        startYRef.current = 0;
    };

    return (
        <div 
            onTouchStart={handleTouchStart} 
            onTouchMove={handleTouchMove} 
            onTouchEnd={handleTouchEnd}
            className="w-full relative min-h-screen"
        >
            <div 
                className="absolute top-0 left-0 right-0 flex justify-center items-end overflow-hidden transition-all duration-200 z-10 pointer-events-none"
                style={{ height: `${pullDistance}px` }}
            >
                <div className="mb-3 bg-white shadow-md rounded-full p-1.5 flex items-center justify-center">
                    <ArrowPathIcon 
                        className={`w-5 h-5 text-indigo-600 ${isRefreshing ? 'animate-spin' : ''}`} 
                        style={{ transform: `rotate(${pullDistance * 4}deg)` }} 
                    />
                </div>
            </div>
            <div 
                className="w-full transition-transform duration-200 bg-gray-50 min-h-screen"
                style={{ transform: `translateY(${pullDistance}px)` }}
            >
                {children}
            </div>
        </div>
    );
}
