<?php
// API endpoint to get salon operating hours for a specific date
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

if (!validateDate($input['date'])) {
    errorResponse('Invalid date format');
}

$date = $input['date'];
$dayName = getDayName($date);

try {
    $db = getDB();
    
    // Get salon operating hours for the day
    $hours = $db->fetch(
        "SELECT * FROM operating_hours WHERE day_name = ?",
        [$dayName]
    );
    
    if (!$hours) {
        // Default to closed if no hours set
        $hours = [
            'day_name' => $dayName,
            'opening_time' => null,
            'closing_time' => null,
            'is_open' => false
        ];
    }
    
    successResponse('Salon hours retrieved successfully', $hours);
    
} catch (Exception $e) {
    error_log("Error in get-salon-hours.php: " . $e->getMessage());
    errorResponse('Failed to retrieve salon hours');
}
?>