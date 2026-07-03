import React from 'react';
import { Head } from '@inertiajs/react';
import MobileLayout from '../Layouts/MobileLayout';
import Card from '../Components/UI/Card';
import UserInfo from '../Components/UserInfo';

export default function Home() {
    const dummyUser = { name: "Budi Santoso", role: "Sales Supervisor" };

    return (
        <MobileLayout user={dummyUser} title="Beranda">
            <Head title="Beranda" />
            
            <div className="space-y-6">
                <UserInfo user={dummyUser} />
                
                <section>
                    <h2 className="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3 px-1">Ringkasan Hari Ini</h2>
                    <div className="grid grid-cols-2 gap-4">
                        <Card padding="sm" className="bg-gradient-to-br from-indigo-50 to-blue-50 border-none relative overflow-hidden">
                            <div className="absolute -right-4 -top-4 opacity-10">
                                <svg className="w-24 h-24 text-indigo-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div className="p-2 relative z-10">
                                <div className="text-indigo-600 mb-2">
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                </div>
                                <h3 className="text-3xl font-black text-gray-900">24</h3>
                                <p className="text-xs text-gray-600 font-semibold mt-1">Kunjungan</p>
                            </div>
                        </Card>
                        <Card padding="sm" className="bg-gradient-to-br from-emerald-50 to-teal-50 border-none relative overflow-hidden">
                            <div className="absolute -right-4 -bottom-4 opacity-10">
                                <svg className="w-24 h-24 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div className="p-2 relative z-10">
                                <div className="text-emerald-600 mb-2">
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <h3 className="text-3xl font-black text-gray-900">12<span className="text-lg">M</span></h3>
                                <p className="text-xs text-gray-600 font-semibold mt-1">Total Sales</p>
                            </div>
                        </Card>
                    </div>
                </section>
                
                <section>
                    <h2 className="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3 px-1">Aktivitas Terakhir</h2>
                    <div className="space-y-3">
                        {[1, 2, 3].map(i => (
                            <Card key={i} padding="sm" onClick={() => {}}>
                                <div className="flex items-center space-x-4">
                                    <div className="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100 shadow-sm">
                                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="font-bold text-sm text-gray-900 leading-tight">Toko Jaya Abadi {i}</h4>
                                        <p className="text-xs text-gray-500 mt-0.5">Dikunjungi pukul 10:3{i} AM</p>
                                    </div>
                                </div>
                            </Card>
                        ))}
                    </div>
                </section>
            </div>
        </MobileLayout>
    );
}
