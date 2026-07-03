import React from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function BottomMenu() {
    const { url } = usePage();

    // Reusable icons to avoid external dependencies for simplicity
    const icons = {
        Home: (
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        ),
        Explore: (
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        ),
        Menu: (
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        ),
        Profile: (
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        )
    };

    const isActive = (path: string) => url.startsWith(path);

    return (
        <div className="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-100 shadow-[0_-4px_20px_rgba(0,0,0,0.03)] pb-safe transition-all duration-300">
            <div className="w-full max-w-2xl mx-auto flex justify-around items-center h-16 md:h-20 transition-all duration-300">
                <Link 
                    href="/mobile/home" 
                    className={`flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors ${isActive('/mobile/home') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-500'}`}
                >
                    <div className={`${isActive('/mobile/home') ? 'bg-indigo-50' : 'bg-transparent'} p-1.5 rounded-2xl transition-all duration-300`}>
                        {icons.Home}
                    </div>
                    <span className="text-[10px] font-semibold tracking-wide">Home</span>
                </Link>
                
                <Link 
                    href="/mobile/explore" 
                    className={`flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors ${isActive('/mobile/explore') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-500'}`}
                >
                    <div className={`${isActive('/mobile/explore') ? 'bg-indigo-50' : 'bg-transparent'} p-1.5 rounded-2xl transition-all duration-300`}>
                        {icons.Explore}
                    </div>
                    <span className="text-[10px] font-semibold tracking-wide">Explore</span>
                </Link>

                <Link 
                    href="/mobile/menu" 
                    className={`flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors ${isActive('/mobile/menu') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-500'}`}
                >
                    <div className={`${isActive('/mobile/menu') ? 'bg-indigo-50' : 'bg-transparent'} p-1.5 rounded-2xl transition-all duration-300`}>
                        {icons.Menu}
                    </div>
                    <span className="text-[10px] font-semibold tracking-wide">Menu</span>
                </Link>

                <Link 
                    href="/mobile/profile" 
                    className={`flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors ${isActive('/mobile/profile') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-500'}`}
                >
                    <div className={`${isActive('/mobile/profile') ? 'bg-indigo-50' : 'bg-transparent'} p-1.5 rounded-2xl transition-all duration-300`}>
                        {icons.Profile}
                    </div>
                    <span className="text-[10px] font-semibold tracking-wide">Profile</span>
                </Link>
            </div>
        </div>
    );
}
