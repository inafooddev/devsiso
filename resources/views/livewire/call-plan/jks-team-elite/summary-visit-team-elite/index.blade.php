<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <!-- TABS -->
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <button class="tab tab-xs px-4 transition-colors {{ $currentTab === 'dashboard' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}" wire:click="setTab('dashboard')">Dashboard</button>
            <button class="tab tab-xs px-4 transition-colors {{ $currentTab === 'summary' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}" wire:click="setTab('summary')">Summary</button>
            <button class="tab tab-xs px-4 transition-colors {{ $currentTab === 'detail' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}" wire:click="setTab('detail')">Detail</button>
        </div>
    </div>
    @if($isFiltered && $currentTab === 'detail')
    <!-- KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-3 md:gap-4 lg:gap-6 shrink-0 -mb-1 lg:-mb-3">
        
        <!-- Total Visit -->
        <div class="bg-base-100 rounded-xl px-2.5 py-2 shadow-sm border border-base-300 flex flex-col justify-center">
            <div class="text-[9px] text-base-content/50 font-semibold uppercase tracking-wider mb-0.5" title="Jumlah toko yang telah dikunjungi">Total Visit</div>
            <div class="flex items-center justify-between">
                <div class="text-lg xl:text-xl font-extrabold text-base-content tracking-tight leading-none">{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pct = ($kpiData['total_toko'] ?? 0) > 0 ? (($kpiData['total_visit'] ?? 0) / ($kpiData['total_toko'] ?? 1) * 100) : 0;
                            $pctClass = $pct >= 80 ? 'text-success bg-success/10' : ($pct >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctClass }}">{{ number_format($pct, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-1 flex flex-col gap-0">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Total Toko</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['total_toko'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_toko'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 mt-0.5 pt-0.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Gap</span>
                    @php
                        $gap = ($kpiData['total_toko'] ?? 0) - ($kpiData['total_visit'] ?? 0);
                        $gapClass = $gap > 0 ? 'text-error' : 'text-success';
                    @endphp
                    <span class="text-[11px] font-bold {{ $gapClass }} text-right" title="{{ number_format($gap, 0, ',', '.') }}">{{ number_format($gap, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Total Toko Order -->
        <div class="bg-base-100 rounded-xl px-2.5 py-2 shadow-sm border border-base-300 flex flex-col justify-center">
            <div class="text-[9px] text-base-content/50 font-semibold uppercase tracking-wider mb-0.5" title="Jumlah toko yang melakukan pemesanan (Order > 0)">Toko Order</div>
            <div class="flex items-center justify-between">
                <div class="text-lg xl:text-xl font-extrabold text-base-content tracking-tight leading-none">{{ number_format($kpiData['total_toko_order'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctOrder = ($kpiData['total_visit'] ?? 0) > 0 ? (($kpiData['total_toko_order'] ?? 0) / ($kpiData['total_visit'] ?? 1) * 100) : 0;
                            $pctOrderClass = $pctOrder >= 80 ? 'text-success bg-success/10' : ($pctOrder >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctOrderClass }}">{{ number_format($pctOrder, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-1 flex flex-col gap-0">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Total Visit</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 mt-0.5 pt-0.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Gap</span>
                    @php
                        $gapOrder = ($kpiData['total_visit'] ?? 0) - ($kpiData['total_toko_order'] ?? 0);
                        $gapOrderClass = $gapOrder > 0 ? 'text-error' : 'text-success';
                    @endphp
                    <span class="text-[11px] font-bold {{ $gapOrderClass }} text-right" title="{{ number_format($gapOrder, 0, ',', '.') }}">{{ number_format($gapOrder, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Total Order -->
        <div class="bg-base-100 rounded-xl px-2.5 py-2 shadow-sm border border-base-300 flex flex-col justify-center">
            <div class="text-[9px] text-base-content/50 font-semibold uppercase tracking-wider mb-0.5" title="Total nilai pemesanan keseluruhan">Total Order</div>
            <div class="flex items-center justify-between">
                <div class="text-lg xl:text-xl font-extrabold text-base-content tracking-tight leading-none" title="{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctTarget = ($kpiData['total_target'] ?? 0) > 0 ? (($kpiData['total_order'] ?? 0) / ($kpiData['total_target'] ?? 1) * 100) : 0;
                            $pctTargetClass = $pctTarget >= 80 ? 'text-success bg-success/10' : ($pctTarget >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctTargetClass }}">{{ number_format($pctTarget, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-1 flex flex-col gap-0">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Target</span>
                    <span class="text-[9px] font-bold text-base-content/70" title="{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 mt-0.5 pt-0.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Gap</span>
                    @php
                        $gapTarget = ($kpiData['total_target'] ?? 0) - ($kpiData['total_order'] ?? 0);
                        $gapTargetClass = $gapTarget > 0 ? 'text-error' : 'text-success';
                    @endphp
                    <span class="text-[11px] font-bold {{ $gapTargetClass }} text-right" title="{{ number_format($gapTarget, 0, ',', '.') }}">{{ number_format($gapTarget, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Visit 1. RWO -->
        <div class="bg-base-100 rounded-xl px-2.5 py-2 shadow-sm border border-base-300 flex flex-col justify-center">
            <div class="text-[9px] text-base-content/50 font-semibold uppercase tracking-wider mb-0.5" title="Kinerja toko pada pilar 1. RWO">Total 1. RWO</div>
            <div class="flex items-center justify-between">
                <div class="text-lg xl:text-xl font-extrabold text-base-content tracking-tight leading-none">{{ number_format($kpiData['total_rwo'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctRwo = ($kpiData['total_rwo'] ?? 0) > 0 ? (($kpiData['toko_order_rwo'] ?? 0) / ($kpiData['total_rwo'] ?? 1) * 100) : 0;
                            $pctRwoClass = $pctRwo >= 80 ? 'text-success bg-success/10' : ($pctRwo >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctRwoClass }}">{{ number_format($pctRwo, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-1 flex flex-col gap-0">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Toko Order</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['toko_order_rwo'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['toko_order_rwo'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 mt-0.5 pt-0.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Val Order</span>
                    <span class="text-[11px] font-bold text-success text-right" title="{{ number_format($kpiData['total_order_rwo'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_order_rwo'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Visit 2. PNR -->
        <div class="bg-base-100 rounded-xl px-2.5 py-2 shadow-sm border border-base-300 flex flex-col justify-center">
            <div class="text-[9px] text-base-content/50 font-semibold uppercase tracking-wider mb-0.5" title="Kinerja toko pada pilar 2. PNR">Total 2. PNR</div>
            <div class="flex items-center justify-between">
                <div class="text-lg xl:text-xl font-extrabold text-base-content tracking-tight leading-none">{{ number_format($kpiData['total_pnr'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctPnr = ($kpiData['total_pnr'] ?? 0) > 0 ? (($kpiData['toko_order_pnr'] ?? 0) / ($kpiData['total_pnr'] ?? 1) * 100) : 0;
                            $pctPnrClass = $pctPnr >= 80 ? 'text-success bg-success/10' : ($pctPnr >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctPnrClass }}">{{ number_format($pctPnr, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-1 flex flex-col gap-0">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Toko Order</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['toko_order_pnr'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['toko_order_pnr'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 mt-0.5 pt-0.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Val Order</span>
                    <span class="text-[11px] font-bold text-success text-right" title="{{ number_format($kpiData['total_order_pnr'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_order_pnr'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Visit 3. NGVO -->
        <div class="bg-base-100 rounded-xl px-2.5 py-2 shadow-sm border border-base-300 flex flex-col justify-center">
            <div class="text-[9px] text-base-content/50 font-semibold uppercase tracking-wider mb-0.5" title="Kinerja toko pada pilar 3. NGVO">Total 3. NGVO</div>
            <div class="flex items-center justify-between">
                <div class="text-lg xl:text-xl font-extrabold text-base-content tracking-tight leading-none">{{ number_format($kpiData['total_ngvo'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctNgvo = ($kpiData['total_ngvo'] ?? 0) > 0 ? (($kpiData['toko_order_ngvo'] ?? 0) / ($kpiData['total_ngvo'] ?? 1) * 100) : 0;
                            $pctNgvoClass = $pctNgvo >= 80 ? 'text-success bg-success/10' : ($pctNgvo >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctNgvoClass }}">{{ number_format($pctNgvo, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-1 flex flex-col gap-0">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Toko Order</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['toko_order_ngvo'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['toko_order_ngvo'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 mt-0.5 pt-0.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Val Order</span>
                    <span class="text-[11px] font-bold text-success text-right" title="{{ number_format($kpiData['total_order_ngvo'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_order_ngvo'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Total NOO -->
        <div class="bg-base-100 rounded-xl px-2.5 py-2 shadow-sm border border-base-300 flex flex-col justify-center">
            <div class="text-[9px] text-base-content/50 font-semibold uppercase tracking-wider mb-0.5" title="Jumlah toko berstatus NOO">Total NOO</div>
            <div class="flex items-center justify-between">
                <div class="text-lg xl:text-xl font-extrabold text-base-content tracking-tight leading-none">{{ number_format($kpiData['total_noo'] ?? 0, 0, ',', '.') }}</div>
            </div>
            
            <div class="mt-1 flex flex-col gap-0">
                <div class="flex justify-between items-center opacity-0 pointer-events-none">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">-</span>
                    <span class="text-[8px] font-bold px-1.5 py-0.5 rounded">-</span>
                </div>
                <div class="flex justify-between items-center border-t border-transparent pt-1.5 opacity-0 pointer-events-none">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">-</span>
                    <span class="text-[11px] font-bold">-</span>
                </div>
            </div>
        </div>

    </div>
    @endif
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary" />
                    Summary Visit Team Elite
                </h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Ringkasan kunjungan JKS Team Elite</p>
            </div>
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <select wire:model.live="selectedRegion" class="select select-sm select-bordered min-w-[150px]" @if(count($regions) <= 1) disabled @endif>
                    @if(count($regions) > 1) <option value="">Semua Region</option> @endif
                    @foreach($regions as $region)
                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="selectedLevel" class="select select-sm select-bordered min-w-[150px]" @if(count($levels) <= 1) disabled @endif>
                    @if(count($levels) > 1) <option value="">Semua Level</option> @endif
                    @foreach($levels as $level)
                        <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
                @if($currentTab === 'detail')
                <select wire:model.live="selectedTeam" class="select select-sm select-bordered min-w-[150px]" @if(empty($teams) || count($teams) <= 1) disabled @endif>
                    @if(count($teams) > 1 || count($teams) == 0) <option value="" disabled>Pilih Team...</option> @endif
                    @foreach($teams as $team)
                        <option value="{{ $team->team_code }}">{{ $team->team_name }}</option>
                    @endforeach
                </select>
                
                <select wire:model="selectedKeterangan" class="select select-sm select-bordered">
                    <option value="">Semua Keterangan</option>
                    <option value="RO">RO</option>
                    <option value="NOO">NOO</option>
                </select>
                @endif
                <input type="month" wire:model="selectedMonth" class="input input-sm input-bordered min-w-[150px]" />
                <x-ui.button size="sm" variant="primary" icon="magnifying-glass" wire:click="applyFilter">
                    Filter
                </x-ui.button>
            </div>
        </div>

        @if($isFiltered)
        @if($currentTab === 'dashboard')
        <div class="flex-1 overflow-auto bg-base-200/30 w-full p-4 md:p-6" 
             x-data="{
                 dataSummary: @js($dataSummary),
                 kpiData: @js($kpiData),
                 pillarChartInstance: null,
                 teamChartInstance: null,
                 
                 formatNumber(val) {
                     return new Intl.NumberFormat('id-ID').format(val);
                 },
                 
                 get topFulfillment() {
                     return [...this.dataSummary].map(item => {
                         const order = parseFloat(item.total_order) || 0;
                         const invoice = parseFloat(item.total_invoice) || 0;
                         const pct = order > 0 ? (invoice / order * 100) : (invoice > 0 ? 100 : 0);
                         return {
                             name: item.team_name || item.area_name || item.region_name,
                             order: order,
                             invoice: invoice,
                             pct: pct.toFixed(1)
                         };
                     }).sort((a, b) => b.pct - a.pct).slice(0, 5);
                 },
                 
                 get topLeakage() {
                     return [...this.dataSummary].map(item => {
                         const order = parseFloat(item.total_order) || 0;
                         const invoice = parseFloat(item.total_invoice) || 0;
                         const gap = invoice - order; 
                         const gapAbs = Math.abs(gap);
                         return {
                             name: item.team_name || item.area_name || item.region_name,
                             order: order,
                             invoice: invoice,
                             gap: gap,
                             gapAbs: gapAbs
                         };
                     }).filter(item => item.gap < 0).sort((a, b) => a.gap - b.gap).slice(0, 5); // Most negative first
                 },

                 init() {
                     if (typeof ApexCharts === 'undefined') {
                         let script = document.createElement('script');
                         script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
                         script.onload = () => this.renderCharts();
                         document.head.appendChild(script);
                     } else {
                         this.renderCharts();
                     }
                 },
                 
                 renderCharts() {
                     this.renderPillarChart();
                     this.renderTeamChart();
                 },

                 renderPillarChart() {
                     if (!this.$refs.pillarChart) return;
                     if (this.pillarChartInstance) {
                         this.pillarChartInstance.destroy();
                     }

                     const rwo_pct = this.kpiData.total_rwo > 0 ? (this.kpiData.toko_order_rwo / this.kpiData.total_rwo * 100) : 0;
                     const pnr_pct = this.kpiData.total_pnr > 0 ? (this.kpiData.toko_order_pnr / this.kpiData.total_pnr * 100) : 0;
                     const ngvo_pct = this.kpiData.total_ngvo > 0 ? (this.kpiData.toko_order_ngvo / this.kpiData.total_ngvo * 100) : 0;

                     var options = {
                         series: [{
                             name: 'Pencapaian',
                             data: [rwo_pct.toFixed(1), pnr_pct.toFixed(1), ngvo_pct.toFixed(1)]
                         }],
                         chart: {
                             type: 'bar',
                             height: 250,
                             toolbar: { show: false },
                             parentHeightOffset: 0
                         },
                         plotOptions: {
                             bar: {
                                 borderRadius: 4,
                                 horizontal: false,
                                 columnWidth: '40%',
                                 distributed: true,
                             }
                         },
                         dataLabels: {
                             enabled: true,
                             formatter: function (val) {
                                 return val + '%';
                             }
                         },
                         colors: ['#00a96e', '#ffbe00', '#3abff8'],
                         xaxis: {
                             categories: ['1. RWO', '2. PNR', '3. NGVO'],
                             labels: { style: { fontWeight: 600 } }
                         },
                         yaxis: { max: 100 },
                         legend: { show: false }
                     };

                     this.pillarChartInstance = new ApexCharts(this.$refs.pillarChart, options);
                     this.pillarChartInstance.render();
                 },
                 
                 renderTeamChart() {
                     if (!this.$refs.teamChart) return;
                     if (this.teamChartInstance) {
                         this.teamChartInstance.destroy();
                     }
                     
                     const chartData = [...this.dataSummary].slice(0, 15);
                     const categories = chartData.map(item => item.team_name || item.area_name || item.region_name);
                     const orderData = chartData.map(item => parseFloat(item.total_order) || 0);
                     const invoiceData = chartData.map(item => parseFloat(item.total_invoice) || 0);

                     var options = {
                         series: [{
                             name: 'Order',
                             data: orderData
                         }, {
                             name: 'Invoice',
                             data: invoiceData
                         }],
                         chart: {
                             type: 'bar',
                             height: 250,
                             toolbar: { show: false },
                             parentHeightOffset: 0
                         },
                         plotOptions: {
                             bar: {
                                 horizontal: false,
                                 columnWidth: '55%',
                                 borderRadius: 3
                             },
                         },
                         colors: ['#3abff8', '#00a96e'],
                         dataLabels: { enabled: false },
                         stroke: { show: true, width: 2, colors: ['transparent'] },
                         xaxis: {
                             categories: categories,
                             labels: { trim: true, hideOverlappingLabels: true, style: { fontSize: '10px' } }
                         },
                         yaxis: {
                             labels: {
                                 formatter: function (val) {
                                     if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                     if (val >= 1000) return (val / 1000).toFixed(0) + 'K';
                                     return val;
                                 }
                             }
                         },
                         tooltip: {
                             y: {
                                 formatter: (val) => {
                                     return this.formatNumber(val);
                                 }
                             }
                         }
                     };

                     this.teamChartInstance = new ApexCharts(this.$refs.teamChart, options);
                     this.teamChartInstance.render();
                 }
             }"
             wire:key="dashboard-{{ md5(json_encode($dataSummary)) }}">
             
             <!-- KPI Cards (4 cards) -->
             <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                 <!-- Card 1: Order -->
                 <div class="bg-base-100 rounded-xl p-4 shadow-sm border border-base-200 flex items-center justify-between">
                     <div>
                         <div class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-1">Total Order</div>
                         <div class="text-2xl font-bold text-info">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</div>
                         <div class="text-[10px] text-base-content/50 mt-1">Estimasi pendapatan awal</div>
                     </div>
                     <div class="rounded-full bg-info/10 text-info p-3">
                         <x-heroicon-o-shopping-bag class="w-8 h-8" />
                     </div>
                 </div>

                 <!-- Card 2: Invoice -->
                 <div class="bg-base-100 rounded-xl p-4 shadow-sm border border-base-200 flex items-center justify-between">
                     <div>
                         <div class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-1">Total Invoice</div>
                         <div class="text-2xl font-bold text-success">{{ number_format($kpiData['total_invoice'] ?? 0, 0, ',', '.') }}</div>
                         <div class="text-[10px] text-base-content/50 mt-1">Realisasi tagihan akhir</div>
                     </div>
                     <div class="rounded-full bg-success/10 text-success p-3">
                         <x-heroicon-o-document-currency-dollar class="w-8 h-8" />
                     </div>
                 </div>

                 <!-- Card 3: Selisih / Loss -->
                 @php
                     $gapInvoice = ($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0);
                 @endphp
                 <div class="bg-base-100 rounded-xl p-4 shadow-sm border {{ $gapInvoice < 0 ? 'border-error/50 bg-error/5' : 'border-base-200' }} flex items-center justify-between">
                     <div>
                         <div class="text-xs font-semibold {{ $gapInvoice < 0 ? 'text-error/80' : 'text-base-content/60' }} uppercase tracking-wider mb-1">Selisih / Loss</div>
                         <div class="text-2xl font-bold {{ $gapInvoice < 0 ? 'text-error' : 'text-success' }}">{{ number_format($gapInvoice, 0, ',', '.') }}</div>
                         <div class="text-[10px] {{ $gapInvoice < 0 ? 'text-error/70' : 'text-base-content/50' }} mt-1">
                            {{ $gapInvoice < 0 ? 'Kebocoran dari order' : 'Tidak ada kebocoran' }}
                         </div>
                     </div>
                     <div class="rounded-full {{ $gapInvoice < 0 ? 'bg-error/10 text-error' : 'bg-base-200 text-base-content/50' }} p-3">
                         @if($gapInvoice < 0)
                             <x-heroicon-o-arrow-trending-down class="w-8 h-8" />
                         @else
                             <x-heroicon-o-check-circle class="w-8 h-8" />
                         @endif
                     </div>
                 </div>

                 <!-- Card 4: Fulfillment Rate -->
                 <div class="bg-base-100 rounded-xl p-4 shadow-sm border border-base-200 flex items-center justify-between">
                     <div>
                         <div class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-1">Fulfillment Rate</div>
                         <div class="text-2xl font-bold">{{ number_format(($kpiData['total_order'] ?? 0) > 0 ? (($kpiData['total_invoice'] ?? 0) / ($kpiData['total_order']) * 100) : 0, 1, ',', '.') }}%</div>
                         <div class="text-[10px] text-base-content/50 mt-1">Rasio Invoice terhadap Order</div>
                     </div>
                     @php
                        $fulfillPct = ($kpiData['total_order'] ?? 0) > 0 ? (($kpiData['total_invoice'] ?? 0) / ($kpiData['total_order']) * 100) : 0;
                        $fulfillColor = $fulfillPct >= 90 ? 'text-success bg-success/10' : ($fulfillPct >= 70 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                     @endphp
                     <div class="radial-progress font-bold text-xs border-4 border-base-100 {{ $fulfillColor }}" style="--value:{{ $fulfillPct }}; --size:3.5rem;">
                         {{ number_format($fulfillPct, 0) }}%
                     </div>
                 </div>
             </div>

             <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                 <!-- Regional/Team Performance Chart -->
                 <div class="bg-base-100 rounded-xl p-4 shadow-sm border border-base-200 lg:col-span-2">
                     <h3 class="font-bold text-sm mb-4">Order vs Invoice <span class="text-base-content/50 text-xs font-normal ml-2" x-text="dataSummary.length > 0 ? (dataSummary[0].team_name ? '(per Tim)' : '(per Region)') : ''"></span></h3>
                     <div id="teamChart" x-ref="teamChart" class="w-full"></div>
                 </div>
                 
                 <!-- Pillar Chart -->
                 <div class="bg-base-100 rounded-xl p-4 shadow-sm border border-base-200 lg:col-span-1">
                     <h3 class="font-bold text-sm mb-4">Pencapaian 3 Pilar</h3>
                     <div id="pillarChart" x-ref="pillarChart" class="w-full"></div>
                 </div>
             </div>
             
             <!-- Leaderboard -->
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <!-- Top Leakage (Worst) -->
                 <div class="bg-base-100 rounded-xl p-4 shadow-sm border border-base-200">
                     <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
                         <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-error" />
                         Top Leakage / Selisih Terbesar
                     </h3>
                     <div class="flex flex-col gap-3">
                         <template x-for="(team, index) in topLeakage" :key="index">
                             <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-error/5 transition-colors border border-transparent hover:border-error/20">
                                 <div class="font-black text-error/30 text-lg w-5 text-center" x-text="index + 1"></div>
                                 <div class="flex-1 min-w-0">
                                     <div class="text-xs font-bold text-error/90 truncate" x-text="team.name" :title="team.name"></div>
                                     <div class="text-[10px] text-base-content/60 truncate" x-text="'Order: ' + formatNumber(team.order) + ' | Invoice: ' + formatNumber(team.invoice)"></div>
                                 </div>
                                 <div class="text-xs font-bold px-2 py-1 rounded bg-error/10 text-error" x-text="formatNumber(team.gap)"></div>
                             </div>
                         </template>
                         <div x-show="topLeakage.length === 0" class="text-xs text-success font-medium py-2 flex items-center gap-2">
                             <x-heroicon-o-check-circle class="w-4 h-4" /> Tidak ada tim dengan selisih negatif!
                         </div>
                     </div>
                 </div>

                 <!-- Top Fulfillment (Best) -->
                 <div class="bg-base-100 rounded-xl p-4 shadow-sm border border-base-200">
                     <h3 class="font-bold text-sm mb-4 flex items-center gap-2">
                         <x-heroicon-s-star class="w-4 h-4 text-success" />
                         Top Fulfillment Rate
                     </h3>
                     <div class="flex flex-col gap-3">
                         <template x-for="(team, index) in topFulfillment" :key="index">
                             <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-success/5 transition-colors border border-transparent hover:border-success/20">
                                 <div class="font-black text-success/30 text-lg w-5 text-center" x-text="index + 1"></div>
                                 <div class="flex-1 min-w-0">
                                     <div class="text-xs font-bold text-success/90 truncate" x-text="team.name" :title="team.name"></div>
                                     <div class="text-[10px] text-base-content/60 truncate" x-text="'Order: ' + formatNumber(team.order) + ' | Invoice: ' + formatNumber(team.invoice)"></div>
                                 </div>
                                 <div class="text-xs font-bold px-2 py-1 rounded bg-success/10 text-success" x-text="team.pct + '%'"></div>
                             </div>
                         </template>
                         <div x-show="topFulfillment.length === 0" class="text-xs text-base-content/50 italic py-2">Tidak ada data.</div>
                     </div>
                 </div>
             </div>
        </div>
        @elseif($currentTab === 'summary')
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra w-full whitespace-nowrap text-xs">
                <thead class="sticky top-0 z-10 text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm [&_th]:bg-base-300">
                <tr>
                    <th rowspan="2" class="align-middle border-b border-r border-base-200">Region</th>
                    <th rowspan="2" class="align-middle border-b border-r border-base-200">Area</th>
                    <th rowspan="2" class="align-middle border-b border-r border-base-200">Team</th>
                    <th colspan="3" class="text-center border-b border-r border-base-200">Total Toko</th>
                    <th colspan="5" class="text-center border-b border-r border-base-200">Value</th>
                    <th colspan="3" class="text-center border-b border-r border-base-200">1. RWO</th>
                    <th colspan="3" class="text-center border-b border-r border-base-200">2. PNR</th>
                    <th colspan="3" class="text-center border-b border-base-200">3. NGVO</th>
                </tr>
                <tr>
                    <th class="text-center border-b border-base-200">JKS</th>
                    <th class="text-center border-b border-base-200">Visit</th>
                    <th class="text-center border-b border-r border-base-200">%</th>
                    
                    <th class="text-right border-b border-base-200">Target</th>
                    <th class="text-right border-b border-base-200">Order</th>
                    <th class="text-center border-b border-base-200">%</th>
                    <th class="text-right border-b border-base-200">Invoice</th>
                    <th class="text-right border-b border-r border-base-200">Selisih</th>
                    
                    <th class="text-center border-b border-base-200">JKS</th>
                    <th class="text-center border-b border-base-200">Visit</th>
                    <th class="text-center border-b border-r border-base-200">%</th>
                    
                    <th class="text-center border-b border-base-200">JKS</th>
                    <th class="text-center border-b border-base-200">Visit</th>
                    <th class="text-center border-b border-r border-base-200">%</th>
                    
                    <th class="text-center border-b border-base-200">JKS</th>
                    <th class="text-center border-b border-base-200">Visit</th>
                    <th class="text-center border-b border-base-200">%</th>
                </tr>
                </thead>
                <tbody class="text-xs">
                @if(empty($dataSummary))
                    <tr>
                        <td colspan="99">
                            <div class="flex flex-col items-center justify-center py-12 gap-3 text-base-content/40">
                                <x-heroicon-o-inbox class="w-10 h-10" />
                                <p class="text-sm">Tidak ada data.</p>
                            </div>
                        </td>
                    </tr>
                @else
                    @foreach($dataSummary as $row)
                <tr>
                    <td class="whitespace-nowrap border-r border-base-200/50">{{ $row->region_name }}</td>
                    <td class="whitespace-nowrap border-r border-base-200/50">{{ $row->area_name }}</td>
                    <td class="whitespace-nowrap font-medium border-r border-base-200/50">{{ $row->team_name }}</td>
                    
                    <td class="whitespace-nowrap text-center">{{ number_format($row->total_toko, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">{{ number_format($row->total_visit, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center border-r border-base-200/50">
                        @php
                            $pct = $row->total_toko > 0 ? ($row->total_visit / $row->total_toko) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($pct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                    
                    <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->total_target ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right font-bold">{{ number_format($row->total_order ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center border-base-200/50">
                        @php
                            $valPct = $row->total_target > 0 ? ($row->total_order / $row->total_target) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $valPct >= 80 ? 'success' : ($valPct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($valPct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap font-mono text-right font-bold text-primary">{{ number_format($row->total_invoice ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right border-r border-base-200/50 {{ (($row->total_invoice ?? 0) - ($row->total_order ?? 0)) < 0 ? 'text-error' : 'text-success' }}">{{ number_format(($row->total_invoice ?? 0) - ($row->total_order ?? 0), 0, ',', '.') }}</td>
                    
                    <td class="whitespace-nowrap text-center">{{ number_format($row->rwo_toko, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">{{ number_format($row->rwo_visit, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center border-r border-base-200/50">
                        @php
                            $rwoPct = $row->rwo_toko > 0 ? ($row->rwo_visit / $row->rwo_toko) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $rwoPct >= 80 ? 'success' : ($rwoPct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($rwoPct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                    
                    <td class="whitespace-nowrap text-center">{{ number_format($row->pnr_toko, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">{{ number_format($row->pnr_visit, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center border-r border-base-200/50">
                        @php
                            $pnrPct = $row->pnr_toko > 0 ? ($row->pnr_visit / $row->pnr_toko) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $pnrPct >= 80 ? 'success' : ($pnrPct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($pnrPct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                    
                    <td class="whitespace-nowrap text-center">{{ number_format($row->ngvo_toko, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">{{ number_format($row->ngvo_visit, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">
                        @php
                            $ngvoPct = $row->ngvo_toko > 0 ? ($row->ngvo_visit / $row->ngvo_toko) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $ngvoPct >= 80 ? 'success' : ($ngvoPct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($ngvoPct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                </tr>
                    @endforeach
                @endif
                </tbody>
                @if(!empty($dataSummary))
                <tfoot class="sticky bottom-0 z-10 bg-base-200 shadow-[0_-1px_3px_rgba(0,0,0,0.1)]">
                    <tr class="font-bold border-t-2 border-base-300">
                    <td colspan="3" class="text-right border-r border-base-200/50 uppercase tracking-wider">Subtotal</td>
                    <td class="text-center">{{ number_format($kpiData['total_toko'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-r border-base-200/50">
                        @php
                            $pctTotal = ($kpiData['total_toko'] ?? 0) > 0 ? (($kpiData['total_visit'] ?? 0) / ($kpiData['total_toko'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($pctTotal, 1, ',', '.') }}%
                    </td>
                    
                    <td class="text-right font-mono">{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-base-200/50">
                        @php
                            $valPctTotal = ($kpiData['total_target'] ?? 0) > 0 ? (($kpiData['total_order'] ?? 0) / ($kpiData['total_target'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($valPctTotal, 1, ',', '.') }}%
                    </td>
                    <td class="text-right font-mono">{{ number_format($kpiData['total_invoice'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono border-r border-base-200/50 {{ (($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0)) < 0 ? 'text-error' : 'text-success' }}">
                        {{ number_format(($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0), 0, ',', '.') }}
                    </td>
                    
                    <td class="text-center">{{ number_format($kpiData['total_rwo'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($kpiData['toko_order_rwo'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-r border-base-200/50">
                        @php
                            $rwoPctTotal = ($kpiData['total_rwo'] ?? 0) > 0 ? (($kpiData['toko_order_rwo'] ?? 0) / ($kpiData['total_rwo'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($rwoPctTotal, 1, ',', '.') }}%
                    </td>
                    
                    <td class="text-center">{{ number_format($kpiData['total_pnr'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($kpiData['toko_order_pnr'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-r border-base-200/50">
                        @php
                            $pnrPctTotal = ($kpiData['total_pnr'] ?? 0) > 0 ? (($kpiData['toko_order_pnr'] ?? 0) / ($kpiData['total_pnr'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($pnrPctTotal, 1, ',', '.') }}%
                    </td>
                    
                    <td class="text-center">{{ number_format($kpiData['total_ngvo'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($kpiData['toko_order_ngvo'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-r border-base-200/50">
                        @php
                            $ngvoPctTotal = ($kpiData['total_ngvo'] ?? 0) > 0 ? (($kpiData['toko_order_ngvo'] ?? 0) / ($kpiData['total_ngvo'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($ngvoPctTotal, 1, ',', '.') }}%
                    </td>
                </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @elseif($currentTab === 'detail')
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap text-xs">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm [&_th]:bg-base-300">
                <tr>
                    <th>Region</th>
                    <th>Area</th>
                    <th>Team</th>
                    <th>Cust No</th>
                    <th>Uniq ID</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Ket</th>
                    <th>Visit</th>
                    <th>Pilar</th>
                    <th class="text-right">Target</th>
                    <th class="text-right">Order Val</th>
                    <th class="text-right">Invoice</th>
                    <th class="text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('selisih')">
                        Selisih
                        @if($sortField === 'selisih')
                            <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 inline-block ml-1" />
                        @else
                            <x-heroicon-s-chevron-up-down class="w-3 h-3 inline-block ml-1 opacity-30" />
                        @endif
                    </th>
                    <th class="text-center">Remark</th>
                </tr>
                </thead>
                <tbody class="text-xs">
                @if(empty($dataKunjungan))
                    <tr>
                        <td colspan="99">
                            <div class="flex flex-col items-center justify-center py-12 gap-3 text-base-content/40">
                                <x-heroicon-o-inbox class="w-10 h-10" />
                                <p class="text-sm">Tidak ada data.</p>
                            </div>
                        </td>
                    </tr>
                @else
                    @foreach($dataKunjungan as $row)
                <tr class="{{ str_contains(strtoupper($row->keterangan ?? ''), 'NOO') ? '!bg-warning/20 hover:!bg-warning/30' : '' }}">
                    <td class="whitespace-nowrap">{{ $row->region_name }}</td>
                    <td class="whitespace-nowrap">{{ $row->area_name }}</td>
                    <td class="max-w-[150px] truncate" title="{{ $row->team_name }}">{{ $row->team_name }}</td>
                    <td class="max-w-[100px] truncate" title="{{ $row->custno }}">{{ $row->custno }}</td>
                    <td class="max-w-[120px] truncate" title="{{ $row->uniq_id }}">{{ $row->uniq_id }}</td>
                    <td class="max-w-[145px] truncate" title="{{ $row->custname }}">{{ $row->custname }}</td>
                    <td class="max-w-[110px] text-xs truncate" title="{{ $row->address }}">{{ $row->address }}</td>
                    <td class="whitespace-nowrap">{{ $row->keterangan }}</td>
                    <td class="whitespace-nowrap text-center">
                        @if($row->status_visit == 'Y')
                            <x-ui.badge variant="success">Yes</x-ui.badge>
                        @elseif($row->status_visit == 'N')
                            <x-ui.badge variant="error">No</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">{{ $row->status_visit }}</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap text-center">
                        @if($row->pilar)
                            <x-ui.badge variant="neutral">{{ $row->pilar }}</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->target ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->order_val ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right font-bold">{{ number_format($row->invoice ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right {{ (($row->invoice ?? 0) - ($row->order_val ?? 0)) < 0 ? 'text-error' : 'text-success' }}">{{ number_format(($row->invoice ?? 0) - ($row->order_val ?? 0), 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">
                        <div class="flex items-center justify-center gap-1">
                            @if(!empty($row->remark))
                            <button wire:click="openRemarkModal('{{ $row->bulan }}', '{{ $row->team_code }}', '{{ $row->custno }}', '{{ addslashes($row->remark ?? '') }}')" class="btn btn-xs btn-ghost btn-square text-info hover:bg-info/20 shrink-0" title="Lihat Remark">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </button>
                            @else
                            <span class="w-6 h-6 inline-block shrink-0"></span> <!-- Placeholder agar tombol edit tetap sejajar -->
                            @endif
                            <button wire:click="openRemarkModal('{{ $row->bulan }}', '{{ $row->team_code }}', '{{ $row->custno }}', '{{ addslashes($row->remark ?? '') }}')" class="btn btn-xs btn-ghost btn-square text-primary hover:bg-primary/20 shrink-0" title="Edit Remark">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </button>
                        </div>
                    </td>
                </tr>
                    @endforeach
                @endif
                </tbody>
                @if(!empty($dataKunjungan))
                <tfoot class="sticky bottom-0 z-10 bg-base-200 shadow-[0_-1px_3px_rgba(0,0,0,0.1)]">
                    <tr class="font-bold border-t-2 border-base-300">
                    <td colspan="10" class="text-right uppercase tracking-wider">Subtotal</td>
                    <td class="text-right font-mono text-sm text-base-content">{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-sm text-base-content">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-sm text-base-content">{{ number_format($kpiData['total_invoice'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-sm {{ (($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0)) < 0 ? 'text-error' : 'text-success' }}">{{ number_format(($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @endif
        @else
        <div class="p-8 text-center text-base-content/60">
            <x-heroicon-o-funnel class="w-12 h-12 mx-auto mb-3 opacity-50" />
            @if($currentTab === 'detail')
            <p>Silakan pilih <strong>Team</strong> secara spesifik dan sesuaikan filter lainnya, lalu klik tombol <strong>Filter</strong> untuk menampilkan data.</p>
            @else
            <p>Silakan sesuaikan filter bulan, level atau team, lalu klik tombol <strong>Filter</strong> untuk menampilkan data summary.</p>
            @endif
        </div>
        @endif
    </div>

    <!-- Remark Modal -->
    <input type="checkbox" id="remark_modal" class="modal-toggle" wire:model.live="showRemarkModal" />
    <div class="modal modal-bottom sm:modal-middle" role="dialog">
        <div class="modal-box relative">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" wire:click="closeRemarkModal">✕</button>
            <h3 class="text-lg font-bold flex items-center gap-2">
                <x-heroicon-o-pencil-square class="w-5 h-5 text-primary" />
                Edit Remark
            </h3>
            <div class="py-4">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">Tulis Catatan / Remark</span>
                    </label>
                    <textarea class="textarea textarea-bordered h-24" wire:model="editingRemark.remark" placeholder="Tuliskan remark di sini..."></textarea>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn" wire:click="closeRemarkModal">Batal</button>
                <button type="button" class="btn btn-primary" wire:click="saveRemark" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveRemark">Simpan</span>
                    <span wire:loading wire:target="saveRemark" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
        <label class="modal-backdrop" wire:click="closeRemarkModal">Close</label>
    </div>
</div>
