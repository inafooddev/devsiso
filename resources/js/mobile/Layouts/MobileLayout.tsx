import React, { ReactNode } from 'react';
import Navbar from '../Components/Navbar';
import { User } from '../Components/UserInfo';

interface MobileLayoutProps {
    user?: User;
    title?: string;
    children: ReactNode;
    bottomNavigation?: ReactNode;
    backUrl?: string;
}

export default function MobileLayout({ user, title, children, bottomNavigation, backUrl }: MobileLayoutProps) {
    return (
        <div className="min-h-screen bg-gray-50 flex flex-col font-sans">
            <Navbar user={user} title={title} backUrl={backUrl} />
            
            {/* Main Content Area */}
            {/* pt-16 (md:pt-20) accounts for Navbar height, pb-24 (md:pb-28) accounts for BottomMenu height + extra padding */}
            <main className={`flex-1 w-full max-w-2xl mx-auto pt-16 md:pt-20 ${bottomNavigation ? 'pb-24 md:pb-28' : 'pb-6 md:pb-10'} transition-all duration-300 relative`}>
                {children}
            </main>
            
            {bottomNavigation}
        </div>
    );
}
