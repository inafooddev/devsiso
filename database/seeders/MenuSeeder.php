<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::statement('TRUNCATE menus RESTART IDENTITY CASCADE;');

        // 1. Dashboard
        $dashboard = Menu::create(['name' => 'Dashboard', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z"/></svg>', 'order_number' => 1]);
        Menu::create(['name' => 'Selling In Summary', 'route' => 'dashboard.analytics', 'parent_id' => $dashboard->id, 'order_number' => 1]);
        Menu::create(['name' => 'National Sell In', 'route' => 'dashboard.national-sell-in', 'parent_id' => $dashboard->id, 'order_number' => 2]);
        Menu::create(['name' => 'Area Sell In', 'route' => 'dashboard.area-sell-in', 'parent_id' => $dashboard->id, 'order_number' => 3]);
        Menu::create(['name' => 'Cabang Sell In', 'route' => 'dashboard.cabang-sell-in', 'parent_id' => $dashboard->id, 'order_number' => 4]);
        Menu::create(['name' => 'Supervisor Sell In', 'route' => 'dashboard.supervisor-sell-in', 'parent_id' => $dashboard->id, 'order_number' => 5]);
        Menu::create(['name' => 'Titik Distribusi', 'route' => 'dashboard.metabase', 'parent_id' => $dashboard->id, 'order_number' => 6]);

        // 2. Selling In
        Menu::create(['name' => 'Selling In', 'route' => 'selling-in.report', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M3.375 3C2.339 3 1.5 3.84 1.5 4.875v.75c0 1.036.84 1.875 1.875 1.875h17.25c1.035 0 1.875-.84 1.875-1.875v-.75C22.5 3.839 21.66 3 20.625 3H3.375Z" /><path fill-rule="evenodd" d="m3.087 9 .54 9.176A3 3 0 0 0 6.62 21h10.757a3 3 0 0 0 2.995-2.824L20.913 9H3.087Zm6.163 3.75A.75.75 0 0 1 10 12h4a.75.75 0 0 1 0 1.5h-4a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" /></svg>', 'order_number' => 2]);

        // 3. Selling Out
        $sellingOut = Menu::create(['name' => 'Selling Out', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M2.25 2.25a.75.75 0 0 0 0 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 0 0-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 0 0 0-1.5H5.378A2.25 2.25 0 0 1 7.5 15h11.218a.75.75 0 0 0 .674-.421 60.358 60.358 0 0 0 2.96-7.228.75.75 0 0 0-.525-.965A60.864 60.864 0 0 0 5.68 4.509l-.232-.867A1.875 1.875 0 0 0 3.636 2.25H2.25ZM3.75 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0ZM16.5 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" /></svg>', 'order_number' => 3]);
        Menu::create(['name' => 'Import', 'route' => 'sales-invoice-report.index', 'parent_id' => $sellingOut->id, 'order_number' => 1]);
        $generate = Menu::create(['name' => 'Generate', 'parent_id' => $sellingOut->id, 'order_number' => 2]);
        Menu::create(['name' => 'Eska Version', 'route' => 'sell-out.process', 'parent_id' => $generate->id, 'order_number' => 1]);
        Menu::create(['name' => 'Default Version', 'route' => 'sell-out.process-v2', 'parent_id' => $generate->id, 'order_number' => 2]);

        // 4. Master Data
        $masterData = Menu::create(['name' => 'Master Data', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M21 6.375c0 2.692-4.03 4.875-9 4.875S3 9.067 3 6.375 7.03 1.5 12 1.5s9 2.183 9 4.875Z" /><path d="M12 12.75c2.685 0 5.19-.586 7.078-1.609a8.283 8.283 0 0 0 1.897-1.384c.016.121.025.244.025.368C21 12.817 16.97 15 12 15s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.285 8.285 0 0 0 1.897 1.384C6.809 12.164 9.315 12.75 12 12.75Z" /><path d="M12 16.5c2.685 0 5.19-.586 7.078-1.609a8.282 8.282 0 0 0 1.897-1.384c.016.121.025.244.025.368 0 2.692-4.03 4.875-9 4.875s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.284 8.284 0 0 0 1.897 1.384C6.809 15.914 9.315 16.5 12 16.5Z" /><path d="M12 20.25c2.685 0 5.19-.586 7.078-1.609a8.282 8.282 0 0 0 1.897-1.384c.016.121.025.244.025.368 0 2.692-4.03 4.875-9 4.875s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.284 8.284 0 0 0 1.897 1.384C6.809 19.914 9.315 20.25 12 20.25Z" /></svg>', 'order_number' => 4]);
        
        $master = Menu::create(['name' => 'Master', 'parent_id' => $masterData->id, 'order_number' => 1]);
        $distribusi = Menu::create(['name' => 'Distribusi', 'parent_id' => $master->id, 'order_number' => 1]);
        Menu::create(['name' => 'Region', 'route' => 'master-regions.index', 'parent_id' => $distribusi->id, 'order_number' => 1]);
        Menu::create(['name' => 'Area', 'route' => 'master-areas.index', 'parent_id' => $distribusi->id, 'order_number' => 2]);
        Menu::create(['name' => 'Supervisor', 'route' => 'master-supervisors.index', 'parent_id' => $distribusi->id, 'order_number' => 3]);
        Menu::create(['name' => 'Cabang', 'route' => 'master-branches.index', 'parent_id' => $distribusi->id, 'order_number' => 4]);
        Menu::create(['name' => 'Distributor', 'route' => 'master-distributors.index', 'parent_id' => $distribusi->id, 'order_number' => 5]);
        
        $product = Menu::create(['name' => 'Product', 'parent_id' => $master->id, 'order_number' => 2]);
        Menu::create(['name' => 'Line', 'route' => 'product-lines.index', 'parent_id' => $product->id, 'order_number' => 1]);
        Menu::create(['name' => 'Brand', 'route' => 'product-brands.index', 'parent_id' => $product->id, 'order_number' => 2]);
        Menu::create(['name' => 'Grup', 'route' => 'product-groups.index', 'parent_id' => $product->id, 'order_number' => 3]);
        Menu::create(['name' => 'Sub Brand', 'route' => 'product-sub-brands.index', 'parent_id' => $product->id, 'order_number' => 4]);
        Menu::create(['name' => 'Product Master', 'route' => 'product-masters.index', 'parent_id' => $product->id, 'order_number' => 5]);
        Menu::create(['name' => 'Kategori', 'route' => 'categories.index', 'parent_id' => $product->id, 'order_number' => 6]);
        Menu::create(['name' => 'Product Kategori', 'route' => 'product-categories.index', 'parent_id' => $product->id, 'order_number' => 7]);
        Menu::create(['name' => 'Unit Mapping', 'route' => 'product-unit-mappings.index', 'parent_id' => $product->id, 'order_number' => 8]);
        
        Menu::create(['name' => 'Salesmen', 'route' => 'salesmans.index', 'parent_id' => $master->id, 'order_number' => 3]);

        $mapping = Menu::create(['name' => 'Mapping', 'parent_id' => $masterData->id, 'order_number' => 2]);
        Menu::create(['name' => 'Product', 'route' => 'product-mappings.index', 'parent_id' => $mapping->id, 'order_number' => 1]);
        Menu::create(['name' => 'Salesmen', 'route' => 'salesman-mappings.index', 'parent_id' => $mapping->id, 'order_number' => 2]);

        $unmapping = Menu::create(['name' => 'Unmapping', 'parent_id' => $masterData->id, 'order_number' => 3]);
        Menu::create(['name' => 'Product', 'route' => 'mapping.unmapped-products', 'parent_id' => $unmapping->id, 'order_number' => 1]);
        Menu::create(['name' => 'Salesmen', 'route' => 'mapping.unmapped-salesmans', 'parent_id' => $unmapping->id, 'order_number' => 2]);

        // 5. Eskalink
        $eskalink = Menu::create(['name' => 'Eskalink', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M21.721 12.752a9.711 9.711 0 0 0-.945-5.003 12.754 12.754 0 0 1-4.339 2.708 18.991 18.991 0 0 1-.214 4.772 17.165 17.165 0 0 0 5.498-2.477ZM14.634 15.55a17.324 17.324 0 0 0 .332-4.647c-.952.227-1.945.347-2.966.347-1.021 0-2.014-.12-2.966-.347a17.515 17.515 0 0 0 .332 4.647 17.385 17.385 0 0 0 5.268 0ZM9.772 17.119a18.963 18.963 0 0 0 4.456 0A17.182 17.182 0 0 1 12 21.724a17.18 17.18 0 0 1-2.228-4.605ZM7.777 15.23a18.87 18.87 0 0 1-.214-4.774 12.753 12.753 0 0 1-4.34-2.708 9.711 9.711 0 0 0-.944 5.004 17.165 17.165 0 0 0 5.498 2.477ZM21.356 14.752a9.765 9.765 0 0 1-7.478 6.817 18.64 18.64 0 0 0 1.988-4.718 18.627 18.627 0 0 0 5.49-2.098ZM2.644 14.752c1.682.971 3.53 1.688 5.49 2.099a18.64 18.64 0 0 0 1.988 4.718 9.765 9.765 0 0 1-7.478-6.816ZM13.878 2.43a9.755 9.755 0 0 1 6.116 3.986 11.267 11.267 0 0 1-3.746 2.504 18.63 18.63 0 0 0-2.37-6.49ZM12 2.276a17.152 17.152 0 0 1 2.805 7.121c-.897.23-1.837.353-2.805.353-.968 0-1.908-.122-2.805-.353A17.151 17.151 0 0 1 12 2.276ZM10.122 2.43a18.629 18.629 0 0 0-2.37 6.49 11.266 11.266 0 0 1-3.746-2.504 9.754 9.754 0 0 1 6.116-3.985Z" /></svg>', 'order_number' => 5]);
        $customerEska = Menu::create(['name' => 'Customer', 'parent_id' => $eskalink->id, 'order_number' => 1]);
        Menu::create(['name' => 'Principal', 'route' => 'customer-eska.index', 'parent_id' => $customerEska->id, 'order_number' => 1]);
        Menu::create(['name' => 'Distributor', 'route' => 'customer-eska-dist.index', 'parent_id' => $customerEska->id, 'order_number' => 2]);
        Menu::create(['name' => 'Mapping', 'route' => 'customer-eska-map.index', 'parent_id' => $customerEska->id, 'order_number' => 3]);
        
        $unmapEska = Menu::create(['name' => 'Unmapping', 'parent_id' => $customerEska->id, 'order_number' => 4]);
        Menu::create(['name' => 'Principal', 'route' => 'customer.csv.export', 'parent_id' => $unmapEska->id, 'order_number' => 1]);
        Menu::create(['name' => 'Distributor', 'route' => 'customer-eska-unmap.index', 'parent_id' => $unmapEska->id, 'order_number' => 2]);
        
        $produkEska = Menu::create(['name' => 'Produk', 'parent_id' => $eskalink->id, 'order_number' => 2]);
        Menu::create(['name' => 'Master Produk', 'route' => 'produk-eska.index', 'parent_id' => $produkEska->id, 'order_number' => 1]);
        Menu::create(['name' => 'Mapping Produk', 'route' => 'produk-eska-map.index', 'parent_id' => $produkEska->id, 'order_number' => 2]);
        
        Menu::create(['name' => 'Comparasi SO Eska', 'route' => 'dashboard.sales-comparison', 'parent_id' => $eskalink->id, 'order_number' => 3]);

        // 6. Call Plan
        $callPlan = Menu::create(['name' => 'Call Plan', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M8.161 2.58a1.875 1.875 0 0 1 1.678 0l4.993 2.498c.106.052.23.052.336 0l3.869-1.935A1.875 1.875 0 0 1 21.75 4.82v12.485c0 .71-.401 1.36-1.037 1.677l-4.875 2.437a1.875 1.875 0 0 1-1.676 0l-4.994-2.497a.375.375 0 0 0-.336 0l-3.868 1.935A1.875 1.875 0 0 1 2.25 19.18V6.695c0-.71.401-1.36 1.036-1.677l4.875-2.437ZM9 6a.75.75 0 0 1 .75.75v9.5a.75.75 0 0 1-1.5 0v-9.5A.75.75 0 0 1 9 6Zm6.75 2.75a.75.75 0 0 0-1.5 0v9.5a.75.75 0 0 0 1.5 0v-9.5Z" clip-rule="evenodd" /></svg>', 'order_number' => 6]);
        Menu::create(['name' => 'JKS', 'route' => 'call-plan.index', 'parent_id' => $callPlan->id, 'order_number' => 1]);
        Menu::create(['name' => 'Import', 'route' => 'plan-call-team-elite.import', 'parent_id' => $callPlan->id, 'order_number' => 2]);
        Menu::create(['name' => 'List Toko Pareto', 'route' => 'plan-call-team-elite.toko-pareto', 'parent_id' => $callPlan->id, 'order_number' => 3]);

        // 7. Jobs (External Links)
        $jobs = Menu::create(['name' => 'Jobs', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M7.5 5.25a3 3 0 0 1 3-3h3a3 3 0 0 1 3 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033c0 1.211-.734 2.352-1.936 2.752A24.726 24.726 0 0 1 12 15.75c-2.73 0-5.357-.442-7.814-1.259-1.202-.4-1.936-1.541-1.936-2.752V8.706c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 0 1 7.5 5.455V5.25Zm7.5 0v.09a49.488 49.488 0 0 0-6 0v-.09a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5Zm-3 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" /><path d="M3 18.4v-2.796a4.3 4.3 0 0 0 .713.31A26.226 26.226 0 0 0 12 17.25c2.892 0 5.68-.468 8.287-1.335.252-.084.49-.189.713-.311V18.4c0 1.452-1.047 2.728-2.523 2.923-2.12.282-4.282.427-6.477.427a49.19 49.19 0 0 1-6.477-.427C4.047 21.128 3 19.852 3 18.4Z" /></svg>', 'order_number' => 7]);
        Menu::create(['name' => 'Eskalink', 'route' => 'https://sfa.asiatop.co.id/#/', 'parent_id' => $jobs->id, 'order_number' => 1]);
        Menu::create(['name' => '3013', 'route' => 'https://jobs.asiatop.co.id:3013/#Schedule', 'parent_id' => $jobs->id, 'order_number' => 2]);
        Menu::create(['name' => '3012', 'route' => 'https://sfa.asiatop.co.id:3012/#Login', 'parent_id' => $jobs->id, 'order_number' => 3]);
        Menu::create(['name' => 'SISO', 'route' => 'http://192.168.1.92:3012/#Schedule', 'parent_id' => $jobs->id, 'order_number' => 4]);

        // Attach all menus to User with ID 1 (or the first user found)
        $user = User::first();
        if ($user) {
            $user->menus()->sync(Menu::pluck('id')->toArray());
        }
    }
}
