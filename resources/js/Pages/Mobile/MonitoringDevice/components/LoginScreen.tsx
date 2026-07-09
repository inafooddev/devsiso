import { useForm } from '@inertiajs/react';
import React from 'react';

export const LoginScreen = () => {
    const { data, setData, post, processing, errors } = useForm({
        sales_code: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/app/monitoring-device/login');
    };

    return (
        <div className="min-h-screen bg-slate-50 flex flex-col justify-center items-center p-6 relative overflow-hidden">
            <div className="w-full max-w-sm bg-white rounded-[2rem] shadow-xl border border-slate-100 p-8 relative z-10">
                <div className="w-16 h-16 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-6 mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="w-8 h-8">
                        <path d="M10.5 18.75a.75.75 0 000 1.5h3a.75.75 0 000-1.5h-3z" />
                        <path fillRule="evenodd" d="M8.625.75A3.375 3.375 0 005.25 4.125v15.75a3.375 3.375 0 003.375 3.375h6.75a3.375 3.375 0 003.375-3.375V4.125A3.375 3.375 0 0015.375.75h-6.75zM7.5 4.125C7.5 3.504 8.004 3 8.625 3H9.75v.375c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V3h1.125c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-6.75A1.125 1.125 0 017.5 19.875V4.125z" clipRule="evenodd" />
                    </svg>
                </div>
                
                <h1 className="text-xl font-black text-slate-800 text-center uppercase tracking-tight">Monitoring Device</h1>
                <p className="text-xs text-slate-500 font-medium text-center mt-2 mb-8">Silakan masukkan Kode Sales Anda untuk melanjutkan.</p>
                
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="form-control">
                        <label className="label py-1">
                            <span className="label-text text-[0.625rem] font-extrabold uppercase tracking-wider text-slate-500">Kode Sales</span>
                        </label>
                        <input 
                            type="text" 
                            className="input input-bordered h-12 rounded-xl text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-center uppercase tracking-widest"
                            placeholder="Contoh: SLS001"
                            value={data.sales_code}
                            onChange={e => setData('sales_code', e.target.value.toUpperCase())}
                            required
                            autoComplete="off"
                        />
                        {errors.sales_code && <span className="text-error text-[0.625rem] mt-1.5 font-semibold text-center block">{errors.sales_code}</span>}
                    </div>

                    <button 
                        type="submit" 
                        disabled={processing}
                        className="btn btn-primary w-full h-12 rounded-xl font-bold text-white shadow-lg shadow-primary/30 mt-2"
                    >
                        {processing ? <span className="loading loading-spinner loading-sm"></span> : 'Masuk'}
                    </button>
                </form>
            </div>
            
            {/* Decorative blobs */}
            <div className="absolute top-[-10%] left-[-10%] w-64 h-64 bg-primary/10 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
            <div className="absolute top-[20%] right-[-10%] w-64 h-64 bg-sky-200 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
            <div className="absolute bottom-[-10%] left-[20%] w-64 h-64 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000"></div>
        </div>
    );
};
