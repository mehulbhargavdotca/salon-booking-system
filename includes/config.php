<?php
// Salon Booking System Configuration File

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'salon_booking');

// Application Configuration
define('SITE_URL', 'http://localhost/salon-booking');
define('SITE_NAME', 'Elegant Salon & Spa');
define('ADMIN_EMAIL', 'admin@elegantsalon.com');

// Time Zone
date_default_timezone_set('America/New_York');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Application Constants
define('BOOKING_ADVANCE_DAYS', 30);
define('DEFAULT_BUFFER_TIME', 30); // minutes
define('MAX_SERVICES_PER_BOOKING', 5);

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('RECORDS_PER_PAGE', 10);

// Email Configuration (if needed)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_TIME', 3600); // 1 hour

// Generate CSRF token if not exists
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
}

// Status Constants
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('BOOKING_PENDING', 'pending');
define('BOOKING_CONFIRMED', 'confirmed');
define('BOOKING_COMPLETED', 'completed');
define('BOOKING_CANCELLED', 'cancelled');
?>