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
import MobileLayout from '../../Layouts/MobileLayout';

const actualIcon = L.divIcon({
    html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-slate-500"><path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>`,
    className: 'bg-transparent border-0',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
});

const newIcon = L.divIcon({
    html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-rose-500"><path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>`,
    className: 'bg-transparent border-0',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
});

const deg2rad = (deg: number) => deg * (Math.PI / 180);

const getDistance = (lat1: number, lon1: number, lat2: number, lon2: number) => {
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

interface IndexProps {
    tokoList: any[];
    riwayatPerbaikan: any[];
    sessionSalesCode?: string;
    sessionSalesName?: string;
}

export default function Index({ tokoList = [], riwayatPerbaikan = [], sessionSalesCode, sessionSalesName }: IndexProps) {
    const user = { name: sessionSalesName || sessionSalesCode || 'User' };

    // --- State Management ---
    const [toast, setToast] = useState<{message: string, type: string} | null>(null);
    const toastTimerRef = useRef<NodeJS.Timeout | null>(null);
    const [activeTab, setActiveTab] = useState('toko');
    const [search, setSearch] = useState('');
    const [selectedStatusFilter, setSelectedStatusFilter] = useState('');
    const [detailOutlet, setDetailOutlet] = useState<any>(null);
    const [displayLimit, setDisplayLimit] = useState(30);

    const showToast = (message: string, type = 'success') => {
        if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
        setToast({ message, type });
        toastTimerRef.current = setTimeout(() => setToast(null), type === 'error' ? 5000 : 3000);
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    };

    const clearSearch = () => setSearch('');

    const handleTabSwitch = (tab: string) => {
        setActiveTab(tab);
        setSelectedStatusFilter('');
        setDisplayLimit(30);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // --- Detail/Form Logic ---
    const fileInputRef = useRef<HTMLInputElement>(null);
    const { data, setData, post, processing, reset } = useForm({
        region_code: '',
        area_code: '',
        distributor_code: '',
        sales_code: '',
        customer_code: '',
        latitude: '',
        longitude: '',
        accuracy: '',
        foto: null as File | null,
    });

    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [isGettingLocation, setIsGettingLocation] = useState(false);
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showNoPhotoWarning, setShowNoPhotoWarning] = useState(false);
    const isSubmittingRef = useRef(false);

    // Map & Tracking State
    const [actualLocation, setActualLocation] = useState<{lat: number, lng: number} | null>(null);
    const [trackingTimer, setTrackingTimer] = useState(5);
    const [bestAccuracy, setBestAccuracy] = useState<number | null>(null);
    const watchIdRef = useRef<number | null>(null);
    const intervalRef = useRef<NodeJS.Timeout | null>(null);
    const [gpsError, setGpsError] = useState(false);
    const mapContainerRef = useRef<HTMLDivElement>(null);
    const leafletMapRef = useRef<L.Map | null>(null);
    const markersRef = useRef<{actual: L.Marker | null, baru: L.Marker | null, line: L.Polyline | null}>({ actual: null, baru: null, line: null });

    useEffect(() => {
        if (data.foto instanceof File) {
            const url = URL.createObjectURL(data.foto);
            setPreviewUrl(url);
            return () => URL.revokeObjectURL(url);
        } else {
            setPreviewUrl(null);
        }
    }, [data.foto]);

    // Hardware Back Button Integration using #hash
    useEffect(() => {
        const handleHashChange = () => {
            if (window.location.hash !== '#detail' && detailOutlet) {
                // Diminta tutup via tombol back HP
                if (data.foto) {
                    if (confirm('Foto yang sudah diambil akan hilang. Yakin ingin membatalkan?')) {
                        setDetailOutlet(null);
                    } else {
                        // User batal menutup, kembalikan hash
                        window.history.pushState(null, '', '#detail');
                    }
                } else {
                    setDetailOutlet(null);
                }
            }
        };
        window.addEventListener('popstate', handleHashChange);
        return () => window.removeEventListener('popstate', handleHashChange);
    }, [detailOutlet, data.foto]);

    const openDetail = (outlet: any) => {
        setDetailOutlet(outlet);
        setShowNoPhotoWarning(false);
        window.history.pushState(null, '', '#detail');

        if (fileInputRef.current) fileInputRef.current.value = '';

        const actLat = parseFloat(outlet.latitude);
        const actLng = parseFloat(outlet.longitude);
        setActualLocation(!isNaN(actLat) && !isNaN(actLng) ? { lat: actLat, lng: actLng } : null);
        setBestAccuracy(null);
        setTrackingTimer(30);

        setData({
            region_code: outlet.region_code || '',
            area_code: outlet.area_code || '',
            distributor_code: outlet.distributor_code || '',
            sales_code: outlet.sales_code || sessionSalesCode || '',
            customer_code: outlet.customer_code || '',
            latitude: '',
            longitude: '',
            accuracy: '',
            foto: null,
        });
    };

    const handleCloseDetail = () => {
        if (data.foto) {
            if (!confirm('Foto yang sudah diambil akan hilang. Yakin ingin membatalkan?')) return;
        }
        setDetailOutlet(null);
        if (window.location.hash === '#detail') {
            window.history.back();
        }
    };

    const fetchCurrentLocation = () => {
        if (!navigator.geolocation) {
            showToast('Browser Anda tidak mendukung GPS.', 'error');
            return;
        }

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

                    if (acc <= 15) {
                        if (intervalRef.current) clearInterval(intervalRef.current);
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
                if (intervalRef.current) clearInterval(intervalRef.current);
                if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);
                
                if (localBestAccuracy > 100) {
                    setData(d => ({ ...d, latitude: '', longitude: '', accuracy: '' }));
                    setBestAccuracy(null);
                }

                if (error.code === 1) {
                    showToast('WAJIB: Akses Lokasi ditolak! Izinkan lokasi di pengaturan HP/Browser Anda.', 'error');
                } else if (error.code === 2) {
                    showToast('WAJIB: GPS mati! Harap nyalakan fitur Lokasi (GPS) di HP Anda.', 'error');
                } else if (error.code === 3) {
                    showToast('Sinyal GPS lemah. Harap pindah ke area terbuka.', 'error');
                } else {
                    showToast('Gagal mengambil lokasi. Pastikan GPS menyala dan diizinkan.', 'error');
                }
            },
            { enableHighAccuracy: true, maximumAge: 0, timeout: 30000 }
        );

        let currentTimer = 30;
        setTrackingTimer(currentTimer);

        intervalRef.current = setInterval(() => {
            currentTimer -= 1;
            setTrackingTimer(currentTimer);
            
            if (currentTimer <= 0) {
                if (intervalRef.current) clearInterval(intervalRef.current);
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
            }
        }, 1000);
    };

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

    useEffect(() => {
        if (!detailOutlet || !mapContainerRef.current) return;

        if (!leafletMapRef.current) {
            leafletMapRef.current = L.map(mapContainerRef.current, {
                attributionControl: false,
                zoomControl: true,
                preferCanvas: true,
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

        if (data.latitude && data.longitude) {
            const newLat = parseFloat(data.latitude);
            const newLng = parseFloat(data.longitude);
            
            if (!isNaN(newLat) && !isNaN(newLng)) {
                m.baru = L.marker([newLat, newLng], { icon: newIcon })
                          .bindPopup(`<b class="text-xs">Titik Baru</b><br/><span class="text-[10px]">Akurasi: ${bestAccuracy ? bestAccuracy.toFixed(1) + 'm' : '-'}</span>`)
                          .addTo(map);
                bounds.extend([newLat, newLng]);
            }
        }

        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 18 });
        } else {
            map.setView([-6.2088, 106.8456], 5);
        }

        setTimeout(() => { map.invalidateSize(); }, 500);
    }, [detailOutlet, actualLocation, data.latitude, data.longitude, bestAccuracy]);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) {
            setData('foto', null);
            return;
        }
        if (file.size > 5242880) {
            showToast('Ukuran foto terlalu besar (Maks. 5MB)', 'error');
            if (fileInputRef.current) fileInputRef.current.value = '';
            return;
        }
        setShowNoPhotoWarning(false);
        setData('foto', file);
    };

    const submitForm = (e: React.FormEvent) => {
        e.preventDefault();
        if (isSubmittingRef.current || processing) return;

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
                if (window.location.hash === '#detail') window.history.back();
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

    const displayedOutlets = useMemo(() => {
        let result = activeTab === 'toko' ? tokoList : riwayatPerbaikan;

        if (search) {
            const q = search.toLowerCase();
            result = result.filter(o => 
                String(o.customer_name || '').toLowerCase().includes(q) || 
                String(o.customer_code || '').toLowerCase().includes(q) ||
                String(o.address || '').toLowerCase().includes(q)
            );
        }

        if (activeTab === 'laporan' && selectedStatusFilter) {
            result = result.filter(o => o.status_perbaikan?.toLowerCase() === selectedStatusFilter);
        }
        
        if (activeTab === 'laporan') {
            const statusOrder: Record<string, number> = { pending: 1, rejected: 2, approved: 3 };
            result = [...result].sort((a, b) => {
                const orderA = statusOrder[a.status_perbaikan?.toLowerCase()] || 99;
                const orderB = statusOrder[b.status_perbaikan?.toLowerCase()] || 99;
                return orderA - orderB;
            });
        }

        return result;
    }, [tokoList, riwayatPerbaikan, activeTab, search, selectedStatusFilter]);

    // (Diabaikan sementara agar bisa preview UI tanpa harus login)
    // if (!sessionSalesCode) {
    //     return ( ... );
    // }

    return (
        <MobileLayout user={user} title="Perbaikan Tikor Tim Elite">
            <Head title="Perbaikan Tikor Tim Elite" />
            
            <div className="flex flex-col relative pb-20 w-full animate-fade-in">
                {/* Toast System */}
                {toast && (
                    <div
                        onClick={() => { if (toastTimerRef.current) clearTimeout(toastTimerRef.current); setToast(null); }}
                        className={`fixed top-safe left-4 right-4 z-[100] px-4 py-3 rounded-2xl shadow-lg flex items-center gap-3 text-xs font-bold text-white transition-all cursor-pointer ${toast.type === 'success' ? 'bg-emerald-500 shadow-emerald-500/20' : 'bg-rose-500 shadow-rose-500/20'} mt-2 animate-slide-down`}
                    >
                        {toast.type === 'success' ? <ShieldCheckIcon className="w-6 h-6 shrink-0" /> : <ShieldExclamationIcon className="w-6 h-6 shrink-0" />}
                        <span className="flex-1">{toast.message}</span>
                    </div>
                )}

                {/* Tabs & Search Area */}
                <div className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm shrink-0">
                    <div className="px-4 py-3 pb-2 flex items-center gap-2">
                        <form onSubmit={handleSearchSubmit} className="relative flex-1 flex items-center">
                            <button type="submit" className="absolute left-3 text-gray-400 hover:text-indigo-600 transition-colors">
                                <MagnifyingGlassIcon className="w-5 h-5" />
                            </button>
                            <input value={search} onChange={(e) => setSearch(e.target.value)}
                                   type="search"
                                   placeholder="Cari Toko/Kode..."
                                   className="block w-full pl-10 pr-8 py-2.5 text-sm border border-gray-200 rounded-2xl bg-gray-50 focus:border-indigo-500 focus:bg-white outline-none text-gray-800 transition-all shadow-inner" />
                            {search && (
                                <button type="button" onClick={clearSearch} className="absolute right-3 text-gray-400 hover:text-gray-600 bg-gray-200 rounded-full p-0.5">
                                    <XMarkIcon className="w-3 h-3" />
                                </button>
                            )}
                        </form>
                    </div>

                    <div className="px-4 pb-3 pt-1">
                        <div className="bg-gray-100/80 p-1.5 rounded-2xl flex items-center w-full shadow-inner">
                            <button
                                onClick={() => handleTabSwitch('toko')}
                                className={`flex-1 py-2.5 flex items-center justify-center gap-2 text-[10px] md:text-xs font-black uppercase tracking-wider rounded-xl transition-all duration-300 ${activeTab === 'toko' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'}`}
                            >
                                <BuildingStorefrontOutline className={`w-4 h-4 ${activeTab === 'toko' ? 'stroke-2' : ''}`} />
                                Toko ({tokoList.length})
                            </button>
                            <button
                                onClick={() => handleTabSwitch('laporan')}
                                className={`flex-1 py-2.5 flex items-center justify-center gap-2 text-[10px] md:text-xs font-black uppercase tracking-wider rounded-xl transition-all duration-300 ${activeTab === 'laporan' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'}`}
                            >
                                <ClipboardDocumentListIcon className={`w-4 h-4 ${activeTab === 'laporan' ? 'stroke-2' : ''}`} />
                                Riwayat ({riwayatPerbaikan.length})
                            </button>
                        </div>
                    </div>

                    {activeTab === 'laporan' && (
                        <div className="px-4 pb-3">
                            <select
                                value={selectedStatusFilter}
                                onChange={(e) => setSelectedStatusFilter(e.target.value)}
                                className="w-full bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold rounded-xl px-4 py-2.5 outline-none focus:border-indigo-500 uppercase tracking-wider shadow-sm transition-all appearance-none"
                                style={{ backgroundImage: "url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e\")", backgroundPosition: "right 0.5rem center", backgroundRepeat: "no-repeat", backgroundSize: "1.5em 1.5em" }}
                            >
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>
                    )}
                </div>

                {/* Main List */}
                <main className="flex-1 px-4 pt-4">
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        {displayedOutlets.length > 0 ? displayedOutlets.slice(0, displayLimit).map((outlet, idx) => (
                            <div key={`${outlet.distributor_code}_${outlet.customer_code}_${idx}`} className="border rounded-2xl p-4 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col gap-3.5 transition-all bg-white border-gray-100 hover:border-indigo-100">
                                <div className="flex items-stretch justify-between gap-3">
                                    <div className="flex-1 min-w-0 pr-2">
                                        <h4 className="text-[13px] md:text-sm font-bold text-gray-800 tracking-tight leading-snug truncate">
                                            {outlet.customer_code} - {outlet.customer_name}
                                        </h4>
                                        <div className="flex flex-col gap-1.5 mt-2.5">
                                            <div className="flex items-start gap-1.5 text-[10px] text-gray-500 font-medium leading-tight">
                                                <MapPinIcon className="w-3.5 h-3.5 shrink-0 text-gray-400 mt-0.5" />
                                                <span className="line-clamp-2">{outlet.address || '-'}</span>
                                            </div>
                                            <div className="flex items-center gap-1.5 text-[10px] text-gray-500 font-medium">
                                                <BuildingStorefrontOutline className="w-3.5 h-3.5 shrink-0 text-gray-400" />
                                                <span className="truncate">{outlet.distributor_name || '-'} (Cabang: {outlet.area_code || '-'})</span>
                                            </div>
                                        </div>
                                        {outlet.status_perbaikan && activeTab === 'laporan' && outlet.keterangan_perbaikan && (
                                            <div className={`mt-3 text-[10px] p-2 rounded-lg border font-medium leading-relaxed ${
                                                outlet.status_perbaikan.toLowerCase() === 'rejected'
                                                    ? 'text-rose-700 bg-rose-50 border-rose-100'
                                                    : 'text-gray-600 bg-gray-50 border-gray-100'
                                            }`}>
                                                <span className="font-bold">Info:</span> {outlet.keterangan_perbaikan}
                                            </div>
                                        )}
                                    </div>
                                    
                                    {outlet.status_perbaikan && (
                                        <div className="w-20 shrink-0 flex">
                                            {activeTab === 'toko' ? (
                                                outlet.status_perbaikan.toLowerCase() !== 'rejected' && (
                                                    <div className={`w-full h-full flex flex-col items-center justify-center rounded-xl p-2 font-black uppercase tracking-wider text-center leading-tight ${
                                                        outlet.status_perbaikan.toLowerCase() === 'pending' ? 'bg-amber-50 text-amber-600 border border-amber-200' :
                                                        'bg-emerald-50 text-emerald-600 border border-emerald-200'
                                                    }`}>
                                                        <span className="text-[9px]">{outlet.status_perbaikan.toLowerCase() === 'pending' ? 'Pending' : 'Pernah Perbaikan'}</span>
                                                    </div>
                                                )
                                            ) : (
                                                <div className={`w-full h-full flex flex-col items-center justify-center rounded-xl p-2 font-black uppercase tracking-wider text-center leading-tight ${
                                                    outlet.status_perbaikan.toLowerCase() === 'pending' ? 'bg-amber-50 text-amber-600 border border-amber-200' :
                                                    outlet.status_perbaikan.toLowerCase() === 'approved' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' :
                                                    outlet.status_perbaikan.toLowerCase() === 'rejected' ? 'bg-rose-50 text-rose-600 border border-rose-200' :
                                                    'bg-indigo-50 text-indigo-600 border border-indigo-200'
                                                }`}>
                                                    <span className="text-[9px]">{outlet.status_perbaikan}</span>
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                                {activeTab === 'toko' && (
                                    <div className="flex items-center gap-2 mt-2 pt-3 border-t border-gray-50">
                                        {outlet.latitude && outlet.longitude && (
                                            <a href={`https://www.google.com/maps/dir/?api=1&destination=${outlet.latitude},${outlet.longitude}`} target="_blank" rel="noreferrer" className="flex-1 inline-flex items-center justify-center gap-1.5 h-9 rounded-xl bg-gray-50 text-gray-600 border border-gray-200 text-[10px] font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors">
                                                <MapIcon className="w-3.5 h-3.5" />
                                                Map Lama
                                            </a>
                                        )}
                                        {outlet.status_perbaikan && outlet.status_perbaikan.toLowerCase() === 'pending' ? (
                                            <button disabled className="flex-1 inline-flex items-center justify-center gap-1.5 h-9 rounded-xl bg-gray-100 text-gray-400 border border-gray-200 text-[10px] font-bold uppercase tracking-wider cursor-not-allowed opacity-75">
                                                <InformationCircleIcon className="w-3.5 h-3.5" />
                                                Menunggu ACC
                                            </button>
                                        ) : (
                                            <button onClick={() => openDetail(outlet)} className="flex-1 inline-flex items-center justify-center gap-1.5 h-9 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-black uppercase tracking-wider hover:bg-indigo-100 transition-colors active:scale-95">
                                                <MapPinIcon className="w-3.5 h-3.5" />
                                                Perbaiki
                                            </button>
                                        )}
                                    </div>
                                )}
                            </div>
                        )) : (
                            <div className="bg-transparent py-16 px-6 text-center flex-1 flex flex-col items-center justify-center col-span-full">
                                <div className="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-4 shadow-inner">
                                    <ShieldExclamationIcon className="w-8 h-8" />
                                </div>
                                <h4 className="text-[13px] font-black uppercase tracking-wider text-gray-800">Tidak Ada Data</h4>
                                <p className="text-[11px] text-gray-500 mt-1.5 font-medium max-w-[200px] leading-relaxed">
                                    {activeTab === 'toko' ? 'Semua toko sudah diperbaiki atau daftar kosong.' : 'Belum ada toko yang diperbaiki.'}
                                </p>
                            </div>
                        )}
                    </div>
                    {displayedOutlets.length > displayLimit && (
                        <div className="mt-8 mb-4 text-center">
                            <button
                                onClick={() => setDisplayLimit(prev => prev + 30)}
                                className="px-6 py-3 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-indigo-600 text-[11px] font-black uppercase tracking-wider rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] transition-all flex items-center justify-center gap-2 mx-auto active:scale-95"
                            >
                                Muat Lebih Banyak
                                <span className="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md text-[9px] font-bold">{displayedOutlets.length - displayLimit} tersisa</span>
                            </button>
                        </div>
                    )}
                </main>
            </div>

            {/* Detail Form Modal (Sheet) */}
            {detailOutlet && (
                <div className="fixed inset-0 z-[60] flex items-end sm:items-center justify-center animate-fade-in">
                    {/* Backdrop */}
                    <div 
                        className="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
                        onClick={handleCloseDetail}
                    ></div>
                    
                    {/* Modal Content */}
                    <div className="bg-white w-full sm:max-w-lg sm:rounded-3xl rounded-t-3xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl relative z-10 animate-slide-up sm:animate-zoom-in">
                        <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                        <div className="px-5 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between bg-white relative z-10">
                            <div>
                                <h3 className="text-sm font-black text-gray-800 uppercase tracking-wider">Perbaikan Koordinat</h3>
                                <p className="text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-0.5">{detailOutlet?.customer_name}</p>
                            </div>
                            <button onClick={handleCloseDetail} className="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition-colors active:scale-95">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="flex-1 overflow-y-auto p-5 scrollbar-hide bg-gray-50/30">
                        <form onSubmit={submitForm} className="space-y-6">
                            <div className="space-y-2.5">
                                <div className="flex justify-between items-end">
                                    <label className="text-[10px] font-black text-gray-600 uppercase tracking-widest ml-1">Peta Lokasi Baru</label>
                                    {actualLocation && data.latitude && data.longitude && (
                                        <span className="text-[9px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md tracking-wide">
                                            Jarak: {(getDistance(actualLocation.lat, actualLocation.lng, parseFloat(data.latitude), parseFloat(data.longitude)) * 1000).toFixed(0)} meter
                                        </span>
                                    )}
                                </div>
                                <div
                                    ref={mapContainerRef}
                                    style={{ height: '220px', minHeight: '220px', width: '100%' }}
                                    className="bg-gray-100 rounded-2xl border border-gray-200 overflow-hidden relative z-0 shadow-inner"
                                ></div>
                                {isGettingLocation && (
                                    <div className="flex justify-between items-center text-[10px] text-gray-500 px-1 font-medium">
                                        <span>
                                            {bestAccuracy
                                                ? <>Akurasi GPS terkini: <b className={bestAccuracy <= 15 ? 'text-emerald-500' : (bestAccuracy <= 100 ? 'text-amber-500' : 'text-rose-500')}>{bestAccuracy.toFixed(1)}m</b></>
                                                : 'Menunggu sinyal GPS...'
                                            }
                                        </span>
                                        <span className="text-indigo-500 font-bold animate-pulse tracking-wider">{trackingTimer}s</span>
                                    </div>
                                )}
                                {!isGettingLocation && bestAccuracy !== null && (
                                    <div className="text-[10px] text-gray-500 px-1 font-medium">
                                        Akurasi GPS Akhir: <b className={bestAccuracy <= 15 ? 'text-emerald-500' : (bestAccuracy <= 100 ? 'text-amber-500' : 'text-rose-500')}>{bestAccuracy.toFixed(1)}m</b>
                                    </div>
                                )}
                            </div>

                            <div className="space-y-2.5">
                                <label className="text-[10px] font-black text-gray-600 uppercase tracking-widest ml-1">Koordinat (Latitude & Longitude) <span className="text-rose-500">*</span></label>
                                <div className="flex gap-3">
                                    <input type="text" readOnly value={data.latitude || ''} className="flex-1 bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-xs font-medium text-gray-700 outline-none shadow-sm" placeholder="Latitude..." />
                                    <input type="text" readOnly value={data.longitude || ''} className="flex-1 bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-xs font-medium text-gray-700 outline-none shadow-sm" placeholder="Longitude..." />
                                </div>
                                <button
                                    type="button"
                                    onClick={fetchCurrentLocation}
                                    disabled={isGettingLocation || processing}
                                    className={`w-full flex items-center justify-center gap-2 py-3.5 rounded-xl border-2 font-black text-[11px] uppercase tracking-wider transition-all active:scale-[0.98] disabled:opacity-60 shadow-sm mt-1 ${
                                        gpsError 
                                            ? 'border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100' 
                                            : 'border-indigo-100 bg-indigo-50 text-indigo-600 hover:bg-indigo-100'
                                    }`}
                                >
                                    {isGettingLocation ? (
                                        <><div className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div> Mengunci Sinyal ({trackingTimer}s)...</>
                                    ) : (
                                        <><MapPinIcon className="w-4 h-4" /> {gpsError ? 'Nyalakan GPS & Coba Lagi' : (data.latitude ? 'Deteksi Ulang Lokasi GPS' : 'Deteksi Lokasi GPS Saat Ini')}</>
                                    )}
                                </button>
                            </div>

                            <div className="space-y-2.5">
                                <label className="text-[10px] font-black text-gray-600 uppercase tracking-widest ml-1">Foto Toko <span className="text-rose-500">*</span></label>
                                <div
                                    className={`border-2 border-dashed rounded-2xl p-5 flex flex-col items-center justify-center gap-3 text-center relative overflow-hidden transition-all ${previewUrl ? 'border-indigo-500 bg-indigo-50/50' : (showNoPhotoWarning ? 'border-rose-300 bg-rose-50 shadow-inner' : 'border-gray-300 bg-white hover:bg-gray-50 shadow-sm')}`}
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
                                        <img src={previewUrl} alt="Preview" className="h-40 object-contain rounded-xl shadow-sm" />
                                    ) : (
                                        <>
                                            <div className="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm">
                                                <CameraIcon className="w-7 h-7" />
                                            </div>
                                            <div>
                                                <p className="text-xs font-black text-gray-700 uppercase tracking-wider">Ambil Foto Toko</p>
                                                <p className="text-[10px] text-gray-400 mt-1 font-medium">Foto langsung di tempat · Maks. 5MB</p>
                                            </div>
                                        </>
                                    )}
                                </div>
                                {showNoPhotoWarning && !data.foto && (
                                    <p className="text-[10px] font-bold text-rose-500 ml-1">Foto toko wajib dilampirkan sebelum menyimpan.</p>
                                )}
                            </div>

                            <div className="pt-6 pb-2 flex gap-3">
                                <button
                                    type="button"
                                    onClick={handleCloseDetail}
                                    className="flex-[0.8] py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-xs uppercase tracking-wider rounded-2xl transition-colors active:scale-95"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing || !data.latitude || !data.longitude || (bestAccuracy !== null && bestAccuracy > 100)}
                                    className="flex-[1.2] py-4 bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-500/30 font-black text-xs uppercase tracking-wider rounded-2xl transition-all active:scale-95 flex items-center justify-center gap-2 disabled:opacity-60 disabled:shadow-none"
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

            {/* Success Modal */}
            {showSuccessModal && (
                <div className="fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
                    <div className="bg-white rounded-3xl p-8 w-full max-w-sm shadow-2xl animate-zoom-in flex flex-col items-center">
                        <div className="w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 mb-6 border-[8px] border-white shadow-sm">
                            <ShieldCheckIcon className="w-10 h-10" />
                        </div>
                        <h3 className="text-lg font-black text-gray-800 text-center uppercase tracking-wider mb-2">Berhasil!</h3>
                        <p className="text-xs text-gray-500 text-center font-medium leading-relaxed mb-8">
                            Data perbaikan koordinat toko telah disimpan dan sedang direview (Status: Pending).
                        </p>
                        <button onClick={() => setShowSuccessModal(false)} className="w-full py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/30 rounded-2xl font-black text-[11px] uppercase tracking-widest transition-all active:scale-95">
                            Tutup & Lanjutkan
                        </button>
                    </div>
                </div>
            )}
        </MobileLayout>
    );
}
