<?php
require_once 'includes/functions.php';

// Get salon information
$salonInfo = getSalonInfo();

// Get all services grouped by category
$stmt = $pdo->query("
    SELECT c.name as category_name, c.note as category_note,
           s.id, s.name as service_name, s.price, s.duration_in_minutes
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
        $servicesByCategory[$categoryName] = [
            'note' => $service['category_note'],
            'services' => []
        ];
    }
    $servicesByCategory[$categoryName]['services'][] = $service;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($salonInfo['name'] ?? 'Dena Beauty Salon'); ?> - Price List</title>
    <link href="Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <!-- Salon Header -->
        <div class="salon-header">
            <h1 class="salon-name"><?php echo htmlspecialchars($salonInfo['name'] ?? 'Dena Beauty Salon'); ?></h1>
            <p class="salon-slogan"><?php echo htmlspecialchars($salonInfo['slogan'] ?? 'Your Trusted Destination For Advanced Beauty Solutions.'); ?></p>
            
            <div class="contact-info mb-4">
                <?php if ($salonInfo['phone']): ?>
                    <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($salonInfo['phone']); ?></p>
                <?php endif; ?>
                <?php if ($salonInfo['email']): ?>
                    <p class="mb-3"><strong>Email:</strong> <?php echo htmlspecialchars($salonInfo['email']); ?></p>
                <?php endif; ?>
            </div>
            
            <a href="booking.php" class="btn book-appointment-btn">Book Appointment</a>
        </div>

        <!-- Price List -->
        <div class="price-list p-4">
            <h2 class="text-center mb-4">Our Services & Pricing</h2>
            
            <?php foreach ($servicesByCategory as $categoryName => $categoryData): ?>
                <div class="category-section mb-5">
                    <div class="category-header">
                        <h3 class="mb-1"><?php echo htmlspecialchars($categoryName); ?></h3>
                        <?php if ($categoryData['note']): ?>
                            <p class="mb-0"><small><?php echo htmlspecialchars($categoryData['note']); ?></small></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($categoryData['services'] as $service): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card service-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($service['service_name']); ?></h5>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h4 text-primary mb-0">
                                                $<?php echo number_format($service['price'], 2); ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php echo $service['duration_in_minutes']; ?> minutes
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div class="text-center py-4">
            <p class="text-muted">To book an appointment, please click the "Book Appointment" button above.</p>
            <p class="text-muted small">
                <?php echo htmlspecialchars($salonInfo['name'] ?? 'Dena Beauty Salon'); ?> &copy; <?php echo date('Y'); ?>
            </p>
        </div>
    </div>

    <script src="Bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
