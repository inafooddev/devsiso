import React, { useState, useEffect, useRef } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import {
    MagnifyingGlassIcon, XMarkIcon, MapPinIcon, ShieldCheckIcon,
    AdjustmentsHorizontalIcon, XCircleIcon, CheckCircleIcon,
    InformationCircleIcon, MapIcon, CameraIcon, EyeIcon, PencilIcon,
    ChartPieIcon, ListBulletIcon, TrashIcon
} from '@heroicons/react/24/outline';
import { ShieldExclamationIcon, BuildingStorefrontIcon } from '@heroicons/react/24/solid';

export default function Index({ outlets, auditReports = [] }) {
    // Draft states (for UI inputs only)
    const [search, setSearch] = useState('');
    const [selectedRegion, setSelectedRegion] = useState('');
    const [selectedArea, setSelectedArea] = useState('');
    const [selectedDistributor, setSelectedDistributor] = useState('');
    
    // Applied states (triggers the actual data filtering)
    const [appliedSearch, setAppliedSearch] = useState('');
    const [appliedRegion, setAppliedRegion] = useState('');
    const [appliedArea, setAppliedArea] = useState('');
    const [appliedDistributor, setAppliedDistributor] = useState('');

    const [filteredOutlets, setFilteredOutlets] = useState([]);
    const [showFiltersSheet, setShowFiltersSheet] = useState(false);
    const [detailOutlet, setDetailOutlet] = useState(null);
    const [zoomedImage, setZoomedImage] = useState(null);
    const [isGettingLocation, setIsGettingLocation] = useState(false);
    const [toast, setToast] = useState(null);
    const [activeTab, setActiveTab] = useState('list'); // 'list' or 'report'

    const showToast = (message, type = 'success') => {
        setToast({ message, type });
        setTimeout(() => setToast(null), 3000);
    };

    const fileInput1 = useRef(null);
    const fileInput2 = useRef(null);
    const fileInput3 = useRef(null);

    const { data, setData, post, processing, errors, reset, transform } = useForm({
        customer_code: '',
        distributor_code: '',
        customer_name: '',
        customer_address: '',
        auditor: '',
        keterangan_hasil_audit: '',
        latitude: '',
        longitude: '',
        foto_audit1: null,
        foto_audit2: null,
        foto_audit3: null,
    });

    const openDetail = (outlet) => {
        setDetailOutlet(outlet);
        
        // Reset file inputs explicitly just in case
        if (fileInput1.current) fileInput1.current.value = '';
        if (fileInput2.current) fileInput2.current.value = '';
        if (fileInput3.current) fileInput3.current.value = '';

        setData({
            customer_code: outlet.customer_code,
            distributor_code: outlet.distributor_code,
            customer_name: outlet.customer_name,
            customer_address: outlet.customer_address,
            auditor: outlet.auditor || '',
            keterangan_hasil_audit: outlet.keterangan_hasil_audit || '',
            latitude: outlet.latitude || '',
            longitude: outlet.longitude || '',
            foto_audit1: null,
            foto_audit2: null,
            foto_audit3: null,
        });
    };

    const fetchCurrentLocation = () => {
        setIsGettingLocation(true);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    setData(prev => ({
                        ...prev,
                        latitude: position.coords.latitude.toString(),
                        longitude: position.coords.longitude.toString()
                    }));
                    setIsGettingLocation(false);
                    showToast('Lokasi GPS berhasil diambil!', 'success');
                },
                (error) => {
                    console.error("GPS Error:", error);
                    setIsGettingLocation(false);
                    let errMsg = 'Gagal mengambil lokasi.';
                    if (error.code === 1) errMsg = 'Izin GPS ditolak oleh perangkat.';
                    else if (error.code === 2) errMsg = 'Posisi GPS tidak tersedia.';
                    else if (error.code === 3) errMsg = 'Waktu pengambilan GPS habis.';
                    showToast(errMsg, 'error');
                },
                { enableHighAccuracy: true, timeout: 7000, maximumAge: 0 }
            );
        } else {
            setIsGettingLocation(false);
            showToast('Browser Anda tidak mendukung GPS.', 'error');
        }
    };

    const submitAudit = (e) => {
        e.preventDefault();
        
        // If coordinate is already retrieved, submit directly
        if (data.latitude && data.longitude && data.latitude !== '0' && data.longitude !== '0') {
            executeSubmit(data.latitude, data.longitude);
            return;
        }

        setIsGettingLocation(true);
        let isSubmitted = false;

        const safeExecuteSubmit = (lat, lng) => {
            if (isSubmitted) return;
            isSubmitted = true;
            executeSubmit(lat, lng);
        };

        if (navigator.geolocation) {
            const locationTimeout = setTimeout(() => {
                console.warn("Manual Geolocation timeout hit");
                safeExecuteSubmit(0, 0);
            }, 5000);

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    clearTimeout(locationTimeout);
                    safeExecuteSubmit(position.coords.latitude, position.coords.longitude);
                },
                (error) => {
                    clearTimeout(locationTimeout);
                    console.warn("Geolocation failed, continuing with 0", error);
                    safeExecuteSubmit(0, 0);
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 10000 }
            );
        } else {
            console.warn("Geolocation not supported by this browser, continuing with 0");
            safeExecuteSubmit(0, 0);
        }
    };

    const executeSubmit = (lat, lng) => {
        transform((currentData) => ({
            ...currentData,
            latitude: lat,
            longitude: lng,
        }));

        post('/mobile/audit', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                setDetailOutlet(null);
                reset();
                setIsGettingLocation(false);
                showToast('Data audit berhasil disimpan!', 'success');
            },
            onError: (errs) => {
                console.error('Validation Error:', errs);
                setIsGettingLocation(false);
                showToast('Gagal menyimpan. Pastikan semua data wajib telah diisi.', 'error');
            },
            onFinish: () => {
                setIsGettingLocation(false);
            }
        });
    };

    // Generate dropdown options dynamically based on DRAFT states
    const regions = [...new Set((outlets || []).map(o => o.region_name).filter(Boolean))].sort();
    const areas = [...new Set((outlets || [])
        .filter(o => !selectedRegion || o.region_name === selectedRegion)
        .map(o => o.area_name).filter(Boolean))].sort();
    const distributors = [...new Set((outlets || [])
        .filter(o => (!selectedRegion || o.region_name === selectedRegion) && (!selectedArea || o.area_name === selectedArea))
        .map(o => o.distributor_name).filter(Boolean))].sort();

    const isFiltered = appliedSearch || appliedRegion || appliedArea || appliedDistributor;

    // Heavy filtering logic only runs when APPLIED states change
    useEffect(() => {
        if (!isFiltered) {
            setFilteredOutlets([]);
            return;
        }

        let result = outlets || [];
        
        if (appliedRegion) result = result.filter(o => o.region_name === appliedRegion);
        if (appliedArea) result = result.filter(o => o.area_name === appliedArea);
        if (appliedDistributor) result = result.filter(o => o.distributor_name === appliedDistributor);

        if (appliedSearch) {
            const q = appliedSearch.toLowerCase();
            result = result.filter(o => 
                (o.customer_name && o.customer_name.toLowerCase().includes(q)) || 
                (o.customer_code && o.customer_code.toLowerCase().includes(q)) ||
                (o.distributor_name && o.distributor_name.toLowerCase().includes(q))
            );
        }
        
        // Membatasi hasil maksimal 150 agar browser tidak freeze saat render data terlalu banyak
        setFilteredOutlets(result.slice(0, 150));
    }, [appliedSearch, appliedRegion, appliedArea, appliedDistributor, outlets, isFiltered]);

    // Handlers
    const applyFilters = () => {
        setAppliedRegion(selectedRegion);
        setAppliedArea(selectedArea);
        setAppliedDistributor(selectedDistributor);
        setShowFiltersSheet(false);
    };

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        setAppliedSearch(search);
    };

    const clearSearch = () => {
        setSearch('');
        setAppliedSearch('');
    };

    const resetFilters = () => {
        setSelectedRegion('');
        setSelectedArea('');
        setSelectedDistributor('');
        setAppliedRegion('');
        setAppliedArea('');
        setAppliedDistributor('');
        setSearch('');
        setAppliedSearch('');
    };

    const handleDetailClick = (report) => {
        const outlet = (outlets || []).find(o => o.customer_code === report.customer_code);
        if (outlet) {
            openDetail(outlet);
        } else {
            showToast('Data toko tidak ditemukan di master list.', 'error');
        }
    };

    const handleEditClick = (report) => {
        const outlet = (outlets || []).find(o => o.customer_code === report.customer_code);
        if (outlet) {
            openDetail(outlet);
            setTimeout(() => {
                const formEl = document.getElementById('audit-form-container');
                if (formEl) {
                    formEl.scrollIntoView({ behavior: 'smooth' });
                }
            }, 300);
        } else {
            showToast('Data toko tidak ditemukan di master list.', 'error');
        }
    };

    const handleDeleteClick = (report) => {
        if (confirm(`Apakah Anda yakin ingin menghapus hasil audit untuk ${report.customer_name}?`)) {
            router.delete(`/mobile/audit/${report.customer_code}`, {
                preserveScroll: true,
                onSuccess: () => {
                    showToast('Hasil audit berhasil dihapus!', 'success');
                },
                onError: () => {
                    showToast('Gagal menghapus hasil audit.', 'error');
                }
            });
        }
    };

    return (
        <div className="w-full max-w-md mx-auto min-h-screen bg-slate-50 text-slate-800 flex flex-col shadow-sm border-x border-slate-100 relative">
            <Head title="Audit Toko" />

            {/* Header */}
            <div className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm shrink-0">
                <header className="px-4 py-3 flex items-center justify-between" style={{ paddingTop: 'calc(0.75rem + env(safe-area-inset-top, 0px))' }}>
                    <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-xl bg-indigo-600/10 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-600/10">
                            <ShieldCheckIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <h1 className="text-xs font-black uppercase tracking-wider text-slate-900 leading-tight">Audit Toko</h1>
                            <p className="text-[8px] font-bold text-indigo-600 tracking-widest uppercase leading-none">Daftar Outlet</p>
                        </div>
                    </div>
                </header>

                <div className="px-4 pb-3 flex items-center gap-2">
                    <form onSubmit={handleSearchSubmit} className="relative flex-1 flex items-center">
                        <button type="submit" className="absolute left-3 text-slate-400 hover:text-indigo-600">
                            <MagnifyingGlassIcon className="w-5 h-5" />
                        </button>
                        <input value={search} onChange={(e) => setSearch(e.target.value)}
                               type="text" 
                               placeholder="Cari (Tekan Enter / Go)..." 
                               className="block w-full pl-10 pr-8 py-2 text-sm text-gray-900 border border-gray-300 rounded-xl bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
                        {search && (
                            <button type="button" onClick={clearSearch} className="absolute right-3 text-slate-400 hover:text-slate-600">
                                <XMarkIcon className="w-4 h-4" />
                            </button>
                        )}
                    </form>
                    <button onClick={() => setShowFiltersSheet(true)} 
                            className={`w-10 h-10 rounded-xl border flex items-center justify-center transition-all duration-200 relative shrink-0 ${appliedRegion || appliedArea || appliedDistributor ? 'bg-indigo-600 text-white shadow-md border-indigo-600' : 'bg-slate-50 text-slate-600 border-slate-200'}`}>
                        <AdjustmentsHorizontalIcon className="w-5 h-5" />
                        {(appliedRegion || appliedArea || appliedDistributor) && (
                            <span className="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full animate-bounce"></span>
                        )}
                    </button>
                </div>


            </div>

            {/* Main Content */}
            {activeTab === 'list' ? (
                <main className="flex-1 px-4 pt-4 pb-24 space-y-4 flex flex-col bg-slate-50/50">
                    <div className="flex-1 flex flex-col gap-3">
                    {filteredOutlets.length > 0 ? filteredOutlets.map((outlet, index) => (
                        <div key={index} className="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm flex flex-col gap-3.5">
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex-1 min-w-0">
                                    <div className="flex flex-wrap items-center gap-1.5 mb-1.5">
                                        <span className="text-[9px] px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold font-mono tracking-wider w-fit">{outlet.customer_code}</span>
                                        <span className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${outlet.status_audit === 'Sudah' ? 'bg-emerald-50 text-emerald-600 border-emerald-100/80' : 'bg-rose-50 text-rose-600 border-rose-100/80'}`}>
                                            {outlet.status_audit === 'Sudah' ? 'Sudah Audit' : 'Belum Audit'}
                                        </span>
                                    </div>
                                    <h4 className="text-xs font-black text-slate-800 tracking-tight leading-snug truncate">{outlet.customer_name}</h4>
                                    
                                    <div className="flex flex-col gap-1 mt-2">
                                        <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium">
                                            <MapPinIcon className="w-3 h-3 shrink-0 text-slate-400" />
                                            <span className="truncate flex-1">{outlet.customer_address || '-'}</span>
                                        </div>
                                        <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium">
                                            <BuildingStorefrontIcon className="w-3 h-3 shrink-0 text-slate-400" />
                                            <span className="truncate flex-1">Cabang: {outlet.cabang || '-'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Action Buttons */}
                            <div className="flex items-center gap-2 mt-2 pt-3 border-t border-slate-100">
                                {outlet.latitude && outlet.longitude && (
                                    <a href={`https://www.google.com/maps/dir/?api=1&destination=${outlet.latitude},${outlet.longitude}`} target="_blank" rel="noreferrer" className="flex-1 inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold uppercase tracking-wide hover:bg-emerald-100">
                                        <MapIcon className="w-3.5 h-3.5" />
                                        Direction
                                    </a>
                                )}
                                <button onClick={() => openDetail(outlet)} className="flex-1 inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-bold uppercase tracking-wide hover:bg-indigo-100">
                                    <InformationCircleIcon className="w-3.5 h-3.5" />
                                    Detail
                                </button>
                            </div>
                        </div>
                    )) : (
                        <div className="bg-white border border-slate-100 rounded-3xl py-12 px-6 text-center shadow-sm flex-1 flex flex-col items-center justify-center">
                            <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                {isFiltered ? <ShieldExclamationIcon className="w-8 h-8" /> : <MagnifyingGlassIcon className="w-8 h-8" />}
                            </div>
                            <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">
                                {isFiltered ? 'Tidak Ada Data' : 'Silakan Cari/Filter Data'}
                            </h4>
                            {!isFiltered && (
                                <p className="text-[10px] text-slate-400 mt-2 font-medium">
                                    Gunakan pencarian atau filter untuk memunculkan daftar audit outlet.
                                </p>
                            )}
                        </div>
                    )}
                </div>
            </main>
            ) : (
                <main className="flex-1 px-4 pt-4 pb-24 space-y-4 flex flex-col bg-slate-50/50">
                    <div className="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col gap-4">
                        {/* List Audit Results */}
                        <div className="mt-2">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                                <h4 className="text-[11px] font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                    <ListBulletIcon className="w-4 h-4 text-indigo-500" />
                                    Daftar Hasil Audit
                                </h4>
                                <a 
                                    href="/mobile/audit/export" 
                                    className="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-wide hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/10"
                                >
                                    <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Export Excel
                                </a>
                            </div>
                            <div className="space-y-3 max-h-[50vh] overflow-y-auto pr-1">
                                {auditReports.length > 0 ? auditReports.map((report, index) => (
                                    <div key={index} className="bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm flex flex-col gap-2 relative overflow-hidden">
                                        <div className="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                                        <div className="flex justify-between items-start gap-2">
                                            <div className="flex-1 min-w-0">
                                                <span className="text-[9px] px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 font-bold font-mono tracking-wider w-fit inline-block mb-1">{report.customer_code}</span>
                                                <h5 className="text-xs font-black text-slate-800 tracking-tight leading-snug truncate">{report.customer_name}</h5>
                                            </div>
                                            <div className="flex flex-col items-end shrink-0">
                                                <span className="text-[8px] uppercase tracking-wider font-extrabold text-slate-400 mb-0.5">Auditor</span>
                                                <span className="text-[10px] px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold shadow-sm">{report.auditor}</span>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium mt-1">
                                            <BuildingStorefrontIcon className="w-3.5 h-3.5 shrink-0 text-slate-400" />
                                            <span className="flex-1 leading-snug">Cabang: <span className="font-bold text-slate-700">{report.cabang || '-'}</span></span>
                                        </div>
                                        {report.keterangan_hasil_audit && (
                                            <div className="text-[10px] text-slate-500 mt-1 leading-snug">
                                                <span className="font-bold text-slate-700">Keterangan Hasil Audit:</span>{' '}
                                                <span className="text-slate-600">{report.keterangan_hasil_audit}</span>
                                            </div>
                                        )}
                                        <div className="flex gap-2 mt-3 pt-3 border-t border-slate-200/60">
                                            <button 
                                                onClick={() => handleDetailClick(report)} 
                                                className="flex-1 inline-flex items-center justify-center gap-1 h-8 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-bold uppercase tracking-wide hover:bg-indigo-100 transition-colors"
                                            >
                                                <EyeIcon className="w-3.5 h-3.5" />
                                                Detail
                                            </button>
                                            <button 
                                                onClick={() => handleEditClick(report)} 
                                                className="flex-1 inline-flex items-center justify-center gap-1 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-bold uppercase tracking-wide hover:bg-amber-100 transition-colors"
                                            >
                                                <PencilIcon className="w-3.5 h-3.5" />
                                                Edit
                                            </button>
                                            <button 
                                                onClick={() => handleDeleteClick(report)} 
                                                className="flex-1 inline-flex items-center justify-center gap-1 h-8 rounded-lg bg-rose-50 text-rose-600 border border-rose-100 text-[10px] font-bold uppercase tracking-wide hover:bg-rose-100 transition-colors"
                                            >
                                                <TrashIcon className="w-3.5 h-3.5" />
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                )) : (
                                    <div className="text-center py-8 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col items-center justify-center">
                                        <ShieldExclamationIcon className="w-8 h-8 text-slate-300 mb-2" />
                                        <span className="text-[11px] font-bold text-slate-500">Belum ada hasil audit</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </main>
            )}


            {/* Filter Bottom Sheet */}
            {showFiltersSheet && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={() => setShowFiltersSheet(false)}></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                            <div>
                                <h4 className="text-sm font-black text-slate-900">Filter Data</h4>
                                <p className="text-[10px] font-semibold text-slate-400">Pilih kriteria lalu tekan terapkan</p>
                            </div>
                            <button onClick={() => setShowFiltersSheet(false)} className="text-slate-400 p-1 bg-slate-50 rounded-full">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="flex-1 overflow-y-auto p-5 space-y-4">
                            <div>
                                <label className="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">Region</label>
                                <select value={selectedRegion} onChange={(e) => { setSelectedRegion(e.target.value); setSelectedArea(''); setSelectedDistributor(''); }} className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-500 font-semibold text-slate-700">
                                    <option value="">Semua Region</option>
                                    {regions.map(r => <option key={r} value={r}>{r}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">Area</label>
                                <select value={selectedArea} onChange={(e) => { setSelectedArea(e.target.value); setSelectedDistributor(''); }} disabled={!selectedRegion && areas.length > 50} className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-500 font-semibold text-slate-700 disabled:opacity-50 disabled:bg-slate-100">
                                    <option value="">Semua Area</option>
                                    {areas.map(a => <option key={a} value={a}>{a}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">Distributor</label>
                                <select value={selectedDistributor} onChange={(e) => setSelectedDistributor(e.target.value)} disabled={!selectedArea && distributors.length > 50} className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-500 font-semibold text-slate-700 disabled:opacity-50 disabled:bg-slate-100">
                                    <option value="">Semua Distributor</option>
                                    {distributors.map(d => <option key={d} value={d}>{d}</option>)}
                                </select>
                            </div>
                        </div>
                        <div className="p-5 border-t border-slate-100 bg-slate-50 flex gap-3">
                            <button onClick={() => { setSelectedRegion(''); setSelectedArea(''); setSelectedDistributor(''); }} className="flex-1 h-12 border border-slate-200 bg-white rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50">Kosongkan</button>
                            <button onClick={applyFilters} className="flex-[2] h-12 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20">Terapkan</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Detail Bottom Sheet */}
            {detailOutlet && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={() => setDetailOutlet(null)}></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                            <div>
                                <span className="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 font-mono font-bold rounded-md text-[9px] mb-1">{detailOutlet.customer_code}</span>
                                <h4 className="text-sm font-black text-slate-900 leading-tight pr-2">{detailOutlet.customer_name}</h4>
                            </div>
                            <button onClick={() => setDetailOutlet(null)} className="text-slate-400 p-1 bg-slate-50 rounded-full shrink-0">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="flex-1 overflow-y-auto p-5 space-y-4">
                            {/* General */}
                            <div>
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 mb-2">Informasi Umum</h5>
                                <div className="bg-slate-50 rounded-xl p-3 space-y-2 border border-slate-100">
                                    <div className="flex justify-between border-b border-slate-200 pb-2 gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">Distributor</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.distributor_name}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-slate-200 pb-2 gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">Cabang</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.cabang}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-slate-200 pb-2 gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">Alamat</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.customer_address}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-slate-200 pb-2 gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">Status Audit</span>
                                        <span className={`text-[10px] font-bold uppercase ${detailOutlet.status_audit === 'Sudah' ? 'text-emerald-600' : 'text-rose-600'}`}>{detailOutlet.status_audit} Audit</span>
                                    </div>
                                    <div className="flex justify-between gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">Keterangan Audit</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.keterangan_hasil_audit || '-'}</span>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Identitas Pemilik */}
                            <div>
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 mb-2">Identitas Pemilik</h5>
                                <div className="bg-slate-50 rounded-xl p-3 space-y-2 border border-slate-100">
                                    <div className="flex justify-between border-b border-slate-200 pb-2 gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">Nama Pemilik</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.nama_pemilik_toko || '-'}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-slate-200 pb-2 gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">Nama KTP</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.nama_ktp || '-'}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-slate-200 pb-2 gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">NIK KTP</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.nik_ktp || '-'}</span>
                                    </div>
                                    <div className="flex justify-between gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">No. HP</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.no_hp || '-'}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Rekening */}
                            <div>
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 mb-2">Rekening Bank</h5>
                                <div className="bg-slate-50 rounded-xl p-3 space-y-2 border border-slate-100">
                                    <div className="flex justify-between border-b border-slate-200 pb-2 gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">Nama Bank</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.nama_bank || '-'}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-slate-200 pb-2 gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">No. Rekening</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.no_rekening || '-'}</span>
                                    </div>
                                    <div className="flex justify-between gap-2">
                                        <span className="text-[10px] font-semibold text-slate-500 shrink-0">A/N Rekening</span>
                                        <span className="text-[10px] font-bold text-slate-800 text-right">{detailOutlet.nama_pemilik_norek || '-'}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Foto */}
                            <div className="pb-6">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 mb-2">Foto Lampiran</h5>
                                <div className="grid grid-cols-2 gap-3">
                                    <div className="bg-slate-50 rounded-xl overflow-hidden border border-slate-100 shadow-sm flex flex-col relative group">
                                        <span className="text-[9px] font-bold text-slate-600 px-2.5 py-1.5 border-b border-slate-100 bg-white/90 backdrop-blur-sm absolute top-0 w-full z-10">KTP</span>
                                        {detailOutlet.foto_ktp ? (
                                            <button type="button" onClick={() => setZoomedImage(`/storage/${detailOutlet.foto_ktp}`)} className="block mt-6 focus:outline-none w-full text-left">
                                                <img src={`/storage/${detailOutlet.foto_ktp}`} alt="Foto KTP" className="w-full h-24 object-cover" />
                                            </button>
                                        ) : (
                                            <div className="mt-6 flex-1 h-24 flex items-center justify-center bg-slate-100/50">
                                                <span className="text-[10px] font-semibold text-slate-400">Belum ada</span>
                                            </div>
                                        )}
                                    </div>

                                    <div className="bg-slate-50 rounded-xl overflow-hidden border border-slate-100 shadow-sm flex flex-col relative group">
                                        <span className="text-[9px] font-bold text-slate-600 px-2.5 py-1.5 border-b border-slate-100 bg-white/90 backdrop-blur-sm absolute top-0 w-full z-10">Tampak Depan</span>
                                        {detailOutlet.tampak_depan ? (
                                            <button type="button" onClick={() => setZoomedImage(`/storage/${detailOutlet.tampak_depan}`)} className="block mt-6 focus:outline-none w-full text-left">
                                                <img src={`/storage/${detailOutlet.tampak_depan}`} alt="Tampak Depan" className="w-full h-24 object-cover" />
                                            </button>
                                        ) : (
                                            <div className="mt-6 flex-1 h-24 flex items-center justify-center bg-slate-100/50">
                                                <span className="text-[10px] font-semibold text-slate-400">Belum ada</span>
                                            </div>
                                        )}
                                    </div>

                                    <div className="bg-slate-50 rounded-xl overflow-hidden border border-slate-100 shadow-sm flex flex-col relative group">
                                        <span className="text-[9px] font-bold text-slate-600 px-2.5 py-1.5 border-b border-slate-100 bg-white/90 backdrop-blur-sm absolute top-0 w-full z-10">Tampak Dalam</span>
                                        {detailOutlet.tampak_dalam ? (
                                            <button type="button" onClick={() => setZoomedImage(`/storage/${detailOutlet.tampak_dalam}`)} className="block mt-6 focus:outline-none w-full text-left">
                                                <img src={`/storage/${detailOutlet.tampak_dalam}`} alt="Tampak Dalam" className="w-full h-24 object-cover" />
                                            </button>
                                        ) : (
                                            <div className="mt-6 flex-1 h-24 flex items-center justify-center bg-slate-100/50">
                                                <span className="text-[10px] font-semibold text-slate-400">Belum ada</span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Form Audit */}
                            <div id="audit-form-container" className="pb-6">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 mb-2">Form Hasil Audit</h5>
                                <form onSubmit={submitAudit} className="bg-white rounded-xl p-4 border border-indigo-100 shadow-sm shadow-indigo-100/50 space-y-4">
                                    <div>
                                        <label className="block text-[10px] font-bold text-slate-700 mb-1">Nama Auditor *</label>
                                        <select value={data.auditor} onChange={e => setData('auditor', e.target.value)} required className="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg outline-none focus:border-indigo-500 bg-slate-50">
                                            <option value="" disabled>Pilih nama auditor...</option>
                                            <option value="Lisa">Lisa</option>
                                            <option value="Juliana">Juliana</option>
                                            <option value="Vera">Vera</option>
                                        </select>
                                        {errors.auditor && <div className="text-[10px] text-rose-500 mt-1">{errors.auditor}</div>}
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-bold text-slate-700 mb-1">Keterangan Audit</label>
                                        <textarea value={data.keterangan_hasil_audit} onChange={e => setData('keterangan_hasil_audit', e.target.value)} placeholder="Tuliskan keterangan jika ada..." rows="2" className="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg outline-none focus:border-indigo-500 bg-slate-50"></textarea>
                                    </div>
                                    {/* Lokasi Koordinat */}
                                    <div className="bg-slate-50 border border-slate-100 p-3 rounded-xl space-y-2">
                                        <div className="flex justify-between items-center">
                                            <span className="text-[10px] font-bold text-slate-700">Titik Koordinat (GPS)</span>
                                            <button 
                                                type="button" 
                                                onClick={fetchCurrentLocation}
                                                disabled={isGettingLocation}
                                                className="text-[9px] text-indigo-600 hover:text-indigo-800 font-black uppercase tracking-wide flex items-center gap-1 disabled:opacity-50"
                                            >
                                                <MapPinIcon className="w-3.5 h-3.5" />
                                                {isGettingLocation ? 'Mengambil...' : 'Ambil Lokasi'}
                                            </button>
                                        </div>
                                        <div className="grid grid-cols-2 gap-2">
                                            <div className="bg-white border border-slate-200/80 rounded-lg p-2 text-center shadow-sm">
                                                <span className="text-[8px] font-extrabold text-slate-400 block mb-0.5 uppercase tracking-wider">Latitude</span>
                                                <span className="text-[10px] font-mono font-bold text-slate-800">{data.latitude || 'Belum diambil'}</span>
                                            </div>
                                            <div className="bg-white border border-slate-200/80 rounded-lg p-2 text-center shadow-sm">
                                                <span className="text-[8px] font-extrabold text-slate-400 block mb-0.5 uppercase tracking-wider">Longitude</span>
                                                <span className="text-[10px] font-mono font-bold text-slate-800">{data.longitude || 'Belum diambil'}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-bold text-slate-700 mb-2">Foto Audit (Opsional)</label>
                                        <div className="grid grid-cols-3 gap-2">
                                            {/* Foto 1 */}
                                            <div className="relative aspect-square rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 flex flex-col items-center justify-center overflow-hidden transition-colors">
                                                {data.foto_audit1 || detailOutlet?.foto_audit1 ? (
                                                    <>
                                                        <img src={data.foto_audit1 ? URL.createObjectURL(data.foto_audit1) : `/storage/${detailOutlet.foto_audit1}`} alt="Audit 1" className="absolute inset-0 w-full h-full object-cover" />
                                                        <div className="absolute inset-0 bg-slate-900/40 flex items-center justify-center gap-3">
                                                            <button type="button" onClick={(e) => { e.preventDefault(); setZoomedImage(data.foto_audit1 ? URL.createObjectURL(data.foto_audit1) : `/storage/${detailOutlet.foto_audit1}`) }} className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <EyeIcon className="w-4 h-4" />
                                                            </button>
                                                            <button type="button" onClick={(e) => { e.preventDefault(); fileInput1.current.click() }} className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <PencilIcon className="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </>
                                                ) : (
                                                    <div onClick={() => fileInput1.current.click()} className="absolute inset-0 flex flex-col items-center justify-center cursor-pointer">
                                                        <CameraIcon className="w-5 h-5 text-indigo-400 mb-1" />
                                                        <span className="text-[9px] font-semibold text-indigo-600">Foto 1</span>
                                                    </div>
                                                )}
                                                <input ref={fileInput1} type="file" onChange={e => setData('foto_audit1', e.target.files[0])} accept="image/*" capture="environment" className="hidden" />
                                            </div>
                                            
                                            {/* Foto 2 */}
                                            <div className="relative aspect-square rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 flex flex-col items-center justify-center overflow-hidden transition-colors">
                                                {data.foto_audit2 || detailOutlet?.foto_audit2 ? (
                                                    <>
                                                        <img src={data.foto_audit2 ? URL.createObjectURL(data.foto_audit2) : `/storage/${detailOutlet.foto_audit2}`} alt="Audit 2" className="absolute inset-0 w-full h-full object-cover" />
                                                        <div className="absolute inset-0 bg-slate-900/40 flex items-center justify-center gap-3">
                                                            <button type="button" onClick={(e) => { e.preventDefault(); setZoomedImage(data.foto_audit2 ? URL.createObjectURL(data.foto_audit2) : `/storage/${detailOutlet.foto_audit2}`) }} className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <EyeIcon className="w-4 h-4" />
                                                            </button>
                                                            <button type="button" onClick={(e) => { e.preventDefault(); fileInput2.current.click() }} className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <PencilIcon className="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </>
                                                ) : (
                                                    <div onClick={() => fileInput2.current.click()} className="absolute inset-0 flex flex-col items-center justify-center cursor-pointer">
                                                        <CameraIcon className="w-5 h-5 text-indigo-400 mb-1" />
                                                        <span className="text-[9px] font-semibold text-indigo-600">Foto 2</span>
                                                    </div>
                                                )}
                                                <input ref={fileInput2} type="file" onChange={e => setData('foto_audit2', e.target.files[0])} accept="image/*" capture="environment" className="hidden" />
                                            </div>
                                            
                                            {/* Foto 3 */}
                                            <div className="relative aspect-square rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 flex flex-col items-center justify-center overflow-hidden transition-colors">
                                                {data.foto_audit3 || detailOutlet?.foto_audit3 ? (
                                                    <>
                                                        <img src={data.foto_audit3 ? URL.createObjectURL(data.foto_audit3) : `/storage/${detailOutlet.foto_audit3}`} alt="Audit 3" className="absolute inset-0 w-full h-full object-cover" />
                                                        <div className="absolute inset-0 bg-slate-900/40 flex items-center justify-center gap-3">
                                                            <button type="button" onClick={(e) => { e.preventDefault(); setZoomedImage(data.foto_audit3 ? URL.createObjectURL(data.foto_audit3) : `/storage/${detailOutlet.foto_audit3}`) }} className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <EyeIcon className="w-4 h-4" />
                                                            </button>
                                                            <button type="button" onClick={(e) => { e.preventDefault(); fileInput3.current.click() }} className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <PencilIcon className="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </>
                                                ) : (
                                                    <div onClick={() => fileInput3.current.click()} className="absolute inset-0 flex flex-col items-center justify-center cursor-pointer">
                                                        <CameraIcon className="w-5 h-5 text-indigo-400 mb-1" />
                                                        <span className="text-[9px] font-semibold text-indigo-600">Foto 3</span>
                                                    </div>
                                                )}
                                                <input ref={fileInput3} type="file" onChange={e => setData('foto_audit3', e.target.files[0])} accept="image/*" capture="environment" className="hidden" />
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" disabled={processing || isGettingLocation} className="w-full h-10 bg-indigo-600 text-white rounded-lg text-xs font-bold shadow-md shadow-indigo-600/20 disabled:opacity-50 disabled:cursor-not-allowed">
                                        {(processing || isGettingLocation) ? 'Menyimpan & Mengambil Lokasi...' : 'Simpan Hasil Audit'}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            )}


            {/* Bottom Navigation */}
            <div className="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-100 shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.1)] pb-[env(safe-area-inset-bottom)] max-w-md mx-auto">
                <div className="flex items-center justify-around p-2">
                    <button 
                        onClick={() => setActiveTab('list')}
                        className={`flex flex-col items-center justify-center w-full py-2 gap-1 rounded-xl transition-all ${activeTab === 'list' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                    >
                        <ListBulletIcon className={`w-6 h-6 ${activeTab === 'list' ? 'text-indigo-600 scale-110' : 'scale-100'} transition-transform`} />
                        <span className={`text-[10px] font-bold ${activeTab === 'list' ? 'text-indigo-600' : ''}`}>Daftar Toko</span>
                    </button>
                    <button 
                        onClick={() => setActiveTab('report')}
                        className={`flex flex-col items-center justify-center w-full py-2 gap-1 rounded-xl transition-all ${activeTab === 'report' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'}`}
                    >
                        <ChartPieIcon className={`w-6 h-6 ${activeTab === 'report' ? 'text-indigo-600 scale-110' : 'scale-100'} transition-transform`} />
                        <span className={`text-[10px] font-bold ${activeTab === 'report' ? 'text-indigo-600' : ''}`}>Laporan</span>
                    </button>
                </div>
            </div>

            {/* Image Zoom Modal */}
            {zoomedImage && (
                <div className="fixed inset-0 z-[60] flex items-center justify-center p-4">
                    <div className="absolute inset-0 bg-slate-950/90 backdrop-blur-sm" onClick={() => setZoomedImage(null)}></div>
                    <div className="relative w-full max-w-sm max-h-[80vh] flex flex-col items-center justify-center animate-fade-in z-[61]">
                        <button onClick={() => setZoomedImage(null)} className="absolute -top-12 right-0 text-white/80 hover:text-white p-2 rounded-full bg-slate-800/50">
                            <XMarkIcon className="w-6 h-6" />
                        </button>
                        <img src={zoomedImage} alt="Zoomed" className="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl ring-1 ring-white/10" />
                    </div>
                </div>
            )}

            {/* Custom Toast Notification */}
            {toast && (
                <div className="fixed top-20 left-1/2 transform -translate-x-1/2 z-[100] px-5 py-3 rounded-full shadow-lg shadow-black/10 flex items-center gap-2.5 transition-all animate-fade-in-down" style={{
                    backgroundColor: toast.type === 'success' ? '#10b981' : '#f43f5e',
                    color: 'white'
                }}>
                    {toast.type === 'success' ? <CheckCircleIcon className="w-5 h-5" /> : <XCircleIcon className="w-5 h-5" />}
                    <span className="text-xs font-bold tracking-wide">{toast.message}</span>
                </div>
            )}
        </div>
    );
}
