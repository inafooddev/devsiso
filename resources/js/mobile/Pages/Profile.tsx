import React, { useState, useEffect } from 'react';
import { Head, router, usePage, useForm } from '@inertiajs/react';
import MobileLayout from '../Layouts/MobileLayout';
import Card from '../Components/UI/Card';
import UserInfo from '../Components/UserInfo';
import Button from '../Components/UI/Button';
import { ShieldCheckIcon, XMarkIcon, EyeIcon, EyeSlashIcon } from '@heroicons/react/24/outline';

// Modal component wrapper
const BottomModal = ({ isOpen, onClose, title, children }: any) => {
    if (!isOpen) return null;
    return (
        <div className="fixed inset-0 z-[100] flex items-end justify-center sm:items-center">
            <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onClick={onClose}></div>
            <div className="relative bg-white rounded-t-3xl sm:rounded-3xl w-full sm:max-w-md p-6 shadow-xl transform transition-all duration-300">
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-lg font-black text-slate-800">{title}</h3>
                    <button type="button" onClick={onClose} className="p-2 bg-slate-100 rounded-full text-slate-500 hover:bg-slate-200">
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>
                {children}
            </div>
        </div>
    );
};

export default function Profile() {
    const { auth, flash } = usePage().props as any;
    const currentUser = auth?.user || { 
        name: "Guest", 
        email: "-", 
        role: "Unknown" 
    };

    const [isProfileOpen, setIsProfileOpen] = useState(false);
    const [isPasswordOpen, setIsPasswordOpen] = useState(false);
    const [showCurrentPassword, setShowCurrentPassword] = useState(false);
    const [showNewPassword, setShowNewPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    // Profile Form
    const profileForm = useForm({
        name: currentUser.name || '',
        email: currentUser.email || '',
    });

    useEffect(() => {
        if (currentUser.name || currentUser.email) {
            profileForm.setDefaults({
                name: currentUser.name || '',
                email: currentUser.email || '',
            });
            profileForm.reset();
        }
    }, [currentUser.name, currentUser.email]);

    const submitProfile = (e: React.FormEvent) => {
        e.preventDefault();
        profileForm.post('/mobile/profile/update', {
            onSuccess: () => {
                setIsProfileOpen(false);
            },
        });
    };

    // Password Form
    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submitPassword = (e: React.FormEvent) => {
        e.preventDefault();
        passwordForm.post('/mobile/profile/password', {
            onSuccess: () => {
                passwordForm.reset();
                setIsPasswordOpen(false);
            },
        });
    };

    return (
        <MobileLayout user={currentUser} title="Profil Saya" backUrl="/mobile/home">
            <Head title="Profil" />
            
            <div className="px-4 md:px-6 pt-6 md:pt-8 space-y-6 animate-fade-in pb-10">
                <UserInfo user={currentUser} />

                {flash?.success && (
                    <div className="p-4 bg-emerald-50 rounded-2xl flex items-start gap-3 border border-emerald-100">
                        <ShieldCheckIcon className="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" />
                        <div>
                            <p className="text-xs font-bold text-emerald-800">{flash.success}</p>
                        </div>
                    </div>
                )}

                <section>
                    <h2 className="text-sm font-bold text-slate-500 uppercase tracking-wide mb-3 px-1">Pengaturan</h2>
                    <Card padding="none" className="overflow-hidden">
                        <ul className="divide-y divide-slate-100">
                            <li onClick={() => setIsProfileOpen(true)} className="flex justify-between items-center p-4 hover:bg-indigo-50 cursor-pointer active:bg-indigo-100 transition-colors group">
                                <div className="flex items-center space-x-3">
                                    <div className="w-8 h-8 rounded-full bg-slate-50 group-hover:bg-indigo-100 flex items-center justify-center text-slate-400 group-hover:text-indigo-600 transition-colors">
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
                                    </div>
                                    <span className="font-semibold text-slate-700 text-sm group-hover:text-indigo-900 transition-colors">Pengaturan Akun</span>
                                </div>
                                <svg className="w-4 h-4 text-slate-300 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
                            </li>
                            <li onClick={() => setIsPasswordOpen(true)} className="flex justify-between items-center p-4 hover:bg-indigo-50 cursor-pointer active:bg-indigo-100 transition-colors group">
                                <div className="flex items-center space-x-3">
                                    <div className="w-8 h-8 rounded-full bg-slate-50 group-hover:bg-indigo-100 flex items-center justify-center text-slate-400 group-hover:text-indigo-600 transition-colors">
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    </div>
                                    <span className="font-semibold text-slate-700 text-sm group-hover:text-indigo-900 transition-colors">Keamanan & Password</span>
                                </div>
                                <svg className="w-4 h-4 text-slate-300 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
                            </li>
                        </ul>
                    </Card>
                </section>

                <div className="pt-2">
                    <Button fullWidth variant="danger" size="lg" onClick={() => router.post('/mobile/logout')} className="shadow-sm">
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Keluar (Logout)
                    </Button>
                </div>
            </div>

            {/* Profile Update Modal */}
            <BottomModal isOpen={isProfileOpen} onClose={() => setIsProfileOpen(false)} title="Pengaturan Akun">
                <form onSubmit={submitProfile} className="space-y-4">
                    <div>
                        <label className="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap</label>
                        <input 
                            type="text" 
                            value={profileForm.data.name} 
                            onChange={e => profileForm.setData('name', e.target.value)} 
                            className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-black uppercase focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"
                        />
                        {profileForm.errors.name && <p className="text-rose-500 text-xs mt-1 font-medium">{profileForm.errors.name}</p>}
                    </div>
                    <div>
                        <label className="block text-xs font-bold text-slate-600 mb-1">Email</label>
                        <input 
                            type="email" 
                            value={profileForm.data.email} 
                            onChange={e => profileForm.setData('email', e.target.value)} 
                            className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-black focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"
                        />
                        {profileForm.errors.email && <p className="text-rose-500 text-xs mt-1 font-medium">{profileForm.errors.email}</p>}
                    </div>
                    <div className="pt-3">
                        <Button type="submit" fullWidth disabled={profileForm.processing}>
                            {profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                        </Button>
                    </div>
                </form>
            </BottomModal>

            {/* Password Update Modal */}
            <BottomModal isOpen={isPasswordOpen} onClose={() => setIsPasswordOpen(false)} title="Ubah Password">
                <form onSubmit={submitPassword} className="space-y-4">
                    <div>
                        <label className="block text-xs font-bold text-slate-600 mb-1">Password Saat Ini</label>
                        <div className="relative">
                            <input 
                                type={showCurrentPassword ? "text" : "password"} 
                                value={passwordForm.data.current_password} 
                                onChange={e => passwordForm.setData('current_password', e.target.value)} 
                                className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-12 py-3 text-sm text-black focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"
                            />
                            <button
                                type="button"
                                onClick={() => setShowCurrentPassword(!showCurrentPassword)}
                                className="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 transition-colors"
                            >
                                {showCurrentPassword ? <EyeSlashIcon className="h-5 w-5" /> : <EyeIcon className="h-5 w-5" />}
                            </button>
                        </div>
                        {passwordForm.errors.current_password && <p className="text-rose-500 text-xs mt-1 font-medium">{passwordForm.errors.current_password}</p>}
                    </div>
                    <div>
                        <label className="block text-xs font-bold text-slate-600 mb-1">Password Baru</label>
                        <div className="relative">
                            <input 
                                type={showNewPassword ? "text" : "password"} 
                                value={passwordForm.data.password} 
                                onChange={e => passwordForm.setData('password', e.target.value)} 
                                className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-12 py-3 text-sm text-black focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"
                            />
                            <button
                                type="button"
                                onClick={() => setShowNewPassword(!showNewPassword)}
                                className="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 transition-colors"
                            >
                                {showNewPassword ? <EyeSlashIcon className="h-5 w-5" /> : <EyeIcon className="h-5 w-5" />}
                            </button>
                        </div>
                        {passwordForm.errors.password && <p className="text-rose-500 text-xs mt-1 font-medium">{passwordForm.errors.password}</p>}
                    </div>
                    <div>
                        <label className="block text-xs font-bold text-slate-600 mb-1">Konfirmasi Password Baru</label>
                        <div className="relative">
                            <input 
                                type={showConfirmPassword ? "text" : "password"} 
                                value={passwordForm.data.password_confirmation} 
                                onChange={e => passwordForm.setData('password_confirmation', e.target.value)} 
                                className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-12 py-3 text-sm text-black focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"
                            />
                            <button
                                type="button"
                                onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                className="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 transition-colors"
                            >
                                {showConfirmPassword ? <EyeSlashIcon className="h-5 w-5" /> : <EyeIcon className="h-5 w-5" />}
                            </button>
                        </div>
                    </div>
                    <div className="pt-3">
                        <Button type="submit" fullWidth disabled={passwordForm.processing}>
                            {passwordForm.processing ? 'Menyimpan...' : 'Perbarui Password'}
                        </Button>
                    </div>
                </form>
            </BottomModal>
        </MobileLayout>
    );
}
