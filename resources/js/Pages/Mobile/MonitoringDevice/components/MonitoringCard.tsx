import React from 'react';

export const MonitoringCard = ({ item, mData, onAdd, onDetail }: any) => {
    return (
        <div className="bg-white border border-slate-100 rounded-2xl p-4 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] flex flex-col gap-3.5">
            <div className="flex flex-col gap-1.5">
                {/* Line 1: Month Year | Status Badge */}
                <div className="flex items-center justify-between gap-2">
                    <span className="text-[0.625rem] font-bold text-slate-500 uppercase tracking-wider">{item.month_name}</span>
                    {mData ? (
                        <span className="text-[0.5rem] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border bg-emerald-50 text-emerald-600 border-emerald-100/80">Sudah Update</span>
                    ) : (
                        <span className="text-[0.5rem] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border bg-rose-50 text-rose-600 border-rose-100/80">Belum Update</span>
                    )}
                </div>
                
                {/* Line 2: Sales Name - Sales Code */}
                <div className="flex items-center gap-1.5">
                    <span className="text-xs font-bold text-slate-700 truncate">{item.sales_name}</span>
                    <span className="text-[0.5625rem] px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold font-mono tracking-wider shrink-0">{item.sales_code}</span>
                </div>

                {/* Line 3: Distributor Name */}
                <h4 className="text-[0.6875rem] font-black text-slate-900 tracking-tight leading-snug">{item.distributor_name}</h4>
            </div>
            
            {mData && (
                <div className="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100/50">
                    <div>
                        <span className="text-[0.5rem] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Kondisi HP</span>
                        <span className="text-[0.625rem] font-black text-slate-700 truncate block max-w-[7.5rem]" title={mData.kondisi_hp}>{mData.kondisi_hp || '-'}</span>
                    </div>
                    <div>
                        <span className="text-[0.5rem] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Kondisi Kartu</span>
                        <span className={`text-[0.625rem] font-black ${mData.kondisi_kartu === 'Aktif' ? 'text-emerald-600' : 'text-rose-600'}`}>{mData.kondisi_kartu || '-'}</span>
                    </div>
                </div>
            )}
            
            <div className="flex flex-wrap items-center justify-between gap-2.5 border-t border-slate-100 pt-3">
                <div className="flex items-center gap-1.5">
                    {mData && (
                        <>
                            {mData.foto_tampak_depan ? (
                                <div className="flex items-center gap-1 px-2 py-1 rounded-full text-[0.5rem] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" /></svg> Depan
                                </div>
                            ) : (
                                <div className="flex items-center gap-1 px-2 py-1 rounded-full text-[0.5rem] font-bold bg-slate-50 text-slate-400 border border-slate-100/50">
                                    <span className="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Depan
                                </div>
                            )}

                            {mData.foto_tampak_belakang ? (
                                <div className="flex items-center gap-1 px-2 py-1 rounded-full text-[0.5rem] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-3 h-3"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" /></svg> Belakang
                                </div>
                            ) : (
                                <div className="flex items-center gap-1 px-2 py-1 rounded-full text-[0.5rem] font-bold bg-slate-50 text-slate-400 border border-slate-100/50">
                                    <span className="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Belakang
                                </div>
                            )}
                        </>
                    )}
                </div>

                <div className="flex items-center gap-1.5">
                    {mData ? (
                        <button onClick={onDetail} className="btn btn-xs btn-outline border-slate-200 hover:bg-slate-100 h-8 rounded-lg text-[0.5625rem] uppercase font-black text-slate-700 tracking-wider flex items-center gap-1 shadow-xs">
                            Detail
                        </button>
                    ) : (
                        <button onClick={onAdd} className="btn btn-xs btn-primary h-8 rounded-lg text-[0.5625rem] uppercase font-black text-white tracking-wider flex items-center gap-1 shadow-xs">
                            Tambah
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
};
