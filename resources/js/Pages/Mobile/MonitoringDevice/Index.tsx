import React, { useState, useEffect } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { LoginScreen } from './components/LoginScreen';
import { MonitoringCard } from './components/MonitoringCard';
import { MonitoringFormSheet } from './components/MonitoringFormSheet';
import { MonitoringDetailSheet } from './components/MonitoringDetailSheet';
import { useMonitoringForm } from './hooks/useMonitoringForm';

import Swal from 'sweetalert2';

export default function Index({ salesData, monitoringData, months, filterYear, sessionSalesCode, sessionSalesName }: any) {
    const { flash } = usePage().props as any;

    useEffect(() => {
        if (flash?.success || flash?.error) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: flash.success ? 'success' : 'error',
                title: flash.success || flash.error,
                customClass: {
                    popup: 'rounded-xl shadow-xl border border-slate-100 p-2 text-sm font-bold',
                    title: 'text-sm font-bold text-slate-700 font-sans'
                }
            });
        }
    }, [flash]);
    
    // Form & Detail State
    const [showForm, setShowForm] = useState(false);
    const [showDetail, setShowDetail] = useState(false);
    const [detailData, setDetailData] = useState<any>(null);
    const formHook = useMonitoringForm();

    const handleLogout = () => {
        if (window.confirm('Anda yakin ingin keluar?')) {
            router.post('/app/monitoring-device/logout');
        }
    };

    if (!sessionSalesCode) {
        return (
            <>
                <Head title="Login - Monitoring Device" />
                <LoginScreen />
            </>
        );
    }

    const handleAdd = (distributorCode: string, monthValue: string) => {
        formHook.resetForm();
        formHook.setData({
            tanggal: monthValue,
            form_distributor_code: distributorCode,
            form_sales_code: sessionSalesCode,
            foto_tampak_depan: null,
            foto_tampak_belakang: null,
            kondisi_hp: '',
            kondisi_kartu: '',
            id: null,
        });
        setShowForm(true);
    };

    const handleEdit = (item: any) => {
        formHook.resetForm();
        formHook.setEditId(item.id);
        formHook.setExistingFotoDepan(item.foto_tampak_depan_url || null);
        formHook.setExistingFotoBelakang(item.foto_tampak_belakang_url || null);
        formHook.setData({
            tanggal: item.tanggal.substring(0, 7),
            form_distributor_code: item.distributor_code,
            form_sales_code: item.sales_code,
            foto_tampak_depan: null,
            foto_tampak_belakang: null,
            kondisi_hp: item.kondisi_hp || '',
            kondisi_kartu: item.kondisi_kartu || '',
            id: item.id,
        });
        setShowForm(true);
    };

    const openDetail = (data: any) => {
        setDetailData(data);
        setShowDetail(true);
    };



    return (
        <div className="w-full max-w-md mx-auto min-h-screen bg-slate-50 text-slate-800 flex flex-col shadow-sm border-x border-slate-100 relative">
            <Head title="Mobile Monitoring Device" />

            {/* Flash Message Handled by SweetAlert2 */}

            {/* Sticky Header */}
            <div className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm shrink-0 pt-safe">
                <header className="px-4 py-3 flex items-center justify-between">
                    <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shadow-sm shadow-primary/10">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="w-5 h-5">
                                <path d="M10.5 18.75a.75.75 0 000 1.5h3a.75.75 0 000-1.5h-3z" />
                                <path fillRule="evenodd" d="M8.625.75A3.375 3.375 0 005.25 4.125v15.75a3.375 3.375 0 003.375 3.375h6.75a3.375 3.375 0 003.375-3.375V4.125A3.375 3.375 0 0015.375.75h-6.75zM7.5 4.125C7.5 3.504 8.004 3 8.625 3H9.75v.375c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V3h1.125c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-6.75A1.125 1.125 0 017.5 19.875V4.125z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <h1 className="text-xs font-black uppercase tracking-wider text-slate-900 leading-tight">Monitoring Device</h1>
                            <p className="text-[0.5rem] font-bold text-primary tracking-widest uppercase leading-none">{sessionSalesName}</p>
                        </div>
                    </div>
                    <button onClick={handleLogout} className="btn btn-ghost btn-circle btn-xs text-rose-500 bg-rose-50 hover:bg-rose-100" title="Keluar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-4 h-4">
                            <path fillRule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clipRule="evenodd" />
                            <path fillRule="evenodd" d="M19 10a.75.75 0 00-.75-.75H8.704l1.048-1.048a.75.75 0 10-1.06-1.06l-2.25 2.25a.75.75 0 000 1.06l2.25 2.25a.75.75 0 101.06-1.06l-1.048-1.048h9.546A.75.75 0 0019 10z" clipRule="evenodd" />
                        </svg>
                    </button>
                </header>
            </div>

            {/* Main Content */}
            <main className="flex-1 px-4 py-4 space-y-4 flex flex-col bg-slate-50/50 pb-24">
                <div className="flex-1 flex flex-col gap-3">
                    {salesData.length > 0 ? (
                        salesData.map((item: any, i: number) => {
                            const mKey = `${item.distributor_code}_${item.sales_code}_${item.month_value}`;
                            const mData = monitoringData[mKey];
                            
                            return (
                                <MonitoringCard 
                                    key={mKey}
                                    item={item}
                                    mData={mData}
                                    onAdd={() => handleAdd(item.distributor_code, item.month_value)}
                                    onDetail={() => openDetail(mData)}
                                />
                            );
                        })
                    ) : (
                        <div className="bg-white border border-slate-100 rounded-3xl py-12 px-6 text-center shadow-xs flex-1 flex flex-col items-center justify-center">
                            <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-8 h-8">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                            <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">Data Tidak Ditemukan</h4>
                            <p className="text-[0.625rem] text-slate-400 max-w-52 mx-auto leading-normal font-semibold mt-1">
                                Tidak ada data yang cocok dengan pencarian Anda.
                            </p>
                        </div>
                    )}
                </div>
            </main>



            {/* Form Sheet */}
            <MonitoringFormSheet 
                show={showForm} 
                onClose={() => setShowForm(false)} 
                formHook={formHook}
                salesName={sessionSalesName}
            />

            {/* Detail Sheet */}
            <MonitoringDetailSheet 
                show={showDetail} 
                onClose={() => setShowDetail(false)} 
                data={detailData}
                salesName={sessionSalesName}
                onEdit={() => {
                    const snapshot = detailData;
                    setShowDetail(false);
                    setDetailData(null);
                    setTimeout(() => handleEdit(snapshot), 350);
                }}
            />
        </div>
    );
}
