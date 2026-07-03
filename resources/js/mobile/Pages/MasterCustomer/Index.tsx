import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head } from '@inertiajs/react';
import MobileLayout from '../../Layouts/MobileLayout';
import { 
    CameraIcon, MapIcon, InformationCircleIcon, 
    MagnifyingGlassIcon, AdjustmentsHorizontalIcon, 
    CheckCircleIcon, PencilSquareIcon, XMarkIcon,
    UserCircleIcon, CreditCardIcon, ChevronDownIcon,
    ShieldCheckIcon, ServerIcon, DocumentTextIcon
} from '@heroicons/react/24/solid';

export default function Index() {
    const dummyUser = { name: "Budi Santoso", role: "Sales Supervisor" };

    const customers = [
        { 
            id: 'C001', 
            customer_code: 'OUT-JKT-001',
            name: 'Toko Sumber Rejeki', 
            alamat: 'Jl. Sudirman No. 45, Jakarta Selatan', 
            status: 'Complete', 
            is_valid: true,
            distance: 1.2,
            foto_depan: true,
            foto_dalam: true,
            nama_pemilik: 'Bapak Sugeng',
            nama_ktp: 'Sugeng Riyadi',
            nik_ktp: '3174001234567890',
            no_hp: '081234567890',
            nama_bank: 'BANK BCA',
            no_rekening: '1234567890',
            nama_pemilik_norek: 'Sugeng Riyadi',
            foto_ktp: true
        },
        { 
            id: 'C002', 
            customer_code: 'OUT-BDG-045',
            name: 'Warung Makmur', 
            alamat: 'Jl. Melati Blok C/2, Bandung', 
            status: 'Not Complete', 
            is_valid: false,
            distance: 3.5,
            foto_depan: true,
            foto_dalam: false,
            nama_pemilik: 'Ibu Ratna',
            nama_ktp: '',
            nik_ktp: '',
            no_hp: '081987654321',
            nama_bank: '',
            no_rekening: '',
            nama_pemilik_norek: '',
            foto_ktp: false
        },
        { 
            id: 'C003', 
            customer_code: 'OUT-SBY-901',
            name: 'Minimarket 24', 
            alamat: 'Jl. Pahlawan No. 1, Surabaya', 
            status: 'Not Complete', 
            is_valid: true,
            distance: 0.8,
            foto_depan: false,
            foto_dalam: false,
            nama_pemilik: 'Hendra Setiawan',
            nama_ktp: 'Hendra Setiawan',
            nik_ktp: '3574009876543210',
            no_hp: '085612341234',
            nama_bank: 'BANK MANDIRI',
            no_rekening: '0987654321',
            nama_pemilik_norek: 'Hendra Setiawan',
            foto_ktp: true
        },
    ];

    const [search, setSearch] = useState('');
    
    // States for Bottom Sheets
    const [detailOutlet, setDetailOutlet] = useState<any>(null);
    const [editingOutlet, setEditingOutlet] = useState<any>(null);
    const [activeOutlet, setActiveOutlet] = useState<any>(null); // For Upload

    // Mock states for Edit form dropdown
    const [showBankDropdown, setShowBankDropdown] = useState(false);
    const [editNamaBank, setEditNamaBank] = useState('');

    const openEdit = (outlet: any) => {
        setEditingOutlet(outlet);
        setEditNamaBank(outlet.nama_bank || '');
        setShowBankDropdown(false);
    }

    const bankList = ['BANK BCA', 'BANK MANDIRI', 'BANK BNI', 'BANK BRI', 'BANK SYARIAH INDONESIA (BSI)'];
    const filteredBanks = bankList.filter(b => b.toLowerCase().includes(editNamaBank.toLowerCase()));

    return (
        <MobileLayout user={dummyUser} title="Master Customer">
            <Head title="Master Customer RWO" />
            
            {/* Spacer for Fixed Search Area (Using standard responsive Tailwind classes) */}
            <div className="h-12 md:h-8 w-full shrink-0"></div>

            {/* List Outlets */}
            <div className="flex flex-col gap-3 pb-24 relative z-10">
                {customers.map(outlet => (
                    <div key={outlet.id} className={`bg-white border border-slate-100 rounded-2xl p-4 shadow-sm transition-all duration-300 flex flex-col gap-3.5 ${(activeOutlet?.id === outlet.id || detailOutlet?.id === outlet.id || editingOutlet?.id === outlet.id) ? 'ring-2 ring-blue-600 ring-offset-1' : ''}`}>
                        
                        <div className="flex items-start justify-between gap-3">
                            <div className="flex-1 min-w-0">
                                <div className="flex flex-wrap items-center gap-1.5">
                                    <span className="text-[9px] px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold font-mono tracking-wider w-fit">{outlet.customer_code}</span>
                                    
                                    <span className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${outlet.status === 'Complete' ? 'bg-emerald-50 text-emerald-600 border-emerald-100/80' : 'bg-rose-50 text-rose-600 border-rose-100/80'}`}>
                                        {outlet.status}
                                    </span>
                                    
                                    <span className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${outlet.is_valid ? 'bg-blue-50 text-blue-600 border-blue-100/80' : 'bg-slate-50 text-slate-500 border-slate-200/80'}`}>
                                        {outlet.is_valid ? 'Terverifikasi' : 'Belum Verifikasi'}
                                    </span>
                                            
                                    <span className="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border bg-purple-50 text-purple-600 border-purple-100/80">
                                        {outlet.distance.toFixed(2)} KM
                                    </span>
                                </div>
                                <h4 className="text-xs font-black text-slate-800 mt-2 tracking-tight truncate">{outlet.name}</h4>
                                <p className="text-[10px] text-slate-400 font-semibold leading-normal mt-0.5 line-clamp-2">{outlet.alamat}</p>
                            </div>
                        </div>
                        
                        <div className="flex flex-wrap items-center justify-between gap-2.5 border-t border-slate-100 pt-3">
                            <div className="flex items-center gap-1.5">
                                <div className={`flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-extrabold tracking-wider transition-colors duration-200 ${outlet.foto_depan ? 'bg-emerald-50 text-emerald-600 border border-emerald-100/50' : 'bg-slate-50 text-slate-400 border border-slate-100/50'}`}>
                                    {outlet.foto_depan ? <CheckCircleIcon className="w-3.5 h-3.5 text-emerald-500" /> : <span className="w-1.5 h-1.5 rounded-full bg-slate-300"></span>}
                                    <span>Tampak Depan</span>
                                </div>
                                
                                <div className={`flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-extrabold tracking-wider transition-colors duration-200 ${outlet.foto_dalam ? 'bg-emerald-50 text-emerald-600 border border-emerald-100/50' : 'bg-slate-50 text-slate-400 border border-slate-100/50'}`}>
                                    {outlet.foto_dalam ? <CheckCircleIcon className="w-3.5 h-3.5 text-emerald-500" /> : <span className="w-1.5 h-1.5 rounded-full bg-slate-300"></span>}
                                    <span>Tampak Dalam</span>
                                </div>
                            </div>

                            <div className="flex items-center gap-1.5 mt-2 w-full justify-end sm:mt-0 sm:w-auto">
                                <a href={`https://www.google.com/maps/dir/?api=1&destination=-6.200000,106.816666`} target="_blank" rel="noreferrer" className="inline-flex flex-1 items-center justify-center px-2 py-1 text-[9px] font-black uppercase text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-100 gap-1 h-8 sm:flex-none cursor-pointer">
                                    <MapIcon className="w-3.5 h-3.5 text-blue-500" />
                                    <span>Arah</span>
                                </a>
                                <button onClick={() => setDetailOutlet(outlet)} className="inline-flex flex-1 items-center justify-center px-2 py-1 text-[9px] font-black uppercase text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-100 gap-1 h-8 sm:flex-none">
                                    <InformationCircleIcon className="w-3.5 h-3.5 text-slate-400" />
                                    <span>Detail</span>
                                </button>
                                <button onClick={() => openEdit(outlet)} className="inline-flex flex-1 items-center justify-center px-2 py-1 text-[9px] font-black uppercase text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-100 gap-1 h-8 sm:flex-none">
                                    <PencilSquareIcon className="w-3.5 h-3.5 text-indigo-500" />
                                    <span>Edit</span>
                                </button>
                                <button onClick={() => setActiveOutlet(outlet)} className="inline-flex flex-1 items-center justify-center px-2 py-1 text-[9px] font-black uppercase text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm gap-1 h-8 sm:flex-none">
                                    <CameraIcon className="w-3.5 h-3.5" />
                                    <span>Upload</span>
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Modals & Fixed Elements using React Portal to escape transform stacking context */}
            {typeof document !== 'undefined' && createPortal(
                <>
                    {/* Fixed Search Area - Spans full width like Navbar */}
                    <div className="fixed top-16 md:top-[72px] left-0 right-0 z-40 bg-white border-b border-gray-200 shadow-sm">
                        <div className="w-full max-w-2xl mx-auto px-4 md:px-6 py-3">
                            <div className="flex items-center gap-2">
                                <div className="relative flex-1 flex items-center">
                                    <span className="absolute left-3 text-slate-400">
                                        <MagnifyingGlassIcon className="w-5 h-5" />
                                    </span>
                                    <input 
                                        value={search} onChange={(e) => setSearch(e.target.value)}
                                        type="text" 
                                        placeholder="Cari nama / kode toko..." 
                                        className="block w-full pl-9 pr-4 py-2 text-sm text-gray-900 border border-gray-300 rounded-xl bg-gray-50 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all shadow-none" 
                                    />
                                </div>
                                
                                <button className="w-10 h-10 rounded-xl border flex items-center justify-center transition-all duration-200 relative bg-gray-50 text-slate-600 border-gray-300 shadow-none">
                                    <AdjustmentsHorizontalIcon className="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Bottom Sheet: Edit Data Outlet */}
                    {editingOutlet && (
                        <div className="fixed inset-0 z-[100]">
                            <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" onClick={() => setEditingOutlet(null)}></div>
                            <div className="fixed bottom-0 left-0 right-0 max-w-2xl mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[90%] z-50 animate-slide-up">
                                <div className="w-12 h-1.5 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                                <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                                    <div className="min-w-0 pr-4">
                                        <span className="inline-block px-2 py-0.5 bg-indigo-100 text-indigo-700 font-mono font-bold rounded-lg text-[9px]">{editingOutlet.customer_code}</span>
                                        <h4 className="text-xs font-black text-slate-900 mt-1 truncate">Edit Data: {editingOutlet.name}</h4>
                                    </div>
                                    <button onClick={() => setEditingOutlet(null)} className="text-slate-400 p-1 bg-slate-100 rounded-full hover:bg-slate-200 transition-colors">
                                        <XMarkIcon className="w-5 h-5" />
                                    </button>
                                </div>
                                
                                <div className="flex-1 overflow-y-auto p-5 space-y-5">
                                    {/* Identitas Pemilik */}
                                    <div className="space-y-3">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 pl-1 flex items-center gap-1.5">
                                            <UserCircleIcon className="w-4 h-4" /> Identitas Pemilik
                                        </h5>
                                        <div className="bg-slate-50 p-4 rounded-2xl border border-slate-100/50 space-y-3">
                                            <div>
                                                <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Nama Pemilik Toko</label>
                                                <input type="text" defaultValue={editingOutlet.nama_pemilik} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" />
                                            </div>
                                            <div>
                                                <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Nama KTP</label>
                                                <input type="text" defaultValue={editingOutlet.nama_ktp} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" />
                                            </div>
                                            <div>
                                                <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">NIK KTP</label>
                                                <input type="text" inputMode="numeric" maxLength={16} defaultValue={editingOutlet.nik_ktp} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" />
                                            </div>
                                            <div>
                                                <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">No. HP</label>
                                                <input type="text" inputMode="tel" defaultValue={editingOutlet.no_hp} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {/* Rekening Bank */}
                                    <div className="space-y-3">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 pl-1 flex items-center gap-1.5">
                                            <CreditCardIcon className="w-4 h-4" /> Rekening Bank
                                        </h5>
                                        <div className="bg-slate-50 p-4 rounded-2xl border border-slate-100/50 space-y-3">
                                            <div className="relative">
                                                <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Nama Bank</label>
                                                <input 
                                                    type="text" 
                                                    value={editNamaBank} 
                                                    onChange={(e) => {setEditNamaBank(e.target.value); setShowBankDropdown(true);}} 
                                                    onFocus={() => setShowBankDropdown(true)}
                                                    placeholder="Pilih atau cari bank..." 
                                                    className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 pr-8" 
                                                />
                                                <span className="absolute right-3 top-[26px] text-slate-400 pointer-events-none"><ChevronDownIcon className="w-4 h-4" /></span>
                                                {showBankDropdown && (
                                                    <div className="absolute z-50 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white border border-slate-100 rounded-xl shadow-lg top-full">
                                                        {filteredBanks.map(bank => (
                                                            <button key={bank} type="button" onClick={() => {setEditNamaBank(bank); setShowBankDropdown(false);}} className="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 border-b border-slate-50">{bank}</button>
                                                        ))}
                                                        {filteredBanks.length === 0 && <div className="px-4 py-2 text-xs text-slate-400 italic">Bank tidak ditemukan...</div>}
                                                    </div>
                                                )}
                                            </div>
                                            <div>
                                                <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">No. Rekening</label>
                                                <input type="text" inputMode="numeric" defaultValue={editingOutlet.no_rekening} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" />
                                            </div>
                                            <div>
                                                <label className="block text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Nama Pemilik Rekening</label>
                                                <input type="text" defaultValue={editingOutlet.nama_pemilik_norek} className="w-full h-10 px-3 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {/* Foto KTP */}
                                    <div className="space-y-3">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 pl-1 flex items-center gap-1.5">
                                            <CameraIcon className="w-4 h-4" /> Foto KTP
                                        </h5>
                                        <div className={`relative border border-dashed rounded-2xl overflow-hidden min-h-[120px] flex flex-col items-center justify-center p-3 ${editingOutlet.foto_ktp ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200 bg-slate-50'}`}>
                                            {editingOutlet.foto_ktp ? (
                                                <div className="w-full flex flex-col items-center">
                                                    <div className="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                                        <ShieldCheckIcon className="w-5 h-5" />
                                                    </div>
                                                    <span className="text-[10px] font-bold text-emerald-600 mt-1.5">Foto KTP Sudah Terunggah</span>
                                                </div>
                                            ) : (
                                                <div className="w-full flex flex-col items-center py-2 cursor-pointer hover:bg-slate-100 transition-colors rounded-xl">
                                                    <div className="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-slate-500"><CameraIcon className="w-5 h-5" /></div>
                                                    <span className="text-[11px] font-bold text-slate-700 mt-1.5">Unggah Foto KTP</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                                <div className="p-5 border-t border-slate-100 bg-slate-50 flex items-center gap-3 shrink-0">
                                    <button onClick={() => setEditingOutlet(null)} className="flex-1 h-12 border border-slate-200 bg-white rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition-all">Batal</button>
                                    <button onClick={() => setEditingOutlet(null)} className="flex-1 h-12 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20 active:scale-95 transition-all">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Bottom Sheet: Detail Outlet */}
                    {detailOutlet && (
                        <div className="fixed inset-0 z-[100]">
                            <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" onClick={() => setDetailOutlet(null)}></div>
                            <div className="fixed bottom-0 left-0 right-0 max-w-2xl mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[90%] z-50 animate-slide-up">
                                <div className="w-12 h-1.5 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                                <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                                    <div className="min-w-0 pr-4">
                                        <span className="inline-block px-2 py-0.5 bg-fuchsia-100 text-fuchsia-700 font-mono font-bold rounded-lg text-[9px]">{detailOutlet.customer_code}</span>
                                        <h4 className="text-xs font-black text-slate-900 mt-1 truncate">{detailOutlet.name}</h4>
                                    </div>
                                    <button onClick={() => setDetailOutlet(null)} className="text-slate-400 p-1 bg-slate-100 rounded-full hover:bg-slate-200 transition-colors">
                                        <XMarkIcon className="w-5 h-5" />
                                    </button>
                                </div>
                                <div className="flex-1 overflow-y-auto p-5 space-y-6 text-xs text-slate-600">
                                    {/* Informasi Dasar */}
                                    <div className="space-y-3">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                            <MapIcon className="w-3.5 h-3.5" /> Informasi Dasar
                                        </h5>
                                        <div className="grid grid-cols-2 gap-y-3 gap-x-4">
                                            <div className="col-span-2">
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Alamat</span>
                                                <span className="font-semibold text-slate-700 leading-normal">{detailOutlet.alamat || '-'}</span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Kode Eskalink</span>
                                                <span className="font-bold text-slate-800">{detailOutlet.eskalink_code || '-'}</span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">No. HP</span>
                                                <span className="font-bold text-slate-800">{detailOutlet.no_hp || '-'}</span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Region</span>
                                                <span className="font-bold text-slate-700">{detailOutlet.region_code || '-'}</span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Area</span>
                                                <span className="font-bold text-slate-700">{detailOutlet.area_code || '-'}</span>
                                            </div>
                                            <div className="col-span-2">
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Cabang</span>
                                                <span className="font-bold text-slate-700">{detailOutlet.branch_name || '-'}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {/* Identitas Pemilik */}
                                    <div className="space-y-3">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                            <UserCircleIcon className="w-3.5 h-3.5" /> Identitas Pemilik
                                        </h5>
                                        <div className="grid grid-cols-1 gap-y-3">
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Pemilik Toko</span>
                                                <span className="font-bold text-slate-800">{detailOutlet.nama_pemilik || '-'}</span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Sesuai KTP</span>
                                                <span className="font-bold text-slate-800">{detailOutlet.nama_ktp || '-'}</span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">NIK KTP</span>
                                                <span className="font-bold font-mono text-slate-800">{detailOutlet.nik_ktp || '-'}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Rekening Bank */}
                                    <div className="space-y-3">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                            <CreditCardIcon className="w-3.5 h-3.5" /> Rekening Bank
                                        </h5>
                                        <div className="grid grid-cols-1 gap-y-3">
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Bank</span>
                                                <span className="font-bold text-slate-800">{detailOutlet.nama_bank || '-'}</span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">No. Rekening</span>
                                                <span className="font-bold font-mono text-slate-800">{detailOutlet.no_rekening || '-'}</span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Pemilik Rekening</span>
                                                <span className="font-bold text-slate-800">{detailOutlet.nama_pemilik_norek || '-'}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Data Server */}
                                    <div className="space-y-3">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                            <ServerIcon className="w-3.5 h-3.5" /> Data Server
                                        </h5>
                                        <div className="grid grid-cols-1 gap-y-3">
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Status Kelengkapan</span>
                                                <span className={`inline-block px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider ${detailOutlet.status === 'Complete' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'}`}>
                                                    {detailOutlet.status || 'Not Complete'}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Status Verifikasi Pusat</span>
                                                <span className={`font-bold ${detailOutlet.is_valid ? 'text-emerald-600' : 'text-slate-500'}`}>
                                                    {detailOutlet.is_valid ? 'Terverifikasi' : 'Belum Verifikasi'}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Catatan / Keterangan</span>
                                                <span className="text-slate-700 leading-normal">{detailOutlet.keterangan || '-'}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Lampiran Foto */}
                                    <div className="space-y-3">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                            <DocumentTextIcon className="w-3.5 h-3.5" /> Lampiran Foto
                                        </h5>
                                        <div className="grid grid-cols-2 gap-3">
                                            {/* KTP */}
                                            <div className="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col h-24 relative">
                                                <div className="p-1.5 bg-white border-b border-slate-200 text-center text-[9px] font-black uppercase text-slate-500 tracking-widest shrink-0">Foto KTP</div>
                                                <div className="flex-1 relative flex items-center justify-center p-2">
                                                    {detailOutlet.foto_ktp ? (
                                                        <div className="text-emerald-500"><CheckCircleIcon className="w-6 h-6" /></div>
                                                    ) : <div className="text-[9px] font-bold text-slate-400 bg-slate-200/50 px-2 py-1 rounded-lg">Belum Ada</div>}
                                                </div>
                                            </div>
                                            {/* Toko Lengkap */}
                                            <div className="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col h-24 relative">
                                                <div className="p-1.5 bg-white border-b border-slate-200 text-center text-[9px] font-black uppercase text-slate-500 tracking-widest shrink-0">Foto Toko</div>
                                                <div className="flex-1 relative flex items-center justify-center p-2">
                                                    {detailOutlet.foto_toko ? (
                                                        <div className="text-emerald-500"><CheckCircleIcon className="w-6 h-6" /></div>
                                                    ) : <div className="text-[9px] font-bold text-slate-400 bg-slate-200/50 px-2 py-1 rounded-lg">Belum Ada</div>}
                                                </div>
                                            </div>
                                            {/* Depan */}
                                            <div className="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col h-24 relative">
                                                <div className="p-1.5 bg-white border-b border-slate-200 text-center text-[9px] font-black uppercase text-slate-500 tracking-widest shrink-0">Depan</div>
                                                <div className="flex-1 relative flex items-center justify-center p-2">
                                                    {detailOutlet.foto_depan ? (
                                                        <div className="text-emerald-500"><CheckCircleIcon className="w-6 h-6" /></div>
                                                    ) : <div className="text-[9px] font-bold text-slate-400 bg-slate-200/50 px-2 py-1 rounded-lg">Belum Ada</div>}
                                                </div>
                                            </div>
                                            {/* Dalam */}
                                            <div className="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex flex-col h-24 relative">
                                                <div className="p-1.5 bg-white border-b border-slate-200 text-center text-[9px] font-black uppercase text-slate-500 tracking-widest shrink-0">Dalam</div>
                                                <div className="flex-1 relative flex items-center justify-center p-2">
                                                    {detailOutlet.foto_dalam ? (
                                                        <div className="text-emerald-500"><CheckCircleIcon className="w-6 h-6" /></div>
                                                    ) : <div className="text-[9px] font-bold text-slate-400 bg-slate-200/50 px-2 py-1 rounded-lg">Belum Ada</div>}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Bottom Sheet: Upload Photos */}
                    {activeOutlet && (
                        <div className="fixed inset-0 z-[100]">
                            <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" onClick={() => setActiveOutlet(null)}></div>
                            <div className="fixed bottom-0 left-0 right-0 max-w-2xl mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                                <div className="w-12 h-1.5 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                                <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                                    <div className="min-w-0 pr-4">
                                        <span className="inline-block px-2 py-0.5 bg-blue-100 text-blue-700 font-mono font-bold rounded-lg text-[9px]">UPLOAD FOTO</span>
                                        <h4 className="text-xs font-black text-slate-900 mt-1 truncate">{activeOutlet.name}</h4>
                                    </div>
                                    <button onClick={() => setActiveOutlet(null)} className="text-slate-400 p-1 bg-slate-100 rounded-full hover:bg-slate-200 transition-colors">
                                        <XMarkIcon className="w-5 h-5" />
                                    </button>
                                </div>
                                <div className="flex-1 overflow-y-auto p-5 space-y-4">
                                    
                                    <div className="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50 flex flex-col h-40 items-center justify-center cursor-pointer hover:bg-slate-100 transition-colors">
                                        <div className="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-2">
                                            <CameraIcon className="w-6 h-6" />
                                        </div>
                                        <span className="text-sm font-bold text-slate-700">Ambil Foto Tampak Depan</span>
                                        <span className="text-[10px] text-slate-400 mt-1">Pastikan nama toko terlihat jelas</span>
                                    </div>

                                    <div className="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50 flex flex-col h-40 items-center justify-center cursor-pointer hover:bg-slate-100 transition-colors">
                                        <div className="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mb-2">
                                            <CameraIcon className="w-6 h-6" />
                                        </div>
                                        <span className="text-sm font-bold text-slate-700">Ambil Foto Tampak Dalam</span>
                                        <span className="text-[10px] text-slate-400 mt-1">Tampilkan susunan produk / etalase</span>
                                    </div>

                                </div>
                                <div className="p-5 border-t border-slate-100 bg-white shadow-[0_-10px_20px_rgb(0,0,0,0.03)] shrink-0">
                                    <button onClick={() => setActiveOutlet(null)} className="w-full h-12 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-600/30 active:scale-95 transition-transform flex items-center justify-center gap-2">
                                        <CheckCircleIcon className="w-5 h-5" /> Simpan & Unggah
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </>, document.body
            )}
        </MobileLayout>
    );
}
