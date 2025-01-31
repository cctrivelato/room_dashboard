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
        return file_exists($this->dataFile) 
            ? json_decode(file_get_contents($this->dataFile), true) 
            : null;
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
    <title>SFVIS Monitoring Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .dashboard { max-width: 1400px; margin: auto; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .chart-container { height: 300px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="card">
            <h1>SFVIS Monitoring Dashboard</h1>
            <select id="tableSelector"></select>
            <div id="lastUpdate"></div>
        </div>
        
        <div class="grid" id="chartsContainer"></div>
        
        <div class="card">
            <h2>Current Metrics</h2>
            <table>
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="metricsTableBody"></tbody>
            </table>
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
                this.updateDashboard();
            });
        }
        
        async fetchData() {
            try {
                const response = await fetch('index.php');
                this.data = await response.json();
                this.updateDashboard();
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }
        
        updateDashboard() {
            if (!this.data || !this.data.history || !this.data.history.length) return;
            
            const lastUpdate = new Date(this.data.history[this.data.history.length - 1].timestamp);
            document.getElementById('lastUpdate').textContent = 
                `Last Updated: ${lastUpdate.toLocaleString()}`;
            
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
            const metrics = [
                'workstation', 'vision_system', 'old_status', 
                'new_status', 'people_count', 'frame_rate', 
                'presence_change_total', 'presence_change_rate'
            ];
            
            const chartsContainer = document.getElementById('chartsContainer');
            chartsContainer.innerHTML = '';
            
            metrics.forEach(metricName => {
                const chartDiv = document.createElement('div');
                chartDiv.className = 'card';
                
                const titleDiv = document.createElement('h3');
                titleDiv.textContent = metricName.replace('_', ' ').toUpperCase();
                chartDiv.appendChild(titleDiv);
                
                const canvasDiv = document.createElement('div');
                canvasDiv.className = 'chart-container';
                
                const canvas = document.createElement('canvas');
                canvas.id = `${metricName}Chart`;
                canvasDiv.appendChild(canvas);
                chartDiv.appendChild(canvasDiv);
                
                chartsContainer.appendChild(chartDiv);
                
                const labels = this.data.history.map(h => 
                    new Date(h.timestamp).toLocaleTimeString()
                );
                
                const metricData = this.data.history.map(h => {
                    const tableData = h.tables.find(t => t.table_name === this.currentTable);
                    const metric = tableData.metrics.find(m => m.metric_name === metricName);
                    return metric ? metric.value : null;
                });
                
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: metricName,
                            data: metricData,
                            borderColor: this.getRandomColor(),
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            });
        }
        
        updateMetricsTable() {
            const tbody = document.getElementById('metricsTableBody');
            const latestData = this.data.history[this.data.history.length - 1];
            const tableData = latestData.tables.find(t => t.table_name === this.currentTable);
            
            tbody.innerHTML = tableData.metrics.map(metric => `
                <tr>
                    <td>${metric.metric_name}</td>
                    <td>${metric.value}</td>
                    <td>${new Date(metric.timestamp).toLocaleString()}</td>
                </tr>
            `).join('');
        }
        
        getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
        
        startPolling() {
            this.fetchData();
            setInterval(() => this.fetchData(), 5000);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        new DashboardManager();
    });
    </script>
</body>
</html>