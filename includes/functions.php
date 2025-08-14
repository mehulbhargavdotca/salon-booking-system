<?php
require_once 'db.php';

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateTimeSlots($openTime, $closeTime, $bufferMinutes, $serviceDuration, $bookedTimes = []) {
    $slots = [];
    $current = strtotime($openTime);
    $end = strtotime($closeTime);
    
    while ($current < $end) {
        $timeSlot = date('H:i', $current);
        $endTime = $current + ($serviceDuration * 60);
        
        if ($endTime <= $end && !in_array($timeSlot, $bookedTimes)) {
            $slots[] = $timeSlot;
        }
        
        $current += ($bufferMinutes * 60);
    }
    
    return $slots;
}

function getBusinessHours($dayOfWeek) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT open_time, close_time FROM business_hours WHERE day_of_week = ?");
    $stmt->execute([$dayOfWeek]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getBufferTime() {
    global $pdo;
    $stmt = $pdo->query("SELECT buffer_time_minutes FROM settings LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['buffer_time_minutes'] : 15;
}

function getSalonInfo() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM salon_info LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getBookedTimes($date, $stylistId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ? AND stylist_id = ?");
    $stmt->execute([$date, $stylistId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getAvailableStylists($serviceId, $date, $time) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT s.* FROM stylists s 
        JOIN stylist_services ss ON s.id = ss.stylist_id 
        WHERE ss.service_id = ? 
        AND s.id NOT IN (
            SELECT stylist_id FROM appointments 
            WHERE appointment_date = ? AND appointment_time = ?
        )
    ");
    $stmt->execute([$serviceId, $date, $time]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>