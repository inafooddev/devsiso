import React from 'react';
import { Head, router } from '@inertiajs/react';
import MobileLayout from '../Layouts/MobileLayout';
import Card from '../Components/UI/Card';
import UserInfo from '../Components/UserInfo';
import Button from '../Components/UI/Button';

export default function Profile() {
    const dummyUser = { 
        name: "Budi Santoso", 
        email: "budi.santoso@example.com", 
        role: "Sales Supervisor" 
    };

    return (
        <MobileLayout user={dummyUser} title="Profil Saya">
            <Head title="Profil" />
            
            <div className="space-y-6">
                <UserInfo user={dummyUser} />

                <section>
                    <h2 className="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3 px-1">Pengaturan</h2>
                    <Card padding="none" className="overflow-hidden">
                        <ul className="divide-y divide-gray-100">
                            {[
                                { name: 'Pengaturan Akun', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
                                { name: 'Notifikasi', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
                                { name: 'Keamanan & Password', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
                                { name: 'Pusat Bantuan', icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
                            ].map((item, idx) => (
                                <li key={idx} className="flex justify-between items-center p-4 hover:bg-indigo-50 cursor-pointer active:bg-indigo-100 transition-colors group">
                                    <div className="flex items-center space-x-3">
                                        <div className="w-8 h-8 rounded-full bg-gray-50 group-hover:bg-indigo-100 flex items-center justify-center text-gray-400 group-hover:text-indigo-600 transition-colors">
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={item.icon} /></svg>
                                        </div>
                                        <span className="font-semibold text-gray-700 text-sm group-hover:text-indigo-900 transition-colors">{item.name}</span>
                                    </div>
                                    <svg className="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
                                </li>
                            ))}
                        </ul>
                    </Card>
                </section>

                <div className="pt-2">
                    <Button fullWidth variant="danger" size="lg" onClick={() => router.get('/mobile/login')} className="shadow-sm">
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Keluar (Logout)
                    </Button>
                </div>
            </div>
        </MobileLayout>
    );
}
