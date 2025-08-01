<?php
// Utility Functions for Salon Booking System

require_once 'database.php';

// Security Functions
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateCSRF($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !isset($_SESSION[CSRF_TOKEN_NAME . '_time'])) {
        return false;
    }
    
    $tokenAge = time() - $_SESSION[CSRF_TOKEN_NAME . '_time'];
    if ($tokenAge > CSRF_TOKEN_TIME) {
        return false;
    }
    
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function generateCSRFToken() {
    return $_SESSION[CSRF_TOKEN_NAME];
}

// Validation Functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10;
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function validateTime($time, $format = 'H:i') {
    $t = DateTime::createFromFormat($format, $time);
    return $t && $t->format($format) === $time;
}

// Date and Time Functions
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

function formatTime($time, $format = 'g:i A') {
    return date($format, strtotime($time));
}

function getCurrentDate() {
    return date('Y-m-d');
}

function getCurrentTime() {
    return date('H:i:s');
}

function addMinutesToTime($time, $minutes) {
    return date('H:i:s', strtotime($time . ' + ' . $minutes . ' minutes'));
}

function getDateRange($startDate, $days) {
    $dates = [];
    for ($i = 0; $i < $days; $i++) {
        $dates[] = date('Y-m-d', strtotime($startDate . ' + ' . $i . ' days'));
    }
    return $dates;
}

function getDayName($date) {
    return date('l', strtotime($date));
}

// Price Formatting
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function calculateTotalPrice($services) {
    $total = 0;
    foreach ($services as $service) {
        $total += isset($service['price']) ? $service['price'] : 0;
    }
    return $total;
}

// Database Helper Functions
function getMainCategories($status = 'active') {
    $db = getDB();
    $sql = "SELECT * FROM main_categories WHERE status = ? ORDER BY name";
    return $db->fetchAll($sql, [$status]);
}

function getSubcategories($mainCategoryId, $status = 'active') {
    $db = getDB();
    $sql = "SELECT * FROM subcategories WHERE main_category_id = ? AND status = ? ORDER BY name";
    return $db->fetchAll($sql, [$mainCategoryId, $status]);
}

function getServices($subcategoryId, $status = 'active') {
    $db = getDB();
    $sql = "SELECT * FROM services WHERE subcategory_id = ? AND status = ? ORDER BY name";
    return $db->fetchAll($sql, [$subcategoryId, $status]);
}

function getServiceById($serviceId) {
    $db = getDB();
    $sql = "SELECT s.*, sc.name as subcategory_name, mc.name as main_category_name 
            FROM services s 
            JOIN subcategories sc ON s.subcategory_id = sc.id 
            JOIN main_categories mc ON sc.main_category_id = mc.id 
            WHERE s.id = ?";
    return $db->fetch($sql, [$serviceId]);
}

function getStaffByServices($serviceIds) {
    if (empty($serviceIds)) return [];
    
    $db = getDB();
    $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
    $sql = "SELECT DISTINCT s.* FROM staff s 
            JOIN staff_services ss ON s.id = ss.staff_id 
            WHERE ss.service_id IN ($placeholders) AND s.status = 'active'
            ORDER BY s.name";
    return $db->fetchAll($sql, $serviceIds);
}

function getStaffAvailability($staffId, $dayName) {
    $db = getDB();
    $sql = "SELECT * FROM staff_availability 
            WHERE staff_id = ? AND day_name = ? AND is_available = 1";
    return $db->fetch($sql, [$staffId, $dayName]);
}

function getSalonOperatingHours($dayName) {
    $db = getDB();
    $sql = "SELECT * FROM operating_hours WHERE day_name = ? AND is_open = 1";
    return $db->fetch($sql, [$dayName]);
}

function getSalonSettings() {
    $db = getDB();
    $sql = "SELECT setting_name, setting_value FROM salon_settings";
    $results = $db->fetchAll($sql);
    
    $settings = [];
    foreach ($results as $setting) {
        $settings[$setting['setting_name']] = $setting['setting_value'];
    }
    return $settings;
}

function getBufferTime() {
    $settings = getSalonSettings();
    return isset($settings['buffer_time']) ? (int)$settings['buffer_time'] : DEFAULT_BUFFER_TIME;
}

// Booking Functions
function generateTimeSlots($startTime, $endTime, $bufferTime = 30) {
    $slots = [];
    $current = strtotime($startTime);
    $end = strtotime($endTime);
    
    while ($current < $end) {
        $slots[] = date('H:i:s', $current);
        $current += ($bufferTime * 60);
    }
    
    return $slots;
}

function isTimeSlotAvailable($staffId, $date, $time, $duration) {
    $db = getDB();
    $endTime = addMinutesToTime($time, $duration);
    
    $sql = "SELECT COUNT(*) as count FROM bookings 
            WHERE staff_id = ? AND booking_date = ? 
            AND status IN ('pending', 'confirmed')
            AND (
                (booking_time < ? AND DATE_ADD(STR_TO_DATE(CONCAT(booking_date, ' ', booking_time), '%Y-%m-%d %H:%i:%s'), INTERVAL (
                    SELECT SUM(duration) FROM booking_services bs 
                    JOIN services s ON bs.service_id = s.id 
                    WHERE bs.booking_id = bookings.id
                ) MINUTE) > ?)
                OR (booking_time < ? AND booking_time >= ?)
            )";
    
    $result = $db->fetch($sql, [$staffId, $date, $endTime, $time, $endTime, $time]);
    return $result['count'] == 0;
}

function getExistingBookings($staffId, $date) {
    $db = getDB();
    $sql = "SELECT b.*, 
            (SELECT SUM(s.duration) FROM booking_services bs 
             JOIN services s ON bs.service_id = s.id 
             WHERE bs.booking_id = b.id) as total_duration
            FROM bookings b 
            WHERE staff_id = ? AND booking_date = ? 
            AND status IN ('pending', 'confirmed')
            ORDER BY booking_time";
    return $db->fetchAll($sql, [$staffId, $date]);
}

// Response Functions
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function successResponse($message, $data = null) {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    jsonResponse($response);
}

function errorResponse($message, $status = 400) {
    jsonResponse(['success' => false, 'message' => $message], $status);
}

// URL Functions
function redirect($url) {
    header("Location: $url");
    exit;
}

function getCurrentURL() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
           "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

// File Functions
function uploadFile($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    if ($fileError !== 0) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if ($fileSize > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $newFileName = uniqid('', true) . '.' . $fileExt;
    $uploadPath = $uploadDir . '/' . $newFileName;
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        return ['success' => true, 'filename' => $newFileName, 'path' => $uploadPath];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

// Pagination Functions
function getPaginationData($currentPage, $totalRecords, $recordsPerPage = RECORDS_PER_PAGE) {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $recordsPerPage;
    
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_records' => $totalRecords,
        'records_per_page' => $recordsPerPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

// Debug Functions
function debugLog($message, $data = null) {
    $log = "[" . date('Y-m-d H:i:s') . "] " . $message;
    if ($data !== null) {
        $log .= " | Data: " . print_r($data, true);
    }
    error_log($log);
}

function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}
?>