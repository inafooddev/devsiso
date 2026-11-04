<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SemarakRejekiRsmTarget;
use App\Models\SemarakRejekiAsmTarget;

class SemarakRejekiAsmRsmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            // 1. JAWA 1
            [
                'tahun'             => 2026,
                'region_code'       => 'JAWA_1',
                'region_name'       => 'JAWA 1',
                'rsm_db_pattern'    => 'INA JAWA 1',
                'nomor_proposal'    => '001/INA-RWDASMRSM/JAWA1/IX/2026',
                'max_ly'            => 17203,
                'target_l1_monthly' => 20660,
                'growth_l1_pct'     => 20.00,
                'reward_rsm_l1'     => 6000000,
                'target_l2_monthly' => 22380,
                'growth_l2_pct'     => 30.00,
                'reward_rsm_l2'     => 12300000,
                'urutan'            => 1,
                'is_active'         => true,
                'areas' => [
                    [
                        'area_code'         => 'JABODETABEK',
                        'area_name'         => 'JABODETABEK',
                        'asm_db_name'       => 'ASM JABODETABEK',
                        'max_ly'            => 612,
                        'target_l1_monthly' => 740,
                        'growth_l1_pct'     => 21.00,
                        'reward_asm_l1'     => 900000,
                        'target_l2_monthly' => 800,
                        'growth_l2_pct'     => 31.00,
                        'reward_asm_l2'     => 1300000,
                        'urutan'            => 1,
                    ],
                    [
                        'area_code'         => 'JATIM_1',
                        'area_name'         => 'JATIM 1',
                        'asm_db_name'       => 'ASM JATIM 1',
                        'max_ly'            => 9010,
                        'target_l1_monthly' => 10820,
                        'growth_l1_pct'     => 20.00,
                        'reward_asm_l1'     => 12600000,
                        'target_l2_monthly' => 11720,
                        'growth_l2_pct'     => 30.00,
                        'reward_asm_l2'     => 19200000,
                        'urutan'            => 2,
                    ],
                    [
                        'area_code'         => 'JATIM_2',
                        'area_name'         => 'JATIM 2',
                        'asm_db_name'       => 'ASM JATIM 2',
                        'max_ly'            => 7580,
                        'target_l1_monthly' => 9100,
                        'growth_l1_pct'     => 20.00,
                        'reward_asm_l1'     => 10600000,
                        'target_l2_monthly' => 9860,
                        'growth_l2_pct'     => 30.00,
                        'reward_asm_l2'     => 16100000,
                        'urutan'            => 3,
                    ],
                ],
            ],

            // 2. JAWA 2
            [
                'tahun'             => 2026,
                'region_code'       => 'JAWA_2',
                'region_name'       => 'JAWA 2',
                'rsm_db_pattern'    => 'INA JAWA 2',
                'nomor_proposal'    => '002/INA-RWDASMRSM/JAWA2/IX/2026',
                'max_ly'            => 10091,
                'target_l1_monthly' => 12120,
                'growth_l1_pct'     => 20.00,
                'reward_rsm_l1'     => 3700000,
                'target_l2_monthly' => 13130,
                'growth_l2_pct'     => 30.00,
                'reward_rsm_l2'     => 7200000,
                'urutan'            => 2,
                'is_active'         => true,
                'areas' => [
                    [
                        'area_code'         => 'JABAR',
                        'area_name'         => 'JABAR',
                        'asm_db_name'       => 'ASM JABAR',
                        'max_ly'            => 2327,
                        'target_l1_monthly' => 2800,
                        'growth_l1_pct'     => 20.00,
                        'reward_asm_l1'     => 3400000,
                        'target_l2_monthly' => 3030,
                        'growth_l2_pct'     => 30.00,
                        'reward_asm_l2'     => 5000000,
                        'urutan'            => 1,
                    ],
                    [
                        'area_code'         => 'JATENG_1',
                        'area_name'         => 'JATENG 1',
                        'asm_db_name'       => 'ASM JATENG 1',
                        'max_ly'            => 2700,
                        'target_l1_monthly' => 3240,
                        'growth_l1_pct'     => 20.00,
                        'reward_asm_l1'     => 4000000,
                        'target_l2_monthly' => 3510,
                        'growth_l2_pct'     => 30.00,
                        'reward_asm_l2'     => 5700000,
                        'urutan'            => 2,
                    ],
                    [
                        'area_code'         => 'JATENG_2',
                        'area_name'         => 'JATENG 2',
                        'asm_db_name'       => 'ASM JATENG 2',
                        'max_ly'            => 5064,
                        'target_l1_monthly' => 6080,
                        'growth_l1_pct'     => 20.00,
                        'reward_asm_l1'     => 7400000,
                        'target_l2_monthly' => 6590,
                        'growth_l2_pct'     => 30.00,
                        'reward_asm_l2'     => 10800000,
                        'urutan'            => 3,
                    ],
                ],
            ],

            // 3. PULAU
            [
                'tahun'             => 2026,
                'region_code'       => 'PULAU',
                'region_name'       => 'PULAU',
                'rsm_db_pattern'    => 'INA PULAU 1',
                'nomor_proposal'    => '003/INA-RWDASMRSM/PULAU/V/2026',
                'max_ly'            => 9376,
                'target_l1_monthly' => 11260,
                'growth_l1_pct'     => 20.00,
                'reward_rsm_l1'     => 6600000,
                'target_l2_monthly' => 12200,
                'growth_l2_pct'     => 30.00,
                'reward_rsm_l2'     => 12400000,
                'urutan'            => 3,
                'is_active'         => true,
                'areas' => [
                    [
                        'area_code'         => 'KALIMANTAN',
                        'area_name'         => 'KALIMANTAN',
                        'asm_db_name'       => 'ASM KALIMANTAN',
                        'max_ly'            => 2749,
                        'target_l1_monthly' => 3300,
                        'growth_l1_pct'     => 20.00,
                        'reward_asm_l1'     => 7700000,
                        'target_l2_monthly' => 3580,
                        'growth_l2_pct'     => 30.00,
                        'reward_asm_l2'     => 10900000,
                        'urutan'            => 1,
                    ],
                    [
                        'area_code'         => 'SULAWESI',
                        'area_name'         => 'SULAWESI',
                        'asm_db_name'       => 'ASM SULAWESI',
                        'max_ly'            => 6627,
                        'target_l1_monthly' => 7960,
                        'growth_l1_pct'     => 20.00,
                        'reward_asm_l1'     => 18500000,
                        'target_l2_monthly' => 8620,
                        'growth_l2_pct'     => 30.00,
                        'reward_asm_l2'     => 26200000,
                        'urutan'            => 2,
                    ],
                ],
            ],

            // 4. SUMATERA 1
            [
                'tahun'             => 2026,
                'region_code'       => 'SUMATERA_1',
                'region_name'       => 'SUMATERA 1',
                'rsm_db_pattern'    => 'INA SUMATERA 1',
                'nomor_proposal'    => '004/INA-RWDASMRSM/SUM1/V/2026',
                'max_ly'            => 1231,
                'target_l1_monthly' => 1500,
                'growth_l1_pct'     => 22.00,
                'reward_rsm_l1'     => 1000000,
                'target_l2_monthly' => 1620,
                'growth_l2_pct'     => 32.00,
                'reward_rsm_l2'     => 1700000,
                'urutan'            => 4,
                'is_active'         => true,
                'areas' => [
                    [
                        'area_code'         => 'KEPRI',
                        'area_name'         => 'KEPRI',
                        'asm_db_name'       => 'ASM KEPRI',
                        'max_ly'            => 310,
                        'target_l1_monthly' => 380,
                        'growth_l1_pct'     => 23.00,
                        'reward_asm_l1'     => 900000,
                        'target_l2_monthly' => 410,
                        'growth_l2_pct'     => 32.00,
                        'reward_asm_l2'     => 1300000,
                        'urutan'            => 1,
                    ],
                    [
                        'area_code'         => 'NAD_SUMUT',
                        'area_name'         => 'NAD - SUMUT',
                        'asm_db_name'       => 'ASM NAD - SUMUT',
                        'max_ly'            => 295,
                        'target_l1_monthly' => 360,
                        'growth_l1_pct'     => 22.00,
                        'reward_asm_l1'     => 900000,
                        'target_l2_monthly' => 390,
                        'growth_l2_pct'     => 32.00,
                        'reward_asm_l2'     => 1200000,
                        'urutan'            => 2,
                    ],
                    [
                        'area_code'         => 'RIAU_SUMBAR',
                        'area_name'         => 'RIAU - SUMBAR',
                        'asm_db_name'       => 'ASM RIAU - SUMBAR',
                        'max_ly'            => 627,
                        'target_l1_monthly' => 760,
                        'growth_l1_pct'     => 21.00,
                        'reward_asm_l1'     => 1900000,
                        'target_l2_monthly' => 820,
                        'growth_l2_pct'     => 31.00,
                        'reward_asm_l2'     => 2500000,
                        'urutan'            => 3,
                    ],
                ],
            ],

            // 5. SUMATERA 2
            [
                'tahun'             => 2026,
                'region_code'       => 'SUMATERA_2',
                'region_name'       => 'SUMATERA 2',
                'rsm_db_pattern'    => 'INA SUMATERA 2',
                'nomor_proposal'    => '005/INA-RWDASMRSM/SUM2/IX/2026',
                'max_ly'            => 4438,
                'target_l1_monthly' => 5340,
                'growth_l1_pct'     => 20.00,
                'reward_rsm_l1'     => 3300000,
                'target_l2_monthly' => 5780,
                'growth_l2_pct'     => 30.00,
                'reward_rsm_l2'     => 5800000,
                'urutan'            => 5,
                'is_active'         => true,
                'areas' => [
                    [
                        'area_code'         => 'JALU',
                        'area_name'         => 'JALU',
                        'asm_db_name'       => 'ASM JALU',
                        'max_ly'            => 930,
                        'target_l1_monthly' => 1120,
                        'growth_l1_pct'     => 0.00,
                        'reward_asm_l1'     => 2700000,
                        'target_l2_monthly' => 1210,
                        'growth_l2_pct'     => 30.00,
                        'reward_asm_l2'     => 3600000,
                        'urutan'            => 1,
                    ],
                    [
                        'area_code'         => 'LAMPUNG',
                        'area_name'         => 'LAMPUNG',
                        'asm_db_name'       => 'ASM LAMPUNG',
                        'max_ly'            => 2168,
                        'target_l1_monthly' => 2610,
                        'growth_l1_pct'     => 20.00,
                        'reward_asm_l1'     => 6400000,
                        'target_l2_monthly' => 2820,
                        'growth_l2_pct'     => 30.00,
                        'reward_asm_l2'     => 8400000,
                        'urutan'            => 2,
                    ],
                    [
                        'area_code'         => 'SUMSEL',
                        'area_name'         => 'SUMSEL',
                        'asm_db_name'       => 'ASM SUMSEL',
                        'max_ly'            => 1340,
                        'target_l1_monthly' => 1610,
                        'growth_l1_pct'     => 20.00,
                        'reward_asm_l1'     => 3900000,
                        'target_l2_monthly' => 1750,
                        'growth_l2_pct'     => 31.00,
                        'reward_asm_l2'     => 5200000,
                        'urutan'            => 3,
                    ],
                ],
            ],
        ];

        foreach ($regions as $regData) {
            $areas = $regData['areas'];
            unset($regData['areas']);

            $rsm = SemarakRejekiRsmTarget::updateOrCreate(
                [
                    'tahun'       => $regData['tahun'],
                    'region_code' => $regData['region_code'],
                ],
                $regData
            );

            foreach ($areas as $aData) {
                $aData['rsm_target_id'] = $rsm->id;
                $aData['tahun'] = $rsm->tahun;
                $aData['is_active'] = true;

                SemarakRejekiAsmTarget::updateOrCreate(
                    [
                        'tahun'     => $aData['tahun'],
                        'area_code' => $aData['area_code'],
                    ],
                    $aData
                );
            }
        }
    }
}
