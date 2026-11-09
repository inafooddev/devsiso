import React from 'react';
import { JksItem } from '../constants';
import {
    PencilSquareIcon,
    TrashIcon,
    InformationCircleIcon,
} from '@heroicons/react/24/outline';

interface CompactStoreRowProps {
    item: JksItem;
    index: number;
    onEdit: (item: JksItem) => void;
    onDelete: (item: JksItem) => void;
    onDetail: (item: JksItem) => void;
}

export default function CompactStoreRow({
    item,
    index,
    onEdit,
    onDelete,
    onDetail,
}: CompactStoreRowProps) {
    const hasCoordinates = Boolean(
        item.latitude &&
        item.longitude &&
        !isNaN(Number(item.latitude)) &&
        !isNaN(Number(item.longitude)) &&
        Number(item.latitude) !== 0 &&
        Number(item.longitude) !== 0
    );

    return (
        <div className="px-3 py-2 flex items-center justify-between gap-2 hover:bg-slate-50/80 transition-colors">
            {/* Kolom 1: Nomor Urut (Rata Kanan Rapi) */}
            <span className="text-xs font-bold text-slate-400 font-mono shrink-0 w-6 text-right">
                {index + 1}.
            </span>

            {/* Kolom 2: Nama Toko di atas, Alamat di bawah */}
            <div className="flex-1 min-w-0 pr-1">
                {/* Baris 1: Nama Toko & Indikator GPS */}
                <div className="flex items-center gap-1.5 min-w-0">
                    <h4 className="text-xs md:text-sm font-black text-slate-900 truncate leading-snug" title={item.customer_name}>
                        {item.customer_name}
                    </h4>
                    {!hasCoordinates && (
                        <span className="shrink-0 w-1.5 h-1.5 rounded-full bg-rose-500" title="Belum Ada GPS" />
                    )}
                </div>

                {/* Baris 2: Alamat Toko Rata dengan Nama Toko */}
                <p className="text-xs text-slate-400 truncate mt-0.5">
                    {item.customer_address || '-'}
                    {(item.kecamatan || item.desa) && (
                        <span> ({[item.desa, item.kecamatan].filter(Boolean).join(', ')})</span>
                    )}
                </p>
            </div>

            {/* Kolom 3: Aksi Edit, Hapus, Detail Rapat di Kanan */}
            <div className="flex items-center gap-1 shrink-0">
                {/* Tombol Edit */}
                <button
                    type="button"
                    onClick={() => onEdit(item)}
                    className="w-7 h-7 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 active:scale-95 transition-all flex items-center justify-center border border-indigo-100"
                    title="Edit Jadwal"
                >
                    <PencilSquareIcon className="w-3.5 h-3.5 stroke-[2.2]" />
                </button>

                {/* Tombol Hapus */}
                <button
                    type="button"
                    onClick={() => onDelete(item)}
                    className="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 active:scale-95 transition-all flex items-center justify-center border border-rose-100"
                    title="Hapus Jadwal"
                >
                    <TrashIcon className="w-3.5 h-3.5 stroke-[2.2]" />
                </button>

                {/* Tombol Detail */}
                <button
                    type="button"
                    onClick={() => onDetail(item)}
                    className="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 active:scale-95 transition-all flex items-center justify-center border border-slate-200"
                    title="Detail Toko & Riwayat Kunjungan"
                >
                    <InformationCircleIcon className="w-3.5 h-3.5 stroke-[2.2]" />
                </button>
            </div>
        </div>
    );
}
