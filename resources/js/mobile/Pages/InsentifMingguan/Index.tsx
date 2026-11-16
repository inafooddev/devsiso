import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import MobileLayout from '../../Layouts/MobileLayout';
import { InsentifMingguanPageProps, SalesmanSeData, SpvData, KacabData } from './types';
import { InsentifTabType, TeamFilterChip, getMonthName } from './constants';

import HeaderSummaryCard from './Components/HeaderSummaryCard';
import SfaAlertBanner from './Components/SfaAlertBanner';
import PillarProgressCard from './Components/PillarProgressCard';
import SpvPillarProgressCard from './Components/SpvPillarProgressCard';
import VtkpBreakdownSheet from './Components/VtkpBreakdownSheet';
import TeamMonitoringTab, { TeamModeType } from './Components/TeamMonitoringTab';
import FilterSheet from './Components/FilterSheet';
import InfoMekanismeModal from './Components/InfoMekanismeModal';
import InsentifBottomNav from './Components/InsentifBottomNav';

import {
    FunnelIcon,
    UsersIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    BuildingOffice2Icon,
} from '@heroicons/react/24/solid';

export default function Index({
    user,
    currentBulan,
    listBulan = [],
    filterRegion,
    filterArea,
    listRegions = [],
    listAreas = [],
    myIncentive,
    salesmenList = [],
    spvList = [],
    kacabList = [],
    summaryTotals,
}: InsentifMingguanPageProps) {
    const [activeTab, setActiveTab] = useState<InsentifTabType>('ringkasan');
    const [initialTeamMode, setInitialTeamMode] = useState<TeamModeType>('SE');
    const [initialSeChip, setInitialSeChip] = useState<TeamFilterChip>('all');

    const [isFilterSheetOpen, setIsFilterSheetOpen] = useState(false);
    const [isInfoModalOpen, setIsInfoModalOpen] = useState(false);
    const [isMyVtkpSheetOpen, setIsMyVtkpSheetOpen] = useState(false);

    // If user has personal SE, SPV, or Kacab data
    const mySeData = myIncentive?.role_type === 'SE' ? (myIncentive.data as SalesmanSeData) : null;
    const mySpvData = myIncentive?.role_type === 'SPV' ? (myIncentive.data as SpvData) : null;
    const myKacabData = myIncentive?.role_type === 'KACAB' ? (myIncentive.data as KacabData) : null;

    // Warning SFA count for badge
    const warningCount = summaryTotals.total_penalty_sfa || 0;

    // Helper to jump from Ringkasan KPI cards to Tim Sales tab with specific filter
    const jumpToTeamTab = (mode: TeamModeType, seChip: TeamFilterChip = 'all') => {
        setInitialTeamMode(mode);
        setInitialSeChip(seChip);
        setActiveTab('tim');
    };

    return (
        <MobileLayout
            user={user}
            title="Insentif Sales"
            backUrl="/mobile/home"
            bottomNavigation={
                <InsentifBottomNav
                    activeTab={activeTab}
                    onTabChange={(tab) => {
                        if (tab === 'aturan') {
                            setIsInfoModalOpen(true);
                        } else {
                            setActiveTab(tab);
                        }
                    }}
                    teamCount={salesmenList.length}
                    warningCount={warningCount}
                />
            }
        >
            <Head title="Insentif Sales" />

            <div className="pt-2 pb-6">
                {/* Top Filter Pill Bar */}
                <div className="mx-4 mb-3 flex items-center justify-between">
                    <button
                        type="button"
                        onClick={() => setIsFilterSheetOpen(true)}
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-white border border-gray-200 text-xs font-bold text-gray-700 shadow-sm active:scale-95 transition-all hover:bg-gray-50"
                    >
                        <FunnelIcon className="w-3.5 h-3.5 text-indigo-600" />
                        <span>
                            {getMonthName(currentBulan)}
                            {filterRegion ? ` • ${filterRegion}` : ''}
                            {filterArea ? ` (${filterArea})` : ''}
                        </span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setIsInfoModalOpen(true)}
                        className="p-1.5 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-indigo-600 shadow-sm"
                        title="Aturan & Tier"
                    >
                        <InformationCircleIcon className="w-4 h-4" />
                    </button>
                </div>

                {/* TAB 1: RINGKASAN MONITORING */}
                {activeTab === 'ringkasan' && (
                    <div className="space-y-4">
                        {/* 1. Header Card (Personal or Territory Aggregate) */}
                        {mySeData ? (
                            <HeaderSummaryCard
                                title="Estimasi Insentif Saya (SE)"
                                subtitle={`${mySeData.salesman_name} (${mySeData.cabang})`}
                                periodMonth={currentBulan}
                                thp={mySeData.totals.thp}
                                kotor={mySeData.totals.kotor}
                                pph={mySeData.totals.pph}
                                sfaPct={mySeData.sfa.sfa_pct}
                                sfaPenaltyApplied={mySeData.sfa.penalty_applied}
                            />
                        ) : mySpvData ? (
                            <HeaderSummaryCard
                                title="Estimasi Insentif Saya (SPV)"
                                subtitle={`${mySpvData.supervisor_name} (${mySpvData.supervisor_code})`}
                                periodMonth={currentBulan}
                                thp={mySpvData.transfer_70}
                                kotor={mySpvData.total_all_insentif}
                                pph={mySpvData.total_all_insentif * 0.05}
                                isSpv={true}
                                tabungan30={mySpvData.tabungan_30}
                                transfer70={mySpvData.transfer_70}
                            />
                        ) : myKacabData ? (
                            <HeaderSummaryCard
                                title="Estimasi Insentif Saya (Kacab)"
                                subtitle={`Cabang ${myKacabData.cabang} • ${myKacabData.nama_kacab}`}
                                periodMonth={currentBulan}
                                thp={myKacabData.trf}
                                kotor={myKacabData.nilai_insentif}
                                pph={myKacabData.pph}
                            />
                        ) : (
                            <HeaderSummaryCard
                                title="Total Estimasi Insentif Wilayah"
                                subtitle={`${filterRegion || 'Nasional'} • ${salesmenList.length} Salesman`}
                                periodMonth={currentBulan}
                                thp={summaryTotals.total_thp}
                                kotor={summaryTotals.total_kotor ?? (summaryTotals.total_thp / 0.95)}
                                pph={summaryTotals.total_pph ?? ((summaryTotals.total_thp / 0.95) * 0.05)}
                            />
                        )}

                        {/* 2. SFA Alert Banner (If personal SE) */}
                        {mySeData && (
                            <SfaAlertBanner
                                sfaPct={mySeData.sfa.sfa_pct}
                                penaltyApplied={mySeData.sfa.penalty_applied}
                                pc={mySeData.sfa.pc}
                                ac={mySeData.sfa.ac}
                            />
                        )}

                        {/* 3. Ringkasan Monitoring Wilayah (Ditukar ke posisi ATAS sebelum Progress Pilar) */}
                        <div className="mx-4">
                            <div className="flex items-center justify-between mb-2.5 px-1">
                                <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Ringkasan Monitoring Wilayah
                                </h3>
                                <span className="text-[11px] text-indigo-600 font-semibold">
                                    Ketuk kartu untuk rincian
                                </span>
                            </div>
                            <div className="grid grid-cols-2 gap-2.5">
                                {/* Total Salesmen */}
                                <button
                                    type="button"
                                    onClick={() => jumpToTeamTab('SE', 'all')}
                                    className="bg-white p-3.5 rounded-2xl border border-gray-100 hover:border-indigo-200 shadow-sm flex items-center gap-3 text-left active:scale-[0.98] transition-all"
                                >
                                    <div className="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                        <UsersIcon className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <span className="text-[11px] text-gray-500 block font-medium">Sales Executive</span>
                                        <span className="text-base font-extrabold text-gray-900">
                                            {summaryTotals.total_salesmen} Orang
                                        </span>
                                    </div>
                                </button>

                                {/* Dapat Insentif */}
                                <button
                                    type="button"
                                    onClick={() => jumpToTeamTab('SE', 'has_incentive')}
                                    className="bg-white p-3.5 rounded-2xl border border-gray-100 hover:border-emerald-200 shadow-sm flex items-center gap-3 text-left active:scale-[0.98] transition-all"
                                >
                                    <div className="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                        <CheckCircleIcon className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <span className="text-[11px] text-gray-500 block font-medium">Dapat Insentif</span>
                                        <span className="text-base font-extrabold text-emerald-700">
                                            {summaryTotals.total_achieved} SE
                                        </span>
                                    </div>
                                </button>

                                {/* SFA Warning */}
                                <button
                                    type="button"
                                    onClick={() => jumpToTeamTab('SE', 'warning_sfa')}
                                    className="bg-white p-3.5 rounded-2xl border border-gray-100 hover:border-rose-200 shadow-sm flex items-center gap-3 text-left active:scale-[0.98] transition-all"
                                >
                                    <div className="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                        <ExclamationTriangleIcon className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <span className="text-[11px] text-gray-500 block font-medium">SFA &lt; 95%</span>
                                        <span className="text-base font-extrabold text-rose-700">
                                            {summaryTotals.total_penalty_sfa} SE
                                        </span>
                                    </div>
                                </button>

                                {/* Total SPV / Cabang */}
                                <button
                                    type="button"
                                    onClick={() => jumpToTeamTab('SPV', 'all')}
                                    className="bg-white p-3.5 rounded-2xl border border-gray-100 hover:border-purple-200 shadow-sm flex items-center gap-3 text-left active:scale-[0.98] transition-all"
                                >
                                    <div className="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                        <BuildingOffice2Icon className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <span className="text-[11px] text-gray-500 block font-medium">SPV / Cabang</span>
                                        <span className="text-base font-extrabold text-purple-700">
                                            {summaryTotals.total_spv} / {summaryTotals.total_kacab}
                                        </span>
                                    </div>
                                </button>
                            </div>

                            {/* Quick CTA to see Team list */}
                            <div className="mt-2.5">
                                <button
                                    type="button"
                                    onClick={() => jumpToTeamTab('SE', 'all')}
                                    className="w-full py-3 px-4 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-2xl text-xs font-bold flex items-center justify-center gap-2 border border-indigo-100 shadow-sm transition-all active:scale-[0.99]"
                                >
                                    <UsersIcon className="w-4 h-4" />
                                    <span>Lihat & Pantau Seluruh Tim Sales ({salesmenList.length}) &rarr;</span>
                                </button>
                            </div>
                        </div>

                        {/* 4. Progress Pilar (Ditukar ke posisi BAWAH setelah Ringkasan Monitoring Wilayah) */}
                        {mySeData && (
                            <PillarProgressCard
                                data={mySeData}
                                onOpenVtkpDetail={() => setIsMyVtkpSheetOpen(true)}
                            />
                        )}

                        {mySpvData && (
                            <SpvPillarProgressCard
                                data={mySpvData}
                            />
                        )}
                    </div>
                )}

                {/* TAB 2: MONITORING TIM SALES */}
                {activeTab === 'tim' && (
                    <div>
                        <TeamMonitoringTab
                            salesmenList={salesmenList}
                            spvList={spvList}
                            kacabList={kacabList}
                            initialTeamMode={initialTeamMode}
                            initialSeChip={initialSeChip}
                        />
                    </div>
                )}
            </div>

            {/* Filter Bottom Sheet */}
            <FilterSheet
                isOpen={isFilterSheetOpen}
                onClose={() => setIsFilterSheetOpen(false)}
                currentBulan={currentBulan}
                listBulan={listBulan}
                currentRegion={filterRegion}
                listRegions={listRegions}
                currentArea={filterArea}
                listAreas={listAreas}
                accessLevel={user.access_level}
            />

            {/* Modal Info Mekanisme & Tier */}
            <InfoMekanismeModal
                isOpen={isInfoModalOpen}
                onClose={() => setIsInfoModalOpen(false)}
            />

            {/* Personal VTKP breakdown modal (if applicable) */}
            {mySeData && (
                <VtkpBreakdownSheet
                    isOpen={isMyVtkpSheetOpen}
                    onClose={() => setIsMyVtkpSheetOpen(false)}
                    salesmanName={mySeData.salesman_name}
                    details={mySeData.vtkp.details || []}
                    totalInsentif={mySeData.vtkp.total_insentif}
                />
            )}
        </MobileLayout>
    );
}
