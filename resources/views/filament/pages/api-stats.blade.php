@push('scripts')
    <!-- Include ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Performance Over Time Chart
                if (document.getElementById('performance-chart')) {
                    const performanceData = @json($performanceOverTime ?? []);
                    const performanceChart = new ApexCharts(document.getElementById('performance-chart'), {
                        chart: {
                            type: 'line',
                            height: 320,
                            toolbar: {
                                show: true,
                                tools: {
                                    download: true,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: true,
                                    reset: true
                                }
                            },
                            zoom: {
                                enabled: true
                            },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: [4, 3, 3]
                        },
                        series: [{
                            name: 'Requests',
                            type: 'column',
                            data: performanceData.requests || []
                        }, {
                            name: 'Avg Latency (ms)',
                            type: 'line',
                            data: performanceData.latency || []
                        }, {
                            name: 'Error Rate (%)',
                            type: 'line',
                            data: performanceData.error_rate || []
                        }],
                        colors: ['#3B82F6', '#F59E0B', '#EF4444'],
                        dataLabels: {
                            enabled: false
                        },
                        markers: {
                            size: 5,
                            hover: {
                                size: 7
                            }
                        },
                        xaxis: {
                            categories: performanceData.dates || [],
                            labels: {
                                rotate: -45,
                                style: {
                                    fontSize: '12px'
                                },
                                formatter: function(value) {
                                    if (!value) return '';
                                    // Only show every 3rd label to reduce clutter
                                    const dates = performanceData.dates || [];
                                    const index = dates.indexOf(value);
                                    if (index % 3 === 0 || index === dates.length - 1) {
                                        // Format as MM-DD
                                        const parts = value.split('-');
                                        if (parts.length === 3) {
                                            return parts[1] + '-' + parts[2];
                                        }
                                        return value;
                                    }
                                    return '';
                                }
                            }
                        },
                        yaxis: [
                            {
                                title: {
                                    text: 'Number of Requests'
                                },
                                seriesName: 'Requests',
                                min: 0,
                                forceNiceScale: true
                            },
                            {
                                title: {
                                    text: 'Avg Latency (ms)'
                                },
                                seriesName: 'Avg Latency (ms)',
                                opposite: true,
                                min: 0,
                                forceNiceScale: true
                            },
                            {
                                title: {
                                    text: 'Error Rate (%)'
                                },
                                seriesName: 'Error Rate (%)',
                                opposite: true,
                                min: 0,
                                max: 100,
                                forceNiceScale: true
                            }
                        ],
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: function(value, { seriesIndex }) {
                                    if (seriesIndex === 0) return value.toFixed(0) + " requests";
                                    if (seriesIndex === 1) return value.toFixed(2) + " ms";
                                    if (seriesIndex === 2) return value.toFixed(1) + "%";
                                    return value;
                                }
                            },
                            x: {
                                formatter: function(value) {
                                    // Format date as YYYY-MM-DD if it's a valid date
                                    if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                        const date = new Date(value);
                                        if (!isNaN(date.getTime())) {
                                            return date.toLocaleDateString(undefined, {
                                                year: 'numeric',
                                                month: 'short',
                                                day: 'numeric'
                                            });
                                        }
                                    }
                                    return value;
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'center',
                            fontSize: '14px'
                        },
                        grid: {
                            borderColor: '#e0e0e0',
                            row: {
                                colors: ['transparent', 'transparent'],
                                opacity: 0.5
                            }
                        },
                        responsive: [{
                            breakpoint: 768,
                            options: {
                                chart: {
                                    height: 400
                                },
                                legend: {
                                    position: 'bottom',
                                    offsetY: 0
                                },
                                xaxis: {
                                    labels: {
                                        rotate: -90
                                    }
                                }
                            }
                        }]
                    });
                    performanceChart.render();
                }

                // Status Distribution Chart
                if (document.getElementById('status-chart')) {
                    const statusCodes = Object.keys(@json($statusDistribution ?? []));

                    if (statusCodes.length > 0) {
                        const statusDistribution = @json($statusDistribution ?? []);
                        const statusCounts = statusCodes.map(code => {
                            return statusDistribution[code]?.count ?? 0;
                        });
                        const statusColors = statusCodes.map(code => {
                            if (code >= 200 && code < 300) return '#10B981'; // Success
                            if (code >= 300 && code < 400) return '#F59E0B'; // Redirect
                            if (code >= 400 && code < 500) return '#EF4444'; // Client Error
                            if (code >= 500) return '#991B1B'; // Server Error
                            return '#6B7280'; // Default
                        });

                        const statusChart = new ApexCharts(document.getElementById('status-chart'), {
                            chart: {
                                type: 'pie',
                                height: 256
                            },
                            series: statusCounts,
                            labels: statusCodes.map(code => 'Status ' + code),
                            colors: statusColors,
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                y: {
                                    formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
                                        const percentage = @json($statusDistribution ?? [])[statusCodes[seriesIndex]]?.percentage ?? 0;
                                        return `${value} (${percentage}%)`;
                                    }
                                }
                            }
                        });
                        statusChart.render();
                    } else {
                        document.getElementById('status-chart').innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No status data available</div>';
                    }
                }

                // Hourly Usage Chart
                if (document.getElementById('hourly-chart')) {
                    const hourlyDataRaw = @json($hourlyData ?? []);
                    const hourlyData = Object.values(hourlyDataRaw);

                    if (hourlyData.length > 0 && hourlyData.some(val => val > 0)) {
                        const hourlyChart = new ApexCharts(document.getElementById('hourly-chart'), {
                            chart: {
                                type: 'bar',
                                height: 256
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '70%'
                                }
                            },
                            series: [{
                                name: 'API Calls',
                                data: hourlyData
                            }],
                            colors: ['#6366F1'],
                            xaxis: {
                                categories: Object.keys(hourlyDataRaw).map(hour => hour + ':00'),
                                labels: {
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'API Calls'
                                }
                            }
                        });
                        hourlyChart.render();
                    } else {
                        document.getElementById('hourly-chart').innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No hourly data available</div>';
                    }
                }

                // Day of Week Usage Chart
                if (document.getElementById('daily-chart')) {
                    const dailyDataRaw = @json($dailyData ?? []);
                    const dailyData = Object.values(dailyDataRaw);

                    if (dailyData.length > 0 && dailyData.some(val => val > 0)) {
                        const dailyChart = new ApexCharts(document.getElementById('daily-chart'), {
                            chart: {
                                type: 'bar',
                                height: 256
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '70%'
                                }
                            },
                            series: [{
                                name: 'API Calls',
                                data: dailyData
                            }],
                            colors: ['#8B5CF6'],
                            xaxis: {
                                categories: Object.keys(dailyDataRaw),
                                labels: {
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'API Calls'
                                }
                            }
                        });
                        dailyChart.render();
                    } else {
                        document.getElementById('daily-chart').innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No daily data available</div>';
                    }
                }

                // Popular Endpoints Chart
                if (document.getElementById('endpoints-chart')) {
                    const endpoints = Object.keys(@json($popularEndpoints ?? []));

                    if (endpoints.length > 0) {
                        const popularEndpoints = @json($popularEndpoints ?? []);
                        const endpointData = endpoints.map(endpoint => {
                            return {
                                endpoint: endpoint,
                                count: popularEndpoints[endpoint]?.count ?? 0,
                                latency: popularEndpoints[endpoint]?.avg_latency ?? 0,
                                success_rate: popularEndpoints[endpoint]?.success_rate ?? 0
                            };
                        }).sort((a, b) => b.count - a.count);

                        const endpointsChart = new ApexCharts(document.getElementById('endpoints-chart'), {
                            chart: {
                                type: 'bar',
                                height: 384,
                                stacked: false
                            },
                            series: [{
                                name: 'Requests',
                                data: endpointData.map(e => e.count)
                            }, {
                                name: 'Avg Latency (ms)',
                                data: endpointData.map(e => e.latency)
                            }, {
                                name: 'Success Rate (%)',
                                data: endpointData.map(e => e.success_rate)
                            }],
                            colors: ['#3B82F6', '#F59E0B', '#34D399'],
                            plotOptions: {
                                bar: {
                                    horizontal: true,
                                    dataLabels: {
                                        position: 'top',
                                    },
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                offsetX: 8,
                                style: {
                                    fontSize: '12px',
                                    colors: ['#fff']
                                }
                            },
                            stroke: {
                                width: 1,
                                colors: ['#fff']
                            },
                            xaxis: {
                                categories: endpointData.map(e => {
                                    // Get the last part of the URL
                                    const parts = e.endpoint.split('/');
                                    return parts[parts.length - 1] || e.endpoint;
                                })
                            },
                            yaxis: {
                                title: {
                                    text: 'Endpoints'
                                }
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                y: {
                                    formatter: function (value, { seriesIndex }) {
                                        if (seriesIndex === 0) return value + " requests";
                                        if (seriesIndex === 1) return value + " ms";
                                        if (seriesIndex === 2) return value + "%";
                                        return value;
                                    }
                                }
                            },
                            legend: {
                                position: 'top',
                                horizontalAlign: 'left'
                            }
                        });
                        endpointsChart.render();
                    } else {
                        document.getElementById('endpoints-chart').innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No endpoint data available</div>';
                    }
                }
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        });
    </script>
@endpush

<x-filament-panels::page>
    <div class="text-center text-lg text-gray-500 mb-6">
        Summary of API usage across the platform.
    </div>

    <!-- Overall API Statistics Section -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Overall API Statistics</h2>
        <p class="text-sm text-gray-500 mb-4">Combined statistics for production and sandbox</p>

        @livewire(\App\Filament\Widgets\ApiStatsOverview::class)
    </div>

    <!-- Analytics Dashboard Section -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">API Analytics Dashboard</h2>
        <p class="text-sm text-gray-500 mb-4">Data from {{ $startDate ?? now()->subDays(30)->format('Y-m-d') }} to {{ $endDate ?? now()->format('Y-m-d') }}</p>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Performance Over Time Chart -->
            <div class="bg-white rounded-lg shadow p-4 col-span-full">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-medium">Performance Over Time</h3>
                    <div class="text-sm text-gray-500">
                        <span class="inline-flex items-center mr-3">
                            <span class="w-3 h-3 rounded-full bg-blue-400 mr-1"></span>
                            Requests
                        </span>
                        <span class="inline-flex items-center mr-3">
                            <span class="w-3 h-3 rounded-full bg-red-300 mr-1"></span>
                            Latency
                        </span>
                        <span class="inline-flex items-center">
                            <span class="w-3 h-3 rounded-full bg-red-500 mr-1"></span>
                            Error Rate
                        </span>
                    </div>
                </div>
                <div id="performance-chart" class="h-80"></div>
                <div class="text-xs text-gray-500 mt-2">Tip: Click and drag to zoom, double-click to reset view</div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-medium mb-3">Status Code Distribution</h3>
                <div id="status-chart" class="h-64"></div>
            </div>

            <!-- Hourly Usage Chart -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-medium mb-3">API Calls by Hour of Day</h3>
                <div id="hourly-chart" class="h-64"></div>
            </div>

            <!-- Day of Week Usage Chart -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-medium mb-3">API Calls by Day of Week</h3>
                <div id="daily-chart" class="h-64"></div>
            </div>

            <!-- Popular Endpoints Chart -->
            <div class="bg-white rounded-lg shadow p-4 col-span-full">
                <h3 class="text-lg font-medium mb-3">Top 10 Endpoints</h3>
                <div id="endpoints-chart" class="h-96"></div>
            </div>
        </div>
    </div>

    <!-- Sandbox Environment Section -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Sandbox Environment</h2>
        <p class="text-sm text-gray-500 mb-4">Data for sandbox token usage only</p>

        @livewire(\App\Filament\Widgets\SandboxStatsWidget::class)
    </div>

    <!-- API Logs Section -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">API Logs</h2>
        <p class="text-sm text-gray-500 mb-4">Recent API requests</p>

        @livewire(\App\Filament\Widgets\ApiLogsTable::class)
    </div>
</x-filament-panels::page>
