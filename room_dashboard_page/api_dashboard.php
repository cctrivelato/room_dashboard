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

        // Save updated data with locking
        file_put_contents($this->dataFile, json_encode($currentData, JSON_PRETTY_PRINT | LOCK_EX));
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

    // Debugging: Log received JSON
    file_put_contents('debug_log.txt', print_r($data, true), FILE_APPEND);

    if (!isset($data['timestamp']) || !isset($data['tables']) || !is_array($data['tables'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid data format']);
        exit;
    }

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