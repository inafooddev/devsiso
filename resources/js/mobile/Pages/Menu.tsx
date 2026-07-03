import React from 'react';
import { Head, router } from '@inertiajs/react';
import MobileLayout from '../Layouts/MobileLayout';
import Card from '../Components/UI/Card';

export default function Menu() {
    const dummyUser = { name: "Budi Santoso" };

    // Struktur Menu Berbasis Grup
    const menuStructure = [
        {
            group: "Reward Outlet (RWO)",
            items: [
                { 
                    title: "Master Customer", 
                    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
                    route: "/mobile/master-customer"
                },
                { 
                    title: "List Potensi", 
                    icon: "M13 10V3L4 14h7v7l9-11h-7z", // Lightning icon
                    route: "#"
                }
            ]
        },
        {
            group: "Call Plan",
            items: [
                { 
                    title: "Master Customer", 
                    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
                    route: "/mobile/master-customer"
                },
                { 
                    title: "Jadwal Kunjungan", 
                    icon: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z", // Calendar
                    route: "#"
                }
            ]
        }
    ];

    return (
        <MobileLayout user={dummyUser} title="Menu Utama">
            <Head title="Menu Utama" />
            
            <div className="space-y-6 pb-4">
                {/* Search Bar for Menus */}
                <div className="relative animate-fade-in">
                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" placeholder="Cari menu..." className="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm transition-all" />
                </div>

                {menuStructure.map((group, gIdx) => (
                    <section key={gIdx} className="animate-slide-up" style={{ animationDelay: `${gIdx * 100}ms` }}>
                        <h2 className="text-xs font-black text-gray-400 uppercase tracking-widest mb-3 pl-2">{group.group}</h2>
                        <Card padding="none" className="overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
                            <div className="flex flex-col">
                                {group.items.map((item, iIdx) => (
                                    <div 
                                        key={iIdx}
                                        className="border-b border-gray-100 last:border-0 flex justify-between items-center p-4 cursor-pointer transition-colors hover:bg-indigo-50/30 active:bg-indigo-50 group"
                                        onClick={() => { if(item.route !== '#') router.get(item.route); }}
                                    >
                                        <div className="flex items-center space-x-3 flex-1 overflow-hidden">
                                            <div className="shrink-0 w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition-colors group-hover:bg-indigo-100">
                                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d={item.icon} />
                                                </svg>
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <h3 className="font-bold text-[15px] text-gray-800 truncate group-hover:text-indigo-900 transition-colors">{item.title}</h3>
                                                {item.desc && <p className="text-[11px] text-gray-500 mt-0.5 truncate">{item.desc}</p>}
                                            </div>
                                        </div>
                                        
                                        <div className="text-gray-300 group-hover:text-indigo-400 transition-colors shrink-0 ml-2">
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </Card>
                    </section>
                ))}
            </div>
        </MobileLayout>
    );
}
