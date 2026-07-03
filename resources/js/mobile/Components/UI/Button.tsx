import React, { ButtonHTMLAttributes } from 'react';

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
    size?: 'sm' | 'md' | 'lg';
    fullWidth?: boolean;
    isLoading?: boolean;
}

export default function Button({
    children,
    variant = 'primary',
    size = 'md',
    fullWidth = false,
    isLoading = false,
    className = '',
    disabled,
    ...props
}: ButtonProps) {
    // Base styles for mobile feel: rounded corners, smooth transition, scale down on tap
    const baseStyle = "inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 active:scale-95 disabled:opacity-70 disabled:pointer-events-none";
    
    const variants = {
        primary: "bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-200",
        secondary: "bg-indigo-50 text-indigo-700 hover:bg-indigo-100",
        outline: "border-2 border-indigo-200 text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50",
        ghost: "text-gray-600 hover:bg-gray-100 active:bg-gray-200",
        danger: "bg-red-50 text-red-600 hover:bg-red-100"
    };

    const sizes = {
        sm: "px-3 py-1.5 text-sm",
        md: "px-5 py-2.5 text-base",
        lg: "px-6 py-3.5 text-lg"
    };

    const widthClass = fullWidth ? "w-full" : "";
    const isLoadingClass = isLoading ? "opacity-75 cursor-wait" : "";

    return (
        <button
            className={`${baseStyle} ${variants[variant]} ${sizes[size]} ${widthClass} ${isLoadingClass} ${className}`}
            disabled={disabled || isLoading}
            {...props}
        >
            {isLoading && (
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            )}
            {children}
        </button>
    );
}
