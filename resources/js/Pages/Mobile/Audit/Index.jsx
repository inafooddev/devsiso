import React, { useState, useEffect, useRef } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import {
    MagnifyingGlassIcon, XMarkIcon, MapPinIcon, ShieldCheckIcon,
    AdjustmentsHorizontalIcon, XCircleIcon, CheckCircleIcon,
    InformationCircleIcon, MapIcon, CameraIcon, EyeIcon, PencilIcon,
    ChartPieIcon, ListBulletIcon, TrashIcon
} from '@heroicons/react/24/outline';
import { ShieldExclamationIcon, BuildingStorefrontIcon } from '@heroicons/react/24/solid';

const deg2rad = (deg) => {
    return deg * (Math.PI / 180);
};

const getDistance = (lat1, lon1, lat2, lon2) => {
    if (!lat1 || !lon1 || !lat2 || !lon2) return Infinity;
    const R = 6371; // Radius of the earth in km
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a = 
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
        Math.sin(dLon / 2) * Math.sin(dLon / 2)
    ; 
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)); 
    const d = R * c; // Distance in km
    return d;
};

export default function Index({ outlets, auditReports = [] }) {
    const [sessionAuditor, setSessionAuditor] = useState(null);
    const [isSessionLoaded, setIsSessionLoaded] = useState(false);
    const [showLogoutModal, setShowLogoutModal] = useState(false);
    const [isFormTouched, setIsFormTouched] = useState(false);

    useEffect(() => {
        const stored = localStorage.getItem('selected_auditor');
        if (stored) {
            setSessionAuditor(stored);
        }
        setIsSessionLoaded(true);
    }, []);

    const handleLoginAuditor = (name) => {
        localStorage.setItem('selected_auditor', name);
        setSessionAuditor(name);
        showToast(`Selamat datang, ${name}!`, 'success');
    };

    const handleLogoutAuditor = () => {
        setShowLogoutModal(true);
    };

    const confirmLogoutAuditor = () => {
        localStorage.removeItem('selected_auditor');
        setSessionAuditor(null);
        setShowLogoutModal(false);
        showToast('Berhasil keluar dari sesi.', 'success');
    };

    const [reportSearch, setReportSearch] = useState('');
    const allMyReports = (auditReports || []).filter(r => r.auditor?.toLowerCase().trim() === sessionAuditor?.toLowerCase().trim());
    const filteredReports = allMyReports.filter(r => {
        if (!reportSearch) return true;
        const q = reportSearch.toLowerCase();
        return (r.customer_name?.toLowerCase().includes(q) || r.customer_code?.toLowerCase().includes(q) || r.cabang?.toLowerCase().includes(q));
    });

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
    const [userLocation, setUserLocation] = useState(null);
    const userLocationRef = useRef(null);
    const [gpsStatus, setGpsStatus] = useState('loading'); // 'loading', 'success', 'error'

    useEffect(() => {
        let isMounted = true;

        const timeoutId = setTimeout(() => {
            if (isMounted && !userLocationRef.current) {
                setGpsStatus('error');
            }
        }, 10000); // 10 seconds check

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    clearTimeout(timeoutId);
                    if (isMounted) {
                        const newLocation = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        };
                        setUserLocation(newLocation);
                        userLocationRef.current = newLocation;
                        setGpsStatus('success');
                    }
                },
                (error) => {
                    clearTimeout(timeoutId);
                    console.warn("Could not get current position for nearest stores", error);
                    if (isMounted) {
                        setGpsStatus('error');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 10000 }
            );
        } else {
            clearTimeout(timeoutId);
            if (isMounted) {
                setGpsStatus('error');
            }
        }

        return () => {
            isMounted = false;
            clearTimeout(timeoutId);
        };
    }, []);

    const showToast = (message, type = 'success') => {
        setToast({ message, type });
        setTimeout(() => setToast(null), 3000);
    };

    const fileInput1 = useRef(null);
    const fileInput2 = useRef(null);
    const fileInput3 = useRef(null);

    const { data, setData, post, processing, errors, reset, isDirty } = useForm({
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
    const [showDiscardModal, setShowDiscardModal] = useState(false);
    const [showNoPhotoWarning, setShowNoPhotoWarning] = useState(false);
    const [deletingReport, setDeletingReport] = useState(null);
    const [isDeletingCode, setIsDeletingCode] = useState(null);
    const [totalFilteredCount, setTotalFilteredCount] = useState(0);

    useEffect(() => {
        if (sessionAuditor) {
            setData('auditor', sessionAuditor);
        }
    }, [sessionAuditor]);

    const openDetail = (outlet) => {
        setIsFormTouched(false);
        setDetailOutlet(outlet);
        setShowNoPhotoWarning(false);
        setZoomedImage(null);
        
        // Reset file inputs explicitly just in case
        if (fileInput1.current) fileInput1.current.value = '';
        if (fileInput2.current) fileInput2.current.value = '';
        if (fileInput3.current) fileInput3.current.value = '';

        setData({
            customer_code: outlet.customer_code,
            distributor_code: outlet.distributor_code,
            customer_name: outlet.customer_name,
            customer_address: outlet.customer_address,
            auditor: sessionAuditor || outlet.auditor || '',
            keterangan_hasil_audit: outlet.keterangan_hasil_audit || '',
            latitude: outlet.audit_latitude || '',
            longitude: outlet.audit_longitude || '',
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

    const proceedSubmit = () => {
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

    const submitAudit = (e) => {
        e.preventDefault();
        
        // Warning no photo
        const hasPhoto = data.foto_audit1 || data.foto_audit2 || data.foto_audit3 || detailOutlet?.foto_audit1 || detailOutlet?.foto_audit2 || detailOutlet?.foto_audit3;
        if (!hasPhoto && !showNoPhotoWarning) {
            setShowNoPhotoWarning(true);
            return;
        }

        proceedSubmit();
    };

    const executeSubmit = (lat, lng) => {
        post('/mobile/audit', {
            data: {
                ...data,
                latitude: lat,
                longitude: lng,
            },
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
            if (userLocation && outlets && outlets.length > 0) {
                const sorted = [...outlets]
                    .map(o => {
                        const dist = getDistance(
                            userLocation.latitude,
                            userLocation.longitude,
                            parseFloat(o.master_latitude),
                            parseFloat(o.master_longitude)
                        );
                        return { ...o, distance: dist };
                    })
                    .filter(o => o.distance <= 5) // Filter radius 5km
                    .sort((a, b) => a.distance - b.distance);
                setFilteredOutlets(sorted);
            } else {
                setFilteredOutlets([]);
            }
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
        setTotalFilteredCount(result.length);
        setFilteredOutlets(result.slice(0, 150));
    }, [appliedSearch, appliedRegion, appliedArea, appliedDistributor, outlets, isFiltered, userLocation]);

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

    const openDetailFromReport = (report, scrollToForm = false) => {
        const outlet = (outlets || []).find(o => o.customer_code === report.customer_code);
        if (outlet) {
            openDetail(outlet);
            if (scrollToForm) {
                setTimeout(() => {
                    const formEl = document.getElementById('audit-form-container');
                    if (formEl) {
                        formEl.scrollIntoView({ behavior: 'smooth' });
                    }
                }, 300);
            }
        } else {
            showToast('Data toko tidak ditemukan di master list.', 'error');
        }
    };

    const requestDeleteReport = (report) => {
        setDeletingReport(report);
    };

    const confirmDeleteReport = () => {
        if (!deletingReport) return;
        setIsDeletingCode(deletingReport.customer_code);
        
        router.delete(`/mobile/audit/${deletingReport.customer_code}`, {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Hasil audit berhasil dihapus!', 'success');
                setDeletingReport(null);
            },
            onError: () => {
                showToast('Gagal menghapus hasil audit.', 'error');
                setDeletingReport(null);
            },
            onFinish: () => {
                setIsDeletingCode(null);
            }
        });
    };

    const handleCloseDetail = () => {
        if (isFormTouched) {
            setShowDiscardModal(true);
        } else {
            setDetailOutlet(null);
            setShowNoPhotoWarning(false);
        }
    };

    if (!isSessionLoaded) {
        return (
            <div className="w-full min-h-screen bg-slate-50 flex items-center justify-center">
                <div className="w-8 h-8 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
            </div>
        );
    }

    if (isSessionLoaded && !sessionAuditor) {
        return (
            <div className="w-full min-h-screen bg-gradient-to-br from-indigo-50 via-slate-50 to-indigo-100/50 flex items-center justify-center p-6">
                <Head title="Pilih Auditor - Audit Toko" />
                <div className="w-full max-w-sm bg-white/90 backdrop-blur-lg border border-slate-200/50 rounded-3xl shadow-xl p-6 flex flex-col items-center animate-fade-in">
                    
                    <div className="w-14 h-14 rounded-2xl bg-indigo-600/10 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-600/10 mb-4 animate-bounce-slow">
                        <ShieldCheckIcon className="w-8 h-8" />
                    </div>
                    <h2 className="text-sm font-black uppercase tracking-wider text-slate-900 leading-tight text-center">Sistem Audit Toko</h2>
                    <p className="text-[10px] font-bold text-indigo-600 tracking-widest uppercase mb-6 leading-none text-center">Pilih Identitas Auditor</p>
                    


                    <div className="w-full flex flex-col gap-3">
                        {[
                            { name: 'Lisa', gradient: 'linear-gradient(135deg, #6366f1, #2563eb)' },
                            { name: 'Juliana', gradient: 'linear-gradient(135deg, #ec4899, #e11d48)' },
                            { name: 'Vera', gradient: 'linear-gradient(135deg, #10b981, #0d9488)' }
                        ].map((auditor) => (
                            <button
                                key={auditor.name}
                                onClick={() => handleLoginAuditor(auditor.name)}
                                className="w-full flex items-center justify-between p-3.5 bg-white border border-slate-200 hover:border-indigo-500 hover:shadow-md active:scale-[0.98] rounded-2xl transition-all group"
                            >
                                <div className="flex items-center gap-3">
                                    <div 
                                        style={{ background: auditor.gradient }} 
                                        className="w-10 h-10 rounded-xl text-white font-black flex items-center justify-center shadow-md shadow-slate-900/10 shrink-0 text-sm"
                                    >
                                        {auditor.name.charAt(0)}
                                    </div>
                                    <div className="text-left">
                                        <h4 className="text-xs font-black text-slate-800 tracking-tight leading-snug group-hover:text-indigo-600 transition-colors">{auditor.name}</h4>
                                    </div>
                                </div>
                                <div className="w-6 h-6 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                    <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="3">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </button>
                        ))}
                    </div>

                    <div className="mt-8 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">
                        PT INAFOOD © {new Date().getFullYear()}
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="w-full min-h-screen bg-slate-50 text-slate-800 flex flex-col relative">
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
                            <p className="text-[8px] font-bold text-indigo-600 tracking-widest uppercase leading-none">
                                {activeTab === 'list' ? 'Daftar Outlet' : 'Hasil Laporan Audit'}
                            </p>
                        </div>
                    </div>
                    {sessionAuditor && (
                        <div className="flex items-center gap-2">
                            <div className="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1.5 rounded-xl shadow-inner">
                                <div className="w-5 h-5 rounded-lg bg-indigo-600 text-white text-[9px] font-black flex items-center justify-center uppercase shrink-0">
                                    {sessionAuditor.charAt(0)}
                                </div>
                                <span className="text-[10px] font-black text-slate-700 leading-none">{sessionAuditor}</span>
                            </div>
                            <button 
                                type="button"
                                onClick={handleLogoutAuditor}
                                className="p-1.5 rounded-xl text-rose-500 bg-rose-50 hover:bg-rose-100 transition-colors border border-rose-100 shrink-0"
                                title="Ganti Auditor"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </div>
                    )}
                </header>

                <div className="px-4 pb-3 flex items-center gap-2">
                    <form onSubmit={handleSearchSubmit} className="relative flex-1 flex items-center">
                        <button type="submit" className="absolute left-3 text-slate-400 hover:text-indigo-600">
                            <MagnifyingGlassIcon className="w-5 h-5" />
                        </button>
                        <input value={search} onChange={(e) => setSearch(e.target.value)}
                               type="text" 
                               placeholder="Cari (Tekan Enter / Go)..." 
                               className="block w-full pl-10 pr-8 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:border-indigo-500 outline-none text-slate-800" />
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
                <main className="flex-1 px-4 pt-4 pb-24 bg-slate-50/50">
                    {!isFiltered && gpsStatus === 'success' && (
                        <div className="bg-indigo-50 border border-indigo-100 rounded-xl p-3 flex items-center gap-2.5 mb-4 text-[10px] text-indigo-700 font-bold shadow-sm">
                            <MapPinIcon className="w-4 h-4 text-indigo-600 shrink-0" />
                            <span>Menampilkan toko terdekat dalam radius 5 km dari lokasi Anda.</span>
                        </div>
                    )}
                    {!isFiltered && gpsStatus === 'loading' && (
                        <div className="bg-slate-100 border border-slate-200 rounded-xl p-3 flex items-center gap-2.5 mb-4 text-[10px] text-slate-600 font-bold animate-pulse shadow-sm">
                            <div className="w-4 h-4 rounded-full border-2 border-slate-400 border-t-transparent animate-spin shrink-0"></div>
                            <span>Mendeteksi lokasi GPS Anda untuk mencari toko dalam radius 5 km (Maksimal 10 detik)...</span>
                        </div>
                    )}
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    {filteredOutlets.length > 0 ? filteredOutlets.map((outlet) => (
                        <div key={outlet.customer_code} className={`border rounded-2xl p-4 shadow-sm flex flex-col gap-3.5 transition-all ${outlet.rwo_status === 'RWO' ? 'bg-purple-50/60 border-purple-200/80 shadow-purple-100/40' : 'bg-white border-slate-100'}`}>
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex-1 min-w-0">
                                    <div className="flex flex-wrap items-center gap-1.5 mb-1.5">
                                        <span className="text-[9px] px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold font-mono tracking-wider w-fit">{outlet.customer_code}</span>
                                        <span className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${outlet.status_audit === 'Sudah' ? 'bg-emerald-50 text-emerald-600 border-emerald-100/80' : 'bg-rose-50 text-rose-600 border-rose-100/80'}`}>
                                            {outlet.status_audit === 'Sudah' ? 'Sudah Audit' : 'Belum Audit'}
                                        </span>
                                        {outlet.status_audit === 'Sudah' && (
                                            outlet.auditor?.toLowerCase().trim() === sessionAuditor?.toLowerCase().trim() ? (
                                                <span className="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-indigo-50 text-indigo-600 border border-indigo-100/80">
                                                    ✓ Oleh Anda
                                                </span>
                                            ) : outlet.auditor && (
                                                <span className="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-slate-50 text-slate-500 border border-slate-200/80">
                                                    Oleh: {outlet.auditor}
                                                </span>
                                            )
                                        )}
                                        {outlet.rwo_status && (
                                            <span className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${outlet.rwo_status === 'RWO' ? 'bg-purple-50 text-purple-600 border-purple-100/80' : 'bg-slate-50 text-slate-500 border-slate-200/80'}`}>
                                                {outlet.rwo_status}
                                            </span>
                                        )}
                                        {outlet.distance !== undefined && outlet.distance !== Infinity && (
                                            <span className="text-[8px] px-1.5 py-0.5 rounded-md bg-indigo-50 text-indigo-600 border border-indigo-100/80 font-bold">
                                                {outlet.distance < 1 ? `${Math.round(outlet.distance * 1000)} m` : `${outlet.distance.toFixed(1)} km`}
                                            </span>
                                        )}
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
                                {outlet.master_latitude && outlet.master_longitude && (
                                    <a href={`https://www.google.com/maps/dir/?api=1&destination=${outlet.master_latitude},${outlet.master_longitude}`} target="_blank" rel="noreferrer" className="flex-1 inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold uppercase tracking-wide hover:bg-emerald-100">
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
                        <div className="bg-white border border-slate-100 rounded-3xl py-12 px-6 text-center shadow-sm flex-1 flex flex-col items-center justify-center col-span-full">
                            {isFiltered ? (
                                <>
                                    <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                        <ShieldExclamationIcon className="w-8 h-8" />
                                    </div>
                                    <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">Tidak Ada Data</h4>
                                    <p className="text-[10px] text-slate-400 mt-2 font-medium">
                                        Kriteria pencarian Anda tidak cocok dengan toko mana pun.
                                    </p>
                                </>
                            ) : (
                                gpsStatus === 'loading' ? (
                                    <>
                                        <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                            <div className="w-8 h-8 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
                                        </div>
                                        <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">Mencari Toko Terdekat...</h4>
                                        <p className="text-[10px] text-slate-400 mt-2 font-medium">
                                            Mendeteksi lokasi GPS perangkat Anda.
                                        </p>
                                    </>
                                ) : gpsStatus === 'error' ? (
                                    <>
                                        <div className="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-2">
                                            <ShieldExclamationIcon className="w-8 h-8" />
                                        </div>
                                        <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">Gagal Mendeteksi Lokasi</h4>
                                        <p className="text-[10px] text-slate-400 mt-2 font-medium max-w-xs mx-auto leading-relaxed">
                                            Izin lokasi ditolak, waktu habis, atau GPS mati. <br />
                                            <span className="text-indigo-600 font-bold">Silakan nyalakan GPS Anda</span> atau terapkan filter wilayah di pojok kanan atas secara manual.
                                        </p>
                                    </>
                                ) : (
                                    <>
                                        <div className="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 mb-2">
                                            <MapPinIcon className="w-8 h-8" />
                                        </div>
                                        <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">Tidak Ada Toko Terdekat</h4>
                                        <p className="text-[10px] text-slate-400 mt-2 font-medium max-w-xs mx-auto leading-relaxed">
                                            Lokasi Anda berhasil dideteksi, namun tidak ditemukan toko dalam <span className="font-bold">radius 5 km</span>. <br />
                                            Silakan terapkan filter wilayah di pojok kanan atas secara manual.
                                        </p>
                                    </>
                                )
                            )}
                        </div>
                    )}
                    {totalFilteredCount > 150 && (
                        <div className="mt-4 text-center">
                            <span className="inline-block px-3 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] font-bold rounded-lg shadow-sm">
                                Menampilkan 150 dari {totalFilteredCount} hasil. Gunakan filter untuk mempersempit.
                            </span>
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
                                    href={`/mobile/audit/export?auditor=${sessionAuditor}`}
                                    className="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-wide hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/10"
                                >
                                    <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Export Excel
                                </a>
                            </div>

                            <div className="relative mb-4">
                                <input type="text" value={reportSearch} onChange={(e) => setReportSearch(e.target.value)} placeholder="Cari laporan (toko, kode, cabang)..." className="w-full h-10 pl-10 pr-10 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:border-indigo-500 outline-none" />
                                <MagnifyingGlassIcon className="w-5 h-5 absolute left-3 top-2.5 text-slate-400" />
                                {reportSearch && (
                                    <button onClick={() => setReportSearch('')} className="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                                        <XMarkIcon className="w-5 h-5" />
                                    </button>
                                )}
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-[60vh] overflow-y-auto pr-1">
                                {filteredReports.length > 0 ? filteredReports.map((report) => (
                                    <div key={report.customer_code} className="bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm flex flex-col gap-2 relative overflow-hidden">
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
                                        {report.created_at && (
                                            <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium mt-1">
                                                <svg className="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span className="flex-1 leading-snug">Tanggal: <span className="font-bold text-slate-700">{new Date(report.created_at).toLocaleString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit'})}</span></span>
                                            </div>
                                        )}
                                        {report.keterangan_hasil_audit && (
                                            <div className="text-[10px] text-slate-500 mt-1 leading-snug">
                                                <span className="font-bold text-slate-700">Keterangan Hasil Audit:</span>{' '}
                                                <span className="text-slate-600">{report.keterangan_hasil_audit}</span>
                                            </div>
                                        )}
                                        <div className="flex gap-2 mt-3 pt-3 border-t border-slate-200/60">
                                            <button 
                                                onClick={() => openDetailFromReport(report)} 
                                                className="flex-1 inline-flex items-center justify-center gap-1 h-8 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-bold uppercase tracking-wide hover:bg-indigo-100 transition-colors"
                                            >
                                                <EyeIcon className="w-3.5 h-3.5" />
                                                Detail
                                            </button>
                                            <button 
                                                onClick={() => openDetailFromReport(report, true)} 
                                                className="flex-1 inline-flex items-center justify-center gap-1 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-bold uppercase tracking-wide hover:bg-amber-100 transition-colors"
                                            >
                                                <PencilIcon className="w-3.5 h-3.5" />
                                                Edit
                                            </button>
                                            <button 
                                                onClick={() => requestDeleteReport(report)} 
                                                disabled={isDeletingCode === report.customer_code}
                                                className={`flex-1 inline-flex items-center justify-center gap-1 h-8 rounded-lg text-[10px] font-bold uppercase tracking-wide transition-colors ${isDeletingCode === report.customer_code ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100'}`}
                                            >
                                                {isDeletingCode === report.customer_code ? (
                                                    <div className="w-3 h-3 rounded-full border-2 border-slate-400 border-t-transparent animate-spin"></div>
                                                ) : (
                                                    <TrashIcon className="w-3.5 h-3.5" />
                                                )}
                                                {isDeletingCode === report.customer_code ? 'Hapus...' : 'Hapus'}
                                            </button>
                                        </div>
                                    </div>
                                )) : (
                                    <div className="text-center py-8 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col items-center justify-center col-span-full">
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
                    <div className="fixed bottom-0 left-0 right-0 max-w-xl md:max-w-2xl mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
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
                                <select value={selectedArea} onChange={(e) => { setSelectedArea(e.target.value); setSelectedDistributor(''); }} disabled={!selectedRegion} className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-500 font-semibold text-slate-700 disabled:opacity-50 disabled:bg-slate-100">
                                    <option value="">Semua Area</option>
                                    {areas.map(a => <option key={a} value={a}>{a}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">Distributor</label>
                                <select value={selectedDistributor} onChange={(e) => setSelectedDistributor(e.target.value)} disabled={!selectedArea} className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-500 font-semibold text-slate-700 disabled:opacity-50 disabled:bg-slate-100">
                                    <option value="">Semua Distributor</option>
                                    {distributors.map(d => <option key={d} value={d}>{d}</option>)}
                                </select>
                            </div>
                        </div>
                        <div className="p-5 border-t border-slate-100 bg-slate-50 flex gap-3">
                            <button onClick={() => { resetFilters(); setShowFiltersSheet(false); }} className="flex-1 h-12 border border-slate-200 bg-white rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50">Kosongkan</button>
                            <button onClick={applyFilters} className="flex-1 h-12 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20">Terapkan</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Detail Bottom Sheet */}
            {detailOutlet && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={handleCloseDetail}></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-xl md:max-w-2xl mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                            <div>
                                <div className="flex flex-wrap items-center gap-1.5 mb-1">
                                    <span className="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 font-mono font-bold rounded-md text-[9px]">{detailOutlet.customer_code}</span>
                                    {detailOutlet.rwo_status && (
                                        <span className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${detailOutlet.rwo_status === 'RWO' ? 'bg-purple-50 text-purple-600 border-purple-100/80' : 'bg-slate-50 text-slate-500 border-slate-200/80'}`}>
                                            {detailOutlet.rwo_status}
                                        </span>
                                    )}
                                </div>
                                <h4 className="text-sm font-black text-slate-900 leading-tight pr-2">{detailOutlet.customer_name}</h4>
                            </div>
                            <button onClick={handleCloseDetail} className="text-slate-400 p-1 bg-slate-50 rounded-full shrink-0">
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
                                        <input 
                                            type="text" 
                                            value={data.auditor} 
                                            readOnly 
                                            required 
                                            className="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg outline-none bg-slate-100 text-slate-500 cursor-not-allowed font-bold" 
                                        />
                                        {errors.auditor && <div className="text-[10px] text-rose-500 mt-1">{errors.auditor}</div>}
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-bold text-slate-700 mb-1">Keterangan Audit</label>
                                        <textarea 
                                            value={data.keterangan_hasil_audit || ''} 
                                            onChange={e => {
                                                setIsFormTouched(true);
                                                setData('keterangan_hasil_audit', e.target.value);
                                            }} 
                                            maxLength={500}
                                            placeholder="Tuliskan keterangan jika ada..." 
                                            rows="2" 
                                            className="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg outline-none focus:border-indigo-500 bg-slate-50"
                                        ></textarea>
                                        <div className="flex justify-end mt-1">
                                            <span className={`text-[9px] font-semibold ${(data.keterangan_hasil_audit || '').length > 480 ? 'text-rose-500' : (data.keterangan_hasil_audit || '').length > 400 ? 'text-amber-500' : 'text-slate-400'}`}>
                                                {(data.keterangan_hasil_audit || '').length}/500
                                            </span>
                                        </div>
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
                                                        <div className="absolute inset-0 bg-slate-900/40 flex items-center justify-center gap-1.5 sm:gap-3">
                                                            <button type="button" onClick={(e) => { e.preventDefault(); setZoomedImage(data.foto_audit1 ? URL.createObjectURL(data.foto_audit1) : `/storage/${detailOutlet.foto_audit1}`) }} className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <EyeIcon className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                            </button>
                                                            <button type="button" onClick={(e) => { e.preventDefault(); fileInput1.current.click() }} className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <PencilIcon className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                            </button>
                                                            {data.foto_audit1 && (
                                                                <button type="button" onClick={(e) => { e.preventDefault(); setData('foto_audit1', null); if(fileInput1.current) fileInput1.current.value=''; }} className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 hover:bg-rose-500/80 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                    <TrashIcon className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                                </button>
                                                            )}
                                                        </div>
                                                    </>
                                                ) : (
                                                    <div onClick={() => fileInput1.current.click()} className="absolute inset-0 flex flex-col items-center justify-center cursor-pointer">
                                                        <CameraIcon className="w-5 h-5 text-indigo-400 mb-1" />
                                                        <span className="text-[9px] font-semibold text-indigo-600">Foto 1</span>
                                                    </div>
                                                )}
                                                <input ref={fileInput1} type="file" onChange={e => setData('foto_audit1', e.target.files[0])} accept="image/*" className="hidden" />
                                            </div>
                                            
                                            {/* Foto 2 */}
                                            <div className="relative aspect-square rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 flex flex-col items-center justify-center overflow-hidden transition-colors">
                                                {data.foto_audit2 || detailOutlet?.foto_audit2 ? (
                                                    <>
                                                        <img src={data.foto_audit2 ? URL.createObjectURL(data.foto_audit2) : `/storage/${detailOutlet.foto_audit2}`} alt="Audit 2" className="absolute inset-0 w-full h-full object-cover" />
                                                        <div className="absolute inset-0 bg-slate-900/40 flex items-center justify-center gap-1.5 sm:gap-3">
                                                            <button type="button" onClick={(e) => { e.preventDefault(); setZoomedImage(data.foto_audit2 ? URL.createObjectURL(data.foto_audit2) : `/storage/${detailOutlet.foto_audit2}`) }} className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <EyeIcon className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                            </button>
                                                            <button type="button" onClick={(e) => { e.preventDefault(); fileInput2.current.click() }} className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <PencilIcon className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                            </button>
                                                            {data.foto_audit2 && (
                                                                <button type="button" onClick={(e) => { e.preventDefault(); setData('foto_audit2', null); if(fileInput2.current) fileInput2.current.value=''; }} className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 hover:bg-rose-500/80 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                    <TrashIcon className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                                </button>
                                                            )}
                                                        </div>
                                                    </>
                                                ) : (
                                                    <div onClick={() => fileInput2.current.click()} className="absolute inset-0 flex flex-col items-center justify-center cursor-pointer">
                                                        <CameraIcon className="w-5 h-5 text-indigo-400 mb-1" />
                                                        <span className="text-[9px] font-semibold text-indigo-600">Foto 2</span>
                                                    </div>
                                                )}
                                                <input ref={fileInput2} type="file" onChange={e => setData('foto_audit2', e.target.files[0])} accept="image/*" className="hidden" />
                                            </div>
                                            
                                            {/* Foto 3 */}
                                            <div className="relative aspect-square rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 flex flex-col items-center justify-center overflow-hidden transition-colors">
                                                {data.foto_audit3 || detailOutlet?.foto_audit3 ? (
                                                    <>
                                                        <img src={data.foto_audit3 ? URL.createObjectURL(data.foto_audit3) : `/storage/${detailOutlet.foto_audit3}`} alt="Audit 3" className="absolute inset-0 w-full h-full object-cover" />
                                                        <div className="absolute inset-0 bg-slate-900/40 flex items-center justify-center gap-1.5 sm:gap-3">
                                                            <button type="button" onClick={(e) => { e.preventDefault(); setZoomedImage(data.foto_audit3 ? URL.createObjectURL(data.foto_audit3) : `/storage/${detailOutlet.foto_audit3}`) }} className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <EyeIcon className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                            </button>
                                                            <button type="button" onClick={(e) => { e.preventDefault(); fileInput3.current.click() }} className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                <PencilIcon className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                            </button>
                                                            {data.foto_audit3 && (
                                                                <button type="button" onClick={(e) => { e.preventDefault(); setData('foto_audit3', null); if(fileInput3.current) fileInput3.current.value=''; }} className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 hover:bg-rose-500/80 flex items-center justify-center text-white backdrop-blur-sm transition-all">
                                                                    <TrashIcon className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                                </button>
                                                            )}
                                                        </div>
                                                    </>
                                                ) : (
                                                    <div onClick={() => fileInput3.current.click()} className="absolute inset-0 flex flex-col items-center justify-center cursor-pointer">
                                                        <CameraIcon className="w-5 h-5 text-indigo-400 mb-1" />
                                                        <span className="text-[9px] font-semibold text-indigo-600">Foto 3</span>
                                                    </div>
                                                )}
                                                <input ref={fileInput3} type="file" onChange={e => setData('foto_audit3', e.target.files[0])} accept="image/*" className="hidden" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {showNoPhotoWarning && (
                                        <div className="bg-amber-50 border border-amber-200 rounded-xl p-3 flex flex-col gap-2">
                                            <div className="flex items-center gap-2 text-amber-700 font-bold text-[10px]">
                                                <ShieldExclamationIcon className="w-4 h-4 shrink-0" />
                                                <span>Belum ada foto audit yang dilampirkan.</span>
                                            </div>
                                            <p className="text-[9px] text-amber-600">Apakah Anda yakin ingin menyimpan tanpa melampirkan bukti dokumentasi audit satupun?</p>
                                            <div className="flex gap-2 mt-1">
                                                <button type="button" onClick={() => setShowNoPhotoWarning(false)} className="flex-1 h-8 bg-white border border-amber-200 text-amber-700 rounded-lg text-[9px] font-bold">Batal</button>
                                                <button type="button" onClick={() => { setShowNoPhotoWarning(false); proceedSubmit(); }} className="flex-1 h-8 bg-amber-500 text-white rounded-lg text-[9px] font-bold shadow-sm hover:bg-amber-600">Ya, Tetap Simpan</button>
                                            </div>
                                        </div>
                                    )}

                                    <button type="submit" disabled={processing || isGettingLocation} className="w-full h-10 bg-indigo-600 text-white rounded-lg text-xs font-bold shadow-md shadow-indigo-600/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                        {(processing || isGettingLocation) && (
                                            <div className="w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></div>
                                        )}
                                        <span>{(processing || isGettingLocation) ? 'Menyimpan & Mengambil Lokasi...' : 'Simpan Hasil Audit'}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            )}


            {/* Bottom Navigation */}
            <div className="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-100 shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.1)] pb-[env(safe-area-inset-bottom)]">
                <div className="flex items-center justify-around p-2 max-w-2xl mx-auto">
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
                        <div className="relative">
                            <ChartPieIcon className={`w-6 h-6 ${activeTab === 'report' ? 'text-indigo-600 scale-110' : 'scale-100'} transition-transform`} />
                            {allMyReports.length > 0 && (
                                <span className="absolute -top-1.5 -right-2 bg-rose-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded-full shadow-sm">
                                    {allMyReports.length > 99 ? '99+' : allMyReports.length}
                                </span>
                            )}
                        </div>
                        <span className={`text-[10px] font-bold ${activeTab === 'report' ? 'text-indigo-600' : ''}`}>Laporan</span>
                    </button>
                </div>
            </div>

            {/* Discard Changes Modal */}
            {showDiscardModal && (
                <div className="fixed inset-0 z-[70] flex items-center justify-center p-4">
                    <div className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={() => setShowDiscardModal(false)}></div>
                    <div className="relative w-full max-w-sm bg-white rounded-3xl shadow-xl flex flex-col items-center p-6 animate-fade-in z-[71]">
                        <div className="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 mb-4">
                            <ShieldExclamationIcon className="w-8 h-8" />
                        </div>
                        <h4 className="text-sm font-black text-slate-800 text-center mb-2">Buang Perubahan?</h4>
                        <p className="text-[11px] text-slate-500 text-center mb-6 leading-relaxed">
                            Anda memiliki form yang belum disimpan. Yakin ingin membuang semua perubahan?
                        </p>
                        <div className="flex w-full gap-3">
                            <button onClick={() => setShowDiscardModal(false)} className="flex-1 h-11 border border-slate-200 bg-white rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50">Lanjut Edit</button>
                            <button onClick={() => { setShowDiscardModal(false); setDetailOutlet(null); setShowNoPhotoWarning(false); }} className="flex-1 h-11 bg-amber-500 text-white rounded-xl text-xs font-bold shadow-md shadow-amber-500/20 hover:bg-amber-600">Ya, Buang</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Delete Confirmation Modal */}
            {deletingReport && (
                <div className="fixed inset-0 z-[70] flex items-center justify-center p-4">
                    <div className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={() => setDeletingReport(null)}></div>
                    <div className="relative w-full max-w-sm bg-white rounded-3xl shadow-xl flex flex-col items-center p-6 animate-fade-in z-[71]">
                        <div className="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-4">
                            <TrashIcon className="w-8 h-8" />
                        </div>
                        <h4 className="text-sm font-black text-slate-800 text-center mb-2">Hapus Hasil Audit?</h4>
                        <p className="text-[11px] text-slate-500 text-center mb-6 leading-relaxed">
                            Tindakan ini tidak dapat dibatalkan. Hasil audit untuk toko <br/><span className="font-bold text-slate-800">{deletingReport.customer_name}</span> akan dihapus permanen.
                        </p>
                        <div className="flex w-full gap-3">
                            <button onClick={() => setDeletingReport(null)} className="flex-1 h-11 border border-slate-200 bg-white rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button onClick={confirmDeleteReport} className="flex-1 h-11 bg-rose-600 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 hover:bg-rose-700">Ya, Hapus</button>
                        </div>
                    </div>
                </div>
            )}

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

            {/* Logout Modal */}
            {showLogoutModal && (
                <div className="fixed inset-0 z-[70] flex items-center justify-center p-4">
                    <div className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={() => setShowLogoutModal(false)}></div>
                    <div className="relative w-full max-w-sm bg-white rounded-3xl shadow-xl flex flex-col items-center p-6 animate-fade-in z-[71]">
                        <div className="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-4">
                            <ShieldExclamationIcon className="w-8 h-8" />
                        </div>
                        <h4 className="text-sm font-black text-slate-800 text-center mb-2">Ganti Auditor?</h4>
                        <p className="text-[11px] text-slate-500 text-center mb-6 leading-relaxed">
                            Apakah Anda yakin ingin keluar dari identitas auditor saat ini? Data yang belum tersimpan akan hilang.
                        </p>
                        <div className="flex w-full gap-3">
                            <button onClick={() => setShowLogoutModal(false)} className="flex-1 h-11 border border-slate-200 bg-white rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button onClick={confirmLogoutAuditor} className="flex-1 h-11 bg-rose-600 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 hover:bg-rose-700">Ya, Ganti</button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
