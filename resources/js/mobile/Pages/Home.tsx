import React, { useState, useEffect, useMemo } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import {
    BuildingStorefrontIcon,
    MapPinIcon,
    ArrowRightIcon,
    CalendarDaysIcon,
    ArrowRightOnRectangleIcon,
    ClipboardDocumentCheckIcon,
    ArrowPathIcon,
    ShieldCheckIcon,
    SparklesIcon,
    SquaresPlusIcon,
    ClockIcon,
    BanknotesIcon,
} from '@heroicons/react/24/solid';

interface ModuleItem {
    id: string;
    title: string;
    quickSummary?: string;
    url?: string;
    icon: React.ComponentType<{ className?: string }>;
    iconBg: string;
    comingSoon?: boolean;
}

const RECENT_MODULE_STORAGE_KEY = 'siso_mobile_recent_module';

export default function Home() {
    const { auth } = usePage().props as any;
    const [loadingModule, setLoadingModule] = useState<string | null>(null);
    const [recentModuleId, setRecentModuleId] = useState<string>('jks-salesman');

    const user = auth?.user;
    const userName: string = user?.name || 'Pengguna SISO';
    const userId: string = user?.userid || '-';
    const accessLevel: string = (user?.role || 'nasional').toUpperCase();

    const userRolesRaw = user?.roles || (user?.role ? [user.role] : []);
    const userRoles: string[] = Array.isArray(userRolesRaw)
        ? userRolesRaw.map((r: any) =>
              (typeof r === 'object' && r !== null ? r.name : String(r)).toLowerCase()
          )
        : [String(userRolesRaw).toLowerCase()];

    const allowedAuditRoles = ['audit', 'manaudit', 'admin', 'super-admin'];
    const canAccessAudit = userRoles.some((r: string) => allowedAuditRoles.includes(r));

    // Inisial nama user untuk avatar
    const userInitials = useMemo(() => {
        const parts = userName.trim().split(/\s+/);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }
        return userName.slice(0, 2).toUpperCase();
    }, [userName]);

    // Sapaan otomatis berdasarkan jam lokal & tanggal hari ini
    const { greeting, formattedDate } = useMemo(() => {
        const now = new Date();
        const hour = now.getHours();
        let greet = 'Selamat Pagi';
        if (hour >= 11 && hour < 15) greet = 'Selamat Siang';
        else if (hour >= 15 && hour < 18) greet = 'Selamat Sore';
        else if (hour >= 18 || hour < 4) greet = 'Selamat Malam';

        const dateStr = now.toLocaleDateString('id-ID', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });

        return { greeting: greet, formattedDate: dateStr };
    }, []);

    // Daftar menu aktif sesuai hak akses user
    const activeModules = useMemo<ModuleItem[]>(() => {
        const list: ModuleItem[] = [
            {
                id: 'jks-salesman',
                title: 'JKS Salesman',
                quickSummary: 'Dashboard • Jadwal • NOO • Pengajuan',
                url: '/mobile/jks-salesman',
                icon: CalendarDaysIcon,
                iconBg: 'bg-gradient-to-br from-sky-500 to-indigo-600 shadow-sky-500/25',
            },
            {
                id: 'skb-rwo',
                title: 'Reward Outlet',
                quickSummary: 'Program SKB • Pencapaian • Histori Order',
                url: '/mobile/skb-rwo',
                icon: BuildingStorefrontIcon,
                iconBg: 'bg-gradient-to-br from-indigo-500 to-violet-600 shadow-indigo-500/25',
            },
            {
                id: 'perbaikan-tikor',
                title: 'Perbaikan Tikor',
                quickSummary: 'Validasi & Pembaruan Koordinat GPS Toko',
                url: '/mobile/perbaikan-tikor-tim-elite',
                icon: MapPinIcon,
                iconBg: 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-500/25',
            },
            {
                id: 'insentif-sales',
                title: 'Insentif Sales',
                quickSummary: 'Monitoring Proyeksi • SFA Alert • Tim',
                url: '/mobile/insentif',
                icon: BanknotesIcon,
                iconBg: 'bg-gradient-to-br from-amber-500 to-orange-600 shadow-amber-500/25',
            },
        ];

        if (canAccessAudit) {
            list.push({
                id: 'audit-toko',
                title: 'Audit Toko',
                quickSummary: 'Inspeksi Lapangan & Verifikasi Outlet',
                url: '/mobile/audit',
                icon: ClipboardDocumentCheckIcon,
                iconBg: 'bg-gradient-to-br from-purple-500 to-fuchsia-600 shadow-purple-500/25',
            });
        }

        return list;
    }, [canAccessAudit]);

    // Genapkan selalu menjadi 9 kotak menu (3 kolom x 3 baris)
    const gridModules = useMemo<ModuleItem[]>(() => {
        const result = [...activeModules];
        while (result.length < 9) {
            result.push({
                id: `coming-soon-${result.length + 1}`,
                title: 'Segera Hadir',
                icon: SquaresPlusIcon,
                iconBg: '',
                comingSoon: true,
            });
        }
        return result.slice(0, 9);
    }, [activeModules]);

    // Ambil riwayat modul terakhir yang diakses dari localStorage (default: JKS Salesman)
    useEffect(() => {
        try {
            const savedId = localStorage.getItem(RECENT_MODULE_STORAGE_KEY);
            if (savedId && activeModules.some((m) => m.id === savedId)) {
                setRecentModuleId(savedId);
            }
        } catch {
            // Abaikan jika storage tidak tersedia
        }
    }, [activeModules]);

    const recentModule = useMemo<ModuleItem>(() => {
        return (
            activeModules.find((m) => m.id === recentModuleId) ||
            activeModules[0]
        );
    }, [activeModules, recentModuleId]);

    useEffect(() => {
        const unbindFinish = router.on('finish', () => {
            setLoadingModule(null);
        });
        const unbindError = router.on('error', () => {
            setLoadingModule(null);
        });

        return () => {
            unbindFinish();
            unbindError();
        };
    }, []);

    const handleNavigate = (url: string, moduleName: string, moduleId?: string) => {
        if (loadingModule) return;
        if (moduleId) {
            try {
                localStorage.setItem(RECENT_MODULE_STORAGE_KEY, moduleId);
                setRecentModuleId(moduleId);
            } catch {
                // Abaikan jika storage tidak tersedia
            }
        }
        setLoadingModule(moduleName);
        router.visit(url, {
            onError: () => setLoadingModule(null),
            onCancel: () => setLoadingModule(null),
        });
    };

    const RecentIcon = recentModule.icon;

    return (
        <div className="min-h-screen bg-slate-50 font-sans pb-10 selection:bg-indigo-500 selection:text-white relative flex flex-col justify-between">
            <Head title="SISO Workspace" />

            {/* Global Navigation Loading Overlay */}
            {loadingModule && (
                <div className="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-sm animate-fade-in px-6">
                    <div className="bg-white rounded-3xl p-6 shadow-2xl flex flex-col items-center max-w-xs w-full text-center border border-slate-100 animate-scale-up">
                        <div className="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 mb-3.5 shadow-sm">
                            <ArrowPathIcon className="w-7 h-7 animate-spin text-indigo-600" />
                        </div>
                        <h3 className="text-sm font-black text-slate-800 tracking-tight">
                            Memuat Modul...
                        </h3>
                        <p className="text-xs font-bold text-indigo-600 mt-1">{loadingModule}</p>
                        <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-4">
                            <div className="bg-indigo-600 h-full rounded-full animate-pulse w-3/4 mx-auto" />
                        </div>
                        <p className="text-2xs text-slate-400 mt-3 font-medium">
                            Sedang menyiapkan data, mohon tunggu...
                        </p>
                    </div>
                </div>
            )}

            <div>
                {/* 1. Modern Dark Hero Header */}
                <header className="bg-slate-900 pt-6 pb-20 rounded-b-3xl relative overflow-hidden shadow-xl">
                    <div className="absolute -top-24 -right-16 w-72 h-72 bg-indigo-500/25 rounded-full blur-3xl pointer-events-none" />
                    <div className="absolute -bottom-24 -left-16 w-72 h-72 bg-sky-500/20 rounded-full blur-3xl pointer-events-none" />

                    <div className="relative z-10 px-5 max-w-2xl md:max-w-4xl mx-auto space-y-5">
                        {/* Top Bar: Brand Pill (Kiri) + Tanggal & Tombol Logout (Kanan) */}
                        <div className="flex items-center justify-between gap-2">
                            <div className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10">
                                <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                                <span className="text-2xs font-extrabold tracking-wider text-white uppercase">
                                    SISO Workspace
                                </span>
                            </div>

                            <div className="flex items-center gap-2.5">
                                <span className="text-2xs sm:text-xs text-slate-300 font-semibold">
                                    {formattedDate}
                                </span>
                                <Link
                                    href="/mobile/logout"
                                    method="post"
                                    as="button"
                                    onClick={() => setLoadingModule('Keluar Akun')}
                                    className="w-8 h-8 rounded-xl bg-rose-500/20 hover:bg-rose-500/35 backdrop-blur-md border border-rose-400/30 flex items-center justify-center text-rose-200 hover:text-white transition-colors active:scale-95"
                                    title="Keluar Aplikasi"
                                >
                                    <ArrowRightOnRectangleIcon className="w-4 h-4" />
                                </Link>
                            </div>
                        </div>

                        {/* Personalized User Greeting & Avatar Profile Button */}
                        <div className="flex items-center justify-between gap-3">
                            <div className="min-w-0 flex-1">
                                <p className="text-xs font-semibold text-indigo-300 flex items-center gap-1.5">
                                    <SparklesIcon className="w-3.5 h-3.5 shrink-0" />
                                    <span>{greeting},</span>
                                </p>
                                <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight truncate mt-0.5">
                                    {userName}
                                </h1>
                                <div className="flex items-center gap-2 mt-1.5 flex-wrap">
                                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-3xs font-bold uppercase tracking-wider bg-indigo-500/25 text-indigo-200 border border-indigo-400/30">
                                        <ShieldCheckIcon className="w-3 h-3 text-indigo-300" />
                                        Akses: {accessLevel}
                                    </span>
                                    <span className="text-2xs font-mono text-slate-400">
                                        ID: {userId}
                                    </span>
                                </div>
                            </div>

                            {/* Satu-satunya Tombol Profil: Avatar Inisial */}
                            <button
                                type="button"
                                onClick={() => handleNavigate('/mobile/profile', 'Profil Pengguna')}
                                disabled={Boolean(loadingModule)}
                                className="group flex flex-col items-center gap-1 shrink-0 active:scale-95 transition-transform"
                                title="Buka Profil Saya"
                            >
                                <div className="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 p-0.5 shadow-lg shadow-indigo-500/25">
                                    <div className="w-full h-full rounded-xl bg-slate-900/40 group-hover:bg-slate-900/20 backdrop-blur-sm flex items-center justify-center text-white font-black text-sm sm:text-base tracking-wider transition-colors">
                                        {userInitials}
                                    </div>
                                </div>
                                <span className="text-3xs font-bold text-slate-400 group-hover:text-white transition-colors">
                                    Profil
                                </span>
                            </button>
                        </div>
                    </div>
                </header>

                {/* 2. Main Content: Kartu Akses Terakhir + Grid 6 Menu (3x2) */}
                <main className="px-4 sm:px-5 -mt-12 relative z-20 max-w-2xl md:max-w-4xl mx-auto space-y-4">
                    {/* Kartu Atas: Terakhir Diakses (Quick Resume Banner) */}
                    <div
                        role="button"
                        tabIndex={0}
                        onClick={() =>
                            recentModule.url &&
                            handleNavigate(recentModule.url, recentModule.title, recentModule.id)
                        }
                        className="group block cursor-pointer select-none"
                    >
                        <div className="bg-white rounded-3xl p-4 shadow-md shadow-slate-200/70 hover:shadow-lg border border-slate-200/80 transition-all duration-200 active:scale-[0.99] flex items-center justify-between gap-3.5">
                            <div className="flex items-center gap-3.5 min-w-0 flex-1">
                                <div
                                    className={`w-12 h-12 rounded-2xl ${recentModule.iconBg} flex items-center justify-center shadow-md text-white shrink-0 group-hover:scale-105 transition-transform`}
                                >
                                    <RecentIcon className="w-6 h-6" />
                                </div>

                                <div className="min-w-0 flex-1">
                                    <span className="inline-flex items-center gap-1 text-3xs font-extrabold uppercase tracking-wider text-indigo-600">
                                        <ClockIcon className="w-3 h-3" />
                                        Terakhir Diakses
                                    </span>
                                    <h2 className="text-sm sm:text-base font-black text-slate-900 tracking-tight truncate">
                                        {recentModule.title}
                                    </h2>
                                    {recentModule.quickSummary && (
                                        <p className="text-2xs text-slate-400 font-medium truncate mt-0.5">
                                            {recentModule.quickSummary}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="w-9 h-9 rounded-xl bg-indigo-50/70 group-hover:bg-indigo-600 border border-indigo-100 flex items-center justify-center shrink-0 transition-colors">
                                {loadingModule === recentModule.title ? (
                                    <ArrowPathIcon className="w-4 h-4 text-indigo-600 group-hover:text-white animate-spin" />
                                ) : (
                                    <ArrowRightIcon className="w-4 h-4 text-indigo-600 group-hover:text-white transition-colors" />
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Grid 6 Menu Proporsional (3 Kolom x 2 Baris) */}
                    <div>
                        <div className="px-1 mb-2.5 flex items-center justify-between">
                            <h3 className="text-2xs sm:text-xs font-extrabold uppercase tracking-wider text-slate-400">
                                Menu Aplikasi
                            </h3>
                            <span className="text-3xs sm:text-2xs font-semibold text-slate-400">
                                {activeModules.length} Modul Aktif
                            </span>
                        </div>

                        <div className="grid grid-cols-3 gap-2.5 sm:gap-3.5">
                            {gridModules.map((mod) => {
                                const Icon = mod.icon;
                                const isLoadingThis = loadingModule === mod.title;

                                if (mod.comingSoon) {
                                    return (
                                        <div
                                            key={mod.id}
                                            className="rounded-2xl border-2 border-dashed border-slate-200/90 bg-slate-50/50 px-2 py-4 flex flex-col items-center justify-center text-center gap-2 select-none min-h-[104px]"
                                        >
                                            <div className="w-10 h-10 rounded-xl border border-dashed border-slate-300 flex items-center justify-center text-slate-300">
                                                <Icon className="w-5 h-5" />
                                            </div>
                                            <span className="text-2xs font-bold text-slate-400 leading-tight">
                                                Segera Hadir
                                            </span>
                                        </div>
                                    );
                                }

                                return (
                                    <div
                                        key={mod.id}
                                        role="button"
                                        tabIndex={0}
                                        onClick={() =>
                                            mod.url && handleNavigate(mod.url, mod.title, mod.id)
                                        }
                                        className="group bg-white rounded-2xl border border-slate-200/80 hover:border-indigo-300 shadow-sm hover:shadow-md px-1.5 py-4 flex flex-col items-center justify-center text-center gap-2 cursor-pointer select-none active:scale-95 transition-all min-h-[104px]"
                                    >
                                        <div
                                            className={`w-11 h-11 sm:w-12 sm:h-12 rounded-2xl ${mod.iconBg} flex items-center justify-center shadow-md text-white group-hover:scale-105 transition-transform`}
                                        >
                                            {isLoadingThis ? (
                                                <ArrowPathIcon className="w-5 h-5 animate-spin text-white" />
                                            ) : (
                                                <Icon className="w-5 h-5 sm:w-6 sm:h-6" />
                                            )}
                                        </div>
                                        <h4 className="text-2xs sm:text-xs font-extrabold text-slate-800 group-hover:text-indigo-600 transition-colors leading-tight">
                                            {mod.title}
                                        </h4>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </main>
            </div>

            {/* 3. Footer Status */}
            <footer className="pt-8 text-center px-4">
                <p className="text-3xs sm:text-2xs font-bold text-slate-400 uppercase tracking-widest flex items-center justify-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse" />
                    <span>SISO Mobile Portal • System Online</span>
                </p>
            </footer>
        </div>
    );
}
