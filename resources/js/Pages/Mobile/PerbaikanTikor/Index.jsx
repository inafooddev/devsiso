import React, { useState, useEffect, useRef, useMemo } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import {
    MagnifyingGlassIcon, XMarkIcon, MapPinIcon, ShieldCheckIcon,
    InformationCircleIcon, MapIcon, CameraIcon, ArrowPathIcon,
    ArrowTopRightOnSquareIcon, BuildingStorefrontIcon as BuildingStorefrontOutline,
    ClipboardDocumentListIcon
} from '@heroicons/react/24/outline';
import { ShieldExclamationIcon, BuildingStorefrontIcon, CheckCircleIcon } from '@heroicons/react/24/solid';

const deg2rad = (deg) => deg * (Math.PI / 180);

const isValidCoordinate = (lat, lng) => {
    if (lat === null || lat === undefined || lng === null || lng === undefined) return false;
    const nLat = typeof lat === 'number' ? lat : parseFloat(lat);
    const nLng = typeof lng === 'number' ? lng : parseFloat(lng);
    if (isNaN(nLat) || isNaN(nLng)) return false;
    if (nLat === 0 && nLng === 0) return false;
    if (nLat < -90 || nLat > 90 || nLng < -180 || nLng > 180) return false;
    return true;
};

const getDistance = (lat1, lon1, lat2, lon2) => {
    if (!isValidCoordinate(lat1, lon1) || !isValidCoordinate(lat2, lon2)) return null;
    const nLat1 = parseFloat(lat1);
    const nLon1 = parseFloat(lon1);
    const nLat2 = parseFloat(lat2);
    const nLon2 = parseFloat(lon2);
    const R = 6371;
    const dLat = deg2rad(nLat2 - nLat1);
    const dLon = deg2rad(nLon2 - nLon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(nLat1)) * Math.cos(deg2rad(nLat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
};

// Kompresi foto otomatis di browser (Client-Side Canvas)
// Mengecilkan foto mentah 5-10MB menjadi ~200-400KB tanpa menghilangkan ketajaman toko
const compressImage = (file, maxWidth = 1280, quality = 0.75) => {
    return new Promise((resolve) => {
        if (!file || !file.type || !file.type.startsWith('image/')) {
            resolve(file);
            return;
        }
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                let width = img.width;
                let height = img.height;
                if (width > maxWidth) {
                    height = Math.round((height * maxWidth) / width);
                    width = maxWidth;
                }
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                canvas.toBlob((blob) => {
                    if (!blob) {
                        resolve(file);
                        return;
                    }
                    const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                        type: 'image/jpeg',
                        lastModified: Date.now(),
                    });
                    resolve(compressedFile);
                }, 'image/jpeg', quality);
            };
            img.onerror = () => resolve(file);
        };
        reader.onerror = () => resolve(file);
    });
};

class PageErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null, errorInfo: null, copied: false };
    }
    static getDerivedStateFromError(error) {
        return { hasError: true, error };
    }
    componentDidCatch(error, errorInfo) {
        console.error('Page crash caught by PageErrorBoundary:', error, errorInfo);
        this.setState({ errorInfo });
    }
    copyError = () => {
        const text = `ERROR PERBAIKAN TIKOR:\n${this.state.error?.toString()}\n\nStack:\n${this.state.error?.stack || ''}\n\nComponent Stack:\n${this.state.errorInfo?.componentStack || ''}`;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.setState({ copied: true });
                setTimeout(() => this.setState({ copied: false }), 3000);
            });
        }
    };
    render() {
        if (this.state.hasError) {
            const errorMsg = this.state.error?.message || this.state.error?.toString() || 'Unknown Error';
            const errorStack = this.state.error?.stack || this.state.errorInfo?.componentStack || '';

            return (
                <div className="w-full min-h-screen bg-slate-50 flex flex-col items-center justify-center p-5 text-center">
                    <div className="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-3 border border-rose-200 shadow-sm">
                        <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h2 className="text-base font-black text-slate-800 mb-1">Terjadi Kendala pada Halaman</h2>
                    <p className="text-xs text-slate-500 mb-4 max-w-xs leading-relaxed">
                        Aplikasi mendeteksi kendala teknis. Rincian error ditampilkan di bawah untuk mempermudah perbaikan.
                    </p>

                    {/* KOTAK RINCIAN ERROR TERBUKA LANGSUNG */}
                    <div className="w-full max-w-sm bg-rose-50 border border-rose-200 rounded-2xl p-3.5 text-left mb-4 shadow-sm">
                        <div className="flex items-center justify-between mb-1.5">
                            <span className="text-[10px] font-black uppercase text-rose-700 tracking-wider">Rincian Error:</span>
                            <button
                                type="button"
                                onClick={this.copyError}
                                className="text-[10px] bg-white border border-rose-300 text-rose-700 font-bold px-2 py-0.5 rounded-md hover:bg-rose-100 transition-colors flex items-center gap-1"
                            >
                                {this.state.copied ? '✓ Tersalin' : 'Salin Pesan'}
                            </button>
                        </div>
                        <div className="text-xs font-mono font-bold text-rose-900 bg-white/80 p-2.5 rounded-xl border border-rose-200/80 break-words">
                            {errorMsg}
                        </div>
                        {errorStack && (
                            <details className="mt-2 text-[9px] text-slate-500">
                                <summary className="cursor-pointer hover:underline text-rose-600 font-bold">Lihat Stack Trace Lengkap</summary>
                                <pre className="mt-1.5 p-2 bg-slate-900 text-slate-100 rounded-lg overflow-x-auto whitespace-pre-wrap font-mono text-[8px] max-h-40">
                                    {errorStack}
                                </pre>
                            </details>
                        )}
                    </div>

                    <div className="flex flex-col gap-2 w-full max-w-sm">
                        <button
                            type="button"
                            onClick={() => window.location.reload()}
                            className="w-full py-3.5 bg-indigo-600 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-md hover:bg-indigo-700 active:scale-95 transition-all"
                        >
                            Muat Ulang Halaman
                        </button>
                    </div>
                </div>
            );
        }
        return this.props.children;
    }
}

export default function Index(props) {
    return (
        <PageErrorBoundary>
            <PerbaikanTikorContent {...props} />
        </PageErrorBoundary>
    );
}

function PerbaikanTikorContent({ tokoList = [], riwayatPerbaikan = [], sessionSalesCode, sessionSalesName }) {
    const [showLogoutModal, setShowLogoutModal] = useState(false);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const [loginSalesCode, setLoginSalesCode] = useState('');
    const [isLoginLoading, setIsLoginLoading] = useState(false);
    const [isGpsReady, setIsGpsReady] = useState(false);
    const [checkingGps, setCheckingGps] = useState(true);
    const [gpsBlockReason, setGpsBlockReason] = useState('');
    const [toast, setToast] = useState(null);
    const toastTimerRef = useRef(null);

    const showToast = (message, type = 'success') => {
        if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
        setToast({ message, type });
        toastTimerRef.current = setTimeout(() => setToast(null), type === 'error' ? 5000 : 3000);
    };

    const handleLogin = (e) => {
        e.preventDefault();
        if (!loginSalesCode || isLoginLoading) return;
        setIsLoginLoading(true);
        router.post('/mobile/perbaikan-tikor/login', { sales_code: loginSalesCode }, {
            onError: (errors) => {
                setIsLoginLoading(false);
                if (errors.sales_code) {
                    showToast(errors.sales_code, 'error');
                }
            },
            onSuccess: () => {
                window.location.href = '/mobile/perbaikan-tikor';
            }
        });
    };

    const handleLogout = () => setShowLogoutModal(true);

    const confirmLogout = () => {
        if (isLoggingOut) return;
        setIsLoggingOut(true);
        router.post('/mobile/perbaikan-tikor/logout', {}, {
            onSuccess: () => {
                window.location.href = '/mobile/perbaikan-tikor';
            },
            onError: () => setIsLoggingOut(false),
        });
    };

    // --- Filtering and Display Logic ---
    const [activeTab, setActiveTab] = useState('toko');
    const [search, setSearch] = useState('');
    const [selectedStatusFilter, setSelectedStatusFilter] = useState('');
    const [detailOutlet, setDetailOutlet] = useState(null);
    const [displayLimit, setDisplayLimit] = useState(30);

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        if (document.activeElement) document.activeElement.blur();
    };

    const handleSearchChange = (e) => {
        setSearch(e.target.value);
        setDisplayLimit(30);
    };

    const clearSearch = () => {
        setSearch('');
        setDisplayLimit(30);
    };

    const handleTabSwitch = (tab) => {
        setActiveTab(tab);
        setSelectedStatusFilter('');
        setDisplayLimit(30);
        window.scrollTo({ top: 0, behavior: 'smooth' });
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
    const [isCapturing, setIsCapturing] = useState(false);
    const [captureStatus, setCaptureStatus] = useState(null); // 'locating' | 'compressing' | 'success' | 'error'
    const [captureError, setCaptureError] = useState('');
    const [captureTimestamp, setCaptureTimestamp] = useState(null);
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showNoPhotoWarning, setShowNoPhotoWarning] = useState(false);
    const isSubmittingRef = useRef(false);

    // Titik lama toko
    const [actualLocation, setActualLocation] = useState(null);

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
        setCaptureStatus(null);
        setCaptureError('');
        setCaptureTimestamp(null);
        setIsCapturing(false);

        if (fileInputRef.current) fileInputRef.current.value = '';

        const actLat = outlet.latitude;
        const actLng = outlet.longitude;
        const hasValidAct = isValidCoordinate(actLat, actLng);
        setActualLocation(hasValidAct ? { lat: parseFloat(actLat), lng: parseFloat(actLng) } : null);

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

    // Memicu pembukaan kamera belakang HP langsung
    const triggerCamera = () => {
        if (isCapturing || processing) return;
        setShowNoPhotoWarning(false);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
            fileInputRef.current.click();
        }
    };

    // ALUR BUNDLED ANTI-FRAUD:
    // Begitu foto selesai diambil, GPS seketika membaca titik koordinat di tempat.
    // Foto & GPS terkunci dalam satu aksi tunggal.
    const handlePhotoCaptured = async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        setIsCapturing(true);
        setCaptureStatus('locating');
        setCaptureError('');

        if (!navigator.geolocation) {
            setIsCapturing(false);
            setCaptureStatus('error');
            setCaptureError('Browser HP Anda tidak mendukung fitur GPS.');
            showToast('Browser HP Anda tidak mendukung fitur GPS.', 'error');
            if (fileInputRef.current) fileInputRef.current.value = '';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const acc = position.coords.accuracy;
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Batas akurasi maksimal 100m
                if (acc > 100) {
                    setIsCapturing(false);
                    setCaptureStatus('error');
                    const errText = `Akurasi sinyal GPS terlalu lemah (${Math.round(acc)}m). Batas maksimal 100m. Harap berada di area terbuka di depan toko lalu ulangi.`;
                    setCaptureError(errText);
                    showToast(errText, 'error');
                    if (fileInputRef.current) fileInputRef.current.value = '';
                    return;
                }

                // Proses kompresi foto di memori HP (sangat cepat & hemat memori)
                setCaptureStatus('compressing');
                try {
                    const compressedFile = await compressImage(file, 1280, 0.75);
                    const now = Date.now();
                    setCaptureTimestamp(now);

                    setData(prev => ({
                        ...prev,
                        latitude: lat.toString(),
                        longitude: lng.toString(),
                        accuracy: Math.round(acc).toString(),
                        foto: compressedFile,
                    }));

                    setIsCapturing(false);
                    setCaptureStatus('success');
                    showToast(`Foto & Titik GPS berhasil dikunci! (Akurasi: ${Math.round(acc)}m)`, 'success');
                } catch (err) {
                    setIsCapturing(false);
                    setCaptureStatus('error');
                    setCaptureError('Gagal memproses gambar foto toko. Silakan coba lagi.');
                    showToast('Gagal memproses gambar foto toko.', 'error');
                    if (fileInputRef.current) fileInputRef.current.value = '';
                }
            },
            (error) => {
                setIsCapturing(false);
                setCaptureStatus('error');
                let errMsg = 'Gagal mengunci titik GPS saat foto diambil.';
                if (error.code === 1) {
                    errMsg = 'Akses Lokasi ditolak! Pastikan izin lokasi (GPS) aktif di browser Anda.';
                } else if (error.code === 2) {
                    errMsg = 'GPS HP tidak aktif! Harap nyalakan fitur Lokasi (GPS) di HP Anda.';
                } else if (error.code === 3) {
                    errMsg = 'Sinyal GPS timeout. Pastikan Anda berada di luar ruangan / area terbuka.';
                }
                setCaptureError(errMsg);
                showToast(errMsg, 'error');
                if (fileInputRef.current) fileInputRef.current.value = '';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    };

    const submitForm = (e) => {
        e.preventDefault();
        if (isSubmittingRef.current || processing || isCapturing) return;

        if (!data.foto || !data.latitude || !data.longitude) {
            setShowNoPhotoWarning(true);
            showToast('Foto toko dan titik GPS wajib diambil bersamaan di depan toko.', 'error');
            return;
        }

        // Validasi TTL (Anti-Cheat): Maksimal 5 menit sejak foto & koordinat diambil
        if (captureTimestamp && (Date.now() - captureTimestamp > 5 * 60 * 1000)) {
            showToast('Waktu perbaikan kedaluwarsa (> 5 menit). Silakan ambil foto & titik ulang di depan toko.', 'error');
            return;
        }

        // Cek jika status toko sudah Approved
        if (detailOutlet?.status_perbaikan?.toLowerCase() === 'approved') {
            const confirmRetry = window.confirm('Toko ini sudah pernah diperbaiki dan approved. Yakin ingin mengajukan perbaikan ulang?');
            if (!confirmRetry) return;
        }

        isSubmittingRef.current = true;

        post('/mobile/perbaikan-tikor', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                setDetailOutlet(null);
                reset();
                isSubmittingRef.current = false;
                setShowSuccessModal(true);
            },
            onError: (errors) => {
                isSubmittingRef.current = false;
                let errorMsg = 'Gagal menyimpan.';
                if (errors && typeof errors === 'object') {
                    errorMsg = Object.values(errors).flat().join(' | ') || 'Gagal menyimpan. Pastikan data terisi benar.';
                } else if (typeof errors === 'string') {
                    errorMsg = errors;
                }
                showToast(errorMsg, 'error');
                setCaptureError(errorMsg);
            }
        });
    };

    const handleCloseDetail = () => {
        if (data.foto) {
            if (!confirm('Foto dan titik yang sudah dikunci akan hilang. Yakin ingin membatalkan?')) return;
        }
        setDetailOutlet(null);
    };

    // Filter daftar toko
    const displayedOutlets = useMemo(() => {
        let result = activeTab === 'toko' ? (tokoList || []) : (riwayatPerbaikan || []);

        // Apply Search (Hanya nama toko dan kode toko)
        if (search && search.trim() !== '') {
            const q = search.toLowerCase().trim();
            result = result.filter(o => 
                String(o.customer_name || '').toLowerCase().includes(q) || 
                String(o.customer_code || '').toLowerCase().includes(q)
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
                if (error.code === 1) {
                    setGpsBlockReason('Akses Lokasi Ditolak. Harap izinkan akses lokasi di pengaturan browser Anda.');
                } else if (error.code === 2) {
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

            {/* Toast System */}
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
                <div className="px-4 pb-2 flex items-center gap-2">
                    <form onSubmit={handleSearchSubmit} className="relative flex-1 flex items-center">
                        <button type="submit" className="absolute left-3 text-slate-400 hover:text-indigo-600">
                            <MagnifyingGlassIcon className="w-5 h-5" />
                        </button>
                        <input value={search} onChange={handleSearchChange}
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

                {search && search.trim() !== '' && (
                    <div className="px-4 pb-2.5 flex items-center justify-between text-[11px] font-bold text-slate-500 animate-fade-in">
                        <span>Ditemukan: <b className="text-indigo-600 font-black">{displayedOutlets.length}</b> toko</span>
                        <button type="button" onClick={clearSearch} className="text-[10px] text-slate-400 hover:text-rose-500 uppercase tracking-wider underline">
                            Hapus Filter
                        </button>
                    </div>
                )}

                {/* Tabs */}
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

                {/* Status Filter */}
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
                                            <CameraIcon className="w-3.5 h-3.5" />
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

            {/* Detail Form Modal (LIGHTWEIGHT & ANTI-FRAUD) */}
            {detailOutlet && (
                <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/40 backdrop-blur-sm sm:p-4 animate-fade-in">
                    <div className="bg-white w-full sm:max-w-lg sm:rounded-3xl rounded-t-3xl max-h-[92vh] overflow-hidden flex flex-col shadow-2xl relative animate-slide-up sm:animate-zoom-in border border-slate-100">
                        <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                        <div className="px-5 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 relative z-10">
                            <div>
                                <h3 className="text-sm font-black text-slate-800 uppercase tracking-wider">Perbaikan Titik Toko</h3>
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{detailOutlet.customer_name}</p>
                            </div>
                            <button onClick={handleCloseDetail} className="w-8 h-8 rounded-full bg-slate-200/50 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="flex-1 overflow-y-auto p-5 scrollbar-hide">
                            <form onSubmit={submitForm} className="space-y-4">
                                
                                {/* Ringkasan Info Toko */}
                                <div className="bg-slate-50 border border-slate-200/70 rounded-2xl p-3.5 space-y-1.5 text-xs text-slate-600">
                                    <div className="flex justify-between items-start">
                                        <span className="font-bold text-slate-800 text-[11px]">{detailOutlet.customer_code}</span>
                                        <span className="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                                            Cabang: {detailOutlet.area_code || '-'}
                                        </span>
                                    </div>
                                    <p className="text-[11px] text-slate-600 leading-snug">{detailOutlet.address || 'Alamat tidak tersedia'}</p>
                                    
                                    {/* Titik Lama jika ada */}
                                    {actualLocation && (
                                        <div className="pt-2 mt-2 border-t border-slate-200/60 flex items-center justify-between text-[10px]">
                                            <span className="text-slate-500">Titik Lama: <b className="font-mono text-slate-700">{actualLocation.lat.toFixed(6)}, {actualLocation.lng.toFixed(6)}</b></span>
                                            <a
                                                href={`https://www.google.com/maps/search/?api=1&query=${actualLocation.lat},${actualLocation.lng}`}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="inline-flex items-center gap-1 text-indigo-600 font-bold hover:underline"
                                            >
                                                Peta Lama <ArrowTopRightOnSquareIcon className="w-3 h-3" />
                                            </a>
                                        </div>
                                    )}
                                </div>

                                {/* Hidden input camera */}
                                <input
                                    type="file"
                                    accept="image/*"
                                    capture="environment"
                                    ref={fileInputRef}
                                    onChange={handlePhotoCaptured}
                                    className="hidden"
                                />

                                {/* KARTU STATUS FOTO & GPS TUNGGAL (ANTI-FRAUD) */}
                                <div className="space-y-2">
                                    <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1 flex items-center justify-between">
                                        <span>Bukti Foto & Titik GPS <span className="text-rose-500">*</span></span>
                                        <span className="text-[9px] text-slate-400 font-normal">Wajib di depan toko</span>
                                    </label>

                                    {/* 1. STATE BELUM MENGAMBIL FOTO & TITIK */}
                                    {!data.foto && !isCapturing && (
                                        <div className="border-2 border-dashed border-slate-200 bg-slate-50 rounded-2xl p-5 flex flex-col items-center justify-center gap-3 text-center transition-all">
                                            <div className="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm">
                                                <CameraIcon className="w-7 h-7" />
                                            </div>
                                            <div>
                                                <h4 className="text-xs font-black text-slate-800">Ambil Foto & Kunci Titik Toko</h4>
                                                <p className="text-[10px] text-slate-500 mt-1 max-w-[260px] leading-relaxed">
                                                    Berdirilah tepat di depan toko. Kamera akan terbuka dan titik GPS terkunci otomatis saat foto diambil.
                                                </p>
                                            </div>

                                            {captureError && (
                                                <div className="w-full bg-rose-50 border border-rose-200 rounded-xl p-2.5 text-rose-600 text-[10px] font-bold leading-tight">
                                                    {captureError}
                                                </div>
                                            )}

                                            <button
                                                type="button"
                                                onClick={triggerCamera}
                                                className="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-md shadow-indigo-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                                            >
                                                <CameraIcon className="w-4 h-4" />
                                                Buka Kamera & Kunci Titik
                                            </button>
                                        </div>
                                    )}

                                    {/* 2. STATE SEDANG MENGUNCI GPS / PROSES FOTO */}
                                    {isCapturing && (
                                        <div className="border-2 border-indigo-200 bg-indigo-50/40 rounded-2xl p-6 flex flex-col items-center justify-center gap-3 text-center animate-pulse">
                                            <div className="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                                            <div>
                                                <h4 className="text-xs font-black text-indigo-900">
                                                    {captureStatus === 'locating' ? 'Mengunci Koordinat GPS di Lokasi...' : 'Memproses & Mengompresi Foto...'}
                                                </h4>
                                                <p className="text-[10px] text-indigo-600 mt-1">Harap tetap berada di posisi toko, jangan menutup aplikasi.</p>
                                            </div>
                                        </div>
                                    )}

                                    {/* 3. STATE SUKSES: FOTO + GPS TERKUNCI SATU PAKET */}
                                    {data.foto && !isCapturing && (
                                        <div className="space-y-3 animate-fade-in">
                                            {/* Preview Foto */}
                                            <div className="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 shadow-sm max-h-56 flex items-center justify-center">
                                                {previewUrl && (
                                                    <img src={previewUrl} alt="Foto Toko" className="w-full h-56 object-cover" />
                                                )}
                                                <div className="absolute top-2 right-2 bg-emerald-500/95 backdrop-blur-sm text-white px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider flex items-center gap-1 shadow-sm">
                                                    <CheckCircleIcon className="w-3.5 h-3.5" />
                                                    Foto Valid
                                                </div>
                                            </div>

                                            {/* Kartu Status Koordinat GPS Ringan (Tanpa Peta Berat) */}
                                            <div className="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-3.5 space-y-2">
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center gap-1.5 text-emerald-800 font-black text-xs">
                                                        <MapPinIcon className="w-4 h-4 text-emerald-600" />
                                                        Titik Baru Terkunci
                                                    </div>
                                                    <span className="text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-emerald-600 text-white">
                                                        Akurasi: {data.accuracy}m
                                                    </span>
                                                </div>

                                                <div className="grid grid-cols-2 gap-2 text-[10px] font-mono text-slate-700 bg-white/80 p-2 rounded-xl border border-emerald-100">
                                                    <div>
                                                        <span className="text-slate-400 block text-[9px] font-sans">Latitude</span>
                                                        <b>{parseFloat(data.latitude).toFixed(6)}</b>
                                                    </div>
                                                    <div>
                                                        <span className="text-slate-400 block text-[9px] font-sans">Longitude</span>
                                                        <b>{parseFloat(data.longitude).toFixed(6)}</b>
                                                    </div>
                                                </div>

                                                {/* Informasi Jarak jika toko memiliki titik lama */}
                                                {actualLocation && (() => {
                                                    const dist = getDistance(actualLocation.lat, actualLocation.lng, data.latitude, data.longitude);
                                                    if (dist === null) return null;
                                                    const meters = Math.round(dist * 1000);
                                                    return (
                                                        <div className="flex items-center justify-between text-[10px] pt-1 border-t border-emerald-200/60 text-slate-600">
                                                            <span>Jarak dari titik lama:</span>
                                                            <b className={meters > 100 ? 'text-amber-600' : 'text-emerald-700'}>{meters} meter</b>
                                                        </div>
                                                    );
                                                })()}

                                                {/* Link Cek di Google Maps Luar (0% Beban di browser HP) */}
                                                <div className="pt-1 flex items-center justify-between text-[10px]">
                                                    <a
                                                        href={`https://www.google.com/maps/search/?api=1&query=${data.latitude},${data.longitude}`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="inline-flex items-center gap-1 text-emerald-700 font-bold hover:underline"
                                                    >
                                                        Cek Lokasi di Google Maps <ArrowTopRightOnSquareIcon className="w-3 h-3" />
                                                    </a>
                                                    <span className="text-[9px] text-slate-400">
                                                        {captureTimestamp ? new Date(captureTimestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB' : ''}
                                                    </span>
                                                </div>
                                            </div>

                                            {/* Tombol Foto Ulang jika SE ingin mengulang */}
                                            <button
                                                type="button"
                                                onClick={triggerCamera}
                                                className="w-full py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-colors"
                                            >
                                                <ArrowPathIcon className="w-3.5 h-3.5" />
                                                Foto Ulang & Kunci Ulang Titik
                                            </button>
                                        </div>
                                    )}

                                    {showNoPhotoWarning && !data.foto && (
                                        <p className="text-[10px] font-bold text-rose-500 ml-1">
                                            Foto toko dan titik GPS wajib diambil sebelum simpan perbaikan.
                                        </p>
                                    )}

                                    {captureError && data.foto && (
                                        <div className="bg-rose-50 border border-rose-200 rounded-xl p-2.5 text-rose-700 text-[10px] font-bold leading-tight">
                                            {captureError}
                                        </div>
                                    )}
                                </div>

                                {/* Submit Actions */}
                                <div className="pt-4 flex gap-3 border-t border-slate-100">
                                    <button
                                        type="button"
                                        onClick={handleCloseDetail}
                                        className="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-colors active:scale-[0.98]"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing || isCapturing || !data.foto || !data.latitude || !data.longitude}
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
