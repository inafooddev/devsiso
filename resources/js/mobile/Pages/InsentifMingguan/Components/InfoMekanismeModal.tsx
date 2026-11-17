import React, { useState, useEffect } from 'react';
import {
    XMarkIcon,
    InformationCircleIcon,
    CurrencyDollarIcon,
    SparklesIcon,
    TruckIcon,
    ShoppingBagIcon,
    DevicePhoneMobileIcon,
} from '@heroicons/react/24/outline';

interface InfoMekanismeModalProps {
    isOpen: boolean;
    onClose: () => void;
}

export default function InfoMekanismeModal({ isOpen, onClose }: InfoMekanismeModalProps) {
    const [activeSection, setActiveSection] = useState<'se' | 'spv' | 'sfa'>('se');

    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    if (!isOpen) return null;

    return (
        <div className="fixed top-16 md:top-20 inset-x-0 bottom-0 z-[100] flex flex-col justify-end">
            {/* Clickable Backdrop */}
            <div
                className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                onClick={onClose}
            />

            {/* Modal Box - Flush with Header */}
            <div className="relative z-10 w-full max-w-2xl md:max-w-4xl mx-auto bg-white h-full shadow-2xl overflow-hidden flex flex-col animate-slide-up border-x border-gray-200/80">
                {/* Header */}
                <div className="px-5 py-3.5 border-b border-gray-200/80 flex items-center justify-between bg-slate-50 shrink-0">
                    <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                            <InformationCircleIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <h3 className="text-sm font-bold text-gray-900">Mekanisme & Aturan Insentif</h3>
                            <p className="text-[11px] text-gray-500 font-medium">Panduan matriks tier perhitungan</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-700 shadow-sm transition-colors"
                    >
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>

                {/* Sub Tab Buttons */}
                <div className="flex border-b border-gray-100 bg-gray-50/50 p-2 gap-1 text-xs">
                    <button
                        type="button"
                        onClick={() => setActiveSection('se')}
                        className={`flex-1 py-1.5 font-bold rounded-xl transition-all ${
                            activeSection === 'se'
                                ? 'bg-white text-indigo-700 shadow-sm'
                                : 'text-gray-500 hover:text-gray-900'
                        }`}
                    >
                        Sales Executive (SE)
                    </button>
                    <button
                        type="button"
                        onClick={() => setActiveSection('spv')}
                        className={`flex-1 py-1.5 font-bold rounded-xl transition-all ${
                            activeSection === 'spv'
                                ? 'bg-white text-indigo-700 shadow-sm'
                                : 'text-gray-500 hover:text-gray-900'
                        }`}
                    >
                        Supervisor (SPV)
                    </button>
                    <button
                        type="button"
                        onClick={() => setActiveSection('sfa')}
                        className={`flex-1 py-1.5 font-bold rounded-xl transition-all ${
                            activeSection === 'sfa'
                                ? 'bg-white text-rose-700 shadow-sm'
                                : 'text-gray-500 hover:text-gray-900'
                        }`}
                    >
                        Penalti SFA & Pajak
                    </button>
                </div>

                {/* Body Content */}
                <div className="flex-1 p-4 overflow-y-auto space-y-4 text-xs">
                    {activeSection === 'se' && (
                        <div className="space-y-4">
                            {/* 1. Value Reguler */}
                            <div className="p-3 bg-indigo-50/40 rounded-2xl border border-indigo-100">
                                <h4 className="font-bold text-indigo-900 flex items-center gap-1.5 mb-1.5">
                                    <CurrencyDollarIcon className="w-4 h-4 text-indigo-600" />
                                    1. Insentif Value Reguler
                                </h4>
                                <p className="text-[11px] text-gray-600 mb-2">
                                    Target reguler minimal Rp 250 Juta. Nilai insentif berdasarkan target & persentase pencapaian.
                                </p>
                                <table className="w-full text-[11px] text-left border-collapse">
                                    <thead>
                                        <tr className="border-b border-indigo-200/60 font-bold text-indigo-950">
                                            <th className="py-1">Target</th>
                                            <th className="py-1">&ge; 125%</th>
                                            <th className="py-1">&ge; 100%</th>
                                            <th className="py-1">&ge; 90%</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-indigo-100/50">
                                        <tr>
                                            <td className="py-1 font-semibold">&ge; Rp 450 Jt</td>
                                            <td className="py-1 text-emerald-700 font-bold">1,2 Jt</td>
                                            <td className="py-1 text-emerald-700">1,0 Jt</td>
                                            <td className="py-1 text-amber-700">300 Rb</td>
                                        </tr>
                                        <tr>
                                            <td className="py-1 font-semibold">&ge; Rp 350 Jt</td>
                                            <td className="py-1 text-emerald-700 font-bold">1,0 Jt</td>
                                            <td className="py-1 text-emerald-700">800 Rb</td>
                                            <td className="py-1 text-amber-700">150 Rb</td>
                                        </tr>
                                        <tr>
                                            <td className="py-1 font-semibold">&ge; Rp 250 Jt</td>
                                            <td className="py-1 text-emerald-700 font-bold">800 Rb</td>
                                            <td className="py-1 text-emerald-700">500 Rb</td>
                                            <td className="py-1 text-gray-400">Rp 0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {/* 2. VTKP */}
                            <div className="p-3 bg-purple-50/40 rounded-2xl border border-purple-100">
                                <h4 className="font-bold text-purple-900 flex items-center gap-1.5 mb-1.5">
                                    <SparklesIcon className="w-4 h-4 text-purple-600" />
                                    2. VTKP Produk Fokus
                                </h4>
                                <p className="text-[11px] text-gray-600 mb-2">
                                    <strong>Syarat Mutlak:</strong> Pencapaian Value Reguler harus &ge; 60%. Dihitung dari pertumbuhan (%) dan realisasi kuantiti (karton).
                                </p>
                                <div className="text-[11px] space-y-1 text-gray-700">
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>Growth &ge; 30%:</span>
                                        <span className="font-bold">Rp 50 Rb s/d Rp 500 Rb</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>Growth &ge; 20%:</span>
                                        <span className="font-bold">Rp 35 Rb s/d Rp 350 Rb</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>Growth &ge; 10%:</span>
                                        <span className="font-bold">Rp 25 Rb s/d Rp 200 Rb</span>
                                    </div>
                                </div>
                            </div>

                            {/* 3. EC */}
                            <div className="p-3 bg-amber-50/40 rounded-2xl border border-amber-100">
                                <h4 className="font-bold text-amber-900 flex items-center gap-1.5 mb-1.5">
                                    <TruckIcon className="w-4 h-4 text-amber-600" />
                                    3. Kunjungan Efektif (EC)
                                </h4>
                                <p className="text-[11px] text-gray-600 mb-2">
                                    <strong>Syarat Mutlak:</strong> Value Ach &ge; 60% dan RO &ge; 250 toko.
                                </p>
                                <div className="text-[11px] space-y-1 text-gray-700">
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>% EC &ge; 80% & Harian &ge; 16 toko:</span>
                                        <span className="font-bold text-emerald-700">Rp 800.000</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>% EC &ge; 70% & Harian &ge; 14 toko:</span>
                                        <span className="font-bold text-emerald-700">Rp 500.000</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>% EC &ge; 60% & Harian &ge; 12 toko:</span>
                                        <span className="font-bold text-emerald-700">Rp 300.000</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>% EC &ge; 50% & Harian &ge; 10 toko:</span>
                                        <span className="font-bold text-emerald-700">Rp 50.000</span>
                                    </div>
                                </div>
                            </div>

                            {/* 4. IPT */}
                            <div className="p-3 bg-sky-50/40 rounded-2xl border border-sky-100">
                                <h4 className="font-bold text-sky-900 flex items-center gap-1.5 mb-1.5">
                                    <ShoppingBagIcon className="w-4 h-4 text-sky-600" />
                                    4. Item Per Transaksi (IPT)
                                </h4>
                                <p className="text-[11px] text-gray-600 mb-2">
                                    Dihitung dari SKU / Transaksi EC. Syarat mutlak RO &ge; 250 toko.
                                </p>
                                <div className="text-[11px] space-y-1 text-gray-700">
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>IPT &ge; 12 SKU/EC:</span>
                                        <span className="font-bold text-emerald-700">Rp 600.000</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>IPT &ge; 8 SKU/EC:</span>
                                        <span className="font-bold text-emerald-700">Rp 500.000</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>IPT &ge; 7 SKU/EC:</span>
                                        <span className="font-bold text-emerald-700">Rp 250.000</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>IPT &ge; 5 SKU/EC:</span>
                                        <span className="font-bold text-emerald-700">Rp 150.000</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {activeSection === 'spv' && (
                        <div className="space-y-4">
                            <div className="p-3 bg-indigo-50/40 rounded-2xl border border-indigo-100">
                                <h4 className="font-bold text-indigo-900 mb-1.5">1. Insentif Sell Out Gabungan (SO)</h4>
                                <p className="text-[11px] text-gray-600 mb-2">
                                    Gabungan seluruh cabang binaan SPV. Insentif cair jika pencapaian &ge; 90%.
                                </p>
                                <div className="text-[11px] space-y-1">
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>Target &ge; Rp 2 Milyar:</span>
                                        <span className="font-bold">Rp 500 Rb s/d Rp 2,5 Jt</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>Target &ge; Rp 1 Milyar:</span>
                                        <span className="font-bold">Rp 400 Rb s/d Rp 2,25 Jt</span>
                                    </div>
                                    <div className="flex justify-between p-1 bg-white rounded-lg">
                                        <span>Target &lt; Rp 1 Milyar:</span>
                                        <span className="font-bold">Rp 300 Rb s/d Rp 2,0 Jt</span>
                                    </div>
                                </div>
                            </div>

                            <div className="p-3 bg-purple-50/40 rounded-2xl border border-purple-100">
                                <h4 className="font-bold text-purple-900 mb-1.5">2. Insentif VTKP Pcs Cabang</h4>
                                <p className="text-[11px] text-gray-600 mb-1">
                                    <strong>Syarat Mutlak:</strong> Pencapaian SO SPV harus &ge; 80%.
                                </p>
                                <p className="text-[11px] text-gray-600 mb-2">
                                    Dihitung per selisih pcs (Real - Target):
                                    <br />• Growth &ge; 30%: <strong>Rp 600 / pcs</strong>
                                    <br />• Growth &ge; 20%: <strong>Rp 400 / pcs</strong>
                                    <br />• Growth &ge; 10%: <strong>Rp 250 / pcs</strong>
                                </p>
                            </div>

                            <div className="p-3 bg-emerald-50/40 rounded-2xl border border-emerald-100">
                                <h4 className="font-bold text-emerald-900 mb-1.5">3. Split Pembayaran (70% / 30%)</h4>
                                <p className="text-[11px] text-gray-700 leading-relaxed">
                                    Dari total seluruh insentif SPV (SO + VTKP + RWO + IPT):
                                    <br />• <strong>70% Transfer:</strong> Langsung ditransfer sebagai THP periode berjalan.
                                    <br />• <strong>30% Tabungan:</strong> Ditahan perusahaan dan dicairkan pada periode tertentu.
                                </p>
                            </div>
                        </div>
                    )}

                    {activeSection === 'sfa' && (
                        <div className="space-y-4">
                            <div className="p-4 bg-rose-50 rounded-2xl border border-rose-200">
                                <h4 className="font-bold text-rose-900 flex items-center gap-2 mb-2">
                                    <DevicePhoneMobileIcon className="w-5 h-5 text-rose-600" />
                                    Aturan Penalti Kepatuhan SFA (&lt; 95%)
                                </h4>
                                <p className="text-xs text-rose-800 leading-relaxed">
                                    Kepatuhan SFA dihitung dari persentase kunjungan aktual terhadap rencana (AC / PC).
                                    <br />
                                    <br />
                                    <strong>Jika persentase SFA &lt; 95%:</strong>
                                    <br />
                                    Seluruh insentif (Value + VTKP + EC + IPT) dikenakan <strong>penalti potongan 75%</strong>, sehingga <strong>hanya dibayarkan 25% saja</strong>!
                                </p>
                                <div className="mt-3 p-2.5 bg-white/80 rounded-xl text-[11px] text-gray-600">
                                    *Pengecualian: Jika PC dan AC bernilai 0 (karena kendala device), sistem menganggap 100%.
                                </div>
                            </div>

                            <div className="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                                <h4 className="font-bold text-gray-900 mb-1.5">Pemotongan Pajak PPh (5%)</h4>
                                <p className="text-xs text-gray-700 leading-relaxed">
                                    Seluruh insentif yang dibayarkan dikenakan pajak penghasilan sebesar <strong>5%</strong>.
                                    <br />
                                    <br />
                                    <strong>Take Home Pay (THP) = Total Estimasi Insentif - PPh 5%</strong>
                                </p>
                            </div>
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="p-4 border-t border-gray-100 bg-gray-50 flex justify-end">
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-full py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-all"
                    >
                        Saya Mengerti
                    </button>
                </div>
            </div>
        </div>
    );
}
