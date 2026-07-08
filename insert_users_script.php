<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$data = <<<EOF
ALFINO NUR GUSAL;spvinajabo1;10;spvinajabo1@email.com;SPVINAJABO1
VACANT;spvinajabo3;10;spvinajabo3@email.com;SPVINAJABO3
POERNOMO SOLEH;spvinajabo4;10;spvinajabo4@email.com;SPVINAJABO4
GALUH RASPATY MARISTIYONO;spvinajabo5;10;spvinajabo5@email.com;SPVINAJABO5
SANDI IRAWAN;spvinajatim4;10;spvinajatim4@email.com;SPVINAJATIM4
TRIPUTRA ARIEF KURNIAWAN;spvinajatim1;10;spvinajatim1@email.com;SPVINAJATIM1
RIVAL WARIYANZAH;spvinajatim5;10;spvinajatim5@email.com;SPVINAJATIM5
SAIFUL HAKIM;spvinajatim2;10;spvinajatim2@email.com;SPVINAJATIM2
SUHARI ADI WIBOWO;spvinajatim8;10;spvinajatim8@email.com;SPVINAJATIM8
MARDI AMSARI;spvinajatim6;10;spvinajatim6@email.com;SPVINAJATIM6
BUDI SETIAWAN;spvinajatim10;10;spvinajatim10@email.com;SPVINAJATIM10
AMIN TOHARI;spvinajatim9;10;spvinajatim9@email.com;SPVINAJATIM9
DICKY KINZA PRISDIANTO;spvinajatim7;10;spvinajatim7@email.com;SPVINAJATIM7
TETI ROHAETI;spvinajabar1;10;spvinajabar1@email.com;SPVINAJABAR1
Y R MAMAT SITUMORANG;spvinajabar5;10;spvinajabar5@email.com;SPVINAJABAR5
CECEP KUSNADI;spvinajabar4;10;spvinajabar4@email.com;SPVINAJABAR4
AGUS HARITS FELANI;spvinajabar3;10;spvinajabar3@email.com;SPVINAJABAR3
DIAN KURNIAWAN;spvinajateng3;10;spvinajateng3@email.com;SPVINAJATENG3
DENI SANTOSO;spvinajateng1;10;spvinajateng1@email.com;SPVINAJATENG1
RANDHY ADITYA FIRMANSYAH;spvinajateng2;10;spvinajateng2@email.com;SPVINAJATENG2
TRIYONO;spvinajateng6;10;spvinajateng6@email.com;SPVINAJATENG6
MUHAMMAD ADHA PRASETYO;spvinajateng4;10;spvinajateng4@email.com;SPVINAJATENG4
ICSYA HELMI SEPTIAN NUR PRADANA;spvinajateng5;10;spvinajateng5@email.com;SPVINAJATENG5
GALIH PERMATA PUTRA;spvinajateng7;10;spvinajateng7@email.com;SPVINAJATENG7
VACANT;spvinakal3;10;spvinakal3@email.com;SPVINAKAL3
VACANT;spvinakal2;10;spvinakal2@email.com;SPVINAKAL2
IVANDI;spvinakal1;10;spvinakal1@email.com;SPVINAKAL1
RIZA ARIFIN;spvinakal5;10;spvinakal5@email.com;SPVINAKAL5
CAHYA BUDI;spvinakal4;10;spvinakal4@email.com;SPVINAKAL4
RIFZKY BAWAZIER;spvinakal6;10;spvinakal6@email.com;SPVINAKAL6
MUHAMMAD SYARIFUDDIN;spvinasul1;10;spvinasul1@email.com;SPVINASUL1
RAHMAT IBRAHIM;spvinasul2;10;spvinasul2@email.com;SPVINASUL2
VACANT;spvinasul3;10;spvinasul3@email.com;SPVINASUL3
SALDY PERMADI RIDWAN;spvinasul4;10;spvinasul4@email.com;SPVINASUL4
FAKHRI RAHMADI;spvinasumut2;10;spvinasumut2@email.com;SPVINASUMUT2
YADEKEN SIHALOLO;spvinariau1;10;spvinariau1@email.com;SPVINARIAU1
DICKY WENDRIADI;spvinasumbar1;10;spvinasumbar1@email.com;SPVINASUMBAR1
SYAHRUN ALI HR;spvinasumut1;10;spvinasumut1@email.com;SPVINASUMUT1
DHANAR CHANDRA PERMANA;spvinabengkulu;10;spvinabengkulu@email.com;SPVINABENGKULU
TUMIJAN;spvinajmb1;10;spvinajmb1@email.com;SPVINAJMB1
SYARIF HIDAYAT;spvinalamp2;10;spvinalamp2@email.com;SPVINALAMP2
FAHRUROZI;spvinalamp3;10;spvinalamp3@email.com;SPVINALAMP3
RACHMAD YUDI HARTONO;spvinalamp1;10;spvinalamp1@email.com;SPVINALAMP1
DODI CANDRA;spvinasum2;10;spvinasum2@email.com;SPVINASUM2
MUHAMMAD IRHAM;spvinasum1;10;spvinasum1@email.com;SPVINASUM1
EOF;

$lines = explode("\n", trim($data));
$count = 0;
foreach ($lines as $line) {
    if (empty(trim($line))) continue;
    $parts = explode(";", trim($line));
    if (count($parts) >= 5) {
        $name = $parts[0];
        $userid = $parts[1];
        $access_group_id = $parts[2];
        $email = $parts[3];
        $supervisor_code = $parts[4];
        
        $user = User::where('userid', $userid)->orWhere('email', $email)->first();
        if (!$user) {
            User::create([
                'name' => $name,
                'userid' => $userid,
                'access_group_id' => $access_group_id,
                'email' => $email,
                'supervisor_code' => $supervisor_code,
                'password' => Hash::make('12345678')
            ]);
            echo "Inserted: $name ($userid)\n";
            $count++;
        } else {
            echo "Already exists: $userid or $email\n";
        }
    }
}
echo "Total inserted: $count\n";
