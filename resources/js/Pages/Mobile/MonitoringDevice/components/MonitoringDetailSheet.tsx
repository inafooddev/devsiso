import React, { useState, useEffect } from 'react';

export const MonitoringDetailSheet = ({ show, onClose, data, salesName, onEdit }: any) => {
    const [previewImage, setPreviewImage] = useState<string | null>(null);
    const [render, setRender] = useState(show);
    const [animate, setAnimate] = useState(false);

    useEffect(() => {
        if (show) {
            setRender(true);
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => requestAnimationFrame(() => setAnimate(true)));
        } else {
            setAnimate(false);
            setPreviewImage(null);
            document.body.style.overflow = '';
        }
        return () => { document.body.style.overflow = ''; };
    }, [show]);

    const handleAnimationEnd = () => {
        if (!show) setRender(false);
    };

    if (!render || !data) return null;

    return (
        <div className="fixed inset-0 z-40">
            <div className={`fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-300 ${animate ? 'opacity-100' : 'opacity-0'}`} onClick={onClose}></div>
            <div 
                className={`fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[85%] flex flex-col z-50 transition-transform duration-300 ease-out ${animate ? 'translate-y-0' : 'translate-y-full'} overflow-hidden`}
                onTransitionEnd={handleAnimationEnd}
            >
                
                <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                
                <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                    <div className="min-w-0 pr-4">
                        <span className="badge badge-secondary badge-xs font-mono font-bold rounded-lg px-2 text-[0.5625rem]">Detail Monitoring</span>
                        <h4 className="text-xs font-black text-slate-900 mt-1 truncate">{data.sales_code} - {salesName}</h4>
                    </div>
                    <button onClick={onClose} className="btn btn-ghost btn-circle btn-xs text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-5 h-5"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                    </button>
                </div>
                
                <div className="flex-1 overflow-y-auto p-5 space-y-5 text-xs font-medium text-slate-600">
                    <div className="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
                        <div className="col-span-2">
                            <span className="text-[0.5625rem] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Bulan</span>
                            <span className="font-black text-slate-800">{data.tanggal_formatted}</span>
                        </div>
                        <div className="col-span-2">
                            <span className="text-[0.5625rem] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Distributor</span>
                            <span className="font-bold text-slate-800">{data.distributor_code}</span>
                        </div>
                        <div>
                            <span className="text-[0.5625rem] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Kondisi HP</span>
                            <span className="font-black text-slate-800">{data.kondisi_hp || '-'}</span>
                        </div>
                        <div>
                            <span className="text-[0.5625rem] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Kondisi Kartu</span>
                            <span className={`font-black ${data.kondisi_kartu === 'Aktif' ? 'text-emerald-600' : 'text-rose-600'}`}>{data.kondisi_kartu || '-'}</span>
                        </div>
                    </div>

                    <div className="space-y-3 pb-8">
                        <h5 className="text-[0.625rem] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3.5 h-3.5"><path fillRule="evenodd" d="M1 8a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 018.07 3h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0016.07 6H17a2 2 0 012 2v7a2 2 0 01-2 2H3a2 2 0 01-2-2V8zm13.5 3a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM10 14a3 3 0 100-6 3 3 0 000 6z" clipRule="evenodd" /></svg>
                            Foto Tersimpan
                        </h5>
                        <div className="grid grid-cols-2 gap-3">
                            {/* Foto Depan */}
                            <div className="border border-slate-200 rounded-xl p-2 bg-white flex flex-col items-center">
                                <span className="text-[0.5625rem] font-extrabold uppercase tracking-wider text-slate-500 mb-2">Device Depan</span>
                                {data.foto_tampak_depan_url ? (
                                    <img 
                                        src={data.foto_tampak_depan_url} 
                                        onClick={() => setPreviewImage(data.foto_tampak_depan_url)}
                                        className="w-full h-32 object-contain rounded-lg bg-slate-50 cursor-pointer hover:opacity-90 transition-opacity" 
                                    />
                                ) : (
                                    <div className="w-full h-32 bg-slate-50 rounded-lg flex flex-col items-center justify-center text-slate-400">
                                        <span className="text-[0.5rem] font-bold">Tidak ada foto</span>
                                    </div>
                                )}
                            </div>

                            {/* Foto Belakang */}
                            <div className="border border-slate-200 rounded-xl p-2 bg-white flex flex-col items-center">
                                <span className="text-[0.5625rem] font-extrabold uppercase tracking-wider text-slate-500 mb-2">Device Belakang</span>
                                {data.foto_tampak_belakang_url ? (
                                    <img 
                                        src={data.foto_tampak_belakang_url} 
                                        onClick={() => setPreviewImage(data.foto_tampak_belakang_url)}
                                        className="w-full h-32 object-contain rounded-lg bg-slate-50 cursor-pointer hover:opacity-90 transition-opacity" 
                                    />
                                ) : (
                                    <div className="w-full h-32 bg-slate-50 rounded-lg flex flex-col items-center justify-center text-slate-400">
                                        <span className="text-[0.5rem] font-bold">Tidak ada foto</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="p-5 border-t border-slate-100 bg-slate-50 shrink-0">
                    <button onClick={onEdit} className="btn btn-primary w-full h-11 rounded-xl text-xs font-bold text-white shadow-md shadow-primary/20 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-4 h-4"><path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" /><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" /></svg>
                        Edit Data
                    </button>
                </div>
            </div>

            {/* Full Screen Image Preview Modal */}
            {previewImage && (
                <div className="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4 animate-fade-in" onClick={() => setPreviewImage(null)}>
                    <button className="absolute top-4 right-4 btn btn-circle btn-sm btn-ghost text-white bg-black/50 hover:bg-black/80" onClick={() => setPreviewImage(null)}>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-5 h-5"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                    </button>
                    <img src={previewImage} className="max-w-full max-h-full object-contain rounded-lg shadow-2xl" onClick={(e) => e.stopPropagation()} />
                </div>
            )}
        </div>
    );
};
