<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('index.php');
}

$bookingId = (int)$_GET['id'];

try {
    $db = getDB();
    
    // Get booking details with staff and services
    $booking = $db->fetch(
        "SELECT b.*, s.name as staff_name, s.email as staff_email 
         FROM bookings b 
         JOIN staff s ON b.staff_id = s.id 
         WHERE b.id = ?",
        [$bookingId]
    );
    
    if (!$booking) {
        redirect('index.php');
    }
    
    // Get booking services
    $services = $db->fetchAll(
        "SELECT bs.*, sv.name, sv.duration, sv.description 
         FROM booking_services bs 
         JOIN services sv ON bs.service_id = sv.id 
         WHERE bs.booking_id = ?
         ORDER BY sv.name",
        [$bookingId]
    );
    
    $salonSettings = getSalonSettings();
    
} catch (Exception $e) {
    error_log("Error loading booking details: " . $e->getMessage());
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - <?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4af37;
            --secondary-color: #2c3e50;
            --accent-color: #e8f4f8;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: #f8f9fa;
        }
        
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
        
        .success-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 3rem 0;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: -50px;
            position: relative;
            z-index: 2;
        }
        
        .booking-header {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .detail-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .detail-value {
            color: var(--text-light);
        }
        
        .service-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .service-item:last-child {
            border-bottom: none;
        }
        
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary-custom:hover {
            background-color: #b8941f;
            border-color: #b8941f;
            color: white;
        }
        
        .print-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background: white !important;
            }
            
            .booking-card {
                box-shadow: none;
                margin-top: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm no-print">
        <div class="container">
            <a class="navbar-brand font-playfair fw-bold fs-3" href="index.php" style="color: var(--primary-color);">
                <i class="fas fa-cut me-2"></i><?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home me-1"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Success Header -->
    <section class="success-header no-print">
        <div class="container text-center">
            <div class="success-icon">
                <i class="fas fa-check fa-3x"></i>
            </div>
            <h1 class="display-4 font-playfair fw-bold mb-3">Booking Confirmed!</h1>
            <p class="lead">Your appointment has been successfully scheduled</p>
        </div>
    </section>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Booking Details Card -->
                <div class="booking-card">
                    <div class="booking-header">
                        <h3 class="mb-0 font-playfair">
                            <i class="fas fa-calendar-check me-2"></i>Appointment Details
                        </h3>
                        <p class="mb-0 mt-2">Booking ID: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    
                    <div class="card-body p-0">
                        <!-- Customer Information -->
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-user me-2 text-primary"></i>Customer Name
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['customer_name']); ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-envelope me-2 text-primary"></i>Email
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['customer_email']); ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-phone me-2 text-primary"></i>Phone
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['customer_phone']); ?></span>
                        </div>
                        
                        <!-- Appointment Information -->
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-calendar me-2 text-primary"></i>Date
                            </span>
                            <span class="detail-value"><?php echo formatDate($booking['booking_date'], 'l, F j, Y'); ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-clock me-2 text-primary"></i>Time
                            </span>
                            <span class="detail-value"><?php echo formatTime($booking['booking_time']); ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-user-tie me-2 text-primary"></i>Stylist
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['staff_name']); ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-hourglass-half me-2 text-primary"></i>Duration
                            </span>
                            <span class="detail-value"><?php echo array_sum(array_column($services, 'duration')); ?> minutes</span>
                        </div>
                        
                        <!-- Services -->
                        <div class="detail-item">
                            <div class="w-100">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-list me-2 text-primary"></i>
                                    <span class="detail-label">Selected Services</span>
                                </div>
                                <div class="services-list">
                                    <?php foreach ($services as $service): ?>
                                    <div class="service-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                            <?php if ($service['description']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($service['description']); ?></small>
                                            <?php endif; ?>
                                            <br><small class="text-muted"><?php echo $service['duration']; ?> minutes</small>
                                        </div>
                                        <span class="fw-bold text-primary"><?php echo formatPrice($service['service_price']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Special Notes -->
                        <?php if (!empty($booking['special_notes'])): ?>
                        <div class="detail-item">
                            <div class="w-100">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-sticky-note me-2 text-primary"></i>
                                    <span class="detail-label">Special Notes</span>
                                </div>
                                <div class="bg-light p-3 rounded">
                                    <?php echo nl2br(htmlspecialchars($booking['special_notes'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Total -->
                        <div class="detail-item bg-light">
                            <span class="detail-label fs-5">
                                <i class="fas fa-receipt me-2 text-primary"></i>Total Amount
                            </span>
                            <span class="detail-value fs-4 fw-bold text-primary"><?php echo formatPrice($booking['total_price']); ?></span>
                        </div>
                        
                        <!-- Status -->
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-info-circle me-2 text-primary"></i>Status
                            </span>
                            <span class="badge bg-warning text-dark">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-outline-primary btn-lg me-3">
                        <i class="fas fa-print me-2"></i>Print Confirmation
                    </button>
                    <a href="booking.php" class="btn btn-primary-custom btn-lg me-3">
                        <i class="fas fa-plus me-2"></i>Book Another Appointment
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>

                <!-- Important Information -->
                <div class="alert alert-info mt-4 no-print">
                    <h5 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>Important Information
                    </h5>
                    <ul class="mb-0">
                        <li>Please arrive 10 minutes before your appointment time</li>
                        <li>If you need to cancel or reschedule, please call us at <?php echo $salonSettings['salon_phone'] ?? '+1-555-0123'; ?></li>
                        <li>A confirmation email has been sent to <?php echo htmlspecialchars($booking['customer_email']); ?></li>
                        <li>Late arrivals may result in shortened service time</li>
                    </ul>
                </div>

                <!-- Contact Information -->
                <div class="print-section mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="font-playfair fw-bold mb-3">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Salon Location
                            </h5>
                            <p class="mb-1"><strong><?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?></strong></p>
                            <p class="mb-1">123 Beauty Street</p>
                            <p class="mb-1">City, State 12345</p>
                            <p class="mb-0">Phone: <?php echo $salonSettings['salon_phone'] ?? '+1-555-0123'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-playfair fw-bold mb-3">
                                <i class="fas fa-clock me-2 text-primary"></i>Operating Hours
                            </h5>
                            <p class="mb-1">Monday - Saturday: 10:00 AM - 8:00 PM</p>
                            <p class="mb-1">Sunday: 10:00 AM - 7:00 PM</p>
                            <p class="mb-0">Email: <?php echo $salonSettings['salon_email'] ?? 'info@elegantsalon.com'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5 no-print">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-focus for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Show success animation
            const successIcon = document.querySelector('.success-icon');
            if (successIcon) {
                successIcon.style.animation = 'pulse 2s infinite';
            }
        });
        
        // Print functionality
        function printBooking() {
            window.print();
        }
        
        // Add print styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>