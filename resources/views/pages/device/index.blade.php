@extends('layout.master')

@section('title')
    @parent
    Companies
@endsection

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
@endpush

@push('custom-styles')
    <style>
        .mr_8 {
            margin-right: 8px;
        }

        #chartsWrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .chart-container {
            width: calc(50% - 10px);
            max-width: 600px;
            height: 400px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }

        @media (max-width: 768px) {
            .chart-container {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div style="margin-bottom: 20px;">
        <label for="timeRange">Select Time Range: </label>
        <select id="timeRange">
            <option value="1d">Last 1 Day</option>
            <option value="1w">Last 1 Week</option>
        </select>
    </div>
    <div id="chartsWrapper"></div>

    <script>
        let loginData;
        let deviceAuthData;
        let sensorsData;

        // Helper to calculate timestamps for time range
        function getTimeRange(range) {
            const now = new Date();
            let start;

            if (range === '1d') {
                start = new Date(now.getTime() - 24 * 60 * 60 * 1000); // last 1 day
            } else if (range === '1w') {
                start = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000); // last 1 week
            } else {
                start = new Date(now.getTime() - 24 * 60 * 60 * 1000); // default 1 day
            }

            return {
                from: start.toISOString(),
                to: now.toISOString()
            };
        }

        // Login API
        async function login() {
            const response = await fetch("https://public-api.recasoft.com/api/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "accessKey": "bc5559bd-9fdf-4fb9-b2cc-7daf60a4320b-803b1eac-50f2-440e-8ef3-78cea0fa8e52",
                    "secretKey": "007b7478-8004-489f-b40a-611468390219-b07810e4-f6a0-441b-a916-1793b58bc2b2"
                }
            });
            return await response.json();
        }

        // Device auth API
        async function deviceAuth(loginData) {
            const response = await fetch("https://public-api.recasoft.com/api/sensors-auth", {
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
            return await response.json();
        }

        // Get all sensors
        async function getSensors(deviceAuthData) {
            const response = await fetch("https://public-api.recasoft.com/api/sensors", {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${deviceAuthData.accessToken}`,
                    "X-ProjectId": loginData.projectId
                }
            });
            return await response.json();
        }

        // Fetch sensor measurements
        async function getSensorMeasurements(sensorId, from, to) {
            const url = `https://public-api.recasoft.com/api/sensors/${sensorId}/measurements?from=${from}&to=${to}`;
            const response = await fetch(url, {
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${deviceAuthData.accessToken}`,
                    "X-ProjectId": loginData.projectId
                }
            });
            return await response.json();
        }

        // Render charts for all sensors
        async function renderSensorCharts(timeRange) {
            const { from, to } = getTimeRange(timeRange);
            console.log("Selected time range:", timeRange, from, to);

            sensorsData = sensorsData.data;
            const chartsWrapper = document.getElementById('chartsWrapper');
            chartsWrapper.innerHTML = '';

            for (let i = 0; i < sensorsData.length; i++) {
                const sensor = sensorsData[i];

                // Get actual measurements
                const measurements = await getSensorMeasurements(sensor.id, from, to);

                // Extract labels and values
                const labels = measurements.data.map(d => new Date(d.date).toLocaleString());
                const values = measurements.data.map(d => d.value);

                // Create container & canvas
                const container = document.createElement('div');
                container.classList.add('chart-container');

                const canvas = document.createElement('canvas');
                canvas.id = `chart-${i}`;
                container.appendChild(canvas);
                chartsWrapper.appendChild(container);

                // Render chart
                new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: sensor.name,
                            data: values,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: sensor.name
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        // Master function
        const runApis = async (timeRange) => {
            loginData = await login();
            deviceAuthData = await deviceAuth(loginData);
            sensorsData = await getSensors(deviceAuthData);

            await renderSensorCharts(timeRange);
        }

        // Call once on page load with default value
        runApis(document.getElementById('timeRange').value);

        // Call again whenever dropdown changes
        document.getElementById('timeRange').addEventListener('change', (e) => {
            runApis(e.target.value);
        });
    </script>
@endsection
