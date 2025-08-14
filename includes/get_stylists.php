<?php
require_once 'functions.php';

header('Content-Type: application/json');

if ($_POST) {
    $service_id = (int)($_POST['service_id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    
    if (!$service_id || !$date || !$time) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }
    
    try {
        // Get available stylists for this service, date, and time
        $availableStylists = getAvailableStylists($service_id, $date, $time);
        
        echo json_encode([
            'success' => true, 
            'stylists' => $availableStylists,
            'count' => count($availableStylists)
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>