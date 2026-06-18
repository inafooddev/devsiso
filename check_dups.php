echo "--- master_distributors duplicates (active) ---\n";
dump(DB::table('master_distributors')->where('is_active', true)->select('distributor_code', DB::raw('count(*) as total'))->groupBy('distributor_code')->havingRaw('count(*) > 1')->get()->toArray());
echo "--- distributor_implementasi_eskalink duplicates (implementasi = Y) ---\n";
dump(DB::table('distributor_implementasi_eskalink')->where('implementasi', 'Y')->select('distributor_code', 'eskalink_code', DB::raw('count(*) as total'))->groupBy('distributor_code', 'eskalink_code')->havingRaw('count(*) > 1')->get()->toArray());
echo "--- nominal_qc_dist duplicates ---\n";
dump(DB::table('nominal_qc_dist')->select('distributor_code', 'tanggal', DB::raw('count(*) as total'))->groupBy('distributor_code', 'tanggal')->havingRaw('count(*) > 1')->get()->toArray());
