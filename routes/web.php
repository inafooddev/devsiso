<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Http\Controllers\ConfigSalesInvoiceDistributorController;
use App\Livewire\SalesConfig\Index as SalesConfigIndex;
use App\Livewire\SalesConfig\Create as SalesConfigCreate;
use App\Livewire\SalesConfig\Edit as SalesConfigEdit;
use App\Livewire\SalesInvoiceImport;
use App\Livewire\MasterRegions\Index as MasterRegionsIndex;
use App\Livewire\MasterRegions\Create as MasterRegionsCreate;
use App\Livewire\MasterRegions\Edit as MasterRegionsEdit;
use App\Livewire\MasterAreas\Index as MasterAreaIndex;  
use App\Livewire\MasterSupervisors\Index as MasterSupervisorIndex;
use App\Livewire\MasterBranches\Index as MasterBranchIndex;
use App\Livewire\MasterDistributors\Index as MasterDistributorsIndex;
use App\Livewire\SalesInvoiceReport\Index as SalesInvoiceReportIndex;
use App\Livewire\Product\LineProduct\Index as ProductLineIndex; 
use App\Livewire\Product\BrandProduct\Index as ProductBrandIndex;
use App\Livewire\Product\GroupProduct\Index as ProductGroupIndex;
use App\Livewire\Product\SubBrandProduct\Index as ProductSubBrandIndex;
use App\Livewire\Product\CategoriesProduct\Index as CategoryIndex;
use App\Livewire\Product\MasterProduct\Index as ProductMasterIndex;
use App\Livewire\Product\ProductCategories\Index as ProductCategoryIndex;
use App\Livewire\Mapping\Product\Index as ProductMappingIndex; // [DITAMBAHKAN]
use App\Livewire\Mapping\Product\Create as ProductMappingCreate; // [DITAMBAHKAN]
use App\Livewire\Mapping\Product\Edit as ProductMappingEdit; // [DITAMBAHKAN]
use App\Livewire\Salesmans\Index as SalesmanIndex; // [DITAMBAHKAN]
use App\Livewire\Salesmans\Create as SalesmanCreate; // [DITAMBAHKAN]
use App\Livewire\Salesmans\Edit as SalesmanEdit; // [DITAMBAHKAN]
use App\Livewire\Mapping\Salesman\Index as SalesmanMappingIndex; // [DITAMBAHKAN]
use App\Livewire\Mapping\Salesman\Create as SalesmanMappingCreate; // [DITAMBAHKAN]
use App\Livewire\Mapping\Salesman\Edit as SalesmanMappingEdit; // [DITAMBAHKAN]
use App\Livewire\Mapping\UnmappedProduct\Index as UnmappedProductIndex; // [DITAMBAHKAN]
use App\Livewire\Mapping\UnmappedSalesman\Index as UnmappedSalesmanIndex;
use App\Livewire\SellOut\Process\Index as SellOutProcessIndex; // [DITAMBAHKAN]
use App\Livewire\SellOut\Export\Index as SellOutExportIndex;
use App\Livewire\SellOut\ProcessV2\Index as SellOutProcessIndexV2;
use App\Livewire\Dashboard\DistributorMap;
use App\Livewire\Pages\UnderConstruction;
use App\Livewire\Pages\UnderBounce;
use App\Livewire\Dashboard\MetabaseDashboard;
use App\Livewire\Dashboard\AnalyticsDashboard;
use App\Livewire\CustomerExportComponent;
use App\Livewire\CustomerCsvExportComponent;
use App\Livewire\Customers\CustomerData;
use App\Livewire\CustomerEska\Index as CustomerEskaIndex;
use App\Livewire\CustomerEskaDist\Index as CustomerEskaDistIndex;
use App\Livewire\CustomerEskaMap\Index as CustomerEskaMapIndex;
use App\Livewire\CustomerEskaUnmap\Index as CustomerEskaUnmapIndex;
use App\Livewire\SellingOutEskalink\Index as SellingOutEskalinkIndex;
use App\Livewire\Dashboard\SalesComparison;
use App\Livewire\ProdukEska\Index as ProdukEskaIndex;
use App\Livewire\ProdukEskaMap\Index as ProdukEskaMapIndex;
use App\Livewire\SellingIn\Index as SellingInIndex;
use App\Livewire\CallPlan\Index as CallPlanIndex;
use App\Livewire\Geotagging\Reverse;
use App\Livewire\PlanCallTeamElite\Import as PlanCallTeamEliteImportComponent;
use App\Livewire\PlanCallTeamElite\ListTokoPareto;
use App\Livewire\SellingIn\Report as SellingInReport;
use App\Livewire\UserManagement;
use App\Livewire\RoleManagement;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Auth\Register;

// ...
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');

});



Route::middleware(['auth'])->group(function () {

    // Aksi Logout (Harus POST untuk keamanan CSRF)
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
    
    Route::get('/roles', RoleManagement::class)->name('roles.index');
    Route::get('/users', UserManagement::class)->name('users.index');
Route::get('/plan-call-team-elite/toko-pareto', ListTokoPareto::class)->name('plan-call-team-elite.toko-pareto');
Route::get('/plan-call-team-elite/import', PlanCallTeamEliteImportComponent::class)->name('plan-call-team-elite.import');
Route::get('/customers/data', CustomerData::class)->name('customers.data');
Route::get('/customer-eska', CustomerEskaIndex::class)->name('customer-eska.index');
Route::get('/customer-eska-dist', CustomerEskaDistIndex::class)->name('customer-eska-dist.index');
Route::get('/customer-eska-map', CustomerEskaMapIndex::class)->name('customer-eska-map.index');
Route::get('/customer-eska-unmap', CustomerEskaUnmapIndex::class)->name('customer-eska-unmap.index');
Route::get('/selling-out-eskalink', SellingOutEskalinkIndex::class)->name('selling-out-eskalink.index');
Route::get('/dashboard/sales-comparison', SalesComparison::class)->name('dashboard.sales-comparison');
Route::get('/produk-eska', ProdukEskaIndex::class)->name('produk-eska.index');
Route::get('/produk-eska-map', ProdukEskaMapIndex::class)->name('produk-eska-map.index');
Route::get('/selling-in', SellingInIndex::class)->name('selling-in.index');
Route::get('/call-plan', CallPlanIndex::class)->name('call-plan.index');
Route::get('/geotagging-reverse', Reverse::class)->name('geotagging.reverse');
Route::get('/selling-in/report', SellingInReport::class)->name('selling-in.report');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Customer Export Route
Route::get('/customer-export', CustomerExportComponent::class)->name('customer.export');
// Route Baru (CSV)
Route::get('/customer-csv-export', CustomerCsvExportComponent::class)->name('customer.csv.export');

// Arahkan ke komponen Livewire Dashboard
Route::get('/under-construction', UnderConstruction::class)->name('under-construction');
Route::get('/under-bounce', UnderBounce::class)->name('under-bounce');

Route::get('/', Dashboard::class)->name('dashboard');

Route::get('/dashboard/distributor-map', DistributorMap::class)->name('dashboard.distributor-map');
Route::get('/dashboard/metabase', MetabaseDashboard::class)->name('dashboard.metabase');
Route::get('/dashboard/analytics', AnalyticsDashboard::class)->name('dashboard.analytics');

// Grup route untuk konfigurasi sales
Route::prefix('sales-configs')->name('sales-configs.')->group(function () {
    Route::get('/', SalesConfigIndex::class)->name('index');
    Route::get('/create', SalesConfigCreate::class)->name('create');
    // PERUBAHAN: Menggunakan parameter biasa untuk ID yang dienkripsi
    Route::get('/{encodedId}/edit', SalesConfigEdit::class)->name('edit');
});

// Route baru untuk halaman impor
Route::get('/import-sales-invoices', SalesInvoiceImport::class)->name('sales-invoices.import');

// Route untuk Master Region
Route::get('/master-regions', MasterRegionsIndex::class)->name('master-regions.index');

// Master Areas Routes
Route::get('/master-areas', MasterAreaIndex::class)->name('master-areas.index');

// Master Supervisors Routes
Route::get('/master-supervisors', MasterSupervisorIndex::class)->name('master-supervisors.index');

// Master Branches Routes
Route::get('/master-branches', MasterBranchIndex::class)->name('master-branches.index');

// Master Distributors
Route::get('/master-distributors', MasterDistributorsIndex::class)->name('master-distributors.index');

// Route untuk Laporan Sales Invoice
Route::get('/sales-invoice-report', SalesInvoiceReportIndex::class)->name('sales-invoice-report.index');

// Product Line Routes
Route::get('/product-lines', ProductLineIndex::class)->name('product-lines.index');

// Product Brand Routes
Route::get('/product-brands', ProductBrandIndex::class)->name('product-brands.index');

// Product Group Routes
Route::get('/product-groups', ProductGroupIndex::class)->name('product-groups.index');

// Product Sub-Brand Routes
Route::get('/product-sub-brands', ProductSubBrandIndex::class)->name('product-sub-brands.index');

// Product Categories Routes
Route::get('/categories', CategoryIndex::class)->name('categories.index');

// Product Master Routes
Route::get('/product-masters', ProductMasterIndex::class)->name('product-masters.index');

// Product Categories Mapping Routes
Route::get('/product-categories', ProductCategoryIndex::class)->name('product-categories.index');


// Product Mappings Routes
Route::prefix('product-mappings')->name('product-mappings.')->group(function () {
    Route::get('/', ProductMappingIndex::class)->name('index');
    Route::get('/create', ProductMappingCreate::class)->name('create');
    Route::get('/{id}/edit', ProductMappingEdit::class)->name('edit'); 
});

// Route untuk Salesmans
Route::get('/salesmans', SalesmanIndex::class)->name('salesmans.index');

//Route untuk Salesman Mappings
Route::prefix('salesman-mappings')->name('salesman-mappings.')->group(function () {
    Route::get('/', SalesmanMappingIndex::class)->name('index');
    Route::get('/create', SalesmanMappingCreate::class)->name('create');
    Route::get('/{id}/edit', SalesmanMappingEdit::class)->name('edit'); // {id} akan di-hash
});

// [DITAMBAHKAN] Route untuk Laporan Produk Belum Terpetakan
Route::get('/mapping/unmapped-products', UnmappedProductIndex::class)->name('mapping.unmapped-products');
// [DITAMBAHKAN] Route untuk Laporan Salesman Belum Terpetakan
Route::get('/mapping/unmapped-salesmans', UnmappedSalesmanIndex::class)->name('mapping.unmapped-salesmans');

Route::get('/sell-out/process', SellOutProcessIndex::class)->name('sell-out.process');
Route::get('/sell-out/export', SellOutExportIndex::class)->name('sell-out.export');
Route::get('/sell-out/process-v2', SellOutProcessIndexV2::class)->name('sell-out.process-v2');
});