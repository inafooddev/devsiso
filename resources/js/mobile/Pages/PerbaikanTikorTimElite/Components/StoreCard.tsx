import React from 'react';
import { BuildingStorefrontIcon as BuildingStorefrontOutline, MapPinIcon } from '@heroicons/react/24/outline';
import { InformationCircleIcon, CheckCircleIcon, XCircleIcon, ClockIcon } from '@heroicons/react/24/solid';

interface StoreCardProps {
    outlet: any;
    activeTab: string;
    onPerbaikiClick: (outlet: any) => void;
}

export default function StoreCard({ outlet, activeTab, onPerbaikiClick }: StoreCardProps) {
    return (
        <div className="border rounded-2xl p-4 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col gap-3.5 transition-all bg-white border-gray-100 hover:border-indigo-100">
            <div className="flex flex-col gap-2.5">
                <div className="flex items-start justify-between gap-3">
                    <h4 className="flex-1 min-w-0 text-sm md:text-sm font-bold text-gray-800 tracking-tight leading-snug truncate">
                        {outlet.customer_name} - {outlet.customer_code}
                    </h4>
                    {outlet.status_perbaikan && (
                        <div className="shrink-0 flex items-start pt-0.5">
                            <div className={`flex items-center gap-1 rounded-full px-2 py-0.5 font-bold uppercase tracking-wider ${
                                outlet.status_perbaikan.toLowerCase() === 'pending' ? 'bg-amber-50 text-amber-600 border border-amber-200' :
                                outlet.status_perbaikan.toLowerCase() === 'approved' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' :
                                outlet.status_perbaikan.toLowerCase() === 'rejected' ? 'bg-rose-50 text-rose-600 border border-rose-200' :
                                'bg-indigo-50 text-indigo-600 border border-indigo-200'
                            }`}>
                                {outlet.status_perbaikan.toLowerCase() === 'pending' ? <ClockIcon className="w-3 h-3" /> :
                                 outlet.status_perbaikan.toLowerCase() === 'approved' ? <CheckCircleIcon className="w-3 h-3" /> :
                                 outlet.status_perbaikan.toLowerCase() === 'rejected' ? <XCircleIcon className="w-3 h-3" /> :
                                 <InformationCircleIcon className="w-3 h-3" />}
                                <span className="text-xs leading-none">{outlet.status_perbaikan}</span>
                            </div>
                        </div>
                    )}
                </div>

                <div className="flex flex-col gap-1.5">
                    <div className="flex items-center gap-1.5 text-xs text-gray-500 font-medium leading-tight">
                        <MapPinIcon className="w-3.5 h-3.5 shrink-0 text-gray-400" />
                        <span className="truncate">{outlet.address || '-'}</span>
                    </div>
                    <div className="flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                        <BuildingStorefrontOutline className="w-3.5 h-3.5 shrink-0 text-gray-400" />
                        <span className="truncate">{outlet.distributor_name || '-'}</span>
                    </div>
                    {activeTab === 'laporan' && (outlet.sales_code || outlet.sales_name) && (
                        <div className="flex items-center gap-1.5 text-xs text-blue-600 font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-3.5 h-3.5 shrink-0 text-blue-500">
                              <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            <span className="truncate">
                                {outlet.sales_name || '-'} {outlet.sales_code ? `(${outlet.sales_code})` : ''}
                            </span>
                        </div>
                    )}
                    {outlet.status_perbaikan && (activeTab === 'laporan' || outlet.status_perbaikan.toLowerCase() === 'rejected') && outlet.keterangan_perbaikan && (
                        <div className={`mt-2 text-xs p-2 rounded-lg border font-medium leading-relaxed ${
                            outlet.status_perbaikan.toLowerCase() === 'rejected'
                                ? 'text-rose-700 bg-rose-50 border-rose-100'
                                : 'text-gray-600 bg-gray-50 border-gray-100'
                        }`}>
                            <span className="font-bold">Info:</span> {outlet.keterangan_perbaikan}
                        </div>
                    )}
                </div>
            </div>
            {activeTab === 'toko' && (
                <div className="flex items-center gap-2 mt-2 pt-3 border-t border-gray-50">
                    {outlet.latitude && outlet.longitude ? (
                        <a href={`https://www.google.com/maps/dir/?api=1&destination=${outlet.latitude},${outlet.longitude}`} target="_blank" rel="noreferrer" className="flex-1 inline-flex items-center justify-center gap-1.5 h-9 rounded-lg bg-gray-50 text-gray-600 border border-gray-200 text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors">
                            <MapPinIcon className="w-4 h-4" /> Navigasi
                        </a>
                    ) : (
                        <button disabled className="flex-1 inline-flex items-center justify-center gap-1.5 h-9 rounded-lg bg-gray-50/50 text-gray-400 border border-gray-100 text-xs font-bold uppercase tracking-wider cursor-not-allowed">
                            <MapPinIcon className="w-4 h-4 opacity-50" /> Navigasi
                        </button>
                    )}
                    <button onClick={() => onPerbaikiClick(outlet)} className="flex-1 inline-flex items-center justify-center h-9 rounded-lg bg-indigo-600 text-white text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-indigo-700 transition-colors">
                        PERBAIKI
                    </button>
                </div>
            )}
            {activeTab === 'visit' && (
                <div className="flex items-center gap-2 mt-2 pt-3 border-t border-gray-50">
                    <button onClick={() => onPerbaikiClick(outlet)} className="flex-1 inline-flex items-center justify-center h-9 rounded-lg bg-indigo-600 text-white text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-indigo-700 transition-colors">
                        PERBAIKI
                    </button>
                </div>
            )}
        </div>
    );
}
