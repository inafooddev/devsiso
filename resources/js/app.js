import './bootstrap';
import 'alpinejs';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix icon paths untuk Leaflet
import icon from 'leaflet/dist/images/marker-icon.png';
import iconShadow from 'leaflet/dist/images/marker-shadow.png';
import iconRetina from 'leaflet/dist/images/marker-icon-2x.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: iconRetina,
    iconUrl: icon,
    shadowUrl: iconShadow,
});

// Make Leaflet globally available
window.L = L;