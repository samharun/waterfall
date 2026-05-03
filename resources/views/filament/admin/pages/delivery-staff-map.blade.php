<x-filament-panels::page>
@php
    $mapData = $this->getInitialMapData();
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="wf-map-shell">
    <div class="wf-map-stats">
        <div class="wf-map-stat">
            <span>Total Staff</span>
            <strong data-map-stat="total_staff">{{ $mapData['stats']['total_staff'] }}</strong>
        </div>
        <div class="wf-map-stat">
            <span>Tracked</span>
            <strong data-map-stat="tracked">{{ $mapData['stats']['tracked'] }}</strong>
        </div>
        <div class="wf-map-stat">
            <span>Online</span>
            <strong data-map-stat="online">{{ $mapData['stats']['online'] }}</strong>
        </div>
        <div class="wf-map-stat">
            <span>Stale</span>
            <strong data-map-stat="stale">{{ $mapData['stats']['stale'] }}</strong>
        </div>
        <div class="wf-map-stat">
            <span>No Location</span>
            <strong data-map-stat="missing">{{ $mapData['stats']['missing'] }}</strong>
        </div>
    </div>

    <div class="wf-map-layout">
        <section class="wf-map-panel">
            <div class="wf-map-header">
                <div>
                    <h2>Live Staff Locations</h2>
                    <p>Last refresh: <span data-map-refreshed>Loading...</span></p>
                </div>
                <button type="button" data-map-refresh>Refresh</button>
            </div>
            <div id="delivery-staff-map" class="wf-map-canvas"></div>
            <div data-map-error class="wf-map-error" hidden></div>
        </section>

        <aside class="wf-map-list-panel">
            <div class="wf-map-list-header">
                <h3>Tracked Staff</h3>
                <span data-map-count>{{ count($mapData['markers']) }}</span>
            </div>
            <div data-map-list class="wf-map-list"></div>
        </aside>
    </div>
</div>

<style>
    .wf-map-shell {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .wf-map-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }

    .wf-map-stat {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
    }

    .wf-map-stat span {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .wf-map-stat strong {
        display: block;
        color: #0f172a;
        font-size: 26px;
        font-weight: 800;
        line-height: 1.1;
        margin-top: 6px;
    }

    .wf-map-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 16px;
        align-items: stretch;
    }

    .wf-map-panel,
    .wf-map-list-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .wf-map-header,
    .wf-map-list-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .wf-map-header h2,
    .wf-map-list-header h3 {
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        margin: 0;
    }

    .wf-map-header p {
        color: #64748b;
        font-size: 12px;
        margin: 3px 0 0;
    }

    .wf-map-header button {
        background: #0077B6;
        border: 0;
        border-radius: 8px;
        color: #fff;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        padding: 8px 14px;
    }

    .wf-map-canvas {
        height: min(68vh, 680px);
        min-height: 460px;
        width: 100%;
    }

    .wf-map-error {
        background: #fef2f2;
        border-top: 1px solid #fecaca;
        color: #991b1b;
        font-size: 13px;
        padding: 12px 16px;
    }

    .wf-map-list-header span {
        background: #e0f2fe;
        border-radius: 9999px;
        color: #0369a1;
        font-size: 12px;
        font-weight: 800;
        padding: 3px 10px;
    }

    .wf-map-list {
        max-height: min(68vh, 680px);
        min-height: 460px;
        overflow-y: auto;
    }

    .wf-map-person {
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        padding: 13px 16px;
    }

    .wf-map-person:hover {
        background: #f0f9ff;
    }

    .wf-map-person strong {
        color: #0f172a;
        display: block;
        font-size: 13px;
        font-weight: 800;
    }

    .wf-map-person small,
    .wf-map-person span {
        color: #64748b;
        display: block;
        font-size: 12px;
        margin-top: 4px;
    }

    .wf-map-status {
        align-items: center;
        display: inline-flex;
        gap: 6px;
    }

    .wf-map-status::before,
    .wf-map-marker-dot {
        border-radius: 9999px;
        content: "";
        display: inline-block;
        height: 9px;
        width: 9px;
    }

    .wf-map-status.online::before,
    .wf-map-marker-dot.online {
        background: #16a34a;
    }

    .wf-map-status.stale::before,
    .wf-map-marker-dot.stale {
        background: #f59e0b;
    }

    .wf-map-status.offline::before,
    .wf-map-marker-dot.offline {
        background: #dc2626;
    }

    .wf-map-marker {
        align-items: center;
        background: #fff;
        border: 2px solid #0077B6;
        border-radius: 9999px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .22);
        display: flex;
        height: 28px;
        justify-content: center;
        width: 28px;
    }

    @media (max-width: 1100px) {
        .wf-map-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .wf-map-layout {
            grid-template-columns: 1fr;
        }

        .wf-map-list,
        .wf-map-canvas {
            min-height: 360px;
        }
    }
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (() => {
        const initialData = @js($mapData);
        const feedUrl = @js(route('admin.delivery-staff-locations.index'));
        const defaultCenter = [23.8103, 90.4125];
        const markerLayer = {};
        let map;
        let markersGroup;
        let firstFit = true;

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        })[char]);

        const statusLabel = (status) => {
            if (status === 'online') return 'Online';
            if (status === 'stale') return 'Stale';
            return 'Offline';
        };

        const markerIcon = (status) => L.divIcon({
            className: '',
            html: `<div class="wf-map-marker"><span class="wf-map-marker-dot ${escapeHtml(status)}"></span></div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
            popupAnchor: [0, -14],
        });

        const popupHtml = (marker) => {
            const delivery = marker.delivery;

            return `
                <div style="min-width:220px">
                    <strong style="display:block;color:#0f172a;font-size:14px;margin-bottom:4px">${escapeHtml(marker.staff_name)}</strong>
                    <div style="color:#64748b;font-size:12px;margin-bottom:8px">${escapeHtml(marker.mobile || 'No mobile')}</div>
                    <div style="font-size:12px;margin-bottom:4px"><b>Status:</b> ${escapeHtml(statusLabel(marker.status))}</div>
                    <div style="font-size:12px;margin-bottom:4px"><b>Last seen:</b> ${escapeHtml(marker.last_seen || 'Unknown')}</div>
                    ${marker.accuracy ? `<div style="font-size:12px;margin-bottom:4px"><b>Accuracy:</b> ${escapeHtml(marker.accuracy)} m</div>` : ''}
                    ${marker.battery_level !== null && marker.battery_level !== undefined ? `<div style="font-size:12px;margin-bottom:4px"><b>Battery:</b> ${escapeHtml(marker.battery_level)}%</div>` : ''}
                    ${delivery ? `
                        <hr style="border:0;border-top:1px solid #e5e7eb;margin:8px 0">
                        <div style="font-size:12px;margin-bottom:4px"><b>Delivery:</b> ${escapeHtml(delivery.delivery_no || '')}</div>
                        <div style="font-size:12px;margin-bottom:4px"><b>Order:</b> ${escapeHtml(delivery.order_no || '')}</div>
                        <div style="font-size:12px;margin-bottom:4px"><b>Zone:</b> ${escapeHtml(delivery.zone || '')}</div>
                        <div style="font-size:12px"><b>Customer:</b> ${escapeHtml(delivery.party_name || '')}</div>
                    ` : '<div style="color:#64748b;font-size:12px;margin-top:8px">No active delivery assigned.</div>'}
                </div>
            `;
        };

        const updateStats = (stats) => {
            Object.entries(stats || {}).forEach(([key, value]) => {
                const element = document.querySelector(`[data-map-stat="${key}"]`);
                if (element) element.textContent = value;
            });
        };

        const renderList = (markers) => {
            const list = document.querySelector('[data-map-list]');
            const count = document.querySelector('[data-map-count]');
            if (! list) return;

            count.textContent = markers.length;

            if (! markers.length) {
                list.innerHTML = '<div style="padding:32px 16px;text-align:center;color:#94a3b8;font-size:13px">No staff locations received yet.</div>';
                return;
            }

            list.innerHTML = markers.map((marker) => {
                const delivery = marker.delivery;
                return `
                    <button type="button" class="wf-map-person" data-staff-id="${escapeHtml(marker.staff_id)}" style="background:transparent;border:0;text-align:left;width:100%">
                        <strong>${escapeHtml(marker.staff_name)}</strong>
                        <small class="wf-map-status ${escapeHtml(marker.status)}">${escapeHtml(statusLabel(marker.status))} • ${escapeHtml(marker.last_seen || 'Unknown')}</small>
                        <span>${escapeHtml(delivery?.zone || 'No active zone')} ${delivery?.delivery_no ? '• ' + escapeHtml(delivery.delivery_no) : ''}</span>
                    </button>
                `;
            }).join('');

            list.querySelectorAll('[data-staff-id]').forEach((item) => {
                item.addEventListener('click', () => {
                    const leafletMarker = markerLayer[item.dataset.staffId];
                    if (! leafletMarker) return;

                    map.setView(leafletMarker.getLatLng(), 16);
                    leafletMarker.openPopup();
                });
            });
        };

        const renderMarkers = (markers) => {
            markersGroup.clearLayers();
            Object.keys(markerLayer).forEach((key) => delete markerLayer[key]);

            markers.forEach((marker) => {
                if (! marker.latitude || ! marker.longitude) return;

                const leafletMarker = L.marker([marker.latitude, marker.longitude], {
                    icon: markerIcon(marker.status),
                    title: marker.staff_name,
                }).bindPopup(popupHtml(marker));

                leafletMarker.addTo(markersGroup);
                markerLayer[marker.staff_id] = leafletMarker;
            });

            if (firstFit && markersGroup.getLayers().length) {
                map.fitBounds(markersGroup.getBounds(), { padding: [28, 28], maxZoom: 15 });
                firstFit = false;
            }
        };

        const render = (data) => {
            const markers = data.markers || [];
            updateStats(data.stats || {});
            renderMarkers(markers);
            renderList(markers);

            const refreshed = document.querySelector('[data-map-refreshed]');
            if (refreshed) {
                refreshed.textContent = data.refreshed_at ? new Date(data.refreshed_at).toLocaleString() : 'Unknown';
            }
        };

        const refresh = async () => {
            const error = document.querySelector('[data-map-error]');

            try {
                const response = await fetch(feedUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error('Unable to load staff locations.');
                }

                const data = await response.json();
                if (error) error.hidden = true;
                render(data);
            } catch (exception) {
                if (error) {
                    error.textContent = exception.message || 'Unable to refresh map data.';
                    error.hidden = false;
                }
            }
        };

        const boot = () => {
            const canvas = document.getElementById('delivery-staff-map');
            if (! canvas || canvas.dataset.ready === '1') return;

            if (! window.L) {
                const error = document.querySelector('[data-map-error]');
                if (error) {
                    error.textContent = 'Map library could not be loaded. Please check the internet connection for Leaflet/OpenStreetMap assets.';
                    error.hidden = false;
                }
                return;
            }

            canvas.dataset.ready = '1';
            map = L.map(canvas, { scrollWheelZoom: true }).setView(defaultCenter, 11);
            markersGroup = L.layerGroup().addTo(map);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            render(initialData);
            document.querySelector('[data-map-refresh]')?.addEventListener('click', refresh);
            window.setInterval(refresh, 15000);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot, { once: true });
        } else {
            boot();
        }
    })();
</script>
</x-filament-panels::page>
