<?php
require_once 'functions.php';

header('Content-Type: application/json');

if ($_POST) {
    $date = $_POST['date'] ?? '';
    $service_id = (int)($_POST['service_id'] ?? 0);
    
    if (!$date || !$service_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }
    
    try {
        // Get service details
        $stmt = $pdo->prepare("SELECT duration_in_minutes FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();
        
        if (!$service) {
            echo json_encode(['success' => false, 'message' => 'Service not found']);
            exit;
        }
        
        // Get day of week
        $dayOfWeek = date('l', strtotime($date));
        
        // Get business hours for the day
        $businessHours = getBusinessHours($dayOfWeek);
        
        if (!$businessHours) {
            echo json_encode(['success' => false, 'message' => 'Salon is closed on this day']);
            exit;
        }
        
        // Get buffer time
        $bufferTime = getBufferTime();
        
        // Get all booked times for this date
        $stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ?");
        $stmt->execute([$date]);
        $bookedTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Generate available time slots
        $availableSlots = generateTimeSlots(
            $businessHours['open_time'], 
            $businessHours['close_time'], 
            $bufferTime, 
            $service['duration_in_minutes'], 
            $bookedTimes
        );
        
        echo json_encode([
            'success' => true, 
            'slots' => $availableSlots,
            'date' => $date,
            'day' => $dayOfWeek
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>