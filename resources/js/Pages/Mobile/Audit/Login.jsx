import React, { useEffect } from "react";
import { Head, useForm } from "@inertiajs/react";
import { ShieldCheckIcon } from "@heroicons/react/24/outline";

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        user_id: "",
        password: "",
        remember: false,
    });

    useEffect(() => {
        return () => {
            reset("password");
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post(route("mobile.audit.login"));
    };

    return (
        <div className="w-full min-h-screen bg-gradient-to-br from-indigo-50 via-slate-50 to-indigo-100/50 flex items-center justify-center p-6">
            <Head title="Login Auditor - Audit Toko" />
            <div className="w-full max-w-sm bg-white/90 backdrop-blur-lg border border-slate-200/50 rounded-3xl shadow-xl p-8 flex flex-col items-center animate-fade-in">
                <div className="w-14 h-14 rounded-2xl bg-indigo-600/10 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-600/10 mb-4 animate-bounce-slow">
                    <ShieldCheckIcon className="w-8 h-8" />
                </div>
                <h2 className="text-sm md:text-base font-black uppercase tracking-wider text-slate-900 leading-tight text-center">
                    Sistem Audit Toko
                </h2>
                <p className="text-[10px] font-bold text-indigo-600 tracking-widest uppercase mb-8 leading-none text-center">
                    Login Auditor
                </p>

                <form onSubmit={submit} className="w-full flex flex-col gap-4">
                    <div>
                        <label
                            htmlFor="user_id"
                            className="block text-xs font-bold text-slate-700 mb-1.5 ml-1 uppercase tracking-wide"
                        >
                            User ID
                        </label>
                        <input
                            id="user_id"
                            type="text"
                            name="user_id"
                            value={data.user_id}
                            onChange={(e) => setData("user_id", e.target.value)}
                            className="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                            placeholder="Masukkan User ID Anda"
                            autoComplete="username"
                        />
                        {errors.user_id && (
                            <p className="text-[11px] text-red-500 mt-1.5 ml-1 font-medium">
                                {errors.user_id}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="password"
                            className="block text-xs font-bold text-slate-700 mb-1.5 ml-1 uppercase tracking-wide"
                        >
                            Password
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            onChange={(e) => setData("password", e.target.value)}
                            className="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                            placeholder="Masukkan Password"
                            autoComplete="current-password"
                        />
                        {errors.password && (
                            <p className="text-[11px] text-red-500 mt-1.5 ml-1 font-medium">
                                {errors.password}
                            </p>
                        )}
                    </div>
                    
                    <div className="flex items-center mt-2 ml-1">
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData("remember", e.target.checked)}
                                className="w-4 h-4 rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            />
                            <span className="text-xs font-bold text-slate-600">Ingat Saya</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full mt-4 bg-indigo-600 text-white font-bold text-sm py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/40 active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                    >
                        {processing ? (
                            <>
                                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sedang memproses...
                            </>
                        ) : (
                            "Masuk Sekarang"
                        )}
                    </button>
                </form>

                <div className="mt-8 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">
                    PT INAFOOD © {new Date().getFullYear()}
                </div>
            </div>
        </div>
    );
}
