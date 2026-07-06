import React, { useState, useRef, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
    XMarkIcon, CheckBadgeIcon, PhotoIcon, ArrowPathIcon
} from '@heroicons/react/24/outline';

export default function SkbModal({ data, onClose, showToast }) {
    const [skbForm, setSkbForm] = useState({ approval_status: '', reject_reason: '', foto_skb: null });
    const [previewUrl, setPreviewUrl] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const fotoSkbRef = useRef(null);

    useEffect(() => {
        if (data) {
            setSkbForm({ 
                approval_status: data.is_approved === true ? 'approve' : (data.is_approved === false ? 'reject' : ''), 
                reject_reason: data.skb_reason || data.reason || '', 
                foto_skb: null 
            });
            setPreviewUrl(data.skb_foto ? `/storage/${data.skb_foto}` : null);
        }
    }, [data]);

    if (!data) return null;

    const handlePhotoChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                showToast('Ukuran foto maksimal 2MB', 'error');
                e.target.value = '';
                return;
            }
            setSkbForm({ ...skbForm, foto_skb: file });
            setPreviewUrl(URL.createObjectURL(file));
        }
    };

    const handleSkbSubmit = (e) => {
        e.preventDefault();
        if (!skbForm.approval_status) return showToast('Pilih status approval terlebih dahulu.', 'error');
        if (skbForm.approval_status === 'reject' && !skbForm.reject_reason) return showToast('Alasan reject wajib diisi.', 'error');

        const formData = new FormData();
        formData.append('customer_code', data.customer_code);
        formData.append('distributor_code', data.distributor_code);
        if (data.kuartal) formData.append('kuartal', data.kuartal);
        formData.append('approval_status', skbForm.approval_status);
        if (skbForm.approval_status === 'reject') formData.append('reject_reason', skbForm.reject_reason);
        if (skbForm.foto_skb) formData.append('foto_skb', skbForm.foto_skb);

        setIsSubmitting(true);
        router.post('/mobile/skb-rwo/submit-skb', formData, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setIsSubmitting(false);
                onClose();
                showToast('Aksi SKB berhasil diproses.', 'success');
            },
            onError: (errors) => {
                setIsSubmitting(false);
                const msg = errors.foto_skb || errors.error || errors.reject_reason || 'Gagal memproses SKB.';
                showToast(msg, 'error');
            }
        });
    };

    return (
        <div className="fixed inset-0 z-[70] bg-slate-900/60 backdrop-blur-sm flex justify-center items-end sm:items-center p-0 sm:p-4 animate-fade-in">
            <div className="bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl max-h-[95vh] flex flex-col shadow-2xl animate-slide-up">
                <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur z-10 rounded-t-3xl">
                    <h3 className="text-sm font-black text-slate-800 uppercase tracking-wider">Aksi SKB</h3>
                    <button onClick={onClose} disabled={isSubmitting} className="p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 rounded-full transition-colors disabled:opacity-50">
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
                                    rows="3"
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
                                        <img src={previewUrl} alt="SKB Preview" className="max-h-32 rounded-lg object-contain" />
                                        <div className="flex gap-2 mt-2 w-full">
                                            <button type="button" onClick={() => { setPreviewUrl(null); setSkbForm({...skbForm, foto_skb: null}); if(fotoSkbRef.current) fotoSkbRef.current.value = ''; }} className="flex-1 py-1.5 text-[10px] font-bold uppercase bg-rose-100 text-rose-600 rounded-lg">Hapus</button>
                                            <button type="button" onClick={() => fotoSkbRef.current?.click()} className="flex-1 py-1.5 text-[10px] font-bold uppercase bg-indigo-100 text-indigo-600 rounded-lg">Ganti</button>
                                        </div>
                                    </>
                                ) : (
                                    <>
                                        <PhotoIcon className="w-8 h-8 text-slate-400" />
                                        <p className="text-[10px] text-slate-500 font-medium text-center">Ketuk untuk mengambil/mengunggah foto SKB</p>
                                        <button type="button" onClick={() => fotoSkbRef.current?.click()} className="mt-1 px-4 py-2 bg-slate-200 text-slate-700 text-[10px] font-bold uppercase tracking-wider rounded-lg">Pilih Foto</button>
                                    </>
                                )}
                                <input type="file" accept="image/*" capture="environment" className="hidden" ref={fotoSkbRef} onChange={handlePhotoChange} />
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
    );
}
