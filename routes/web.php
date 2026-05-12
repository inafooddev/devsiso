<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\ConfigSalesInvoiceDistributorController;

// Livewire Components (Alphabetically ordered)
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\CallPlan\Index as CallPlanIndex;
use App\Livewire\CustomerCsvExportComponent;
use App\Livewire\CustomerEska\Index as CustomerEskaIndex;
use App\Livewire\CustomerEskaDist\Index as CustomerEskaDistIndex;
use App\Livewire\CustomerEskaMap\Index as CustomerEskaMapIndex;
use App\Livewire\CustomerEskaUnmap\Index as CustomerEskaUnmapIndex;
use App\Livewire\CustomerExportComponent;
use App\Livewire\Customers\CustomerData;
use App\Livewire\Dashboard;
use App\Livewire\Dashboard\AnalyticsDashboard;
use App\Livewire\Dashboard\NationalSellInDashboard;
use App\Livewire\Dashboard\DistributorMap;
use App\Livewire\Dashboard\MetabaseDashboard;
use App\Livewire\Dashboard\SalesComparison;
use App\Livewire\Geotagging\Reverse;
use App\Livewire\Mapping\Product\Index as ProductMappingIndex;
use App\Livewire\Mapping\Salesman\Index as SalesmanMappingIndex;
use App\Livewire\Mapping\UnmappedProduct\Index as UnmappedProductIndex;
use App\Livewire\Mapping\UnmappedSalesman\Index as UnmappedSalesmanIndex;
use App\Livewire\MasterAreas\Index as MasterAreaIndex;
use App\Livewire\MasterBranches\Index as MasterBranchIndex;
use App\Livewire\MasterDistributors\Index as MasterDistributorsIndex;
use App\Livewire\MasterRegions\Create as MasterRegionsCreate;
use App\Livewire\MasterRegions\Edit as MasterRegionsEdit;
use App\Livewire\MasterRegions\Index as MasterRegionsIndex;
use App\Livewire\MasterSupervisors\Index as MasterSupervisorIndex;
use App\Livewire\MenuManagement;
use App\Livewire\Pages\UnderBounce;
use App\Livewire\Pages\UnderConstruction;
use App\Livewire\PlanCallTeamElite\Import as PlanCallTeamEliteImportComponent;
use App\Livewire\PlanCallTeamElite\ListTokoPareto;
use App\Livewire\Product\BrandProduct\Index as ProductBrandIndex;
use App\Livewire\Product\CategoriesProduct\Index as CategoryIndex;
use App\Livewire\Product\GroupProduct\Index as ProductGroupIndex;
use App\Livewire\Product\LineProduct\Index as ProductLineIndex;
use App\Livewire\Product\MasterProduct\Index as ProductMasterIndex;
use App\Livewire\Product\ProductCategories\Index as ProductCategoryIndex;
use App\Livewire\Product\SubBrandProduct\Index as ProductSubBrandIndex;
use App\Livewire\Product\UnitMapping\Index as UnitMappingIndex;
use App\Livewire\ProdukEska\Index as ProdukEskaIndex;
use App\Livewire\ProdukEskaMap\Index as ProdukEskaMapIndex;
use App\Livewire\Profile;
use App\Livewire\RoleManagement;
use App\Livewire\SalesConfig\Create as SalesConfigCreate;
use App\Livewire\SalesConfig\Edit as SalesConfigEdit;
use App\Livewire\SalesConfig\Index as SalesConfigIndex;
use App\Livewire\SalesInvoiceImport;
use App\Livewire\SalesInvoiceReport\Index as SalesInvoiceReportIndex;
use App\Livewire\Salesmans\Create as SalesmanCreate;
use App\Livewire\Salesmans\Edit as SalesmanEdit;
use App\Livewire\Salesmans\Index as SalesmanIndex;
use App\Livewire\SellOut\Export\Index as SellOutExportIndex;
use App\Livewire\SellOut\Process\Index as SellOutProcessIndex;
use App\Livewire\SellOut\ProcessV2\Index as SellOutProcessIndexV2;
use App\Livewire\SellingIn\Index as SellingInIndex;
use App\Livewire\SellingIn\Report as SellingInReport;
use App\Livewire\SellingOutEskalink\Index as SellingOutEskalinkIndex;
use App\Livewire\UserManagement;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::middleware(['auth'])->group(function () {

    // ==========================================
    // AUTHENTICATION & PROFILE
    // ==========================================
    Route::post('/logout', function () {
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

    // ==========================================
    // DASHBOARD & ANALYTICS
    // ==========================================
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/dashboard/distributor-map', DistributorMap::class)->name('dashboard.distributor-map');
    Route::get('/dashboard/metabase', MetabaseDashboard::class)->name('dashboard.metabase');
    Route::get('/dashboard/analytics', AnalyticsDashboard::class)->name('dashboard.analytics');
    Route::get('/dashboard/national-sell-in', NationalSellInDashboard::class)->name('dashboard.national-sell-in');
    Route::get('/dashboard/sales-comparison', SalesComparison::class)->name('dashboard.sales-comparison');

    // ==========================================
    // MASTER DATA: DISTRIBUTION
    // ==========================================
    Route::get('/master-regions', MasterRegionsIndex::class)->name('master-regions.index')->middleware('menu.access');
    Route::get('/master-areas', MasterAreaIndex::class)->name('master-areas.index');
    Route::get('/master-supervisors', MasterSupervisorIndex::class)->name('master-supervisors.index');
    Route::get('/master-branches', MasterBranchIndex::class)->name('master-branches.index');
    Route::get('/master-distributors', MasterDistributorsIndex::class)->name('master-distributors.index');

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
    Route::get('/customer-csv-export', CustomerCsvExportComponent::class)->name('customer.csv.export');

    // ==========================================
    // CALL PLAN
    // ==========================================
    Route::get('/call-plan', CallPlanIndex::class)->name('call-plan.index');
    Route::get('/plan-call-team-elite/import', PlanCallTeamEliteImportComponent::class)->name('plan-call-team-elite.import');
    Route::get('/plan-call-team-elite/toko-pareto', ListTokoPareto::class)->name('plan-call-team-elite.toko-pareto');

    // ==========================================
    // SELLING IN & OUT
    // ==========================================
    Route::get('/selling-in', SellingInIndex::class)->name('selling-in.index');
    Route::get('/selling-in/report', SellingInReport::class)->name('selling-in.report');
    Route::get('/selling-out-eskalink', SellingOutEskalinkIndex::class)->name('selling-out-eskalink.index');
    Route::get('/sell-out/process', SellOutProcessIndex::class)->name('sell-out.process');
    Route::get('/sell-out/process-v2', SellOutProcessIndexV2::class)->name('sell-out.process-v2');
    Route::get('/sell-out/export', SellOutExportIndex::class)->name('sell-out.export');

    // ==========================================
    // SALES INVOICE & CONFIGURATION
    // ==========================================
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
    Route::get('/geotagging-reverse', Reverse::class)->name('geotagging.reverse');
    Route::get('/under-construction', UnderConstruction::class)->name('under-construction');
    Route::get('/under-bounce', UnderBounce::class)->name('under-bounce');

});