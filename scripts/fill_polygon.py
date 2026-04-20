import sys
import pandas as pd
import geopandas as gpd
from shapely.geometry import Point
import os

def process_excel(excel_path):
    # Pastikan path ini sudah mengarah ke folder sisodev
    shp_path = "/var/www/sisodev/storage/app/private/spatial_data/Batas_Wilayah_KelurahanDesa_10K_AR.shp"
    
    if not os.path.exists(shp_path):
        print(f"Error: File Shapefile tidak ditemukan di: {shp_path}")
        sys.exit(1)

    try:
        # Load Polygon Map (Sangat Cepat)
        gdf = gpd.read_file(shp_path)
        
        # Load Excel File
        df = pd.read_excel(excel_path)
        
        # Standarisasi nama kolom (cari kolom latitude dan longitude)
        cols = {str(c).strip().lower(): c for c in df.columns}
        lat_col = cols.get('latitude') or cols.get('la') or cols.get('lat')
        lng_col = cols.get('longitude') or cols.get('lg') or cols.get('lng')

        if not lat_col or not lng_col:
            print("Warning: Kolom Latitude/Longitude tidak ditemukan di Excel.")
            sys.exit(0) # Keluar dengan aman agar Laravel tetap memproses sisa datanya

        # Mengubah data Latitude & Longitude menjadi Numerik (Float)
        df['temp_lat'] = pd.to_numeric(df[lat_col].astype(str).str.replace(',', '.'), errors='coerce')
        df['temp_lng'] = pd.to_numeric(df[lng_col].astype(str).str.replace(',', '.'), errors='coerce')

        # Membuat Point Geometry
        # MENGHINDARI PENGGUNAAN INDEX ANGKA AGAR TIDAK HILANG SAAT DI-COPY
        geometry = []
        for lng, lat in zip(df['temp_lng'], df['temp_lat']):
            if pd.notnull(lng) and pd.notnull(lat):
                geometry.append(Point(lng, lat))
            else:
                geometry.append(None)
        
        # Buat GeoDataFrame dari Excel
        points_gdf = gpd.GeoDataFrame(df, geometry=geometry, crs=gdf.crs)
        
        # SPATIAL JOIN (Mencocokkan Titik ke Polygon - Offline & Instan)
        joined = gpd.sjoin(points_gdf, gdf, how="left", predicate="within")
        
        # Timpa/Isi kolom kecamatan dan desa di DataFrame asli
        df['kecamatan'] = joined['WADMKC'].fillna(df.get('kecamatan', ''))
        df['desa'] = joined['NAMOBJ'].fillna(df.get('desa', ''))

        # Bersihkan kolom sementara
        df = df.drop(columns=['temp_lat', 'temp_lng'])
        
        # Simpan kembali ke file Excel yang sama
        df.to_excel(excel_path, index=False)
        
        print("Success: Polygon data merged.")
        sys.exit(0)
        
    except Exception as e:
        print(f"Error: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Error: Path Excel tidak diberikan.")
        sys.exit(1)
        
    # KUNCI PERBAIKAN: 
    # Menggunakan .pop() untuk mengambil parameter path terakhir 
    # agar tidak perlu menggunakan kurung siku (yang rentan hilang saat copy-paste).
    exact_file_path = sys.argv.pop()
    process_excel(exact_file_path)