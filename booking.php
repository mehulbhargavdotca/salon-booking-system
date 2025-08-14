<?php
require_once 'includes/functions.php';

$message = '';
$step = $_GET['step'] ?? 1;

// Handle appointment booking
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'book') {
    $customer_name = sanitize($_POST['customer_name']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $service_id = (int)$_POST['service_id'];
    $stylist_id = (int)$_POST['stylist_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    
    // Validate that the time slot is still available
    $availableStylists = getAvailableStylists($service_id, $appointment_date, $appointment_time);
    $stylistAvailable = false;
    foreach ($availableStylists as $stylist) {
        if ($stylist['id'] == $stylist_id) {
            $stylistAvailable = true;
            break;
        }
    }
    
    if ($stylistAvailable) {
        $stmt = $pdo->prepare("
            INSERT INTO appointments (customer_name, customer_phone, service_id, stylist_id, appointment_date, appointment_time) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$customer_name, $customer_phone, $service_id, $stylist_id, $appointment_date, $appointment_time])) {
            header('Location: booking.php?success=1');
            exit;
        } else {
            $message = '<div class="alert alert-danger">Error booking appointment. Please try again.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Sorry, this time slot is no longer available. Please select a different time.</div>';
    }
}

// Get salon information
$salonInfo = getSalonInfo();

// Get all categories and services
$stmt = $pdo->query("
    SELECT c.id as category_id, c.name as category_name, 
           s.id as service_id, s.name as service_name, s.price, s.duration_in_minutes
    FROM categories c 
    JOIN services s ON c.id = s.category_id 
    ORDER BY c.name, s.name
");

$servicesData = $stmt->fetchAll();

// Group services by category
$servicesByCategory = [];
foreach ($servicesData as $service) {
    $categoryName = $service['category_name'];
    if (!isset($servicesByCategory[$categoryName])) {
        $servicesByCategory[$categoryName] = [];
    }
    $servicesByCategory[$categoryName][] = $service;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - <?php echo htmlspecialchars($salonInfo['name'] ?? 'Dena Beauty Salon'); ?></title>
    <link href="Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="salon-name"><?php echo htmlspecialchars($salonInfo['name'] ?? 'Dena Beauty Salon'); ?></h1>
            <p class="salon-slogan"><?php echo htmlspecialchars($salonInfo['slogan'] ?? 'Your Trusted Destination For Advanced Beauty Solutions.'); ?></p>
            <a href="index.php" class="btn btn-outline-primary">← Back to Price List</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success text-center">
                <h4>Appointment Booked Successfully!</h4>
                <p>Thank you for booking with us. We'll see you soon!</p>
                <a href="index.php" class="btn btn-primary">Back to Home</a>
            </div>
        <?php else: ?>
            <?php echo $message; ?>
            
            <!-- Booking Form -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Book Your Appointment</h4>
                        </div>
                        <div class="card-body">
                            <form id="bookingForm" method="POST">
                                <input type="hidden" name="action" value="book">
                                
                                <!-- Step 1: Service Selection -->
                                <div id="step1" class="booking-step">
                                    <h5>Step 1: Choose Your Service</h5>
                                    <div class="row">
                                        <?php foreach ($servicesByCategory as $categoryName => $services): ?>
                                            <div class="col-12 mb-4">
                                                <h6 class="text-primary"><?php echo htmlspecialchars($categoryName); ?></h6>
                                                <div class="row">
                                                    <?php foreach ($services as $service): ?>
                                                        <div class="col-md-6 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="service_id" 
                                                                       value="<?php echo $service['service_id']; ?>" 
                                                                       id="service_<?php echo $service['service_id']; ?>"
                                                                       data-price="<?php echo $service['price']; ?>"
                                                                       data-duration="<?php echo $service['duration_in_minutes']; ?>"
                                                                       data-name="<?php echo htmlspecialchars($service['service_name']); ?>"
                                                                       required>
                                                                <label class="form-check-label w-100" for="service_<?php echo $service['service_id']; ?>">
                                                                    <div class="d-flex justify-content-between">
                                                                        <span><?php echo htmlspecialchars($service['service_name']); ?></span>
                                                                        <span class="text-primary">$<?php echo number_format($service['price'], 2); ?></span>
                                                                    </div>
                                                                    <small class="text-muted"><?php echo $service['duration_in_minutes']; ?> minutes</small>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next: Select Date & Time</button>
                                </div>

                                <!-- Step 2: Date and Time Selection -->
                                <div id="step2" class="booking-step" style="display: none;">
                                    <h5>Step 2: Choose Date & Time</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="appointment_date" class="form-label">Select Date</label>
                                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                                   min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Available Times</label>
                                            <div id="timeSlots" class="d-flex flex-wrap">
                                                <p class="text-muted">Please select a date first</p>
                                            </div>
                                            <input type="hidden" name="appointment_time" id="selected_time">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep(1)">← Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next: Choose Stylist</button>
                                    </div>
                                </div>

                                <!-- Step 3: Stylist Selection -->
                                <div id="step3" class="booking-step" style="display: none;">
                                    <h5>Step 3: Choose Your Stylist</h5>
                                    <div id="stylistOptions">
                                        <p class="text-muted">Please complete previous steps first</p>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep(2)">← Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="nextStep(4)">Next: Your Information</button>
                                    </div>
                                </div>

                                <!-- Step 4: Customer Information -->
                                <div id="step4" class="booking-step" style="display: none;">
                                    <h5>Step 4: Your Information</h5>
                                    
                                    <!-- Booking Summary -->
                                    <div class="card bg-light mb-4">
                                        <div class="card-body">
                                            <h6>Booking Summary</h6>
                                            <div id="bookingSummary"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="customer_name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="customer_phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="stylist_id" id="selected_stylist">
                                    
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep(3)">← Previous</button>
                                        <button type="submit" class="btn btn-success">Book Appointment</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/booking.js"></script>
</body>
</html>
