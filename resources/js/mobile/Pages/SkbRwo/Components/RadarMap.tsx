import React, { useState, useEffect, useMemo } from 'react';
import { MapContainer, TileLayer, Circle, Marker, Popup, useMap, LayerGroup } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { SkbRwoItem } from './StoreCard';
import { INCENTIVE_THRESHOLDS } from '../constants';
import { haversineDistance, formatDistance } from '../utils/geo';
import { ArrowTopRightOnSquareIcon, MapPinIcon, ShieldExclamationIcon, ArrowPathIcon } from '@heroicons/react/24/outline';
import { UserLocation } from '../hooks/useNearby';

// Fix untuk default icon leaflet di react-leaflet
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

export interface RadarMapProps {
    data: SkbRwoItem[];
    showToast: (msg: string, type?: 'success' | 'error') => void;
}

function ChangeView({ center, zoom }: { center: [number, number]; zoom: number }) {
    const map = useMap();
    useEffect(() => {
        map.setView(center, zoom);
        // Force leafet to recalculate size after mount to prevent missing tiles/markers
        setTimeout(() => {
            map.invalidateSize();
        }, 300);
    }, [center, zoom, map]);
    return null;
}

const createStaticIcon = (color: string) => {
    return L.divIcon({
        className: 'custom-pin-icon',
        html: `<svg viewBox="0 0 24 24" fill="${color}" stroke="white" stroke-width="1" width="32" height="32" style="filter: drop-shadow(0px 3px 3px rgba(0,0,0,0.3));">
                 <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
               </svg>`,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });
};

const PIN_ICONS = {
    GOLD: createStaticIcon('#eab308'),
    BLUE: createStaticIcon('#4f46e5'),
    RED: createStaticIcon('#ef4444')
};

export default function RadarMap({ data, showToast }: RadarMapProps) {
    const [userLocation, setUserLocation] = useState<UserLocation | null>(null);
    const [gpsError, setGpsError] = useState<string | null>(null);
    const [isLoadingGps, setIsLoadingGps] = useState(true);
    const [radiusKm, setRadiusKm] = useState(5);

    const getDynamicZoom = (radius: number) => {
        if (radius <= 5) return 13;
        if (radius <= 10) return 12;
        if (radius <= 20) return 11;
        if (radius <= 30) return 10;
        if (radius <= 50) return 9;
        return 8;
    };

    const requestGps = () => {
        setIsLoadingGps(true);
        setGpsError(null);

        if (typeof navigator === 'undefined' || !('geolocation' in navigator)) {
            setGpsError('GPS tidak tersedia di perangkat ini.');
            setIsLoadingGps(false);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                setUserLocation({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                });
                setIsLoadingGps(false);
            },
            (error) => {
                setIsLoadingGps(false);
                if (error.code === error.PERMISSION_DENIED) {
                    setGpsError('Izin GPS ditolak. Aktifkan izin lokasi untuk melihat peta Nearby.');
                } else if (error.code === error.TIMEOUT) {
                    setGpsError('Pencarian GPS timeout. Coba lagi di area terbuka.');
                } else {
                    setGpsError('Gagal mendapatkan lokasi GPS.');
                }
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
        );
    };

    useEffect(() => {
        requestGps();
    }, []);

    // Filter toko yang dalam radius 5km dan punya koordinat
    const storesInRadius = useMemo(() => {
        if (!userLocation) return [];

        return data.map(item => {
            const latStr = String(item.latitude || '').replace(',', '.').trim();
            const lngStr = String(item.longitude || '').replace(',', '.').trim();
            const lat = parseFloat(latStr);
            const lng = parseFloat(lngStr);

            if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) return null;

            const distance = haversineDistance(userLocation.lat, userLocation.lng, lat, lng);
            if (distance <= radiusKm) {
                return { ...item, distance, parsedLat: lat, parsedLng: lng };
            }
            return null;
        }).filter(Boolean) as (SkbRwoItem & { distance: number; parsedLat: number; parsedLng: number })[];
    }, [data, userLocation, radiusKm]);

    const getIconForTarget = (target?: number) => {
        const val = Number(target) || 0;
        if (val >= INCENTIVE_THRESHOLDS.TIER_1.minTarget) return PIN_ICONS.GOLD;
        if (val >= INCENTIVE_THRESHOLDS.TIER_2.minTarget) return PIN_ICONS.BLUE;
        return PIN_ICONS.RED;
    };

    if (isLoadingGps) {
        return (
            <div className="flex-1 flex flex-col items-center justify-center p-6 text-center animate-fade-in">
                <ArrowPathIcon className="w-12 h-12 text-indigo-400 animate-spin mb-4" />
                <h3 className="text-lg font-black text-slate-700">Mencari Sinyal GPS...</h3>
                <p className="text-sm text-slate-500 mt-2">Mohon tunggu, sedang menyesuaikan peta Nearby dengan lokasi Anda.</p>
            </div>
        );
    }

    if (gpsError || !userLocation) {
        return (
            <div className="flex-1 flex flex-col items-center justify-center p-6 text-center animate-fade-in">
                <div className="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center text-rose-400 mb-4">
                    <ShieldExclamationIcon className="w-10 h-10" />
                </div>
                <h3 className="text-lg font-black text-slate-700 mb-2">Aktifkan GPS</h3>
                <p className="text-sm text-slate-500 mb-6 max-w-xs">{gpsError || 'Fitur Nearby membutuhkan lokasi Anda untuk mencari toko terdekat.'}</p>
                <button 
                    onClick={requestGps}
                    className="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-sm shadow-indigo-200 hover:bg-indigo-700 transition-colors flex items-center gap-2"
                >
                    <MapPinIcon className="w-5 h-5" />
                    Coba Lagi
                </button>
            </div>
        );
    }

    return (
        <div className="flex-1 flex flex-col relative w-full animate-fade-in" style={{ minHeight: 'calc(100vh - 120px)' }}>
            <div className="absolute top-2 left-1/2 -translate-x-1/2 z-[400] flex items-center gap-2">
                <div className="bg-white/90 backdrop-blur-sm px-4 py-2 rounded-full shadow-md shadow-slate-200/50 border border-slate-100 flex items-center gap-2">
                    <div className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span className="text-[10px] font-bold tracking-wider uppercase text-slate-700 whitespace-nowrap">
                        {storesInRadius.length} Toko
                    </span>
                </div>
                
                <select 
                    value={radiusKm}
                    onChange={(e) => setRadiusKm(Number(e.target.value))}
                    className="bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full shadow-md shadow-slate-200/50 border border-slate-100 text-[10px] font-bold tracking-wider uppercase text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer appearance-none"
                    style={{ WebkitAppearance: 'none', MozAppearance: 'none' }}
                >
                    <option value={5}>Radius 5 KM</option>
                    <option value={10}>Radius 10 KM</option>
                    <option value={20}>Radius 20 KM</option>
                    <option value={30}>Radius 30 KM</option>
                    <option value={40}>Radius 40 KM</option>
                    <option value={50}>Radius 50 KM</option>
                    <option value={100}>Radius 100 KM</option>
                </select>
            </div>

            <button 
                onClick={requestGps}
                className="absolute top-2 right-2 z-[400] bg-white/90 backdrop-blur-sm p-2 rounded-full shadow-md shadow-slate-200/50 border border-slate-100 text-slate-500 hover:text-indigo-600 transition-colors"
                title="Refresh GPS"
            >
                <ArrowPathIcon className="w-5 h-5" />
            </button>
            
            <MapContainer 
                center={[userLocation.lat, userLocation.lng]} 
                zoom={getDynamicZoom(radiusKm)} 
                className="w-full h-full z-0 absolute inset-0"
                zoomControl={false}
                style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}
            >
                <TileLayer
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    attribution='&copy; OpenStreetMap'
                />
                
                <ChangeView center={[userLocation.lat, userLocation.lng]} zoom={getDynamicZoom(radiusKm)} />

                {/* Lingkaran Radius Aktif */}
                <Circle 
                    center={[userLocation.lat, userLocation.lng]} 
                    radius={radiusKm * 1000} 
                    pathOptions={{ color: '#6366f1', fillColor: '#6366f1', fillOpacity: 0.08, weight: 1 }} 
                />

                {/* Marker Toko */}
                <LayerGroup>
                    {storesInRadius.map(store => {
                        const val = Number(store.total_target) || 0;
                        let color = '#ef4444';
                        let icon = PIN_ICONS.RED;
                        if (val >= INCENTIVE_THRESHOLDS.TIER_1.minTarget) {
                            color = '#eab308';
                            icon = PIN_ICONS.GOLD;
                        } else if (val >= INCENTIVE_THRESHOLDS.TIER_2.minTarget) {
                            color = '#4f46e5';
                            icon = PIN_ICONS.BLUE;
                        }

                        let rewardText = INCENTIVE_THRESHOLDS.DEFAULT.rewardPct;
                        if (val >= INCENTIVE_THRESHOLDS.TIER_1.minTarget) rewardText = INCENTIVE_THRESHOLDS.TIER_1.rewardPct;
                        else if (val >= INCENTIVE_THRESHOLDS.TIER_2.minTarget) rewardText = INCENTIVE_THRESHOLDS.TIER_2.rewardPct;

                        return (
                            <Marker 
                                key={store.customer_code}
                                position={[store.parsedLat, store.parsedLng]} 
                                icon={icon}
                            >
                                <Popup className="radar-popup">
                                    <div className="flex flex-col gap-1 min-w-[160px]">
                                        <h3 className="font-black text-sm text-slate-800 leading-tight m-0">{store.customer_name}</h3>
                                        <p className="text-[10px] text-slate-400 font-medium m-0">{store.customer_code}</p>
                                        
                                        <div className="flex items-center gap-2 mt-1 mb-2">
                                            <span className="px-1.5 py-0.5 rounded text-[9px] font-bold text-white" style={{ backgroundColor: color }}>
                                                {rewardText} Reward
                                            </span>
                                            <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">
                                                {formatDistance(store.distance)}
                                            </span>
                                        </div>

                                        <a
                                            href={`https://www.google.com/maps/dir/?api=1&destination=${store.parsedLat},${store.parsedLng}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="flex items-center justify-center gap-1.5 w-full py-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 rounded-md font-bold text-[10px] transition-colors border border-sky-100 no-underline"
                                        >
                                            <ArrowTopRightOnSquareIcon className="w-3 h-3" /> Arahkan
                                        </a>
                                    </div>
                                </Popup>
                            </Marker>
                        );
                    })}
                </LayerGroup>
            </MapContainer>
            
            {/* Custom CSS untuk popup Leaflet agar lebih rapi */}
            <style>{`
                .leaflet-popup-content-wrapper {
                    border-radius: 12px;
                    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                }
                .leaflet-popup-content {
                    margin: 12px;
                }
                .leaflet-container a.leaflet-popup-close-button {
                    padding: 4px;
                    color: #94a3b8;
                }
                .custom-pin-icon {
                    background: transparent;
                    border: none;
                }
            `}</style>
        </div>
    );
}
