<?php
// data_handler.php
header('Content-Type: application/json');

class DataService {
    private $dataFile = 'service_data.json';
    
    public function getData() {
        if (file_exists($this->dataFile)) {
            return json_decode(file_get_contents($this->dataFile), true);
        }
        return null;
    }
    
    public function updateData($data) {
        file_put_contents($this->dataFile, json_encode($data));
    }
}

// Handle incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $service = new DataService();
    echo json_encode($service->getData());
}

// Handle updates from the service
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = new DataService();
    $data = json_decode(file_get_contents('php://input'), true);
    $service->updateData($data);
    echo json_encode(['status' => 'success']);
}
?>