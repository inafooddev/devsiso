import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export const useMonitoringForm = () => {
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        tanggal: '',
        form_distributor_code: '',
        form_sales_code: '',
        foto_tampak_depan: null as File | null,
        foto_tampak_belakang: null as File | null,
        kondisi_hp: '',
        kondisi_kartu: '',
        id: null as number | null,
    });

    const [editId, setEditId] = useState<number | null>(null);
    const [existingFotoDepan, setExistingFotoDepan] = useState<string | null>(null);
    const [existingFotoBelakang, setExistingFotoBelakang] = useState<string | null>(null);

    const resetForm = () => {
        reset();
        clearErrors();
        setEditId(null);
        setExistingFotoDepan(null);
        setExistingFotoBelakang(null);
    };

    const submitForm = (onSuccess: () => void) => {
        post('/app/monitoring-device', {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
                onSuccess();
            },
        });
    };

    return {
        data,
        setData,
        processing,
        errors,
        resetForm,
        submitForm,
        editId,
        setEditId,
        existingFotoDepan,
        setExistingFotoDepan,
        existingFotoBelakang,
        setExistingFotoBelakang,
    };
};
