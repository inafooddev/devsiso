import React, { useState, useEffect, useRef, Component } from "react";
import { Head, router, useForm } from "@inertiajs/react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import {
    MagnifyingGlassIcon,
    XMarkIcon,
    MapPinIcon,
    ShieldCheckIcon,
    AdjustmentsHorizontalIcon,
    XCircleIcon,
    CheckCircleIcon,
    InformationCircleIcon,
    MapIcon,
    CameraIcon,
    EyeIcon,
    PencilIcon,
    ChartPieIcon,
    ListBulletIcon,
    TrashIcon,
    ClockIcon,
} from "@heroicons/react/24/outline";
import {
    ShieldExclamationIcon,
    BuildingStorefrontIcon,
} from "@heroicons/react/24/solid";

const deg2rad = (deg) => {
    return deg * (Math.PI / 180);
};

const getDistance = (lat1, lon1, lat2, lon2) => {
    if (!lat1 || !lon1 || !lat2 || !lon2) return Infinity;
    const R = 6371; // Radius of the earth in km
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) *
            Math.cos(deg2rad(lat2)) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const d = R * c; // Distance in km
    return d;
};

const calcDistanceText = (lat1, lon1, lat2, lon2) => {
    if (!lat1 || !lon1 || !lat2 || !lon2) return null;
    const p1 = parseFloat(lat1);
    const p2 = parseFloat(lon1);
    const p3 = parseFloat(lat2);
    const p4 = parseFloat(lon2);
    if (isNaN(p1) || isNaN(p2) || isNaN(p3) || isNaN(p4)) return null;

    const distKm = getDistance(p1, p2, p3, p4);
    if (distKm < 1) {
        return Math.round(distKm * 1000) + " m";
    }
    return distKm.toFixed(2) + " km";
};

class ErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null, errorInfo: null };
    }
    static getDerivedStateFromError(error) {
        return { hasError: true };
    }
    componentDidCatch(error, errorInfo) {
        this.setState({ error, errorInfo });
        console.error("ErrorBoundary caught an error", error, errorInfo);
    }
    render() {
        if (this.state.hasError) {
            return (
                <div className="p-4 m-4 bg-red-100 border border-red-400 text-red-700 rounded-lg overflow-y-auto max-h-screen">
                    <h2 className="text-lg font-bold mb-2">
                        Terjadi Kesalahan (Runtime Error)
                    </h2>
                    <p className="text-sm font-semibold mb-2">
                        Mohon screenshot error ini dan kirimkan:
                    </p>
                    <details
                        className="whitespace-pre-wrap text-[10px] font-mono bg-white p-2 rounded border border-red-200"
                        open
                    >
                        <summary className="font-bold cursor-pointer text-red-800 mb-1">
                            Error Message:
                        </summary>
                        {this.state.error && this.state.error.toString()}
                        <hr className="my-2 border-red-200" />
                        <span className="font-bold text-red-800">
                            Component Stack:
                        </span>
                        <br />
                        {this.state.errorInfo &&
                            this.state.errorInfo.componentStack}
                    </details>
                    <button
                        onClick={() => window.location.reload()}
                        className="mt-4 px-4 py-2 bg-red-600 text-white rounded font-bold text-xs"
                    >
                        Muat Ulang Halaman
                    </button>
                </div>
            );
        }
        return this.props.children;
    }
}

export default function Index({ outlets, auditReports = [], sessionAuditor }) {
    const [showLogoutModal, setShowLogoutModal] = useState(false);
    const [isFormTouched, setIsFormTouched] = useState(false);


    const handleLogoutAuditor = () => {
        setShowLogoutModal(true);
    };

    const confirmLogoutAuditor = () => {
        router.post(
            "/mobile/audit/logout",
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setShowLogoutModal(false);
                    showToast("Berhasil keluar dari aplikasi.", "success");
                },
            },
        );
    };

    const [reportSearch, setReportSearch] = useState("");
    const [reportSort, setReportSort] = useState("newest"); // "newest", "oldest", "name"
    const allMyReports = auditReports || [];
    const filteredReports = (allMyReports || [])
        .filter((r) => {
            if (!reportSearch) return true;
            const q = reportSearch.toLowerCase();
            return (
                r.customer_name?.toLowerCase().includes(q) ||
                r.customer_code?.toLowerCase().includes(q) ||
                r.cabang?.toLowerCase().includes(q)
            );
        })
        .sort((a, b) => {
            if (reportSort === "oldest") {
                return new Date(a.created_at || 0) - new Date(b.created_at || 0);
            } else if (reportSort === "name") {
                return (a.customer_name || "").localeCompare(b.customer_name || "");
            }
            return new Date(b.created_at || 0) - new Date(a.created_at || 0);
        });

    // Draft states (for UI inputs only)
    const [search, setSearch] = useState("");
    const [selectedRegion, setSelectedRegion] = useState("");
    const [selectedArea, setSelectedArea] = useState("");
    const [selectedDistributor, setSelectedDistributor] = useState("");

    // Applied states (triggers the actual data filtering)
    const [appliedSearch, setAppliedSearch] = useState("");
    const [appliedRegion, setAppliedRegion] = useState("");
    const [appliedArea, setAppliedArea] = useState("");
    const [appliedDistributor, setAppliedDistributor] = useState("");

    const [filteredOutlets, setFilteredOutlets] = useState([]);
    const [showFiltersSheet, setShowFiltersSheet] = useState(false);
    const [detailOutlet, setDetailOutlet] = useState(null);
    const [zoomedImage, setZoomedImage] = useState(null);
    const [isGettingLocation, setIsGettingLocation] = useState(false);
    const [toast, setToast] = useState(null);
    const [activeTab, setActiveTab] = useState("list"); // 'list' or 'report'
    const [userLocation, setUserLocation] = useState(null);
    const userLocationRef = useRef(null);
    const [gpsStatus, setGpsStatus] = useState("loading"); // 'loading', 'success', 'error'
    const isSubmittingRef = useRef(false);

    const auditMapContainerRef = useRef(null);
    const auditLeafletMapRef = useRef(null);
    const auditMarkersRef = useRef({ master: null, audit: null, line: null });

    useEffect(() => {
        let isMounted = true;

        const timeoutId = setTimeout(() => {
            if (isMounted && !userLocationRef.current) {
                setGpsStatus("error");
            }
        }, 10000); // 10 seconds check

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    clearTimeout(timeoutId);
                    if (isMounted) {
                        const newLocation = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                        };
                        setUserLocation(newLocation);
                        userLocationRef.current = newLocation;
                        setGpsStatus("success");
                    }
                },
                (error) => {
                    clearTimeout(timeoutId);
                    console.warn(
                        "Could not get current position for nearest stores",
                        error,
                    );
                    if (isMounted) {
                        setGpsStatus("error");
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 10000 },
            );
        } else {
            clearTimeout(timeoutId);
            if (isMounted) {
                setGpsStatus("error");
            }
        }

        return () => {
            isMounted = false;
            clearTimeout(timeoutId);
        };
    }, []);

    const showToast = (message, type = "success") => {
        setToast({ message, type });
        setTimeout(() => setToast(null), 3000);
    };

    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
        isDirty,
        transform,
    } = useForm({
        customer_code: "",
        distributor_code: "",
        customer_name: "",
        customer_address: "",
        auditor: sessionAuditor || "",
        keterangan_hasil_audit: "",
        is_toko_fisik: false,
        is_nama_pemilik: false,
        is_nama_ktp: false,
        is_nik_ktp: false,
        is_no_hp: false,
        is_no_rekening: false,
        is_an_rekening: false,
        is_titik_koordinat: false,
        latitude: "",
        longitude: "",
        foto_audit1: null,
        foto_audit2: null,
        foto_audit3: null,
        foto_audit4: null,
        foto_audit5: null,
        foto_audit6: null,
        foto_audit7: null,
        foto_audit8: null,
    });

    useEffect(() => {
        if (!detailOutlet || !auditMapContainerRef.current) return;

        const masterLat = parseFloat(detailOutlet.master_latitude);
        const masterLng = parseFloat(detailOutlet.master_longitude);
        const auditLat = parseFloat(data.latitude);
        const auditLng = parseFloat(data.longitude);

        const hasMaster = !isNaN(masterLat) && !isNaN(masterLng);
        const hasAudit = !isNaN(auditLat) && !isNaN(auditLng);

        if (!hasMaster && !hasAudit) return;

        let map = auditLeafletMapRef.current;

        try {
            if (
                auditMapContainerRef.current &&
                auditMapContainerRef.current._leaflet_id
            ) {
                auditMapContainerRef.current._leaflet_id = null;
            }

            if (!map) {
                map = L.map(auditMapContainerRef.current, {
                    attributionControl: false,
                    zoomControl: false,
                    dragging: false,
                    scrollWheelZoom: false,
                    doubleClickZoom: false,
                    touchZoom: false,
                    boxZoom: false,
                    keyboard: false,
                });
                L.tileLayer(
                    "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
                    {
                        maxZoom: 20,
                    },
                ).addTo(map);
                auditLeafletMapRef.current = map;
            }

            const m = auditMarkersRef.current;

            if (m.master) {
                try {
                    map.removeLayer(m.master);
                } catch (e) {}
                m.master = null;
            }
            if (m.audit) {
                try {
                    map.removeLayer(m.audit);
                } catch (e) {}
                m.audit = null;
            }
            if (m.line) {
                try {
                    map.removeLayer(m.line);
                } catch (e) {}
                m.line = null;
            }

            const masterIcon = L.divIcon({
                className: "custom-marker-master",
                html: `<div style="background-color: #2563eb; color: white; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3); font-weight: bold; font-size: 11px;">M</div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 13],
            });

            const auditIcon = L.divIcon({
                className: "custom-marker-audit",
                html: `<div style="background-color: #059669; color: white; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3); font-weight: bold; font-size: 11px;">A</div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 13],
            });

            const bounds = L.latLngBounds([]);

            if (hasMaster) {
                m.master = L.marker([masterLat, masterLng], {
                    icon: masterIcon,
                })
                    .bindPopup(
                        `<b style="font-size:11px;">Titik Master Outlet</b>`,
                    )
                    .addTo(map);
                bounds.extend([masterLat, masterLng]);
            }

            if (hasAudit) {
                m.audit = L.marker([auditLat, auditLng], { icon: auditIcon })
                    .bindPopup(
                        `<b style="font-size:11px;">Titik Hasil Audit</b>`,
                    )
                    .addTo(map);
                bounds.extend([auditLat, auditLng]);
            }

            if (hasMaster && hasAudit) {
                m.line = L.polyline(
                    [
                        [masterLat, masterLng],
                        [auditLat, auditLng],
                    ],
                    {
                        color: "#6366f1",
                        weight: 3,
                        dashArray: "6, 6",
                        opacity: 0.8,
                    },
                ).addTo(map);

                map.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
            } else if (hasMaster) {
                map.setView([masterLat, masterLng], 15);
            } else if (hasAudit) {
                map.setView([auditLat, auditLng], 15);
            }

            setTimeout(() => {
                if (auditLeafletMapRef.current) {
                    try {
                        auditLeafletMapRef.current.invalidateSize();
                    } catch (e) {}
                }
            }, 300);
        } catch (err) {
            console.error("Leaflet map initialization error:", err);
        }

        return () => {
            if (auditLeafletMapRef.current) {
                try {
                    auditLeafletMapRef.current.remove();
                } catch (e) {}
                auditLeafletMapRef.current = null;
            }
            auditMarkersRef.current = { master: null, audit: null, line: null };
        };
    }, [detailOutlet, data.latitude, data.longitude]);
    // Preview URLs removed to save memory on mobile devices

    const [showDiscardModal, setShowDiscardModal] = useState(false);

    // Intercept hardware back button for Detail Modal
    useEffect(() => {
        const handlePopState = (e) => {
            if (detailOutlet) {
                if (isFormTouched) {
                    // User tried to go back but form is touched. Push state back to prevent leaving
                    window.history.pushState({ modal: 'detail' }, '');
                    setShowDiscardModal(true);
                } else {
                    // Close the modal directly
                    if (auditLeafletMapRef.current) {
                        auditLeafletMapRef.current.remove();
                        auditLeafletMapRef.current = null;
                    }
                    auditMarkersRef.current = { master: null, audit: null, line: null };
                    setDetailOutlet(null);
                    setShowNoPhotoWarning(false);
                }
            }
        };

        window.addEventListener('popstate', handlePopState);
        return () => window.removeEventListener('popstate', handlePopState);
    }, [detailOutlet, isFormTouched]);

    // Global listener for HTTP 500 Server Errors to prevent silent failures
    useEffect(() => {
        const removeErrorListener = router.on("exception", (event) => {
            event.preventDefault(); // Prevent default Inertia error modal
            setIsGettingLocation(false);
            isSubmittingRef.current = false;
            showToast(
                "Terjadi kesalahan sistem (Server Error). Pastikan foto tidak lebih dari 20MB dan berformat JPG/PNG.",
                "error"
            );
        });
        return () => removeErrorListener();
    }, []);
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showNoPhotoWarning, setShowNoPhotoWarning] = useState(false);
    const [deletingReport, setDeletingReport] = useState(null);
    const [isDeletingCode, setIsDeletingCode] = useState(null);
    const [totalFilteredCount, setTotalFilteredCount] = useState(0);
    const [displayLimit, setDisplayLimit] = useState(30);
    const [isExporting, setIsExporting] = useState(false);
    const [gpsError, setGpsError] = useState(null);
    const [showExportModal, setShowExportModal] = useState(false);
    const [exportStartDate, setExportStartDate] = useState("");
    const [exportEndDate, setExportEndDate] = useState("");

    const isAnyProcessLoading =
        processing ||
        isGettingLocation ||
        isDeletingCode !== null ||
        isExporting;

    useEffect(() => {
        if (sessionAuditor) {
            setData("auditor", sessionAuditor);
        }
    }, [sessionAuditor]);

    const handleFileChange = (field, file) => {
        if (!file) {
            setData(field, null);
            return;
        }

        if (file.size > 20 * 1024 * 1024) {
            showToast(`Ukuran file (${(file.size / 1024 / 1024).toFixed(1)}MB) melebihi batas maksimal 20MB.`, "error");
            return;
        }

        if (file.type.startsWith("image/") && file.size > 1024 * 1024) {
            // Compress if > 1MB
            const img = new Image();
            const objectUrl = URL.createObjectURL(file);

            img.onload = () => {
                URL.revokeObjectURL(objectUrl); // Clean up immediately

                const canvas = document.createElement("canvas");
                let width = img.width;
                let height = img.height;

                const MAX_SIZE = 1280;
                if (width > height) {
                    if (width > MAX_SIZE) {
                        height = Math.round((height * MAX_SIZE) / width);
                        width = MAX_SIZE;
                    }
                } else {
                    if (height > MAX_SIZE) {
                        width = Math.round((width * MAX_SIZE) / height);
                        height = MAX_SIZE;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (blob) {
                            const newFile = new File([blob], file.name, {
                                type: "image/jpeg",
                                lastModified: Date.now(),
                            });
                            setIsFormTouched(true);
                            setData(field, newFile);
                        } else {
                            // Fallback if compression fails
                            setIsFormTouched(true);
                            setData(field, file);
                        }
                    },
                    "image/jpeg",
                    0.7,
                );
            };

            img.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                setIsFormTouched(true);
                setData(field, file); // Fallback
            };

            img.src = objectUrl;
        } else {
            setIsFormTouched(true);
            setData(field, file);
        }
    };

    const openDetail = (outlet) => {
        setIsFormTouched(false);
        setDetailOutlet(outlet);
        window.history.pushState({ modal: 'detail' }, '');
        setShowNoPhotoWarning(false);
        setZoomedImage(null);

        // Reset file inputs explicitly just in case
        for (let i = 1; i <= 8; i++) {
            const fi = document.getElementById(`fileInputAudit${i}`);
            if (fi) fi.value = "";
        }

        setData({
            customer_code: outlet.customer_code,
            distributor_code: outlet.distributor_code,
            customer_name: outlet.customer_name,
            customer_address: outlet.customer_address,
            auditor: sessionAuditor || outlet.auditor || "",
            keterangan_hasil_audit: outlet.keterangan_hasil_audit || "",
            is_toko_fisik: Boolean(outlet.is_toko_fisik),
            is_nama_pemilik: Boolean(outlet.is_nama_pemilik),
            is_nama_ktp: Boolean(outlet.is_nama_ktp),
            is_nik_ktp: Boolean(outlet.is_nik_ktp),
            is_no_hp: Boolean(outlet.is_no_hp),
            is_no_rekening: Boolean(outlet.is_no_rekening),
            is_an_rekening: Boolean(outlet.is_an_rekening),
            is_titik_koordinat: Boolean(outlet.is_titik_koordinat),
            latitude: outlet.audit_latitude || "",
            longitude: outlet.audit_longitude || "",
            foto_audit1: null,
            foto_audit2: null,
            foto_audit3: null,
            foto_audit4: null,
            foto_audit5: null,
            foto_audit6: null,
            foto_audit7: null,
            foto_audit8: null,
        });
    };

    const fetchCurrentLocation = () => {
        setIsGettingLocation(true);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    setData((prev) => ({
                        ...prev,
                        latitude: position.coords.latitude.toString(),
                        longitude: position.coords.longitude.toString(),
                    }));
                    setIsGettingLocation(false);
                    showToast("Lokasi GPS berhasil diambil!", "success");
                },
                (error) => {
                    console.error("GPS Error:", error);
                    setIsGettingLocation(false);
                    let errMsg = "Gagal mengambil lokasi.";
                    if (error.code === 1)
                        errMsg = "Izin GPS ditolak oleh perangkat.";
                    else if (error.code === 2)
                        errMsg = "Posisi GPS tidak tersedia.";
                    else if (error.code === 3)
                        errMsg = "Waktu pengambilan GPS habis.";
                    showToast(errMsg, "error");
                },
                { enableHighAccuracy: true, timeout: 7000, maximumAge: 0 },
            );
        } else {
            setIsGettingLocation(false);
            showToast("Browser Anda tidak mendukung GPS.", "error");
        }
    };

    const proceedSubmit = () => {
        // If coordinate is already retrieved, submit directly
        if (
            data.latitude &&
            data.longitude &&
            data.latitude !== "0" &&
            data.longitude !== "0"
        ) {
            executeSubmit(data.latitude, data.longitude);
            return;
        }

        setIsGettingLocation(true);
        setGpsError(null);
        let isSubmitted = false;

        if (!navigator.geolocation) {
            setIsGettingLocation(false);
            setGpsError("unavailable");
            isSubmittingRef.current = false;
            showToast("Perangkat atau browser Anda tidak mendukung GPS.", "error");
            return;
        }

        const locationTimeout = setTimeout(() => {
            if (isSubmitted) return;
            isSubmitted = true;
            setIsGettingLocation(false);
            setGpsError("timeout");
            isSubmittingRef.current = false;
            showToast("Waktu pencarian GPS habis. Sinyal GPS lemah.", "error");
        }, 5000);

        navigator.geolocation.getCurrentPosition(
            (position) => {
                if (isSubmitted) return;
                isSubmitted = true;
                clearTimeout(locationTimeout);
                executeSubmit(
                    position.coords.latitude,
                    position.coords.longitude,
                );
            },
            (error) => {
                if (isSubmitted) return;
                isSubmitted = true;
                clearTimeout(locationTimeout);
                setIsGettingLocation(false);
                setGpsError(error.code === 1 ? "denied" : "unavailable");
                isSubmittingRef.current = false;
                showToast(
                    error.code === 1 
                        ? "Izin GPS ditolak. Silakan izinkan akses lokasi." 
                        : "Gagal mendapatkan lokasi GPS.", 
                    "error"
                );
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 10000 },
        );
    };

    const submitAudit = (e) => {
        e.preventDefault();

        if (isSubmittingRef.current || processing) return;
        isSubmittingRef.current = true;

        // Warning no photo
        const hasPhoto = [1, 2, 3, 4, 5, 6, 7, 8].some(i => data[`foto_audit${i}`] || detailOutlet?.[`foto_audit${i}`]);
        
        if (!hasPhoto && !showNoPhotoWarning) {
            setShowNoPhotoWarning(true);
            isSubmittingRef.current = false;
            showToast("Foto bukti belum dilampirkan. Mohon periksa kembali.", "warning");
            return;
        }

        proceedSubmit();
    };

    const executeSubmit = (lat, lng) => {
        transform((currentData) => ({
            ...currentData,
            latitude: lat || currentData.latitude,
            longitude: lng || currentData.longitude,
        }));

        post("/mobile/audit", {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                setDetailOutlet(null);
                if (window.history.state?.modal === 'detail') {
                    window.history.back();
                }
                reset();
                setIsFormTouched(false);
                setIsGettingLocation(false);
                isSubmittingRef.current = false;
                setShowSuccessModal(true);
            },
            onError: (errors) => {
                setIsGettingLocation(false);
                isSubmittingRef.current = false;
                console.error("Validation Error:", errors);
                const firstError = Object.values(errors)[0];
                showToast(
                    firstError || "Gagal menyimpan. Pastikan semua data wajib telah diisi.",
                    "error",
                );
            },
            onFinish: () => {
                setIsGettingLocation(false);
                isSubmittingRef.current = false;
            },
        });
    };

    // Generate dropdown options dynamically based on DRAFT states
    const regions = [
        ...new Set((outlets || []).map((o) => o.region_name).filter(Boolean)),
    ].sort();
    const areas = [
        ...new Set(
            (outlets || [])
                .filter(
                    (o) => !selectedRegion || o.region_name === selectedRegion,
                )
                .map((o) => o.area_name)
                .filter(Boolean),
        ),
    ].sort();
    const distributors = [
        ...new Set(
            (outlets || [])
                .filter(
                    (o) =>
                        (!selectedRegion || o.region_name === selectedRegion) &&
                        (!selectedArea || o.area_name === selectedArea),
                )
                .map((o) => o.distributor_name)
                .filter(Boolean),
        ),
    ].sort();

    const isFiltered =
        appliedSearch || appliedRegion || appliedArea || appliedDistributor;

    // Heavy filtering logic only runs when APPLIED states change
    useEffect(() => {
        if (!isFiltered) {
            if (userLocation && outlets && outlets.length > 0) {
                const sorted = [...outlets]
                    .map((o) => {
                        const dist = getDistance(
                            userLocation.latitude,
                            userLocation.longitude,
                            parseFloat(o.master_latitude),
                            parseFloat(o.master_longitude),
                        );
                        return { ...o, distance: dist };
                    })
                    .filter((o) => o.distance <= 5) // Filter radius 5km
                    .sort((a, b) => a.distance - b.distance);
                setDisplayLimit(30);
                setFilteredOutlets(sorted);
            } else {
                setFilteredOutlets(outlets || []);
            }
            return;
        }

        let result = outlets || [];

        if (appliedRegion)
            result = result.filter((o) => o.region_name === appliedRegion);
        if (appliedArea)
            result = result.filter((o) => o.area_name === appliedArea);
        if (appliedDistributor)
            result = result.filter(
                (o) => o.distributor_name === appliedDistributor,
            );

        if (appliedSearch) {
            const q = appliedSearch.toLowerCase();
            result = result.filter(
                (o) =>
                    (o.customer_name &&
                        o.customer_name.toLowerCase().includes(q)) ||
                    (o.customer_code &&
                        o.customer_code.toLowerCase().includes(q)) ||
                    (o.distributor_name &&
                        o.distributor_name.toLowerCase().includes(q)),
            );
        }

        // Simpan semua hasil, render dibatasi oleh displayLimit
        setDisplayLimit(30);
        setTotalFilteredCount(result.length);
        setFilteredOutlets(result);
    }, [
        appliedSearch,
        appliedRegion,
        appliedArea,
        appliedDistributor,
        outlets,
        isFiltered,
        userLocation,
    ]);

    // Handlers
    const applyFilters = () => {
        setAppliedRegion(selectedRegion);
        setAppliedArea(selectedArea);
        setAppliedDistributor(selectedDistributor);
        setShowFiltersSheet(false);
    };

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        setAppliedSearch(search);
        if (document.activeElement) {
            document.activeElement.blur();
        }
    };

    const clearSearch = () => {
        setSearch("");
        setAppliedSearch("");
    };

    const resetFilters = () => {
        setSelectedRegion("");
        setSelectedArea("");
        setSelectedDistributor("");
        setAppliedRegion("");
        setAppliedArea("");
        setAppliedDistributor("");
        // Do not clear search text here, so search results are preserved
    };

    const openDetailFromReport = (report, scrollToForm = false) => {
        let outlet = (outlets || []).find(
            (o) => o.customer_code === report.customer_code,
        );
        if (!outlet) {
            // Fallback: Construct outlet object from report data if missing in outlets list
            outlet = {
                customer_code: report.customer_code,
                distributor_code: report.distributor_code || "",
                customer_name: report.customer_name,
                customer_address: report.customer_address || "",
                auditor: report.auditor || "",
                keterangan_hasil_audit: report.keterangan_hasil_audit || "",
                is_toko_fisik: Boolean(report.is_toko_fisik),
                is_nama_pemilik: Boolean(report.is_nama_pemilik),
                is_nama_ktp: Boolean(report.is_nama_ktp),
                is_nik_ktp: Boolean(report.is_nik_ktp),
                is_no_hp: Boolean(report.is_no_hp),
                is_no_rekening: Boolean(report.is_no_rekening),
                is_an_rekening: Boolean(report.is_an_rekening),
                is_titik_koordinat: Boolean(report.is_titik_koordinat),
                audit_latitude: report.latitude || "",
                audit_longitude: report.longitude || "",
                status_audit: "Sudah",
                status_approval: report.status_approval || "Pending",
                alasan_reject: report.alasan_reject || null,
                foto_audit1: report.foto_audit1,
                foto_audit2: report.foto_audit2,
                foto_audit3: report.foto_audit3,
                foto_audit4: report.foto_audit4,
                foto_audit5: report.foto_audit5,
                foto_audit6: report.foto_audit6,
                foto_audit7: report.foto_audit7,
                foto_audit8: report.foto_audit8,
            };
        }
        openDetail(outlet);
        if (scrollToForm) {
            setTimeout(() => {
                const formEl = document.getElementById(
                    "audit-form-container",
                );
                if (formEl) {
                    const scrollContainer = formEl.closest(".overflow-y-auto");
                    if (scrollContainer) {
                        scrollContainer.scrollTo({
                            top: formEl.offsetTop - 20,
                            behavior: "smooth",
                        });
                    } else {
                        formEl.scrollIntoView({ behavior: "smooth" });
                    }
                }
            }, 300);
        }
    };

    const requestDeleteReport = (report) => {
        setDeletingReport(report);
    };

    const confirmDeleteReport = () => {
        if (!deletingReport) return;
        setIsDeletingCode(deletingReport.customer_code);

        router.delete(`/mobile/audit/${deletingReport.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showToast("Hasil audit berhasil dihapus!", "success");
                setDeletingReport(null);
            },
            onError: () => {
                showToast("Gagal menghapus hasil audit.", "error");
                setDeletingReport(null);
            },
            onFinish: () => {
                setIsDeletingCode(null);
            },
        });
    };

    const handleCloseDetail = () => {
        if (isFormTouched) {
            setShowDiscardModal(true);
        } else {
            window.history.back(); // This triggers popstate, which closes the modal
        }
    };

    const confirmDiscard = () => {
        setIsFormTouched(false);
        setShowDiscardModal(false);
        // Biarkan state ter-update dulu di siklus React berikutnya, baru trigger back
        setTimeout(() => {
            window.history.back();
        }, 0);
    };

    return (
        <div className="w-full min-h-screen bg-slate-50 text-slate-800 flex flex-col relative">
            <Head title="Audit Toko" />

            {/* Header */}
            <div className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm shrink-0">
                <header
                    className="px-4 py-3 flex items-center justify-between"
                    style={{
                        paddingTop:
                            "calc(0.75rem + env(safe-area-inset-top, 0px))",
                    }}
                >
                    <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-xl bg-indigo-600/10 flex items-center justify-center text-indigo-600 shadow-sm shadow-indigo-600/10">
                            <ShieldCheckIcon className="w-5 h-5" />
                        </div>
                        <div>
                            <h1 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-900 leading-tight">
                                Audit Toko
                            </h1>
                            <p className="text-[8px] font-bold text-indigo-600 tracking-widest uppercase leading-none">
                                {activeTab === "list"
                                    ? "Daftar Outlet"
                                    : "Hasil Laporan Audit"}
                            </p>
                        </div>
                    </div>
                    {sessionAuditor && (
                        <div className="flex items-center gap-2">
                            <div className="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1.5 rounded-xl shadow-inner">
                                <div className="w-5 h-5 rounded-lg bg-indigo-600 text-white text-[9px] font-black flex items-center justify-center uppercase shrink-0">
                                    {sessionAuditor.charAt(0)}
                                </div>
                                <span className="text-[10px] font-black text-slate-700 leading-none">
                                    {sessionAuditor}
                                </span>
                            </div>
                            <button
                                type="button"
                                onClick={handleLogoutAuditor}
                                className="p-1.5 rounded-xl text-rose-500 bg-rose-50 hover:bg-rose-100 transition-colors border border-rose-100 shrink-0"
                                title="Ganti Auditor"
                            >
                                <svg
                                    className="w-4 h-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    strokeWidth="2.5"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                    />
                                </svg>
                            </button>
                        </div>
                    )}
                </header>

                {activeTab === "list" && (
                    <div className="px-4 pb-3 flex items-center gap-2">
                        <form
                            onSubmit={handleSearchSubmit}
                            className="relative flex-1 flex items-center"
                        >
                            <button
                                type="submit"
                                className="absolute left-3 text-slate-400 hover:text-indigo-600"
                            >
                                <MagnifyingGlassIcon className="w-5 h-5" />
                            </button>
                            <input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                type="search"
                                placeholder="Cari (Tekan Enter / Go)..."
                                className="block w-full pl-10 pr-8 py-2 text-sm md:text-base border border-slate-200 rounded-xl bg-slate-50 focus:border-indigo-500 outline-none text-slate-800"
                            />
                            {search && (
                                <button
                                    type="button"
                                    onClick={clearSearch}
                                    className="absolute right-3 text-slate-400 hover:text-slate-600"
                                >
                                    <XMarkIcon className="w-4 h-4" />
                                </button>
                            )}
                        </form>

                        <button
                            type="button"
                            onClick={() => setShowFiltersSheet(true)}
                            className={`w-10 h-10 rounded-xl border flex items-center justify-center transition-all duration-200 relative shrink-0 ${appliedRegion || appliedArea || appliedDistributor ? "bg-indigo-600 text-white shadow-md border-indigo-600" : "bg-slate-50 text-slate-600 border-slate-200"}`}
                        >
                            <AdjustmentsHorizontalIcon className="w-5 h-5" />
                            {(appliedRegion ||
                                appliedArea ||
                                appliedDistributor) && (
                                <span className="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full animate-bounce"></span>
                            )}
                        </button>
                    </div>
                )}
            </div>

            {/* Main Content */}
            {activeTab === "list" ? (
                <main className="flex-1 px-4 pt-4 pb-24 bg-slate-50/50">
                    {!isFiltered && gpsStatus === "success" && (
                        <div className="bg-indigo-50 border border-indigo-100 rounded-xl p-3 flex items-center gap-2.5 mb-4 text-[10px] text-indigo-700 font-bold shadow-sm">
                            <MapPinIcon className="w-4 h-4 text-indigo-600 shrink-0" />
                            <span>
                                Menampilkan toko terdekat dalam radius 5 km dari
                                lokasi Anda.
                            </span>
                        </div>
                    )}
                    {!isFiltered && gpsStatus === "loading" && (
                        <div className="bg-slate-100 border border-slate-200 rounded-xl p-3 flex items-center gap-2.5 mb-4 text-[10px] text-slate-600 font-bold animate-pulse shadow-sm">
                            <div className="w-4 h-4 rounded-full border-2 border-slate-400 border-t-transparent animate-spin shrink-0"></div>
                            <span>
                                Mendeteksi lokasi GPS Anda untuk mencari toko
                                dalam radius 5 km (Maksimal 10 detik)...
                            </span>
                        </div>
                    )}
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        {filteredOutlets.length > 0 ? (
                            filteredOutlets
                                .slice(0, displayLimit)
                                .map((outlet) => (
                                    <div
                                        key={outlet.customer_code}
                                        className={`border rounded-2xl p-4 shadow-sm flex flex-col gap-3.5 transition-all ${outlet.rwo_status === "RWO" ? "bg-purple-50/60 border-purple-200/80 shadow-purple-100/40" : "bg-white border-slate-100"}`}
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="flex-1 min-w-0">
                                                <div className="flex flex-wrap items-center gap-1.5 mb-1.5">
                                                    <span className="text-[9px] px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold font-mono tracking-wider w-fit">
                                                        {outlet.customer_code}
                                                    </span>
                                                    <span
                                                        className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${outlet.status_audit === "Sudah" ? "bg-emerald-50 text-emerald-600 border-emerald-100/80" : "bg-rose-50 text-rose-600 border-rose-100/80"}`}
                                                    >
                                                        {outlet.status_audit ===
                                                        "Sudah"
                                                            ? "Sudah Audit"
                                                            : "Belum Audit"}
                                                    </span>
                                                    {outlet.status_audit ===
                                                        "Sudah" &&
                                                        (outlet.auditor
                                                            ?.toLowerCase()
                                                            .trim() ===
                                                        sessionAuditor
                                                            ?.toLowerCase()
                                                            .trim() ? (
                                                            <span className="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-indigo-50 text-indigo-600 border border-indigo-100/80">
                                                                ✓ Oleh Anda
                                                            </span>
                                                        ) : (
                                                            outlet.auditor && (
                                                                <span className="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-slate-50 text-slate-500 border border-slate-200/80">
                                                                    Oleh:{" "}
                                                                    {
                                                                        outlet.auditor
                                                                    }
                                                                </span>
                                                            )
                                                        ))}
                                                    {outlet.rwo_status && (
                                                        <span
                                                            className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${outlet.rwo_status === "RWO" ? "bg-purple-50 text-purple-600 border-purple-100/80" : "bg-slate-50 text-slate-500 border-slate-200/80"}`}
                                                        >
                                                            {outlet.rwo_status}
                                                        </span>
                                                    )}
                                                    {outlet.distance !==
                                                        undefined &&
                                                        outlet.distance !==
                                                            Infinity && (
                                                            <span className="text-[8px] px-1.5 py-0.5 rounded-md bg-indigo-50 text-indigo-600 border border-indigo-100/80 font-bold">
                                                                {outlet.distance <
                                                                1
                                                                    ? `${Math.round(outlet.distance * 1000)} m`
                                                                    : `${outlet.distance.toFixed(1)} km`}
                                                            </span>
                                                        )}
                                                </div>
                                                <h4 className="text-xs md:text-sm font-black text-slate-800 tracking-tight leading-snug truncate">
                                                    {outlet.customer_name}
                                                </h4>

                                                <div className="flex flex-col gap-1 mt-2">
                                                    <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium">
                                                        <MapPinIcon className="w-3 h-3 shrink-0 text-slate-400" />
                                                        <span className="truncate flex-1">
                                                            {outlet.customer_address ||
                                                                "-"}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium">
                                                        <BuildingStorefrontIcon className="w-3 h-3 shrink-0 text-slate-400" />
                                                        <span className="truncate flex-1">
                                                            Cabang:{" "}
                                                            {outlet.cabang ||
                                                                "-"}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Action Buttons */}
                                        <div className="flex items-center gap-2 mt-2 pt-3 border-t border-slate-100">
                                            {outlet.master_latitude &&
                                                outlet.master_longitude && (
                                                    <a
                                                        href={`https://www.google.com/maps/dir/?api=1&destination=${outlet.master_latitude},${outlet.master_longitude}${userLocation ? `&origin=${userLocation.latitude},${userLocation.longitude}` : ""}`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="flex-1 inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold uppercase tracking-wide hover:bg-emerald-100"
                                                    >
                                                        <MapIcon className="w-3.5 h-3.5" />
                                                        Direction
                                                    </a>
                                                )}
                                            <button
                                                onClick={() =>
                                                    openDetail(outlet)
                                                }
                                                className="flex-1 inline-flex items-center justify-center gap-1.5 h-8 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-bold uppercase tracking-wide hover:bg-indigo-100"
                                            >
                                                <InformationCircleIcon className="w-3.5 h-3.5" />
                                                Detail
                                            </button>
                                        </div>
                                    </div>
                                ))
                        ) : (
                            <div className="bg-white border border-slate-100 rounded-3xl py-12 px-6 text-center shadow-sm flex-1 flex flex-col items-center justify-center col-span-full">
                                {isFiltered ? (
                                    <>
                                        <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                            <ShieldExclamationIcon className="w-8 h-8" />
                                        </div>
                                        <h4 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-700">
                                            Tidak Ada Data
                                        </h4>
                                        <p className="text-[10px] text-slate-400 mt-2 font-medium">
                                            Kriteria pencarian Anda tidak cocok
                                            dengan toko mana pun.
                                        </p>
                                    </>
                                ) : gpsStatus === "loading" ? (
                                    <>
                                        <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                            <div className="w-8 h-8 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
                                        </div>
                                        <h4 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-700">
                                            Mencari Toko Terdekat...
                                        </h4>
                                        <p className="text-[10px] text-slate-400 mt-2 font-medium">
                                            Mendeteksi lokasi GPS perangkat
                                            Anda.
                                        </p>
                                    </>
                                ) : gpsStatus === "error" ? (
                                    <>
                                        <div className="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-2">
                                            <ShieldExclamationIcon className="w-8 h-8" />
                                        </div>
                                        <h4 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-700">
                                            Gagal Mendeteksi Lokasi
                                        </h4>
                                        <p className="text-[10px] text-slate-400 mt-2 font-medium max-w-xs mx-auto leading-relaxed">
                                            Izin lokasi ditolak, waktu habis,
                                            atau GPS mati. <br />
                                            <span className="text-indigo-600 font-bold">
                                                Silakan nyalakan GPS Anda
                                            </span>{" "}
                                            atau terapkan filter wilayah di
                                            pojok kanan atas secara manual.
                                        </p>
                                    </>
                                ) : (
                                    <>
                                        <div className="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 mb-2">
                                            <MapPinIcon className="w-8 h-8" />
                                        </div>
                                        <h4 className="text-xs md:text-sm font-black uppercase tracking-wider text-slate-700">
                                            Tidak Ada Toko Terdekat
                                        </h4>
                                        <p className="text-[10px] text-slate-400 mt-2 font-medium max-w-xs mx-auto leading-relaxed">
                                            Lokasi Anda berhasil dideteksi,
                                            namun tidak ditemukan toko dalam{" "}
                                            <span className="font-bold">
                                                radius 5 km
                                            </span>
                                            . <br />
                                            Silakan terapkan filter wilayah di
                                            pojok kanan atas secara manual.
                                        </p>
                                    </>
                                )}
                            </div>
                        )}
                        {filteredOutlets.length > displayLimit && (
                            <div className="mt-6 mb-2 text-center">
                                <button
                                    onClick={() =>
                                        setDisplayLimit((prev) => prev + 30)
                                    }
                                    className="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 text-[11px] font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 mx-auto"
                                >
                                    Muat Lebih Banyak
                                    <span className="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-md text-[9px]">
                                        {filteredOutlets.length - displayLimit}{" "}
                                        tersisa
                                    </span>
                                </button>
                            </div>
                        )}
                    </div>
                </main>
            ) : (
                <main className="flex-1 px-4 pt-4 pb-24 space-y-4 flex flex-col bg-slate-50/50">
                    <div className="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col gap-4">
                        {/* List Audit Results */}
                        <div className="mt-2">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                                <h4 className="text-[11px] font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                    <ListBulletIcon className="w-4 h-4 text-indigo-500" />
                                    Daftar Hasil Audit
                                </h4>
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        setShowExportModal(true);
                                    }}
                                    className="inline-flex items-center gap-1.5 px-2.5 md:px-4 py-1.5 rounded-lg bg-emerald-600 text-white text-[10px] md:text-xs font-bold uppercase tracking-wide hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/10 cursor-pointer"
                                >
                                    <svg
                                        className="w-3.5 h-3.5 md:w-4 md:h-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        strokeWidth="2.5"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                    Export Excel
                                </button>
                            </div>

                            <div className="flex flex-col sm:flex-row gap-2 mb-4">
                                <div className="relative flex-1">
                                    <input
                                        type="search"
                                        value={reportSearch}
                                        onChange={(e) =>
                                            setReportSearch(e.target.value)
                                        }
                                        placeholder="Cari laporan (toko, kode, cabang)..."
                                        className="w-full h-10 pl-10 pr-10 text-sm md:text-base border border-slate-200 rounded-xl bg-slate-50 focus:border-indigo-500 outline-none"
                                    />
                                    <MagnifyingGlassIcon className="w-5 h-5 absolute left-3 top-2.5 text-slate-400" />
                                    {reportSearch && (
                                        <button
                                            onClick={() => setReportSearch("")}
                                            className="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600"
                                        >
                                            <XMarkIcon className="w-5 h-5" />
                                        </button>
                                    )}
                                </div>
                                <select
                                    value={reportSort}
                                    onChange={(e) => setReportSort(e.target.value)}
                                    className="h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm font-semibold text-slate-700 outline-none focus:border-indigo-500 shrink-0"
                                >
                                    <option value="newest">Terbaru</option>
                                    <option value="oldest">Terlama</option>
                                    <option value="name">Nama (A-Z)</option>
                                </select>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                {filteredReports.length > 0 ? (
                                    filteredReports.map((report) => {
                                        const verifiedCount = [
                                            report.is_toko_fisik,
                                            report.is_nama_pemilik,
                                            report.is_nama_ktp,
                                            report.is_nik_ktp,
                                            report.is_no_hp,
                                            report.is_no_rekening,
                                            report.is_an_rekening,
                                            report.is_titik_koordinat,
                                        ].filter(Boolean).length;

                                        return (
                                            <div
                                                key={report.customer_code}
                                                className="bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm flex flex-col gap-2 relative overflow-hidden"
                                            >
                                                <div className="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                                                <div className="flex justify-between items-start gap-2">
                                                    <div className="flex-1 min-w-0">
                                                        <div className="flex items-center gap-1.5 flex-wrap mb-1">
                                                            <span className="text-[9px] px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 font-bold font-mono tracking-wider">
                                                                {
                                                                    report.customer_code
                                                                }
                                                            </span>
                                                            {report.status_approval === "Approved" ? (
                                                                <span className="text-[9px] px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-extrabold flex items-center gap-1">
                                                                    <CheckCircleIcon className="w-3 h-3" /> Disetujui
                                                                </span>
                                                            ) : report.status_approval === "Rejected" ? (
                                                                <span className="text-[9px] px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-extrabold flex items-center gap-1">
                                                                    <XCircleIcon className="w-3 h-3" /> Ditolak
                                                                </span>
                                                            ) : (
                                                                <span className="text-[9px] px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-extrabold flex items-center gap-1">
                                                                    <ClockIcon className="w-3 h-3" /> Menunggu Approval
                                                                </span>
                                                            )}
                                                        </div>
                                                        <h5 className="text-xs md:text-sm font-black text-slate-800 tracking-tight leading-snug truncate">
                                                            {
                                                                report.customer_name
                                                            }
                                                        </h5>
                                                    </div>
                                                    <div className="flex flex-col items-end shrink-0">
                                                        <span className="text-[8px] uppercase tracking-wider font-extrabold text-slate-400 mb-0.5">
                                                            Auditor
                                                        </span>
                                                        <span className="text-[10px] px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold shadow-sm">
                                                            {report.auditor}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium mt-1">
                                                    <BuildingStorefrontIcon className="w-3.5 h-3.5 shrink-0 text-slate-400" />
                                                    <span className="flex-1 leading-snug">
                                                        Cabang:{" "}
                                                        <span className="font-bold text-slate-700">
                                                            {report.cabang ||
                                                                "-"}
                                                        </span>
                                                    </span>
                                                </div>
                                                <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium mt-1">
                                                    <ShieldCheckIcon className="w-3.5 h-3.5 shrink-0 text-emerald-600" />
                                                    <span className="flex-1 leading-snug">
                                                        Checklist:{" "}
                                                        <span className="font-bold text-emerald-700">
                                                            {verifiedCount}/8
                                                            Sesuai
                                                        </span>
                                                    </span>
                                                </div>

                                                {report.status_approval === "Rejected" && report.alasan_reject && (
                                                    <div className="mt-1 bg-rose-50 border border-rose-200 rounded-xl p-2 text-[10px] text-rose-700 font-medium">
                                                        <div className="font-bold flex items-center gap-1 mb-0.5 text-rose-800">
                                                            <ShieldExclamationIcon className="w-3.5 h-3.5" /> Catatan Penolakan:
                                                        </div>
                                                        <p className="line-clamp-2 text-[10px] text-rose-600 pl-4">{report.alasan_reject}</p>
                                                    </div>
                                                )}
                                                {report.created_at && (
                                                    <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium mt-1">
                                                        <svg
                                                            className="w-3.5 h-3.5 shrink-0 text-slate-400"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            strokeWidth="2"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                            />
                                                        </svg>
                                                        <span className="flex-1 leading-snug">
                                                            Tanggal:{" "}
                                                            <span className="font-bold text-slate-700">
                                                                {formatDateSafe(report.created_at)}
                                                            </span>
                                                        </span>
                                                    </div>
                                                )}
                                                {report.keterangan_hasil_audit && (
                                                    <div className="text-[10px] text-slate-500 mt-1 leading-snug">
                                                        <span className="font-bold text-slate-700">
                                                            Keterangan Hasil
                                                            Audit:
                                                        </span>{" "}
                                                        <span className="text-slate-600">
                                                            {
                                                                report.keterangan_hasil_audit
                                                            }
                                                        </span>
                                                    </div>
                                                )}
                                                <div className="flex gap-2 mt-3 pt-3 border-t border-slate-200/60">
                                                    <button
                                                        onClick={() =>
                                                            openDetailFromReport(
                                                                report,
                                                                true,
                                                            )
                                                        }
                                                        className="flex-1 inline-flex items-center justify-center gap-1 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-bold uppercase tracking-wide hover:bg-amber-100 transition-colors"
                                                    >
                                                        <PencilIcon className="w-3.5 h-3.5" />
                                                        Edit
                                                    </button>
                                                    <button
                                                        onClick={() =>
                                                            requestDeleteReport(
                                                                report,
                                                            )
                                                        }
                                                        disabled={
                                                            isDeletingCode ===
                                                            report.customer_code
                                                        }
                                                        className={`flex-1 inline-flex items-center justify-center gap-1 h-8 rounded-lg text-[10px] font-bold uppercase tracking-wide transition-colors ${isDeletingCode === report.customer_code ? "bg-slate-100 text-slate-400 cursor-not-allowed" : "bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100"}`}
                                                    >
                                                        {isDeletingCode ===
                                                        report.customer_code ? (
                                                            <div className="w-3 h-3 rounded-full border-2 border-slate-400 border-t-transparent animate-spin"></div>
                                                        ) : (
                                                            <TrashIcon className="w-3.5 h-3.5" />
                                                        )}
                                                        {isDeletingCode ===
                                                        report.customer_code
                                                            ? "Hapus..."
                                                            : "Hapus"}
                                                    </button>
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="text-center py-8 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col items-center justify-center col-span-full">
                                        <ShieldExclamationIcon className="w-8 h-8 text-slate-300 mb-2" />
                                        <span className="text-[11px] font-bold text-slate-500">
                                            {allMyReports.length > 0
                                                ? "Laporan tidak ditemukan untuk pencarian tersebut"
                                                : "Belum ada hasil audit"}
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </main>
            )}

            {/* Filter Bottom Sheet */}
            {showFiltersSheet && (
                <div className="fixed inset-0 z-50">
                    <div
                        className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"
                        onClick={() => setShowFiltersSheet(false)}
                    ></div>
                    <div className="fixed bottom-0 left-0 right-0 max-w-xl md:max-w-2xl mx-auto bg-white rounded-t-[32px] shadow-2xl flex flex-col max-h-[85%] z-50 animate-slide-up">
                        <div className="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                        <div className="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                            <div>
                                <h4 className="text-sm md:text-base font-black text-slate-900">
                                    Filter Data
                                </h4>
                                <p className="text-[10px] font-semibold text-slate-400">
                                    Pilih kriteria lalu tekan terapkan
                                </p>
                            </div>
                            <button
                                onClick={() => setShowFiltersSheet(false)}
                                className="text-slate-400 p-1 bg-slate-50 rounded-full"
                            >
                                <XMarkIcon className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="flex-1 overflow-y-auto p-5 space-y-4">
                            <div>
                                <label className="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">
                                    Region
                                </label>
                                <select
                                    value={selectedRegion}
                                    onChange={(e) => {
                                        setSelectedRegion(e.target.value);
                                        setSelectedArea("");
                                        setSelectedDistributor("");
                                    }}
                                    className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm md:text-base outline-none focus:border-indigo-500 font-semibold text-slate-700"
                                >
                                    <option value="">Semua Region</option>
                                    {regions.map((r) => (
                                        <option key={r} value={r}>
                                            {r}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">
                                    Area
                                </label>
                                <select
                                    value={selectedArea}
                                    onChange={(e) => {
                                        setSelectedArea(e.target.value);
                                        setSelectedDistributor("");
                                    }}
                                    disabled={!selectedRegion}
                                    className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm md:text-base outline-none focus:border-indigo-500 font-semibold text-slate-700 disabled:opacity-60 disabled:bg-slate-200/50 disabled:text-slate-400 disabled:cursor-not-allowed"
                                >
                                    <option value="">
                                        {!selectedRegion
                                            ? "Pilih Region dahulu..."
                                            : "Semua Area"}
                                    </option>
                                    {areas.map((a) => (
                                        <option key={a} value={a}>
                                            {a}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">
                                    Distributor
                                </label>
                                <select
                                    value={selectedDistributor}
                                    onChange={(e) =>
                                        setSelectedDistributor(e.target.value)
                                    }
                                    disabled={!selectedArea}
                                    className="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm md:text-base outline-none focus:border-indigo-500 font-semibold text-slate-700 disabled:opacity-60 disabled:bg-slate-200/50 disabled:text-slate-400 disabled:cursor-not-allowed"
                                >
                                    <option value="">
                                        {!selectedArea
                                            ? "Pilih Area dahulu..."
                                            : "Semua Distributor"}
                                    </option>
                                    {distributors.map((d) => (
                                        <option key={d} value={d}>
                                            {d}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                        <div className="p-5 border-t border-slate-100 bg-slate-50 flex gap-3">
                            <button
                                onClick={() => {
                                    resetFilters();
                                    setShowFiltersSheet(false);
                                }}
                                className="flex-1 h-12 border border-slate-200 bg-white rounded-xl text-xs md:text-sm font-bold text-slate-700 hover:bg-slate-50"
                            >
                                Kosongkan
                            </button>
                            <button
                                onClick={applyFilters}
                                className="flex-1 h-12 bg-indigo-600 text-white rounded-xl text-xs md:text-sm font-bold shadow-md shadow-indigo-600/20"
                            >
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Detail Bottom Sheet */}
            {detailOutlet && (
                <div className="fixed inset-0 z-50">
                    <div
                        className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"
                        onClick={handleCloseDetail}
                    ></div>
                    <div className="fixed inset-0 max-w-xl md:max-w-2xl mx-auto bg-white flex flex-col h-full z-50 animate-slide-up">
                        <ErrorBoundary>
                            <div className="px-5 pb-3 pt-4 flex items-start justify-between border-b border-slate-100 shrink-0">
                                <div>
                                    <div className="flex flex-wrap items-center gap-1.5 mb-1">
                                        <span className="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 font-mono font-bold rounded-md text-[9px]">
                                            {detailOutlet.customer_code}
                                        </span>
                                        {detailOutlet.status_approval === "Approved" ? (
                                            <span className="text-[9px] px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-extrabold flex items-center gap-1">
                                                <CheckCircleIcon className="w-3 h-3" /> Disetujui
                                            </span>
                                        ) : detailOutlet.status_approval === "Rejected" ? (
                                            <span className="text-[9px] px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-extrabold flex items-center gap-1">
                                                <XCircleIcon className="w-3 h-3" /> Ditolak
                                            </span>
                                        ) : (
                                            <span className="text-[9px] px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-extrabold flex items-center gap-1">
                                                <ClockIcon className="w-3 h-3" /> Menunggu Approval
                                            </span>
                                        )}
                                        <span
                                            className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${detailOutlet.status_audit === "Sudah" ? "bg-emerald-50 text-emerald-600 border-emerald-100/80" : "bg-rose-50 text-rose-600 border-rose-100/80"}`}
                                        >
                                            {detailOutlet.status_audit === "Sudah"
                                                ? "Sudah Audit"
                                                : "Belum Audit"}
                                        </span>
                                        {detailOutlet.rwo_status && (
                                            <span
                                                className={`text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border ${detailOutlet.rwo_status === "RWO" ? "bg-purple-50 text-purple-600 border-purple-100/80" : "bg-slate-50 text-slate-500 border-slate-200/80"}`}
                                            >
                                                {detailOutlet.rwo_status}
                                            </span>
                                        )}
                                    </div>
                                    <h4 className="text-sm md:text-base font-black text-slate-900 leading-tight pr-2">
                                        {detailOutlet.customer_name}
                                    </h4>
                                </div>
                                <button
                                    onClick={handleCloseDetail}
                                    className="text-slate-400 p-1.5 bg-slate-50 rounded-full shrink-0"
                                >
                                    <XMarkIcon className="w-5 h-5 md:w-6 md:h-6" />
                                </button>
                            </div>

                            {detailOutlet.status_approval === "Rejected" && (
                                <div className="mx-5 mt-3 bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded-2xl flex flex-col gap-1 shadow-sm animate-fade-in">
                                    <div className="flex items-center gap-1.5 font-bold text-xs text-rose-700">
                                        <XCircleIcon className="w-4 h-4 shrink-0 text-rose-600" />
                                        <span>Catatan Penolakan Manager:</span>
                                    </div>
                                    <p className="text-[11px] font-medium leading-relaxed pl-5 text-rose-700">
                                        {detailOutlet.alasan_reject || "Belum ada rincian alasan penolakan."}
                                    </p>
                                    <p className="text-[9.5px] font-bold text-rose-600 pl-5 mt-1">
                                        💡 Silakan perbaiki data/foto di bawah ini dan tekan "Simpan Hasil Audit" untuk pengajuan ulang ke Manager.
                                    </p>
                                </div>
                            )}

                            {Object.keys(errors).length > 0 && (
                                <div className="mx-5 mt-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-[11px] md:text-xs shadow-sm flex flex-col animate-fade-in">
                                    <span className="font-bold mb-1 flex items-center gap-1.5">
                                        <XCircleIcon className="w-4 h-4" />
                                        Gagal menyimpan data:
                                    </span>
                                    <ul className="list-disc ml-5 space-y-0.5">
                                        {Object.values(errors).map((err, i) => (
                                            <li key={i}>{err}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            <div className="flex-1 overflow-y-auto overflow-x-hidden p-5 custom-scrollbar">
                                {/* Identitas Pemilik */}
                                <div>
                                    <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 mb-2">
                                        Identitas Pemilik
                                    </h5>
                                    <div className="bg-slate-50 rounded-xl p-3 space-y-2 border border-slate-100">
                                        <div className="flex items-center justify-between border-b border-slate-200 pb-2 gap-2">
                                            <span className="w-1/3 text-[10px] font-semibold text-slate-500 shrink-0">
                                                Toko Fisik
                                            </span>
                                            <span className="flex-1 text-[10px] font-bold text-slate-800 truncate">
                                                Ada / Beroperasi
                                            </span>
                                            <label
                                                className={`shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg border text-[9px] font-bold cursor-pointer transition-all ${data.is_toko_fisik ? "bg-emerald-50 border-emerald-300 text-emerald-700 shadow-sm" : "bg-white border-slate-200 text-slate-400 hover:bg-slate-50"}`}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={Boolean(
                                                        data.is_toko_fisik,
                                                    )}
                                                    onChange={(e) => {
                                                        setIsFormTouched(true);
                                                        setData(
                                                            "is_toko_fisik",
                                                            e.target.checked,
                                                        );
                                                    }}
                                                    className="w-3.5 h-3.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer"
                                                />
                                                <span>
                                                    {data.is_toko_fisik
                                                        ? "Fisik Sesuai"
                                                        : "Verifikasi"}
                                                </span>
                                            </label>
                                        </div>
                                        <div className="flex items-center justify-between border-b border-slate-200 pb-2 gap-2">
                                            <span className="w-1/3 text-[10px] font-semibold text-slate-500 shrink-0">
                                                Nama Pemilik
                                            </span>
                                            <span className="flex-1 text-[10px] font-bold text-slate-800 truncate">
                                                {detailOutlet.nama_pemilik_toko ||
                                                    "-"}
                                            </span>
                                            <label
                                                className={`shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg border text-[9px] font-bold cursor-pointer transition-all ${data.is_nama_pemilik ? "bg-emerald-50 border-emerald-300 text-emerald-700 shadow-sm" : "bg-white border-slate-200 text-slate-400 hover:bg-slate-50"}`}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={Boolean(
                                                        data.is_nama_pemilik,
                                                    )}
                                                    onChange={(e) => {
                                                        setIsFormTouched(true);
                                                        setData(
                                                            "is_nama_pemilik",
                                                            e.target.checked,
                                                        );
                                                    }}
                                                    className="w-3.5 h-3.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer"
                                                />
                                                <span>
                                                    {data.is_nama_pemilik
                                                        ? "Sesuai"
                                                        : "Verifikasi"}
                                                </span>
                                            </label>
                                        </div>
                                        <div className="flex items-center justify-between border-b border-slate-200 pb-2 gap-2">
                                            <span className="w-1/3 text-[10px] font-semibold text-slate-500 shrink-0">
                                                Nama KTP
                                            </span>
                                            <span className="flex-1 text-[10px] font-bold text-slate-800 truncate">
                                                {detailOutlet.nama_ktp || "-"}
                                            </span>
                                            <label
                                                className={`shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg border text-[9px] font-bold cursor-pointer transition-all ${data.is_nama_ktp ? "bg-emerald-50 border-emerald-300 text-emerald-700 shadow-sm" : "bg-white border-slate-200 text-slate-400 hover:bg-slate-50"}`}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={Boolean(
                                                        data.is_nama_ktp,
                                                    )}
                                                    onChange={(e) => {
                                                        setIsFormTouched(true);
                                                        setData(
                                                            "is_nama_ktp",
                                                            e.target.checked,
                                                        );
                                                    }}
                                                    className="w-3.5 h-3.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer"
                                                />
                                                <span>
                                                    {data.is_nama_ktp
                                                        ? "Sesuai"
                                                        : "Verifikasi"}
                                                </span>
                                            </label>
                                        </div>
                                        <div className="flex items-center justify-between border-b border-slate-200 pb-2 gap-2">
                                            <span className="w-1/3 text-[10px] font-semibold text-slate-500 shrink-0">
                                                NIK KTP
                                            </span>
                                            <span className="flex-1 text-[10px] font-bold text-slate-800 truncate">
                                                {detailOutlet.nik_ktp || "-"}
                                            </span>
                                            <label
                                                className={`shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg border text-[9px] font-bold cursor-pointer transition-all ${data.is_nik_ktp ? "bg-emerald-50 border-emerald-300 text-emerald-700 shadow-sm" : "bg-white border-slate-200 text-slate-400 hover:bg-slate-50"}`}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={Boolean(
                                                        data.is_nik_ktp,
                                                    )}
                                                    onChange={(e) => {
                                                        setIsFormTouched(true);
                                                        setData(
                                                            "is_nik_ktp",
                                                            e.target.checked,
                                                        );
                                                    }}
                                                    className="w-3.5 h-3.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer"
                                                />
                                                <span>
                                                    {data.is_nik_ktp
                                                        ? "Sesuai"
                                                        : "Verifikasi"}
                                                </span>
                                            </label>
                                        </div>
                                        <div className="flex items-center justify-between gap-2">
                                            <span className="w-1/3 text-[10px] font-semibold text-slate-500 shrink-0">
                                                No. HP
                                            </span>
                                            <span className="flex-1 text-[10px] font-bold text-slate-800 truncate">
                                                {detailOutlet.no_hp || "-"}
                                            </span>
                                            <label
                                                className={`shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg border text-[9px] font-bold cursor-pointer transition-all ${data.is_no_hp ? "bg-emerald-50 border-emerald-300 text-emerald-700 shadow-sm" : "bg-white border-slate-200 text-slate-400 hover:bg-slate-50"}`}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={Boolean(
                                                        data.is_no_hp,
                                                    )}
                                                    onChange={(e) => {
                                                        setIsFormTouched(true);
                                                        setData(
                                                            "is_no_hp",
                                                            e.target.checked,
                                                        );
                                                    }}
                                                    className="w-3.5 h-3.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer"
                                                />
                                                <span>
                                                    {data.is_no_hp
                                                        ? "Sesuai"
                                                        : "Verifikasi"}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {/* Rekening */}
                                <div>
                                    <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 mb-2">
                                        Rekening Bank
                                    </h5>
                                    <div className="bg-slate-50 rounded-xl p-3 space-y-2 border border-slate-100">
                                        <div className="flex items-center justify-between border-b border-slate-200 pb-2 gap-2">
                                            <span className="w-1/3 text-[10px] font-semibold text-slate-500 shrink-0">
                                                Nama Bank
                                            </span>
                                            <span className="flex-1 text-[10px] font-bold text-slate-800 truncate">
                                                {detailOutlet.nama_bank || "-"}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between border-b border-slate-200 pb-2 gap-2">
                                            <span className="w-1/3 text-[10px] font-semibold text-slate-500 shrink-0">
                                                No. Rekening
                                            </span>
                                            <span className="flex-1 text-[10px] font-bold text-slate-800 truncate">
                                                {detailOutlet.no_rekening ||
                                                    "-"}
                                            </span>
                                            <label
                                                className={`shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg border text-[9px] font-bold cursor-pointer transition-all ${data.is_no_rekening ? "bg-emerald-50 border-emerald-300 text-emerald-700 shadow-sm" : "bg-white border-slate-200 text-slate-400 hover:bg-slate-50"}`}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={Boolean(
                                                        data.is_no_rekening,
                                                    )}
                                                    onChange={(e) => {
                                                        setIsFormTouched(true);
                                                        setData(
                                                            "is_no_rekening",
                                                            e.target.checked,
                                                        );
                                                    }}
                                                    className="w-3.5 h-3.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer"
                                                />
                                                <span>
                                                    {data.is_no_rekening
                                                        ? "Sesuai"
                                                        : "Verifikasi"}
                                                </span>
                                            </label>
                                        </div>
                                        <div className="flex items-center justify-between gap-2">
                                            <span className="w-1/3 text-[10px] font-semibold text-slate-500 shrink-0">
                                                A/N Rekening
                                            </span>
                                            <span className="flex-1 text-[10px] font-bold text-slate-800 truncate">
                                                {detailOutlet.nama_pemilik_norek ||
                                                    "-"}
                                            </span>
                                            <label
                                                className={`shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg border text-[9px] font-bold cursor-pointer transition-all ${data.is_an_rekening ? "bg-emerald-50 border-emerald-300 text-emerald-700 shadow-sm" : "bg-white border-slate-200 text-slate-400 hover:bg-slate-50"}`}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={Boolean(
                                                        data.is_an_rekening,
                                                    )}
                                                    onChange={(e) => {
                                                        setIsFormTouched(true);
                                                        setData(
                                                            "is_an_rekening",
                                                            e.target.checked,
                                                        );
                                                    }}
                                                    className="w-3.5 h-3.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer"
                                                />
                                                <span>
                                                    {data.is_an_rekening
                                                        ? "Sesuai"
                                                        : "Verifikasi"}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {/* Titik Koordinat & Peta Lokasi */}
                                <div>
                                    <div className="flex items-center justify-between mb-2">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600">
                                            Titik Koordinat (GPS)
                                        </h5>
                                        <label
                                            className={`shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg border text-[9px] font-bold cursor-pointer transition-all ${data.is_titik_koordinat ? "bg-emerald-50 border-emerald-300 text-emerald-700 shadow-sm" : "bg-white border-slate-200 text-slate-400 hover:bg-slate-50"}`}
                                        >
                                            <input
                                                type="checkbox"
                                                checked={Boolean(
                                                    data.is_titik_koordinat,
                                                )}
                                                onChange={(e) => {
                                                    setIsFormTouched(true);
                                                    setData(
                                                        "is_titik_koordinat",
                                                        e.target.checked,
                                                    );
                                                }}
                                                className="w-3.5 h-3.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer"
                                            />
                                            <span>
                                                {data.is_titik_koordinat
                                                    ? "Koordinat Sesuai"
                                                    : "Verifikasi"}
                                            </span>
                                        </label>
                                    </div>

                                    <div
                                        className={`p-3.5 rounded-2xl border transition-all ${data.latitude && data.longitude ? "bg-emerald-50/60 border-emerald-200" : "bg-amber-50/60 border-amber-300 shadow-sm"}`}
                                    >
                                        {/* Banner Informasional Jarak */}
                                        <div className="flex items-center justify-between bg-white border border-slate-200/80 p-2.5 rounded-xl shadow-sm mb-2.5">
                                            <div className="flex items-center gap-2">
                                                <div
                                                    className={`w-2.5 h-2.5 rounded-full ${data.latitude && data.longitude ? "bg-emerald-500 animate-ping" : "bg-amber-500"}`}
                                                ></div>
                                                <span className="text-[10px] font-bold text-slate-700">
                                                    Jarak dari Master:
                                                </span>
                                            </div>
                                            <span
                                                className={`text-[11px] font-black ${data.latitude && data.longitude ? "text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100" : "text-amber-600"}`}
                                            >
                                                {data.latitude &&
                                                data.longitude &&
                                                detailOutlet.master_latitude &&
                                                detailOutlet.master_longitude
                                                    ? `📏 ${calcDistanceText(detailOutlet.master_latitude, detailOutlet.master_longitude, data.latitude, data.longitude)}`
                                                    : "⚠️ Belum diukur"}
                                            </span>
                                        </div>

                                        {/* Leaflet Mini Map Container */}
                                        <div className="relative w-full h-44 rounded-xl overflow-hidden border border-slate-200/90 shadow-inner mb-3 z-0">
                                            <div
                                                ref={auditMapContainerRef}
                                                className="w-full h-full"
                                            ></div>

                                            {/* Legend Overlay */}
                                            <div className="absolute top-2 right-2 z-[400] bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg border border-slate-200 shadow-sm flex flex-col gap-1 text-[8px] font-bold text-slate-700">
                                                <div className="flex items-center gap-1.5">
                                                    <span className="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block border border-white"></span>
                                                    <span>Titik Master</span>
                                                </div>
                                                <div className="flex items-center gap-1.5">
                                                    <span className="w-2.5 h-2.5 rounded-full bg-emerald-600 inline-block border border-white"></span>
                                                    <span>Hasil Audit</span>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Box Latitude Longitude */}
                                        <div className="flex items-center justify-between bg-white border border-slate-200/80 rounded-xl p-2.5 shadow-sm mb-3">
                                            <div className="flex flex-col">
                                                <span className="text-[8px] font-extrabold text-slate-400 uppercase tracking-wider mb-0.5">
                                                    Lat / Long Audit
                                                </span>
                                                <span
                                                    className={`text-[10px] font-mono font-bold ${data.latitude && data.longitude ? "text-slate-800" : "text-amber-600"}`}
                                                >
                                                    {data.latitude &&
                                                    data.longitude
                                                        ? `${data.latitude}, ${data.longitude}`
                                                        : "Belum diambil"}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Button Ambil GPS */}
                                        <button
                                            type="button"
                                            onClick={fetchCurrentLocation}
                                            disabled={isGettingLocation}
                                            className={`w-full py-2.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider shadow-md flex items-center justify-center gap-2 transition-all active:scale-[0.98] ${
                                                isGettingLocation
                                                    ? "bg-slate-300 text-slate-600 cursor-not-allowed"
                                                    : data.latitude &&
                                                        data.longitude
                                                      ? "bg-emerald-600 text-white hover:bg-emerald-700 shadow-emerald-600/20"
                                                      : "bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-600/25"
                                            }`}
                                        >
                                            {isGettingLocation ? (
                                                <>
                                                    <div className="w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></div>
                                                    <span>
                                                        Mendeteksi Lokasi...
                                                    </span>
                                                </>
                                            ) : (
                                                <span>
                                                    {data.latitude &&
                                                    data.longitude
                                                        ? "Perbarui Lokasi GPS"
                                                        : "Ambil Lokasi GPS Sekarang"}
                                                </span>
                                            )}
                                        </button>
                                    </div>
                                </div>

                                {/* Foto Lampiran */}
                                    <div className="mt-6 pb-6">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 mb-2">
                                            Foto Lampiran
                                        </h5>
                                        <div className="grid grid-cols-3 gap-2">
                                            <div className="bg-slate-50 rounded-xl overflow-hidden border border-slate-100 shadow-sm flex flex-col relative group">
                                                <span className="text-[9px] font-bold text-slate-600 px-2.5 py-1.5 border-b border-slate-100 bg-white/90 backdrop-blur-sm absolute top-0 w-full z-10">
                                                    Foto KTP
                                                </span>
                                                {detailOutlet.foto_ktp ? (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            setZoomedImage(
                                                                `/storage/${detailOutlet.foto_ktp}`,
                                                            )
                                                        }
                                                        className="block mt-6 focus:outline-none w-full text-left"
                                                    >
                                                        <img
                                                            src={`/mobile/audit/thumbnail?path=${encodeURIComponent(detailOutlet.foto_ktp)}`}
                                                            alt="Foto KTP"
                                                            className="w-full h-24 object-cover"
                                                            loading="lazy"
                                                        />
                                                    </button>
                                                ) : (
                                                    <div className="mt-6 flex-1 h-24 flex items-center justify-center bg-slate-100/50">
                                                        <span className="text-[10px] font-semibold text-slate-400">
                                                            Belum ada
                                                        </span>
                                                    </div>
                                                )}
                                            </div>

                                            <div className="bg-slate-50 rounded-xl overflow-hidden border border-slate-100 shadow-sm flex flex-col relative group">
                                                <span className="text-[9px] font-bold text-slate-600 px-2.5 py-1.5 border-b border-slate-100 bg-white/90 backdrop-blur-sm absolute top-0 w-full z-10">
                                                    Tampak Depan
                                                </span>
                                                {detailOutlet.tampak_depan ? (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            setZoomedImage(
                                                                `/storage/${detailOutlet.tampak_depan}`,
                                                            )
                                                        }
                                                        className="block mt-6 focus:outline-none w-full text-left"
                                                    >
                                                        <img
                                                            src={`/mobile/audit/thumbnail?path=${encodeURIComponent(detailOutlet.tampak_depan)}`}
                                                            alt="Tampak Depan"
                                                            className="w-full h-24 object-cover"
                                                            loading="lazy"
                                                        />
                                                    </button>
                                                ) : (
                                                    <div className="mt-6 flex-1 h-24 flex items-center justify-center bg-slate-100/50">
                                                        <span className="text-[10px] font-semibold text-slate-400">
                                                            Belum ada
                                                        </span>
                                                    </div>
                                                )}
                                            </div>

                                            <div className="bg-slate-50 rounded-xl overflow-hidden border border-slate-100 shadow-sm flex flex-col relative group">
                                                <span className="text-[9px] font-bold text-slate-600 px-2.5 py-1.5 border-b border-slate-100 bg-white/90 backdrop-blur-sm absolute top-0 w-full z-10">
                                                    Tampak Dalam
                                                </span>
                                                {detailOutlet.tampak_dalam ? (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            setZoomedImage(
                                                                `/storage/${detailOutlet.tampak_dalam}`,
                                                            )
                                                        }
                                                        className="block mt-6 focus:outline-none w-full text-left"
                                                    >
                                                        <img
                                                            src={`/mobile/audit/thumbnail?path=${encodeURIComponent(detailOutlet.tampak_dalam)}`}
                                                            alt="Tampak Dalam"
                                                            className="w-full h-24 object-cover"
                                                            loading="lazy"
                                                        />
                                                    </button>
                                                ) : (
                                                    <div className="mt-6 flex-1 h-24 flex items-center justify-center bg-slate-100/50">
                                                        <span className="text-[10px] font-semibold text-slate-400">
                                                            Belum ada
                                                        </span>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                    {/* Foto Hasil Audit (Dipisah) */}
                                <div className="pb-6">
                                    <div className="flex items-center justify-between mb-2">
                                        <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600">
                                            Foto Hasil Audit
                                        </h5>
                                        <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100">
                                            {[1, 2, 3, 4, 5, 6, 7, 8].filter(n => {
                                                const f = `foto_audit${n}`;
                                                return data[f] instanceof File || (data[f] !== "delete" && detailOutlet?.[f]);
                                            }).length}/8 Terisi
                                        </span>
                                    </div>
                                    <div className="bg-white rounded-xl p-4 border border-indigo-100 shadow-sm shadow-indigo-100/50">
                                        <div className="grid grid-cols-4 gap-2">
                                            {[1, 2, 3, 4, 5, 6, 7, 8].map(
                                                (num) => {
                                                    const field = `foto_audit${num}`;
                                                    const isDeleted =
                                                        data[field] ===
                                                        "delete";
                                                    const hasUploadedFile =
                                                        data[field] instanceof
                                                        File;
                                                    const hasServerFile =
                                                        isDeleted
                                                            ? null
                                                            : detailOutlet?.[field];
                                                    const hasAnyFile =
                                                        hasUploadedFile ||
                                                        hasServerFile;

                                                    return (
                                                        <div
                                                            key={num}
                                                            className="relative aspect-square rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 flex flex-col items-center justify-center transition-colors group"
                                                        >
                                                            {hasAnyFile ? (
                                                                <>
                                                                    <div className="absolute inset-0 rounded-xl overflow-hidden">
                                                                        {hasUploadedFile ? (
                                                                            <div className="absolute inset-0">
                                                                                <img
                                                                                    src={URL.createObjectURL(data[field])}
                                                                                    alt={`Preview ${num}`}
                                                                                    className="w-full h-full object-cover"
                                                                                />
                                                                                <span className="absolute top-1 left-1 bg-emerald-500 text-white text-[7px] font-black px-1.5 py-0.5 rounded shadow-sm">
                                                                                    BARU
                                                                                </span>
                                                                            </div>
                                                                        ) : (
                                                                            <img
                                                                                src={`/mobile/audit/thumbnail?path=${encodeURIComponent(hasServerFile)}`}
                                                                                alt={`Audit ${num}`}
                                                                                className="absolute inset-0 w-full h-full object-cover"
                                                                            />
                                                                        )}
                                                                        
                                                                        <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                                            <button
                                                                                type="button"
                                                                                onClick={(
                                                                                    e,
                                                                                ) => {
                                                                                    e.preventDefault();
                                                                                    if (hasUploadedFile) {
                                                                                        setZoomedImage(URL.createObjectURL(data[field]));
                                                                                    } else if (hasServerFile) {
                                                                                        setZoomedImage(`/storage/${hasServerFile}`);
                                                                                    }
                                                                                }}
                                                                                className="w-7 h-7 rounded-full bg-slate-900/60 hover:bg-slate-900/80 flex items-center justify-center text-white shadow-sm backdrop-blur-sm transition-all pointer-events-auto"
                                                                            >
                                                                                <EyeIcon className="w-3.5 h-3.5" />
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {hasAnyFile && (
                                                                        <button
                                                                            type="button"
                                                                            onClick={(
                                                                                e,
                                                                            ) => {
                                                                                e.preventDefault();
                                                                                if (hasUploadedFile) {
                                                                                    setData(field, null);
                                                                                } else {
                                                                                    setData(field, "delete");
                                                                                }
                                                                                const fi =
                                                                                    document.getElementById(
                                                                                        `fileInputAudit${num}`,
                                                                                    );
                                                                                if (
                                                                                    fi
                                                                                )
                                                                                    fi.value =
                                                                                        "";
                                                                            }}
                                                                            className="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 flex items-center justify-center text-white shadow-md transition-all z-10 border-2 border-white"
                                                                        >
                                                                            <XMarkIcon className="w-3.5 h-3.5" />
                                                                        </button>
                                                                    )}
                                                                </>
                                                            ) : (
                                                                <div
                                                                    onClick={() =>
                                                                        document
                                                                            .getElementById(
                                                                                `fileInputAudit${num}`,
                                                                            )
                                                                            .click()
                                                                    }
                                                                    className="absolute inset-0 flex flex-col items-center justify-center cursor-pointer"
                                                                >
                                                                    <CameraIcon className="w-5 h-5 text-indigo-400 mb-1" />
                                                                    <span className="text-[9px] font-semibold text-indigo-600">
                                                                        Foto{" "}
                                                                        {num}
                                                                    </span>
                                                                </div>
                                                            )}
                                                            <input
                                                                id={`fileInputAudit${num}`}
                                                                type="file"
                                                                onChange={(e) =>
                                                                    handleFileChange(
                                                                        field,
                                                                        e.target
                                                                            .files[0],
                                                                    )
                                                                }
                                                                accept="image/*"
                                                                className="hidden"
                                                            />
                                                        </div>
                                                    );
                                                },
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* Form Audit */}
                                <div id="audit-form-container" className="pb-6">
                                    <h5 className="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 mb-2">
                                        Catatan Hasil Audit
                                    </h5>
                                    <form
                                        onSubmit={submitAudit}
                                        className="bg-white rounded-xl p-4 border border-indigo-100 shadow-sm shadow-indigo-100/50 space-y-4"
                                    >

                                        <div>
                                            <label className="block text-[10px] font-bold text-slate-700 mb-1">
                                                Keterangan Audit (Free Text)
                                            </label>
                                            <textarea
                                                value={
                                                    data.keterangan_hasil_audit ||
                                                    ""
                                                }
                                                onChange={(e) => {
                                                    setIsFormTouched(true);
                                                    setData(
                                                        "keterangan_hasil_audit",
                                                        e.target.value,
                                                    );
                                                }}
                                                maxLength={500}
                                                placeholder="Tuliskan keterangan jika ada..."
                                                rows="2"
                                                className="w-full text-sm md:text-base px-3 py-2 border border-slate-200 rounded-lg outline-none focus:border-indigo-500 bg-slate-50"
                                            ></textarea>
                                            <div className="flex justify-end mt-1">
                                                <span
                                                    className={`text-[9px] font-semibold ${(data.keterangan_hasil_audit || "").length > 480 ? "text-rose-500" : (data.keterangan_hasil_audit || "").length > 400 ? "text-amber-500" : "text-slate-400"}`}
                                                >
                                                    {
                                                        (
                                                            data.keterangan_hasil_audit ||
                                                            ""
                                                        ).length
                                                    }
                                                    /500
                                                </span>
                                            </div>
                                        </div>

                                        {showNoPhotoWarning && (
                                            <div className="bg-amber-50 border border-amber-200 rounded-xl p-3 flex flex-col gap-2">
                                                <div className="flex items-center gap-2 text-amber-700 font-bold text-[10px]">
                                                    <ShieldExclamationIcon className="w-4 h-4 shrink-0" />
                                                    <span>
                                                        Belum ada foto audit
                                                        yang dilampirkan.
                                                    </span>
                                                </div>
                                                <p className="text-[9px] text-amber-600">
                                                    Apakah Anda yakin ingin
                                                    menyimpan tanpa melampirkan
                                                    bukti dokumentasi audit
                                                    satupun?
                                                </p>
                                                <div className="flex gap-2 mt-1">
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            setShowNoPhotoWarning(
                                                                false,
                                                            )
                                                        }
                                                        className="flex-1 h-8 bg-white border border-amber-200 text-amber-700 rounded-lg text-[9px] font-bold"
                                                    >
                                                        Batal
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setShowNoPhotoWarning(
                                                                false,
                                                            );
                                                            proceedSubmit();
                                                        }}
                                                        className="flex-1 h-8 bg-amber-500 text-white rounded-lg text-[9px] font-bold shadow-sm hover:bg-amber-600"
                                                    >
                                                        Ya, Tetap Simpan
                                                    </button>
                                                </div>
                                            </div>
                                        )}

                                        {gpsError && (
                                            <div className="bg-rose-50 border border-rose-200 rounded-xl p-3 flex flex-col gap-2">
                                                <p className="text-[10px] font-bold text-rose-700">
                                                    ⚠️{" "}
                                                    {gpsError === "denied"
                                                        ? "Izin GPS ditolak."
                                                        : gpsError === "timeout"
                                                          ? "GPS timeout."
                                                          : "GPS tidak tersedia."}
                                                </p>
                                                <p className="text-[9px] text-rose-600">
                                                    Pilih tindakan:
                                                </p>
                                                <div className="flex gap-2 mt-1">
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setGpsError(null);
                                                            proceedSubmit();
                                                        }}
                                                        className="flex-1 h-8 bg-white border border-rose-200 text-rose-700 rounded-lg text-[9px] font-bold"
                                                    >
                                                        Coba Lagi
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setGpsError(null);
                                                            executeSubmit(
                                                                null,
                                                                null,
                                                            );
                                                        }}
                                                        className="flex-1 h-8 bg-rose-600 text-white rounded-lg text-[9px] font-bold shadow-sm hover:bg-rose-700"
                                                    >
                                                        Simpan Tanpa Koordinat
                                                    </button>
                                                </div>
                                            </div>
                                        )}

                                        <button
                                            type="submit"
                                            disabled={
                                                processing || isGettingLocation
                                            }
                                            className="w-full h-10 bg-indigo-600 text-white rounded-lg text-xs md:text-sm font-bold shadow-md shadow-indigo-600/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                        >
                                            {(processing ||
                                                isGettingLocation) && (
                                                <div className="w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></div>
                                            )}
                                            <span>
                                                {processing || isGettingLocation
                                                    ? "Menyimpan..."
                                                    : "Simpan Hasil Audit"}
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </ErrorBoundary>
                    </div>
                </div>
            )}

            {/* Bottom Navigation */}
            <div className="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-100 shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.1)] pb-[env(safe-area-inset-bottom)]">
                <div className="flex items-center justify-around p-2 max-w-2xl mx-auto">
                    <button
                        onClick={() => {
                            setActiveTab("list");
                        }}
                        className={`flex flex-col items-center justify-center w-full py-2 gap-1 rounded-xl transition-all ${activeTab === "list" ? "text-indigo-600" : "text-slate-400 hover:text-slate-600"}`}
                    >
                        <ListBulletIcon
                            className={`w-6 h-6 ${activeTab === "list" ? "text-indigo-600 scale-110" : "scale-100"} transition-transform`}
                        />
                        <span
                            className={`text-[10px] font-bold ${activeTab === "list" ? "text-indigo-600" : ""}`}
                        >
                            Daftar Toko
                        </span>
                    </button>
                    <button
                        onClick={() => {
                            setActiveTab("report");
                        }}
                        className={`flex flex-col items-center justify-center w-full py-2 gap-1 rounded-xl transition-all ${activeTab === "report" ? "text-indigo-600" : "text-slate-400 hover:text-slate-600"}`}
                    >
                        <div className="relative">
                            <ChartPieIcon
                                className={`w-6 h-6 ${activeTab === "report" ? "text-indigo-600 scale-110" : "scale-100"} transition-transform`}
                            />
                            {allMyReports.length > 0 && (
                                <span className="absolute -top-1.5 -right-2 bg-rose-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded-full shadow-sm">
                                    {allMyReports.length > 99
                                        ? "99+"
                                        : allMyReports.length}
                                </span>
                            )}
                        </div>
                        <span
                            className={`text-[10px] font-bold ${activeTab === "report" ? "text-indigo-600" : ""}`}
                        >
                            Laporan
                        </span>
                    </button>
                </div>
            </div>

            {/* Success Confirmation Modal */}
            {showSuccessModal && (
                <div className="fixed inset-0 z-[70] flex items-center justify-center p-4">
                    <div
                        className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                        onClick={() => setShowSuccessModal(false)}
                    ></div>
                    <div className="relative w-full max-w-sm bg-white rounded-3xl shadow-xl flex flex-col items-center p-8 animate-fade-in z-[71]">
                        <div className="w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 mb-6">
                            <CheckCircleIcon className="w-12 h-12" />
                        </div>
                        <h4 className="text-lg font-black text-slate-800 text-center mb-2">
                            Kerja Bagus!
                        </h4>
                        <p className="text-sm text-slate-500 text-center mb-8 leading-relaxed">
                            Data audit toko berhasil disimpan dengan aman ke
                            dalam sistem.
                        </p>
                        <button
                            onClick={() => setShowSuccessModal(false)}
                            className="w-full h-12 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-600/20 hover:bg-indigo-700 transition-colors"
                        >
                            Lanjut Audit
                        </button>
                    </div>
                </div>
            )}

            {/* Discard Changes Modal */}
            {showDiscardModal && (
                <div className="fixed inset-0 z-[70] flex items-center justify-center p-4">
                    <div
                        className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                        onClick={() => setShowDiscardModal(false)}
                    ></div>
                    <div className="relative w-full max-w-sm bg-white rounded-3xl shadow-xl flex flex-col items-center p-6 animate-fade-in z-[71]">
                        <div className="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 mb-4">
                            <ShieldExclamationIcon className="w-8 h-8" />
                        </div>
                        <h4 className="text-sm md:text-base font-black text-slate-800 text-center mb-2">
                            Buang Perubahan?
                        </h4>
                        <p className="text-[11px] text-slate-500 text-center mb-6 leading-relaxed">
                            Anda memiliki form yang belum disimpan. Yakin ingin
                            membuang semua perubahan?
                        </p>
                        <div className="flex w-full gap-3">
                            <button
                                onClick={() => setShowDiscardModal(false)}
                                className="flex-1 h-11 border border-slate-200 bg-white rounded-xl text-xs md:text-sm font-bold text-slate-700 hover:bg-slate-50"
                            >
                                Lanjut Edit
                            </button>
                            <button
                                onClick={() => {
                                    setShowDiscardModal(false);
                                    setDetailOutlet(null);
                                    setShowNoPhotoWarning(false);
                                    setIsFormTouched(false);
                                    reset();
                                }}
                                className="flex-1 h-11 bg-amber-500 text-white rounded-xl text-xs md:text-sm font-bold shadow-md shadow-amber-500/20 hover:bg-amber-600"
                            >
                                Ya, Buang
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Delete Confirmation Modal */}
            {deletingReport && (
                <div className="fixed inset-0 z-[70] flex items-center justify-center p-4">
                    <div
                        className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                        onClick={() => setDeletingReport(null)}
                    ></div>
                    <div className="relative w-full max-w-sm bg-white rounded-3xl shadow-xl flex flex-col items-center p-6 animate-fade-in z-[71]">
                        <div className="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-4">
                            <TrashIcon className="w-8 h-8" />
                        </div>
                        <h4 className="text-sm md:text-base font-black text-slate-800 text-center mb-2">
                            Hapus Hasil Audit?
                        </h4>
                        <p className="text-[11px] text-slate-500 text-center mb-6 leading-relaxed">
                            Tindakan ini tidak dapat dibatalkan. Hasil audit
                            untuk toko <br />
                            <span className="font-bold text-slate-800">
                                {deletingReport.customer_name}
                            </span>{" "}
                            akan dihapus permanen.
                        </p>
                        <div className="flex w-full gap-3">
                            <button
                                onClick={() => setDeletingReport(null)}
                                className="flex-1 h-11 border border-slate-200 bg-white rounded-xl text-xs md:text-sm font-bold text-slate-700 hover:bg-slate-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={confirmDeleteReport}
                                className="flex-1 h-11 bg-rose-600 text-white rounded-xl text-xs md:text-sm font-bold shadow-md shadow-rose-600/20 hover:bg-rose-700"
                            >
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Image Zoom Modal */}
            {zoomedImage && (
                <div className="fixed inset-0 z-[60] flex items-center justify-center p-4">
                    <div
                        className="absolute inset-0 bg-slate-950/90 backdrop-blur-sm"
                        onClick={() => setZoomedImage(null)}
                    ></div>
                    <div className="relative w-full max-w-sm max-h-[80vh] flex flex-col items-center justify-center animate-fade-in z-[61]">
                        <button
                            onClick={() => setZoomedImage(null)}
                            className="fixed top-4 right-4 md:top-6 md:right-6 text-white/80 hover:text-white p-2.5 rounded-full bg-slate-800/80 hover:bg-slate-700/80 backdrop-blur-sm z-[70] shadow-lg transition-colors"
                        >
                            <XMarkIcon className="w-6 h-6" />
                        </button>
                        <img
                            src={zoomedImage}
                            alt="Zoomed"
                            className="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl ring-1 ring-white/10 relative z-[61]"
                        />
                    </div>
                </div>
            )}

            {/* Custom Toast Notification */}
            {toast && (
                <div
                    className="fixed top-20 left-1/2 transform -translate-x-1/2 z-[100] max-w-[90vw] px-4 py-2.5 rounded-2xl shadow-xl shadow-black/10 flex items-center gap-2.5 transition-all animate-fade-in-down text-center"
                    style={{
                        backgroundColor:
                            toast.type === "success"
                                ? "#10b981"
                                : toast.type === "warning"
                                  ? "#f59e0b"
                                  : "#f43f5e",
                        color: "white",
                    }}
                >
                    {toast.type === "success" ? (
                        <CheckCircleIcon className="w-5 h-5 shrink-0" />
                    ) : toast.type === "warning" ? (
                        <InformationCircleIcon className="w-5 h-5 shrink-0" />
                    ) : (
                        <XCircleIcon className="w-5 h-5 shrink-0" />
                    )}
                    <span className="text-xs md:text-sm font-bold tracking-wide leading-snug">
                        {toast.message}
                    </span>
                </div>
            )}

            {/* Logout Modal */}
            {showLogoutModal && (
                <div className="fixed inset-0 z-[70] flex items-center justify-center p-4">
                    <div
                        className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                        onClick={() => setShowLogoutModal(false)}
                    ></div>
                    <div className="relative w-full max-w-sm bg-white rounded-3xl shadow-xl flex flex-col items-center p-6 animate-fade-in z-[71]">
                        <div className="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mb-4">
                            <ShieldExclamationIcon className="w-8 h-8" />
                        </div>
                        <h4 className="text-sm md:text-base font-black text-slate-800 text-center mb-2">
                            Ganti Auditor?
                        </h4>
                        <p className="text-[11px] text-slate-500 text-center mb-6 leading-relaxed">
                            Apakah Anda yakin ingin keluar dari identitas
                            auditor saat ini? Data yang belum tersimpan akan
                            hilang.
                        </p>
                        <div className="flex w-full gap-3">
                            <button
                                onClick={() => setShowLogoutModal(false)}
                                className="flex-1 h-11 border border-slate-200 bg-white rounded-xl text-xs md:text-sm font-bold text-slate-700 hover:bg-slate-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={confirmLogoutAuditor}
                                className="flex-1 h-11 bg-rose-600 text-white rounded-xl text-xs md:text-sm font-bold shadow-md shadow-rose-600/20 hover:bg-rose-700"
                            >
                                Ya, Ganti
                            </button>
                        </div>
                    </div>
                </div>
            )}
            {/* Global Loading Overlay */}
            {isAnyProcessLoading && (
                <div
                    className="fixed inset-0 flex items-center justify-center p-4"
                    style={{ zIndex: 99999 }}
                >
                    <div className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
                    <div className="relative bg-white rounded-3xl p-6 shadow-2xl flex flex-col items-center animate-zoom-in">
                        <div className="w-14 h-14 rounded-full border-4 border-indigo-100 border-t-indigo-600 animate-spin mb-4"></div>
                        <h4 className="text-sm md:text-base font-black text-slate-800">
                            Sedang Memproses...
                        </h4>
                        <p className="text-[11px] md:text-xs text-slate-500 mt-1">
                            Mohon tunggu sebentar.
                        </p>
                    </div>
                </div>
            )}

            {/* Export Range Date Modal */}
            {showExportModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-xl animate-in zoom-in-95 duration-200">
                        <div className="p-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 className="font-bold text-slate-800 flex items-center gap-2 text-sm">
                                Export Hasil Audit
                            </h3>
                            <button
                                onClick={() => setShowExportModal(false)}
                                className="p-1.5 hover:bg-slate-100 rounded-lg transition-colors"
                            >
                                <XMarkIcon className="w-5 h-5 text-slate-400" />
                            </button>
                        </div>
                        <div className="p-4 space-y-4">
                            <div>
                                <label className="block text-[11px] font-bold text-slate-700 mb-1">
                                    Tanggal Mulai
                                </label>
                                <input
                                    type="date"
                                    value={exportStartDate}
                                    onChange={(e) => setExportStartDate(e.target.value)}
                                    className="w-full text-sm md:text-base px-3 py-2 border border-slate-200 rounded-lg outline-none bg-slate-50 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                />
                            </div>
                            <div>
                                <label className="block text-[11px] font-bold text-slate-700 mb-1">
                                    Tanggal Akhir
                                </label>
                                <input
                                    type="date"
                                    value={exportEndDate}
                                    onChange={(e) => setExportEndDate(e.target.value)}
                                    className="w-full text-sm md:text-base px-3 py-2 border border-slate-200 rounded-lg outline-none bg-slate-50 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                />
                            </div>
                        </div>
                        <div className="p-4 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button
                                onClick={() => setShowExportModal(false)}
                                className="flex-1 px-4 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl text-sm shadow-sm hover:bg-slate-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={async () => {
                                    setIsExporting(true);
                                    try {
                                        let url = `/mobile/audit/export?auditor=${sessionAuditor}`;
                                        if (exportStartDate) url += `&start_date=${exportStartDate}`;
                                        if (exportEndDate) url += `&end_date=${exportEndDate}`;
                                        
                                        const response = await fetch(url);
                                        const blob = await response.blob();
                                        const downloadUrl = window.URL.createObjectURL(blob);
                                        const a = document.createElement("a");
                                        a.href = downloadUrl;

                                        const disposition = response.headers.get("content-disposition");
                                        let filename = `hasil_audit_${new Date().toISOString().slice(0, 10).replace(/-/g, "")}.xlsx`;
                                        if (disposition && disposition.indexOf("attachment") !== -1) {
                                            const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                                            if (matches != null && matches[1]) {
                                                filename = matches[1].replace(/['"]/g, "");
                                            }
                                        }

                                        a.download = filename;
                                        document.body.appendChild(a);
                                        a.click();
                                        a.remove();
                                        window.URL.revokeObjectURL(downloadUrl);
                                        setShowExportModal(false);
                                    } catch (error) {
                                        console.error("Export failed:", error);
                                        showToast("Gagal mengunduh file export", "error");
                                    } finally {
                                        setIsExporting(false);
                                    }
                                }}
                                disabled={isExporting}
                                className="flex-1 px-4 py-2 bg-emerald-600 text-white font-bold rounded-xl text-sm shadow-sm shadow-emerald-500/20 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                {isExporting ? "Exporting..." : "Download"}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
