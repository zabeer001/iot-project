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
            overflow: hidden
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
            background: #10b981
        }

        /* >>> graph & right-side metrics inside card body */
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
            color: var(--ink)
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

        // -------- APIs (your originals) --------
        async function login() {

            console.log("Access Key:", "{{ $credentials?->accessKey }}");
            console.log("Secret Key:", "{{ $credentials?->secretKey }}");

            const res = await fetch("https://public-api.recasoft.com/api/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    // "accessKey": "bc5559bd-9fdf-4fb9-b2cc-7daf60a4320b-803b1eac-50f2-440e-8ef3-78cea0fa8e52",
                    // "secretKey": "007b7478-8004-489f-b40a-611468390219-b07810e4-f6a0-441b-a916-1793b58bc2b2",
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

        // -------- UI: grid + charts --------
        async function renderGrid(range = '1d') {
            const grid = document.getElementById('deviceGrid');
            grid.innerHTML = '';
            if (!sensorsData?.data?.length) return;

            const {
                from,
                to
            } = getTimeRange(range);

            for (const sensor of sensorsData.data) {
                const m = await getSensorMeasurements(sensor.id, from, to);
                const rows = m?.data ?? [];

                const vals = rows.map(r => Number(r.value)).filter(v => !Number.isNaN(v));
                const min = vals.length ? Math.min(...vals) : null;
                const max = vals.length ? Math.max(...vals) : null;
                const avg = vals.length ? vals.reduce((a, b) => a + b, 0) / vals.length : null;
                const latest = rows[0] || null;

                const latestVal = latest ? `${Number(latest.value).toFixed(2)}°C` : 'N/A';
                const latestTs = latest ? new Date(latest.date).toLocaleString() : '';
                const avgStr = avg !== null ? `${avg.toFixed(2)}°C` : '—';
                const maxStr = max !== null ? `${max.toFixed(2)}°C` : '—';

                const card = document.createElement('div');
                card.className = 'device-card';
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
                    <div class="value">${latestVal}</div>
                    <div class="meta">${new Date(latestTs).toLocaleString([], { hour12: false })}</div> <!-- 24-hour format -->
                </div>
                </div>
        </div>

        <div class="card-foot">
          
                    <div class="meta">Avg <b>${avgStr}</b></div>
                    <div class="meta">Max <b>${maxStr}</b></div>
                    <div class="meta">Min <b>${min!==null ? (min.toFixed(2) + '°C') : '—'}</b></div>
             
         
        </div>
      `;
                grid.appendChild(card);

                // labels & data (chronological)
                const labels = rows.map(d => formatXLabel(d.date, range)).reverse();
                const data = rows.map(d => Number(d.value)).reverse();

                if (charts[sensor.id]) charts[sensor.id].destroy();

                charts[sensor.id] = new Chart(card.querySelector(`#spark-${sensor.id}`).getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: sensor.name,
                            data,
                            borderWidth: 1.6,
                            fill: false,
                            pointRadius: 0,
                            pointHitRadius: 6,
                            tension: 0.3,
                            borderColor: "#3F484B", // 🔹 changed from default
                            backgroundColor: "#3F484B"
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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
                            // Y = thermometer scale
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
                            // X = time labels by range
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
                loginData = await login();
                deviceAuthData = await deviceAuth(loginData);
                sensorsData = await getSensors(deviceAuthData);
                await renderGrid('1d');
                setInterval(() => {
                    const active = document.querySelector('#rangeToggle .active')?.dataset.range || '1d';
                    renderGrid(active);
                }, 300000);
            } catch (err) {
                console.error(err);
                alert("Failed to load dashboard data. Check network or API credentials.");
            }
        })();
    </script>
@endsection
