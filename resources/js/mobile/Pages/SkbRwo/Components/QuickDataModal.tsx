import React, { useState, useRef, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
    XMarkIcon, PhotoIcon, ArrowPathIcon, ShieldCheckIcon, MagnifyingGlassPlusIcon
} from '@heroicons/react/24/outline';
import { SkbRwoItem } from './StoreCard';

interface QuickDataModalProps {
    data: SkbRwoItem | null;
    onClose: () => void;
    showToast: (message: string, type: 'success' | 'error') => void;
    userName?: string;
}

export default function QuickDataModal({ data, onClose, showToast, userName }: QuickDataModalProps) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isProcessingPhoto, setIsProcessingPhoto] = useState(false);
    const [isLocating, setIsLocating] = useState(false);
    const [zoomImage, setZoomImage] = useState<string | null>(null);

    const [formData, setFormData] = useState({
        nama_pemilik_toko: '',
        no_hp: '',
        foto_toko2: null as File | null,
        foto_toko3: null as File | null
    });

    const [previews, setPreviews] = useState({
        foto_toko2: null as string | null,
        foto_toko3: null as string | null
    });

    const latestLocationRef = useRef<{lat: string, lng: string} | null>(null);
    const formRef = useRef(formData);
    
    const toko2Ref = useRef<HTMLInputElement>(null);
    const toko3Ref = useRef<HTMLInputElement>(null);

    useEffect(() => { formRef.current = formData; }, [formData]);

    useEffect(() => {
        if (data) {
            setFormData({
                nama_pemilik_toko: data.nama_pemilik_toko || '',
                no_hp: data.no_hp || '',
                foto_toko2: null,
                foto_toko3: null
            });
            setPreviews({
                foto_toko2: data.foto_toko2 ? `/storage/${data.foto_toko2}` : null,
                foto_toko3: data.foto_toko3 ? `/storage/${data.foto_toko3}` : null
            });
            
            // Background location fetch
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        latestLocationRef.current = {
                            lat: position.coords.latitude.toString(),
                            lng: position.coords.longitude.toString()
                        };
                    },
                    (error) => console.log('Location error:', error),
                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                );
            }
        }
    }, [data]);

    const handleClose = () => {
        const isChanged = formData.foto_toko2 || formData.foto_toko3 || (formData.nama_pemilik_toko !== (data?.nama_pemilik_toko || '')) || (formData.no_hp !== (data?.no_hp || ''));
        if (isChanged) {
            if (window.confirm('Ada perubahan yang belum disimpan. Yakin ingin keluar?')) {
                onClose();
            }
        } else {
            onClose();
        }
    };

    const handleCameraClick = (ref: React.RefObject<HTMLInputElement>) => {
        if (!latestLocationRef.current) {
            setIsLocating(true);
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    latestLocationRef.current = {
                        lat: position.coords.latitude.toString(),
                        lng: position.coords.longitude.toString()
                    };
                    setIsLocating(false);
                    ref.current?.click();
                },
                (error) => {
                    setIsLocating(false);
                    showToast('Gagal mendapatkan lokasi. Pastikan GPS aktif.', 'error');
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        } else {
            ref.current?.click();
        }
    };

    const handleFileChange = async (field: 'foto_toko2' | 'foto_toko3', e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0] && data) {
            const file = e.target.files[0];
            
            showToast('Memproses foto & geotagging...', 'success');
            setIsProcessingPhoto(true);
            
            let addressText = data.address || '-';
            const currentLat = latestLocationRef.current?.lat || '';
            const currentLng = latestLocationRef.current?.lng || '';

            if (currentLat && currentLng) {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 4000);
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${currentLat}&lon=${currentLng}&zoom=18&addressdetails=1`, { signal: controller.signal });
                    clearTimeout(timeoutId);
                    const geoData = await res.json();
                    if (geoData && geoData.display_name) {
                        addressText = geoData.display_name;
                    }
                } catch (err) {
                    console.log('Reverse geocoding timeout/failed, fallback to db address');
                }
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1280;
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > MAX_WIDTH) {
                        height = Math.round((height * MAX_WIDTH) / width);
                        width = MAX_WIDTH;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        setIsProcessingPhoto(false);
                        return;
                    }
                    
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    const padding = 15;
                    const textLines = [
                        `TOKO: ${data.customer_name || '-'}`,
                        `ALAMAT: ${addressText}`,
                        `GPS: ${currentLat}, ${currentLng}`,
                        `PENGINPUT: ${userName || data.supervisor_name || 'User'}`,
                        `WAKTU: ${new Date().toLocaleString('id-ID')}`
                    ];
                    
                    ctx.font = 'bold 16px Arial';
                    let maxTextWidth = 0;
                    textLines.forEach(text => {
                        const metrics = ctx.measureText(text);
                        if (metrics.width > maxTextWidth) maxTextWidth = metrics.width;
                    });
                    
                    const boxWidth = maxTextWidth + (padding * 2);
                    const boxHeight = (textLines.length * 22) + (padding * 2);
                    const boxX = canvas.width - boxWidth - 10;
                    const boxY = canvas.height - boxHeight - 10;
                    
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.6)';
                    ctx.beginPath();
                    ctx.roundRect(boxX, boxY, boxWidth, boxHeight, 8);
                    ctx.fill();
                    
                    ctx.fillStyle = '#ffffff';
                    ctx.shadowColor = 'rgba(0, 0, 0, 0.8)';
                    ctx.shadowBlur = 4;
                    ctx.shadowOffsetX = 1;
                    ctx.shadowOffsetY = 1;
                    
                    textLines.forEach((text, index) => {
                        ctx.fillText(text, boxX + padding, boxY + padding + 14 + (index * 22));
                    });
                    
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            setIsProcessingPhoto(false);
                            return showToast('Gagal memproses gambar.', 'error');
                        }
                        const watermarkedFile = new File([blob], file.name, { type: 'image/jpeg' });
                        
                        setFormData(prev => ({ ...prev, [field]: watermarkedFile }));
                        setPreviews(prev => ({ ...prev, [field]: URL.createObjectURL(blob) }));
                        showToast('Foto sukses distempel.', 'success');
                        setIsProcessingPhoto(false);
                        
                    }, 'image/jpeg', 0.7);
                };
                img.onerror = () => { setIsProcessingPhoto(false); showToast('Gagal memproses gambar.', 'error'); };
                img.src = event.target?.result as string;
            };
            reader.onerror = () => { setIsProcessingPhoto(false); showToast('Gagal membaca file foto.', 'error'); };
            reader.readAsDataURL(file);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!data) return;

        const submitData = new FormData();
        submitData.append('customer_code', data.customer_code);
        
        // Only append fields if they are edited and were not previously set in DB
        if (!data.nama_pemilik_toko && formData.nama_pemilik_toko) {
            submitData.append('nama_pemilik_toko', formData.nama_pemilik_toko);
        }
        if (!data.no_hp && formData.no_hp) {
            submitData.append('no_hp', formData.no_hp);
        }
        if (formData.foto_toko2) submitData.append('foto_toko2', formData.foto_toko2);
        if (formData.foto_toko3) submitData.append('foto_toko3', formData.foto_toko3);

        setIsSubmitting(true);
        router.post('/mobile/skb-rwo/submit-data', submitData, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setIsSubmitting(false);
                onClose();
            },
            onError: (errors) => {
                setIsSubmitting(false);
                const msg = errors.foto_toko2 || errors.foto_toko3 || errors.error || 'Gagal menyimpan data toko.';
                showToast(msg, 'error');
            }
        });
    };

    if (!data) return null;

    const renderFileInput = (field: 'foto_toko2' | 'foto_toko3', label: string, ref: React.RefObject<HTMLInputElement>) => {
        const isLocked = false; // Always allow changing photo
        const isEmpty = !previews[field];
        return (
            <div>
                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">{label}</label>
                <div className={`border-2 border-dashed rounded-xl p-4 flex flex-col items-center justify-center gap-2 relative overflow-hidden transition-colors ${isLocked ? 'border-slate-200 bg-slate-50' : (isEmpty ? 'border-rose-400 bg-rose-50/50' : 'border-indigo-300 bg-indigo-50/30')}`}>
                    {previews[field] ? (
                        <>
                            <img 
                                src={previews[field]!} 
                                alt={label} 
                                onClick={() => setZoomImage(previews[field]!)}
                                className="max-h-32 rounded-lg object-contain cursor-pointer hover:opacity-90 transition-opacity" 
                            />
                            {!isLocked && (
                                <div className="flex gap-2 mt-2 w-full">
                                    <button type="button" onClick={() => { setPreviews(prev => ({...prev, [field]: null})); setFormData(prev => ({...prev, [field]: null})); if(ref.current) ref.current.value = ''; }} className="flex-1 py-1.5 text-[10px] font-bold uppercase bg-rose-100 text-rose-600 rounded-lg">Hapus</button>
                                    <button type="button" onClick={() => handleCameraClick(ref)} className="flex-1 py-1.5 text-[10px] font-bold uppercase bg-indigo-100 text-indigo-600 rounded-lg">Ganti</button>
                                </div>
                            )}
                            {isLocked && (
                                <div className="absolute inset-0 bg-slate-900/5 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity pointer-events-none">
                                    <div className="px-3 py-1.5 bg-white/90 backdrop-blur rounded-lg shadow-sm flex items-center gap-1.5">
                                        <MagnifyingGlassPlusIcon className="w-4 h-4 text-slate-700" />
                                        <span className="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Ketuk untuk perbesar</span>
                                    </div>
                                </div>
                            )}
                        </>
                    ) : (
                        <>
                            <PhotoIcon className="w-8 h-8 text-slate-400" />
                            <p className="text-[10px] text-slate-500 font-medium text-center">Ketuk untuk mengambil foto</p>
                            <button type="button" onClick={() => handleCameraClick(ref)} className="mt-1 px-4 py-2 bg-slate-200 text-slate-700 text-[10px] font-bold uppercase tracking-wider rounded-lg">Kamera</button>
                        </>
                    )}
                    {!isLocked && (
                        <input type="file" accept="image/*" capture="environment" className="hidden" ref={ref} onChange={(e) => handleFileChange(field, e)} />
                    )}
                </div>
            </div>
        );
    };

    return (
        <>
            {(isLocating || isProcessingPhoto) && (
                <div className="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-center p-4 animate-fade-in">
                    <ArrowPathIcon className="w-12 h-12 text-white animate-spin mb-4" />
                    <h3 className="text-white font-black tracking-wider text-sm uppercase">
                        {isLocating ? 'Melacak Lokasi...' : 'Memproses Foto...'}
                    </h3>
                    <p className="text-white/80 text-[11px] mt-2 text-center max-w-[250px] leading-relaxed font-medium">
                        {isLocating ? 'Sedang mengunci titik GPS Anda saat ini.' : 'Sedang menyematkan titik GPS dan stempel waktu ke dalam foto.'}
                    </p>
                </div>
            )}

            <div className="fixed inset-0 z-[70] bg-slate-900/60 backdrop-blur-sm flex justify-center items-end sm:items-center p-0 sm:p-4 animate-fade-in">
                <div className="bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl max-h-[95vh] flex flex-col shadow-2xl animate-slide-up">
                    <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur z-10 rounded-t-3xl">
                        <div>
                            <h3 className="text-sm font-black text-slate-800 uppercase tracking-wider">Profiling Toko</h3>
                            <p className="text-[10px] text-slate-500 mt-0.5">{data.customer_name} • {data.customer_code}</p>
                        </div>
                        <button onClick={handleClose} disabled={isSubmitting} className="p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 rounded-full transition-colors disabled:opacity-50">
                            <XMarkIcon className="w-5 h-5" />
                        </button>
                    </div>

                    <div className="p-5 overflow-y-auto custom-scrollbar">
                        <form onSubmit={handleSubmit} className="flex flex-col gap-4 animate-fade-in">
                            
                            <div className="bg-cyan-50 border border-cyan-200 rounded-xl p-4 space-y-4">
                                <p className="text-[10px] font-bold text-cyan-800 uppercase tracking-wider mb-2">Data Pemilik</p>
                                
                                <div>
                                    <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">
                                        Nama Pemilik Toko {data.nama_pemilik_toko && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}
                                    </label>
                                    <input 
                                        type="text" 
                                        disabled={!!data.nama_pemilik_toko}
                                        className={`w-full text-xs text-slate-700 bg-white border rounded-lg p-2.5 outline-none transition-all ${!!data.nama_pemilik_toko ? 'border-slate-200 bg-slate-50 opacity-80' : (formData.nama_pemilik_toko ? 'border-emerald-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500' : 'border-rose-400 bg-rose-50/30 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500')}`}
                                        placeholder="Masukkan nama pemilik..."
                                        value={formData.nama_pemilik_toko}
                                        onChange={(e) => setFormData({...formData, nama_pemilik_toko: e.target.value})}
                                    />
                                </div>
                                
                                <div>
                                    <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">
                                        No Handphone {data.no_hp && <ShieldCheckIcon className="w-3 h-3 inline text-emerald-500 mb-0.5" />}
                                    </label>
                                    <input 
                                        type="tel" 
                                        disabled={!!data.no_hp}
                                        className={`w-full text-xs text-slate-700 bg-white border rounded-lg p-2.5 outline-none transition-all ${!!data.no_hp ? 'border-slate-200 bg-slate-50 opacity-80' : (formData.no_hp ? 'border-emerald-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500' : 'border-rose-400 bg-rose-50/30 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500')}`}
                                        placeholder="Contoh: 08123456789"
                                        value={formData.no_hp}
                                        onChange={(e) => setFormData({...formData, no_hp: e.target.value})}
                                    />
                                </div>
                            </div>
                            
                            <div className="flex flex-col gap-4 mt-2">
                                <h5 className="text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-1">Dokumentasi Toko</h5>
                                {renderFileInput('foto_toko2', 'Foto Toko (Depan/Luar)', toko2Ref)}
                                {renderFileInput('foto_toko3', 'Foto Toko (Dalam)', toko3Ref)}
                            </div>

                            <button
                                type="submit"
                                disabled={isSubmitting}
                                className={`mt-4 w-full py-3 rounded-xl text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2 ${isSubmitting ? 'bg-slate-300 shadow-none' : 'bg-cyan-600 hover:bg-cyan-700 shadow-cyan-500/30'}`}
                            >
                                {isSubmitting ? <><ArrowPathIcon className="w-4 h-4 animate-spin" /> Menyimpan...</> : 'Simpan Profiling'}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {zoomImage && (
                <div className="fixed inset-0 z-[110] bg-black/90 backdrop-blur-sm flex justify-center items-center p-4 animate-fade-in" onClick={() => setZoomImage(null)}>
                    <button onClick={() => setZoomImage(null)} className="absolute top-4 right-4 p-2 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 rounded-full transition-colors z-50 backdrop-blur-md">
                        <XMarkIcon className="w-6 h-6" />
                    </button>
                    <img src={zoomImage} alt="Zoomed Preview" className="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl animate-zoom-in" onClick={(e) => e.stopPropagation()} />
                </div>
            )}
        </>
    );
}
