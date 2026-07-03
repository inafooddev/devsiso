import React, { ReactNode } from 'react';
import Navbar from '../Components/Navbar';
import BottomMenu from '../Components/BottomMenu';
import { User } from '../Components/UserInfo';

interface MobileLayoutProps {
    user?: User;
    title?: string;
    children: ReactNode;
    showBottomMenu?: boolean;
}

export default function MobileLayout({ user, title, children, showBottomMenu = true }: MobileLayoutProps) {
    return (
        <div className="min-h-screen bg-gray-50 flex flex-col font-sans">
            <Navbar user={user} title={title} />
            
            {/* Main Content Area */}
            {/* pt-16 (md:pt-18) accounts for Navbar height, pb-20 (md:pb-24) accounts for BottomMenu height + extra padding */}
            <main className={`flex-1 w-full max-w-2xl mx-auto pt-16 md:pt-20 ${showBottomMenu ? 'pb-24 md:pb-28' : 'pb-6 md:pb-10'} px-4 md:px-6 transition-all duration-300`}>
                <div className="mt-6 md:mt-8 animate-fade-in">
                    {children}
                </div>
            </main>
            
            {showBottomMenu && <BottomMenu />}
        </div>
    );
}
