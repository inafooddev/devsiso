<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('userid')->unique()->after('id');
            
            // Ubah dari string menjadi json atau text
            $table->json('region_code')->nullable()->after('email'); 
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            if (Schema::hasColumn('users', 'userid')) {
                $table->dropColumn('userid');
            }

            if (Schema::hasColumn('users', 'region_code')) {
                $table->dropColumn('region_code');
            }
        });
    }
};