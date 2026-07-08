import React, { useState, useEffect, useRef } from 'react';
import { useForm } from '@inertiajs/react';
import { CameraIcon, XMarkIcon, MapPinIcon } from '@heroicons/react/24/outline';
import { CheckCircleIcon, ShieldCheckIcon, InformationCircleIcon, ShieldExclamationIcon } from '@heroicons/react/24/solid';
import L from 'leaflet';
import { newIcon, actualIcon } from '../Utils/mapUtils';

interface MapModalProps {
    detailOutlet: any;
    setDetailOutlet: (outlet: any) => void;
    showToast: (msg: string, type?: string) => void;
}

export default function MapModal({ detailOutlet, setDetailOutlet, showToast }: MapModalProps) {
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
    const [localToast, setLocalToast] = useState<{msg: string, type: string} | null>(null);
    const isSubmittingRef = useRef(false);

    const localToastTimerRef = useRef<NodeJS.Timeout | null>(null);

    const displayLocalToast = (msg: string, type: string = 'error') => {
        setLocalToast({ msg, type });
        if (localToastTimerRef.current) clearTimeout(localToastTimerRef.current);
        localToastTimerRef.current = setTimeout(() => {
            setLocalToast((prev) => prev?.msg === msg ? null : prev);
        }, 3500);
    };

    // Map & Tracking State
    const [actualLocation, setActualLocation] = useState<{lat: number, lng: number} | null>(null);
    const [trackingTimer, setTrackingTimer] = useState(5);
    const [bestAccuracy, setBestAccuracy] = useState<number | null>(null);
    const [locationPermission, setLocationPermission] = useState<'prompt' | 'granted' | 'denied'>('prompt');
    const [isGpsOff, setIsGpsOff] = useState(false);
    const watchIdRef = useRef<number | null>(null);
    const intervalRef = useRef<NodeJS.Timeout | null>(null);
    const [gpsError, setGpsError] = useState(false);
    const mapContainerRef = useRef<HTMLDivElement>(null);
    const leafletMapRef = useRef<L.Map | null>(null);
    const markersRef = useRef<{actual: L.Marker | null, baru: L.Marker | null, line: L.Polyline | null}>({ actual: null, baru: null, line: null });

    // Initialize state when detailOutlet opens
    useEffect(() => {
        if (detailOutlet) {
            setShowNoPhotoWarning(false);
            setLocalToast(null);
            setIsGpsOff(false);
            if (fileInputRef.current) fileInputRef.current.value = '';

            const actLat = parseFloat(detailOutlet.latitude);
            const actLng = parseFloat(detailOutlet.longitude);
            setActualLocation(!isNaN(actLat) && !isNaN(actLng) ? { lat: actLat, lng: actLng } : null);
            setBestAccuracy(null);
            setTrackingTimer(30);

            setData({
                region_code: detailOutlet.region_code || '',
                area_code: detailOutlet.area_code || '',
                distributor_code: detailOutlet.distributor_code || '',
                sales_code: detailOutlet.sales_code || '',
                customer_code: detailOutlet.customer_code || '',
                latitude: '',
                longitude: '',
                accuracy: '',
                foto: null,
            });
            
            // Check initial permission
            if (navigator.permissions && navigator.permissions.query) {
                navigator.permissions.query({ name: 'geolocation' }).then(result => {
                    setLocationPermission(result.state as any);
                    result.onchange = () => {
                        setLocationPermission(result.state as any);
                        if (result.state === 'granted') setIsGpsOff(false);
                    };
                }).catch(() => {});
            }
        }
    }, [detailOutlet]);

    useEffect(() => {
        if (data.foto instanceof File) {
            const url = URL.createObjectURL(data.foto);
            setPreviewUrl(url);
            return () => URL.revokeObjectURL(url);
        } else {
            setPreviewUrl(null);
        }
    }, [data.foto]);

    // Hardware Back Button Integration using #detail
    useEffect(() => {
        const handleHashChange = () => {
            if (window.location.hash !== '#detail' && detailOutlet) {
                if (data.foto) {
                    if (confirm('Foto yang sudah diambil akan hilang. Yakin ingin membatalkan?')) {
                        isSubmittingRef.current = false;
                        setDetailOutlet(null);
                    } else {
                        window.history.pushState(null, '', '#detail');
                    }
                } else {
                    isSubmittingRef.current = false;
                    setDetailOutlet(null);
                }
            }
        };
        window.addEventListener('popstate', handleHashChange);
        return () => window.removeEventListener('popstate', handleHashChange);
    }, [detailOutlet, data.foto]);

    const handleCloseDetail = () => {
        if (data.foto) {
            if (!confirm('Foto yang sudah diambil akan hilang. Yakin ingin membatalkan?')) return;
        }
        isSubmittingRef.current = false;
        setDetailOutlet(null);
        if (window.location.hash === '#detail') {
            window.history.back();
        }
    };

    const fetchCurrentLocation = () => {
        if (!navigator.geolocation) {
            displayLocalToast('Browser Anda tidak mendukung GPS.', 'error');
            return;
        }

        if (intervalRef.current) clearInterval(intervalRef.current);
        if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);

        setIsGettingLocation(true);
        setTrackingTimer(30);
        setBestAccuracy(null);
        setGpsError(false);
        setIsGpsOff(false);
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
                        displayLocalToast(`Titik dikunci instan! Akurasi mantap: ${Math.round(acc)}m`, 'success');
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
                    setLocationPermission('denied');
                    displayLocalToast('Anda belum mengizinkan akses lokasi pada peramban (browser) ini.', 'error');
                } else if (error.code === 2) {
                    setIsGpsOff(true);
                    displayLocalToast('GPS mati atau sinyal satelit tidak tersedia.', 'error');
                } else if (error.code === 3) {
                    displayLocalToast('Sinyal GPS terlalu lemah (Timeout).', 'error');
                } else {
                    displayLocalToast('Gagal mengambil lokasi. Cek pengaturan GPS Anda.', 'error');
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
                    displayLocalToast('Gagal mendapatkan sinyal GPS. Silakan coba lagi.', 'error');
                    setData(d => ({ ...d, latitude: '', longitude: '', accuracy: '' }));
                    setBestAccuracy(null);
                } else if (localBestAccuracy > 100) {
                    displayLocalToast(`Akurasi ditolak (${Math.round(localBestAccuracy)}m). Minimal akurasi 100m. Silakan cari titik ulang!`, 'error');
                    setData(d => ({ ...d, latitude: '', longitude: '', accuracy: '' }));
                    setBestAccuracy(null);
                } else {
                    displayLocalToast(`Waktu habis. Titik dikunci dengan akurasi: ${Math.round(localBestAccuracy)}m`, 'success');
                }
            }
        }, 1000);
    };

    useEffect(() => {
        if (!detailOutlet) {
            if (intervalRef.current) clearInterval(intervalRef.current);
            if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current);
            if (localToastTimerRef.current) clearTimeout(localToastTimerRef.current);
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

        let actualLatLng: L.LatLng | null = null;
        let newLatLng: L.LatLng | null = null;

        if (actualLocation) {
            actualLatLng = L.latLng(actualLocation.lat, actualLocation.lng);
            // Disabled master marker as requested
            // m.actual = L.marker(actualLatLng, { icon: actualIcon })
            //             .bindPopup(`<b class="text-xs">Titik Awal</b>`)
            //             .addTo(map);
            // bounds.extend(actualLatLng);
        }

        if (data.latitude && data.longitude) {
            const newLat = parseFloat(data.latitude);
            const newLng = parseFloat(data.longitude);
            
            if (!isNaN(newLat) && !isNaN(newLng)) {
                newLatLng = L.latLng(newLat, newLng);
                m.baru = L.marker(newLatLng, { icon: newIcon })
                          .bindPopup(`<b class="text-xs">Titik Baru</b><br/><span class="text-xs">Akurasi: ${bestAccuracy ? bestAccuracy.toFixed(1) + 'm' : '-'}</span>`)
                          .addTo(map);
                bounds.extend(newLatLng);
            }
        }

        // Disabled polyline
        // if (actualLatLng && newLatLng) {
        //     m.line = L.polyline([actualLatLng, newLatLng], { color: '#6366f1', weight: 2, dashArray: '5, 5' }).addTo(map);
        // }

        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 18 });
        } else {
            map.setView([-6.2088, 106.8456], 5);
        }

        setTimeout(() => { map.invalidateSize(); }, 500);
    }, [detailOutlet, actualLocation, data.latitude, data.longitude, bestAccuracy]);

    const processImageWithWatermark = (file: File): Promise<File> => {
        return new Promise((resolve) => {
            const img = new Image();
            const reader = new FileReader();
            
            reader.onload = (e) => {
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    if (!ctx) return resolve(file);
                    
                    let width = img.width;
                    let height = img.height;
                    const maxSize = 1280;
                    
                    if (width > height && width > maxSize) {
                        height = Math.round((height * maxSize) / width);
                        width = maxSize;
                    } else if (height > maxSize) {
                        width = Math.round((width * maxSize) / height);
                        height = maxSize;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    const padding = 15;
                    const boxHeight = 110;
                    const textYStart = height - boxHeight + 30;
                    
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
                    ctx.fillRect(0, height - boxHeight, width, boxHeight);
                    
                    ctx.font = 'bold 20px Arial';
                    ctx.fillStyle = 'white';
                    
                    const now = new Date();
                    const timestamp = now.toLocaleString('id-ID');
                    
                    const coords = data.latitude && data.longitude ? 
                        `${parseFloat(data.latitude).toFixed(6)}, ${parseFloat(data.longitude).toFixed(6)}` : 
                        'Lokasi belum dikunci';
                        
                    const storeInfo = detailOutlet ? `${detailOutlet.customer_name} (${detailOutlet.customer_code})` : '';

                    ctx.fillText(`Waktu: ${timestamp}`, padding, textYStart);
                    ctx.fillText(`Tikor: ${coords}`, padding, textYStart + 30);
                    if(storeInfo) ctx.fillText(`Toko: ${storeInfo}`, padding, textYStart + 60);
                    
                    canvas.toBlob(
                        (blob) => {
                            if (blob) {
                                const watermarkedFile = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now(),
                                });
                                resolve(watermarkedFile);
                            } else {
                                resolve(file);
                            }
                        },
                        'image/jpeg',
                        0.85
                    );
                };
                img.onerror = () => resolve(file);
                if (e.target?.result) img.src = e.target.result as string;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    };

    const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) {
            setData('foto', null);
            return;
        }
        if (file.size > 5242880) {
            displayLocalToast('Ukuran foto terlalu besar (Maks. 5MB)', 'error');
            if (fileInputRef.current) fileInputRef.current.value = '';
            return;
        }
        setShowNoPhotoWarning(false);
        
        displayLocalToast('Menyisipkan koordinat ke foto...', 'success');
        try {
            const watermarkedFile = await processImageWithWatermark(file);
            setData('foto', watermarkedFile);
        } catch (error) {
            setData('foto', file);
        }
    };

    const submitForm = (e: React.FormEvent) => {
        e.preventDefault();
        if (isSubmittingRef.current || processing) return;

        if (!data.foto) {
            setShowNoPhotoWarning(true);
            displayLocalToast('Foto wajib dilampirkan sebagai bukti perbaikan.', 'error');
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
            onError: (errors: any) => {
                isSubmittingRef.current = false;
                const errorMsg = errors && Object.values(errors).length > 0 
                    ? Object.values(errors)[0] as string 
                    : 'Gagal mengirim data. Silakan coba lagi.';
                displayLocalToast(errorMsg, 'error');
            }
        });
    };

    if (!detailOutlet) return (
        <>
            {showSuccessModal && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm animate-fade-in">
                    <div className="bg-white rounded-3xl p-6 w-full max-w-sm flex flex-col items-center text-center shadow-2xl animate-scale-up">
                        <div className="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-4 shadow-inner">
                            <CheckCircleIcon className="w-10 h-10 text-emerald-500" />
                        </div>
                        <h3 className="text-lg font-black text-gray-800 mb-2">Perbaikan Berhasil!</h3>
                        <p className="text-sm text-gray-500 leading-relaxed mb-6">
                            Data koordinat baru dan foto bukti telah berhasil dikirim dan sedang menunggu proses approval dari Admin.
                        </p>
                        <button 
                            onClick={() => setShowSuccessModal(false)}
                            className="w-full h-12 bg-gray-900 text-white rounded-xl font-bold uppercase tracking-wider text-sm hover:bg-gray-800 transition-colors shadow-lg shadow-gray-900/20"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            )}
        </>
    );

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 animate-fade-in">
            <div className="bg-white rounded-3xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden shadow-2xl animate-scale-up relative">
                <div className="flex-none bg-white px-4 py-4 flex items-center gap-3 shadow-sm z-20 border-b border-gray-100">
                    <button onClick={handleCloseDetail} className="p-2 -ml-2 rounded-full hover:bg-gray-100 text-gray-600 transition-colors">
                        <XMarkIcon className="w-6 h-6" />
                    </button>
                    <div className="flex-1 min-w-0">
                        <h2 className="text-sm font-black text-gray-800 truncate leading-tight">{detailOutlet.customer_name}</h2>
                        <p className="text-xs text-gray-500 font-medium truncate">{detailOutlet.customer_code} • {detailOutlet.distributor_name}</p>
                    </div>
                </div>

                {localToast && (
                    <div className="absolute top-[72px] left-4 right-4 z-50 animate-slide-up">
                        <div className={`px-4 py-3 rounded-xl shadow-lg border text-xs font-bold ${
                            localToast.type === 'success' 
                                ? 'bg-emerald-50 border-emerald-200 text-emerald-700' 
                                : 'bg-rose-50 border-rose-200 text-rose-700'
                        } flex items-start gap-2`}>
                            {localToast.type === 'success' ? (
                                <CheckCircleIcon className="w-4 h-4 shrink-0 mt-0.5" />
                            ) : (
                                <InformationCircleIcon className="w-4 h-4 shrink-0 mt-0.5" />
                            )}
                            <span className="leading-snug">{localToast.msg}</span>
                        </div>
                    </div>
                )}

            <div className="flex-1 overflow-y-auto pb-24">
                <div className="relative w-full h-[35vh] min-h-[220px] bg-gray-200 shrink-0 z-10">
                    <div ref={mapContainerRef} className="absolute inset-0 z-0"></div>
                    
                    {!data.latitude && !isGettingLocation && (
                        <div className="absolute inset-0 z-10 bg-gray-900/10 backdrop-blur-[2px] flex items-center justify-center pb-16">
                            <div className="bg-white/95 px-5 py-4 rounded-2xl shadow-xl flex flex-col items-center max-w-[280px] text-center border border-white">
                                <div className="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mb-3">
                                    <MapPinIcon className="w-6 h-6 text-indigo-500" />
                                </div>
                                <p className="text-sm font-bold text-gray-800 mb-1">Mulai Perbaikan Geotag</p>
                                <p className="text-[0.65rem] text-gray-500 leading-relaxed font-medium">Tekan tombol di bawah untuk mencari sinyal satelit &amp; mengunci koordinat Anda.</p>
                            </div>
                        </div>
                    )}
                    
                    <div className="absolute bottom-4 left-4 right-4 z-20 flex flex-col gap-2">
                        {isGettingLocation && (
                            <div className="bg-white/95 backdrop-blur px-4 py-3 rounded-2xl shadow-lg border border-indigo-100 flex flex-col items-center">
                                <div className="flex items-center gap-3 mb-2">
                                    <div className="relative flex h-4 w-4">
                                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                        <span className="relative inline-flex rounded-full h-4 w-4 bg-indigo-500"></span>
                                    </div>
                                    <span className="text-xs font-bold text-indigo-700 uppercase tracking-wider">Mencari Satelit GPS...</span>
                                </div>
                                <div className="w-full bg-indigo-50 rounded-full h-2 mb-2 overflow-hidden">
                                    <div className="bg-indigo-500 h-2 rounded-full transition-all duration-1000 ease-linear" style={{ width: `${(1 - trackingTimer/30) * 100}%` }}></div>
                                </div>
                                <div className="flex items-center justify-between w-full text-[0.65rem] font-bold text-gray-500 uppercase">
                                    <span>Tersisa: {trackingTimer}s</span>
                                    {bestAccuracy && <span>Akurasi: <span className="text-indigo-600">{Math.round(bestAccuracy)}m</span></span>}
                                </div>
                            </div>
                        )}
                        
                        {data.latitude && !isGettingLocation && (
                            <div className="bg-white/95 backdrop-blur px-4 py-3 rounded-2xl shadow-lg border border-emerald-100 flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <ShieldCheckIcon className="w-5 h-5 text-emerald-500" />
                                    <div className="flex flex-col">
                                        <span className="text-[0.7rem] font-black text-gray-800 uppercase tracking-wide">Titik Dikunci</span>
                                        <span className="text-[0.65rem] font-bold text-gray-500">Akurasi: {bestAccuracy ? Math.round(bestAccuracy) : '-'} Meter</span>
                                    </div>
                                </div>
                                <button onClick={fetchCurrentLocation} className="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-[0.65rem] font-bold uppercase tracking-wider transition-colors">
                                    Ulangi
                                </button>
                            </div>
                        )}

                        {!isGettingLocation && !data.latitude && (
                            <div className="w-full relative">
                                {locationPermission === 'denied' ? (
                                    <button
                                        type="button"
                                        onClick={() => displayLocalToast('Anda memblokir izin lokasi sebelumnya. Sentuh ikon Gembok di bilah alamat browser Anda, lalu izinkan Lokasi untuk situs ini.', 'error')}
                                        className="w-full h-12 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl shadow-lg shadow-rose-600/30 flex items-center justify-center gap-2 font-bold uppercase tracking-wider text-[0.75rem] transition-all active:scale-[0.98]"
                                    >
                                        <ShieldExclamationIcon className="w-5 h-5" /> Anda Memblokir Izin Lokasi
                                    </button>
                                ) : isGpsOff ? (
                                    <button
                                        type="button"
                                        onClick={() => {
                                            displayLocalToast('Pastikan ikon Lokasi/GPS di HP Anda sudah menyala, lalu coba lagi.', 'error');
                                            fetchCurrentLocation();
                                        }}
                                        className="w-full h-12 bg-orange-500 hover:bg-orange-600 text-white rounded-2xl shadow-lg shadow-orange-500/30 flex items-center justify-center gap-2 font-bold uppercase tracking-wider text-[0.8rem] transition-all active:scale-[0.98]"
                                    >
                                        <ShieldExclamationIcon className="w-5 h-5" /> Nyalakan GPS & Coba Lagi
                                    </button>
                                ) : locationPermission === 'prompt' ? (
                                    <button
                                        type="button"
                                        onClick={fetchCurrentLocation}
                                        className="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 font-bold uppercase tracking-wider text-[0.8rem] transition-all active:scale-[0.98]"
                                    >
                                        <MapPinIcon className="w-5 h-5" /> Beri Akses Lokasi
                                    </button>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={fetchCurrentLocation}
                                        className="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 font-bold uppercase tracking-wider text-[0.8rem] transition-all active:scale-[0.98]"
                                    >
                                        <MapPinIcon className="w-5 h-5" /> Ambil Titik Lokasi
                                    </button>
                                )}
                            </div>
                        )}
                    </div>
                </div>

                <div className="px-4 pt-6 flex flex-col gap-4">
                    <div className="flex flex-col gap-2">
                        <label className="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center justify-between">
                            <span>Foto Bukti (Wajib)</span>
                            {showNoPhotoWarning && <span className="text-[0.65rem] text-rose-500">Mohon lampirkan foto</span>}
                        </label>
                        <input 
                            type="file" 
                            accept="image/*"
                            capture="environment" 
                            onChange={handleFileChange}
                            className="hidden" 
                            ref={fileInputRef} 
                        />
                        {previewUrl ? (
                            <div className="relative w-full aspect-video rounded-2xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm group">
                                <img src={previewUrl} alt="Preview" className="w-full h-full object-cover" />
                                <button
                                    type="button"
                                    onClick={() => {
                                        if(fileInputRef.current) fileInputRef.current.value = '';
                                        setData('foto', null);
                                    }}
                                    className="absolute top-3 right-3 p-1.5 bg-black/50 backdrop-blur rounded-full text-white hover:bg-rose-500 transition-colors"
                                >
                                    <XMarkIcon className="w-4 h-4" />
                                </button>
                                <div className="absolute bottom-3 right-3">
                                    <button
                                        type="button"
                                        onClick={() => fileInputRef.current?.click()}
                                        className="px-3 py-1.5 bg-black/50 backdrop-blur rounded-lg text-white text-[0.65rem] font-bold uppercase tracking-wider hover:bg-black/70 transition-colors"
                                    >
                                        Ganti Foto
                                    </button>
                                </div>
                            </div>
                        ) : (
                            <button 
                                type="button"
                                onClick={() => fileInputRef.current?.click()} 
                                className={`w-full aspect-video rounded-2xl border-2 border-dashed flex flex-col items-center justify-center gap-3 transition-colors ${
                                    showNoPhotoWarning 
                                    ? 'border-rose-300 bg-rose-50 text-rose-500' 
                                    : 'border-gray-300 bg-gray-50 text-gray-500 hover:bg-gray-100 hover:border-gray-400'
                                }`}
                            >
                                <CameraIcon className={`w-8 h-8 ${showNoPhotoWarning ? 'text-rose-400' : 'text-gray-400'}`} />
                                <div className="flex flex-col items-center">
                                    <span className="text-sm font-bold">Ambil Foto Toko</span>
                                    <span className="text-[0.65rem] font-medium mt-0.5 opacity-80">Gunakan kamera HP (Maks. 5MB)</span>
                                </div>
                            </button>
                        )}
                    </div>
                </div>
            </div>

            <div className="absolute bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-100 shadow-[0_-4px_20px_rgb(0,0,0,0.03)] z-30">
                <button
                    onClick={submitForm}
                    disabled={processing || isGettingLocation || !data.latitude || !data.foto}
                    className="w-full h-12 bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 font-bold uppercase tracking-wider text-sm transition-all active:scale-[0.98] disabled:opacity-50 disabled:active:scale-100 disabled:shadow-none disabled:bg-gray-300"
                >
                    {processing ? (
                        <>
                            <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mengirim...
                        </>
                    ) : 'Kirim Perbaikan'}
                </button>
            </div>
            </div>
        </div>
    );
}
