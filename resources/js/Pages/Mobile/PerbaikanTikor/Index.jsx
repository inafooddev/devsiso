import React, { useState, useEffect, useRef, useMemo } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import {
    MagnifyingGlassIcon, XMarkIcon, MapPinIcon, ShieldCheckIcon,
    AdjustmentsHorizontalIcon, InformationCircleIcon, MapIcon, CameraIcon,
    BuildingStorefrontIcon as BuildingStorefrontOutline, ClipboardDocumentListIcon
} from '@heroicons/react/24/outline';
import { ShieldExclamationIcon, BuildingStorefrontIcon } from '@heroicons/react/24/solid';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const actualIcon = L.divIcon({
    html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-slate-500 drop-shadow-md"><path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>`,
    className: 'bg-transparent border-0',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
});

const newIcon = L.divIcon({
    html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-rose-500 drop-shadow-md"><path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>`,
    className: 'bg-transparent border-0',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
});

const deg2rad = (deg) => deg * (Math.PI / 180);

const getDistance = (lat1, lon1, lat2, lon2) => {
    if (!lat1 || !lon1 || !lat2 || !lon2) return Infinity;
    const R = 6371;
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a = 
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
        Math.sin(dLon / 2) * Math.sin(dLon / 2); 
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)); 
    return R * c;
};

export default function Index({ outlets = [], sessionSalesCode, sessionSalesName }) {
    const [showLogoutModal, setShowLogoutModal] = useState(false);
    const [isFormTouched, setIsFormTouched] = useState(false);
    const [toast, setToast] = useState(null);

    // Login Form State
    const [loginSalesCode, setLoginSalesCode] = useState('');

    const showToast = (message, type = 'success') => {
        setToast({ message, type });
        setTimeout(() => setToast(null), type === 'error' ? 5000 : 3000);
    };

    const handleLogin = (e) => {
        e.preventDefault();
        if (!loginSalesCode) {
            showToast('Silakan isi kode sales terlebih dahulu.', 'error');
            return;
        }
        router.post('/mobile/perbaikan-tikor/login', { sales_code: loginSalesCode }, {
            preserveScroll: true,
            onError: (errors) => {
                if (errors.sales_code) {
                    showToast(errors.sales_code, 'error');
                }
            },
            onSuccess: () => showToast(`Selamat datang, ${loginSalesCode}!`, 'success')
        });
    };

    const handleLogout = () => setShowLogoutModal(true);
    const confirmLogout = () => {
        router.post('/mobile/perbaikan-tikor/logout', {}, {
            preserveScroll: true,
            onSuccess: () => {
                setShowLogoutModal(false);
                showToast('Berhasil keluar dari sesi.', 'success');
            }
        });
    };

    // --- Filtering and Display Logic ---
    const [activeTab, setActiveTab] = useState('toko'); // 'toko' or 'laporan'
    const [search, setSearch] = useState('');
    const [selectedRegion, setSelectedRegion] = useState('');
    const [selectedArea, setSelectedArea] = useState('');
    const [selectedDistributor, setSelectedDistributor] = useState('');
    
    const [appliedSearch, setAppliedSearch] = useState('');
    const [appliedRegion, setAppliedRegion] = useState('');
    const [appliedArea, setAppliedArea] = useState('');
    const [appliedDistributor, setAppliedDistributor] = useState('');

    const [filteredOutlets, setFilteredOutlets] = useState([]);
    const [showFiltersSheet, setShowFiltersSheet] = useState(false);
    const [detailOutlet, setDetailOutlet] = useState(null);
    const [displayLimit, setDisplayLimit] = useState(30);

    const isFiltered = appliedSearch || appliedRegion || appliedArea || appliedDistributor;

    const regions = useMemo(() => [...new Set((outlets || []).map(o => o.region_name).filter(Boolean))].sort(), [outlets]);
    const areas = useMemo(() => [...new Set((outlets || [])
        .filter(o => !selectedRegion || o.region_name === selectedRegion)
        .map(o => o.area_name).filter(Boolean))].sort(), [outlets, selectedRegion]);
    const distributors = useMemo(() => [...new Set((outlets || [])
        .filter(o => (!selectedRegion || o.region_name === selectedRegion) && (!selectedArea || o.area_name === selectedArea))
        .map(o => o.distributor_name).filter(Boolean))].sort(), [outlets, selectedRegion, selectedArea]);

    useEffect(() => {
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
        
        setDisplayLimit(30);
        setFilteredOutlets(result);
    }, [appliedSearch, appliedRegion, appliedArea, appliedDistributor, outlets]);

    const applyFilters = () => {
        setAppliedRegion(selectedRegion);
        setAppliedArea(selectedArea);
        setAppliedDistributor(selectedDistributor);
        setShowFiltersSheet(false);
    };

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        setAppliedSearch(search);
        if (document.activeElement) document.activeElement.blur();
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
    };

    // --- Detail/Form Logic ---
    const fileInputRef = useRef(null);
    const { data, setData, post, processing, reset } = useForm({
        region_code: '',
        area_code: '',
        distributor_code: '',
        sales_code: '',
        customer_code: '',
        latitude: '',
        longitude: '',
        foto: null,
    });

    const [previewUrl, setPreviewUrl] = useState(null);
    const [isGettingLocation, setIsGettingLocation] = useState(false);
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showNoPhotoWarning, setShowNoPhotoWarning] = useState(false);
    const isSubmittingRef = useRef(false);

    // Map & Tracking State
    const [actualLocation, setActualLocation] = useState(null);
    const [trackingTimer, setTrackingTimer] = useState(10);
    const [bestAccuracy, setBestAccuracy] = useState(null);
    const watchIdRef = useRef(null);
    const intervalRef = useRef(null);
    const mapContainerRef = useRef(null);
    const leafletMapRef = useRef(null);
    const markersRef = useRef({ actual: null, baru: null, line: null });

    useEffect(() => {
        if (data.foto instanceof File) {
            const url = URL.createObjectURL(data.foto);
            setPreviewUrl(url);
            return () => URL.revokeObjectURL(url);
        } else {
            setPreviewUrl(null);
        }
    }, [data.foto]);

    const openDetail = (outlet) => {
        setIsFormTouched(false);
        setDetailOutlet(outlet);
        setShowNoPhotoWarning(false);
        
        if (fileInputRef.current) fileInputRef.current.value = '';

        const actLat = outlet.latitude;
        const actLng = outlet.longitude;
        setActualLocation(actLat && actLng ? { lat: parseFloat(actLat), lng: parseFloat(actLng) } : null);
        setBestAccuracy(null);
        setTrackingTimer(10);

        setData({
            region_code: outlet.region_code || '',
            area_code: outlet.area_code || '',
            distributor_code: outlet.distributor_code || '',
            sales_code: outlet.sales_code || sessionSalesCode || '',
            customer_code: outlet.customer_code || '',
            latitude: outlet.audit_latitude || '',
            longitude: outlet.audit_longitude || '',
            foto: null,
        });
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (!file) {
            setData('foto', null);
            return;
        }
        if (file.size > 5242880) { // 5MB
            showToast('Ukuran foto terlalu besar (Maks. 5MB)', 'error');
            if (fileInputRef.current) fileInputRef.current.value = '';
            return;
        }
        setIsFormTouched(true);
        setData('foto', file);
    };

    const fetchCurrentLocation = () => {
        if (!navigator.geolocation) {
            showToast('Browser Anda tidak mendukung GPS.', 'error');
            return;
        }

        setIsGettingLocation(true);
        setTrackingTimer(10);
        setBestAccuracy(null);
        setData(prev => ({ ...prev, latitude: '', longitude: '' }));
        
        let localBestAccuracy = Infinity;

        watchIdRef.current = navigator.geolocation.watchPosition(
            (position) => {
                const acc = position.coords.accuracy;
                if (acc < localBestAccuracy) {
                    localBestAccuracy = acc;
                    setBestAccuracy(acc);
                    setData(prev => ({
                        ...prev,
                        latitude: position.coords.latitude.toString(),
                        longitude: position.coords.longitude.toString()
                    }));
                }
            },
            (error) => {
                console.error("GPS Error:", error);
                if (localBestAccuracy === Infinity) {
                    setIsGettingLocation(false);
                    clearInterval(intervalRef.current);
                    if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);
                    showToast('Gagal mengambil lokasi. Pastikan GPS menyala.', 'error');
                }
            },
            { enableHighAccuracy: true, maximumAge: 0 }
        );

        intervalRef.current = setInterval(() => {
            setTrackingTimer(prev => {
                if (prev <= 1) {
                    clearInterval(intervalRef.current);
                    if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);
                    setIsGettingLocation(false);
                    if (localBestAccuracy === Infinity) {
                        showToast('Gagal mendapatkan sinyal GPS. Silakan coba lagi.', 'error');
                        setData(d => ({ ...d, latitude: '', longitude: '' }));
                        setBestAccuracy(null);
                    } else if (localBestAccuracy > 15) {
                        showToast(`Akurasi ditolak (${Math.round(localBestAccuracy)}m). Minimal akurasi 15m. Silakan ke area terbuka!`, 'error');
                        setData(d => ({ ...d, latitude: '', longitude: '' }));
                        setBestAccuracy(null);
                    } else {
                        showToast(`Pencarian selesai! Titik dikunci (Akurasi: ${Math.round(localBestAccuracy)}m)`, 'success');
                    }
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
    };

    // Cleanup GPS & Map on close
    useEffect(() => {
        if (!detailOutlet) {
            if (intervalRef.current) clearInterval(intervalRef.current);
            if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);
            setIsGettingLocation(false);
            
            if (leafletMapRef.current) {
                leafletMapRef.current.remove();
                leafletMapRef.current = null;
            }
            markersRef.current = { actual: null, baru: null, line: null };
        }
    }, [detailOutlet]);

    // Map Rendering Logic
    useEffect(() => {
        if (!detailOutlet || !mapContainerRef.current) return;

        if (!leafletMapRef.current) {
            leafletMapRef.current = L.map(mapContainerRef.current, {
                attributionControl: false,
                zoomControl: true,
            });
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 20
            }).addTo(leafletMapRef.current);
        }

        const map = leafletMapRef.current;
        const m = markersRef.current;

        if (m.actual) { map.removeLayer(m.actual); m.actual = null; }
        if (m.baru) { map.removeLayer(m.baru); m.baru = null; }
        if (m.line) { map.removeLayer(m.line); m.line = null; }

        const bounds = L.latLngBounds([]);

        if (actualLocation) {
            m.actual = L.marker([actualLocation.lat, actualLocation.lng], { icon: actualIcon })
                        .bindPopup('<b class="text-xs">Titik Lama</b>')
                        .addTo(map);
            bounds.extend([actualLocation.lat, actualLocation.lng]);
        }

        if (data.latitude && data.longitude) {
            const newLat = parseFloat(data.latitude);
            const newLng = parseFloat(data.longitude);
            m.baru = L.marker([newLat, newLng], { icon: newIcon })
                      .bindPopup(`<b class="text-xs">Titik Baru</b><br/><span class="text-[10px]">Akurasi: ${bestAccuracy ? bestAccuracy.toFixed(1) + 'm' : '-'}</span>`)
                      .addTo(map);
            bounds.extend([newLat, newLng]);
            
            if (actualLocation) {
                m.line = L.polyline([[actualLocation.lat, actualLocation.lng], [newLat, newLng]], {
                    color: '#f43f5e',
                    dashArray: '5, 5',
                    weight: 2
                }).addTo(map);
            }
        }

        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 18 });
        } else {
            map.setView([-6.2088, 106.8456], 5); // Default Indonesia
        }

        // Fix map not showing up correctly inside a modal due to sizing issues
        setTimeout(() => {
            map.invalidateSize();
        }, 300);
    }, [detailOutlet, actualLocation, data.latitude, data.longitude, bestAccuracy]);

    const submitForm = (e) => {
        e.preventDefault();
        if (isSubmittingRef.current || processing) return;

        if (!data.foto) {
            showToast('Foto wajib dilampirkan sebagai bukti perbaikan.', 'error');
            return;
        }

        isSubmittingRef.current = true;
        
        post('/mobile/perbaikan-tikor', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                setDetailOutlet(null);
                reset();
                setIsFormTouched(false);
                isSubmittingRef.current = false;
                setShowSuccessModal(true);
            },
            onError: () => {
                isSubmittingRef.current = false;
                showToast('Gagal menyimpan. Pastikan data terisi benar.', 'error');
            }
        });
    };

    if (!sessionSalesCode) {
        return (
            <div className="w-full min-h-screen bg-gradient-to-br from-indigo-50 via-slate-50 to-indigo-100/50 flex items-center justify-center p-6">
                <Head title="Login Sales - Perbaikan Tikor" />
                
                {toast && (
                    <div onClick={() => setToast(null)} className={`fixed top-4 left-1/2 -translate-x-1/2 z-[100] px-4 py-2 rounded-xl shadow-lg flex items-center gap-2 text-sm font-bold text-white transition-all cursor-pointer ${toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}`}>
                        {toast.type === 'success' ? <ShieldCheckIcon className="w-5 h-5" /> : <ShieldExclamationIcon className="w-5 h-5" />}
                        {toast.message}
                    </div>
                )}

                <div className="w-full max-w-sm bg-white/90 backdrop-blur-lg border border-slate-200/50 rounded-3xl shadow-xl p-6 flex flex-col items-center animate-fade-in relative">
                    <div className="w-14 h-14 rounded-2xl bg-indigo-600/10 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-600/10 mb-4 animate-bounce-slow">
                        <MapPinIcon className="w-8 h-8" />
                    </div>
                    <h2 className="text-sm md:text-base font-black uppercase tracking-wider text-slate-900 leading-tight text-center">Perbaikan Tikor Toko</h2>
                    <p className="text-[10px] font-bold text-indigo-600 tracking-widest uppercase mb-6 leading-none text-center">Login Sales</p>
                    
                    <form onSubmit={handleLogin} className="w-full flex flex-col gap-4 relative">
                        <div className="relative">
                            <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Kode Sales</label>
                            <input 
                                type="text"
                                value={loginSalesCode}
                                onChange={(e) => setLoginSalesCode(e.target.value.toUpperCase())}
                                placeholder="Ketik Kode Sales..."
                                className="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-800 bg-slate-50 uppercase"
                            />
                        </div>

                        <button 
                            type="submit" 
                            disabled={!loginSalesCode}
                            className={`w-full py-3 rounded-xl text-white font-bold text-sm uppercase tracking-wider transition-all shadow-md ${!loginSalesCode ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/30'}`}
                        >
                            Masuk
                        </button>
                    </form>

                    <div className="mt-8 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">
                        PT INAFOOD © {new Date().getFullYear()}
                    </div>
                </div>
            </div>
        );
    }

    const [selectedStatusFilter, setSelectedStatusFilter] = useState('');

    const displayedOutlets = useMemo(() => {
        let result = filteredOutlets.filter(o => activeTab === 'toko' ? !o.status_perbaikan : !!o.status_perbaikan);
        
        if (activeTab === 'laporan') {
            if (selectedStatusFilter) {
                result = result.filter(o => o.status_perbaikan?.toLowerCase() === selectedStatusFilter);
            }
            
            const statusOrder = { pending: 1, rejected: 2, approved: 3 };
            result.sort((a, b) => {
                const orderA = statusOrder[a.status_perbaikan?.toLowerCase()] || 99;
                const orderB = statusOrder[b.status_perbaikan?.toLowerCase()] || 99;
                return orderA - orderB;
            });
        }
        
        return result;
    }, [filteredOutlets, activeTab, selectedStatusFilter]);

    return (
        <div className="w-full min-h-screen bg-slate-50 text-slate-800 flex flex-col relative pb-10">
            <Head title="Perbaikan Tikor Toko" />

            {/* Toast System */}
            {toast && (
                <div className={`fixed top-4 left-1/2 -translate-x-1/2 z-[100] px-4 py-2 rounded-xl shadow-lg flex items-center gap-2 text-sm font-bold text-white transition-all ${toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}`}>
                    {toast.type === 'success' ? <ShieldCheckIcon className="w-5 h-5" /> : <ShieldExclamationIcon className="w-5 h-5" />}
                    {toast.message}
                </div>
            )}

            {/* Header */}
            <div className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm shrink-0">
                <header className="px-4 py-3 flex items-center justify-between" style={{ paddingTop: 'calc(0.75rem + env(safe-area-inset-top, 0px))' }}>
                    <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-xl bg-indigo-600/10 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-600/10">
                            <MapPinIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <h1 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-900 leading-tight">Perbaikan Tikor</h1>
                            <p className="text-[8px] font-bold text-indigo-600 tracking-widest uppercase leading-none">
                                Daftar Outlet
                            </p>
                        </div>
                    </div>
                    {sessionSalesCode && (
                        <div className="flex items-center gap-2">
                            <div className="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1.5 rounded-xl shadow-inner">
                                <div className="w-5 h-5 rounded-lg bg-indigo-600 text-white text-[9px] font-black flex items-center justify-center uppercase shrink-0">
                                    {sessionSalesCode.charAt(0)}
                                </div>
                                <span className="text-[10px] font-black text-slate-700 leading-none truncate max-w-[120px]">{sessionSalesName || sessionSalesCode}</span>
                            </div>
                            <button 
                                type="button"
                                onClick={handleLogout}
                                className="p-1.5 rounded-xl text-rose-500 bg-rose-50 hover:bg-rose-100 transition-colors border border-rose-100 shrink-0"
                                title="Keluar"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </div>
                    )}
                </header>

                {/* Search Bar */}
                <div className="px-4 pb-3 flex items-center gap-2">
                    <form onSubmit={handleSearchSubmit} className="relative flex-1 flex items-center">
                        <button type="submit" className="absolute left-3 text-slate-400 hover:text-indigo-600">
                            <MagnifyingGlassIcon className="w-5 h-5" />
                        </button>
                        <input value={search} onChange={(e) => setSearch(e.target.value)}
                               type="search" 
                               placeholder="Cari Toko/Kode..." 
                               className="block w-full pl-10 pr-8 py-2 text-sm md:text-base border border-slate-200 rounded-xl bg-slate-50 focus:border-indigo-500 outline-none text-slate-800" />
                        {search && (
                            <button type="button" onClick={clearSearch} className="absolute right-3 text-slate-400 hover:text-slate-600">
                                <XMarkIcon className="w-4 h-4" />
                            </button>
                        )}
                    </form>
                </div>

                {/* Tabs / Segmented Control */}
                <div className="px-4 pb-4">
                    <div className="bg-slate-100 p-1 rounded-xl flex items-center w-full border border-slate-200/60">
                        <button 
                            onClick={() => setActiveTab('toko')}
                            className={`flex-1 py-2.5 flex items-center justify-center gap-2 text-[10px] md:text-xs font-black uppercase tracking-wider rounded-lg transition-all duration-300 ${activeTab === 'toko' ? 'bg-white text-indigo-600 shadow-sm shadow-slate-200/50 border border-slate-200/50' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-200/50'}`}
                        >
                            <BuildingStorefrontOutline className={`w-4 h-4 ${activeTab === 'toko' ? 'stroke-2' : ''}`} />
                            Toko ({filteredOutlets.filter(o => !o.status_perbaikan).length})
                        </button>
                        <button 
                            onClick={() => setActiveTab('laporan')}
                            className={`flex-1 py-2.5 flex items-center justify-center gap-2 text-[10px] md:text-xs font-black uppercase tracking-wider rounded-lg transition-all duration-300 ${activeTab === 'laporan' ? 'bg-white text-indigo-600 shadow-sm shadow-slate-200/50 border border-slate-200/50' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-200/50'}`}
                        >
                            <ClipboardDocumentListIcon className={`w-4 h-4 ${activeTab === 'laporan' ? 'stroke-2' : ''}`} />
                            Perbaikan ({filteredOutlets.filter(o => !!o.status_perbaikan).length})
                        </button>
                    </div>
                </div>

                {activeTab === 'laporan' && (
                    <div className="px-4 pb-3">
                        <select 
                            value={selectedStatusFilter}
                            onChange={(e) => setSelectedStatusFilter(e.target.value)}
                            className="w-full bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-3 py-2 outline-none focus:border-indigo-500 uppercase tracking-wider"
                        >
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                )}
            </div>

            {/* Main Content */}
            <main className="flex-1 px-4 pt-4">
                {/* Outlets List */}
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    {displayedOutlets.length > 0 ? displayedOutlets.slice(0, displayLimit).map((outlet) => (
                        <div key={`${outlet.distributor_code}_${outlet.customer_code}`} className="border rounded-2xl p-4 shadow-sm flex flex-col gap-3.5 transition-all bg-white border-slate-100">
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex-1 min-w-0">
                                    {outlet.status_perbaikan && (
                                        <div className="flex flex-col gap-1.5 mb-2">
                                            <div className="flex flex-wrap items-center gap-1.5">
                                                <span className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${
                                                    outlet.status_perbaikan.toLowerCase() === 'pending' ? 'bg-amber-50 text-amber-600 border-amber-200' :
                                                    outlet.status_perbaikan.toLowerCase() === 'approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' :
                                                    outlet.status_perbaikan.toLowerCase() === 'rejected' ? 'bg-rose-50 text-rose-600 border-rose-200' :
                                                    'bg-indigo-50 text-indigo-600 border-indigo-200'
                                                }`}>
                                                    Diperbaiki: {outlet.status_perbaikan}
                                                </span>
                                            </div>
                                            {outlet.keterangan_perbaikan && (
                                                <div className={`text-[9px] p-1.5 rounded-md border font-medium leading-tight ${
                                                    outlet.status_perbaikan.toLowerCase() === 'rejected' 
                                                        ? 'text-rose-600 bg-rose-50 border-rose-100'
                                                        : 'text-slate-600 bg-slate-50 border-slate-100'
                                                }`}>
                                                    <span className="font-bold">Keterangan:</span> {outlet.keterangan_perbaikan}
                                                </div>
                                            )}
                                        </div>
                                    )}
                                    <h4 className="text-xs md:text-sm font-black text-slate-800 tracking-tight leading-snug truncate">
                                        {outlet.customer_code} - {outlet.customer_name}
                                    </h4>
                                    
                                    <div className="flex flex-col gap-1 mt-2">
                                        <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium">
                                            <MapPinIcon className="w-3 h-3 shrink-0 text-slate-400" />
                                            <span className="truncate flex-1">{outlet.address || '-'}</span>
                                        </div>
                                        <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium">
                                            <BuildingStorefrontIcon className="w-3 h-3 shrink-0 text-slate-400" />
                                            <span className="truncate flex-1">{outlet.distributor_name || '-'} (Cabang: {outlet.area_code || '-'})</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Action Buttons */}
                            <div className="flex items-center gap-2 mt-2 pt-3 border-t border-slate-100">
                                {outlet.latitude && outlet.longitude && (
                                    <a href={`https://www.google.com/maps/dir/?api=1&destination=${outlet.latitude},${outlet.longitude}`} target="_blank" rel="noreferrer" className="flex-1 inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold uppercase tracking-wide hover:bg-emerald-100">
                                        <MapIcon className="w-3.5 h-3.5" />
                                        Map
                                    </a>
                                )}
                                {outlet.status_perbaikan && outlet.status_perbaikan.toLowerCase() === 'pending' ? (
                                    <button disabled className="flex-1 inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-slate-100 text-slate-400 border border-slate-200 text-[10px] font-bold uppercase tracking-wide cursor-not-allowed opacity-75">
                                        <InformationCircleIcon className="w-3.5 h-3.5" />
                                        Menunggu ACC
                                    </button>
                                ) : (
                                    <button onClick={() => openDetail(outlet)} className="flex-1 inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-bold uppercase tracking-wide hover:bg-indigo-100">
                                        <InformationCircleIcon className="w-3.5 h-3.5" />
                                        Perbaiki Tikor
                                    </button>
                                )}
                            </div>
                        </div>
                    )) : (
                        <div className="bg-white border border-slate-100 rounded-3xl py-12 px-6 text-center shadow-sm flex-1 flex flex-col items-center justify-center col-span-full">
                            <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                <ShieldExclamationIcon className="w-8 h-8" />
                            </div>
                            <h4 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-700">Tidak Ada Data</h4>
                            <p className="text-[10px] text-slate-400 mt-2 font-medium">
                                {activeTab === 'toko' ? 'Semua toko sudah diperbaiki atau daftar kosong.' : 'Belum ada toko yang diperbaiki.'}
                            </p>
                        </div>
                    )}
                </div>

                {displayedOutlets.length > displayLimit && (
                    <div className="mt-6 mb-2 text-center">
                        <button 
                            onClick={() => setDisplayLimit(prev => prev + 30)}
                            className="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 text-[11px] font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 mx-auto"
                        >
                            Muat Lebih Banyak 
                            <span className="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-md text-[9px]">{displayedOutlets.length - displayLimit} tersisa</span>
                        </button>
                    </div>
                )}
            </main>

            {/* Modals & Overlays */}
            
            {/* Detail Form Modal */}
            {detailOutlet && (
                <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/40 backdrop-blur-sm sm:p-4 animate-fade-in">
                    <div className="bg-white w-full sm:max-w-lg sm:rounded-3xl rounded-t-3xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl relative animate-slide-up sm:animate-zoom-in border border-slate-100">
                        <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                        <div className="px-5 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 relative z-10">
                            <div>
                                <h3 className="text-sm font-black text-slate-800 uppercase tracking-wider">Perbaikan Koordinat</h3>
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{detailOutlet.customer_name}</p>
                            </div>
                            <button onClick={() => setDetailOutlet(null)} className="w-8 h-8 rounded-full bg-slate-200/50 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        
                        <div className="flex-1 overflow-y-auto p-5 scrollbar-hide">
                            <form onSubmit={submitForm} className="space-y-5">
                                {/* Map View */}
                                <div className="space-y-2">
                                    <div className="flex justify-between items-end">
                                        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">Peta Lokasi</label>
                                        {actualLocation && data.latitude && data.longitude && (
                                            <span className="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                                                Jarak: {(getDistance(actualLocation.lat, actualLocation.lng, parseFloat(data.latitude), parseFloat(data.longitude)) * 1000).toFixed(0)} meter
                                            </span>
                                        )}
                                    </div>
                                    <div 
                                        ref={mapContainerRef} 
                                        style={{ height: '200px', minHeight: '200px', width: '100%' }}
                                        className="bg-slate-100 rounded-2xl border border-slate-200 overflow-hidden relative z-0"
                                    ></div>
                                    {bestAccuracy && (
                                        <div className="flex justify-between items-center text-[10px] text-slate-500 px-1">
                                            <span>Akurasi GPS: <b className={bestAccuracy < 20 ? 'text-emerald-500' : 'text-rose-500'}>{bestAccuracy.toFixed(1)}m</b></span>
                                            {isGettingLocation && (
                                                <span className="text-indigo-500 font-bold animate-pulse">Mencari yang terbaik... ({trackingTimer}s)</span>
                                            )}
                                        </div>
                                    )}
                                </div>

                                {/* GPS Actions */}
                                <div className="space-y-2">
                                    <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">Koordinat Perbaikan <span className="text-rose-500">*</span></label>
                                    <div className="flex gap-2">
                                        <input type="text" readOnly value={data.latitude || ''} className="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 outline-none" placeholder="Menunggu Latitude..." />
                                        <input type="text" readOnly value={data.longitude || ''} className="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 outline-none" placeholder="Menunggu Longitude..." />
                                    </div>
                                    <button 
                                        type="button" 
                                        onClick={fetchCurrentLocation} 
                                        disabled={isGettingLocation || processing}
                                        className="w-full flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-indigo-100 bg-indigo-50 text-indigo-600 font-bold text-xs uppercase tracking-wider hover:bg-indigo-100 transition-colors active:scale-[0.98] disabled:opacity-50"
                                    >
                                        {isGettingLocation ? (
                                            <><div className="w-3.5 h-3.5 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div> Mengunci ({trackingTimer}s)...</>
                                        ) : (
                                            <><MapPinIcon className="w-4 h-4" /> {data.latitude ? 'Deteksi Ulang Lokasi' : 'Deteksi Lokasi Saat Ini'}</>
                                        )}
                                    </button>
                                </div>

                                {/* Photo Block */}
                                <div className="space-y-2">
                                    <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">Foto Toko <span className="text-rose-500">*</span></label>
                                    <div 
                                        className={`border-2 border-dashed rounded-2xl p-4 flex flex-col items-center justify-center gap-2 text-center relative overflow-hidden transition-colors ${previewUrl ? 'border-indigo-500 bg-indigo-50/30' : (showNoPhotoWarning ? 'border-rose-300 bg-rose-50' : 'border-slate-200 bg-slate-50 hover:bg-slate-100')}`}
                                    >
                                        <input 
                                            type="file" 
                                            accept="image/*" 
                                            capture="environment" 
                                            ref={fileInputRef}
                                            onChange={handleFileChange}
                                            className="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                        />
                                        {previewUrl ? (
                                            <img src={previewUrl} alt="Preview" className="h-32 object-contain rounded-lg" />
                                        ) : (
                                            <>
                                                <div className="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                                    <CameraIcon className="w-6 h-6" />
                                                </div>
                                                <div>
                                                    <p className="text-xs font-bold text-slate-700">Ambil Foto Toko</p>
                                                    <p className="text-[10px] text-slate-400 mt-0.5">Format JPG/PNG, Maks. 5MB</p>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                    {showNoPhotoWarning && !data.foto && (
                                        <p className="text-[10px] font-bold text-rose-500 ml-1">Foto toko wajib dilampirkan sebelum submit.</p>
                                    )}
                                </div>

                                {/* Submit Actions */}
                                <div className="pt-4 flex gap-3 border-t border-slate-100">
                                    <button 
                                        type="button" 
                                        onClick={() => setDetailOutlet(null)}
                                        className="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-colors active:scale-[0.98]"
                                    >
                                        Batal
                                    </button>
                                    <button 
                                        type="submit" 
                                        disabled={processing || isGettingLocation || !data.latitude || !data.longitude}
                                        className="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-500/20 font-bold text-sm rounded-xl transition-colors active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-50"
                                    >
                                        {processing ? (
                                            <><div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Menyimpan...</>
                                        ) : (
                                            'Simpan Perbaikan'
                                        )}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* Logout Modal */}
            {showLogoutModal && (
                <div className="fixed inset-0 z-[60] bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
                    <div className="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl animate-zoom-in">
                        <div className="w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-4 mx-auto">
                            <ShieldExclamationIcon className="w-6 h-6" />
                        </div>
                        <h3 className="text-base font-black text-slate-800 text-center uppercase tracking-wider mb-2">Akhiri Sesi?</h3>
                        <p className="text-xs text-slate-500 text-center font-medium leading-relaxed mb-6">
                            Anda akan keluar dari sesi referensi Sales saat ini. Anda perlu masuk kembali jika ingin mencatat perbaikan.
                        </p>
                        <div className="flex gap-3">
                            <button onClick={() => setShowLogoutModal(false)} className="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98]">Batal</button>
                            <button onClick={confirmLogout} className="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white shadow-md shadow-rose-500/20 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98]">Ya, Keluar</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Success Modal */}
            {showSuccessModal && (
                <div className="fixed inset-0 z-[60] bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
                    <div className="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl animate-zoom-in flex flex-col items-center">
                        <div className="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 mb-4 border-[6px] border-white shadow-sm">
                            <ShieldCheckIcon className="w-8 h-8" />
                        </div>
                        <h3 className="text-base font-black text-slate-800 text-center uppercase tracking-wider mb-2">Berhasil!</h3>
                        <p className="text-xs text-slate-500 text-center font-medium leading-relaxed mb-6">
                            Data perbaikan koordinat toko telah disimpan dengan status "Pending".
                        </p>
                        <button onClick={() => setShowSuccessModal(false)} className="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white shadow-md shadow-emerald-500/20 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98]">
                            Selesai
                        </button>
                    </div>
                </div>
            )}
            
        </div>
    );
}
