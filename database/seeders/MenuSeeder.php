<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('menus')->delete();
        
        \DB::table('menus')->insert(array (
            0 => 
            array (
                'id' => 10,
                'name' => 'Import',
                'route' => 'sales-invoice-report.index',
                'icon' => NULL,
                'parent_id' => 9,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            1 => 
            array (
                'id' => 11,
                'name' => 'Generate',
                'route' => NULL,
                'icon' => NULL,
                'parent_id' => 9,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            2 => 
            array (
                'id' => 12,
                'name' => 'Eska Version',
                'route' => 'sell-out.process',
                'icon' => NULL,
                'parent_id' => 11,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            3 => 
            array (
                'id' => 13,
                'name' => 'Default Version',
                'route' => 'sell-out.process-v2',
                'icon' => NULL,
                'parent_id' => 11,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            4 => 
            array (
                'id' => 15,
                'name' => 'Master',
                'route' => NULL,
                'icon' => NULL,
                'parent_id' => 14,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            5 => 
            array (
                'id' => 16,
                'name' => 'Distribusi',
                'route' => NULL,
                'icon' => NULL,
                'parent_id' => 15,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            6 => 
            array (
                'id' => 17,
                'name' => 'Region',
                'route' => 'master-regions.index',
                'icon' => NULL,
                'parent_id' => 16,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            7 => 
            array (
                'id' => 18,
                'name' => 'Area',
                'route' => 'master-areas.index',
                'icon' => NULL,
                'parent_id' => 16,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            8 => 
            array (
                'id' => 19,
                'name' => 'Supervisor',
                'route' => 'master-supervisors.index',
                'icon' => NULL,
                'parent_id' => 16,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            9 => 
            array (
                'id' => 20,
                'name' => 'Cabang',
                'route' => 'master-branches.index',
                'icon' => NULL,
                'parent_id' => 16,
                'order_number' => 4,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            10 => 
            array (
                'id' => 21,
                'name' => 'Distributor',
                'route' => 'master-distributors.index',
                'icon' => NULL,
                'parent_id' => 16,
                'order_number' => 5,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            11 => 
            array (
                'id' => 22,
                'name' => 'Product',
                'route' => NULL,
                'icon' => NULL,
                'parent_id' => 15,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            12 => 
            array (
                'id' => 23,
                'name' => 'Line',
                'route' => 'product-lines.index',
                'icon' => NULL,
                'parent_id' => 22,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            13 => 
            array (
                'id' => 24,
                'name' => 'Brand',
                'route' => 'product-brands.index',
                'icon' => NULL,
                'parent_id' => 22,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            14 => 
            array (
                'id' => 25,
                'name' => 'Grup',
                'route' => 'product-groups.index',
                'icon' => NULL,
                'parent_id' => 22,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            15 => 
            array (
                'id' => 26,
                'name' => 'Sub Brand',
                'route' => 'product-sub-brands.index',
                'icon' => NULL,
                'parent_id' => 22,
                'order_number' => 4,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            16 => 
            array (
                'id' => 27,
                'name' => 'Product Master',
                'route' => 'product-masters.index',
                'icon' => NULL,
                'parent_id' => 22,
                'order_number' => 5,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            17 => 
            array (
                'id' => 28,
                'name' => 'Kategori',
                'route' => 'categories.index',
                'icon' => NULL,
                'parent_id' => 22,
                'order_number' => 6,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            18 => 
            array (
                'id' => 29,
                'name' => 'Product Kategori',
                'route' => 'product-categories.index',
                'icon' => NULL,
                'parent_id' => 22,
                'order_number' => 7,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            19 => 
            array (
                'id' => 30,
                'name' => 'Unit Mapping',
                'route' => 'product-unit-mappings.index',
                'icon' => NULL,
                'parent_id' => 22,
                'order_number' => 8,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            20 => 
            array (
                'id' => 31,
                'name' => 'Salesmen',
                'route' => 'salesmans.index',
                'icon' => NULL,
                'parent_id' => 15,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            21 => 
            array (
                'id' => 32,
                'name' => 'Mapping',
                'route' => NULL,
                'icon' => NULL,
                'parent_id' => 14,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            22 => 
            array (
                'id' => 33,
                'name' => 'Product',
                'route' => 'product-mappings.index',
                'icon' => NULL,
                'parent_id' => 32,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            23 => 
            array (
                'id' => 34,
                'name' => 'Salesmen',
                'route' => 'salesman-mappings.index',
                'icon' => NULL,
                'parent_id' => 32,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            24 => 
            array (
                'id' => 35,
                'name' => 'Unmapping',
                'route' => NULL,
                'icon' => NULL,
                'parent_id' => 14,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            25 => 
            array (
                'id' => 36,
                'name' => 'Product',
                'route' => 'mapping.unmapped-products',
                'icon' => NULL,
                'parent_id' => 35,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            26 => 
            array (
                'id' => 37,
                'name' => 'Salesmen',
                'route' => 'mapping.unmapped-salesmans',
                'icon' => NULL,
                'parent_id' => 35,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            27 => 
            array (
                'id' => 39,
                'name' => 'Customer',
                'route' => NULL,
                'icon' => NULL,
                'parent_id' => 38,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            28 => 
            array (
                'id' => 40,
                'name' => 'Principal',
                'route' => 'customer-eska.index',
                'icon' => NULL,
                'parent_id' => 39,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            29 => 
            array (
                'id' => 41,
                'name' => 'Distributor',
                'route' => 'customer-eska-dist.index',
                'icon' => NULL,
                'parent_id' => 39,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            30 => 
            array (
                'id' => 42,
                'name' => 'Mapping',
                'route' => 'customer-eska-map.index',
                'icon' => NULL,
                'parent_id' => 39,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            31 => 
            array (
                'id' => 43,
                'name' => 'Unmapping',
                'route' => NULL,
                'icon' => NULL,
                'parent_id' => 39,
                'order_number' => 4,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            32 => 
            array (
                'id' => 44,
                'name' => 'Principal',
                'route' => 'customer.csv.export',
                'icon' => NULL,
                'parent_id' => 43,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            33 => 
            array (
                'id' => 45,
                'name' => 'Distributor',
                'route' => 'customer-eska-unmap.index',
                'icon' => NULL,
                'parent_id' => 43,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            34 => 
            array (
                'id' => 9,
                'name' => 'Selling Out',
                'route' => NULL,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <polygon fill="var(--ci-primary-color, currentColor)" points="160 96.039 160 128.039 464 128.039 464 191.384 428.5 304.039 149.932 304.039 109.932 16 16 16 16 48 82.068 48 122.068 336.039 451.968 336.039 496 196.306 496 96.039 160 96.039" class="ci-primary"/>
  <path fill="var(--ci-primary-color, currentColor)" d="M176.984,368.344a64.073,64.073,0,0,0-64,64h0a64,64,0,0,0,128,0h0A64.072,64.072,0,0,0,176.984,368.344Zm0,96a32,32,0,1,1,32-32A32.038,32.038,0,0,1,176.984,464.344Z" class="ci-primary"/>
  <path fill="var(--ci-primary-color, currentColor)" d="M400.984,368.344a64.073,64.073,0,0,0-64,64h0a64,64,0,0,0,128,0h0A64.072,64.072,0,0,0,400.984,368.344Zm0,96a32,32,0,1,1,32-32A32.038,32.038,0,0,1,400.984,464.344Z" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 6,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-08-04 14:15:42',
            ),
            35 => 
            array (
                'id' => 38,
                'name' => 'Eskalink',
                'route' => NULL,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M457.47,55.833c-53.026-53.026-139.307-53.026-192.332,0L168.971,152,191.6,174.627,287.765,78.46A104,104,0,0,1,434.843,225.539l-96.167,96.167L361.3,344.333l96.167-96.167C510.5,195.14,510.5,108.86,457.47,55.833Z" class="ci-primary"/>
  <path fill="var(--ci-primary-color, currentColor)" d="M225.539,434.843a104,104,0,0,1-147.078,0h0a104,104,0,0,1,0-147.078l90.511-90.511-22.627-22.627L55.833,265.138A136,136,0,1,0,248.166,457.47l90.51-90.51-22.627-22.627Z" class="ci-primary"/>
  <rect width="320" height="32" x="93.824" y="243.48" fill="var(--ci-primary-color, currentColor)" class="ci-primary" transform="rotate(-45 253.823 259.48)"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 9,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-08-04 14:19:10',
            ),
            36 => 
            array (
                'id' => 14,
                'name' => 'Master Data',
                'route' => NULL,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M16,48V464H496V48ZM464,432H48V336H464Zm0-128H48V208H464ZM48,176V80H464v96Z" class="ci-primary"/>
  <rect width="32" height="32" x="80" y="112" fill="var(--ci-primary-color, currentColor)" class="ci-primary"/>
  <rect width="32" height="32" x="80" y="240" fill="var(--ci-primary-color, currentColor)" class="ci-primary"/>
  <rect width="32" height="32" x="80" y="368" fill="var(--ci-primary-color, currentColor)" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 8,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-08-04 14:19:01',
            ),
            37 => 
            array (
                'id' => 46,
                'name' => 'Produk',
                'route' => NULL,
                'icon' => NULL,
                'parent_id' => 38,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            38 => 
            array (
                'id' => 47,
                'name' => 'Master Produk',
                'route' => 'produk-eska.index',
                'icon' => NULL,
                'parent_id' => 46,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            39 => 
            array (
                'id' => 48,
                'name' => 'Mapping Produk',
                'route' => 'produk-eska-map.index',
                'icon' => NULL,
                'parent_id' => 46,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            40 => 
            array (
                'id' => 49,
                'name' => 'Comparasi SO Eska',
                'route' => 'dashboard.sales-comparison',
                'icon' => NULL,
                'parent_id' => 38,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            41 => 
            array (
                'id' => 55,
                'name' => 'Eskalink',
                'route' => 'https://sfa.asiatop.co.id/#/',
                'icon' => NULL,
                'parent_id' => 54,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            42 => 
            array (
                'id' => 56,
                'name' => '3013',
                'route' => 'https://jobs.asiatop.co.id:3013/#Schedule',
                'icon' => NULL,
                'parent_id' => 54,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            43 => 
            array (
                'id' => 57,
                'name' => '3012',
                'route' => 'https://sfa.asiatop.co.id:3012/#Login',
                'icon' => NULL,
                'parent_id' => 54,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            44 => 
            array (
                'id' => 58,
                'name' => 'SISO',
                'route' => 'http://192.168.1.92:3012/#Schedule',
                'icon' => NULL,
                'parent_id' => 54,
                'order_number' => 4,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-05-12 13:28:21',
            ),
            45 => 
            array (
                'id' => 94,
                'name' => 'Master Customer',
                'route' => 'rwo.index',
                'icon' => '',
                'parent_id' => 62,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-07-02 18:16:10',
                'updated_at' => '2026-07-02 18:16:10',
            ),
            46 => 
            array (
                'id' => 65,
                'name' => 'dist imp eska',
                'route' => 'mapping-distributor-implementasi-eskalink.index',
                'icon' => '',
                'parent_id' => 16,
                'order_number' => 6,
                'is_active' => true,
                'created_at' => '2026-05-26 10:50:47',
                'updated_at' => '2026-05-26 10:50:47',
            ),
            47 => 
            array (
                'id' => 67,
                'name' => 'JKS Ipul',
                'route' => 'http://192.168.1.92:8789/login.php',
                'icon' => '',
                'parent_id' => 54,
                'order_number' => 5,
                'is_active' => true,
                'created_at' => '2026-05-29 14:39:48',
                'updated_at' => '2026-05-29 14:39:48',
            ),
            48 => 
            array (
                'id' => 78,
                'name' => 'Summary Visit Team Elite',
                'route' => 'call-plan.jks-team-elite.summary-visit-team-elite',
                'icon' => '',
                'parent_id' => 76,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-06-08 13:43:09',
                'updated_at' => '2026-06-08 13:43:21',
            ),
            49 => 
            array (
                'id' => 79,
                'name' => 'Monitoring Visit Pareto',
                'route' => 'call-plan.jks-team-elite.monitoring-outlet-pareto',
                'icon' => '',
                'parent_id' => 76,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-06-08 13:43:55',
                'updated_at' => '2026-06-08 13:50:26',
            ),
            50 => 
            array (
                'id' => 51,
                'name' => 'Maps',
                'route' => 'call-plan.index',
                'icon' => NULL,
                'parent_id' => 50,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-06-03 09:35:32',
            ),
            51 => 
            array (
                'id' => 74,
                'name' => 'Master Outlet',
                'route' => 'call-plan.jks-team-elite.master-customer',
                'icon' => '',
                'parent_id' => 50,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-06-05 15:15:54',
                'updated_at' => '2026-06-10 14:50:03',
            ),
            52 => 
            array (
                'id' => 84,
                'name' => 'QC Eskalink',
                'route' => 'qceskalink.index',
                'icon' => '',
                'parent_id' => 71,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-06-12 10:13:31',
                'updated_at' => '2026-06-12 10:13:31',
            ),
            53 => 
            array (
                'id' => 98,
                'name' => 'Clustering',
                'route' => 'call-plan.cluster-management',
                'icon' => '',
                'parent_id' => 50,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-07-31 14:37:15',
                'updated_at' => '2026-07-31 14:37:15',
            ),
            54 => 
            array (
                'id' => 72,
                'name' => 'Mapping Team Elite',
                'route' => 'mapping-supervisor-code.index',
                'icon' => '',
                'parent_id' => 71,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-06-03 11:36:51',
                'updated_at' => '2026-06-03 11:36:51',
            ),
            55 => 
            array (
                'id' => 100,
                'name' => 'COMPILE',
                'route' => '',
                'icon' => '',
                'parent_id' => NULL,
                'order_number' => 4,
                'is_active' => true,
                'created_at' => '2026-08-04 14:15:11',
                'updated_at' => '2026-08-04 14:15:11',
            ),
            56 => 
            array (
                'id' => 86,
                'name' => 'Analisa Kunjungan TE',
                'route' => 'report.analisa-kunjungan.index',
                'icon' => '',
                'parent_id' => 76,
                'order_number' => 6,
                'is_active' => true,
                'created_at' => '2026-06-18 15:34:11',
                'updated_at' => '2026-06-18 15:35:08',
            ),
            57 => 
            array (
                'id' => 87,
                'name' => 'Perbaikan Tikor',
                'route' => 'others.perbaikantikor',
                'icon' => '',
                'parent_id' => 71,
                'order_number' => 5,
                'is_active' => true,
                'created_at' => '2026-06-24 16:00:42',
                'updated_at' => '2026-06-24 16:00:50',
            ),
            58 => 
            array (
                'id' => 82,
                'name' => 'Monitoring Device SE',
                'route' => 'monitoring-device.index',
                'icon' => '',
                'parent_id' => 71,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-06-11 14:38:28',
                'updated_at' => '2026-06-24 16:01:04',
            ),
            59 => 
            array (
                'id' => 85,
                'name' => 'API Management',
                'route' => 'api-management.index',
                'icon' => '',
                'parent_id' => 71,
                'order_number' => 4,
                'is_active' => true,
                'created_at' => '2026-06-17 11:45:46',
                'updated_at' => '2026-06-24 16:01:14',
            ),
            60 => 
            array (
                'id' => 88,
                'name' => 'Mobile App',
                'route' => '',
                'icon' => '',
                'parent_id' => 54,
                'order_number' => 7,
                'is_active' => true,
                'created_at' => '2026-06-24 16:18:56',
                'updated_at' => '2026-06-24 16:19:16',
            ),
            61 => 
            array (
                'id' => 83,
                'name' => 'QcExtractor',
                'route' => 'http://192.168.1.92:5657/',
                'icon' => '',
                'parent_id' => 54,
                'order_number' => 6,
                'is_active' => true,
                'created_at' => '2026-06-12 10:01:23',
                'updated_at' => '2026-06-24 16:19:45',
            ),
            62 => 
            array (
                'id' => 89,
                'name' => 'Audit Toko',
                'route' => 'https://master.my.id/mobile/audit',
                'icon' => '',
                'parent_id' => 88,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-06-24 16:20:10',
                'updated_at' => '2026-06-24 16:20:10',
            ),
            63 => 
            array (
                'id' => 90,
                'name' => 'RWO',
                'route' => 'https://master.my.id/mobile/rwo',
                'icon' => '',
                'parent_id' => 88,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-06-24 16:20:42',
                'updated_at' => '2026-06-24 16:20:42',
            ),
            64 => 
            array (
                'id' => 91,
                'name' => 'Perbaikan Tikor',
                'route' => 'https://master.my.id/mobile/perbaikan-tikor',
                'icon' => '',
                'parent_id' => 88,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-06-24 16:21:15',
                'updated_at' => '2026-06-24 16:21:15',
            ),
            65 => 
            array (
                'id' => 71,
                'name' => 'Others',
                'route' => '',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M256,144a64,64,0,1,0-64-64A64.072,64.072,0,0,0,256,144Zm0-96a32,32,0,1,1-32,32A32.036,32.036,0,0,1,256,48Z" class="ci-primary"/>
  <path fill="var(--ci-primary-color, currentColor)" d="M256,368a64,64,0,1,0,64,64A64.072,64.072,0,0,0,256,368Zm0,96a32,32,0,1,1,32-32A32.036,32.036,0,0,1,256,464Z" class="ci-primary"/>
  <path fill="var(--ci-primary-color, currentColor)" d="M256,192a64,64,0,1,0,64,64A64.072,64.072,0,0,0,256,192Zm0,96a32,32,0,1,1,32-32A32.036,32.036,0,0,1,256,288Z" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 15,
                'is_active' => true,
                'created_at' => '2026-06-03 11:36:16',
                'updated_at' => '2026-08-04 13:28:31',
            ),
            66 => 
            array (
                'id' => 95,
                'name' => 'Bug & Request',
                'route' => 'others.bug-report-request',
                'icon' => '',
                'parent_id' => 71,
                'order_number' => 6,
                'is_active' => true,
                'created_at' => '2026-07-15 18:28:02',
                'updated_at' => '2026-07-15 18:28:17',
            ),
            67 => 
            array (
                'id' => 93,
                'name' => 'Monitoring RWO',
                'route' => 'rwo.summarylistpotensi',
                'icon' => '',
                'parent_id' => 62,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-07-02 18:15:49',
                'updated_at' => '2026-07-20 11:12:56',
            ),
            68 => 
            array (
                'id' => 96,
                'name' => 'Audit Toko',
                'route' => 'others.audit-toko',
                'icon' => '',
                'parent_id' => 71,
                'order_number' => 7,
                'is_active' => true,
                'created_at' => '2026-07-23 09:57:28',
                'updated_at' => '2026-07-23 09:57:28',
            ),
            69 => 
            array (
                'id' => 97,
                'name' => 'Monitoring BG',
                'route' => 'monitoringbankgaransi.index',
                'icon' => '',
                'parent_id' => 71,
                'order_number' => 8,
                'is_active' => true,
                'created_at' => '2026-07-24 17:50:03',
                'updated_at' => '2026-07-24 17:50:03',
            ),
            70 => 
            array (
                'id' => 63,
                'name' => 'JKS Team Elite',
                'route' => 'jks-team-elite.index',
                'icon' => '',
                'parent_id' => 50,
                'order_number' => 4,
                'is_active' => true,
                'created_at' => '2026-05-24 11:36:07',
                'updated_at' => '2026-07-31 14:37:02',
            ),
            71 => 
            array (
                'id' => 50,
                'name' => 'Call Plan',
                'route' => NULL,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M478.465,89.022,329.6,47.382,180.3,89.438,41.459,50.052h0A20,20,0,0,0,16,69.293v340.6a24.093,24.093,0,0,0,17.449,23.089l146.817,41.65,149.365-42.074,140.983,39.436A20,20,0,0,0,496,452.728V112.135A24.08,24.08,0,0,0,478.465,89.022ZM163,436.466,48,403.842V85.17l115,32.624Zm150.615-32.647L195,437.231V118.542L313.615,85.13ZM464,436.91,345.615,403.8V85.089L464,118.2Z" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 12,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-08-04 15:34:40',
            ),
            72 => 
            array (
                'id' => 8,
                'name' => 'Selling In',
                'route' => 'selling-in.index',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M441.885,141.649A32.028,32.028,0,0,0,415.669,128H336V80a32.036,32.036,0,0,0-32-32H48A32.036,32.036,0,0,0,16,80V408H69.082a67.982,67.982,0,0,0,133.836,0H309.082a67.982,67.982,0,0,0,133.836,0H496V226.522a23.882,23.882,0,0,0-4.338-13.763ZM47.98,80H304V256H48ZM136,432a36,36,0,1,1,36-36A36.04,36.04,0,0,1,136,432Zm240,0a36,36,0,1,1,36-36A36.04,36.04,0,0,1,376,432Zm88-56H440.994a68,68,0,0,0-129.988,0H200.994a68,68,0,0,0-129.988,0H48V288H464Zm0-120H336V160h79.669L464,229.044Z" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 5,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-08-04 14:15:23',
            ),
            73 => 
            array (
                'id' => 76,
                'name' => 'Report',
                'route' => '',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M334.627,16H48V496H472V153.373ZM440,166.627V168H320V48h1.373ZM80,464V48H288V200H440V464Z" class="ci-primary"/>
  <rect width="224" height="32" x="136" y="296" fill="var(--ci-primary-color, currentColor)" class="ci-primary"/>
  <rect width="224" height="32" x="136" y="376" fill="var(--ci-primary-color, currentColor)" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 13,
                'is_active' => true,
                'created_at' => '2026-06-08 13:41:56',
                'updated_at' => '2026-08-04 14:22:24',
            ),
            74 => 
            array (
                'id' => 103,
                'name' => 'OTHERS',
                'route' => '',
                'icon' => '',
                'parent_id' => NULL,
                'order_number' => 14,
                'is_active' => true,
                'created_at' => '2026-08-04 14:22:37',
                'updated_at' => '2026-08-04 14:53:13',
            ),
            75 => 
            array (
                'id' => 104,
                'name' => 'Upload Target Eska',
                'route' => 'others.target-per-se-untuk-eska.index',
                'icon' => '',
                'parent_id' => 71,
                'order_number' => 9,
                'is_active' => true,
                'created_at' => '2026-08-06 16:02:31',
                'updated_at' => '2026-08-06 16:02:31',
            ),
            76 => 
            array (
                'id' => 99,
                'name' => 'DASHBOARD',
                'route' => '',
                'icon' => '',
                'parent_id' => NULL,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => '2026-08-04 14:12:25',
                'updated_at' => '2026-08-04 14:52:36',
            ),
            77 => 
            array (
                'id' => 101,
                'name' => 'MASTER DATA',
                'route' => '',
                'icon' => '',
                'parent_id' => NULL,
                'order_number' => 7,
                'is_active' => true,
                'created_at' => '2026-08-04 14:18:27',
                'updated_at' => '2026-08-04 14:52:54',
            ),
            78 => 
            array (
                'id' => 102,
                'name' => 'REPORT',
                'route' => '',
                'icon' => '',
                'parent_id' => NULL,
                'order_number' => 10,
                'is_active' => true,
                'created_at' => '2026-08-04 14:21:42',
                'updated_at' => '2026-08-04 14:53:05',
            ),
            79 => 
            array (
                'id' => 62,
                'name' => 'RWO',
                'route' => '',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M471.232,111.731H368V16H144.232v95.731h-103A24.8,24.8,0,0,0,16.464,136.5V470.532A24.8,24.8,0,0,0,41.232,495.3h430A24.8,24.8,0,0,0,496,470.532V136.5A24.8,24.8,0,0,0,471.232,111.731ZM176.232,48H336v63.731H176.232Zm286.232,97.269v80.286l-53.177,53.176H273V232H239.464v46.731H103.177L50,225.555V145.269ZM50,461.764V272.982l39.286,39.287H239.464V359.5H273V312.269H423.178l39.286-39.287V461.764Z" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 11,
                'is_active' => true,
                'created_at' => '2026-05-20 12:54:03',
                'updated_at' => '2026-08-04 15:34:33',
            ),
            80 => 
            array (
                'id' => 106,
                'name' => 'Insentif',
                'route' => 'others.insentif.perhitungan.index',
                'icon' => '',
                'parent_id' => 76,
                'order_number' => 7,
                'is_active' => true,
                'created_at' => '2026-08-14 09:29:08',
                'updated_at' => '2026-08-14 09:33:32',
            ),
            81 => 
            array (
                'id' => 107,
                'name' => 'Target Insentif',
                'route' => 'others.insentif.target.index',
                'icon' => '',
                'parent_id' => 76,
                'order_number' => 8,
                'is_active' => true,
                'created_at' => '2026-08-18 15:00:00',
                'updated_at' => '2026-08-18 15:00:00',
            ),
            82 => 
            array (
                'id' => 54,
                'name' => 'Portal',
                'route' => NULL,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M256,16C123.452,16,16,123.452,16,256S123.452,496,256,496,496,388.548,496,256,388.548,16,256,16ZM175.047,447.667A208.26,208.26,0,0,1,64.333,175.047c1.018-2.408,2.092-4.785,3.2-7.145L120,215.126v63.235L197.1,360H236v49.047l-47.052,43.915Q181.926,450.568,175.047,447.667Zm228.031-44.589A207.253,207.253,0,0,1,256,464a210.4,210.4,0,0,1-29.722-2.107L268,422.953V328H210.9L152,265.639V200.874L83.7,139.408a209.259,209.259,0,0,1,91.343-75.075A207.793,207.793,0,0,1,371.3,82.839l-45.564,58.582,15.49,38.725-10.485,10.485-78.618-15.723L208,208v88H384l50.345,67.126A208.127,208.127,0,0,1,403.078,403.078ZM464,256a206.763,206.763,0,0,1-13.873,74.837L400,264H240V224l19.877-14.908,81.382,16.277,37.515-37.515-16.51-41.275,34.2-43.977q3.361,3.084,6.61,6.32A207.253,207.253,0,0,1,464,256Z" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 16,
                'is_active' => true,
                'created_at' => '2026-05-12 13:28:21',
                'updated_at' => '2026-08-04 14:22:50',
            ),
            83 => 
            array (
                'id' => 61,
                'name' => 'Power BI',
                'route' => 'dashboard.selling-overview',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M105.361,398.32A195.891,195.891,0,0,1,343.42,91.125L366.676,67.87A227.875,227.875,0,0,0,82.733,420.948,228.027,228.027,0,0,0,366.24,452.1l-23.312-23.312C267.9,472.768,169.657,462.617,105.361,398.32Z" class="ci-primary"/>
  <path fill="var(--ci-primary-color, currentColor)" d="M468.916,353.07a243.542,243.542,0,0,0,0-186.459c-.885-2.136-1.806-4.251-2.747-6.354A242.246,242.246,0,0,0,416.11,87.571L404.8,76.257,393.483,87.571,221.213,259.84l172.63,172.631L404.8,443.424,416.11,432.11a242.218,242.218,0,0,0,49.452-71.358C466.716,358.212,467.844,355.657,468.916,353.07ZM404.359,121.95a211.57,211.57,0,0,1,0,275.781L266.468,259.84Z" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => '2026-05-15 13:26:53',
                'updated_at' => '2026-08-04 13:53:27',
            ),
            84 => 
            array (
                'id' => 105,
                'name' => 'Dashboard',
                'route' => 'dashboard.v2.sellin',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <path fill="var(--ci-primary-color, currentColor)" d="M105.361,398.32A195.891,195.891,0,0,1,343.42,91.125L366.676,67.87A227.875,227.875,0,0,0,82.733,420.948,228.027,228.027,0,0,0,366.24,452.1l-23.312-23.312C267.9,472.768,169.657,462.617,105.361,398.32Z" class="ci-primary"/>
  <path fill="var(--ci-primary-color, currentColor)" d="M468.916,353.07a243.542,243.542,0,0,0,0-186.459c-.885-2.136-1.806-4.251-2.747-6.354A242.246,242.246,0,0,0,416.11,87.571L404.8,76.257,393.483,87.571,221.213,259.84l172.63,172.631L404.8,443.424,416.11,432.11a242.218,242.218,0,0,0,49.452-71.358C466.716,358.212,467.844,355.657,468.916,353.07ZM404.359,121.95a211.57,211.57,0,0,1,0,275.781L266.468,259.84Z" class="ci-primary"/>
</svg>
',
                'parent_id' => NULL,
                'order_number' => 3,
                'is_active' => true,
                'created_at' => '2026-08-12 11:06:47',
                'updated_at' => '2026-08-12 11:06:47',
            ),
        ));
        
        
    }
}