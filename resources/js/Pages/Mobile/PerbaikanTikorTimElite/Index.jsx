import React, { useState, useEffect, useRef, useMemo } from 'react';
import { Head, router, useForm, Link } from '@inertiajs/react';
import {
    MagnifyingGlassIcon, XMarkIcon, MapPinIcon, ShieldCheckIcon,
    InformationCircleIcon, MapIcon, CameraIcon, ArrowLeftIcon,
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

export default function Index({ tokoList = [], riwayatPerbaikan = [], sessionSalesCode, sessionSalesName }) {
    const [showLogoutModal, setShowLogoutModal] = useState(false);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const [loginSalesCode, setLoginSalesCode] = useState('');
    const [isLoginLoading, setIsLoginLoading] = useState(false);
    const [isGpsReady, setIsGpsReady] = useState(false);
    const [checkingGps, setCheckingGps] = useState(true);
    const [gpsBlockReason, setGpsBlockReason] = useState('');
    const [toast, setToast] = useState(null);
    const toastTimerRef = useRef(null);

    // FIX #10: Toast timer cleared before each new toast to prevent stale timer conflicts
    const showToast = (message, type = 'success') => {
        if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
        setToast({ message, type });
        toastTimerRef.current = setTimeout(() => setToast(null), type === 'error' ? 5000 : 3000);
    };

    // FIX #24: Login button has loading guard to prevent double-submit
    const handleLogin = (e) => {
        e.preventDefault();
        if (!loginSalesCode || isLoginLoading) return;
        setIsLoginLoading(true);
        router.post('/mobile/perbaikan-tikor-tim-elite/login', { sales_code: loginSalesCode }, {
            onError: (errors) => {
                setIsLoginLoading(false);
                if (errors.sales_code) {
                    showToast(errors.sales_code, 'error');
                }
            },
            onSuccess: () => {
                window.location.href = '/mobile/perbaikan-tikor-tim-elite';
            }
        });
    };

    const handleLogout = () => setShowLogoutModal(true);

    // FIX #8: Logout button has loading guard to prevent double-submit
    const confirmLogout = () => {
        if (isLoggingOut) return;
        setIsLoggingOut(true);
        router.post('/mobile/perbaikan-tikor-tim-elite/logout', {}, {
            onSuccess: () => {
                window.location.href = '/mobile/perbaikan-tikor-tim-elite';
            },
            onError: () => setIsLoggingOut(false),
        });
    };

    // --- Filtering and Display Logic ---
    const [activeTab, setActiveTab] = useState('toko');
    const [search, setSearch] = useState('');
    const [selectedStatusFilter, setSelectedStatusFilter] = useState(''); // FIX #20: moved above early return

    const [detailOutlet, setDetailOutlet] = useState(null);
    const [displayLimit, setDisplayLimit] = useState(30);

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        if (document.activeElement) document.activeElement.blur();
    };

    const clearSearch = () => {
        setSearch('');
    };

    // FIX #2, #3: Reset status filter and display limit when switching tabs
    const handleTabSwitch = (tab) => {
        setActiveTab(tab);
        setSelectedStatusFilter('');
        setDisplayLimit(30);
        window.scrollTo({ top: 0, behavior: 'smooth' }); // FIX #23
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
        accuracy: '',
        foto: null,
    });

    const [previewUrl, setPreviewUrl] = useState(null);
    const [isGettingLocation, setIsGettingLocation] = useState(false);
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    // FIX #4: showNoPhotoWarning is now properly set to true on failed submit
    const [showNoPhotoWarning, setShowNoPhotoWarning] = useState(false);
    const isSubmittingRef = useRef(false);

    // Map & Tracking State
    const [actualLocation, setActualLocation] = useState(null);
    const [trackingTimer, setTrackingTimer] = useState(5);
    const [bestAccuracy, setBestAccuracy] = useState(null);
    const watchIdRef = useRef(null);
    const intervalRef = useRef(null);
    const [gpsError, setGpsError] = useState(false);
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
        setDetailOutlet(outlet);
        setShowNoPhotoWarning(false);

        if (fileInputRef.current) fileInputRef.current.value = '';

        const actLat = outlet.latitude;
        const actLng = outlet.longitude;
        setActualLocation(actLat && actLng ? { lat: parseFloat(actLat), lng: parseFloat(actLng) } : null);
        setBestAccuracy(null);
        setTrackingTimer(30);

        setData({
            region_code: outlet.region_code || '',
            area_code: outlet.area_code || '',
            distributor_code: outlet.distributor_code || '',
            sales_code: outlet.sales_code || sessionSalesCode || '',
            customer_code: outlet.customer_code || '',
            latitude: '',  // FIX #28: start fresh — don't pre-fill old audit coords
            longitude: '',
            accuracy: '',
            foto: null,
        });
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (!file) {
            setData('foto', null);
            return;
        }
        if (file.size > 5242880) {
            showToast('Ukuran foto terlalu besar (Maks. 5MB)', 'error');
            if (fileInputRef.current) fileInputRef.current.value = '';
            return;
        }
        setShowNoPhotoWarning(false); // clear warning when photo is selected
        setData('foto', file);
    };

    // FIX #1 & #7: Clear previous GPS resources before starting new ones
    const fetchCurrentLocation = () => {
        if (!navigator.geolocation) {
            showToast('Browser Anda tidak mendukung GPS.', 'error');
            return;
        }

        // Always clear any existing tracking before starting fresh
        if (intervalRef.current) clearInterval(intervalRef.current);
        if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);

        setIsGettingLocation(true);
        setTrackingTimer(30);
        setBestAccuracy(null);
        setGpsError(false);
        setPreviewUrl(null);
        setData(prev => ({ ...prev, latitude: '', longitude: '', accuracy: '' }));

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
                        longitude: position.coords.longitude.toString(),
                        accuracy: acc.toString()
                    }));

                    // Auto-lock instan jika akurasi sudah sangat baik (<= 15m)
                    if (acc <= 15) {
                        clearInterval(intervalRef.current);
                        if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);
                        setIsGettingLocation(false);
                        setTrackingTimer(0);
                        showToast(`Titik dikunci instan! Akurasi mantap: ${Math.round(acc)}m`, 'success');
                    }
                }
            },
            (error) => {
                setIsGettingLocation(false);
                setGpsError(true);
                clearInterval(intervalRef.current);
                if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);
                
                if (error.code === 1) { // PERMISSION_DENIED
                    showToast('WAJIB: Akses Lokasi ditolak! Izinkan lokasi di pengaturan HP/Browser Anda.', 'error');
                } else if (error.code === 2) { // POSITION_UNAVAILABLE
                    showToast('WAJIB: GPS mati! Harap nyalakan fitur Lokasi (GPS) di HP Anda.', 'error');
                } else if (error.code === 3) { // TIMEOUT
                    showToast('Sinyal GPS lemah. Harap pindah ke area terbuka.', 'error');
                } else {
                    showToast('Gagal mengambil lokasi. Pastikan GPS menyala dan diizinkan.', 'error');
                }
            },
            { enableHighAccuracy: true, maximumAge: 0, timeout: 30000 }
        );

        intervalRef.current = setInterval(() => {
            setTrackingTimer(prev => {
                if (prev <= 1) {
                    clearInterval(intervalRef.current);
                    if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);
                    setIsGettingLocation(false);
                    if (localBestAccuracy === Infinity) {
                        setGpsError(true);
                        showToast('Gagal mendapatkan sinyal GPS. Silakan coba lagi.', 'error');
                        setData(d => ({ ...d, latitude: '', longitude: '', accuracy: '' }));
                        setBestAccuracy(null);
                    } else if (localBestAccuracy > 100) {
                        showToast(`Akurasi ditolak (${Math.round(localBestAccuracy)}m). Minimal akurasi 100m. Silakan cari titik ulang!`, 'error');
                        setData(d => ({ ...d, latitude: '', longitude: '', accuracy: '' }));
                        setBestAccuracy(null);
                    } else {
                        showToast(`Waktu habis. Titik dikunci dengan akurasi: ${Math.round(localBestAccuracy)}m`, 'success');
                    }
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
    };

    // Cleanup GPS & Map on modal close
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
            map.setView([-6.2088, 106.8456], 5);
        }

        // FIX #19: Increased to 500ms for slow devices, more reliable
        setTimeout(() => { map.invalidateSize(); }, 500);
    }, [detailOutlet, actualLocation, data.latitude, data.longitude, bestAccuracy]);

    const submitForm = (e) => {
        e.preventDefault();
        if (isSubmittingRef.current || processing) return;

        // FIX #4: properly activate visual warning AND show toast
        if (!data.foto) {
            setShowNoPhotoWarning(true);
            showToast('Foto wajib dilampirkan sebagai bukti perbaikan.', 'error');
            return;
        }

        isSubmittingRef.current = true;

        post('/mobile/perbaikan-tikor-tim-elite', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                setDetailOutlet(null);
                reset();
                isSubmittingRef.current = false;
                setShowSuccessModal(true);
            },
            onError: () => {
                isSubmittingRef.current = false;
                showToast('Gagal menyimpan. Pastikan data terisi benar.', 'error');
            }
        });
    };

    // FIX #5: "Batal" closes modal with unsaved photo confirmation
    const handleCloseDetail = () => {
        if (data.foto) {
            if (!confirm('Foto yang sudah diambil akan hilang. Yakin ingin membatalkan?')) return;
        }
        setDetailOutlet(null);
    };

    // --- displayedOutlets memo (FIX #20 resolved: selectedStatusFilter now declared before early return) ---
    // ─── displayedOutlets memo ────────────────────────────────────────────────────────────
    const displayedOutlets = useMemo(() => {
        let result = activeTab === 'toko' ? tokoList : riwayatPerbaikan;

        // Apply Search
        if (search) {
            const q = search.toLowerCase();
            result = result.filter(o => 
                (o.customer_name || '').toLowerCase().includes(q) || 
                (o.customer_code || '').toLowerCase().includes(q) ||
                (o.address || '').toLowerCase().includes(q)
            );
        }

        if (activeTab === 'laporan') {
            if (selectedStatusFilter) {
                result = result.filter(o => o.status_perbaikan?.toLowerCase() === selectedStatusFilter);
            }
            const statusOrder = { pending: 1, rejected: 2, approved: 3 };
            result = [...result].sort((a, b) => {
                const orderA = statusOrder[a.status_perbaikan?.toLowerCase()] || 99;
                const orderB = statusOrder[b.status_perbaikan?.toLowerCase()] || 99;
                return orderA - orderB;
            });
        }

        return result;
    }, [tokoList, riwayatPerbaikan, activeTab, search, selectedStatusFilter]);

    // ─── EFFECTS ─────────────────────────────────────────────────────────────────
    
    // Initial GPS Check before Login
    useEffect(() => {
        if (!sessionSalesCode) {
            checkInitialGps();
        }
    }, [sessionSalesCode]);

    const checkInitialGps = () => {
        if (!navigator.geolocation) {
            setCheckingGps(false);
            setGpsBlockReason('Browser Anda tidak mendukung fitur GPS.');
            return;
        }
        
        setCheckingGps(true);
        setGpsBlockReason('');
        
        navigator.geolocation.getCurrentPosition(
            () => {
                setIsGpsReady(true);
                setCheckingGps(false);
            },
            (error) => {
                setCheckingGps(false);
                setIsGpsReady(false);
                if (error.code === 1) { // PERMISSION_DENIED
                    setGpsBlockReason('Akses Lokasi Ditolak. Harap izinkan akses lokasi di pengaturan browser Anda.');
                } else if (error.code === 2) { // POSITION_UNAVAILABLE
                    setGpsBlockReason('GPS mati. Harap nyalakan fitur Lokasi/GPS di HP Anda.');
                } else {
                    setGpsBlockReason('Gagal mendapatkan sinyal GPS. Pastikan berada di area terbuka.');
                }
            },
            { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 }
        );
    };

    // ─── LOGIN SCREEN ────────────────────────────────────────────────────────────
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

                    {checkingGps ? (
                        <div className="flex flex-col items-center justify-center gap-3 py-6 w-full">
                            <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider animate-pulse text-center">Memverifikasi GPS...</p>
                        </div>
                    ) : !isGpsReady ? (
                        <div className="flex flex-col items-center justify-center gap-4 w-full bg-rose-50 border border-rose-200 p-5 rounded-2xl">
                            <ShieldExclamationIcon className="w-10 h-10 text-rose-500 animate-bounce-slow" />
                            <p className="text-xs text-center text-rose-600 font-bold leading-relaxed">
                                {gpsBlockReason}
                            </p>
                            <button
                                type="button"
                                onClick={checkInitialGps}
                                className="w-full py-3 rounded-xl bg-rose-500 text-white font-black text-xs uppercase tracking-wider hover:bg-rose-600 transition-colors shadow-lg shadow-rose-500/30 active:scale-[0.98]"
                            >
                                Cek Ulang GPS
                            </button>
                        </div>
                    ) : (
                        <form onSubmit={handleLogin} className="w-full flex flex-col gap-4 relative animate-fade-in">
                            <div className="relative">
                                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Kode Sales</label>
                                <input
                                    type="text"
                                    value={loginSalesCode}
                                    onChange={(e) => setLoginSalesCode(e.target.value.toUpperCase())}
                                    placeholder="Ketik Kode Sales..."
                                    disabled={isLoginLoading}
                                    className="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-800 bg-slate-50 uppercase disabled:opacity-60 font-bold"
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={!loginSalesCode || isLoginLoading}
                                className={`w-full py-3 rounded-xl text-white font-bold text-sm uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2 ${!loginSalesCode || isLoginLoading ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/30 active:scale-[0.98]'}`}
                            >
                                {isLoginLoading ? (
                                    <><div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Memproses...</>
                                ) : 'Masuk'}
                            </button>
                        </form>
                    )}

                    <div className="mt-8 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">
                        PT INAFOOD © {new Date().getFullYear()}
                    </div>
                </div>
            </div>
        );
    }

    // ─── MAIN APP ────────────────────────────────────────────────────────────────
    return (
        <div className="w-full min-h-screen bg-slate-50 text-slate-800 flex flex-col relative pb-10">
            <Head title="Perbaikan Tikor Toko" />

            {/* Toast System — click to dismiss */}
            {toast && (
                <div
                    onClick={() => { if (toastTimerRef.current) clearTimeout(toastTimerRef.current); setToast(null); }}
                    className={`fixed top-4 left-1/2 -translate-x-1/2 z-[100] px-4 py-2 rounded-xl shadow-lg flex items-center gap-2 text-sm font-bold text-white transition-all cursor-pointer ${toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}`}
                >
                    {toast.type === 'success' ? <ShieldCheckIcon className="w-5 h-5" /> : <ShieldExclamationIcon className="w-5 h-5" />}
                    {toast.message}
                </div>
            )}

            {/* Header */}
            <div className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm shrink-0">
                <header className="px-4 py-3 flex items-center justify-between" style={{ paddingTop: 'calc(0.75rem + env(safe-area-inset-top, 0px))' }}>
                    <div className="flex items-center gap-2.5">
                        <Link href="/mobile/portal" className="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition-colors border border-slate-200 shrink-0">
                            <ArrowLeftIcon className="w-4 h-4 stroke-[2.5]" />
                        </Link>
                        <div className="w-8 h-8 rounded-xl bg-indigo-600/10 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-600/10 shrink-0 hidden sm:flex">
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
                <div className="px-4 pb-3">
                    <div className="bg-slate-100 p-1 rounded-xl flex items-center w-full border border-slate-200/60">
                        <button
                            onClick={() => handleTabSwitch('toko')}
                            className={`flex-1 py-2.5 flex items-center justify-center gap-2 text-[10px] md:text-xs font-black uppercase tracking-wider rounded-lg transition-all duration-300 ${activeTab === 'toko' ? 'bg-white text-indigo-600 shadow-sm shadow-slate-200/50 border border-slate-200/50' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-200/50'}`}
                        >
                            <BuildingStorefrontOutline className={`w-4 h-4 ${activeTab === 'toko' ? 'stroke-2' : ''}`} />
                            Toko ({tokoList.length})
                        </button>
                        <button
                            onClick={() => handleTabSwitch('laporan')}
                            className={`flex-1 py-2.5 flex items-center justify-center gap-2 text-[10px] md:text-xs font-black uppercase tracking-wider rounded-lg transition-all duration-300 ${activeTab === 'laporan' ? 'bg-white text-indigo-600 shadow-sm shadow-slate-200/50 border border-slate-200/50' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-200/50'}`}
                        >
                            <ClipboardDocumentListIcon className={`w-4 h-4 ${activeTab === 'laporan' ? 'stroke-2' : ''}`} />
                            Riwayat ({riwayatPerbaikan.length})
                        </button>
                    </div>
                </div>

                {/* Status Filter — only shown on Perbaikan tab */}
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
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    {displayedOutlets.length > 0 ? displayedOutlets.slice(0, displayLimit).map((outlet) => (
                        <div key={`${outlet.distributor_code}_${outlet.customer_code}`} className="border rounded-2xl p-4 shadow-sm flex flex-col gap-3.5 transition-all bg-white border-slate-100">
                            <div className="flex items-stretch justify-between gap-3">
                                <div className="flex-1 min-w-0 pr-2">
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

                                    {outlet.status_perbaikan && activeTab === 'laporan' && outlet.keterangan_perbaikan && (
                                        <div className={`mt-2.5 text-[9px] p-1.5 rounded-md border font-medium leading-tight ${
                                            outlet.status_perbaikan.toLowerCase() === 'rejected'
                                                ? 'text-rose-600 bg-rose-50 border-rose-100'
                                                : 'text-slate-600 bg-slate-50 border-slate-100'
                                        }`}>
                                            <span className="font-bold">Keterangan:</span> {outlet.keterangan_perbaikan}
                                        </div>
                                    )}
                                </div>
                                
                                {outlet.status_perbaikan && (
                                    <div className="w-[20%] shrink-0 flex">
                                        {activeTab === 'toko' ? (
                                            outlet.status_perbaikan.toLowerCase() !== 'rejected' && (
                                                <div className={`w-full h-full flex flex-col items-center justify-center rounded-xl p-2 font-black uppercase tracking-wider text-center leading-tight shadow-inner ${
                                                    outlet.status_perbaikan.toLowerCase() === 'pending' ? 'bg-amber-500 bg-opacity-75 text-white shadow-amber-200' :
                                                    'bg-emerald-500 bg-opacity-75 text-white shadow-emerald-200'
                                                }`}>
                                                    <span className="text-[9px]">{outlet.status_perbaikan.toLowerCase() === 'pending' ? 'Pending' : 'Pernah Perbaikan'}</span>
                                                </div>
                                            )
                                        ) : (
                                            <div className={`w-full h-full flex flex-col items-center justify-center rounded-xl p-2 font-black uppercase tracking-wider text-center leading-tight shadow-inner ${
                                                outlet.status_perbaikan.toLowerCase() === 'pending' ? 'bg-amber-500 bg-opacity-75 text-white shadow-amber-200' :
                                                outlet.status_perbaikan.toLowerCase() === 'approved' ? 'bg-emerald-500 bg-opacity-75 text-white shadow-emerald-200' :
                                                outlet.status_perbaikan.toLowerCase() === 'rejected' ? 'bg-rose-500 bg-opacity-75 text-white shadow-rose-200' :
                                                'bg-indigo-500 bg-opacity-75 text-white shadow-indigo-200'
                                            }`}>
                                                <span className="text-[9px]">{outlet.status_perbaikan}</span>
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>

                            {/* Action Buttons */}
                            {activeTab === 'toko' && (
                                <div className="flex items-center gap-2 mt-2 pt-3 border-t border-slate-100">
                                    {outlet.latitude && outlet.longitude && (
                                        <a href={`https://www.google.com/maps/dir/?api=1&destination=${outlet.latitude},${outlet.longitude}`} target="_blank" rel="noreferrer" className="flex-1 inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold uppercase tracking-wide hover:bg-emerald-100">
                                            <MapIcon className="w-3.5 h-3.5" />
                                            Map Lama
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
                            )}
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
                            {/* FIX #5: X button uses handleCloseDetail for unsaved photo guard */}
                            <button onClick={handleCloseDetail} className="w-8 h-8 rounded-full bg-slate-200/50 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
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
                                    {/* FIX #9: accuracy info only shown once, in one place */}
                                    {isGettingLocation && (
                                        <div className="flex justify-between items-center text-[10px] text-slate-500 px-1">
                                            <span>
                                                {bestAccuracy
                                                    ? <>Akurasi GPS terkini: <b className={bestAccuracy < 20 ? 'text-emerald-500' : 'text-rose-500'}>{bestAccuracy.toFixed(1)}m</b></>
                                                    : 'Menunggu sinyal GPS...'
                                                }
                                            </span>
                                            <span className="text-indigo-500 font-bold animate-pulse">{trackingTimer}s</span>
                                        </div>
                                    )}
                                    {!isGettingLocation && bestAccuracy && (
                                        <div className="text-[10px] text-slate-500 px-1">
                                            Akurasi GPS: <b className={bestAccuracy < 20 ? 'text-emerald-500' : 'text-rose-500'}>{bestAccuracy.toFixed(1)}m</b>
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
                                        className={`w-full flex items-center justify-center gap-2 py-3 rounded-xl border-2 font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98] disabled:opacity-50 ${
                                            gpsError 
                                                ? 'border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100' 
                                                : 'border-indigo-100 bg-indigo-50 text-indigo-600 hover:bg-indigo-100'
                                        }`}
                                    >
                                        {isGettingLocation ? (
                                            <><div className="w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full animate-spin"></div> Mengunci ({trackingTimer}s)...</>
                                        ) : (
                                            <><MapPinIcon className="w-4 h-4" /> {gpsError ? 'Nyalakan GPS & Coba Lagi' : (data.latitude ? 'Deteksi Ulang Lokasi' : 'Deteksi Lokasi Saat Ini')}</>
                                        )}
                                    </button>
                                </div>

                                {/* Photo Block */}
                                <div className="space-y-2">
                                    <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">Foto Toko <span className="text-rose-500">*</span></label>
                                    <div
                                        className={`border-2 border-dashed rounded-2xl p-4 flex flex-col items-center justify-center gap-2 text-center relative overflow-hidden transition-colors ${previewUrl ? 'border-indigo-500 bg-indigo-50/30' : (showNoPhotoWarning ? 'border-rose-300 bg-rose-50' : 'border-slate-200 bg-slate-50 hover:bg-slate-100')}`}
                                    >
                                        {/* Wajib foto langsung dari kamera belakang, tidak bisa dari galeri */}
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
                                                    <p className="text-[10px] text-slate-400 mt-0.5">Foto langsung di tempat · Maks. 5MB</p>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                    {/* FIX #4: Warning shown properly when showNoPhotoWarning = true */}
                                    {showNoPhotoWarning && !data.foto && (
                                        <p className="text-[10px] font-bold text-rose-500 ml-1">Foto toko wajib dilampirkan sebelum submit.</p>
                                    )}
                                </div>

                                {/* Submit Actions */}
                                <div className="pt-4 flex gap-3 border-t border-slate-100">
                                    {/* FIX #5: Batal uses handleCloseDetail for unsaved photo guard */}
                                    <button
                                        type="button"
                                        onClick={handleCloseDetail}
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

            {/* Logout Confirmation Modal */}
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
                            <button
                                onClick={() => setShowLogoutModal(false)}
                                disabled={isLoggingOut}
                                className="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98] disabled:opacity-60"
                            >
                                Batal
                            </button>
                            {/* FIX #8: Logout button protected with isLoggingOut state */}
                            <button
                                onClick={confirmLogout}
                                disabled={isLoggingOut}
                                className="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white shadow-md shadow-rose-500/20 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-60"
                            >
                                {isLoggingOut ? (
                                    <><div className="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Keluar...</>
                                ) : 'Ya, Keluar'}
                            </button>
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
