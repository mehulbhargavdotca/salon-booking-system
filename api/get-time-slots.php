<?php
// API endpoint to get available time slots for booking
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
if (!isset($input['date']) || empty($input['date'])) {
    errorResponse('Date is required');
}

if (!isset($input['services']) || !is_array($input['services']) || empty($input['services'])) {
    errorResponse('Services are required');
}

if (!validateDate($input['date'])) {
    errorResponse('Invalid date format');
}

$date = $input['date'];
$serviceIds = array_map('intval', $input['services']);
$dayName = getDayName($date);

try {
    $db = getDB();
    
    // Get salon operating hours for the day
    $salonHours = getSalonOperatingHours($dayName);
    
    if (!$salonHours || !$salonHours['is_open']) {
        successResponse('Time slots retrieved successfully', []);
        return;
    }
    
    // Calculate total duration of selected services
    $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
    $services = $db->fetchAll(
        "SELECT * FROM services WHERE id IN ($placeholders) AND status = 'active'",
        $serviceIds
    );
    
    if (count($services) !== count($serviceIds)) {
        errorResponse('Some services not found');
    }
    
    $totalDuration = array_sum(array_column($services, 'duration'));
    $bufferTime = getBufferTime();
    
    // Generate all possible time slots
    $timeSlots = generateTimeSlots(
        $salonHours['opening_time'], 
        $salonHours['closing_time'], 
        $bufferTime
    );
    
    // Get staff who can perform all selected services
    $staffList = getStaffByServices($serviceIds);
    
    if (empty($staffList)) {
        successResponse('Time slots retrieved successfully', []);
        return;
    }
    
    $availableSlots = [];
    
    foreach ($timeSlots as $timeSlot) {
        // Calculate end time for this slot
        $endTime = addMinutesToTime($timeSlot, $totalDuration);
        
        // Check if appointment would end before salon closes
        if (strtotime($endTime) > strtotime($salonHours['closing_time'])) {
            continue;
        }
        
        // Check if any staff member is available for this time slot
        $hasAvailableStaff = false;
        
        foreach ($staffList as $staff) {
            // Check staff availability for this day
            $staffAvailability = getStaffAvailability($staff['id'], $dayName);
            
            if (!$staffAvailability) {
                continue;
            }
            
            // Check if time slot is within staff working hours
            if (strtotime($timeSlot) < strtotime($staffAvailability['start_time']) ||
                strtotime($endTime) > strtotime($staffAvailability['end_time'])) {
                continue;
            }
            
            // Check if staff is available (no conflicting bookings)
            if (isTimeSlotAvailable($staff['id'], $date, $timeSlot, $totalDuration)) {
                $hasAvailableStaff = true;
                break;
            }
        }
        
        if ($hasAvailableStaff) {
            $availableSlots[] = [
                'time' => $timeSlot,
                'end_time' => $endTime,
                'duration' => $totalDuration
            ];
        }
    }
    
    successResponse('Time slots retrieved successfully', $availableSlots);
    
} catch (Exception $e) {
    error_log("Error in get-time-slots.php: " . $e->getMessage());
    errorResponse('Failed to retrieve time slots');
}
?>