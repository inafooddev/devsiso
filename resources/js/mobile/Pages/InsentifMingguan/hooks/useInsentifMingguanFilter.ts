import { useState, useMemo } from 'react';
import { SalesmanSeData } from '../types';
import { TeamFilterChip, SFA_MIN_COMPLIANCE } from '../constants';

interface UseInsentifMingguanFilterProps {
    salesmenList: SalesmanSeData[];
    initialChip?: TeamFilterChip;
}

export function useInsentifMingguanFilter({
    salesmenList = [],
    initialChip = 'all',
}: UseInsentifMingguanFilterProps) {
    const [search, setSearch] = useState('');
    const [selectedCabang, setSelectedCabang] = useState('ALL');
    const [selectedChip, setSelectedChip] = useState<TeamFilterChip>(initialChip);
    const [sortBy, setSortBy] = useState<'thp_desc' | 'sfa_asc' | 'ach_desc' | 'name_asc'>('thp_desc');

    // Unique cabang options
    const cabangOptions = useMemo(() => {
        const set = new Set<string>();
        salesmenList.forEach((item) => {
            if (item.cabang) set.add(item.cabang);
        });
        return Array.from(set).sort((a, b) => a.localeCompare(b));
    }, [salesmenList]);

    // Base list filtered by Cabang + Search (used for chip counts)
    const baseFiltered = useMemo(() => {
        let result = salesmenList;

        if (selectedCabang !== 'ALL') {
            result = result.filter((item) => item.cabang === selectedCabang);
        }

        if (search.trim()) {
            const query = search.toLowerCase().trim();
            result = result.filter(
                (item) =>
                    item.salesman_name.toLowerCase().includes(query) ||
                    item.salesman_code.toLowerCase().includes(query) ||
                    item.cabang.toLowerCase().includes(query) ||
                    item.distributor.toLowerCase().includes(query)
            );
        }

        return result;
    }, [salesmenList, selectedCabang, search]);

    // Counts for chip badges based on active cabang & search
    const chipCounts = useMemo(() => {
        let hasIncentiveCount = 0;
        let warningSfaCount = 0;
        let achievedCount = 0;
        let underTargetCount = 0;

        for (const s of baseFiltered) {
            if (s.totals.thp > 0) hasIncentiveCount++;
            if (s.sfa.sfa_pct < SFA_MIN_COMPLIANCE) warningSfaCount++;
            if (s.value.ach_pct >= 100) achievedCount++;
            if (s.value.ach_pct < 60) underTargetCount++;
        }

        return {
            all: baseFiltered.length,
            has_incentive: hasIncentiveCount,
            warning_sfa: warningSfaCount,
            achieved: achievedCount,
            under_target: underTargetCount,
        };
    }, [baseFiltered]);

    const filteredSalesmen = useMemo(() => {
        let result = [...baseFiltered];

        // Chip Filtering
        if (selectedChip === 'has_incentive') {
            result = result.filter((item) => item.totals.thp > 0);
        } else if (selectedChip === 'warning_sfa') {
            result = result.filter((item) => item.sfa.sfa_pct < SFA_MIN_COMPLIANCE);
        } else if (selectedChip === 'achieved') {
            result = result.filter((item) => item.value.ach_pct >= 100);
        } else if (selectedChip === 'under_target') {
            result = result.filter((item) => item.value.ach_pct < 60);
        }

        // Sorting
        result.sort((a, b) => {
            if (sortBy === 'thp_desc') {
                return b.totals.thp - a.totals.thp;
            } else if (sortBy === 'sfa_asc') {
                return a.sfa.sfa_pct - b.sfa.sfa_pct;
            } else if (sortBy === 'ach_desc') {
                return b.value.ach_pct - a.value.ach_pct;
            } else {
                return a.salesman_name.localeCompare(b.salesman_name);
            }
        });

        return result;
    }, [baseFiltered, selectedChip, sortBy]);

    const resetFilters = () => {
        setSearch('');
        setSelectedCabang('ALL');
        setSelectedChip('all');
        setSortBy('thp_desc');
    };

    const hasActiveFilter =
        search.trim() !== '' || selectedCabang !== 'ALL' || selectedChip !== 'all';

    return {
        search,
        setSearch,
        selectedCabang,
        setSelectedCabang,
        cabangOptions,
        selectedChip,
        setSelectedChip,
        sortBy,
        setSortBy,
        filteredSalesmen,
        chipCounts,
        resetFilters,
        hasActiveFilter,
    };
}
