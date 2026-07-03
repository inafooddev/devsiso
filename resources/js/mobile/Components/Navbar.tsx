import React from 'react';
import { Link } from '@inertiajs/react';
import UserInfo from './UserInfo';

interface NavbarProps {
    user?: any;
    title?: string;
}

export default function Navbar({ user, title }: NavbarProps) {
    return (
        <nav className="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200 shadow-sm transition-all duration-300">
            <div className="w-full max-w-2xl mx-auto px-4 md:px-6 h-16 md:h-18 flex items-center justify-between transition-all duration-300">
                <div className="flex-shrink-0 flex items-center">
                    <Link href="/">
                        <div className="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-md shadow-indigo-200">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                        </div>
                    </Link>
                </div>
                
                {title && (
                    <div className="font-bold text-gray-800 text-lg truncate px-4 flex-1 text-center">
                        {title}
                    </div>
                )}
                
                <div className="flex items-center">
                    {user ? (
                        <UserInfo user={user} compact={true} />
                    ) : (
                        <Link href="/login" className="text-sm font-semibold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full hover:bg-indigo-100 transition-colors">
                            Login
                        </Link>
                    )}
                </div>
            </div>
        </nav>
    );
}
