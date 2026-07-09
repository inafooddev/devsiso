import React, { useRef, useEffect, useState, useMemo } from 'react';
import { router } from '@inertiajs/react';

const PREDEFINED_KONDISI_HP = [
    "Baik",
    "Device Lemot",
    "Baterai Boros",
    "Tidak Bisa di-Charger",
    "Mati Sendiri",
    "Layar Retak",
    "Tombol Power/Volume Rusak",
    "Aplikasi sering force close",
    "Kamera Bermasalah"
];

export const MonitoringFormSheet = ({ show, onClose, formHook, salesName }: any) => {
    const [render, setRender] = useState(show);
    const [animate, setAnimate] = useState(false);

    useEffect(() => {
        if (show) {
            setRender(true);
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => requestAnimationFrame(() => setAnimate(true)));
        } else {
            setAnimate(false);
            document.body.style.overflow = '';
        }
        return () => { document.body.style.overflow = ''; };
    }, [show]);

    const handleAnimationEnd = () => {
        if (!show) setRender(false);
    };

    const {
        data, setData, processing, errors, submitForm, editId,
        existingFotoDepan, existingFotoBelakang, setExistingFotoDepan, setExistingFotoBelakang
    } = formHook;

    const fileInputDepan = useRef<HTMLInputElement>(null);
    const fileInputBelakang = useRef<HTMLInputElement>(null);

    const previewUrlDepan = useMemo(() => data.foto_tampak_depan ? URL.createObjectURL(data.foto_tampak_depan) : null, [data.foto_tampak_depan]);
    const previewUrlBelakang = useMemo(() => data.foto_tampak_belakang ? URL.createObjectURL(data.foto_tampak_belakang) : null, [data.foto_tampak_belakang]);

    useEffect(() => {
        return () => { if (previewUrlDepan) URL.revokeObjectURL(previewUrlDepan); };
    }, [previewUrlDepan]);

    useEffect(() => {
        return () => { if (previewUrlBelakang) URL.revokeObjectURL(previewUrlBelakang); };
    }, [previewUrlBelakang]);

    if (!render) return null;

    const isFormValid = 
        data.kondisi_hp?.trim() !== '' && 
        data.kondisi_hp !== '__others__' &&
        data.kondisi_kartu !== '' && 
        (data.foto_tampak_depan || existingFotoDepan) && 
        (data.foto_tampak_belakang || existingFotoBelakang);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        submitForm(() => {
            if (fileInputDepan.current) fileInputDepan.current.value = '';
            if (fileInputBelakang.current) fileInputBelakang.current.value = '';
            onClose();
        });
    };

    const handleDeleteImage = (type: 'depan' | 'belakang') => {
        if (!editId) return;
        if (!window.confirm(`Yakin ingin menghapus foto bagian ${type}? Tindakan ini tidak bisa dibatalkan.`)) return;

        router.post('/app/monitoring-device/destroy-image', {
            id: editId,
            type: type
        }, {
            preserveScroll: true,
            onSuccess: () => {
                if (type === 'depan') setExistingFotoDepan(null);
                if (type === 'belakang') setExistingFotoBelakang(null);
            }
        });
    };

    return (
        <div className="fixed inset-0 z-40">
            <div className={`fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-300 ${animate ? 'opacity-100' : 'opacity-0'}`} onClick={onClose}></div>
            <div 
                className={`fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[90%] flex flex-col z-50 transition-transform duration-300 ease-out ${animate ? 'translate-y-0' : 'translate-y-full'}`}
                onTransitionEnd={handleAnimationEnd}
            >
                
                <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                
                <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                    <div className="min-w-0 pr-4">
                        <span className="badge badge-primary badge-xs font-mono font-bold rounded-lg px-2 text-[0.5625rem]">{data.form_sales_code}</span>
                        <h4 className="text-xs font-black text-slate-900 mt-1">{editId ? 'Edit Monitoring' : 'Tambah Monitoring'}</h4>
                    </div>
                    <button onClick={onClose} className="btn btn-ghost btn-circle btn-xs text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-5 h-5"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                    </button>
                </div>
                
                <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-5 space-y-4">
                    
                    <div className="form-control">
                        <label className="label py-1"><span className="label-text text-[0.625rem] font-extrabold uppercase tracking-wider text-slate-500">Bulan Monitoring</span></label>
                        <input 
                            type="month" 
                            value={data.tanggal} 
                            disabled={true} 
                            className="input input-bordered h-11 rounded-xl text-sm bg-slate-50 text-slate-500" 
                        />
                    </div>

                    <div className="divider my-2"></div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="form-control">
                            <label className="label py-1"><span className="label-text text-[0.625rem] font-extrabold uppercase tracking-wider text-slate-500">Foto Depan <span className="text-rose-500">*</span></span></label>
                            <input 
                                type="file" 
                                accept="image/*"
                                capture="environment"
                                ref={fileInputDepan}
                                onChange={e => setData('foto_tampak_depan', e.target.files ? e.target.files[0] : null)}
                                className="hidden" 
                            />
                            
                            {data.foto_tampak_depan ? (
                                <div className="mt-1 relative inline-block w-full">
                                    <div className="absolute top-2 left-2 bg-emerald-500 text-white rounded-full p-1 shadow-md z-10 animate-bounce">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3"><path fillRule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clipRule="evenodd" /></svg>
                                    </div>
                                    <img src={previewUrlDepan!} className="w-full h-32 object-contain rounded-xl border border-slate-200 bg-slate-50" />
                                    <button type="button" onClick={() => { setData('foto_tampak_depan', null); if (fileInputDepan.current) fileInputDepan.current.value = ''; }} className="absolute -top-2 -right-2 btn btn-xs btn-circle btn-error text-white shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                                    </button>
                                </div>
                            ) : !data.foto_tampak_depan && existingFotoDepan ? (
                                <div className="mt-1 relative inline-block w-full">
                                    <img src={existingFotoDepan} className="w-full h-32 object-contain rounded-xl border border-slate-200 bg-slate-50" />
                                    <button type="button" onClick={() => handleDeleteImage('depan')} className="absolute -top-2 -right-2 btn btn-xs btn-circle btn-error text-white shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                                    </button>
                                </div>
                            ) : (
                                <div 
                                    onClick={() => fileInputDepan.current?.click()}
                                    className={`mt-1 h-32 w-full rounded-xl border-2 border-dashed ${errors.foto_tampak_depan ? 'border-rose-300 bg-rose-50 text-rose-500' : 'border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-400'} flex flex-col items-center justify-center cursor-pointer transition-colors`}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-8 h-8 mb-2">
                                      <path strokeLinecap="round" strokeLinejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                      <path strokeLinecap="round" strokeLinejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                    <span className="text-[0.625rem] font-bold">Ambil Foto</span>
                                </div>
                            )}
                            {errors.foto_tampak_depan && <span className="text-error text-[0.625rem] mt-1">{errors.foto_tampak_depan}</span>}
                        </div>

                        <div className="form-control">
                            <label className="label py-1"><span className="label-text text-[0.625rem] font-extrabold uppercase tracking-wider text-slate-500">Foto Belakang <span className="text-rose-500">*</span></span></label>
                            <input 
                                type="file" 
                                accept="image/*"
                                capture="environment"
                                ref={fileInputBelakang}
                                onChange={e => setData('foto_tampak_belakang', e.target.files ? e.target.files[0] : null)}
                                className="hidden" 
                            />
                            
                            {data.foto_tampak_belakang ? (
                                <div className="mt-1 relative inline-block w-full">
                                    <div className="absolute top-2 left-2 bg-emerald-500 text-white rounded-full p-1 shadow-md z-10 animate-bounce">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3"><path fillRule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clipRule="evenodd" /></svg>
                                    </div>
                                    <img src={previewUrlBelakang!} className="w-full h-32 object-contain rounded-xl border border-slate-200 bg-slate-50" />
                                    <button type="button" onClick={() => { setData('foto_tampak_belakang', null); if (fileInputBelakang.current) fileInputBelakang.current.value = ''; }} className="absolute -top-2 -right-2 btn btn-xs btn-circle btn-error text-white shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                                    </button>
                                </div>
                            ) : !data.foto_tampak_belakang && existingFotoBelakang ? (
                                <div className="mt-1 relative inline-block w-full">
                                    <img src={existingFotoBelakang} className="w-full h-32 object-contain rounded-xl border border-slate-200 bg-slate-50" />
                                    <button type="button" onClick={() => handleDeleteImage('belakang')} className="absolute -top-2 -right-2 btn btn-xs btn-circle btn-error text-white shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                                    </button>
                                </div>
                            ) : (
                                <div 
                                    onClick={() => fileInputBelakang.current?.click()}
                                    className={`mt-1 h-32 w-full rounded-xl border-2 border-dashed ${errors.foto_tampak_belakang ? 'border-rose-300 bg-rose-50 text-rose-500' : 'border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-400'} flex flex-col items-center justify-center cursor-pointer transition-colors`}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-8 h-8 mb-2">
                                      <path strokeLinecap="round" strokeLinejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                      <path strokeLinecap="round" strokeLinejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                    <span className="text-[0.625rem] font-bold">Ambil Foto</span>
                                </div>
                            )}
                            {errors.foto_tampak_belakang && <span className="text-error text-[0.625rem] mt-1">{errors.foto_tampak_belakang}</span>}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 mt-2">
                        <div className="form-control">
                            <label className="label py-1"><span className="label-text text-[0.625rem] font-extrabold uppercase tracking-wider text-slate-500">Kondisi HP <span className="text-rose-500">*</span></span></label>
                            {(() => {
                                const isOthers = data.kondisi_hp !== '' && !PREDEFINED_KONDISI_HP.includes(data.kondisi_hp);
                                return (
                                    <>
                                        <select 
                                            value={isOthers ? 'Others' : data.kondisi_hp}
                                            onChange={e => {
                                                if (e.target.value === 'Others') {
                                                    setData('kondisi_hp', '__others__');
                                                } else {
                                                    setData('kondisi_hp', e.target.value);
                                                }
                                            }}
                                            className={`select select-bordered h-11 rounded-xl text-sm bg-slate-50 ${isOthers ? 'mb-2' : ''}`} 
                                            required
                                        >
                                            <option value="" disabled>Pilih Kondisi HP</option>
                                            {PREDEFINED_KONDISI_HP.map(k => <option key={k} value={k}>{k}</option>)}
                                            <option value="Others">Others (silahkan isi)</option>
                                        </select>

                                        {isOthers && (
                                            <input 
                                                type="text" 
                                                value={data.kondisi_hp === '__others__' ? '' : data.kondisi_hp}
                                                onChange={e => setData('kondisi_hp', e.target.value || '__others__')}
                                                className="input input-bordered h-11 rounded-xl text-sm bg-slate-50 w-full" 
                                                placeholder="Silahkan isi kondisi lainnya..." 
                                                required 
                                                autoFocus
                                            />
                                        )}
                                    </>
                                );
                            })()}
                            {errors.kondisi_hp && <span className="text-error text-[0.625rem] mt-1">{errors.kondisi_hp}</span>}
                        </div>
                        <div className="form-control">
                            <label className="label py-1"><span className="label-text text-[0.625rem] font-extrabold uppercase tracking-wider text-slate-500">Kondisi Kartu <span className="text-rose-500">*</span></span></label>
                            <select 
                                value={data.kondisi_kartu}
                                onChange={e => setData('kondisi_kartu', e.target.value)}
                                className="select select-bordered h-11 rounded-xl text-sm bg-slate-50" 
                                required
                            >
                                <option value="" disabled>Pilih</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Mati">Mati</option>
                                <option value="Hilang">Hilang</option>
                            </select>
                            {errors.kondisi_kartu && <span className="text-error text-[0.625rem] mt-1">{errors.kondisi_kartu}</span>}
                        </div>
                    </div>

                    <div className="pb-16 pt-2"></div> 
                    
                    <div className="absolute bottom-0 left-0 right-0 p-5 border-t border-slate-100 bg-slate-50 flex items-center gap-3">
                        <button type="button" onClick={onClose} className="btn btn-outline border-slate-200 flex-1 h-11 rounded-xl text-xs font-bold text-slate-700">Batal</button>
                        <button type="submit" disabled={processing || !isFormValid} className="btn btn-primary flex-1 h-11 rounded-xl text-xs font-bold text-white shadow-md shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            {processing ? <span className="loading loading-spinner loading-sm"></span> : 'Simpan'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};
