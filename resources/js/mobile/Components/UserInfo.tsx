import React from 'react';

export interface User {
    name?: string;
    email?: string;
    avatar?: string;
    role?: string;
}

interface UserInfoProps {
    user?: User;
    compact?: boolean;
}

export default function UserInfo({ user, compact = false }: UserInfoProps) {
    if (!user) return null;

    // Helper to get initials if no avatar is provided
    const getInitials = (name?: string) => {
        return name ? name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'U';
    };

    if (compact) {
        return (
            <div className="flex items-center space-x-2 cursor-pointer transition-transform hover:scale-105 active:scale-95">
                <div className="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm overflow-hidden shadow-sm border-2 border-white ring-2 ring-indigo-50">
                    {user.avatar ? (
                        <img src={user.avatar} alt={user.name} className="w-full h-full object-cover" />
                    ) : (
                        <span>{getInitials(user.name)}</span>
                    )}
                </div>
            </div>
        );
    }

    return (
        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 relative overflow-hidden">
            {/* Background decoration */}
            <div className="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full mix-blend-multiply filter blur-2xl opacity-70 transform translate-x-1/2 -translate-y-1/2"></div>
            
            <div className="w-16 h-16 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex-shrink-0 flex items-center justify-center text-white font-bold text-xl overflow-hidden shadow-md border-4 border-white z-10">
                {user.avatar ? (
                    <img src={user.avatar} alt={user.name} className="w-full h-full object-cover" />
                ) : (
                    <span>{getInitials(user.name)}</span>
                )}
            </div>
            
            <div className="flex-1 z-10">
                <h3 className="text-lg font-bold text-gray-900 leading-tight">{user.name || 'Guest User'}</h3>
                <p className="text-sm text-gray-500 mb-1">{user.email || 'No email provided'}</p>
                {user.role && (
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                        {user.role}
                    </span>
                )}
            </div>
        </div>
    );
}
