import React, { useState, useRef, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
    XMarkIcon, CheckBadgeIcon, PhotoIcon, ArrowPathIcon, ShieldExclamationIcon
} from '@heroicons/react/24/outline';
import { SkbRwoItem } from './StoreCard';

interface SkbModalProps {
    data: SkbRwoItem | null;
    onClose: () => void;
    showToast: (message: string, type: 'success' | 'error') => void;
    onOpenDetail?: (item: SkbRwoItem, forceEdit?: boolean) => void;
}

export default function SkbModal({ data, onClose, showToast, onOpenDetail }: SkbModalProps) {
    const [skbForm, setSkbForm] = useState<{
        approval_status: string;
        reject_reason: string;
        foto_skb: File | null;
    }>({ approval_status: '', reject_reason: '', foto_skb: null });
    
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [zoomImage, setZoomImage] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isLocating, setIsLocating] = useState(false);
    const [isProcessingPhoto, setIsProcessingPhoto] = useState(false);
    const [showMissingPrompt, setShowMissingPrompt] = useState(false);
    const fotoSkbRef = useRef<HTMLInputElement>(null);
    const latestLocationRef = useRef<{lat: string, lng: string} | null>(null);

    useEffect(() => {
        if (data) {
            setSkbForm({ 
                approval_status: data.is_approved === true || data.is_approved === 1 ? 'approve' : (data.is_approved === false || data.is_approved === 0 ? 'reject' : ''), 
                reject_reason: data.skb_reason || data.reason || '', 
                foto_skb: null 
            });
            setPreviewUrl(data.skb_foto ? `/storage/${data.skb_foto}` : null);
        }
    }, [data]);

    const skbFormRef = useRef(skbForm);
    useEffect(() => { skbFormRef.current = skbForm; }, [skbForm]);

    useEffect(() => {
        if (!data) return;
        
        if (window.location.hash !== '#skb') {
            window.history.pushState(null, '', window.location.pathname + window.location.search + '#skb');
        }

        const handlePopState = (e: PopStateEvent) => {
            if (window.location.hash !== '#skb') {
                const currentForm = skbFormRef.current;
                const isApp = data.is_approved === true || data.is_approved === 1;
                const isRej = data.is_approved === false || data.is_approved === 0;
                const originalApproval = isApp ? 'approve' : (isRej ? 'reject' : '');
                
                const isChanged = currentForm.approval_status !== originalApproval || currentForm.foto_skb || (currentForm.reject_reason !== (data.skb_reason || data.reason || ''));
                
                if (isChanged) {
                    if (window.confirm('Aksi SKB belum disimpan. Yakin ingin keluar?')) {
                        onClose();
                    } else {
                        window.history.pushState(null, '', window.location.pathname + window.location.search + '#skb');
                    }
                } else {
                    onClose();
                }
            }
        };

        window.addEventListener('popstate', handlePopState);
        return () => {
            window.removeEventListener('popstate', handlePopState);
            if (window.location.hash === '#skb') {
                window.history.replaceState(null, '', window.location.pathname + window.location.search);
            }
        };
    }, [data]);

    if (!data) return null;

    const handleClose = () => {
        const currentForm = skbForm;
        const isApp = data.is_approved === true || data.is_approved === 1;
        const isRej = data.is_approved === false || data.is_approved === 0;
        const originalApproval = isApp ? 'approve' : (isRej ? 'reject' : '');
        
        const isChanged = currentForm.approval_status !== originalApproval || currentForm.foto_skb || (currentForm.reject_reason !== (data.skb_reason || data.reason || ''));
        
        if (isChanged) {
            if (!window.confirm('Aksi SKB belum disimpan. Yakin ingin keluar?')) return;
        }
        if (window.location.hash === '#skb') {
            window.history.back();
        } else {
            onClose();
        }
    };

    const handleUploadClick = () => {
        const isDesktop = /Windows|Macintosh|Linux/i.test(navigator.userAgent) && !/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        if (isDesktop) {
            return showToast('Maaf, unggah foto hanya bisa dilakukan melalui perangkat Mobile (HP/Tablet).', 'error');
        }

        if (!navigator.geolocation) {
            return showToast('Perangkat tidak mendukung GPS', 'error');
        }

        setIsLocating(true);
        showToast('Mengunci lokasi GPS...', 'success');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude.toString();
                const lng = position.coords.longitude.toString();
                latestLocationRef.current = { lat, lng };
                setIsLocating(false);
                fotoSkbRef.current?.click();
            },
            (error) => {
                setIsLocating(false);
                showToast('Akses GPS ditolak / Gagal mendapatkan lokasi. Harap nyalakan GPS Anda.', 'error');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    };

    const handlePhotoChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
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
                        `WAKTU: ${new Date().toLocaleString('id-ID')}`
                    ];
                    
                    ctx.font = 'bold 16px monospace';
                    let maxTextWidth = 0;
                    textLines.forEach(line => {
                        const m = ctx.measureText(line);
                        if(m.width > maxTextWidth) maxTextWidth = m.width;
                    });
                    
                    const boxWidth = maxTextWidth + (padding * 2);
                    const boxHeight = (textLines.length * 24) + (padding * 2);
                    
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.6)';
                    ctx.fillRect(10, height - boxHeight - 10, boxWidth, boxHeight);
                    
                    ctx.fillStyle = '#ffffff';
                    textLines.forEach((line, i) => {
                        ctx.fillText(line, 10 + padding, height - boxHeight - 10 + padding + (i * 24) + 16);
                    });
                    
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            setIsProcessingPhoto(false);
                            return showToast('Gagal memproses gambar.', 'error');
                        }
                        const watermarkedFile = new File([blob], file.name, { type: 'image/jpeg' });
                        
                        setSkbForm(prev => ({ ...prev, foto_skb: watermarkedFile }));
                        setPreviewUrl(URL.createObjectURL(blob));
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

    const handleSkbSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!skbForm.approval_status) return showToast('Pilih status approval terlebih dahulu.', 'error');
        if (skbForm.approval_status === 'reject' && !skbForm.reject_reason) return showToast('Alasan reject wajib diisi.', 'error');

        const formData = new FormData();
        formData.append('customer_code', data.customer_code);
        if (data.distributor_code) formData.append('distributor_code', data.distributor_code);
        if (data.kuartal) formData.append('kuartal', data.kuartal);
        if (data.tahun) formData.append('tahun', data.tahun);
        formData.append('approval_status', skbForm.approval_status);
        if (skbForm.approval_status === 'reject') formData.append('reject_reason', skbForm.reject_reason);
        if (skbForm.foto_skb) formData.append('foto_skb', skbForm.foto_skb);

        setIsSubmitting(true);
        router.post('/mobile/skb-rwo/submit-skb', formData, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setIsSubmitting(false);
                showToast('Aksi SKB berhasil diproses.', 'success');
                
                const isMissing = !data.nama_pemilik_toko || !data.no_hp || !data.foto_toko2 || !data.foto_toko3;
                if (isMissing) {
                    setShowMissingPrompt(true);
                } else {
                    onClose();
                }
            },
            onError: (errors) => {
                setIsSubmitting(false);
                const msg = errors.foto_skb || errors.error || errors.reject_reason || 'Gagal memproses SKB.';
                showToast(msg, 'error');
            }
        });
    };

    return (
        <>
            {/* Processing Overlay */}
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
                    <h3 className="text-sm font-black text-slate-800 uppercase tracking-wider">Aksi SKB</h3>
                    <button onClick={handleClose} disabled={isSubmitting} className="p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 rounded-full transition-colors disabled:opacity-50">
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>
                <div className="p-5 overflow-y-auto custom-scrollbar">
                    <div className="mb-4">
                        <h4 className="text-sm font-black text-slate-800">{data.customer_name}</h4>
                        <p className="text-xs font-bold text-indigo-600 mt-0.5">{data.customer_code}</p>
                    </div>

                    <form onSubmit={handleSkbSubmit} className="flex flex-col gap-4">
                        <div>
                            <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 block">Status Approval</label>
                            <div className="grid grid-cols-2 gap-3">
                                <label className={`border rounded-xl p-3 flex flex-col items-center justify-center gap-1 cursor-pointer transition-all ${skbForm.approval_status === 'approve' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-500' : 'border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100'}`}>
                                    <input type="radio" name="approval" className="hidden" checked={skbForm.approval_status === 'approve'} onChange={() => setSkbForm({...skbForm, approval_status: 'approve'})} />
                                    <CheckBadgeIcon className="w-6 h-6" />
                                    <span className="text-xs font-bold uppercase tracking-wider">Approve</span>
                                </label>
                                <label className={`border rounded-xl p-3 flex flex-col items-center justify-center gap-1 cursor-pointer transition-all ${skbForm.approval_status === 'reject' ? 'border-rose-500 bg-rose-50 text-rose-700 ring-1 ring-rose-500' : 'border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100'}`}>
                                    <input type="radio" name="approval" className="hidden" checked={skbForm.approval_status === 'reject'} onChange={() => setSkbForm({...skbForm, approval_status: 'reject'})} />
                                    <XMarkIcon className="w-6 h-6" />
                                    <span className="text-xs font-bold uppercase tracking-wider">Reject</span>
                                </label>
                            </div>
                        </div>

                        {skbForm.approval_status === 'reject' && (
                            <div className="animate-fade-in">
                                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Alasan Reject</label>
                                <textarea
                                    className="w-full border border-slate-200 rounded-xl p-3 text-xs focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none text-slate-800 bg-slate-50"
                                    rows={3}
                                    placeholder="Tulis alasan penolakan..."
                                    value={skbForm.reject_reason}
                                    onChange={(e) => setSkbForm({...skbForm, reject_reason: e.target.value})}
                                ></textarea>
                            </div>
                        )}

                        <div>
                            <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Foto SKB</label>
                            <div className="border border-dashed border-slate-300 rounded-xl p-4 flex flex-col items-center justify-center gap-2 bg-slate-50 relative overflow-hidden">
                                {previewUrl ? (
                                    <>
                                        <img src={previewUrl} alt="SKB Preview" onClick={() => setZoomImage(previewUrl)} className="max-h-32 rounded-lg object-contain cursor-pointer hover:opacity-90 transition-opacity" />
                                        <div className="flex gap-2 mt-2 w-full">
                                            <button type="button" onClick={() => { setPreviewUrl(null); setSkbForm({...skbForm, foto_skb: null}); if(fotoSkbRef.current) fotoSkbRef.current.value = ''; }} className="flex-1 py-1.5 text-[10px] font-bold uppercase bg-rose-100 text-rose-600 rounded-lg">Hapus</button>
                                            <button type="button" onClick={handleUploadClick} className="flex-1 py-1.5 text-[10px] font-bold uppercase bg-indigo-100 text-indigo-600 rounded-lg">Ganti</button>
                                        </div>
                            
            {/* Missing Data Prompt */}
            {showMissingPrompt && (
                <div className="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex justify-center items-center p-4 animate-fade-in">
                    <div className="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl animate-zoom-in text-center">
                        <ShieldExclamationIcon className="w-16 h-16 text-amber-500 mx-auto mb-4" />
                        <h3 className="text-lg font-black text-slate-800 mb-2">Data Toko Belum Lengkap</h3>
                        <p className="text-sm text-slate-600 mb-6 leading-relaxed">
                            SKB berhasil disimpan. Namun data toko (Nama Pemilik, No HP, atau Foto Depan/Dalam) masih kosong. Ingin melengkapinya sekarang?
                        </p>
                        <div className="flex gap-3">
                            <button 
                                onClick={() => { setShowMissingPrompt(false); onClose(); }} 
                                className="flex-1 py-3 bg-slate-100 text-slate-600 font-bold text-xs uppercase rounded-xl hover:bg-slate-200 transition-colors"
                            >Nanti Saja</button>
                            <button 
                                onClick={() => { setShowMissingPrompt(false); onClose(); if (onOpenDetail) onOpenDetail(data, true); }} 
                                className="flex-1 py-3 bg-indigo-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all"
                            >Isi Sekarang</button>
                        </div>
                    </div>
                </div>
            )}
        </>
                                ) : (
                                    <>
                                        <PhotoIcon className="w-8 h-8 text-slate-400" />
                                        <p className="text-[10px] text-slate-500 font-medium text-center">Ketuk untuk mengambil/mengunggah foto SKB</p>
                                        <button type="button" onClick={handleUploadClick} className="mt-1 px-4 py-2 bg-slate-200 text-slate-700 text-[10px] font-bold uppercase tracking-wider rounded-lg">Pilih Foto</button>
                            
            {/* Missing Data Prompt */}
            {showMissingPrompt && (
                <div className="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex justify-center items-center p-4 animate-fade-in">
                    <div className="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl animate-zoom-in text-center">
                        <ShieldExclamationIcon className="w-16 h-16 text-amber-500 mx-auto mb-4" />
                        <h3 className="text-lg font-black text-slate-800 mb-2">Data Toko Belum Lengkap</h3>
                        <p className="text-sm text-slate-600 mb-6 leading-relaxed">
                            SKB berhasil disimpan. Namun data toko (Nama Pemilik, No HP, atau Foto Depan/Dalam) masih kosong. Ingin melengkapinya sekarang?
                        </p>
                        <div className="flex gap-3">
                            <button 
                                onClick={() => { setShowMissingPrompt(false); onClose(); }} 
                                className="flex-1 py-3 bg-slate-100 text-slate-600 font-bold text-xs uppercase rounded-xl hover:bg-slate-200 transition-colors"
                            >Nanti Saja</button>
                            <button 
                                onClick={() => { setShowMissingPrompt(false); onClose(); if (onOpenDetail) onOpenDetail(data, true); }} 
                                className="flex-1 py-3 bg-indigo-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all"
                            >Isi Sekarang</button>
                        </div>
                    </div>
                </div>
            )}
        </>
                                )}
                                <input type="file" accept="image/*" className="hidden" ref={fotoSkbRef} onChange={handlePhotoChange} />
                            </div>
                        </div>

                        <button
                            type="submit"
                            disabled={isSubmitting}
                            className={`mt-4 w-full py-3 rounded-xl text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2 ${isSubmitting ? 'bg-slate-300 shadow-none' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/30'}`}
                        >
                            {isSubmitting ? <><ArrowPathIcon className="w-4 h-4 animate-spin" /> Menyimpan...</> : 'Simpan SKB'}
                        </button>
                    </form>
                </div>
            </div>
        </div>

            {/* Image Preview Modal */}
            {zoomImage && (
                <div className="fixed inset-0 z-[110] bg-black/90 backdrop-blur-sm flex justify-center items-center p-4 animate-fade-in" onClick={() => setZoomImage(null)}>
                    <button onClick={() => setZoomImage(null)} className="absolute top-4 right-4 p-2 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 rounded-full transition-colors z-50 backdrop-blur-md">
                        <XMarkIcon className="w-6 h-6" />
                    </button>
                    <img src={zoomImage} alt="Zoomed Preview" className="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl animate-zoom-in" onClick={(e) => e.stopPropagation()} />
                </div>
            )}

            {/* Missing Data Prompt */}
            {showMissingPrompt && (
                <div className="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex justify-center items-center p-4 animate-fade-in">
                    <div className="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl animate-zoom-in text-center">
                        <ShieldExclamationIcon className="w-16 h-16 text-amber-500 mx-auto mb-4" />
                        <h3 className="text-lg font-black text-slate-800 mb-2">Data Toko Belum Lengkap</h3>
                        <p className="text-sm text-slate-600 mb-6 leading-relaxed">
                            SKB berhasil disimpan. Namun data toko (Nama Pemilik, No HP, atau Foto Depan/Dalam) masih kosong. Ingin melengkapinya sekarang?
                        </p>
                        <div className="flex gap-3">
                            <button 
                                onClick={() => { setShowMissingPrompt(false); onClose(); }} 
                                className="flex-1 py-3 bg-slate-100 text-slate-600 font-bold text-xs uppercase rounded-xl hover:bg-slate-200 transition-colors"
                            >Nanti Saja</button>
                            <button 
                                onClick={() => { setShowMissingPrompt(false); onClose(); if (onOpenDetail) onOpenDetail(data, true); }} 
                                className="flex-1 py-3 bg-indigo-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all"
                            >Isi Sekarang</button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
