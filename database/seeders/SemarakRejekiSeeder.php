<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SemarakRejekiRewardTier;
use App\Models\SemarakRejekiSpvTarget;

class SemarakRejekiSeeder extends Seeder
{
    public function run(): void
    {
        $harga = 220500;

        // ==========================================
        // 1. MASTER REWARD TIERS - REGIONAL JAWA (22 Paket)
        // ==========================================
        $jawaTiers = [
            ['A', 400,   'SMART WATCH REDMI 6 LITE', 900000],
            ['B', 700,   'HP REDMI A5', 1600000],
            ['C', 1000,  'HP REDMI A7 PRO', 2300000],
            ['D', 1800,  'SAMSUNG GALAXY A07 8GB', 4000000],
            ['E', 2100,  'HP SAMSUNG A17 LTE 4GB', 4700000],
            ['F', 2700,  'SAMSUNG GALAXY WATCH 9', 6000000],
            ['G', 2900,  'SamsungTablet GalaxyTabS10 Litewifi', 6400000],
            ['H', 3200,  'HP SAMSUNG GALAXY A56 5G, 8GB', 7100000],
            ['I', 3600,  'HP 14-ep0321TU (Silver) : Intel Core i3-1315U/4GB/512GB SSD', 8000000],
            ['J', 4000,  'SAMSUNG 65" Crystal UHD U8000H 4K Samsung Vision AI Smart TV', 8900000],
            ['K', 4100,  'HP XIAOMI 17T', 9100000],
            ['L', 4700,  'HP SAMSUNG GALAXY A57 5G, 12GB', 10400000],
            ['M', 5100,  'IdeaPad Slim 3 14IRU8 (Arctic Grey) : Intel Core i3-1315U/8GB/512GB SSD', 11300000],
            ['N', 6500,  'HP 14-ep1188TU (Silver) : Core Ultra 5 125H/16GB/512GB SSD', 14400000],
            ['O', 6600,  'SAMSUNG GALAXY TAB S10 FE+ (WI-FI ONLY), 256GB', 14600000],
            ['P', 6700,  'MOTOR LISTRIK UWINFLY T80C', 14800000],
            ['Q', 10700, 'MOTOR YAMAHA FAZZIO HYBRID', 23600000],
            ['R', 13900, 'MOTOR KAWASAKI KLX110R', 30700000],
            ['S', 14800, 'MOTOR LISTRIK UWINFLY M135H', 32700000],
            ['T', 15700, 'MOTOR YAMAHA AEROX ALPHA', 34700000],
            ['U', 16700, 'MOTOR HONDA PCX 160', 36900000],
            ['V', 18000, 'MOTOR KAWASAKI KLX150S', 39700000],
        ];

        foreach ($jawaTiers as $idx => $t) {
            $targetKtn = $t[1];
            $trx = $targetKtn * $harga;
            SemarakRejekiRewardTier::updateOrCreate(
                ['kategori_wilayah' => 'jawa', 'kode_paket' => $t[0]],
                [
                    'target_karton'    => $targetKtn,
                    'harga_per_karton' => $harga,
                    'nilai_transaksi'  => $trx,
                    'nama_hadiah'      => $t[2],
                    'nilai_hadiah'     => $t[3],
                    'cost_ratio'       => 1.0,
                    'urutan'           => $idx + 1,
                    'is_active'        => true,
                ]
            );
        }

        // ==========================================
        // 2. MASTER REWARD TIERS - REGIONAL PULAU (17 Paket)
        // ==========================================
        $pulauTiers = [
            ['A', 200,   'SMART WATCH BAND 11 ACTIVE', 500000],
            ['B', 300,   'REDMI BUDS 6 PRO', 700000],
            ['C', 500,   'REDMI HEADPHONE NEO', 1200000],
            ['D', 900,   'SMART WATCH S4', 2000000],
            ['E', 1100,  'XIAOMI TV A32 2026', 2500000],
            ['F', 1200,  'SMART WATCH XIAOMI S5', 2700000],
            ['G', 1300,  'SAMSUNG GALAXY BUDS 4', 2900000],
            ['H', 1700,  'AirPods 4 with Active Noise Cancellation', 3800000],
            ['I', 2200,  'LAPTOP AXIOO HYPE 10 BLACK N4020/8G/256SD/14"/WIN11/NO TAS', 4900000],
            ['J', 2800,  'XIAOMI TV A PRO 55"', 6200000],
            ['K', 3400,  'Apple Watch Series 11 Sport Band', 7500000],
            ['L', 5400,  'HP 240R G9 : Intel Core 5-120U/8GB/512GB SSD/Win 11 Home', 12000000],
            ['M', 6000,  '13-inch MacBook Neo', 13300000],
            ['N', 6400,  'iPhone 17e', 14200000],
            ['O', 8100,  'SAMSUNG 55" The Frame LS03HE 4K Samsung Vision AI Smart TV', 17900000],
            ['P', 8200,  'MOTOR LISTRIK UWINFLY T90', 18100000],
            ['Q', 10700, 'MOTOR YAMAHA FAZZIO HYBRID', 23600000],
        ];

        foreach ($pulauTiers as $idx => $t) {
            $targetKtn = $t[1];
            $trx = $targetKtn * $harga;
            SemarakRejekiRewardTier::updateOrCreate(
                ['kategori_wilayah' => 'pulau', 'kode_paket' => $t[0]],
                [
                    'target_karton'    => $targetKtn,
                    'harga_per_karton' => $harga,
                    'nilai_transaksi'  => $trx,
                    'nama_hadiah'      => $t[2],
                    'nilai_hadiah'     => $t[3],
                    'cost_ratio'       => 1.0,
                    'urutan'           => $idx + 1,
                    'is_active'        => true,
                ]
            );
        }

        // ==========================================
        // 3. TARGET SPV (35 SPV - Jawa & Pulau)
        // ==========================================
        $spvTargetList = [
            // Regional Jawa
            ['SPVINAJABO1',  700,   'jawa', 'B'],
            ['SPVINAJABO4',  1000,  'jawa', 'C'],
            ['SPVINAJABO5',  2100,  'jawa', 'E'],
            ['SPVINAJATIM4', 3200,  'jawa', 'H'],
            ['SPVINAJATIM5', 6700,  'jawa', 'P'],
            ['SPVINAJATIM1', 13900, 'jawa', 'R'],
            ['SPVINAJATIM2', 14800, 'jawa', 'S'],
            ['SPVINAJATIM8', 2900,  'jawa', 'G'],
            ['SPVINAJATIM6', 3600,  'jawa', 'I'],
            ['SPVINAJATIM9', 5100,  'jawa', 'M'],
            ['SPVINAJATIM10',15700, 'jawa', 'T'],
            ['SPVINAJABAR3', 400,   'jawa', 'A'],
            ['SPVINAJABAR5', 1800,  'jawa', 'D'],
            ['SPVINAJABAR4', 4000,  'jawa', 'J'],
            ['SPVINAJABAR1', 4100,  'jawa', 'K'],
            ['SPVINAJATENG3',2700,  'jawa', 'F'],
            ['SPVINAJATENG1',6600,  'jawa', 'O'],
            ['SPVINAJATENG4',4700,  'jawa', 'L'],
            ['SPVINAJATENG7',6500,  'jawa', 'N'],
            ['SPVINAJATENG6',10700, 'jawa', 'Q'],

            // Regional Pulau
            ['SPVINAKAL6',   1100,  'pulau', 'E'],
            ['SPVINAKAL5',   3400,  'pulau', 'K'],
            ['SPVINAKAL1',   6400,  'pulau', 'N'],
            ['SPVINASUL1',   6000,  'pulau', 'M'],
            ['SPVINASUL4',   8100,  'pulau', 'O'],
            ['SPVINASUL2',   8200,  'pulau', 'P'],
            ['SPVINASUMUT2', 300,   'pulau', 'B'],
            ['SPVINARIAU1',  1200,  'pulau', 'F'],
            ['SPVINASUMBAR1',500,   'pulau', 'C'],
            ['SPVINASUMUT1', 900,   'pulau', 'D'],
            ['SPVINABENGKULU',1300, 'pulau', 'G'],
            ['SPVINAJMB1',   200,   'pulau', 'A'],
            ['SPVINALAMP3',  2200,  'pulau', 'I'],
            ['SPVINALAMP1',  5400,  'pulau', 'L'],
            ['SPVINASUM2',   2800,  'pulau', 'J'],
        ];

        foreach ($spvTargetList as $item) {
            $spvCode = $item[0];
            $target3Bln = $item[1];
            $kategori = $item[2];
            $kodePaket = $item[3];

            // Cari siso_code dari mapping
            $tecm = DB::table('team_elite_code_mappings')->where('team_elite_code', $spvCode)->first();
            $sisoCode = $tecm ? $tecm->siso_code : '';

            // Cari tier id
            $tier = SemarakRejekiRewardTier::where('kategori_wilayah', $kategori)
                ->where('kode_paket', $kodePaket)
                ->first();

            // Pembagian target per bulan (dibagi 3 rata dengan presisi 2 desimal)
            $targetM1 = round($target3Bln / 3, 2);
            $targetM2 = round($target3Bln / 3, 2);
            $targetM3 = round($target3Bln - ($targetM1 + $targetM2), 2); // Menjaga agar total persis sama

            SemarakRejekiSpvTarget::updateOrCreate(
                ['tahun' => 2026, 'supervisor_code' => $spvCode],
                [
                    'siso_code'          => $sisoCode,
                    'kategori_wilayah'   => $kategori,
                    'tier_id'            => $tier ? $tier->id : null,
                    'kode_paket_awal'    => $kodePaket,
                    'target_karton_3bln' => $target3Bln,
                    'target_m1_okt'      => $targetM1,
                    'target_m2_nov'      => $targetM2,
                    'target_m3_des'      => $targetM3,
                    'is_active'          => true,
                    'catatan'            => 'Target Awal Q4 2026',
                ]
            );
        }
    }
}
