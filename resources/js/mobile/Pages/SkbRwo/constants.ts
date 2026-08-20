export type TabType = 'summary' | 'potensi' | 'plan' | 'monitoring' | 'radar';
export type SkbStatusType = 'Semua' | 'Sudah' | 'Belum';
export type ProgressStatusType = 'Semua' | 'Hijau' | 'Kuning' | 'Merah';

export const PAGINATION_LIMIT = 30;

export const INCENTIVE_THRESHOLDS = {
    TIER_1: { minTarget: 90000000, rewardPct: '2.5%' },
    TIER_2: { minTarget: 30000000, rewardPct: '2%' },
    DEFAULT: { rewardPct: '1.5%' }
};
