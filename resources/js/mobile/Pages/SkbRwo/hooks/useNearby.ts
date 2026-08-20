import { useState, useCallback } from 'react';
import { haversineDistance, NEARBY_RADIUS_KM } from '../utils/geo';
import { SkbRwoItem } from '../Components/StoreCard';

export interface UserLocation {
    lat: number;
    lng: number;
}

interface UseNearbyReturn {
    isNearbyActive: boolean;
    isLoadingGps: boolean;
    userLocation: UserLocation | null;
    gpsSupported: boolean;
    toggleNearby: (showToast: (msg: string, type?: 'success' | 'error') => void) => void;
    getDistance: (item: SkbRwoItem) => number | null;
}

export function useNearby(): UseNearbyReturn {
    const [isNearbyActive, setIsNearbyActive] = useState(false);
    const [isLoadingGps, setIsLoadingGps] = useState(false);
    const [userLocation, setUserLocation] = useState<UserLocation | null>(null);

    // Cek apakah browser/device mendukung GPS
    const gpsSupported = typeof navigator !== 'undefined' && 'geolocation' in navigator;

    const toggleNearby = useCallback(
        (showToast: (msg: string, type?: 'success' | 'error') => void) => {
            // Jika sudah aktif → matikan, kembali ke tampilan default
            if (isNearbyActive) {
                setIsNearbyActive(false);
                setUserLocation(null);
                return;
            }

            if (!gpsSupported) {
                showToast('GPS tidak tersedia di perangkat ini.', 'error');
                return;
            }

            setIsLoadingGps(true);

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    setUserLocation({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    });
                    setIsNearbyActive(true);
                    setIsLoadingGps(false);
                    showToast('GPS aktif — menampilkan toko dalam 3 km.', 'success');
                },
                (error) => {
                    setIsLoadingGps(false);
                    setIsNearbyActive(false);
                    if (error.code === error.PERMISSION_DENIED) {
                        showToast('Izin GPS ditolak. Aktifkan di pengaturan browser.', 'error');
                    } else if (error.code === error.TIMEOUT) {
                        showToast('GPS timeout. Coba lagi di tempat terbuka.', 'error');
                    } else {
                        showToast('Gagal mendapatkan lokasi GPS.', 'error');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
            );
        },
        [isNearbyActive, gpsSupported]
    );

    /**
     * Menghitung jarak item toko ke user.
     * Return null jika GPS tidak aktif atau koordinat toko tidak valid.
     */
    const getDistance = useCallback(
        (item: SkbRwoItem): number | null => {
            if (!isNearbyActive || !userLocation) return null;

            const lat = parseFloat(String(item.latitude || ''));
            const lng = parseFloat(String(item.longitude || ''));

            if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) return null;

            return haversineDistance(userLocation.lat, userLocation.lng, lat, lng);
        },
        [isNearbyActive, userLocation]
    );

    return {
        isNearbyActive,
        isLoadingGps,
        userLocation,
        gpsSupported,
        toggleNearby,
        getDistance,
    };
}
