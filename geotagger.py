#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import pandas as pd
import time
import os
import glob
import sys
import json
from datetime import datetime

# Import Library Spasial (Polygon)
import geopandas as gpd
from shapely.geometry import Point

# --- KONFIGURASI PATH ---
BASE_DIR = "/var/www/newsiso/storage/app/private/geotag_jobs/"
INPUT_DIR = os.path.join(BASE_DIR, "input/")
OUTPUT_DIR = os.path.join(BASE_DIR, "output/")

# Path ke file Peta Polygon Anda
# Pastikan nama file .shp nya sesuai dengan yang Anda upload!
SHAPEFILE_PATH = "/var/www/newsiso/storage/app/private/spatial_data/Batas_Wilayah_KelurahanDesa_10K_AR.shp"

os.makedirs(INPUT_DIR, exist_ok=True)
os.makedirs(OUTPUT_DIR, exist_ok=True)

# LOAD PETA DIGITAL KE MEMORI (Hanya dilakukan 1x saat skrip dinyalakan)
print(f"[{datetime.now().strftime('%H:%M:%S')}] Memuat Peta Polygon (Harap tunggu, ini mungkin memakan waktu beberapa detik/menit)...")
try:
    GDF = gpd.read_file(SHAPEFILE_PATH)
    print(f"[{datetime.now().strftime('%H:%M:%S')}] Peta berhasil dimuat! Total Poligon: {len(GDF)}")
except Exception as e:
    print(f"ERROR memuat peta: {str(e)}")
    print("Pastikan file .shp, .shx, .dbf, dll sudah diletakkan di folder spatial_data!")
    GDF = None

def get_region_from_polygon(lat, lon):
    """Mencari wilayah (Desa, Kec, Kab) menggunakan sistem Polygon (Point-in-Polygon)"""
    if GDF is None:
        return '-', '-', '-'
    
    try:
        # Shapely menggunakan urutan (Longitude, Latitude)
        point = Point(lon, lat)
        
        # Cari baris poligon yang menutupi titik ini
        matches = GDF[GDF.contains(point)]
        
        if not matches.empty:
            match = matches.iloc[0]
            # Data BIG Indonesia umumnya menggunakan nama kolom standar ini:
            # NAMOBJ = Desa, WADMKC = Kecamatan, WADMKK = Kabupaten
            desa = match['NAMOBJ'] if 'NAMOBJ' in match else '-'
            kec = match['WADMKC'] if 'WADMKC' in match else '-'
            kab = match['WADMKK'] if 'WADMKK' in match else '-'
            return desa, kec, kab
    except Exception as e:
        print(f"   [Error Polygon: {str(e)}]")
        
    return '-', '-', '-'

def reverse_geocode(lat, lon):
    """Murni menggunakan data offline Polygon tanpa request internet"""
    if pd.isna(lat) or pd.isna(lon) or str(lat).strip() == '' or str(lon).strip() == '':
        return {'status': 'failed', 'village': '-', 'kecamatan': '-', 'city': '-', 'postcode': '-', 'address': 'Koordinat kosong'}

    # AMBIL DATA DARI PETA POLYGON (SUPER CEPAT & AKURAT)
    poly_desa, poly_kec, poly_kab = get_region_from_polygon(lat, lon)

    # Tentukan status berdasarkan hasil polygon
    if poly_desa != '-' or poly_kec != '-' or poly_kab != '-':
        status = 'success'
        error_msg = '-'
    else:
        status = 'failed'
        error_msg = 'Tidak masuk dalam area peta polygon'

    return {
        'status': status,
        'village': poly_desa,
        'kecamatan': poly_kec,
        'city': poly_kab,
        'postcode': '-',
        'address': error_msg
    }

def process_file(file_path):
    filename = os.path.basename(file_path)
    name_only, ext = os.path.splitext(filename)
    
    output_filename = f"result_{name_only}.xlsx"
    output_path = os.path.join(OUTPUT_DIR, output_filename)
    
    processing_path = file_path + ".processing"
    progress_path = file_path + ".progress" # File JSON untuk dibaca UI
    
    try:
        os.rename(file_path, processing_path)
    except OSError:
        return 

    print(f"[{datetime.now().strftime('%H:%M:%S')}] Memproses: {filename}")
    
    try:
        if ext.lower() == '.csv':
            try:
                df = pd.read_csv(processing_path, sep=None, engine='python', encoding='utf-8')
            except UnicodeDecodeError:
                df = pd.read_csv(processing_path, sep=None, engine='python', encoding='latin1')
        else:
            df = pd.read_excel(processing_path)

        df.columns = [str(c).strip().lower() for c in df.columns]
        
        if 'la' not in df.columns or 'lg' not in df.columns:
            print(f"ERROR: Kolom 'la' atau 'lg' tidak ditemukan di {filename}")
            os.rename(processing_path, file_path + ".error")
            return

        total_rows = len(df)
        print(f"Ditemukan {total_rows} baris data.")

        statuses, villages, kecamatans, cities, postcodes, addresses = [], [], [], [], [], []
        
        start_time = time.time() # Mulai stopwatch

        for index, row in df.iterrows():
            # (Hapus print per baris agar terminal tidak spam dan memakan waktu I/O)
            # print(f"  -> Memproses {index + 1}/{total_rows} (Lat: {row['la']}, Lon: {row['lg']})...", end="", flush=True)
            
            res = reverse_geocode(row['la'], row['lg'])
            
            statuses.append(res['status'])
            villages.append(res['village'])
            kecamatans.append(res['kecamatan'])
            cities.append(res['city'])
            postcodes.append(res['postcode'])
            addresses.append(res['address'])
            
            processed_count = index + 1
            
            # Tulis progress ke file JSON setiap 50 baris atau di akhir
            if processed_count % 50 == 0 or processed_count == total_rows:
                elapsed = time.time() - start_time
                rps = processed_count / elapsed if elapsed > 0 else 0
                eta = (total_rows - processed_count) / rps if rps > 0 else 0
                
                # Tulis ke file .progress
                with open(progress_path, 'w') as pf:
                    json.dump({
                        "processed": processed_count,
                        "total": total_rows,
                        "eta_seconds": int(eta)
                    }, pf)
                    
                # Update tampilan di terminal agar tetap informatif
                print(f"  -> Progres: {processed_count}/{total_rows} | ETA: {int(eta)} detik")

        df['Status Geotag'] = statuses
        df['Desa / Kelurahan'] = villages
        df['Kecamatan'] = kecamatans
        df['Kabupaten / Kota'] = cities
        df['Kode Pos'] = postcodes
        df['Alamat Lengkap (Reverse)'] = addresses

        print(f"[{datetime.now().strftime('%H:%M:%S')}] Menyimpan hasil ke: {output_path}")
        df.to_excel(output_path, index=False)
        
        # Bersihkan file sementara
        os.remove(processing_path)
        if os.path.exists(progress_path):
            os.remove(progress_path)
            
        print(f"Selesai memproses {filename}.\n")

    except Exception as e:
         print(f"GAGAL memproses {filename}: {str(e)}")
         os.rename(processing_path, file_path + ".error")
         if os.path.exists(progress_path):
             os.remove(progress_path)

def main():
    print("Geotagging Worker Started (MODE OFFLINE POLYGON). Menunggu file input...")
    try:
        while True:
            raw_files = glob.glob(os.path.join(INPUT_DIR, "*.xls*")) + glob.glob(os.path.join(INPUT_DIR, "*.csv"))
            # Abaikan file temp excel, file yang sedang diolah, file error, dan file progress
            input_files = [f for f in raw_files if not os.path.basename(f).startswith('~$') 
                           and not f.endswith('.processing') 
                           and not f.endswith('.error')
                           and not f.endswith('.progress')]
            
            for file_path in input_files:
                process_file(file_path)
            
            time.sleep(5)
    except KeyboardInterrupt:
        print("\nWorker dihentikan.")
        sys.exit(0)

if __name__ == "__main__":
    main()