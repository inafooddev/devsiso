import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import Button from '../../Components/UI/Button';

export default function Login() {
    const [isLoading, setIsLoading] = useState(false);

    const handleLogin = (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        // Mocking a network request
        setTimeout(() => {
            router.get('/mobile/home');
        }, 1000);
    };

    return (
        <div className="min-h-screen flex flex-col justify-center relative overflow-hidden bg-slate-50 font-sans">
            <Head title="Login" />
            
            {/* Background Orbs for Premium Glassmorphism Look */}
            <div className="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none -z-10">
                <div className="absolute -top-[10%] -left-[10%] w-[60vw] h-[60vw] md:w-[40vw] md:h-[40vw] rounded-full bg-indigo-400/40 mix-blend-multiply filter blur-[60px] animate-blob"></div>
                <div className="absolute top-[20%] -right-[10%] w-[50vw] h-[50vw] md:w-[30vw] md:h-[30vw] rounded-full bg-purple-400/40 mix-blend-multiply filter blur-[60px] animate-blob animation-delay-2000"></div>
                <div className="absolute -bottom-[10%] left-[20%] w-[70vw] h-[70vw] md:w-[50vw] md:h-[50vw] rounded-full bg-blue-400/40 mix-blend-multiply filter blur-[60px] animate-blob animation-delay-4000"></div>
            </div>

            <div className="w-full max-w-md mx-auto px-6 lg:px-8 relative z-10">
                
                {/* Header Section */}
                <div className="text-center mb-10 animate-fade-in">
                    <div className="mx-auto w-24 h-24 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-3xl flex items-center justify-center shadow-xl shadow-indigo-200/50 mb-6 transform -rotate-3 hover:rotate-0 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                        </svg>
                    </div>
                    <h2 className="text-3xl md:text-4xl font-black text-slate-800 tracking-tight">
                        Welcome Back
                    </h2>
                    <p className="mt-2 text-sm font-medium text-slate-500">
                        Masuk untuk melanjutkan ke sistem
                    </p>
                </div>

                {/* Form Card (Glassmorphism) */}
                <div className="bg-white/60 backdrop-blur-2xl rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/80 p-6 md:p-8 animate-slide-up">
                    <form className="space-y-5" onSubmit={handleLogin}>
                        
                        {/* User ID Input */}
                        <div>
                            <label htmlFor="user_id" className="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-2">User ID</label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg className="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input id="user_id" name="user_id" type="text" required defaultValue="SLS-001" placeholder="Masukkan User ID Anda"
                                    className="block w-full pl-11 pr-4 py-4 bg-white/70 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all" />
                            </div>
                        </div>

                        {/* Password Input */}
                        <div>
                            <label htmlFor="password" className="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-2">Password</label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg className="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input id="password" name="password" type="password" autoComplete="current-password" required defaultValue="password"
                                    className="block w-full pl-11 pr-4 py-4 bg-white/70 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all" />
                            </div>
                        </div>

                        {/* Remember & Forgot Password */}
                        <div className="flex items-center justify-between pt-1">
                            <div className="flex items-center">
                                <input id="remember-me" name="remember-me" type="checkbox" className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded" />
                                <label htmlFor="remember-me" className="ml-2 block text-sm font-medium text-slate-600"> Ingat saya </label>
                            </div>
                            <div className="text-sm">
                                <a href="#" className="font-bold text-indigo-600 hover:text-indigo-500 transition-colors"> Lupa password? </a>
                            </div>
                        </div>

                        {/* Submit Button */}
                        <div className="pt-5">
                            <Button type="submit" fullWidth isLoading={isLoading} size="lg" className="py-3.5 text-base font-bold tracking-wide rounded-2xl shadow-md shadow-indigo-200/50">
                                Masuk
                            </Button>
                        </div>
                    </form>
                </div>
                
                {/* Footer text */}
                <p className="text-center text-xs text-slate-400 mt-10 font-medium">
                    &copy; 2026 SISO. All rights reserved.
                </p>
            </div>
            
            {/* Inline styles for custom blob animation */}
            <style dangerouslySetInnerHTML={{__html: `
                @keyframes blob {
                    0% { transform: translate(0px, 0px) scale(1); }
                    33% { transform: translate(30px, -50px) scale(1.1); }
                    66% { transform: translate(-20px, 20px) scale(0.9); }
                    100% { transform: translate(0px, 0px) scale(1); }
                }
                .animate-blob {
                    animation: blob 7s infinite;
                }
                .animation-delay-2000 {
                    animation-delay: 2s;
                }
                .animation-delay-4000 {
                    animation-delay: 4s;
                }
            `}} />
        </div>
    );
}
