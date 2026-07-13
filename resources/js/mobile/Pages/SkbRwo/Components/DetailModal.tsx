import React, { useState, useRef, useEffect } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import {
    XMarkIcon, PhotoIcon, ArrowPathIcon, MapPinIcon, PencilSquareIcon, ShieldCheckIcon, MagnifyingGlassIcon, ChartPieIcon, ChartBarIcon
} from '@heroicons/react/24/outline';
import { SkbRwoItem } from './StoreCard';

interface DetailModalProps {
    data: SkbRwoItem | null;
    isMonitoring?: boolean;
    onClose: () => void;
    showToast: (message: string, type: 'success' | 'error') => void;
}

export default function DetailModal({ data, isMonitoring, onClose, showToast }: DetailModalProps) {
    const [activeTab, setActiveTab] = useState<'pencapaian' | 'history'>('pencapaian');
    const [isEditing, setIsEditing] = useState(false);
    const [previewImage, setPreviewImage] = useState<string | null>(null);

    const [formData, setFormData] = useState({
        no_hp: '',
        nama_pemilik_toko: '',
        nik_ktp: '',
        nama_ktp: '',
        nama_bank: '',
        no_rekening: '',
        nama_pemilik_norek: '',
        latitude: '',
        longitude: '',
        foto_ktp: null as File | null,
        foto_toko2: null as File | null,
        foto_toko3: null as File | null
    });

    const [previews, setPreviews] = useState<{ [key: string]: string | null }>({
        foto_ktp: null,
        foto_toko2: null,
        foto_toko3: null
    });

    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isLocating, setIsLocating] = useState(false);
    const [showCloseConfirm, setShowCloseConfirm] = useState(false);

    const [historyOrder, setHistoryOrder] = useState<any[]>([]);
    const [isLoadingHistory, setIsLoadingHistory] = useState(false);

    const [historyProduk, setHistoryProduk] = useState<{headers: string[], data: any[]}>({headers: [], data: []});
    const [isLoadingProduk, setIsLoadingProduk] = useState(false);

    const ktpRef = useRef<HTMLInputElement>(null);
    const toko2Ref = useRef<HTMLInputElement>(null);
    const toko3Ref = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (data) {
            setFormData({
                no_hp: data.no_hp || '',
                nama_pemilik_toko: data.nama_pemilik_toko || '',
                nik_ktp: data.nik_ktp || '',
                nama_ktp: data.nama_ktp || '',
                nama_bank: data.nama_bank || '',
                no_rekening: data.no_rekening || '',
                nama_pemilik_norek: data.nama_pemilik_norek || '',
                latitude: data.latitude || '',
                longitude: data.longitude || '',
                foto_ktp: null,
                foto_toko2: null,
                foto_toko3: null
            });
            setPreviews({
                foto_ktp: data.foto_ktp ? `/storage/${data.foto_ktp}` : null,
                foto_toko2: data.foto_toko2 ? `/storage/${data.foto_toko2}` : null,
                foto_toko3: data.foto_toko3 ? `/storage/${data.foto_toko3}` : null,
            });
            setIsEditing(false);
            setShowCloseConfirm(false);
        }
    }, [data]);

    useEffect(() => {
        return () => {
            Object.values(previews).forEach(url => {
                if (url && url.startsWith('blob:')) URL.revokeObjectURL(url);
            });
        };
    }, [previews]);


    useEffect(() => {
        if (data && isMonitoring) {
            setIsLoadingHistory(true);
            setIsLoadingProduk(true);

            axios.get(`/mobile/skb-rwo/history-order/${data.customer_code}?kuartal=${data.kuartal || ''}`)
                .then(res => {
                    setHistoryOrder(res.data || []);
                })
                .catch(err => {
                    console.error('Failed to fetch history', err);
                })
                .finally(() => {
                    setIsLoadingHistory(false);
                });

            axios.get(`/mobile/skb-rwo/history-produk?kd_dist=${data.distributor_code}&uniq_kd=${data.customer_code}`)
                .then(res => {
                    setHistoryProduk(res.data || {headers: [], data: []});
                })
                .catch(err => {
                    console.error('Failed to fetch history produk', err);
                })
                .finally(() => {
                    setIsLoadingProduk(false);
                });
        }
    }, [data, isMonitoring]);

    const groupedHistory = React.useMemo(() => {
        if (!historyOrder || historyOrder.length === 0) return {};
        const groups: { [month: string]: { total: number, items: any[] } } = {};
        
        historyOrder.forEach(item => {
            const date = new Date(item.tanggal);
            const monthName = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
            if (!groups[monthName]) {
                groups[monthName] = { total: 0, items: [] };
            }
            groups[monthName].items.push(item);
            groups[monthName].total += Number(item.value_order);
        });
        
        return groups;
    }, [historyOrder]);

    const totalHistoryOrder = React.useMemo(() => {
        return historyOrder.reduce((sum, item) => sum + Number(item.value_order), 0);
    }, [historyOrder]);

    const getMonthNames = (kuartal: number | string | undefined) => {
        const k = Number(kuartal) || Math.ceil((new Date().getMonth() + 1) / 3);
        const months = [
            ['Januari', 'Februari', 'Maret'],
            ['April', 'Mei', 'Juni'],
            ['Juli', 'Agustus', 'September'],
            ['Oktober', 'November', 'Desember']
        ];
        return months[Math.min(Math.max(k - 1, 0), 3)] || months[0];
    };
    const monthNames = getMonthNames(data?.kuartal);

    const isEditingRef = useRef(isEditing);
    const previewImageRef = useRef(previewImage);
    
    useEffect(() => { isEditingRef.current = isEditing; }, [isEditing]);
    useEffect(() => { previewImageRef.current = previewImage; }, [previewImage]);

    useEffect(() => {
        if (!data) return;
        
        if (window.location.hash !== '#detail') {
            window.history.pushState(null, '', window.location.pathname + window.location.search + '#detail');
        }

        const handlePopState = (e: PopStateEvent) => {
            if (window.location.hash !== '#detail') {
                if (previewImageRef.current) {
                    setPreviewImage(null);
                    window.history.pushState(null, '', window.location.pathname + window.location.search + '#detail');
                } else if (isEditingRef.current) {
                    setShowCloseConfirm(true);
                    window.history.pushState(null, '', window.location.pathname + window.location.search + '#detail');
                } else {
                    onClose();
                }
            }
        };

        window.addEventListener('popstate', handlePopState);
        return () => {
            window.removeEventListener('popstate', handlePopState);
            if (window.location.hash === '#detail') {
                window.history.replaceState(null, '', window.location.pathname + window.location.search);
            }
        };
    }, [data]);

    if (!data) return null;

    const handleClose = () => {
        if (isEditing) {
            setShowCloseConfirm(true);
        } else {
            if (window.location.hash === '#detail') {
                window.history.back();
            } else {
                onClose();
            }
        }
    };

    const handleTextChange = (field: string, value: string) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    };

    const handleFileChange = (field: string, e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            if (previews[field] && previews[field]?.startsWith('blob:')) {
                URL.revokeObjectURL(previews[field]!);
            }
            setFormData(prev => ({ ...prev, [field]: file }));
            setPreviews(prev => ({ ...prev, [field]: URL.createObjectURL(file) }));
        }
    };

    const handleGetLocation = () => {
        if (!navigator.geolocation) {
            return showToast('Geolocation tidak didukung oleh browser anda.', 'error');
        }
        setIsLocating(true);
        navigator.geolocation.getCurrentPosition(
            (position) => {
                setIsLocating(false);
                setFormData(prev => ({
                    ...prev,
                    latitude: position.coords.latitude.toString(),
                    longitude: position.coords.longitude.toString()
                }));
                showToast('Lokasi berhasil didapatkan.', 'success');
            },
            (error) => {
                setIsLocating(false);
                showToast('Gagal mendapatkan lokasi. Pastikan izin GPS diberikan atau coba lagi.', 'error');
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!data.nik_ktp && formData.nik_ktp && formData.nik_ktp.toString().length !== 16) {
            showToast('NIK KTP harus tepat 16 digit.', 'error');
            return;
        }

        const payload = new FormData();
        payload.append('customer_code', data.customer_code);
        if (data.distributor_code) payload.append('distributor_code', data.distributor_code);
        
        const textFields = ['no_hp', 'nama_pemilik_toko', 'nik_ktp', 'nama_ktp', 'nama_bank', 'no_rekening', 'nama_pemilik_norek', 'latitude', 'longitude'] as const;
        textFields.forEach(field => {
            const val = formData[field];
            if (val) payload.append(field, val);
        });

        if (formData.foto_ktp) payload.append('foto_ktp', formData.foto_ktp);
        if (formData.foto_toko2) payload.append('foto_toko2', formData.foto_toko2);
        if (formData.foto_toko3) payload.append('foto_toko3', formData.foto_toko3);

        setIsSubmitting(true);
        router.post('/mobile/skb-rwo/submit-data', payload, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setIsSubmitting(false);
                showToast('Data toko berhasil disimpan.', 'success');
                setIsEditing(false);
            },
            onError: (errors: any) => {
                setIsSubmitting(false);
                const msg = Object.values(errors)[0] || 'Gagal menyimpan data toko.';
                showToast(msg as string, 'error');
            }
        });
    };

    const getInputClass = (field: keyof typeof formData, isError = false, isReadOnlyAlways = false) => {
        const isLocked = !!data[field];
        const isEmpty = !formData[field];
        
        if (isLocked) {
            return "w-full border border-slate-200 rounded-xl px-3 py-2 text-xs bg-slate-100 text-slate-500 cursor-not-allowed outline-none transition-colors";
        }
        
        if (isEmpty || isError) {
            return `w-full border border-rose-400 focus:border-rose-500 focus:ring-rose-500 rounded-xl px-3 py-2 text-xs bg-rose-50 ${isReadOnlyAlways ? 'text-rose-500 cursor-not-allowed' : 'text-slate-800'} focus:ring-1 outline-none transition-colors`;
        }
        
        return `w-full border border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl px-3 py-2 text-xs ${isReadOnlyAlways ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-slate-50 text-slate-800'} focus:ring-1 outline-none transition-colors`;
    };

    const renderFileInput = (field: 'foto_ktp'|'foto_toko2'|'foto_toko3', label: string, ref: React.RefObject<HTMLInputElement>, acceptGallery = false) => {
        const isLocked = !!data[field];
        const isEmpty = !previews[field];
        return (
        <div>
            <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">{label}</label>
            <div className={`border-2 border-dashed rounded-xl p-4 flex flex-col items-center justify-center gap-2 relative overflow-hidden transition-colors ${isLocked ? 'border-slate-200 bg-slate-50' : (isEmpty ? 'border-rose-400 bg-rose-50/50' : 'border-indigo-300 bg-indigo-50/30')}`}>
                {previews[field] ? (
                    <>
                        <img src={previews[field]!} alt={label} className="max-h-32 rounded-lg object-contain" />
                        {!isLocked && (
                            <div className="flex gap-2 mt-2 w-full">
                                <button type="button" onClick={() => { 
                                    setPreviews(prev => ({...prev, [field]: null})); 
                                    setFormData(prev => ({...prev, [field]: null})); 
                                    if(ref.current) ref.current.value = ''; 
                                }} className="flex-1 py-1.5 text-[10px] font-bold uppercase bg-rose-100 text-rose-600 rounded-lg">Hapus</button>
                                <button type="button" onClick={() => ref.current?.click()} className="flex-1 py-1.5 text-[10px] font-bold uppercase bg-indigo-100 text-indigo-600 rounded-lg">Ganti</button>
                            </div>
                        )}
                        {isLocked && (
                            <div className="absolute inset-0 flex items-center justify-center bg-slate-900/10">
                                <span className="bg-slate-800 text-white text-[10px] font-bold px-3 py-1 rounded-full opacity-80 flex items-center gap-1 shadow-sm">
                                    <ShieldCheckIcon className="w-3 h-3"/> Terkunci
                                </span>
                            </div>
                        )}
                    </>
                ) : (
                    <>
                        <PhotoIcon className="w-8 h-8 text-slate-400" />
                        <p className="text-[10px] text-slate-500 font-medium text-center">Ketuk untuk mengambil foto</p>
                        <button type="button" onClick={() => ref.current?.click()} className="mt-1 px-4 py-2 bg-slate-200 text-slate-700 text-[10px] font-bold uppercase tracking-wider rounded-lg">Pilih Foto</button>
                    </>
                )}
                {!isLocked && (
                    acceptGallery ? (
                        <input type="file" accept="image/*" className="hidden" ref={ref} onChange={(e) => handleFileChange(field, e)} />
                    ) : (
                        <input type="file" accept="image/*" capture="environment" className="hidden" ref={ref} onChange={(e) => handleFileChange(field, e)} />
                    )
                )}
            </div>
        </div>
        );
    };

    return (
        <>
            <div className="fixed inset-0 z-[70] bg-slate-900/60 backdrop-blur-sm flex justify-center items-end sm:items-center p-0 sm:p-4 animate-fade-in">
                <div className="bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl max-h-[95vh] flex flex-col shadow-2xl animate-slide-up">
                    <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur z-10 rounded-t-3xl shrink-0">
                        <h3 className="text-sm font-black text-slate-800 uppercase tracking-wider">{isEditing ? 'Edit Data Toko' : 'Detail Toko'}</h3>
                        <button onClick={handleClose} disabled={isSubmitting} className="p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 rounded-full transition-colors disabled:opacity-50">
                            <XMarkIcon className="w-5 h-5" />
                        </button>
                    </div>
                    
                    <div className="p-5 overflow-y-auto custom-scrollbar flex-1 relative">
                        {/* Detail Modal Top Header */}
                        <div className="mb-6 flex gap-4 items-start">
                            <div className="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                                <MapPinIcon className="w-6 h-6" />
                            </div>
                            <div className="flex-1 min-w-0">
                                <h4 className="text-sm font-black text-slate-800 leading-tight">{data.customer_name}</h4>
                                <p className="text-[11px] font-bold text-indigo-600 mt-0.5 mb-1">{data.customer_code}</p>
                                <span className={`inline-block px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wider uppercase ${data.status_data_lengkap === 'Lengkap' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200'} border`}>
                                    Data: {data.status_data_lengkap}
                                </span>
                            </div>
                        </div>

                        {!isEditing ? (
                            <div className="animate-fade-in">
                                {/* Monitoring Achievement Section */}
                                {isMonitoring && (
                                    <>
                                        {/* Tabs Navigation */}
                                        <div className="flex border-b border-slate-200 mb-4 bg-slate-50 rounded-t-2xl px-2 pt-2 mx-1 shadow-sm">
                                            <button 
                                                onClick={() => setActiveTab('pencapaian')} 
                                                className={`flex-1 py-3 text-[11px] font-black text-center uppercase tracking-widest border-b-2 transition-colors ${activeTab === 'pencapaian' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-400 hover:text-slate-600'}`}
                                            >
                                                Pencapaian
                                            </button>
                                            <button 
                                                onClick={() => setActiveTab('history')} 
                                                className={`flex-1 py-3 text-[11px] font-black text-center uppercase tracking-widest border-b-2 transition-colors ${activeTab === 'history' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-400 hover:text-slate-600'}`}
                                            >
                                                History
                                            </button>
                                        </div>

                                        {/* Tab Content: Pencapaian */}
                                        {activeTab === 'pencapaian' && (
                                            <>
                                            <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm animate-fade-in mx-1">
                                        <div className="flex items-center gap-2 mb-3">
                                            <ChartPieIcon className="w-4 h-4 text-indigo-600" />
                                            <h4 className="text-[11px] font-black uppercase tracking-widest text-slate-700">Detail Pencapaian {data.kuartal ? `(Kuartal ${data.kuartal})` : ''}</h4>
                                        </div>
                                        <div className="grid grid-cols-1 gap-2 mb-4">
                                            <div className="flex justify-between items-center p-2 bg-white rounded-lg border border-slate-100 shadow-sm">
                                                <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Target Kuartal</span>
                                                <span className="text-xs font-black text-slate-800">Rp {new Intl.NumberFormat('id-ID').format(data.total_target || 0)}</span>
                                            </div>
                                            <div className="flex justify-between items-center p-2 bg-white rounded-lg border border-slate-100 shadow-sm">
                                                <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{monthNames[0]}</span>
                                                <span className="text-xs font-bold text-slate-700">Rp {new Intl.NumberFormat('id-ID').format(data.month_1_value || 0)}</span>
                                            </div>
                                            <div className="flex justify-between items-center p-2 bg-white rounded-lg border border-slate-100 shadow-sm">
                                                <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{monthNames[1]}</span>
                                                <span className="text-xs font-bold text-slate-700">Rp {new Intl.NumberFormat('id-ID').format(data.month_2_value || 0)}</span>
                                            </div>
                                            <div className="flex justify-between items-center p-2 bg-white rounded-lg border border-slate-100 shadow-sm">
                                                <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{monthNames[2]}</span>
                                                <span className="text-xs font-bold text-slate-700">Rp {new Intl.NumberFormat('id-ID').format(data.month_3_value || 0)}</span>
                                            </div>
                                        </div>
                                        <div className="pt-3 border-t border-slate-200">
                                            <div className="flex justify-between items-center">
                                                <span className="text-[11px] font-black text-slate-600 uppercase tracking-wider">Total Actual</span>
                                                <span className="text-sm font-black text-indigo-600">Rp {new Intl.NumberFormat('id-ID').format(data.total_achievement || 0)}</span>
                                            </div>
                                            <div className="flex justify-between items-center mt-1">
                                                <span className="text-[10px] font-bold text-rose-500 uppercase tracking-wider">Sisa Gap</span>
                                                <span className="text-[11px] font-black text-rose-600">Rp {new Intl.NumberFormat('id-ID').format(Math.max(0, (data.total_target || 0) - (data.total_achievement || 0)))}</span>
                                            </div>
                                        </div>
                                        </div>
                                            {/* History Order Card */}
                                            <div className="bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm">
                                                <div className="flex items-center gap-2 mb-3">
                                                    <ChartBarIcon className="w-4 h-4 text-indigo-600" />
                                                    <h5 className="text-[11px] font-black text-slate-700 uppercase tracking-widest">History Order (Kuartal Ini)</h5>
                                                </div>
                                        {isLoadingHistory ? (
                                            <div className="flex justify-center p-4">
                                                <ArrowPathIcon className="w-5 h-5 animate-spin text-slate-400" />
                                            </div>
                                        ) : Object.keys(groupedHistory).length > 0 ? (
                                            <div className="flex flex-col gap-4">
                                                {Object.entries(groupedHistory).map(([month, groupData]) => (
                                                    <div key={month} className="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                                                        <div className="bg-slate-50 px-3 py-2 border-b border-slate-100 flex justify-between items-center">
                                                            <span className="text-[10px] font-bold text-slate-700 uppercase tracking-wider">{month}</span>
                                                            <span className="text-[11px] font-black text-indigo-600">Rp {new Intl.NumberFormat('id-ID').format(groupData.total)}</span>
                                                        </div>
                                                        <div className="flex flex-col">
                                                            {groupData.items.map((item, idx) => (
                                                                <div key={idx} className={`flex justify-between items-center px-3 py-2 ${idx !== groupData.items.length - 1 ? 'border-b border-slate-50' : ''}`}>
                                                                    <span className="text-[10px] font-medium text-slate-500">{new Date(item.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
                                                                    <span className="text-[10px] font-bold text-slate-700">Rp {new Intl.NumberFormat('id-ID').format(item.value_order)}</span>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="text-center p-4 border border-dashed border-slate-200 rounded-xl bg-slate-50 mb-4">
                                                <span className="text-[10px] text-slate-400 font-medium">Belum ada history order di kuartal ini.</span>
                                            </div>
                                        )}

                                        {/* Grand Total History Order */}
                                        {!isLoadingHistory && historyOrder.length > 0 && (
                                            <div className="pt-3 border-t border-slate-200 mt-4">
                                                <div className="flex justify-between items-center">
                                                    <span className="text-[11px] font-black text-slate-600 uppercase tracking-wider">Total Keseluruhan</span>
                                                    <span className="text-sm font-black text-indigo-600">Rp {new Intl.NumberFormat('id-ID').format(totalHistoryOrder)}</span>
                                                </div>
                                            </div>
                                        )}
                                            </div>
                                            </>
                                        )}
                                    
                                        {/* Tab Content: History */}
                                        {activeTab === 'history' && (
                                            <>
                                            <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm animate-fade-in mx-1">
                                                <div className="flex items-center gap-2 mb-3">
                                                    <ChartBarIcon className="w-4 h-4 text-indigo-600" />
                                                    <h4 className="text-[11px] font-black uppercase tracking-widest text-slate-700">Statistik Transaksi (2026)</h4>
                                                </div>
                                                <div className="grid grid-cols-1 gap-2">
                                                    <div className="flex justify-between items-center p-2 bg-indigo-50 rounded-lg border border-indigo-100 shadow-sm mb-1">
                                                        <span className="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Total Transaction</span>
                                                        <span className="text-xs font-black text-indigo-700">Rp {new Intl.NumberFormat('id-ID').format(data.total_transaction || 0)}</span>
                                                    </div>
                                                    <div className="flex justify-between items-center p-2 bg-white rounded-lg border border-slate-100 shadow-sm">
                                                        <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Last Transaction</span>
                                                        <div className="text-right flex flex-col">
                                                            <span className="text-xs font-black text-indigo-700">Rp {new Intl.NumberFormat('id-ID').format(data.last_transaction_value || 0)}</span>
                                                            <span className="text-[9px] font-semibold text-slate-400 mt-0.5">{data.last_transaction_date ? new Date(data.last_transaction_date).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : '-'}</span>
                                                        </div>
                                                    </div>
                                                    <div className="flex justify-between items-center p-2 bg-white rounded-lg border border-slate-100 shadow-sm">
                                                        <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Max Transaction</span>
                                                        <span className="text-xs font-black text-indigo-700">Rp {new Intl.NumberFormat('id-ID').format(data.max_transaction || 0)}</span>
                                                    </div>
                                                    <div className="flex justify-between items-center p-2 bg-white rounded-lg border border-slate-100 shadow-sm">
                                                        <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Average Transaction</span>
                                                        <span className="text-xs font-black text-indigo-700">Rp {new Intl.NumberFormat('id-ID').format(data.avg_transaction || 0)}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {/* Table History Produk */}
                                            <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm animate-fade-in mx-1">
                                                <div className="flex items-center gap-2 mb-3">
                                                    <ChartBarIcon className="w-4 h-4 text-indigo-600" />
                                                    <h4 className="text-[11px] font-black uppercase tracking-widest text-slate-700">History by Produk (2026)</h4>
                                                </div>
                                                
                                                {isLoadingProduk ? (
                                                    <div className="flex justify-center p-4">
                                                        <ArrowPathIcon className="w-5 h-5 animate-spin text-slate-400" />
                                                    </div>
                                                ) : historyProduk.data && historyProduk.data.length > 0 ? (
                                                    <div className="bg-white border border-slate-100 rounded-xl overflow-x-auto shadow-sm">
                                                        <table className="w-full text-left border-collapse min-w-max">
                                                            <thead>
                                                                <tr className="bg-slate-50 border-b border-slate-100">
                                                                    <th className="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider sticky left-0 bg-slate-50 z-10 border-r border-slate-100">Produk</th>
                                                                    <th className="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Max Trans</th>
                                                                    <th className="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Avg Trans</th>
                                                                    {historyProduk.headers.map((h, i) => (
                                                                        <th key={i} className="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">{h}</th>
                                                                    ))}
                                                                </tr>
                                                            </thead>
                                                            <tbody className="divide-y divide-slate-100">
                                                                {historyProduk.data.map((prod, idx) => (
                                                                    <tr key={idx} className="hover:bg-slate-50/50 transition-colors">
                                                                        <td className="p-3 text-[10px] font-black text-slate-700 sticky left-0 bg-white group-hover:bg-slate-50/50 z-10 border-r border-slate-100">
                                                                            {prod.produk_subbrand}
                                                                        </td>
                                                                        <td className="p-3 text-xs font-bold text-slate-700 text-right">
                                                                            {new Intl.NumberFormat('id-ID').format(prod.max_qty || 0)}
                                                                        </td>
                                                                        <td className="p-3 text-xs font-bold text-slate-700 text-right">
                                                                            {new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(prod.avg_qty || 0)}
                                                                        </td>
                                                                        {prod.monthly_qty.map((mq: number, mIdx: number) => (
                                                                            <td key={mIdx} className={`p-3 text-xs font-bold text-right ${mq > 0 ? 'text-indigo-600' : 'text-slate-300'}`}>
                                                                                {mq > 0 ? new Intl.NumberFormat('id-ID').format(mq) : '0'}
                                                                            </td>
                                                                        ))}
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                ) : (
                                                    <div className="text-center p-4 border border-dashed border-slate-200 rounded-xl bg-white">
                                                        <span className="text-[10px] text-slate-400 font-medium">Belum ada history produk.</span>
                                                    </div>
                                                )}
                                            </div>
                                            </>
                                        )}
                                    </>
                                )}

                                {!isMonitoring && (
                                    <>
                                        {/* Alamat & Kode PRC */}
                                        <div className="flex flex-col gap-6 mb-6">
                                    <div>
                                        <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-3">Identitas Outlet</p>
                                        <div className="flex flex-col gap-3">
                                            <div className="flex justify-between border-b border-slate-100 pb-2">
                                                <span className="text-xs text-slate-500 font-medium">PRC Code</span>
                                                <span className="text-xs font-bold text-slate-800">{data.customer_prc || '-'}</span>
                                            </div>
                                            <div className="flex flex-col gap-1 border-b border-slate-100 pb-2">
                                                <span className="text-xs text-slate-500 font-medium">Alamat</span>
                                                <span className="text-xs font-medium text-slate-800 leading-snug">{data.address || '-'}</span>
                                            </div>
                                        </div>
                                        

                                    </div>
                                    
                                    {/* Hirarki Area & Reward */}
                                    <div>
                                        <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-3">Hirarki Area</p>
                                        <div className="flex flex-col gap-3">
                                            <div className="flex justify-between border-b border-slate-100 pb-2">
                                                <span className="text-xs text-slate-500 font-medium">Region</span>
                                                <span className="text-xs font-bold text-slate-800">{data.region_name || '-'}</span>
                                            </div>
                                            <div className="flex justify-between border-b border-slate-100 pb-2">
                                                <span className="text-xs text-slate-500 font-medium">Area</span>
                                                <span className="text-xs font-bold text-slate-800">{data.area_name || '-'}</span>
                                            </div>
                                            <div className="flex flex-col gap-1 border-b border-slate-100 pb-2">
                                                <span className="text-xs text-slate-500 font-medium">Supervisor</span>
                                                <span className="text-xs font-bold text-slate-800">{data.supervisor_name || '-'}</span>
                                            </div>
                                            <div className="flex flex-col gap-1 border-b border-slate-100 pb-2">
                                                <span className="text-xs text-slate-500 font-medium">Distributor</span>
                                                <span className="text-xs font-bold text-slate-800">{data.distributor_name || '-'} ({data.distributor_code || '-'})</span>
                                            </div>
                                        </div>

                                    </div>

                                    <div>
                                        <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-3">Reward & Status</p>
                                        <div className="flex flex-col gap-3">
                                            <div className="flex justify-between border-b border-slate-100 pb-2">
                                                <span className="text-xs text-slate-500 font-medium">Total Target</span>
                                                <span className="text-xs font-black text-indigo-600">Rp {new Intl.NumberFormat('id-ID').format(data.total_target || 0)}</span>
                                            </div>
                                            <div className="flex justify-between border-b border-slate-100 pb-2">
                                                <span className="text-xs text-slate-500 font-medium">Reward</span>
                                                <span className="text-xs font-black text-slate-800">
                                                    {(() => {
                                                        const target = data.total_target || 0;
                                                        const pct = target >= 90000000 ? 2.5 : (target >= 30000000 ? 2.0 : 1.5);
                                                        return pct + '%';
                                                    })()}
                                                </span>
                                            </div>
                                            <div className="flex justify-between border-b border-slate-100 pb-2 items-center">
                                                <span className="text-xs text-slate-500 font-medium">Status SKB</span>
                                                <div>
                                                    {data.status_skb === 'Sudah' ? (
                                                        data.is_approved === true || data.is_approved === 1 ? (
                                                            <span className="px-2 py-1 rounded-md text-[9px] font-bold tracking-wider uppercase bg-emerald-100 text-emerald-700 border border-emerald-200">Approved</span>
                                                        ) : data.is_approved === false || data.is_approved === 0 ? (
                                                            <span className="px-2 py-1 rounded-md text-[9px] font-bold tracking-wider uppercase bg-rose-100 text-rose-700 border border-rose-200">Rejected</span>
                                                        ) : (
                                                            <span className="px-2 py-1 rounded-md text-[9px] font-bold tracking-wider uppercase bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
                                                        )
                                                    ) : (
                                                        <span className="px-2 py-1 rounded-md text-[9px] font-bold tracking-wider uppercase bg-slate-100 text-slate-600 border border-slate-200">Belum</span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>

                                {/* Status Kelengkapan Data */}
                                <div className="pt-4 border-t border-slate-100">
                                    <div className="flex items-center justify-between mb-4">
                                        <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Status Kelengkapan Data Toko</p>
                                        <button onClick={() => setIsEditing(true)} className="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-colors shadow-sm">
                                            <PencilSquareIcon className="w-3.5 h-3.5" /> Edit Data
                                        </button>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                                        {[
                                            { key: 'no_hp', label: 'No HP' },
                                            { key: 'nama_pemilik_toko', label: 'Nama Pemilik Toko' },
                                            { key: 'nik_ktp', label: 'NIK KTP' },
                                            { key: 'nama_ktp', label: 'Nama KTP' },
                                            { key: 'nama_bank', label: 'Nama Bank' },
                                            { key: 'no_rekening', label: 'No Rekening' },
                                            { key: 'nama_pemilik_norek', label: 'Nama Pemilik Norek' },
                                            { key: 'latitude', label: 'Latitude' },
                                            { key: 'longitude', label: 'Longitude' }
                                        ].map(field => (
                                            <div key={field.key} className="flex items-center justify-between border-b border-slate-100 pb-2">
                                                <span className="text-xs text-slate-600 font-medium">{field.label}</span>
                                                {data[field.key] ? (
                                                    <div className="flex items-center gap-1.5 justify-end">
                                                        <ShieldCheckIcon className="w-4 h-4 text-emerald-500 shrink-0" />
                                                        <span className="text-[11px] font-bold text-slate-800 break-all text-right max-w-[160px] md:max-w-[200px]">{data[field.key]}</span>
                                                    </div>
                                                ) : (
                                                    <XMarkIcon className="w-4 h-4 text-rose-500 stroke-2" />
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* Dokumentasi Foto */}
                                <div className="pt-4 border-t border-slate-100 pb-6 mt-4">
                                    <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-4">Dokumentasi Foto</p>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        {[
                                            { key: 'foto_ktp', label: 'Foto KTP' },
                                            { key: 'foto_toko2', label: 'Foto Depan' },
                                            { key: 'foto_toko3', label: 'Foto Dalam' },
                                            { key: 'skb_foto', label: 'Foto SKB' }
                                        ].map(photo => (
                                            <div key={photo.key} className="flex flex-col gap-2">
                                                <span className="text-[10px] font-bold text-slate-700 text-center uppercase tracking-wider">{photo.label}</span>
                                                {data[photo.key] ? (
                                                    <button onClick={() => setPreviewImage(`/storage/${data[photo.key]}`)} className="block w-full bg-slate-50 rounded-xl overflow-hidden border border-slate-200 hover:opacity-90 transition-all flex-1 flex items-center justify-center min-h-[100px] shadow-sm relative group">
                                                        <img src={`/storage/${data[photo.key]}`} alt={photo.label} className="object-cover w-full h-full max-h-32" />
                                                        <div className="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition-colors flex items-center justify-center">
                                                            <MagnifyingGlassIcon className="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                                        </div>
                                                    </button>
                                                ) : (
                                                    <div className="bg-slate-50 rounded-xl border border-slate-200 border-dashed flex-1 flex flex-col items-center justify-center min-h-[100px] text-slate-400 p-2 text-center">
                                                        <PhotoIcon className="w-6 h-6 mb-1 opacity-50" />
                                                        <span className="text-[9px] font-bold uppercase tracking-widest">Belum Ada</span>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                                    </>
                                )}
                            </div>
                        ) : (
                            <form id="dataForm" onSubmit={handleSubmit} className="flex flex-col gap-5 animate-fade-in pt-4 border-t border-slate-100">
                                
                                <div className="flex items-center justify-between mb-2">
                                    <h5 className="text-[11px] font-black uppercase tracking-widest text-slate-400">Form Update Data</h5>
                                    <button type="button" onClick={() => setIsEditing(false)} className="text-[10px] font-bold text-slate-500 underline uppercase tracking-wider">
                                        Batal Edit
                                    </button>
                                </div>

                                {/* Section Pemilik */}
                                <div className="flex flex-col gap-3">
                                    <h5 className="text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-1">Data Pemilik</h5>
                                    <div>
                                        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Nama Pemilik Toko {data.nama_pemilik_toko && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}</label>
                                        <input type="text" disabled={!!data.nama_pemilik_toko} value={formData.nama_pemilik_toko} onChange={e => handleTextChange('nama_pemilik_toko', e.target.value)} className={getInputClass('nama_pemilik_toko')} placeholder="Ketik nama pemilik" />
                                    </div>
                                    <div>
                                        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">No Handphone (WA) {data.no_hp && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}</label>
                                        <input type="tel" disabled={!!data.no_hp} value={formData.no_hp} onChange={e => handleTextChange('no_hp', e.target.value)} className={getInputClass('no_hp')} placeholder="08xxxxxxxxxx" />
                                    </div>
                                </div>

                                {/* Section KTP */}
                                <div className="flex flex-col gap-3">
                                    <h5 className="text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-1 mt-2">Data Identitas (KTP)</h5>
                                    <div>
                                        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">NIK KTP {data.nik_ktp && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}</label>
                                        <input type="text" inputMode="numeric" maxLength={16} disabled={!!data.nik_ktp} value={formData.nik_ktp} onChange={e => handleTextChange('nik_ktp', e.target.value)} className={getInputClass('nik_ktp', !data.nik_ktp && !!formData.nik_ktp && formData.nik_ktp.toString().length !== 16)} placeholder="16 Digit NIK" />
                                        {!data.nik_ktp && formData.nik_ktp && formData.nik_ktp.toString().length !== 16 && (
                                            <span className="text-[9px] font-medium text-rose-500 mt-1 block">NIK harus 16 digit. Saat ini: {formData.nik_ktp.toString().length} digit</span>
                                        )}
                                    </div>
                                    <div>
                                        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Nama Sesuai KTP {data.nama_ktp && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}</label>
                                        <input type="text" disabled={!!data.nama_ktp} value={formData.nama_ktp} onChange={e => handleTextChange('nama_ktp', e.target.value)} className={getInputClass('nama_ktp')} placeholder="Ketik nama di KTP" />
                                    </div>
                                    {renderFileInput('foto_ktp', 'Foto KTP', ktpRef, true)}
                                </div>

                                {/* Section Bank */}
                                <div className="flex flex-col gap-3">
                                    <h5 className="text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-1 mt-2">Data Rekening</h5>
                                    <div>
                                        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Nama Bank {data.nama_bank && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}</label>
                                        <select disabled={!!data.nama_bank} value={formData.nama_bank} onChange={e => handleTextChange('nama_bank', e.target.value)} className={getInputClass('nama_bank')}>
                                            <option value="">Pilih Bank</option>
                                            <optgroup label="Bank Nasional (Himbara & Swasta)">
                                                <option value="BCA">BCA (Bank Central Asia)</option>
                                                <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
                                                <option value="BNI">BNI (Bank Negara Indonesia)</option>
                                                <option value="Mandiri">Bank Mandiri</option>
                                                <option value="BSI">BSI (Bank Syariah Indonesia)</option>
                                                <option value="BTN">BTN (Bank Tabungan Negara)</option>
                                                <option value="CIMB Niaga">Bank CIMB Niaga</option>
                                                <option value="Permata">Bank Permata</option>
                                                <option value="Danamon">Bank Danamon</option>
                                                <option value="Mega">Bank Mega</option>
                                                <option value="BTPN">Bank BTPN</option>
                                            </optgroup>
                                            <optgroup label="Bank Digital">
                                                <option value="Jago">Bank Jago</option>
                                                <option value="SeaBank">SeaBank</option>
                                                <option value="Blu">Blu by BCA Digital</option>
                                                <option value="Neo">Bank Neo Commerce (BNC)</option>
                                                <option value="Allo">Allo Bank</option>
                                                <option value="Jenius">Jenius (BTPN)</option>
                                            </optgroup>
                                            <optgroup label="Bank Pembangunan Daerah (BPD)">
                                                <option value="BJB">Bank BJB (Jawa Barat & Banten)</option>
                                                <option value="DKI">Bank DKI</option>
                                                <option value="Jateng">Bank Jateng</option>
                                                <option value="Jatim">Bank Jatim</option>
                                                <option value="DIY">Bank DIY</option>
                                            </optgroup>
                                            <optgroup label="Lainnya">
                                                <option value="Lainnya">Bank Lainnya</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div>
                                        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Nomor Rekening {data.no_rekening && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}</label>
                                        <input type="number" disabled={!!data.no_rekening} value={formData.no_rekening} onChange={e => handleTextChange('no_rekening', e.target.value)} className={getInputClass('no_rekening')} placeholder="Ketik nomor rekening" />
                                    </div>
                                    <div>
                                        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Nama Pemilik Rekening {data.nama_pemilik_norek && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}</label>
                                        <input type="text" disabled={!!data.nama_pemilik_norek} value={formData.nama_pemilik_norek} onChange={e => handleTextChange('nama_pemilik_norek', e.target.value)} className={getInputClass('nama_pemilik_norek')} placeholder="Ketik nama di buku tabungan" />
                                    </div>
                                </div>

                                {/* Section Geolocation */}
                                <div className="flex flex-col gap-3">
                                    <h5 className="text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-1 mt-2">Titik Lokasi</h5>
                                    <div className="flex gap-2">
                                        <div className="flex-1">
                                            <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Latitude {data.latitude && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}</label>
                                            <input type="text" readOnly value={formData.latitude} className={getInputClass('latitude', false, true)} placeholder="Otomatis" />
                                        </div>
                                        <div className="flex-1">
                                            <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Longitude {data.longitude && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}</label>
                                            <input type="text" readOnly value={formData.longitude} className={getInputClass('longitude', false, true)} placeholder="Otomatis" />
                                        </div>

                                    </div>
                                    {!(data.latitude && data.longitude) && (
                                        <button type="button" onClick={handleGetLocation} disabled={isLocating} className="w-full py-2.5 rounded-xl text-indigo-600 bg-indigo-50 hover:bg-indigo-100 font-bold text-xs uppercase tracking-wider transition-colors border border-indigo-100 flex items-center justify-center gap-2">
                                            {isLocating ? <ArrowPathIcon className="w-4 h-4 animate-spin" /> : <MapPinIcon className="w-4 h-4" />}
                                            {isLocating ? 'Melacak...' : 'Ambil Lokasi Saat Ini'}
                                        </button>
                                    )}
                                </div>

                                {/* Section Foto Toko */}
                                <div className="flex flex-col gap-3 mb-4">
                                    <h5 className="text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-1 mt-2">Dokumentasi Toko</h5>
                                    {renderFileInput('foto_toko2', 'Foto Toko (Depan/Luar)', toko2Ref, false)}
                                    {renderFileInput('foto_toko3', 'Foto Toko (Dalam)', toko3Ref, false)}
                                </div>

                            </form>
                        )}
                    </div>
                    
                    {isEditing && (
                        <div className="p-4 border-t border-slate-100 shrink-0 bg-white rounded-b-3xl">
                            <button
                                type="submit"
                                form="dataForm"
                                disabled={isSubmitting}
                                className={`w-full py-3 rounded-xl text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2 ${isSubmitting ? 'bg-slate-300 shadow-none' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/30'}`}
                            >
                                {isSubmitting ? <><ArrowPathIcon className="w-4 h-4 animate-spin" /> Menyimpan...</> : 'Simpan Data'}
                            </button>
                            <p className="text-[9px] text-center text-slate-400 font-medium mt-2 leading-tight">
                                Formulir dapat disimpan secara parsial (dicicil). <br />Data akan divalidasi setelah semua field terisi lengkap.
                            </p>
                        </div>
                    )}
                </div>
            </div>

            {/* Image Preview Modal */}
            {previewImage && (
                <div className="fixed inset-0 z-[80] bg-black/90 backdrop-blur-sm flex justify-center items-center p-4 animate-fade-in" onClick={() => setPreviewImage(null)}>
                    <button onClick={() => setPreviewImage(null)} className="absolute top-4 right-4 p-2 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 rounded-full transition-colors z-50 backdrop-blur-md">
                        <XMarkIcon className="w-6 h-6" />
                    </button>
                    <img src={previewImage} alt="Preview" className="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl animate-zoom-in" onClick={(e) => e.stopPropagation()} />
                </div>
            )}

            {/* Close Confirmation Modal */}
            {showCloseConfirm && (
                <div className="fixed inset-0 z-[90] bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
                    <div className="bg-white rounded-3xl p-6 w-full max-w-[280px] shadow-2xl animate-zoom-in">
                        <h3 className="text-sm font-black text-slate-800 text-center uppercase tracking-wider mb-2">Batal Edit?</h3>
                        <p className="text-[11px] text-slate-500 text-center font-medium leading-relaxed mb-5">
                            Data yang belum disimpan akan hilang. Yakin ingin keluar?
                        </p>
                        <div className="flex gap-2">
                            <button onClick={() => setShowCloseConfirm(false)} className="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-[10px] uppercase tracking-wider transition-colors">Kembali Edit</button>
                            <button onClick={() => { setShowCloseConfirm(false); if(window.location.hash === '#detail') { window.history.back(); } else { onClose(); } }} className="flex-1 py-2 bg-rose-500 hover:bg-rose-600 text-white shadow-md shadow-rose-500/20 rounded-xl font-bold text-[10px] uppercase tracking-wider transition-colors">Ya, Keluar</button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
