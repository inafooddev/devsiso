export interface VtkpDetailItem {
    nama_header: string;
    target: number;
    real: number;
    growth: number;
    insentif: number;
}

export interface SalesmanSeData {
    area_name: string;
    cabang: string;
    distributor: string;
    kd_dist: string;
    salesman_code: string;
    salesman_name: string;
    value: {
        target: number;
        actual: number;
        ach_pct: number;
        insentif: number;
    };
    vtkp: {
        total_insentif: number;
        details: VtkpDetailItem[];
    };
    ec: {
        ro: number;
        frekuensi: string;
        ac: number;
        ec: number;
        persen_ec: number;
        ec_harian: number;
        insentif: number;
    };
    ipt: {
        sku: number;
        ec: number;
        ipt_val: number;
        insentif: number;
    };
    sfa: {
        pc: number;
        ac: number;
        sfa_pct: number;
        penalty_applied: boolean;
    };
    totals: {
        kotor: number;
        estimasi: number;
        pph: number;
        thp: number;
    };
}

export interface SpvCabangData {
    cabang: string;
    area_name: string;
    distributors: any[];
    rowspan: number;
    vtkp_achievements: Record<string, {
        target: number;
        real: number;
        growth: number;
        insentif: number;
    }>;
    total_insentif_vtkp: number;
}

export interface SpvData {
    supervisor_code: string;
    supervisor_name: string;
    total_target_reguler: number;
    total_aktual_so: number;
    pencapaian_persen: number;
    ins_so: number;
    total_insentif_vtkp: number;
    total_rwo_peserta: number;
    total_rwo_achieve: number;
    rwo_achieve_pct: number;
    insentif_rwo: number;
    total_ipt_sku: number;
    total_ipt_ec: number;
    ipt: number;
    insentif_ipt: number;
    total_all_insentif: number;
    tabungan_30: number;
    transfer_70: number;
    cabangs: Record<string, SpvCabangData>;
}

export interface KacabData {
    area_name: string;
    distributor_name: string;
    cabang: string;
    nama_kacab: string;
    target: number;
    insentif: number;
    sell_out: number;
    percentage: number;
    nilai_insentif: number;
    pph: number;
    trf: number;
}

export interface MyIncentive {
    role_type: 'SE' | 'SPV' | 'KACAB';
    title: string;
    data: SalesmanSeData | SpvData | KacabData;
}

export interface SummaryTotals {
    total_thp: number;
    total_kotor?: number;
    total_estimasi?: number;
    total_pph?: number;
    total_salesmen: number;
    total_achieved: number;
    total_penalty_sfa: number;
    total_spv: number;
    total_kacab: number;
}

export interface HeaderGroupItem {
    id: number;
    nama_header: string;
    details?: Array<{
        id: number;
        product_group_3: string;
    }>;
}

export interface InsentifMingguanPageProps {
    user: {
        name: string;
        userid: string;
        supervisor_code?: string;
        access_level: string;
        role: string;
    };
    currentBulan: string;
    listBulan: string[];
    filterRegion: string;
    filterArea: string;
    listRegions: string[];
    listAreas: string[];
    myIncentive: MyIncentive | null;
    salesmenList: SalesmanSeData[];
    spvList: SpvData[];
    kacabList: KacabData[];
    headers: HeaderGroupItem[];
    summaryTotals: SummaryTotals;
}
