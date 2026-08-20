import { useState, useEffect, useMemo } from 'react';
import { TabType, SkbStatusType, ProgressStatusType, PAGINATION_LIMIT, INCENTIVE_THRESHOLDS } from '../constants';
import { SkbRwoItem, getProratedTarget } from '../Components/StoreCard';
import { haversineDistance, NEARBY_RADIUS_KM } from '../utils/geo';
import { UserLocation } from './useNearby';

interface UseSkbRwoFilterProps {
    listPotensi: SkbRwoItem[];
    listMonitoring: SkbRwoItem[];
    listPlan: SkbRwoItem[];
    nearbyActive?: boolean;
    userLocation?: UserLocation | null;
}

export function useSkbRwoFilter({ listPotensi, listMonitoring, listPlan, nearbyActive = false, userLocation = null }: UseSkbRwoFilterProps) {
    const [activeTab, setActiveTab] = useState<TabType>(() => {
        return (sessionStorage.getItem('skbRwoActiveTab') as TabType) || 'summary';
    });
    
    useEffect(() => {
        sessionStorage.setItem('skbRwoActiveTab', activeTab);
    }, [activeTab]);

    const [search, setSearch] = useState('');
    const [filterSkbStatus, setFilterSkbStatus] = useState<SkbStatusType>('Semua');
    const [filterKuartal, setFilterKuartal] = useState<string>(Math.ceil((new Date().getMonth() + 1) / 3).toString());
    const [filterStatus, setFilterStatus] = useState<ProgressStatusType>('Semua');
    const [filterDistributor, setFilterDistributor] = useState<string>('Semua');
    const [filterReward, setFilterReward] = useState<string>('Semua');
    const [displayLimit, setDisplayLimit] = useState(PAGINATION_LIMIT);
    const [selectedDate, setSelectedDate] = useState(new Date());

    const formatYMD = (date: Date) => {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };

    const handleSearchChange = (val: string) => {
        setSearch(val);
        setDisplayLimit(PAGINATION_LIMIT);
    };

    const handleSearchSubmit = () => {
        setDisplayLimit(PAGINATION_LIMIT);
    };

    const clearSearch = () => {
        setSearch('');
        setDisplayLimit(PAGINATION_LIMIT);
    };

    const handleTabSwitch = (tab: TabType) => {
        setActiveTab(tab);
        setDisplayLimit(PAGINATION_LIMIT);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const displayedData = useMemo(() => {
        let result: SkbRwoItem[] = [];
        if (activeTab === 'potensi') {
            result = listPotensi;
            if (filterSkbStatus !== 'Semua') {
                result = result.filter(item => item.status_skb === filterSkbStatus);
            }
        }
        else if (activeTab === 'monitoring') {
            result = listMonitoring;
            if (filterKuartal !== 'Semua') {
                result = result.filter(item => String(item.kuartal) === filterKuartal);
            }
            if (filterStatus !== 'Semua') {
                result = result.filter(item => {
                    const target = item.total_target || 0;
                    const achievement = item.total_achievement || 0;
                    const proratedTgt = getProratedTarget(target, item.kuartal);
                    const percent = proratedTgt > 0 ? (achievement / proratedTgt) * 100 : 0;
                    if (filterStatus === 'Hijau') return percent >= 100;
                    if (filterStatus === 'Kuning') return percent >= 80 && percent < 100;
                    if (filterStatus === 'Merah') return percent < 80;
                    return true;
                });
            }
            if (filterDistributor !== 'Semua') {
                const qDist = String(filterDistributor || '').toLowerCase();
                result = result.filter(item => 
                    String(item.distributor_name || '').toLowerCase().includes(qDist) ||
                    String(item.distributor_code || '').toLowerCase().includes(qDist)
                );
            }
            if (filterReward !== 'Semua') {
                result = result.filter(item => {
                    const targetAmount = Number(item.total_target) || 0;
                    const pct = targetAmount >= INCENTIVE_THRESHOLDS.TIER_1.minTarget 
                        ? INCENTIVE_THRESHOLDS.TIER_1.rewardPct 
                        : (targetAmount >= INCENTIVE_THRESHOLDS.TIER_2.minTarget ? INCENTIVE_THRESHOLDS.TIER_2.rewardPct : INCENTIVE_THRESHOLDS.DEFAULT.rewardPct);
                    return pct === filterReward;
                });
            }
        }
        else if (activeTab === 'plan') {
            const selectedYMD = formatYMD(selectedDate);
            result = listPlan.filter(item => item.tanggal === selectedYMD);
        }

        if (search) {
            const q = search.toLowerCase();
            result = result.filter(o =>
                String(o.customer_name || '').toLowerCase().includes(q) ||
                String(o.customer_code || '').toLowerCase().includes(q)
            );
        }

        // Filter & sort Nearby (hanya aktif di tab monitoring & potensi)
        if (nearbyActive && userLocation && (activeTab === 'monitoring' || activeTab === 'potensi')) {
            result = result
                .map(item => {
                    const lat = parseFloat(String(item.latitude || ''));
                    const lng = parseFloat(String(item.longitude || ''));
                    const dist = (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0)
                        ? haversineDistance(userLocation.lat, userLocation.lng, lat, lng)
                        : null;
                    return { item, dist };
                })
                .filter(({ dist }) => dist !== null && dist <= NEARBY_RADIUS_KM)
                .sort((a, b) => (a.dist as number) - (b.dist as number))
                .map(({ item }) => item);
        }

        return result;
    }, [listPotensi, listMonitoring, listPlan, activeTab, search, selectedDate, filterSkbStatus, filterKuartal, filterStatus, filterDistributor, filterReward, nearbyActive, userLocation]);

    const filteredSummaryData = useMemo(() => {
        let result = listMonitoring;
        if (filterKuartal !== 'Semua') {
            result = result.filter(item => String(item.kuartal) === filterKuartal);
        }
        return result;
    }, [listMonitoring, filterKuartal]);

    const handleLoadMore = () => {
        setDisplayLimit(prev => prev + PAGINATION_LIMIT);
    };

    return {
        activeTab,
        handleTabSwitch,
        search,
        handleSearchChange,
        setSearch,
        handleSearchSubmit,
        clearSearch,
        filterSkbStatus,
        setFilterSkbStatus,
        filterKuartal,
        setFilterKuartal,
        filterStatus,
        setFilterStatus,
        filterDistributor,
        setFilterDistributor,
        filterReward,
        setFilterReward,
        displayLimit,
        setDisplayLimit,
        handleLoadMore,
        selectedDate,
        setSelectedDate,
        displayedData,
        filteredSummaryData
    };
}
