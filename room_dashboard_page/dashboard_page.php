<?php
// data_handler.php
header('Content-Type: application/json');

class MetricsStorage {
    private $dataFile = 'table_metrics.json';
    private $maxHistoryPoints = 100; // Maximum number of historical data points to keep
    
    public function saveMetrics($data) {
        $currentData = $this->loadMetrics();
        
        if (!$currentData) {
            $currentData = ['history' => []];
        }
        
        // Add new data point to history
        $currentData['history'][] = [
            'timestamp' => $data['timestamp'],
            'tables' => $data['tables']
        ];
        
        // Keep only last N data points
        if (count($currentData['history']) > $this->maxHistoryPoints) {
            $currentData['history'] = array_slice(
                $currentData['history'], 
                -$this->maxHistoryPoints
            );
        }
        
        // Save updated data
        file_put_contents($this->dataFile, json_encode($currentData));
        return true;
    }
    
    public function loadMetrics() {
        if (file_exists($this->dataFile)) {
            return json_decode(file_get_contents($this->dataFile), true);
        }
        return null;
    }
}

// Handle incoming data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storage = new MetricsStorage();
    $data = json_decode(file_get_contents('php://input'), true);
    if ($storage->saveMetrics($data)) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save metrics']);
    }
    exit;
}

// Return metrics data for GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $storage = new MetricsStorage();
    $data = $storage->loadMetrics();
    echo json_encode($data ?? ['history' => []]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL Tables Monitoring Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --text-color: #2c3e50;
            --background-color: #ecf0f1;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--background-color);
            color: var(--text-color);
        }
        
        .dashboard {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .table-selector {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid var(--accent-color);
            margin-bottom: 20px;
            width: 200px;
        }
        
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color);
        }
        
        .metric-label {
            font-size: 14px;
            color: var(--secondary-color);
        }
        
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>MySQL Tables Monitoring Dashboard</h1>
            <div id="lastUpdate"></div>
        </div>
        
        <div class="card">
            <select id="tableSelector" class="table-selector"></select>
            
            <div class="grid">
                <div class="card">
                    <h3>Table Size</h3>
                    <div id="tableSizeChart" class="chart-container"></div>
                </div>
                
                <div class="card">
                    <h3>Row Count History</h3>
                    <div id="rowCountChart" class="chart-container"></div>
                </div>
            </div>
            
            <div class="card">
                <h3>Current Metrics</h3>
                <table id="metricsTable">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        class DashboardManager {
            constructor() {
                this.charts = {};
                this.currentTable = null;
                this.data = null;
                
                this.initializeUI();
                this.startPolling();
            }
            
            initializeUI() {
                this.tableSelector = document.getElementById('tableSelector');
                this.tableSelector.addEventListener('change', () => {
                    this.currentTable = this.tableSelector.value;
                    this.updateCharts();
                    this.updateMetricsTable();
                });
                
                this.initializeCharts();
            }
            
            initializeCharts() {
                // Table Size Chart
                const sizeCtx = document.getElementById('tableSizeChart').getContext('2d');
                this.charts.size = new Chart(sizeCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Data Size (MB)',
                            borderColor: '#3498db',
                            data: []
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                
                // Row Count Chart
                const rowCtx = document.getElementById('rowCountChart').getContext('2d');
                this.charts.rows = new Chart(rowCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Row Count',
                            borderColor: '#2ecc71',
                            data: []
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            
            async fetchData() {
                try {
                    const response = await fetch('data_handler.php');
                    this.data = await response.json();
                    this.updateDashboard();
                } catch (error) {
                    console.error('Error fetching data:', error);
                }
            }
            
            updateDashboard() {
                if (!this.data || !this.data.history || !this.data.history.length) return;
                
                // Update last update time
                const lastUpdate = new Date(this.data.history[this.data.history.length - 1].timestamp);
                document.getElementById('lastUpdate').textContent = 
                    `Last Updated: ${lastUpdate.toLocaleString()}`;
                
                // Update table selector if needed
                if (!this.currentTable) {
                    const tables = this.data.history[0].tables.map(t => t.table_name);
                    this.tableSelector.innerHTML = tables.map(table => 
                        `<option value="${table}">${table}</option>`
                    ).join('');
                    this.currentTable = tables[0];
                }
                
                this.updateCharts();
                this.updateMetricsTable();
            }
            
            updateCharts() {
                if (!this.currentTable || !this.data) return;
                
                const labels = this.data.history.map(h => 
                    new Date(h.timestamp).toLocaleTimeString()
                );
                
                // Update size chart
                const sizeData = this.data.history.map(h => {
                    const tableData = h.tables.find(t => t.table_name === this.currentTable);
                    const sizeMetric = tableData.metrics.find(m => m.metric_name === 'data_size');
                    return sizeMetric ? sizeMetric.value : null;
                });
                
                this.charts.size.data.labels = labels;
                this.charts.size.data.datasets[0].data = sizeData;
                this.charts.size.update();
                
                // Update row count chart
                const rowData = this.data.history.map(h => {
                    const tableData = h.tables.find(t => t.table_name === this.currentTable);
                    const rowMetric = tableData.metrics.find(m => m.metric_name === 'row_count');
                    return rowMetric ? rowMetric.value : null;
                });
                
                this.charts.rows.data.labels = labels;
                this.charts.rows.data.datasets[0].data = rowData;
                this.charts.rows.update();
            }
            
            updateMetricsTable() {
                if (!this.currentTable || !this.data) return;
                
                const tbody = document.querySelector('#metricsTable tbody');
                const latestData = this.data.history[this.data.history.length - 1];
                const tableData = latestData.tables.find(t => t.table_name === this.currentTable);
                
                tbody.innerHTML = tableData.metrics.map(metric => `
                    <tr>
                        <td>${metric.metric_name}</td>
                        <td>${metric.value.toLocaleString()}</td>
                        <td>${new Date(metric.timestamp).toLocaleString()}</td>
                    </tr>
                `).join('');
            }
            
            startPolling() {
                this.fetchData();
                setInterval(() => this.fetchData(), 5000);
            }
        }

        // Initialize dashboard when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new DashboardManager();
        });
    </script>
</body>
</html>