export type InsentifTabType = 'ringkasan' | 'tim' | 'aturan';

export type TeamFilterChip = 'all' | 'has_incentive' | 'warning_sfa' | 'achieved' | 'under_target';

export type SpvFilterChip = 'all' | 'has_incentive' | 'qualified_so' | 'under_so';

export const SFA_MIN_COMPLIANCE = 95.0;

export const PAGE_SIZE = 25;

export const formatRupiah = (value: number | undefined | null): string => {
    if (value === undefined || value === null || isNaN(value)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
};

export const formatMinusRupiah = (value: number | undefined | null): string => {
    if (value === undefined || value === null || isNaN(value) || value <= 0) return 'Rp 0';
    return `-${formatRupiah(value)}`;
};

export const formatCompactRupiah = (value: number | undefined | null): string => {
    if (value === undefined || value === null || isNaN(value) || value === 0) return 'Rp 0';
    const abs = Math.abs(value);
    if (abs >= 1_000_000_000) {
        return `Rp ${(value / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 })} M`;
    }
    if (abs >= 1_000_000) {
        return `Rp ${(value / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} Jt`;
    }
    if (abs >= 1_000) {
        return `Rp ${(value / 1_000).toLocaleString('id-ID', { maximumFractionDigits: 0 })} Rb`;
    }
    return formatRupiah(value);
};

export const formatNumber = (value: number | undefined | null): string => {
    if (value === undefined || value === null || isNaN(value)) return '0';
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 1,
    }).format(value);
};

export const getMonthName = (yearMonth: string): string => {
    if (!yearMonth || yearMonth.length < 7) return yearMonth || '-';
    const [year, month] = yearMonth.split('-');
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const monthIndex = parseInt(month, 10) - 1;
    if (monthIndex >= 0 && monthIndex < 12) {
        return `${months[monthIndex]} ${year}`;
    }
    return yearMonth;
};
