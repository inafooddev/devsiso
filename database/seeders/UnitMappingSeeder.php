<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitMapping;

class UnitMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mappings = [
            'CTN' => ['CARTON', 'CRT', 'CTN', 'DUS', 'KARTON', 'KRT', 'KRT01', 'KRT10', 'KRT12', 'KRT18', 'KRT2', 'KRT24', 'KRT3', 'KRT4', 'KRT6', 'KRT8', 'KRTN', 'KTN'],
            'PCK' => ['BALL', 'BOX', 'PACK', 'PAK', 'PCK', 'PRES', 'RTG', 'RCG', 'BAL', 'DOS', 'PK', 'RENCENG', 'PRESS'],
            'PCS' => ['BKS', 'BUAH', 'PCS', 'PLS', 'TIN', 'TOP', 'PC', 'JAR']
        ];

        // Note: 'BALL' was listed under both PCK and PCS in the user request. 
        // I will map BALL to PCK since it's the first occurrence. I removed it from the PCS list to avoid duplicate source_unit error.

        foreach ($mappings as $standard => $sources) {
            foreach ($sources as $source) {
                UnitMapping::firstOrCreate([
                    'source_unit' => strtoupper(trim($source))
                ], [
                    'standard_unit' => $standard
                ]);
            }
        }
    }
}
