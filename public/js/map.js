// ─── JaanaHai Map Utilities ───────────────────────────────────────────────

const JaanaHaiMap = {

    // Initialize a basic map in a div
    init: function(elementId, lat, lng, zoom) {
        lat  = lat  || 22.3072;   // default: Vadodara
        lng  = lng  || 73.1812;
        zoom = zoom || 12;

        const map = L.map(elementId).setView([lat, lng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        return map;
    },

    // Draw a route between two place names using OSRM
    drawRoute: function(map, fromPlace, toPlace) {
        Promise.all([
            JaanaHaiMap.geocode(fromPlace),
            JaanaHaiMap.geocode(toPlace)
        ]).then(([from, to]) => {
            if (!from || !to) return;

            const fromIcon = L.divIcon({
                html: '<div style="background:#22c55e;width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.4)"></div>',
                iconSize: [14, 14],
                className: ''
            });
            const toIcon = L.divIcon({
                html: '<div style="background:#ef4444;width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.4)"></div>',
                iconSize: [14, 14],
                className: ''
            });

            L.marker([from.lat, from.lon], {icon: fromIcon})
             .addTo(map)
             .bindPopup('<strong>From:</strong> ' + fromPlace);

            L.marker([to.lat, to.lon], {icon: toIcon})
             .addTo(map)
             .bindPopup('<strong>To:</strong> ' + toPlace);

            // Fetch route from OSRM (free, no API key)
            const url = `https://router.project-osrm.org/route/v1/driving/${from.lon},${from.lat};${to.lon},${to.lat}?overview=full&geometries=geojson`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (!data.routes || !data.routes[0]) return;

                    const route  = data.routes[0];
                    const coords = route.geometry.coordinates.map(c => [c[1], c[0]]);

                    const routeLine = L.polyline(coords, {
                        color: '#3b82f6',
                        weight: 4,
                        opacity: 0.8
                    }).addTo(map);

                    map.fitBounds(routeLine.getBounds(), {padding: [30, 30]});

                    const km  = (route.distance / 1000).toFixed(1);
                    const min = Math.round(route.duration / 60);
                    const infoEl = document.getElementById('route-info');
                    if (infoEl) {
                        infoEl.innerHTML = `
                            <span>🛣 ${km} km</span>
                            &nbsp;&nbsp;
                            <span>⏱ ~${min} min</span>
                        `;
                    }
                })
                .catch(err => console.warn('OSRM routing error:', err));
        });
    },

    // Geocode a place name using Nominatim (free, no API key)
    geocode: function(placeName) {
        const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(placeName)},India&format=json&limit=1`;
        return fetch(url, { headers: { 'Accept-Language': 'en' } })
            .then(r => r.json())
            .then(data => data[0] || null)
            .catch(() => null);
    },

    // Autocomplete for a text input using Nominatim
    autocomplete: function(inputId, onSelect) {
        const input = document.getElementById(inputId);
        if (!input) return;

        let timeout  = null;
        let dropdown = null;

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value.trim();
            if (query.length < 3) { removeDropdown(); return; }

            timeout = setTimeout(() => {
                const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)},India&format=json&limit=5&addressdetails=1`;

                fetch(url, { headers: { 'Accept-Language': 'en' } })
                    .then(r => r.json())
                    .then(results => {
                        removeDropdown();
                        if (!results.length) return;

                        dropdown = document.createElement('ul');
                        dropdown.style.cssText = `
                            position:absolute;background:white;border:1px solid #ddd;
                            border-radius:4px;list-style:none;margin:0;padding:0;
                            width:${input.offsetWidth}px;z-index:9999;
                            box-shadow:0 2px 8px rgba(0,0,0,0.15);max-height:200px;overflow-y:auto;
                        `;

                        results.forEach(r => {
                            const li      = document.createElement('li');
                            const display = r.display_name.split(',').slice(0, 3).join(', ');
                            li.textContent = display;
                            li.style.cssText = 'padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0';
                            li.addEventListener('mouseenter', () => li.style.background = '#f5f5f5');
                            li.addEventListener('mouseleave', () => li.style.background = 'white');
                            li.addEventListener('click', () => {
                                input.value = display;
                                removeDropdown();
                                if (onSelect) onSelect(r);
                            });
                            dropdown.appendChild(li);
                        });

                        input.parentNode.style.position = 'relative';
                        input.parentNode.appendChild(dropdown);
                    });
            }, 350);
        });

        document.addEventListener('click', (e) => {
            if (e.target !== input) removeDropdown();
        });

        function removeDropdown() {
            if (dropdown) { dropdown.remove(); dropdown = null; }
        }
    }
};
