import React from 'react';
import { Link } from '@inertiajs/react';
import UserInfo from './UserInfo';

interface NavbarProps {
    user?: any;
    title?: string;
    backUrl?: string;
}

export default function Navbar({ user, title, backUrl }: NavbarProps) {
    return (
        <nav className="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200 shadow-sm transition-all duration-300">
            <div className="w-full max-w-2xl mx-auto px-5 md:px-6 h-16 md:h-20 flex items-center justify-between transition-all duration-300">
                {/* Left Side (Back / Profile) */}
                <div className="flex-shrink-0 flex items-center justify-start w-12">
                    {backUrl ? (
                        <Link href={backUrl}>
                            <div className="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-200 transition-colors border border-gray-200 shadow-sm active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                        </Link>
                    ) : (
                        <Link href="/mobile/profile" className="active:scale-95 transition-transform block">
                            {user ? (
                                <UserInfo user={user} compact={true} />
                            ) : (
                                <div className="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center shadow-md shadow-indigo-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
                                    </svg>
                                </div>
                            )}
                        </Link>
                    )}
                </div>
                
                {/* Center (Title) */}
                {title && (
                    <div className="font-bold text-gray-800 text-[17px] truncate px-2 flex-1 text-center">
                        {title}
                    </div>
                )}
                
                {/* Right Side (Logout / Login) */}
                <div className="flex-shrink-0 flex items-center justify-end w-12">
                    {user ? (
                        <Link href="/mobile/logout" method="post" as="button" className="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center text-rose-500 hover:bg-rose-100 transition-colors border border-rose-100 shadow-sm active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </Link>
                    ) : (
                        <Link href="/login" className="text-sm font-semibold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full hover:bg-indigo-100 transition-colors active:scale-95">
                            Login
                        </Link>
                    )}
                </div>
            </div>
        </nav>
    );
}
