<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\ConfigSalesInvoiceDistributorController;

// Livewire Components (Alphabetically ordered)
use App\Livewire\Auth\Login;
use App\Livewire\CallPlan\Index as CallPlanIndex;

use App\Livewire\Eskalink\CustomerEska\Index as CustomerEskaIndex;
use App\Livewire\Eskalink\CustomerEskaDist\Index as CustomerEskaDistIndex;
use App\Livewire\Eskalink\CustomerEskaMap\Index as CustomerEskaMapIndex;
use App\Livewire\Eskalink\CustomerEskaUnmap\Index as CustomerEskaUnmapIndex;
use App\Livewire\CustomerExportComponent;
use App\Livewire\Customers\CustomerData;
use App\Livewire\Dashboard;
use App\Livewire\Dashboard\AnalyticsDashboard;
use App\Livewire\Dashboard\AreaSellInDashboard;
use App\Livewire\Dashboard\CabangSellInDashboard;
use App\Livewire\Dashboard\NationalSellInDashboard;
use App\Livewire\Dashboard\SupervisorSellInDashboard;
use App\Livewire\Dashboard\DistributorMap;
use App\Livewire\Dashboard\MetabaseDashboard;
use App\Livewire\Dashboard\SalesComparison;
use App\Livewire\Dashboard\SellingOverviewNew;

use App\Livewire\Mapping\Product\Index as ProductMappingIndex;
use App\Livewire\Mapping\Salesman\Index as SalesmanMappingIndex;
use App\Livewire\UnMapping\UnmappedProduct\Index as UnmappedProductIndex;
use App\Livewire\UnMapping\UnmappedSalesman\Index as UnmappedSalesmanIndex;
use App\Livewire\MasterData\MasterAreas\Index as MasterAreaIndex;
use App\Livewire\MasterData\MasterBranches\Index as MasterBranchIndex;
use App\Livewire\MasterData\MappingDistributorImplementasiEskalink\Index as MappingDistributorImplementasiEskalinkIndex;
use App\Livewire\MasterData\MasterDistributors\Index as MasterDistributorsIndex;
use App\Livewire\MasterData\MasterRegions\Index as MasterRegionsIndex;
use App\Livewire\MasterData\MasterSupervisors\Index as MasterSupervisorIndex;
use App\Livewire\Settings\MenuManagement;
use App\Livewire\Pages\UnderBounce;
use App\Livewire\Pages\UnderConstruction;
use App\Livewire\JksTeamElite\Index as JksTeamEliteIndex;
use App\Livewire\CallPlan\JksTeamElite\MasterCustomer\MonitoringCustomerPareto\Index as MonitoringCustomerParetoIndex;
use App\Livewire\CallPlan\JksTeamElite\RouteEfficiency\Index as RouteEfficiencyIndex;
use App\Livewire\CallPlan\JksTeamElite\Clustering\Index as ClusteringIndex;
use App\Livewire\CallPlan\JksTeamElite\ListTokoPareto\Index as ListTokoPareto;
use App\Livewire\CallPlan\JksTeamElite\SummaryKunjungan\Index as SummaryKunjunganIndex;
use App\Livewire\CallPlan\JksTeamElite\SummaryVisitTeamElite\Index as SummaryVisitTeamEliteIndex;
use App\Livewire\CallPlan\JksTeamElite\MonitoringJKSTeamElite\Index as MonitoringJKSTeamEliteIndex;
use App\Livewire\CallPlan\JksTeamElite\MonitoringJksSisoVsEska\Index as MonitoringJksSisoVsEskaIndex;
use App\Livewire\CallPlan\JksTeamElite\MasterCustomer\Index as JksMasterCustomerIndex;
use App\Livewire\CallPlan\JksTeamElite\MonitoringOutletPareto\Index as MonitoringOutletParetoIndex;
use App\Livewire\MasterData\Product\BrandProduct\Index as ProductBrandIndex;
use App\Livewire\MasterData\Product\CategoriesProduct\Index as CategoryIndex;
use App\Livewire\MasterData\Product\GroupProduct\Index as ProductGroupIndex;
use App\Livewire\MasterData\Product\LineProduct\Index as ProductLineIndex;
use App\Livewire\MasterData\Product\MasterProduct\Index as ProductMasterIndex;
use App\Livewire\MasterData\Product\ProductCategories\Index as ProductCategoryIndex;
use App\Livewire\MasterData\Product\SubBrandProduct\Index as ProductSubBrandIndex;
use App\Livewire\Mapping\UnitMapping\Index as UnitMappingIndex;
use App\Livewire\Eskalink\ProdukEska\Index as ProdukEskaIndex;
use App\Livewire\Eskalink\ProdukEskaMap\Index as ProdukEskaMapIndex;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\RoleManagement;
use App\Livewire\Settings\AccessGroupManagement;
use App\Livewire\Settings\ActivityLogManagement;
use App\Livewire\Rwo\Index as RwoIndex;
use App\Livewire\SalesConfig\Create as SalesConfigCreate;
use App\Livewire\SalesConfig\Edit as SalesConfigEdit;
use App\Livewire\SalesConfig\Index as SalesConfigIndex;
use App\Livewire\SalesInvoiceImport;
use App\Livewire\SalesInvoiceReport\Index as SalesInvoiceReportIndex;
use App\Livewire\Report\AnalisaKunjungan\Index as AnalisaKunjunganIndex;
use App\Livewire\MasterData\Salesmans\Index as SalesmanIndex;
use App\Livewire\SellOut\Export\Index as SellOutExportIndex;
use App\Livewire\SellOut\Process\Index as SellOutProcessIndex;
use App\Livewire\SellOut\ProcessV2\Index as SellOutProcessIndexV2;
use App\Livewire\SellingIn\Index as SellingInIndex;
use App\Livewire\SellingIn\Import as SellingInImport;
use App\Livewire\Eskalink\SellingOutEskalink\Index as SellingOutEskalinkIndex;
use App\Livewire\Settings\UserManagement;
use App\Livewire\Welcome;
use App\Livewire\Others\MappingSupervisorCode\Index as MappingSupervisorCodeIndex;
use App\Livewire\Others\ComponentStandard\Index as ComponentStandardIndex;
use App\Livewire\Others\LayoutStandard\Index as LayoutStandardIndex;
use App\Livewire\Others\PageTemplateStandard\Index as PageTemplateStandardIndex;
use App\Livewire\Others\Qceskalink\Index as QceskalinkIndex;
use App\Livewire\Others\Perbaikantikor\Index as AdminPerbaikantikorIndex;
use App\Livewire\Others\PerbaikantikorElite\Index as AdminPerbaikantikorEliteIndex;
use App\Livewire\Others\AuditToko\Index as AdminAuditTokoIndex;
use App\Livewire\Others\AuditToko\MasterCustomer as AdminAuditTokoMasterCustomer;
use App\Livewire\Others\TargetPerSeUntukEska\Index as TargetPerSeUntukEskaIndex;
use App\Livewire\MonitoringDevice\Index as MonitoringDeviceIndex;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// MOBILE ROUTES
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/mobile/login', [\App\Http\Controllers\Mobile\AuthController::class, 'showLogin'])->name('mobile.login');
    Route::post('/mobile/login', [\App\Http\Controllers\Mobile\AuthController::class, 'login']);
    
    // Audit specific login
    Route::get('/mobile/audit/login', [\App\Http\Controllers\Mobile\Audit\IndexController::class, 'showLogin'])->name('mobile.audit.login');
    Route::post('/mobile/audit/login', [\App\Http\Controllers\Mobile\Audit\IndexController::class, 'login']);
});

Route::redirect('/mobile/monitoring-device', '/app/monitoring-device')->name('mobile.monitoring-device.index');

Route::get('/mobile/perbaikan-tikor', [\App\Http\Controllers\Mobile\PerbaikanTikor\IndexController::class, 'index'])->name('mobile.perbaikan.tikor.index');
Route::post('/mobile/perbaikan-tikor', [\App\Http\Controllers\Mobile\PerbaikanTikor\IndexController::class, 'store'])->name('mobile.perbaikan.tikor.store');
Route::get('/mobile/perbaikan-tikor/search-sales', [\App\Http\Controllers\Mobile\PerbaikanTikor\IndexController::class, 'searchSales'])->name('mobile.perbaikan.tikor.search-sales')->middleware('throttle:30,1');
Route::post('/mobile/perbaikan-tikor/login', [\App\Http\Controllers\Mobile\PerbaikanTikor\IndexController::class, 'loginSales'])->name('mobile.perbaikan.tikor.login')->middleware('throttle:30,1');
Route::post('/mobile/perbaikan-tikor/logout', [\App\Http\Controllers\Mobile\PerbaikanTikor\IndexController::class, 'logoutSales'])->name('mobile.perbaikan.tikor.logout');
Route::get('/app/monitoring-device', [\App\Http\Controllers\Mobile\MonitoringDeviceController::class, 'index'])->name('mobile.app.monitoring-device.index');
Route::post('/app/monitoring-device', [\App\Http\Controllers\Mobile\MonitoringDeviceController::class, 'store'])->name('mobile.app.monitoring-device.store');
Route::post('/app/monitoring-device/login', [\App\Http\Controllers\Mobile\MonitoringDeviceController::class, 'loginSales'])->name('mobile.app.monitoring-device.login')->middleware('throttle:30,1');
Route::post('/app/monitoring-device/logout', [\App\Http\Controllers\Mobile\MonitoringDeviceController::class, 'logoutSales'])->name('mobile.app.monitoring-device.logout');
Route::post('/app/monitoring-device/destroy-image', [\App\Http\Controllers\Mobile\MonitoringDeviceController::class, 'destroyImage'])->name('mobile.app.monitoring-device.destroy-image');

Route::middleware('auth')->group(function () {
    Route::get('/mobile/audit', [\App\Http\Controllers\Mobile\Audit\IndexController::class, 'index'])->name('mobile.audit.index');
    Route::post('/mobile/audit', [\App\Http\Controllers\Mobile\Audit\IndexController::class, 'store'])->name('mobile.audit.store');
    Route::delete('/mobile/audit/{id}', [\App\Http\Controllers\Mobile\Audit\IndexController::class, 'destroy'])->name('mobile.audit.destroy');

    Route::get('/mobile/audit/thumbnail', [\App\Http\Controllers\Mobile\Audit\IndexController::class, 'thumbnail'])->name('mobile.audit.thumbnail');
    Route::post('/mobile/audit/logout', [\App\Http\Controllers\Mobile\Audit\IndexController::class, 'logout'])->name('mobile.audit.logout');

    Route::post('/mobile/logout', [\App\Http\Controllers\Mobile\AuthController::class, 'logout'])->name('mobile.logout');
    Route::inertia('/mobile/portal', 'Mobile/Portal/Index')->name('mobile.portal');
    Route::inertia('/mobile/home', 'mobile/Pages/Home')->name('mobile.home');
    Route::inertia('/mobile/profile', 'mobile/Pages/Profile')->name('mobile.profile');
    Route::post('/mobile/profile/update', [\App\Http\Controllers\Mobile\ProfileController::class, 'updateProfile'])->name('mobile.profile.update');
    Route::post('/mobile/profile/password', [\App\Http\Controllers\Mobile\ProfileController::class, 'updatePassword'])->name('mobile.profile.password');

    Route::get('/mobile/rwo', \App\Livewire\Rwo\MobileUpdate::class)->name('mobile.rwo.update');

    Route::get('/mobile/rwo-inertia', [\App\Http\Controllers\Mobile\Rwo\IndexController::class, 'index'])->name('mobile.rwo.inertia');
    Route::post('/mobile/rwo-inertia/upload', [\App\Http\Controllers\Mobile\Rwo\IndexController::class, 'upload'])->name('mobile.rwo.inertia.upload');
    Route::post('/mobile/rwo-inertia/edit', [\App\Http\Controllers\Mobile\Rwo\IndexController::class, 'edit'])->name('mobile.rwo.inertia.edit');

    Route::get('/mobile/skb-rwo', [\App\Http\Controllers\Mobile\SkbRwo\IndexController::class, 'index'])->name('mobile.skb-rwo.index');
    Route::post('/mobile/skb-rwo/login', [\App\Http\Controllers\Mobile\SkbRwo\IndexController::class, 'loginSupervisor'])->name('mobile.skb-rwo.login');
    Route::post('/mobile/skb-rwo/logout', [\App\Http\Controllers\Mobile\SkbRwo\IndexController::class, 'logoutSupervisor'])->name('mobile.skb-rwo.logout');
    Route::post('/mobile/skb-rwo/submit-skb', [\App\Http\Controllers\Mobile\SkbRwo\IndexController::class, 'submitSkb'])->name('mobile.skb-rwo.submit-skb');
    Route::post('/mobile/skb-rwo/submit-data', [\App\Http\Controllers\Mobile\SkbRwo\IndexController::class, 'submitData'])->name('mobile.skb-rwo.submit-data');
    Route::get('/mobile/skb-rwo/history-order/{customer_code}', [\App\Http\Controllers\Mobile\SkbRwo\IndexController::class, 'getHistoryOrder'])->name('mobile.skb-rwo.history-order');
    Route::get('/mobile/skb-rwo/history-produk', [\App\Http\Controllers\Mobile\SkbRwo\IndexController::class, 'getHistoryProduk'])->name('mobile.skb-rwo.history-produk');

    Route::get('/mobile/call-plan', [\App\Http\Controllers\Mobile\CallPlan\IndexController::class, 'index'])->name('mobile.call-plan.index');
    Route::post('/mobile/call-plan/login', [\App\Http\Controllers\Mobile\CallPlan\IndexController::class, 'loginSupervisor'])->name('mobile.call-plan.login');
    Route::post('/mobile/call-plan/logout', [\App\Http\Controllers\Mobile\CallPlan\IndexController::class, 'logoutSupervisor'])->name('mobile.call-plan.logout');


    Route::get('/mobile/perbaikan-tikor-tim-elite', [\App\Http\Controllers\Mobile\PerbaikanTikorTimElite\IndexController::class, 'index'])->name('mobile.perbaikan.tikor-tim-elite.index');
    Route::post('/mobile/perbaikan-tikor-tim-elite', [\App\Http\Controllers\Mobile\PerbaikanTikorTimElite\IndexController::class, 'store'])->name('mobile.perbaikan.tikor-tim-elite.store');

});

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware(['auth'])->group(function () {

    // ==========================================
    // AUTHENTICATION & PROFILE
    // ==========================================
    Route::post('/logout', function () {
        \App\Helpers\ActivityLogger::log('Logout', 'User berhasil logout dari sistem.');
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
    
    Route::get('/profile', Profile::class)->name('profile');

    // ==========================================
    // SYSTEM ADMINISTRATION
    // ==========================================
    Route::get('/roles', RoleManagement::class)->name('roles.index');
    Route::get('/users', UserManagement::class)->name('users.index');
    Route::get('/menus', MenuManagement::class)->name('menus.index')->middleware(['role:admin']);
    Route::get('/access-groups', AccessGroupManagement::class)->name('access-groups.index')->middleware(['role:admin']);
    Route::get('/activity-logs', ActivityLogManagement::class)->name('activity-logs.index')->middleware(['role:admin']);
    Route::get('/api-management', \App\Livewire\Settings\ApiManagement::class)->name('api-management.index')->middleware(['role:admin']);

    // ==========================================
    // DASHBOARD & ANALYTICS
    // ==========================================
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/welcome', Welcome::class)->name('welcome');
    Route::get('/dashboard/distributor-map', DistributorMap::class)->name('dashboard.distributor-map');
    Route::get('/dashboard/metabase', MetabaseDashboard::class)->name('dashboard.metabase');
    Route::get('/dashboard/analytics', AnalyticsDashboard::class)->name('dashboard.analytics');
    Route::get('/dashboard/national-sell-in', NationalSellInDashboard::class)->name('dashboard.national-sell-in');
    Route::get('/dashboard/area-sell-in', AreaSellInDashboard::class)->name('dashboard.area-sell-in');
    Route::get('/dashboard/cabang-sell-in', CabangSellInDashboard::class)->name('dashboard.cabang-sell-in');
    Route::get('/dashboard/supervisor-sell-in', SupervisorSellInDashboard::class)->name('dashboard.supervisor-sell-in');
    Route::get('/dashboard/sales-comparison', SalesComparison::class)->name('dashboard.sales-comparison');
    Route::get('/dashboard/selling-overview', SellingOverviewNew::class)->name('dashboard.selling-overview');

    // ==========================================
    // MASTER DATA: DISTRIBUTION
    // ==========================================
    Route::get('/master-regions', MasterRegionsIndex::class)->name('master-regions.index')->middleware('menu.access');
    Route::get('/master-areas', MasterAreaIndex::class)->name('master-areas.index');
    Route::get('/master-supervisors', MasterSupervisorIndex::class)->name('master-supervisors.index');
    Route::get('/master-branches', MasterBranchIndex::class)->name('master-branches.index');
    Route::get('/master-distributors', MasterDistributorsIndex::class)->name('master-distributors.index');
    Route::get('/mapping-distributor-implementasi-eskalink', MappingDistributorImplementasiEskalinkIndex::class)->name('mapping-distributor-implementasi-eskalink.index');
    Route::get('/monitoringbankgaransi', \App\Livewire\Others\MonitoringBankGaransi\Index::class)->name('monitoringbankgaransi.index');

    // ==========================================
    // MASTER DATA: PRODUCT
    // ==========================================
    Route::get('/product-lines', ProductLineIndex::class)->name('product-lines.index');
    Route::get('/product-brands', ProductBrandIndex::class)->name('product-brands.index');
    Route::get('/product-groups', ProductGroupIndex::class)->name('product-groups.index');
    Route::get('/product-sub-brands', ProductSubBrandIndex::class)->name('product-sub-brands.index');
    Route::get('/categories', CategoryIndex::class)->name('categories.index');
    Route::get('/product-categories', ProductCategoryIndex::class)->name('product-categories.index');
    Route::get('/product-masters', ProductMasterIndex::class)->name('product-masters.index');
    Route::get('/product-unit-mappings', UnitMappingIndex::class)->name('product-unit-mappings.index');

    // ==========================================
    // MASTER DATA: SALESMAN
    // ==========================================
    Route::get('/salesmans', SalesmanIndex::class)->name('salesmans.index');

    // ==========================================
    // MAPPING & UNMAPPING
    // ==========================================
    Route::get('/product-mappings', ProductMappingIndex::class)->name('product-mappings.index');
    Route::get('/salesman-mappings', SalesmanMappingIndex::class)->name('salesman-mappings.index');
    Route::get('/mapping/unmapped-products', UnmappedProductIndex::class)->name('mapping.unmapped-products');
    Route::get('/mapping/unmapped-salesmans', UnmappedSalesmanIndex::class)->name('mapping.unmapped-salesmans');

    // ==========================================
    // ESKALINK: CUSTOMER & PRODUCT
    // ==========================================
    Route::get('/customer-eska', CustomerEskaIndex::class)->name('customer-eska.index');
    Route::get('/customer-eska-dist', CustomerEskaDistIndex::class)->name('customer-eska-dist.index');
    Route::get('/customer-eska-map', CustomerEskaMapIndex::class)->name('customer-eska-map.index');
    Route::get('/customer-eska-unmap', CustomerEskaUnmapIndex::class)->name('customer-eska-unmap.index');
    Route::get('/produk-eska', ProdukEskaIndex::class)->name('produk-eska.index');
    Route::get('/produk-eska-map', ProdukEskaMapIndex::class)->name('produk-eska-map.index');

    // ==========================================
    // CUSTOMER EXPORTS & DATA
    // ==========================================
    Route::get('/customers/data', CustomerData::class)->name('customers.data');
    Route::get('/customer-export', CustomerExportComponent::class)->name('customer.export');
    Route::get('/customer-csv-export', \App\Livewire\Eskalink\CustomerCsvExport\Index::class)->name('customer.csv.export');

    // ==========================================
    // CALL PLAN
    // ==========================================
    Route::post('/call-plan/batas-wilayah-geojson', function (\Illuminate\Http\Request $request) {
        $kecamatans = $request->input('kecamatans', []);
        if (empty($kecamatans)) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []]);
        }
        
        $placeholders = implode(',', array_fill(0, count($kecamatans), '?'));
        
        $sql = "
            SELECT wadmkc, wadmkd, ST_AsGeoJSON(ST_SimplifyPreserveTopology(geom, 0.0005)) as geojson
            FROM batas_wilayah
            WHERE wadmkc IN ($placeholders)
        ";
        
        $results = \Illuminate\Support\Facades\DB::select($sql, $kecamatans);
        
        $features = [];
        foreach ($results as $row) {
            if ($row->geojson) {
                $features[] = [
                    'type' => 'Feature',
                    'geometry' => json_decode($row->geojson),
                    'properties' => [
                        'kecamatan' => $row->wadmkc,
                        'kelurahan' => $row->wadmkd,
                    ]
                ];
            }
        }
        
        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    })->name('call-plan.batas-wilayah-geojson');

    Route::get('/call-plan', CallPlanIndex::class)->name('call-plan.index');
    Route::get('/plan-call-team-elite/toko-pareto', ListTokoPareto::class)->name('plan-call-team-elite.toko-pareto');
    Route::get('/jks-team-elite', JksTeamEliteIndex::class)->name('jks-team-elite.index');
    Route::get('/jks-team-elite/print', [\App\Http\Controllers\JksTeamElitePrintController::class, 'index'])->name('jks-team-elite.print');
    Route::get('/call-plan/jks-team-elite/summary-kunjungan', SummaryKunjunganIndex::class)->name('call-plan.jks-team-elite.summary-kunjungan');
    Route::get('/call-plan/jks-team-elite/summary-visit-team-elite', SummaryVisitTeamEliteIndex::class)->name('call-plan.jks-team-elite.summary-visit-team-elite');
    Route::get('/call-plan/jks-team-elite/monitoring', MonitoringJKSTeamEliteIndex::class)->name('call-plan.jks-team-elite.monitoring');
    Route::get('/call-plan/jks-team-elite/monitoring-siso-vs-eska', MonitoringJksSisoVsEskaIndex::class)->name('call-plan.jks-team-elite.monitoring-siso-vs-eska');
    Route::get('/call-plan/jks-team-elite/master-customer', JksMasterCustomerIndex::class)->name('call-plan.jks-team-elite.master-customer');
    Route::get('/call-plan/jks-team-elite/monitoring-outlet-pareto', MonitoringOutletParetoIndex::class)->name('call-plan.jks-team-elite.monitoring-outlet-pareto');
    Route::get('/call-plan/jks-team-elite/master-customer/monitoring-customer-pareto', MonitoringCustomerParetoIndex::class)->name('call-plan.jks-team-elite.master-customer.monitoring-customer-pareto');
    Route::get('/call-plan/jks-team-elite/route-efficiency', RouteEfficiencyIndex::class)->name('call-plan.jks-team-elite.route-efficiency');
    Route::get('/call-plan/jks-team-elite/clustering', ClusteringIndex::class)->name('call-plan.jks-team-elite.clustering');
    Route::get('/call-plan/management-cluster', \App\Livewire\CallPlan\ClusterManagement\Index::class)->name('call-plan.cluster-management');

    // ==========================================
    // SELLING IN & OUT
    // ==========================================
    Route::get('/selling-in', SellingInIndex::class)->name('selling-in.index');
    Route::get('/selling-in/import', SellingInImport::class)->name('selling-in.import');
    Route::get('/selling-out-eskalink', SellingOutEskalinkIndex::class)->name('selling-out-eskalink.index');
    Route::get('/sell-out/process', SellOutProcessIndex::class)->name('sell-out.process');
    Route::get('/sell-out/process-v2', SellOutProcessIndexV2::class)->name('sell-out.process-v2');
    Route::get('/sell-out/export', SellOutExportIndex::class)->name('sell-out.export');

    // ==========================================
    // SALES INVOICE & CONFIGURATION
    // ==========================================
    Route::get('/report/analisa-kunjungan', AnalisaKunjunganIndex::class)->name('report.analisa-kunjungan.index');
    Route::get('/sales-invoice-report', SalesInvoiceReportIndex::class)->name('sales-invoice-report.index');
    Route::get('/import-sales-invoices', SalesInvoiceImport::class)->name('sales-invoices.import');
    
    Route::prefix('sales-configs')->name('sales-configs.')->group(function () {
        Route::get('/', SalesConfigIndex::class)->name('index');
        Route::get('/create', SalesConfigCreate::class)->name('create');
        Route::get('/{encodedId}/edit', SalesConfigEdit::class)->name('edit');
    });

    // ==========================================
    // MISCELLANEOUS / OTHERS
    // ==========================================

    Route::get('/standarisasi', \App\Livewire\Others\Standarisasi\Index::class)->name('standarisasi.index');
    Route::get('/others/component-standard', ComponentStandardIndex::class)->name('others.component-standard');
    Route::get('/others/layout-standard', LayoutStandardIndex::class)->name('others.layout-standard');
    Route::get('/others/page-template-standard', PageTemplateStandardIndex::class)->name('others.page-template-standard');
    Route::get('/others/perbaikantikor', AdminPerbaikantikorIndex::class)->name('others.perbaikantikor');
    Route::get('/others/perbaikantikor-elite', AdminPerbaikantikorEliteIndex::class)->name('others.perbaikantikor.elite');
    Route::get('/others/audit-toko', AdminAuditTokoIndex::class)->name('others.audit-toko');
    Route::get('/others/audit-toko/master-customer', AdminAuditTokoMasterCustomer::class)->name('others.audit-toko.master');
    Route::get('/others/bug-report-request', \App\Livewire\Others\BugReportRequest\Index::class)->name('others.bug-report-request');
    Route::get('/others/target-per-se-untuk-eska', TargetPerSeUntukEskaIndex::class)->name('others.target-per-se-untuk-eska.index');
    Route::get('/mapping-supervisor-code', MappingSupervisorCodeIndex::class)->name('mapping-supervisor-code.index');
    Route::get('/qceskalink', QceskalinkIndex::class)->name('qceskalink.index');
    Route::get('/monitoring-device', MonitoringDeviceIndex::class)->name('monitoring-device.index');
    Route::get('/rwo', RwoIndex::class)->name('rwo.index');
    Route::get('/rwo/summary', \App\Livewire\Rwo\Summary::class)->name('rwo.summary');
    Route::get('/rwo/plan-kunjungan', \App\Livewire\Rwo\PlanKunjungan::class)->name('rwo.plan-kunjungan');
    Route::get('/rwo/dashboard', \App\Livewire\Rwo\DashboardRwo::class)->name('rwo.dashboard');
    Route::get('/rwo/summary-list-potensi', \App\Livewire\Rwo\SummaryListPotensi::class)->name('rwo.summarylistpotensi');
    Route::get('/rwo/listpotensirwo', \App\Livewire\Rwo\Listpotensirwo::class)->name('rwo.listpotensirwo');
    Route::get('/rwo/surat-kesepakatan-bersama', \App\Livewire\Rwo\SuratKesepakatanBersama::class)->name('rwo.surat-kesepakatan-bersama');
    Route::get('/rwo/pencapaian', \App\Livewire\Rwo\Pencapaianrwo::class)->name('rwo.pencapaian');
    Route::get('/rwo/monitoring-pareto', \App\Livewire\Rwo\MonitoringParetoRwo::class)->name('rwo.monitoring-pareto');
    Route::get('/under-construction', UnderConstruction::class)->name('under-construction');
    Route::get('/under-bounce', UnderBounce::class)->name('under-bounce');

});