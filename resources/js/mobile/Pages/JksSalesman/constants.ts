export type DayKeyType = 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6' | 'non_rute';
export type WeekFilterType = 'w1' | 'w2' | 'w3' | 'w4';

export const PAGINATION_LIMIT = 50;

export const DAY_NAMES: Record<string, string> = {
    h1: 'Senin',
    h2: 'Selasa',
    h3: 'Rabu',
    h4: 'Kamis',
    h5: 'Jumat',
    h6: 'Sabtu',
    h7: 'Minggu'
};

export const DAY_SHORT_NAMES: Record<string, string> = {
    h1: 'Sen',
    h2: 'Sel',
    h3: 'Rab',
    h4: 'Kam',
    h5: 'Jum',
    h6: 'Sab'
};

export const WEEK_OPTIONS: { key: WeekFilterType; label: string }[] = [
    { key: 'w1', label: 'M1' },
    { key: 'w2', label: 'M2' },
    { key: 'w3', label: 'M3' },
    { key: 'w4', label: 'M4' },
];

export interface JksItem {
    id: number;
    bulan: string;
    distributor_code: string;
    distributor_name?: string;
    salesman_code: string;
    salesman_name: string;
    uniq_kode: string;
    customer_code?: string;
    customer_name: string;
    customer_address?: string;
    kecamatan?: string;
    desa?: string;
    pilar?: string;
    target?: number | string;
    latitude?: string | number | null;
    longitude?: string | number | null;
    h1?: string | null;
    h2?: string | null;
    h3?: string | null;
    h4?: string | null;
    h5?: string | null;
    h6?: string | null;
    h7?: string | null;
    w1?: string | null;
    w2?: string | null;
    w3?: string | null;
    w4?: string | null;
}

export interface VisitSummaryItem {
    total_visit: number;
    total_ec: number;
    total_order_val: number;
    latest_visit_date?: string | null;
}

export interface KpiSummary {
    total_toko: number;
    total_rute: number;
    total_non_rute: number;
    total_non_gps: number;
    total_visited: number;
    total_ec: number;
    total_order_val: number;
    strike_rate: number;
}

export interface DistributorOption {
    distributor_code: string;
    distributor_name: string;
}

export interface SalesmanOption {
    salesman_code: string;
    salesman_name: string;
}

export interface ApprovalItem {
    id: number;
    action_type: string;
    action_label: string;
    status: 'PENDING' | 'APPROVED' | 'AUTO_APPROVED' | 'REJECTED';
    status_label: string;
    distributor_code: string;
    distributor_name: string;
    maker_name: string;
    checker_name?: string | null;
    store_name: string;
    store_code: string;
    salesman_code?: string | null;
    salesman_name?: string | null;
    hari?: string | null;
    minggu?: string | null;
    delete_type?: 'SOFT' | 'HARD' | null;
    reason: string;
    raw_reason?: string;
    reject_reason?: string | null;
    created_at: string;
    created_time_ago?: string;
    updated_at?: string | null;
}

export interface NonJksOutletItem {
    id: number;
    distributor_code: string;
    distributor_name: string;
    customer_code: string;
    customer_eska?: string | null;
    customer_name: string;
    alamat: string;
    avg_value_net: number;
    remark?: string | null;
    pending_approval_id?: number | null;
    history: number[];
}

export interface DashboardDayStat {
    label: string;
    short: string;
    gjl: number;
    gnp: number;
    non_gps: number;
}

export interface SalesmanDashboardRow {
    key: string;
    distributor_code: string;
    distributor_name: string;
    salesman_code: string;
    salesman_name: string;
    total_ro: number;
    total_jks: number;
    non_rute: number;
    non_gps: number;
    penambahan: number;
    deleted: number;
    days: DashboardDayStat[];
}

export interface DashboardTotals {
    total_se: number;
    standar_se: number;
    belum_standar_se: number;
    total_ro: number;
    total_jks: number;
    total_non_rute: number;
    total_non_gps: number;
    total_penambahan: number;
    total_deleted: number;
}

export function getCsrfHeaders(): Record<string, string> {
    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    const xsrfMatch = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
    if (xsrfMatch && xsrfMatch[1]) {
        headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfMatch[1]);
    } else {
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (metaToken) {
            headers['X-CSRF-TOKEN'] = metaToken;
        }
    }

    return headers;
}
