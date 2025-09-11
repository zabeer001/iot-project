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

        #currentTempWrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .temp-card {
            width: calc(25% - 15px);
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
            text-align: center;
        }

        .temp-card h3 {
            margin: 0 0 10px 0;
            font-size: 1.2rem;
            color: #333;
        }

        .temp-card p {
            margin: 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .chart-container {
            width: calc(50% - 10px);
            max-width: 600px;
            height: 400px;
            background: white;
            border-radius: 1px;
            padding: 1px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }

        @media (max-width: 768px) {
            .chart-container {
                width: 100%;
            }

            .temp-card {
                width: calc(50% - 10px);
            }
        }

        @media (max-width: 480px) {
            .temp-card {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 20px; gap: 10px;">
        <label for="timeRange" style="font-weight: 500; color: #333;">Select Time Range:</label>
        <select id="timeRange"
            style="
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #fff;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    ">
            <option value="1d">Last 1 Day</option>
            <option value="1w">Last 1 Week</option>
        </select>
    </div>
    <div id="currentTempWrapper"></div>
    <div id="chartsWrapper"></div>

    <script>
        let loginData;
        let deviceAuthData;
        let sensorsData;
        let chartInstances = {}; // Added to store and manage chart instances

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

        // Render current temperature cards
        async function renderCurrentTempCards() {
            const tempWrapper = document.getElementById('currentTempWrapper');
            tempWrapper.innerHTML = '';
            
            // Re-use sensorsData, no need to re-fetch
            if (!sensorsData || !sensorsData.data) {
                console.error("Sensors data not available.");
                return;
            }

            for (let sensor of sensorsData.data) {
                const {
                    from,
                    to
                } = getTimeRange('1d');
                const measurements = await getSensorMeasurements(sensor.id, from, to);

                let latestMeasurement = 'N/A';
                let latestTime = 'N/A';

                if (measurements.data.length > 0) {
                    const lastData = measurements.data[0];
                    latestMeasurement = lastData.value;
                    const dateObj = new Date(lastData.date);
                    latestTime = dateObj.toLocaleString();
                }

                const card = document.createElement('div');
                card.classList.add('temp-card');
                card.innerHTML = `
            <h3>${sensor.name}</h3>
            <p>${latestMeasurement}°C</p>
            <small>Measured at: ${latestTime}</small>
        `;
                tempWrapper.appendChild(card);
            }
        }

        // Render charts for all sensors
        async function renderSensorCharts(timeRange) {
            // Destroy existing charts to prevent memory leaks and rendering issues
            for (let chartId in chartInstances) {
                if (chartInstances[chartId] && typeof chartInstances[chartId].destroy === 'function') {
                    chartInstances[chartId].destroy();
                }
            }
            chartInstances = {};

            const {
                from,
                to
            } = getTimeRange(timeRange);
            const chartsWrapper = document.getElementById('chartsWrapper');
            chartsWrapper.innerHTML = '';
            
            // Re-use sensorsData, no need to re-fetch
            if (!sensorsData || !sensorsData.data) {
                console.error("Sensors data not available.");
                return;
            }

            for (let i = 0; i < sensorsData.data.length; i++) {
                const sensor = sensorsData.data[i];
                const measurements = await getSensorMeasurements(sensor.id, from, to);

                const labels = measurements.data.map(d => new Date(d.date).toLocaleString());
                const values = measurements.data.map(d => d.value);

                const container = document.createElement('div');
                container.classList.add('chart-container');
                const canvas = document.createElement('canvas');
                canvas.id = `chart-${i}`;
                container.appendChild(canvas);
                chartsWrapper.appendChild(container);

                const newChart = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: sensor.name,
                            data: values,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            tension: 0,
                            fill: false,
                            pointRadius: 0,
                            pointHoverRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: sensor.name
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value * 100;
                                    }
                                }
                            }
                        }
                    }
                });
                chartInstances[`chart-${i}`] = newChart;
            }
        }

        // The core logic of the dashboard
        const initializeDashboard = async () => {
            try {
                // One-time authentication and sensor list fetch
                loginData = await login();
                deviceAuthData = await deviceAuth(loginData);
                sensorsData = await getSensors(deviceAuthData);

                // Initial render of cards and charts
                await renderCurrentTempCards();
                await renderSensorCharts(document.getElementById('timeRange').value);

                // Add event listener for time range changes
                document.getElementById('timeRange').addEventListener('change', (e) => {
                    renderSensorCharts(e.target.value);
                });

                // Set up auto-refresh
                setInterval(() => {
                    refreshDashboard();
                }, 300000); // 5 minutes in milliseconds

            } catch (error) {
                console.error("Error initializing dashboard:", error);
                // Display a user-friendly error message
                alert("Failed to load dashboard data. Please check your network connection or API credentials.");
            }
        };

        // Function to refresh the dynamic data (measurements)
        const refreshDashboard = async () => {
            console.log("Refreshing dashboard data...");
            try {
                // Only re-render the charts and cards with the latest measurements
                await renderCurrentTempCards();
                await renderSensorCharts(document.getElementById('timeRange').value);
            } catch (error) {
                console.error("Error refreshing dashboard:", error);
            }
        };

        // Start the application
        initializeDashboard();
    </script>
@endsection