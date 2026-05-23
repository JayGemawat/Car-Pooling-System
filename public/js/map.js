// ─── JaanaHai Map Utilities ───────────────────────────────────────────────

const JaanaHaiMap = (function () {

    // Simple in-memory geocode cache to avoid hammering Nominatim
    const _geocodeCache = {};

    // Default viewbox biased toward India (used as a soft hint, not a hard filter)
    const INDIA_VIEWBOX = '68.1766451354,7.96553477623,97.4025614766,35.4940095078';

    /**
     * Geocode a place name using Nominatim.
     * - No hard country restriction so local area names (colonies, sectors, etc.) resolve.
     * - Uses India viewbox as a soft bias so Indian places rank higher.
     * - Results are cached in memory for the page lifetime.
     */
    function geocode(placeName) {
        const key = placeName.trim().toLowerCase();
        if (_geocodeCache[key]) {
            return Promise.resolve(_geocodeCache[key]);
        }

        // Try with viewbox bias first (covers local areas within India)
        const url = 'https://nominatim.openstreetmap.org/search'
            + '?q=' + encodeURIComponent(placeName)
            + '&format=json&limit=1&addressdetails=0'
            + '&viewbox=' + INDIA_VIEWBOX
            + '&bounded=0';   // bounded=0 means fall back globally if not found in viewbox

        return fetch(url, { headers: { 'Accept-Language': 'en' } })
            .then(r => r.json())
            .then(data => {
                const result = data[0] || null;
                if (result) _geocodeCache[key] = result;
                return result;
            })
            .catch(() => null);
    }

    /**
     * Autocomplete for a text input using Nominatim.
     * - Debounced at 400ms to reduce API calls.
     * - Uses viewbox bias for India + local areas.
     * - Caches results per query string.
     */
    function autocomplete(inputId, onSelect) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const _acCache = {};
        let timeout  = null;
        let dropdown = null;
        let activeIdx = -1;

        input.addEventListener('input', function () {
            clearTimeout(timeout);
            const query = this.value.trim();
            if (query.length < 2) { removeDropdown(); return; }

            timeout = setTimeout(() => {
                if (_acCache[query]) {
                    renderDropdown(_acCache[query], query);
                    return;
                }

                const url = 'https://nominatim.openstreetmap.org/search'
                    + '?q=' + encodeURIComponent(query)
                    + '&format=json&limit=7&addressdetails=1'
                    + '&viewbox=' + INDIA_VIEWBOX
                    + '&bounded=0';

                fetch(url, { headers: { 'Accept-Language': 'en' } })
                    .then(r => r.json())
                    .then(results => {
                        _acCache[query] = results;
                        renderDropdown(results, query);
                    })
                    .catch(() => {});
            }, 400);
        });

        // Keyboard navigation
        input.addEventListener('keydown', function (e) {
            if (!dropdown) return;
            const items = dropdown.querySelectorAll('li');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = Math.min(activeIdx + 1, items.length - 1);
                highlightItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = Math.max(activeIdx - 1, 0);
                highlightItem(items);
            } else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                items[activeIdx] && items[activeIdx].click();
            } else if (e.key === 'Escape') {
                removeDropdown();
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target !== input) removeDropdown();
        });

        function highlightItem(items) {
            items.forEach((li, i) => {
                li.style.background = i === activeIdx ? '#e8f0fe' : 'white';
            });
        }

        function renderDropdown(results, query) {
            removeDropdown();
            if (!results || !results.length) return;

            activeIdx = -1;
            dropdown = document.createElement('ul');
            dropdown.className = 'jaanahai-ac-dropdown';
            dropdown.style.cssText =
                'position:absolute;background:white;border:1px solid #ddd;'
                + 'border-radius:6px;list-style:none;margin:0;padding:0;'
                + 'width:' + input.offsetWidth + 'px;z-index:9999;'
                + 'box-shadow:0 4px 12px rgba(0,0,0,0.15);max-height:240px;overflow-y:auto;';

            results.forEach(r => {
                const li = document.createElement('li');
                // Show suburb/neighbourhood + city + state for local areas
                const addr = r.address || {};
                const parts = [
                    addr.neighbourhood || addr.suburb || addr.quarter || addr.hamlet,
                    addr.city || addr.town || addr.village || addr.county,
                    addr.state
                ].filter(Boolean);
                const display = parts.length ? parts.join(', ') : r.display_name.split(',').slice(0, 3).join(', ');

                li.textContent = display;
                li.dataset.fullName = display;
                li.style.cssText = 'padding:9px 13px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;';
                li.addEventListener('mouseenter', () => { li.style.background = '#f5f5f5'; });
                li.addEventListener('mouseleave', () => { li.style.background = 'white'; });
                li.addEventListener('mousedown', (e) => {
                    // mousedown fires before blur so dropdown stays open
                    e.preventDefault();
                    input.value = display;
                    removeDropdown();
                    if (onSelect) onSelect(r);
                });
                dropdown.appendChild(li);
            });

            input.parentNode.style.position = 'relative';
            input.parentNode.appendChild(dropdown);
        }

        function removeDropdown() {
            if (dropdown) { dropdown.remove(); dropdown = null; }
            activeIdx = -1;
        }
    }

    /**
     * Initialize a Leaflet map inside a div.
     */
    function init(elementId, lat, lng, zoom) {
        lat  = lat  || 22.3072;   // default: Vadodara
        lng  = lng  || 73.1812;
        zoom = zoom || 12;

        const map = L.map(elementId).setView([lat, lng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        return map;
    }

    /**
     * Draw a driving route between two place names using OSRM.
     * Shows distance + ETA in #route-info if present.
     */
    function drawRoute(map, fromPlace, toPlace) {
        Promise.all([
            geocode(fromPlace),
            geocode(toPlace)
        ]).then(([from, to]) => {
            if (!from || !to) {
                const infoEl = document.getElementById('route-info');
                if (infoEl) infoEl.innerHTML = '<span class="text-danger small">Could not locate one or both places on the map.</span>';
                return;
            }

            const fromIcon = L.divIcon({
                html: '<div style="background:#22c55e;width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.4)"></div>',
                iconSize: [14, 14], className: ''
            });
            const toIcon = L.divIcon({
                html: '<div style="background:#ef4444;width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.4)"></div>',
                iconSize: [14, 14], className: ''
            });

            L.marker([from.lat, from.lon], { icon: fromIcon })
             .addTo(map)
             .bindPopup('<strong>From:</strong> ' + fromPlace);

            L.marker([to.lat, to.lon], { icon: toIcon })
             .addTo(map)
             .bindPopup('<strong>To:</strong> ' + toPlace);

            // OSRM for actual road distance + route polyline
            const osrmUrl = 'https://router.project-osrm.org/route/v1/driving/'
                + from.lon + ',' + from.lat + ';'
                + to.lon   + ',' + to.lat
                + '?overview=full&geometries=geojson';

            fetch(osrmUrl)
                .then(r => r.json())
                .then(data => {
                    if (!data.routes || !data.routes[0]) {
                        // Fallback: straight line
                        const line = L.polyline([[from.lat, from.lon], [to.lat, to.lon]], {
                            color: '#94a3b8', weight: 3, dashArray: '6 4'
                        }).addTo(map);
                        map.fitBounds(line.getBounds(), { padding: [30, 30] });
                        return;
                    }

                    const route  = data.routes[0];
                    const coords = route.geometry.coordinates.map(c => [c[1], c[0]]);

                    const routeLine = L.polyline(coords, {
                        color: '#3b82f6', weight: 4, opacity: 0.85
                    }).addTo(map);

                    map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });

                    const km  = (route.distance / 1000).toFixed(1);
                    const min = Math.round(route.duration / 60);
                    const hrs = Math.floor(min / 60);
                    const rem = min % 60;
                    const eta = hrs > 0 ? hrs + 'h ' + rem + 'm' : rem + ' min';

                    const infoEl = document.getElementById('route-info');
                    if (infoEl) {
                        infoEl.innerHTML =
                            '<i class="bi bi-signpost-2 me-1"></i><strong>' + km + ' km</strong>'
                            + ' &nbsp;·&nbsp; '
                            + '<i class="bi bi-clock me-1"></i>~' + eta + ' drive';
                    }
                })
                .catch(() => {
                    // Fallback: straight line if OSRM fails
                    const line = L.polyline([[from.lat, from.lon], [to.lat, to.lon]], {
                        color: '#94a3b8', weight: 3, dashArray: '6 4'
                    }).addTo(map);
                    map.fitBounds(line.getBounds(), { padding: [30, 30] });
                });
        });
    }

    // Public API
    return { init, drawRoute, geocode, autocomplete };

})();
