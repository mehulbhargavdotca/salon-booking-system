<?php
// API endpoint to create a new booking
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

if (!isset($input['stylist']) || !isset($input['stylist']['id'])) {
    errorResponse('Stylist is required');
}

if (!isset($input['customer']) || empty($input['customer'])) {
    errorResponse('Customer information is required');
}

// Validate date and time
if (!validateDate($input['date'])) {
    errorResponse('Invalid date format');
}

if (!validateTime($input['time'])) {
    errorResponse('Invalid time format');
}

// Validate customer information
$customer = $input['customer'];
if (empty($customer['name']) || empty($customer['phone']) || empty($customer['email'])) {
    errorResponse('Customer name, phone, and email are required');
}

if (!validateEmail($customer['email'])) {
    errorResponse('Invalid email address');
}

if (!validatePhone($customer['phone'])) {
    errorResponse('Invalid phone number');
}

// Extract data
$services = $input['services'];
$date = $input['date'];
$time = $input['time'];
$staffId = (int)$input['stylist']['id'];
$customerName = sanitizeInput($customer['name']);
$customerPhone = sanitizeInput($customer['phone']);
$customerEmail = sanitizeInput($customer['email']);
$specialNotes = isset($customer['notes']) ? sanitizeInput($customer['notes']) : '';

try {
    $db = getDB();
    
    // Begin transaction
    $db->beginTransaction();
    
    // Validate services exist and calculate total
    $serviceIds = array_column($services, 'id');
    $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
    $dbServices = $db->fetchAll(
        "SELECT * FROM services WHERE id IN ($placeholders) AND status = 'active'",
        $serviceIds
    );
    
    if (count($dbServices) !== count($serviceIds)) {
        throw new Exception('Some services not found or inactive');
    }
    
    // Validate staff exists and can perform services
    $staff = $db->fetch(
        "SELECT * FROM staff WHERE id = ? AND status = 'active'",
        [$staffId]
    );
    
    if (!$staff) {
        throw new Exception('Staff member not found or inactive');
    }
    
    // Check if staff can perform all services
    $staffServices = $db->fetchAll(
        "SELECT service_id FROM staff_services WHERE staff_id = ? AND service_id IN ($placeholders)",
        array_merge([$staffId], $serviceIds)
    );
    
    if (count($staffServices) !== count($serviceIds)) {
        throw new Exception('Staff member cannot perform all selected services');
    }
    
    // Calculate total duration and price
    $totalDuration = array_sum(array_column($dbServices, 'duration'));
    $totalPrice = array_sum(array_column($dbServices, 'price'));
    $dayName = getDayName($date);
    
    // Validate staff availability
    $staffAvailability = getStaffAvailability($staffId, $dayName);
    if (!$staffAvailability) {
        throw new Exception('Staff member is not available on this day');
    }
    
    $endTime = addMinutesToTime($time, $totalDuration);
    
    // Check if time slot is within staff working hours
    if (strtotime($time) < strtotime($staffAvailability['start_time']) ||
        strtotime($endTime) > strtotime($staffAvailability['end_time'])) {
        throw new Exception('Time slot is outside staff working hours');
    }
    
    // Check if time slot is available
    if (!isTimeSlotAvailable($staffId, $date, $time, $totalDuration)) {
        throw new Exception('Time slot is no longer available');
    }
    
    // Validate salon is open
    $salonHours = getSalonOperatingHours($dayName);
    if (!$salonHours || !$salonHours['is_open']) {
        throw new Exception('Salon is closed on this day');
    }
    
    if (strtotime($time) < strtotime($salonHours['opening_time']) ||
        strtotime($endTime) > strtotime($salonHours['closing_time'])) {
        throw new Exception('Time slot is outside salon operating hours');
    }
    
    // Create booking
    $bookingId = $db->insert(
        "INSERT INTO bookings (customer_name, customer_phone, customer_email, booking_date, booking_time, staff_id, total_price, special_notes, status, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
        [$customerName, $customerPhone, $customerEmail, $date, $time, $staffId, $totalPrice, $specialNotes]
    );
    
    if (!$bookingId) {
        throw new Exception('Failed to create booking');
    }
    
    // Add booking services
    foreach ($dbServices as $service) {
        $db->insert(
            "INSERT INTO booking_services (booking_id, service_id, service_price) VALUES (?, ?, ?)",
            [$bookingId, $service['id'], $service['price']]
        );
    }
    
    // Commit transaction
    $db->commit();
    
    // Prepare response data
    $bookingData = [
        'booking_id' => $bookingId,
        'customer_name' => $customerName,
        'customer_phone' => $customerPhone,
        'customer_email' => $customerEmail,
        'booking_date' => $date,
        'booking_time' => $time,
        'staff_name' => $staff['name'],
        'total_price' => $totalPrice,
        'total_duration' => $totalDuration,
        'services' => $dbServices,
        'special_notes' => $specialNotes,
        'status' => 'pending'
    ];
    
    successResponse('Booking created successfully', $bookingData);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->getConnection()->inTransaction()) {
        $db->rollback();
    }
    
    error_log("Error in create-booking.php: " . $e->getMessage());
    errorResponse($e->getMessage());
}
?>