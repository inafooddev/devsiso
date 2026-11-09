import { useState, useMemo } from 'react';
import { JksItem, VisitSummaryItem, TabType, DayKeyType, WeekFilterType, PAGINATION_LIMIT } from '../constants';

interface UseJksSalesmanFilterProps {
    jksList: JksItem[];
    visitSummary: Record<string, VisitSummaryItem>;
}

export function useJksSalesmanFilter({ jksList, visitSummary }: UseJksSalesmanFilterProps) {
    const [activeTab, setActiveTab] = useState<TabType>('plan');
    const [search, setSearch] = useState('');
    const [selectedDate, setSelectedDate] = useState<Date>(new Date());
    const [selectedDay, setSelectedDay] = useState<DayKeyType>('h1');
    const [weekFilter, setWeekFilter] = useState<WeekFilterType>('w1');
    const [routeFilter, setRouteFilter] = useState<'all' | 'rute' | 'non_rute'>('all');
    const [displayLimit, setDisplayLimit] = useState(PAGINATION_LIMIT);

    // Hitung week key (w1..w4) dan day key (h1..h7) dari selectedDate
    const { currentDayKey, currentWeekKey } = useMemo(() => {
        const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 1 = Monday ... 6 = Saturday
        let hKey = 'h1';
        if (dayOfWeek === 1) hKey = 'h1';
        else if (dayOfWeek === 2) hKey = 'h2';
        else if (dayOfWeek === 3) hKey = 'h3';
        else if (dayOfWeek === 4) hKey = 'h4';
        else if (dayOfWeek === 5) hKey = 'h5';
        else if (dayOfWeek === 6) hKey = 'h6';
        else if (dayOfWeek === 0) hKey = 'h7';

        const dayOfMonth = selectedDate.getDate();
        let wKey = 'w1';
        if (dayOfMonth <= 7) wKey = 'w1';
        else if (dayOfMonth <= 14) wKey = 'w2';
        else if (dayOfMonth <= 21) wKey = 'w3';
        else wKey = 'w4';

        return { currentDayKey: hKey, currentWeekKey: wKey };
    }, [selectedDate]);

    // Handle Tab Switch
    const handleTabSwitch = (tab: TabType) => {
        setActiveTab(tab);
        setDisplayLimit(PAGINATION_LIMIT);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // Filter Data Berdasarkan Tab & Kriteria
    const filteredData = useMemo(() => {
        let result = [...jksList];

        // 1. Filter Tab "plan" (Jadwal Tanggal Spesifik)
        if (activeTab === 'plan') {
            result = result.filter((item) => {
                const dayMatch = (item as any)[currentDayKey] === 'Y';
                const weekMatch = (item as any)[currentWeekKey] === 'Y';
                return dayMatch && weekMatch;
            });
        }

        // 2. Filter Tab "customer" (Daftar Semua Toko dengan Filter Hari & Minggu)
        else if (activeTab === 'customer') {
            if (selectedDay === 'non_rute') {
                result = result.filter((item) => {
                    const hasDay = item.h1 === 'Y' || item.h2 === 'Y' || item.h3 === 'Y' || item.h4 === 'Y' || item.h5 === 'Y' || item.h6 === 'Y';
                    const hasWeek = item.w1 === 'Y' || item.w2 === 'Y' || item.w3 === 'Y' || item.w4 === 'Y';
                    return !hasDay || !hasWeek || item.h7 === 'Y';
                });
            } else {
                result = result.filter((item) => (item as any)[selectedDay] === 'Y');
            }

            result = result.filter((item) => item[weekFilter] === 'Y');

            if (routeFilter === 'rute') {
                result = result.filter((item) => {
                    const hasDay = item.h1 === 'Y' || item.h2 === 'Y' || item.h3 === 'Y' || item.h4 === 'Y' || item.h5 === 'Y' || item.h6 === 'Y';
                    const hasWeek = item.w1 === 'Y' || item.w2 === 'Y' || item.w3 === 'Y' || item.w4 === 'Y';
                    return hasDay && hasWeek && item.h7 !== 'Y';
                });
            } else if (routeFilter === 'non_rute') {
                result = result.filter((item) => {
                    const hasDay = item.h1 === 'Y' || item.h2 === 'Y' || item.h3 === 'Y' || item.h4 === 'Y' || item.h5 === 'Y' || item.h6 === 'Y';
                    const hasWeek = item.w1 === 'Y' || item.w2 === 'Y' || item.w3 === 'Y' || item.w4 === 'Y';
                    return !hasDay || !hasWeek || item.h7 === 'Y';
                });
            }
        }

        // 3. Filter Tab "realisasi" (Hanya yang sudah divisit / diurutkan dari visit terbanyak)
        else if (activeTab === 'realisasi') {
            result.sort((a, b) => {
                const visitA = visitSummary[a.uniq_kode]?.total_visit || 0;
                const visitB = visitSummary[b.uniq_kode]?.total_visit || 0;
                return visitB - visitA;
            });
        }

        // 4. Pencarian Teks Bebas
        if (search.trim()) {
            const q = search.toLowerCase().trim();
            result = result.filter((item) => {
                const name = (item.customer_name || '').toLowerCase();
                const code = (item.customer_code || '').toLowerCase();
                const uniq = (item.uniq_kode || '').toLowerCase();
                const sales = (item.salesman_name || '').toLowerCase();
                const addr = (item.customer_address || '').toLowerCase();
                const kec = (item.kecamatan || '').toLowerCase();
                return name.includes(q) || code.includes(q) || uniq.includes(q) || sales.includes(q) || addr.includes(q) || kec.includes(q);
            });
        }

        return result;
    }, [jksList, activeTab, currentDayKey, currentWeekKey, selectedDay, weekFilter, routeFilter, search, visitSummary]);

    const displayedData = useMemo(() => {
        return filteredData.slice(0, displayLimit);
    }, [filteredData, displayLimit]);

    const handleLoadMore = () => {
        setDisplayLimit((prev) => prev + PAGINATION_LIMIT);
    };

    return {
        activeTab,
        handleTabSwitch,
        search,
        setSearch,
        selectedDate,
        setSelectedDate,
        selectedDay,
        setSelectedDay,
        weekFilter,
        setWeekFilter,
        routeFilter,
        setRouteFilter,
        currentDayKey,
        currentWeekKey,
        filteredData,
        displayedData,
        displayLimit,
        handleLoadMore,
    };
}
