import React, { useState, useEffect, useMemo, useRef, useCallback } from 'react';
import { Head, router } from '@inertiajs/react';
import { 
    CameraIcon, MapIcon, InformationCircleIcon, ArrowPathIcon, 
    MagnifyingGlassIcon, XMarkIcon, AdjustmentsHorizontalIcon, 
    ExclamationTriangleIcon, CloudArrowUpIcon, CheckCircleIcon,
    XCircleIcon, PencilSquareIcon, UserCircleIcon, CreditCardIcon,
    ChevronDownIcon, ShieldCheckIcon, ShieldExclamationIcon, ServerIcon,
    DocumentTextIcon
} from '@heroicons/react/24/solid';
import { MapPinIcon, MapIcon as MapOutlineIcon, MagnifyingGlassIcon as MagnifyingGlassOutlineIcon } from '@heroicons/react/24/outline';
import L from 'leaflet';

export default function Index({ offlineMasterData }) {
    // Basic States
    const [isOffline, setIsOffline] = useState(!navigator.onLine);
    const [search, setSearch] = useState('');
    const [selectedRegion, setSelectedRegion] = useState('');
    const [selectedArea, setSelectedArea] = useState('');
    const [selectedBranch, setSelectedBranch] = useState('');
    
    // UI Sheets
    const [showFiltersSheet, setShowFiltersSheet] = useState(false);
    const [showGuideSheet, setShowGuideSheet] = useState(false);
    const [showMapSheet, setShowMapSheet] = useState(false);
    
    // Data States
    const [outletsList, setOutletsList] = useState([]);
    const [cachedOutlets, setCachedOutlets] = useState(offlineMasterData?.outlets || []);
    const [activeOutlet, setActiveOutlet] = useState(null);
    const [detailOutlet, setDetailOutlet] = useState(null);
    const [editingOutlet, setEditingOutlet] = useState(null);
    
    // Edit States
    const [editNamaPemilikToko, setEditNamaPemilikToko] = useState('');
    const [editNamaKtp, setEditNamaKtp] = useState('');
    const [editNikKtp, setEditNikKtp] = useState('');
    const [editNoHp, setEditNoHp] = useState('');
    const [editNamaBank, setEditNamaBank] = useState('');
    const [editNoRekening, setEditNoRekening] = useState('');
    const [editNamaPemilikNorek, setEditNamaPemilikNorek] = useState('');
    const [fotoKtpPreview, setFotoKtpPreview] = useState(null);
    const [fotoKtpFile, setFotoKtpFile] = useState(null);
    const [fotoKtpState, setFotoKtpState] = useState({ isUploading: false, progress: 0, errorMessage: '' });
    const [showBankDropdown, setShowBankDropdown] = useState(false);
    const bankList = [
        'BANK BCA', 'BANK MANDIRI', 'BANK BNI', 'BANK BRI', 'BANK SYARIAH INDONESIA (BSI)',
        'BANK DANAMON', 'BANK CIMB NIAGA', 'BANK PERMATA', 'BANK BTN', 'BANK BUKOPIN',
        'BANK MEGA', 'BANK OCBC NISP', 'BANK MAYBANK', 'BANK BTPN / JENIUS', 'BANK JAGO', 
        'BANK ALLOBANK', 'BANK NEO COMMERCE', 'SEABANK', 'BANK SINARMAS', 
        'BPD JAWA TIMUR (BANK JATIM)', 'BPD JAWA TENGAH (BANK JATENG)', 'BPD JAWA BARAT BANTEN (BJB)', 
        'BPD DKI (BANK DKI)', 'BPD BALI', 'BPD D.I. YOGYAKARTA (BPD DIY)'
    ];
    const filteredBanks = bankList.filter(b => b.toLowerCase().includes(editNamaBank.toLowerCase()));

    // Location States
    const [userLocation, setUserLocation] = useState(null);
    const [userLocationError, setUserLocationError] = useState(false);
    const [isFetchingLocation, setIsFetchingLocation] = useState(true);

    // Toast State
    const [toast, setToast] = useState({ show: false, message: '', type: 'success' });
    const showToast = (message, type = 'success') => {
        setToast({ show: true, message, type });
        setTimeout(() => setToast({ show: false, message: '', type: 'success' }), 3000);
    };

    // Photo States (Active Upload)
    const [fotoDepanPreview, setFotoDepanPreview] = useState(null);
    const [fotoDepanFile, setFotoDepanFile] = useState(null);
    const [fotoDepanState, setFotoDepanState] = useState({ isUploading: false, progress: 0, errorMessage: '' });

    const [fotoDalamPreview, setFotoDalamPreview] = useState(null);
    const [fotoDalamFile, setFotoDalamFile] = useState(null);
    const [fotoDalamState, setFotoDalamState] = useState({ isUploading: false, progress: 0, errorMessage: '' });

    // Derived Master Data
    const regionsList = offlineMasterData?.regions || [];
    const areasList = offlineMasterData?.areas || [];
    const branchesList = offlineMasterData?.branches || [];
    const supervisorsList = offlineMasterData?.supervisors || [];

    const getFilteredAreas = () => {
        if (!selectedRegion) return [];
        return areasList.filter(a => a.region_code === selectedRegion);
    };

    const getFilteredBranches = () => {
        if (!selectedArea) return [];
        const activeSpvCodes = supervisorsList
            .filter(s => s.area_code === selectedArea)
            .map(s => s.supervisor_code);
        return branchesList.filter(b => activeSpvCodes.includes(b.supervisor_code));
    };

    // Helper Functions
    const maskValue = (val) => {
        if (!val) return '-';
        return val.substring(0, val.length - 4).replace(/./g, '*') + val.substring(val.length - 4);
    };

    const getExistingPhotoUrl = (path) => {
        if (!path || path.startsWith('pending')) return '';
        return '/storage/' + path;
    };

    const calculateDistance = (lat1, lon1, lat2, lon2) => {
        if ((lat1 == lat2) && (lon1 == lon2)) return 0;
        const radlat1 = Math.PI * lat1/180;
        const radlat2 = Math.PI * lat2/180;
        const theta = lon1-lon2;
        const radtheta = Math.PI * theta/180;
        let dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
        if (dist > 1) dist = 1;
        dist = Math.acos(dist);
        dist = dist * 180/Math.PI;
        dist = dist * 60 * 1.1515;
        dist = dist * 1.609344;
        return dist;
    };

    const fetchUserLocation = useCallback(() => {
        setIsFetchingLocation(true);
        setUserLocationError(false);
        setOutletsList([]);
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    setUserLocation({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    });
                    setIsFetchingLocation(false);
                },
                (error) => {
                    setUserLocationError(true);
                    setIsFetchingLocation(false);
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        } else {
            setUserLocationError(true);
            setIsFetchingLocation(false);
        }
    }, []);

    // Query Outlets
    useEffect(() => {
        let outlets = [...cachedOutlets];
        
        if (selectedRegion) outlets = outlets.filter(o => o.region_code === selectedRegion);
        if (selectedArea) outlets = outlets.filter(o => o.area_code === selectedArea);
        if (selectedBranch) outlets = outlets.filter(o => o.branch_name === selectedBranch);
        
        if (search) {
            const q = search.toLowerCase();
            outlets = outlets.filter(o => 
                (o.customer_code && o.customer_code.toLowerCase().includes(q)) || 
                (o.customer_name && o.customer_name.toLowerCase().includes(q))
            );
        }

        const isFiltered = selectedRegion || selectedArea || selectedBranch || search;
        
        if (isFiltered) {
            setOutletsList(outlets.slice(0, 100));
        } else if (userLocation) {
            let nearby = [];
            outlets.forEach(o => {
                if (o.latitude && o.longitude) {
                    const dist = calculateDistance(
                        userLocation.latitude, 
                        userLocation.longitude, 
                        parseFloat(o.latitude), 
                        parseFloat(o.longitude)
                    );
                    if (dist <= 10) {
                        nearby.push({...o, distanceToUser: dist});
                    }
                }
            });
            nearby.sort((a, b) => a.distanceToUser - b.distanceToUser);
            setOutletsList(nearby.slice(0, 100));
        } else if (userLocationError) {
            setOutletsList([]);
        } else {
            setOutletsList([]);
        }
    }, [selectedRegion, selectedArea, selectedBranch, search, userLocation, userLocationError, cachedOutlets]);

    useEffect(() => {
        fetchUserLocation();
        const handleOnline = () => {
            setIsOffline(false);
            showToast('Koneksi internet terhubung kembali.', 'success');
        };
        const handleOffline = () => setIsOffline(true);
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);
        return () => {
            window.removeEventListener('online', handleOnline);
            window.removeEventListener('offline', handleOffline);
        };
    }, [fetchUserLocation]);

    const resetAllFilters = () => {
        setSelectedRegion('');
        setSelectedArea('');
        setSelectedBranch('');
        setSearch('');
    };

    const handleFileSelect = (e, type) => {
        const file = e.target.files[0];
        if (!file) return;

        if (type === 'foto_depan') {
            setFotoDepanFile(file);
            setFotoDepanPreview(URL.createObjectURL(file));
        } else if (type === 'foto_dalam') {
            setFotoDalamFile(file);
            setFotoDalamPreview(URL.createObjectURL(file));
        } else if (type === 'foto_ktp') {
            setFotoKtpFile(file);
            setFotoKtpPreview(URL.createObjectURL(file));
        }
    };

    const cancelUpload = () => {
        setActiveOutlet(null);
        setFotoDepanFile(null);
        setFotoDepanPreview(null);
        setFotoDalamFile(null);
        setFotoDalamPreview(null);
    };

    const savePhotos = () => {
        if (!activeOutlet) return;
        if (!fotoDepanFile && !fotoDalamFile) {
            showToast('Silakan pilih/ambil foto terlebih dahulu.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('outlet_id', activeOutlet.id);
        if (fotoDepanFile) formData.append('foto_depan', fotoDepanFile);
        if (fotoDalamFile) formData.append('foto_dalam', fotoDalamFile);
        if (userLocation) {
            formData.append('latitude', userLocation.latitude);
            formData.append('longitude', userLocation.longitude);
        }

        if (fotoDepanFile) setFotoDepanState({ isUploading: true, progress: 50, errorMessage: '' });
        if (fotoDalamFile) setFotoDalamState({ isUploading: true, progress: 50, errorMessage: '' });

        router.post('/mobile/rwo-inertia/upload', formData, {
            forceFormData: true,
            onFinish: () => {
                setFotoDepanState({ isUploading: false, progress: 0, errorMessage: '' });
                setFotoDalamState({ isUploading: false, progress: 0, errorMessage: '' });
            },
            onSuccess: (page) => {
                showToast('Berhasil upload foto.', 'success');
                setCachedOutlets(page.props.offlineMasterData.outlets);
                cancelUpload();
            },
            onError: (err) => {
                console.error(err);
                showToast('Gagal upload foto. Silakan coba lagi.', 'error');
            }
        });
    };

    // Edit Logic
    const startEdit = (outlet) => {
        setEditingOutlet(outlet);
        setEditNamaPemilikToko(outlet.nama_pemilik_toko || '');
        setEditNamaKtp(outlet.nama_ktp || '');
        setEditNikKtp(outlet.nik_ktp || '');
        setEditNoHp(outlet.no_hp || '');
        setEditNamaBank(outlet.nama_bank || '');
        setEditNoRekening(outlet.no_rekening || '');
        setEditNamaPemilikNorek(outlet.nama_pemilik_norek || '');
        setFotoKtpPreview(null);
        setFotoKtpFile(null);
    };

    const cancelEdit = () => {
        setEditingOutlet(null);
        setFotoKtpFile(null);
        setFotoKtpPreview(null);
    };

    const saveEdits = () => {
        if (!editingOutlet) return;
        const formData = new FormData();
        formData.append('outlet_id', editingOutlet.id);
        formData.append('nama_pemilik_toko', editNamaPemilikToko);
        formData.append('nama_ktp', editNamaKtp);
        formData.append('nik_ktp', editNikKtp);
        formData.append('no_hp', editNoHp);
        formData.append('nama_bank', editNamaBank);
        formData.append('no_rekening', editNoRekening);
        formData.append('nama_pemilik_norek', editNamaPemilikNorek);
        
        if (fotoKtpFile) formData.append('foto_ktp', fotoKtpFile);
        if (userLocation) {
            formData.append('latitude', userLocation.latitude);
            formData.append('longitude', userLocation.longitude);
        }

        setFotoKtpState({ isUploading: true, progress: 50, errorMessage: '' });

        router.post('/mobile/rwo-inertia/edit', formData, {
            forceFormData: true,
            onFinish: () => setFotoKtpState({ isUploading: false, progress: 0, errorMessage: '' }),
            onSuccess: (page) => {
                showToast('Data berhasil diperbarui.', 'success');
                setCachedOutlets(page.props.offlineMasterData.outlets);
                cancelEdit();
            },
            onError: (err) => {
                console.error(err);
                showToast('Gagal menyimpan data.', 'error');
            }
        });
    };

    // Map Implementation
    const mapRef = useRef(null);
    useEffect(() => {
        if (!showMapSheet) {
            if (mapRef.current) {
                mapRef.current.remove();
                mapRef.current = null;
            }
            return;
        }

        // Initialize map if it doesn't exist
        if (!mapRef.current) {
            const map = L.map('outletsMap').setView([-6.200000, 106.816666], 11);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);
            mapRef.current = map;
        }
        
        const map = mapRef.current;
        map.eachLayer((layer) => {
            if (layer instanceof L.Marker) map.removeLayer(layer);
        });

        const bounds = L.latLngBounds();
        let hasBounds = false;

        if (userLocation) {
            const userIcon = L.divIcon({
                className: 'custom-div-icon',
                html: '<div style="background-color:#3b82f6;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 0 5px rgba(0,0,0,0.5);"></div>',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });
            L.marker([userLocation.latitude, userLocation.longitude], {icon: userIcon})
                .bindPopup('<b>Lokasi Anda</b>')
                .addTo(map);
            bounds.extend([userLocation.latitude, userLocation.longitude]);
            hasBounds = true;
        }

        outletsList.forEach(outlet => {
            if (outlet.latitude && outlet.longitude) {
                L.marker([outlet.latitude, outlet.longitude])
                    .bindPopup('<b>' + outlet.customer_name + '</b><br/>' + outlet.customer_code)
                    .addTo(map);
                bounds.extend([outlet.latitude, outlet.longitude]);
                hasBounds = true;
            }
        });

        setTimeout(() => {
            map.invalidateSize();
            if (hasBounds) map.fitBounds(bounds, { padding: [30, 30] });
        }, 300);
    }, [showMapSheet, outletsList, userLocation]);

    const isFiltered = selectedRegion || selectedArea || selectedBranch || search;

    return (
        <div className="w-full max-w-md mx-auto min-h-screen bg-slate-50 text-slate-800 flex flex-col shadow-sm border-x border-slate-100 relative">
            <Head title="Sales RWO - Photo Portal" />
            
            {/* Toast Notification */}
            {toast.show && (
                <div className="fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] w-full max-w-[320px] px-4 animate-fade-in-up">
                    <div className={`shadow-xl rounded-2xl p-3.5 text-[11px] font-extrabold flex items-center gap-2 text-white border border-black/10 ${toast.type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'}`}>
                        {toast.type === 'success' ? <CheckCircleIcon className="w-5 h-5 flex-shrink-0" /> : <XCircleIcon className="w-5 h-5 flex-shrink-0" />}
                        <span className="flex-1 leading-snug">{toast.message}</span>
                    </div>
                </div>
            )}

            {/* Header Area */}
            <div className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm shrink-0">
                <header className="px-4 py-3 flex items-center justify-between" style={{ paddingTop: 'calc(0.75rem + env(safe-area-inset-top, 0px))' }}>
                    <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-xl bg-blue-600/10 flex items-center justify-center text-blue-600 shadow-sm shadow-blue-600/10">
                            <CameraIcon className="w-5 h-5 animate-pulse" />
                        </div>
                        <div>
                            <h1 className="text-xs font-black uppercase tracking-wider text-slate-900 leading-tight">Sales RWO</h1>
                            <p className="text-[8px] font-bold text-blue-600 tracking-widest uppercase leading-none">Photo Portal</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-[9px] font-bold tracking-wider uppercase transition-all duration-300 ${isOffline ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200'}`}>
                            <span className={`w-1.5 h-1.5 rounded-full ${isOffline ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'}`}></span>
                            <span>{isOffline ? 'Offline' : 'Online'}</span>
                        </div>
                        <button onClick={() => setShowMapSheet(true)} className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border border-indigo-500/20 bg-indigo-500/5 text-indigo-600 text-[9px] font-extrabold tracking-wider uppercase hover:bg-indigo-500/10">
                            <MapIcon className="w-3.5 h-3.5" />
                            <span>Peta</span>
                        </button>
                        <button onClick={() => setShowGuideSheet(true)} className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border border-blue-600/20 bg-blue-600/5 text-blue-600 text-[9px] font-extrabold tracking-wider uppercase hover:bg-blue-600/10">
                            <InformationCircleIcon className="w-3.5 h-3.5" />
                            <span>Panduan</span>
                        </button>
                        {isFiltered && (
                            <button onClick={resetAllFilters} className="text-rose-500 hover:text-rose-600">
                                <ArrowPathIcon className="w-4 h-4" />
                            </button>
                        )}
                    </div>
                </header>

                <div className="px-4 pb-3 flex items-center gap-2">
                    <div className="relative flex-1 flex items-center">
                        <span className="absolute left-3 text-slate-400">
                            <MagnifyingGlassIcon className="w-5 h-5" />
                        </span>
                        <input value={search} onChange={(e) => setSearch(e.target.value)}
                               type="text" 
                               placeholder="Cari nama / kode toko..." 
                               className="block w-full pl-9 pr-8 py-2 text-sm text-gray-900 border border-gray-300 rounded-xl bg-gray-50 focus:ring-blue-500 focus:border-blue-500 outline-none" />
                        {search && (
                            <button onClick={() => setSearch('')} className="absolute right-3 text-slate-400 hover:text-slate-600">
                                <XMarkIcon className="w-4 h-4" />
                            </button>
                        )}
                    </div>
                    
                    <button onClick={() => setShowFiltersSheet(true)} 
                            className={`w-10 h-10 rounded-xl border flex items-center justify-center transition-all duration-200 relative ${isFiltered ? 'bg-blue-600 text-white shadow-md border-blue-600' : 'bg-slate-50 text-slate-600 border-slate-200'}`}>
                        <AdjustmentsHorizontalIcon className="w-5 h-5" />
                        {(selectedRegion || selectedArea || selectedBranch) && (
                            <span className="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full animate-bounce"></span>
                        )}
                    </button>
                </div>
            </div>

            <main className="flex-1 px-4 py-4 space-y-4 flex flex-col bg-slate-50/50">
                {isOffline && (
                    <div className="bg-amber-50 border border-amber-200 rounded-2xl p-3 shadow-sm flex items-start gap-2.5 animate-pulse">
                        <ExclamationTriangleIcon className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                        <div className="text-[11px] font-bold text-amber-800 leading-tight">
                            Mode Offline Aktif: Bekerja tanpa internet. Foto akan disinkronkan saat online.
                        </div>
                    </div>
                )}

                <div className="flex-1 flex flex-col gap-3">
                    {outletsList.length > 0 ? outletsList.map(outlet => (
                        <div key={outlet.id} className={`bg-white border border-slate-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col gap-3.5 ${(activeOutlet && activeOutlet.id === outlet.id) ? 'ring-2 ring-blue-600 ring-offset-1' : ''}`}>
                            
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex-1 min-w-0">
                                    <div className="flex flex-wrap items-center gap-1.5">
                                        <span className="text-[9px] px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold font-mono tracking-wider w-fit">{outlet.customer_code}</span>
                                        
                                        <span className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${outlet.status === 'Complete' ? 'bg-emerald-50 text-emerald-600 border-emerald-100/80' : 'bg-rose-50 text-rose-600 border-rose-100/80'}`}>
                                            {outlet.status === 'Complete' ? 'Complete' : 'Not Complete'}
                                        </span>
                                        
                                        <span className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${outlet.is_valid ? 'bg-blue-50 text-blue-600 border-blue-100/80' : 'bg-slate-50 text-slate-500 border-slate-200/80'}`}>
                                            {outlet.is_valid ? 'Terverifikasi' : 'Belum Verifikasi'}
                                        </span>
                                              
                                        {outlet.distanceToUser !== undefined && (
                                            <span className="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border bg-purple-50 text-purple-600 border-purple-100/80">
                                                {outlet.distanceToUser.toFixed(2)} KM
                                            </span>
                                        )}
                                    </div>
                                    <h4 className="text-xs font-black text-slate-800 mt-2 tracking-tight truncate">{outlet.customer_name}</h4>
                                    <p className="text-[10px] text-slate-400 font-semibold leading-normal mt-0.5 line-clamp-2">{outlet.alamat}</p>
                                </div>
                            </div>
                            
                            <div className="flex flex-wrap items-center justify-between gap-2.5 border-t border-slate-100 pt-3">
                                <div className="flex items-center gap-1.5">
                                    <div className={`flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-extrabold tracking-wider transition-colors duration-200 ${outlet.foto_toko2 ? (outlet.foto_toko2.startsWith('pending') ? 'bg-amber-50 text-amber-600 border border-amber-100/50' : 'bg-emerald-50 text-emerald-600 border border-emerald-100/50') : 'bg-slate-50 text-slate-400 border border-slate-100/50'}`}>
                                        {outlet.foto_toko2 ? (outlet.foto_toko2.startsWith('pending') ? <ArrowPathIcon className="w-3.5 h-3.5 animate-spin" /> : <CheckCircleIcon className="w-3.5 h-3.5 text-emerald-500" />) : <span className="w-1.5 h-1.5 rounded-full bg-slate-300"></span>}
                                        <span>{outlet.foto_toko2 ? (outlet.foto_toko2.startsWith('pending') ? 'Depan (Offline)' : 'Tampak Depan') : 'Tampak Depan'}</span>
                                    </div>
                                    
                                    <div className={`flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-extrabold tracking-wider transition-colors duration-200 ${outlet.foto_toko3 ? (outlet.foto_toko3.startsWith('pending') ? 'bg-amber-50 text-amber-600 border border-amber-100/50' : 'bg-emerald-50 text-emerald-600 border border-emerald-100/50') : 'bg-slate-50 text-slate-400 border border-slate-100/50'}`}>
                                        {outlet.foto_toko3 ? (outlet.foto_toko3.startsWith('pending') ? <ArrowPathIcon className="w-3.5 h-3.5 animate-spin" /> : <CheckCircleIcon className="w-3.5 h-3.5 text-emerald-500" />) : <span className="w-1.5 h-1.5 rounded-full bg-slate-300"></span>}
                                        <span>{outlet.foto_toko3 ? (outlet.foto_toko3.startsWith('pending') ? 'Dalam (Offline)' : 'Tampak Dalam') : 'Tampak Dalam'}</span>
                                    </div>
                                </div>

                                <div className="flex items-center gap-1.5 mt-2 w-full justify-end sm:mt-0 sm:w-auto">
                                    {outlet.latitude && outlet.longitude && (
                                        <a href={`https://www.google.com/maps/dir/?api=1&destination=${outlet.latitude},${outlet.longitude}`} target="_blank" rel="noreferrer" className="inline-flex flex-1 items-center justify-center px-2 py-1 text-[9px] font-black uppercase text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-100 gap-1 h-8 sm:flex-none">
                                            <MapIcon className="w-3.5 h-3.5 text-blue-500" />
                                            <span>Arah</span>
                                        </a>
                                    )}
                                    <button onClick={() => setDetailOutlet(outlet)} className="inline-flex flex-1 items-center justify-center px-2 py-1 text-[9px] font-black uppercase text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-100 gap-1 h-8 sm:flex-none">
                                        <InformationCircleIcon className="w-3.5 h-3.5 text-slate-400" />
                                        <span>Detail</span>
                                    </button>
                                    <button onClick={() => startEdit(outlet)} className="inline-flex flex-1 items-center justify-center px-2 py-1 text-[9px] font-black uppercase text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-100 gap-1 h-8 sm:flex-none">
                                        <PencilSquareIcon className="w-3.5 h-3.5 text-indigo-500" />
                                        <span>Edit</span>
                                    </button>
                                    <button onClick={() => setActiveOutlet(outlet)} className="inline-flex flex-1 items-center justify-center px-2 py-1 text-[9px] font-black uppercase text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm gap-1 h-8 sm:flex-none">
                                        <CameraIcon className="w-3.5 h-3.5" />
                                        <span>Upload</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    )) : (
                        <div className="bg-white border border-slate-100 rounded-3xl py-12 px-6 text-center shadow-sm flex-1 flex flex-col items-center justify-center">
                            {(!isFiltered && isFetchingLocation) && (
                                <div className="flex flex-col items-center gap-3 text-slate-300">
                                    <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                        <span className="w-8 h-8 border-4 border-slate-200 border-t-blue-600 rounded-full animate-spin"></span>
                                    </div>
                                    <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">Mencari Lokasi...</h4>
                                </div>
                            )}
                            
                            {(!isFiltered && userLocationError && !isFetchingLocation) && (
                                <div className="flex flex-col items-center gap-3 text-slate-300">
                                    <div className="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-2 border border-rose-100">
                                        <MapPinIcon className="w-8 h-8 stroke-[1.5]" />
                                    </div>
                                    <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">Lokasi Tidak Ditemukan</h4>
                                    <p className="text-[10px] text-slate-400 max-w-[240px] mx-auto leading-normal font-semibold">
                                        GPS gagal atau Anda sedang offline. Pastikan <b>GPS / Lokasi</b> aktif.
                                    </p>
                                    <button onClick={fetchUserLocation} className="mt-2 px-3 py-1 text-[10px] font-bold uppercase border rounded-lg hover:bg-slate-100">Coba Lagi</button>
                                </div>
                            )}

                            {(!isFiltered && userLocation && !isFetchingLocation) && (
                                <div className="flex flex-col items-center gap-3 text-slate-300">
                                    <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                        <MapOutlineIcon className="w-8 h-8 stroke-[1.5]" />
                                    </div>
                                    <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">Tidak Ada Toko Terdekat</h4>
                                </div>
                            )}

                            {isFiltered && (
                                <div className="flex flex-col items-center gap-3 text-slate-300">
                                    <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                        <MagnifyingGlassOutlineIcon className="w-8 h-8 stroke-[1.5]" />
                                    </div>
                                    <h4 className="text-xs font-black uppercase tracking-wider text-slate-700">Toko Tidak Ditemukan</h4>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                <footer className="text-center py-4 text-[9px] text-slate-400 font-semibold tracking-wider uppercase shrink-0">
                    &copy; {new Date().getFullYear()} DevSiso &bull; RWO Mobile Photo Upload
                </footer>
            </main>

            {/* Bottom Sheet: Edit Data Outlet */}
            {editingOutlet && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={cancelEdit}></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                            <div className="min-w-0 pr-4">
                                <span className="inline-block px-2 py-0.5 bg-indigo-100 text-indigo-700 font-mono font-bold rounded-lg text-[9px]">{editingOutlet.customer_code}</span>
                                <h4 className="text-xs font-black text-slate-900 mt-1 truncate">Edit Data: {editingOutlet.customer_name}</h4>
                            </div>
                            <button onClick={cancelEdit} className="text-slate-400 p-1">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="flex-1 overflow-y-auto p-5 space-y-5">
                            {/* Identitas Pemilik */}
                            <div className="space-y-3">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 pl-1 flex items-center gap-1.5">
                                    <UserCircleIcon className="w-3.5 h-3.5" /> Identitas Pemilik
                                </h5>
                                <div className="bg-slate-50 p-4 rounded-2xl border border-slate-100/50 space-y-3">
                                    <div>
                                        <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Nama Pemilik Toko</label>
                                        <input type="text" value={editNamaPemilikToko} onChange={(e) => setEditNamaPemilikToko(e.target.value)} disabled={editingOutlet.nama_pemilik_toko} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 disabled:bg-slate-100 disabled:text-slate-500" />
                                    </div>
                                    <div>
                                        <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Nama KTP</label>
                                        <input type="text" value={editNamaKtp} onChange={(e) => setEditNamaKtp(e.target.value)} disabled={editingOutlet.nama_ktp} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 disabled:bg-slate-100 disabled:text-slate-500" />
                                    </div>
                                    <div>
                                        <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">NIK KTP</label>
                                        <input type="text" inputMode="numeric" maxLength="16" value={editNikKtp} onChange={(e) => setEditNikKtp(e.target.value.replace(/[^\dxX]/g, ''))} disabled={editingOutlet.nik_ktp} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 disabled:bg-slate-100 disabled:text-slate-500" />
                                    </div>
                                    <div>
                                        <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">No. HP</label>
                                        <input type="text" inputMode="tel" value={editNoHp} onChange={(e) => setEditNoHp(e.target.value.replace(/[^\dxX]/g, ''))} disabled={editingOutlet.no_hp} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 disabled:bg-slate-100 disabled:text-slate-500" />
                                    </div>
                                </div>
                            </div>
                            
                            {/* Rekening Bank */}
                            <div className="space-y-3">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 pl-1 flex items-center gap-1.5">
                                    <CreditCardIcon className="w-3.5 h-3.5" /> Rekening Bank
                                </h5>
                                <div className="bg-slate-50 p-4 rounded-2xl border border-slate-100/50 space-y-3">
                                    <div className="relative">
                                        <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Nama Bank</label>
                                        <input type="text" value={editNamaBank} onFocus={() => !editingOutlet.nama_bank && setShowBankDropdown(true)} onChange={(e) => {setEditNamaBank(e.target.value); setShowBankDropdown(true);}} disabled={editingOutlet.nama_bank} placeholder="Pilih atau cari bank..." className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 disabled:bg-slate-100 disabled:text-slate-500 pr-8" />
                                        <span className="absolute right-3 top-[26px] text-slate-400 pointer-events-none"><ChevronDownIcon className="w-4 h-4" /></span>
                                        {showBankDropdown && !editingOutlet.nama_bank && (
                                            <div className="absolute z-50 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white border border-slate-100 rounded-xl shadow-lg top-full">
                                                {filteredBanks.map(bank => (
                                                    <button key={bank} type="button" onClick={() => {setEditNamaBank(bank); setShowBankDropdown(false);}} className="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 border-b border-slate-50">{bank}</button>
                                                ))}
                                                {filteredBanks.length === 0 && <div className="px-4 py-2 text-xs text-slate-400 italic">Bank tidak ditemukan...</div>}
                                            </div>
                                        )}
                                    </div>
                                    <div>
                                        <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">No. Rekening</label>
                                        <input type="text" inputMode="numeric" value={editNoRekening} onChange={(e) => setEditNoRekening(e.target.value.replace(/[^\dxX]/g, ''))} disabled={editingOutlet.no_rekening} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 disabled:bg-slate-100 disabled:text-slate-500" />
                                    </div>
                                    <div>
                                        <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Nama Pemilik Rekening</label>
                                        <input type="text" value={editNamaPemilikNorek} onChange={(e) => setEditNamaPemilikNorek(e.target.value)} disabled={editingOutlet.nama_pemilik_norek} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 disabled:bg-slate-100 disabled:text-slate-500" />
                                    </div>
                                </div>
                            </div>
                            
                            {/* Foto KTP */}
                            <div className="space-y-3">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 pl-1 flex items-center gap-1.5">
                                    <CameraIcon className="w-3.5 h-3.5" /> Foto KTP
                                </h5>
                                <div className={`relative border border-dashed rounded-2xl overflow-hidden min-h-[120px] flex flex-col items-center justify-center p-3 ${fotoKtpPreview || editingOutlet.foto_ktp ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200 bg-slate-50'}`}>
                                    <input type="file" accept="image/*" capture="environment" onChange={(e) => handleFileSelect(e, 'foto_ktp')} disabled={editingOutlet.foto_ktp} className="absolute inset-0 opacity-0 cursor-pointer z-10 disabled:cursor-not-allowed" />
                                    {fotoKtpPreview ? (
                                        <div className="w-full flex flex-col items-center">
                                            <img src={fotoKtpPreview} alt="Preview KTP" className="w-full h-24 object-contain rounded-lg" />
                                            <span className="text-[9px] font-bold text-emerald-600 mt-1.5 flex items-center gap-1"><CheckCircleIcon className="w-3.5 h-3.5" /> Foto KTP siap disimpan</span>
                                        </div>
                                    ) : editingOutlet.foto_ktp ? (
                                        <div className="w-full flex flex-col items-center">
                                            <div className="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                                <ShieldCheckIcon className="w-5 h-5" />
                                            </div>
                                            <span className="text-[10px] font-bold text-emerald-600 mt-1.5">Foto KTP Sudah Terunggah</span>
                                        </div>
                                    ) : (
                                        <div className="w-full flex flex-col items-center py-2">
                                            <div className="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-slate-500"><CameraIcon className="w-5 h-5" /></div>
                                            <span className="text-[11px] font-bold text-slate-700 mt-1.5">Unggah Foto KTP</span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="p-5 border-t border-slate-100 bg-slate-50 flex items-center gap-3">
                            <button onClick={cancelEdit} className="flex-1 h-11 border border-slate-200 bg-white rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button onClick={saveEdits} disabled={fotoKtpState.isUploading} className="flex-1 h-11 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20 disabled:opacity-50">
                                {fotoKtpState.isUploading ? 'Menyimpan...' : 'Simpan Edit'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Bottom Sheet: Outlet Details */}
            {detailOutlet && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={() => setDetailOutlet(null)}></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                            <div className="min-w-0 pr-4">
                                <span className="inline-block px-2 py-0.5 bg-fuchsia-100 text-fuchsia-700 font-mono font-bold rounded-lg text-[9px]">{detailOutlet.customer_code}</span>
                                <h4 className="text-xs font-black text-slate-900 mt-1 truncate">{detailOutlet.customer_name}</h4>
                            </div>
                            <button onClick={() => setDetailOutlet(null)} className="text-slate-400 p-1">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="flex-1 overflow-y-auto p-5 space-y-6 text-xs text-slate-600">
                            {/* Informasi Dasar */}
                            <div className="space-y-3">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                    <MapIcon className="w-3.5 h-3.5" /> Informasi Dasar
                                </h5>
                                <div className="grid grid-cols-2 gap-y-3 gap-x-4">
                                    <div className="col-span-2">
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Alamat</span>
                                        <span className="font-semibold text-slate-700 leading-normal">{detailOutlet.alamat || '-'}</span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Kode Eskalink</span>
                                        <span className="font-bold text-slate-800">{detailOutlet.eskalink_code || '-'}</span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">No. HP</span>
                                        <span className="font-bold text-slate-800">{maskValue(detailOutlet.no_hp)}</span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Region</span>
                                        <span className="font-bold text-slate-700">{detailOutlet.region_code || '-'}</span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Area</span>
                                        <span className="font-bold text-slate-700">{detailOutlet.area_code || '-'}</span>
                                    </div>
                                    <div className="col-span-2">
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Cabang</span>
                                        <span className="font-bold text-slate-700">{detailOutlet.branch_name || '-'}</span>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Identitas Pemilik */}
                            <div className="space-y-3">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                    <UserCircleIcon className="w-3.5 h-3.5" /> Identitas Pemilik
                                </h5>
                                <div className="grid grid-cols-1 gap-y-3">
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Pemilik Toko</span>
                                        <span className="font-bold text-slate-800">{detailOutlet.nama_pemilik_toko || '-'}</span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Sesuai KTP</span>
                                        <span className="font-bold text-slate-800">{detailOutlet.nama_ktp || '-'}</span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">NIK KTP</span>
                                        <span className="font-bold font-mono text-slate-800">{maskValue(detailOutlet.nik_ktp)}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Rekening Bank */}
                            <div className="space-y-3">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                    <CreditCardIcon className="w-3.5 h-3.5" /> Rekening Bank
                                </h5>
                                <div className="grid grid-cols-1 gap-y-3">
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Bank</span>
                                        <span className="font-bold text-slate-800">{detailOutlet.nama_bank || '-'}</span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">No. Rekening</span>
                                        <span className="font-bold font-mono text-slate-800">{maskValue(detailOutlet.no_rekening)}</span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Pemilik Rekening</span>
                                        <span className="font-bold text-slate-800">{detailOutlet.nama_pemilik_norek || '-'}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Data Server */}
                            <div className="space-y-3">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                    <ServerIcon className="w-3.5 h-3.5" /> Data Server
                                </h5>
                                <div className="grid grid-cols-1 gap-y-3">
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Status Kelengkapan</span>
                                        <span className={`inline-block px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider ${detailOutlet.status === 'Complete' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'}`}>
                                            {detailOutlet.status || 'Not Complete'}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Status Verifikasi Pusat</span>
                                        <span className={`font-bold ${detailOutlet.is_valid ? 'text-emerald-600' : 'text-slate-500'}`}>
                                            {detailOutlet.is_valid ? 'Terverifikasi' : 'Belum Verifikasi'}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Catatan / Keterangan</span>
                                        <span className="text-slate-700 leading-normal">{detailOutlet.keterangan || '-'}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Lampiran Foto */}
                            <div className="space-y-3">
                                <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                    <DocumentTextIcon className="w-3.5 h-3.5" /> Lampiran Foto
                                </h5>
                                <div className="grid grid-cols-2 gap-3">
                                    {/* KTP */}
                                    <div className="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col h-32 relative">
                                        <div className="p-1.5 bg-white border-b border-slate-200 text-center text-[9px] font-black uppercase text-slate-500 tracking-widest shrink-0">Foto KTP</div>
                                        <div className="flex-1 relative flex items-center justify-center p-2">
                                            {detailOutlet.foto_ktp ? (
                                                <img src={getExistingPhotoUrl(detailOutlet.foto_ktp)} className="w-full h-full object-contain" />
                                            ) : <div className="text-[9px] font-bold text-slate-400 bg-slate-200/50 px-2 py-1 rounded-lg">Belum Ada</div>}
                                        </div>
                                    </div>
                                    {/* Toko */}
                                    <div className="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col h-32 relative">
                                        <div className="p-1.5 bg-white border-b border-slate-200 text-center text-[9px] font-black uppercase text-slate-500 tracking-widest shrink-0">Foto Toko Lengkap</div>
                                        <div className="flex-1 relative flex items-center justify-center p-2">
                                            {detailOutlet.foto_toko ? (
                                                <img src={getExistingPhotoUrl(detailOutlet.foto_toko)} className="w-full h-full object-contain" />
                                            ) : <div className="text-[9px] font-bold text-slate-400 bg-slate-200/50 px-2 py-1 rounded-lg">Belum Ada</div>}
                                        </div>
                                    </div>
                                    {/* Depan */}
                                    <div className="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col h-32 relative">
                                        <div className="p-1.5 bg-white border-b border-slate-200 text-center text-[9px] font-black uppercase text-slate-500 tracking-widest shrink-0">Foto Tampak Depan</div>
                                        <div className="flex-1 relative flex items-center justify-center p-2">
                                            {detailOutlet.foto_toko2 ? (
                                                <img src={getExistingPhotoUrl(detailOutlet.foto_toko2)} className="w-full h-full object-contain" />
                                            ) : <div className="text-[9px] font-bold text-slate-400 bg-slate-200/50 px-2 py-1 rounded-lg">Belum Ada</div>}
                                        </div>
                                    </div>
                                    {/* Dalam */}
                                    <div className="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col h-32 relative">
                                        <div className="p-1.5 bg-white border-b border-slate-200 text-center text-[9px] font-black uppercase text-slate-500 tracking-widest shrink-0">Foto Tampak Dalam</div>
                                        <div className="flex-1 relative flex items-center justify-center p-2">
                                            {detailOutlet.foto_toko3 ? (
                                                <img src={getExistingPhotoUrl(detailOutlet.foto_toko3)} className="w-full h-full object-contain" />
                                            ) : <div className="text-[9px] font-bold text-slate-400 bg-slate-200/50 px-2 py-1 rounded-lg">Belum Ada</div>}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="p-5 border-t border-slate-100 bg-slate-50">
                            <button onClick={() => setDetailOutlet(null)} className="w-full h-11 border border-slate-200 text-slate-700 bg-white rounded-xl text-xs font-bold hover:bg-slate-50">Tutup</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Map Sheet */}
            {showMapSheet && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={() => setShowMapSheet(false)}></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col h-[85vh] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 flex items-center justify-between border-b border-slate-100 shrink-0">
                            <h3 className="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                                <MapIcon className="w-4 h-4 text-indigo-500" /> Peta Lokasi Toko
                            </h3>
                            <button onClick={() => setShowMapSheet(false)} className="text-slate-400 p-1 hover:bg-slate-100 rounded-full">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="flex-1 w-full bg-slate-100 relative rounded-b-none overflow-hidden">
                            <div id="outletsMap" className="absolute inset-0 z-0"></div>
                        </div>
                    </div>
                </div>
            )}

            {/* Bottom Sheet: Filters */}
            {activeOutlet && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={cancelUpload}></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                            <div className="min-w-0 pr-4">
                                <span className="inline-block px-2 py-0.5 bg-blue-100 text-blue-700 font-mono font-bold rounded-lg text-[9px]">{activeOutlet.customer_code}</span>
                                <h4 className="text-xs font-black text-slate-900 mt-1 truncate">{activeOutlet.customer_name}</h4>
                            </div>
                            <button onClick={cancelUpload} className="text-slate-400 p-1">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="flex-1 overflow-y-auto p-5 space-y-4">
                            {/* Depan */}
                            <div className="w-full">
                                <label className="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500 mb-2">Foto Tampak Depan</label>
                                <div className={`relative border border-dashed rounded-2xl overflow-hidden min-h-[120px] flex flex-col items-center justify-center p-3 ${fotoDepanPreview || activeOutlet.foto_toko2 ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200 bg-slate-50'}`}>
                                    <input type="file" accept="image/*" capture="environment" onChange={(e) => handleFileSelect(e, 'foto_depan')} disabled={activeOutlet.foto_toko2 && !activeOutlet.foto_toko2.startsWith('pending')} className="absolute inset-0 opacity-0 cursor-pointer z-10 disabled:cursor-not-allowed" />
                                    {fotoDepanPreview ? (
                                        <div className="w-full flex flex-col items-center">
                                            <img src={fotoDepanPreview} alt="Preview Depan" className="w-full h-24 object-contain rounded-lg" />
                                        </div>
                                    ) : activeOutlet.foto_toko2 ? (
                                        <div className="w-full flex flex-col items-center">
                                            <img src={getExistingPhotoUrl(activeOutlet.foto_toko2)} alt="Saved Depan" className="w-full h-24 object-contain rounded-lg opacity-80" />
                                        </div>
                                    ) : (
                                        <div className="w-full flex flex-col items-center py-2">
                                            <div className="w-9 h-9 rounded-full bg-slate-200/50 flex items-center justify-center text-slate-500"><CameraIcon className="w-5 h-5" /></div>
                                            <span className="text-[11px] font-bold text-slate-700 mt-1.5">Ambil Foto Depan</span>
                                        </div>
                                    )}
                                </div>
                            </div>
                            {/* Dalam */}
                            <div className="w-full">
                                <label className="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500 mb-2">Foto Tampak Dalam</label>
                                <div className={`relative border border-dashed rounded-2xl overflow-hidden min-h-[120px] flex flex-col items-center justify-center p-3 ${fotoDalamPreview || activeOutlet.foto_toko3 ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200 bg-slate-50'}`}>
                                    <input type="file" accept="image/*" capture="environment" onChange={(e) => handleFileSelect(e, 'foto_dalam')} disabled={activeOutlet.foto_toko3 && !activeOutlet.foto_toko3.startsWith('pending')} className="absolute inset-0 opacity-0 cursor-pointer z-10 disabled:cursor-not-allowed" />
                                    {fotoDalamPreview ? (
                                        <div className="w-full flex flex-col items-center">
                                            <img src={fotoDalamPreview} alt="Preview Dalam" className="w-full h-24 object-contain rounded-lg" />
                                        </div>
                                    ) : activeOutlet.foto_toko3 ? (
                                        <div className="w-full flex flex-col items-center">
                                            <img src={getExistingPhotoUrl(activeOutlet.foto_toko3)} alt="Saved Dalam" className="w-full h-24 object-contain rounded-lg opacity-80" />
                                        </div>
                                    ) : (
                                        <div className="w-full flex flex-col items-center py-2">
                                            <div className="w-9 h-9 rounded-full bg-slate-200/50 flex items-center justify-center text-slate-500"><CameraIcon className="w-5 h-5" /></div>
                                            <span className="text-[11px] font-bold text-slate-700 mt-1.5">Ambil Foto Dalam</span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="p-5 border-t border-slate-100 bg-slate-50 flex items-center gap-3">
                            <button onClick={cancelUpload} className="flex-1 h-11 border border-slate-200 bg-white rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button onClick={savePhotos} disabled={!fotoDepanFile && !fotoDalamFile} className="flex-1 h-11 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20 disabled:opacity-50">
                                {(fotoDepanState.isUploading || fotoDalamState.isUploading) ? 'Menyimpan...' : 'Simpan Foto'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Bottom Sheet: Filters */}
            {showFiltersSheet && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={() => setShowFiltersSheet(false)}></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 flex items-center justify-between border-b border-slate-100 shrink-0">
                            <h3 className="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                                <AdjustmentsHorizontalIcon className="w-4 h-4 text-blue-600" /> Filter Wilayah
                            </h3>
                            <button onClick={() => setShowFiltersSheet(false)} className="text-slate-400 p-1">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="flex-1 overflow-y-auto p-5 space-y-4">
                            <div className="w-full">
                                <label className="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500 mb-1">Region</label>
                                <select value={selectedRegion} onChange={(e) => { setSelectedRegion(e.target.value); setSelectedArea(''); setSelectedBranch(''); }} className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                                    <option value="">-- Semua Region --</option>
                                    {regionsList.map(r => <option key={r.region_code} value={r.region_code}>{r.region_name} ({r.region_code})</option>)}
                                </select>
                            </div>
                            <div className="w-full">
                                <label className="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500 mb-1">Area</label>
                                <select value={selectedArea} disabled={!selectedRegion} onChange={(e) => { setSelectedArea(e.target.value); setSelectedBranch(''); }} className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 disabled:opacity-50">
                                    <option value="">-- Semua Area --</option>
                                    {getFilteredAreas().map(a => <option key={a.area_code} value={a.area_code}>{a.area_name}</option>)}
                                </select>
                            </div>
                            <div className="w-full">
                                <label className="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500 mb-1">Cabang</label>
                                <select value={selectedBranch} disabled={!selectedArea} onChange={(e) => setSelectedBranch(e.target.value)} className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 disabled:opacity-50">
                                    <option value="">-- Semua Cabang --</option>
                                    {getFilteredBranches().map(b => <option key={b.branch_code} value={b.branch_name}>{b.branch_name}</option>)}
                                </select>
                            </div>
                        </div>
                        <div className="p-5 border-t border-slate-100 bg-slate-50 flex items-center gap-3">
                            <button onClick={() => { resetAllFilters(); setShowFiltersSheet(false); }} className="flex-1 h-11 border border-slate-200 text-slate-700 bg-white rounded-xl text-xs font-bold">Reset</button>
                            <button onClick={() => setShowFiltersSheet(false)} className="flex-1 h-11 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">Terapkan</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Bottom Sheet: Panduan */}
            {showGuideSheet && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={() => setShowGuideSheet(false)}></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 pt-2 flex items-center justify-between border-b border-slate-100 shrink-0">
                            <h3 className="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                                <InformationCircleIcon className="w-5 h-5 text-blue-600" /> Panduan Penggunaan
                            </h3>
                            <button onClick={() => setShowGuideSheet(false)} className="text-slate-400 p-1">
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="flex-1 overflow-y-auto p-5 space-y-4 text-xs text-slate-600">
                            <p className="font-semibold leading-relaxed">Aplikasi versi React ini mendukung pencarian toko, filtering wilayah, lokasi GPS, dan unggah foto secara real-time. Mode offline sedang disempurnakan di versi mendatang.</p>
                        </div>
                        <div className="p-5 border-t border-slate-100 bg-slate-50 flex items-center gap-3">
                            <button onClick={() => setShowGuideSheet(false)} className="bg-blue-600 text-white w-full h-11 rounded-xl font-bold">Saya Mengerti</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Custom Tailwind animation classes injected via style tag for simplicity without modifying tailwind.config */}
            <style dangerouslySetInnerHTML={{__html: `
                @keyframes fade-in-up {
                    0% { opacity: 0; transform: translate(-50%, 10px); }
                    100% { opacity: 1; transform: translate(-50%, 0); }
                }
                @keyframes slide-up {
                    0% { transform: translateY(100%); }
                    100% { transform: translateY(0); }
                }
                .animate-fade-in-up { animation: fade-in-up 0.3s ease-out forwards; }
                .animate-slide-up { animation: slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
            `}} />
        </div>
    );
}
