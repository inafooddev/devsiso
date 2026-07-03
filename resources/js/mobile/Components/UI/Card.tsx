import React, { ReactNode } from 'react';

interface CardProps {
    children: ReactNode;
    className?: string;
    padding?: 'none' | 'sm' | 'md' | 'lg';
    onClick?: () => void;
}

export default function Card({ children, className = '', padding = 'md', onClick }: CardProps) {
    const paddings = {
        none: 'p-0',
        sm: 'p-3',
        md: 'p-5',
        lg: 'p-7'
    };

    // If onClick is provided, make the card interactive (tappable feedback)
    const interactiveClasses = onClick ? 'cursor-pointer hover:shadow-md transition-all active:scale-[0.98]' : '';

    return (
        <div 
            className={`bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden ${paddings[padding]} ${interactiveClasses} ${className}`}
            onClick={onClick}
        >
            {children}
        </div>
    );
}
