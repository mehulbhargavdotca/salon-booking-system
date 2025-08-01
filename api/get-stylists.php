<?php
// API endpoint to get available stylists for booking
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['services']) || !is_array($input['services']) || empty($input['services'])) {
    errorResponse('Services are required');
}

if (!isset($input['date']) || empty($input['date'])) {
    errorResponse('Date is required');
}

if (!isset($input['time']) || empty($input['time'])) {
    errorResponse('Time is required');
}

if (!validateDate($input['date'])) {
    errorResponse('Invalid date format');
}

if (!validateTime($input['time'])) {
    errorResponse('Invalid time format');
}

$serviceIds = array_map('intval', $input['services']);
$date = $input['date'];
$time = $input['time'];
$dayName = getDayName($date);

try {
    $db = getDB();
    
    // Get services to calculate total duration
    $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
    $services = $db->fetchAll(
        "SELECT * FROM services WHERE id IN ($placeholders) AND status = 'active'",
        $serviceIds
    );
    
    if (count($services) !== count($serviceIds)) {
        errorResponse('Some services not found');
    }
    
    $totalDuration = array_sum(array_column($services, 'duration'));
    $endTime = addMinutesToTime($time, $totalDuration);
    
    // Get staff who can perform all selected services
    $staffList = getStaffByServices($serviceIds);
    
    $availableStylists = [];
    
    foreach ($staffList as $staff) {
        // Check staff availability for this day
        $staffAvailability = getStaffAvailability($staff['id'], $dayName);
        
        if (!$staffAvailability) {
            continue;
        }
        
        // Check if time slot is within staff working hours
        if (strtotime($time) < strtotime($staffAvailability['start_time']) ||
            strtotime($endTime) > strtotime($staffAvailability['end_time'])) {
            continue;
        }
        
        // Check if staff is available (no conflicting bookings)
        if (isTimeSlotAvailable($staff['id'], $date, $time, $totalDuration)) {
            // Get staff's assigned services for display
            $staffServices = $db->fetchAll(
                "SELECT s.name FROM services s 
                 JOIN staff_services ss ON s.id = ss.service_id 
                 WHERE ss.staff_id = ? AND s.status = 'active'
                 ORDER BY s.name",
                [$staff['id']]
            );
            
            $staff['services'] = array_column($staffServices, 'name');
            $staff['working_hours'] = [
                'start_time' => $staffAvailability['start_time'],
                'end_time' => $staffAvailability['end_time']
            ];
            
            $availableStylists[] = $staff;
        }
    }
    
    // Sort stylists by name
    usort($availableStylists, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    successResponse('Stylists retrieved successfully', $availableStylists);
    
} catch (Exception $e) {
    error_log("Error in get-stylists.php: " . $e->getMessage());
    errorResponse('Failed to retrieve stylists');
}
?>