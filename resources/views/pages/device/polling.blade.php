@extends('layout.master')

@section('title')
    @parent
    Devices
@endsection

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
@endpush

@push('custom-styles')
    <style>
        :root {
            --card-bg: #fff;
            --card-br: 12px;
            --muted: #6b7280;
            --ink: #111827;
            --line: #e5e7eb;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .dashboard-title {
            font-weight: 600;
            font-size: 18px;
            color: var(--ink);
        }

        .segmented {
            display: inline-flex;
            border: 1px solid var(--line);
            border-radius: 999px;
            overflow: hidden;
            background: #fff
        }

        .segmented button {
            border: 0;
            padding: 6px 12px;
            background: transparent;
            font-size: 12px;
            color: var(--muted);
            cursor: pointer
        }

        .segmented button.active {
            background: #111827;
            color: #fff
        }

        .toolbar-right {
            display: flex;
            gap: 10px;
            align-items: center
        }

        .btn-icon {
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 10px;
            padding: 6px 10px;
            cursor: pointer
        }

        #deviceGrid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 14px
        }

        .device-card {
            grid-column: span 6/span 6;
            background: var(--card-bg);
            border: 1px solid var(--line);
            border-radius: var(--card-br);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .device-card.alert {
            border-color: #ef4444;
            background: #fef2f2;
            animation: pulse-alert 2s infinite;
        }

        @keyframes pulse-alert {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            border-bottom: 1px solid var(--line)
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .card-title .dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #10b981;
            transition: background 0.3s ease;
        }

        .card-title .dot.alert {
            background: #ef4444;
            animation: blink 1s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .card-body {
            display: flex;
            align-items: stretch;
            gap: 12px;
            padding: 8px 12px 0
        }

        .card-body .plot {
            flex: 1 1 auto;
            min-width: 0
        }

        .spark {
            width: 100%;
            height: 160px
        }

        .card-body .side {
            width: 170px;
            border-left: 1px solid var(--line);
            padding-left: 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
            gap: 4px;
            text-align: right;
        }

        .side .value {
            font-weight: 800;
            font-size: 22px;
            line-height: 1;
            color: var(--ink);
            transition: color 0.3s ease;
        }

        .side .value.alert {
            color: #ef4444;
        }

        .side .meta {
            font-size: 12px;
            color: var(--muted)
        }

        .side .meta b {
            color: var(--ink);
            font-weight: 700
        }

        .card-foot {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 10px 14px;
            border-top: 1px solid var(--line);
            gap: 18px;
            flex-wrap: wrap
        }

        .metrics {
            display: flex;
            gap: 18px;
            color: var(--muted);
            font-size: 12px
        }

        .metrics b {
            color: var(--ink);
            font-weight: 600;
            margin-left: 4px
        }

        @media(max-width:1100px) {
            .device-card {
                grid-column: span 12/span 12
            }
        }

        @media(max-width:640px) {
            .card-body {
                flex-direction: column
            }

            .card-body .side {
                width: auto;
                border-left: 0;
                border-top: 1px solid var(--line);
                padding-left: 0;
                padding-top: 8px;
                align-items: flex-start;
                text-align: left
            }
        }
    </style>
@endpush

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="toolbar">
        <div class="dashboard-title">Alex Sushi Service Dashboard</div>
        <div class="toolbar-right">
            <div class="segmented" id="rangeToggle">
                <button data-range="1d" class="active">Day</button>
                <button data-range="1w">Week</button>
                <button data-range="3m">3&nbsp;Months</button>
            </div>
            <button class="btn-icon" id="fullscreenBtn" title="Fullscreen">⤢</button>
        </div>
    </div>

    <div id="deviceGrid" aria-live="polite"></div>

    <script>
        let loginData, deviceAuthData, sensorsData;
        const charts = {};
        const deviceElements = {};
        const sensorStates = {};
        const ALERT_THRESHOLD = 28; // Temperature threshold for alerts
        let currentRange = '1d'; // Track current range globally

        // -------- helpers --------
        function getTimeRange(range) {
            const now = new Date();
            let start;
            if (range === '1d') start = new Date(now.getTime() - 1 * 24 * 60 * 60 * 1000);
            else if (range === '1w') start = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
            else if (range === '3m') start = new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000);
            else start = new Date(now.getTime() - 1 * 24 * 60 * 60 * 1000);
            return {
                from: start.toISOString(),
                to: now.toISOString()
            };
        }

        function formatXLabel(d, range) {
            const dt = (d instanceof Date) ? d : new Date(d);
            if (range === '1d') return dt.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            if (range === '1w') return dt.toLocaleDateString([], {
                weekday: 'short'
            });
            return `${dt.toLocaleString([], {month:'short'})} ${dt.getDate()}`;
        }

        // -------- APIs --------
        async function login() {
            const res = await fetch("https://public-api.recasoft.com/api/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "accessKey": "{{ $credentials->accessKey }}",
                    "secretKey": "{{ $credentials->secretKey }}",
                }
            });
            return res.json();
        }

        async function deviceAuth(loginData) {
            const res = await fetch("https://public-api.recasoft.com/api/sensors-auth", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${loginData.refresh_token}`
                },
                body: JSON.stringify({
                    projectId: loginData.projectId,
                    refreshToken: loginData.refresh_token
                })
            });
            return res.json();
        }

        async function getSensors(deviceAuthData) {
            const res = await fetch("https://public-api.recasoft.com/api/sensors", {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${deviceAuthData.accessToken}`,
                    "X-ProjectId": loginData.projectId
                }
            });
            return res.json();
        }

        async function getSensorMeasurements(sensorId, from, to) {
            const url = `https://public-api.recasoft.com/api/sensors/${sensorId}/measurements?from=${from}&to=${to}`;
            const res = await fetch(url, {
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${deviceAuthData.accessToken}`,
                    "X-ProjectId": loginData.projectId
                }
            });
            return res.json();
        }

        // -------- Real-time Updates --------
        function createDeviceCard(sensor) {
            const card = document.createElement('div');
            card.className = 'device-card';
            card.id = `device-${sensor.id}`;
            card.innerHTML = `
                <div class="card-head">
                    <div class="card-title">
                        <span class="dot"></span>
                        <span title="${sensor.name}">${sensor.name}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="plot">
                        <canvas class="spark" id="spark-${sensor.id}"></canvas>
                    </div>
                    <div class="side" style="text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <div class="value" id="value-${sensor.id}">—</div>
                        <div class="meta" id="timestamp-${sensor.id}">—</div>
                    </div>
                </div>
                <div class="card-foot">
                    <div class="meta">Avg <b id="avg-${sensor.id}">—</b></div>
                    <div class="meta">Max <b id="max-${sensor.id}">—</b></div>
                    <div class="meta">Min <b id="min-${sensor.id}">—</b></div>
                </div>
            `;

            deviceElements[sensor.id] = {
                card: card,
                value: card.querySelector(`#value-${sensor.id}`),
                timestamp: card.querySelector(`#timestamp-${sensor.id}`),
                avg: card.querySelector(`#avg-${sensor.id}`),
                max: card.querySelector(`#max-${sensor.id}`),
                min: card.querySelector(`#min-${sensor.id}`),
                canvas: card.querySelector(`#spark-${sensor.id}`),
                dot: card.querySelector('.dot')
            };

            return card;
        }

        function updateDeviceCard(sensorId, data, range) {
            const rows = data?.data ?? [];
            const vals = rows.map(r => Number(r.value)).filter(v => !Number.isNaN(v));
            const min = vals.length ? Math.min(...vals) : null;
            const max = vals.length ? Math.max(...vals) : null;
            const avg = vals.length ? vals.reduce((a, b) => a + b, 0) / vals.length : null;
            const latest = rows[0] || null;

            // Update text elements
            const elements = deviceElements[sensorId];
            const latestVal = latest ? `${Number(latest.value).toFixed(2)}°C` : 'N/A';
            const latestTs = latest ? new Date(latest.date).toLocaleString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }) : '';
            elements.value.textContent = latestVal;
            elements.timestamp.textContent = latestTs;

            const avgStr = avg !== null ? `${avg.toFixed(2)}°C` : '—';
            const maxStr = max !== null ? `${max.toFixed(2)}°C` : '—';
            const minStr = min !== null ? `${min.toFixed(2)}°C` : '—';

            elements.avg.textContent = avgStr;
            elements.max.textContent = maxStr;
            elements.min.textContent = minStr;

            // Check for alerts and update UI state
            const currentTemp = latest ? Number(latest.value) : null;
            const isAlert = currentTemp !== null && currentTemp > ALERT_THRESHOLD;

            updateAlertState(sensorId, isAlert, currentTemp);

            // Update chart with smooth transition
            updateChartData(sensorId, rows, range, isAlert);
        }

        function updateAlertState(sensorId, isAlert, currentTemp) {
            const elements = deviceElements[sensorId];
            const previousState = sensorStates[sensorId]?.isAlert || false;

            // Only update if state changed
            if (previousState !== isAlert) {
                if (isAlert) {
                    elements.card.classList.add('alert');
                    elements.value.classList.add('alert');
                    elements.dot.classList.add('alert');

                    // Show browser notification for critical alerts
                    if (Notification.permission === 'granted') {
                        new Notification('Temperature Alert!', {
                            body: `Sensor ${sensorId} temperature: ${currentTemp}°C - Above threshold!`,
                            icon: '/warning-icon.png',
                            tag: `alert-${sensorId}`
                        });
                    }
                } else {
                    elements.card.classList.remove('alert');
                    elements.value.classList.remove('alert');
                    elements.dot.classList.remove('alert');
                }

                // Update state
                sensorStates[sensorId] = {
                    isAlert,
                    lastUpdate: Date.now()
                };
            }
        }

        function updateChartData(sensorId, rows, range, isAlert = false) {
            const labels = rows.map(d => formatXLabel(d.date, range)).reverse();
            const data = rows.map(d => Number(d.value)).reverse();

            if (charts[sensorId]) {
                const chart = charts[sensorId];
                const currentData = chart.data.datasets[0].data;
                const dataChanged = JSON.stringify(currentData) !== JSON.stringify(data);

                if (dataChanged) {
                    chart.data.labels = labels;
                    chart.data.datasets[0].data = data;

                    // Update colors for alert state
                    const borderColor = isAlert ? '#ef4444' : '#3F484B';
                    const backgroundColor = isAlert ? '#ef4444' : '#3F484B';

                    chart.data.datasets[0].borderColor = borderColor;
                    chart.data.datasets[0].backgroundColor = backgroundColor;

                    // Ensure points remain disabled
                    chart.data.datasets[0].pointRadius = 0;
                    chart.data.datasets[0].pointHoverRadius = 0;

                    chart.update('active');
                }
            } else {
                createChart(sensorId, labels, data, range, isAlert);
            }
        }

        function createChart(sensorId, labels, data, range, isAlert = false) {
            const ctx = deviceElements[sensorId].canvas.getContext('2d');
            const borderColor = isAlert ? '#ef4444' : '#3F484B';
            const backgroundColor = isAlert ? '#ef4444' : '#3F484B';

            charts[sensorId] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Temperature',
                        data,
                        borderWidth: 1.6,
                        fill: false,
                        pointRadius: 0,
                        pointHitRadius: 6,
                        tension: 0.3,
                        borderColor: borderColor,
                        backgroundColor: backgroundColor
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000, // Smooth animations
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: (c) => `${c.parsed.y} °C`
                            }
                        }
                    },
                    scales: {
                        y: {
                            display: true,
                            grid: {
                                color: 'rgba(0,0,0,0.06)'
                            },
                            ticks: {
                                callback: v => `${v}°C`,
                                autoSkip: true,
                                maxTicksLimit: 6
                            }
                        },
                        x: {
                            display: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: (range === '1d' ? 12 : range === '1w' ? 7 : 10)
                            }
                        }
                    }
                }
            });
        }

        // Add this function to generate dummy data
        function generateDummyData(sensorId, range) {
            const now = new Date();
            const dataPoints = [];
            const baseTemp = 22 + (Math.random() * 3); // Base temp between 22-25°C

            // Generate data points based on range
            let points = 24; // Default for day
            if (range === '1w') points = 7;
            if (range === '3m') points = 12;

            for (let i = points - 1; i >= 0; i--) {
                const time = new Date(now.getTime() - i * (range === '1d' ? 3600000 : range === '1w' ? 86400000 :
                    2592000000));
                const variation = Math.sin(i * 0.5) * 2 + (Math.random() * 1 - 0.5);
                const value = (baseTemp + variation).toFixed(2);

                dataPoints.push({
                    date: time.toISOString(),
                    value: value
                });
            }

            return {
                data: dataPoints
            };
        }

        const USE_DUMMY_DATA = true; // Set to true for testing, false for real API

        // Dummy data generator function
        function generateDummyData(sensorId, range) {
            const now = new Date();
            const dataPoints = [];
            const baseTemp = 22 + (Math.random() * 3); // Base temp between 22-25°C

            // Generate data points based on range
            let points = 24; // Default for day
            if (range === '1w') points = 7;
            if (range === '3m') points = 12;

            for (let i = points - 1; i >= 0; i--) {
                const time = new Date(now.getTime() - i * (range === '1d' ? 3600000 : range === '1w' ? 86400000 :
                    2592000000));
                const variation = Math.sin(i * 0.5) * 2 + (Math.random() * 1 - 0.5);
                const value = (baseTemp + variation).toFixed(2);

                dataPoints.push({
                    date: time.toISOString(),
                    value: value
                });
            }

            return {
                data: dataPoints
            };
        }

        async function renderGrid(range = '1d') {
            console.log('🔄 renderGrid called at:', new Date().toLocaleTimeString());

            const grid = document.getElementById('deviceGrid');
            const {
                from,
                to
            } = getTimeRange(range);

            // Update current range
            currentRange = range;

            // Initial render - create cards only once
            if (!sensorsData?.data?.length) return;

            if (Object.keys(deviceElements).length === 0) {
                // First time - create all cards
                for (const sensor of sensorsData.data) {
                    const card = createDeviceCard(sensor);
                    grid.appendChild(card);
                }
            }

            const updatePromises = sensorsData.data.map(async (sensor) => {
                try {
                    if (USE_DUMMY_DATA) {
                        // DUMMY DATA MODE
                        console.log(`🧪 Using dummy data for: ${sensor.name}`);
                        const dummyData = generateDummyData(sensor.id, range);
                        updateDeviceCard(sensor.id, dummyData, range);
                        console.log(`✅ Updated ${sensor.name} with dummy data`);
                    } else {
                        // REAL API MODE
                        console.log(`📡 Fetching real data for: ${sensor.name}`);
                        const measurements = await getSensorMeasurements(sensor.id, from, to);
                        updateDeviceCard(sensor.id, measurements, range);
                        console.log(`✅ Updated ${sensor.name} with real data`);
                    }
                } catch (error) {
                    console.error(`❌ Failed to update sensor ${sensor.id}:`, error);
                }
            });

            await Promise.all(updatePromises);
        }

        // async function renderGrid(range = '1d') {
        //     console.log('hello');

        //     const grid = document.getElementById('deviceGrid');
        //     const {
        //         from,
        //         to
        //     } = getTimeRange(range);

        //     // Update current range
        //     currentRange = range;

        //     // Initial render - create cards only once
        //     if (!sensorsData?.data?.length) return;

        //     if (Object.keys(deviceElements).length === 0) {
        //         // First time - create all cards
        //         for (const sensor of sensorsData.data) {
        //             const card = createDeviceCard(sensor);
        //             grid.appendChild(card);
        //         }
        //     }

        //     async function renderGridTesting(range = '1d') {
        //         console.log('🔄 renderGrid called at:', new Date().toLocaleTimeString());

        //         const grid = document.getElementById('deviceGrid');

        //         // Update current range
        //         currentRange = range;

        //         // Initial render - create cards only once
        //         if (!sensorsData?.data?.length) return;

        //         if (Object.keys(deviceElements).length === 0) {
        //             // First time - create all cards
        //             for (const sensor of sensorsData.data) {
        //                 const card = createDeviceCard(sensor);
        //                 grid.appendChild(card);
        //             }
        //         }

        //         // TEST MODE: Use dummy data instead of API calls
        //         const updatePromises = sensorsData.data.map(async (sensor) => {
        //             try {
        //                 // Generate dummy data for testing
        //                 const dummyData = generateDummyData(sensor.id, range);
        //                 updateDeviceCard(sensor.id, dummyData, range);
        //                 console.log(`✅ Updated ${sensor.name} with dummy data`);
        //             } catch (error) {
        //                 console.error(`Failed to update sensor ${sensor.id}:`, error);
        //             }
        //         });

        //         await Promise.all(updatePromises);
        //     }


        //     // Update data for each sensor - do it in parallel for instant updates
        //     const updatePromises = sensorsData.data.map(async (sensor) => {
        //         try {
        //             const measurements = await getSensorMeasurements(sensor.id, from, to);
        //             updateDeviceCard(sensor.id, measurements, range);
        //         } catch (error) {
        //             console.error(`Failed to update sensor ${sensor.id}:`, error);
        //         }
        //     });

        //     await Promise.all(updatePromises);
        // }

        // Request notification permission
        function requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        }

        // -------- toolbar --------
        function setActiveRangeButton(range) {
            document.querySelectorAll('#rangeToggle button').forEach(b => {
                b.classList.toggle('active', b.dataset.range === range);
            });
        }

        document.getElementById('rangeToggle').addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-range]');
            if (!btn) return;
            const range = btn.dataset.range;
            setActiveRangeButton(range);
            renderGrid(range);
        });

        document.getElementById('fullscreenBtn').addEventListener('click', () => {
            const el = document.documentElement;
            if (!document.fullscreenElement) el.requestFullscreen?.();
            else document.exitFullscreen?.();
        });

        // -------- boot --------
        (async () => {
            try {
                // Request notification permissions
                requestNotificationPermission();

                loginData = await login();
                deviceAuthData = await deviceAuth(loginData);
                sensorsData = await getSensors(deviceAuthData);
                await renderGrid('1d');

                // Real-time updates - instant graph changes
                setInterval(async () => {
                    const activeRange = document.querySelector('#rangeToggle .active')?.dataset.range ||
                        '1d';
                    await renderGrid(activeRange);
                }, 1800); // Faster updates for real-time feel
            } catch (err) {
                console.error(err);
                alert("Failed to load dashboard data. Check network or API credentials.");
            }
        })();
    </script>
@endsection
