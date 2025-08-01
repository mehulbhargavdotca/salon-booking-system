<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Simple authentication check (in production, implement proper admin authentication)
session_start();

// Get dashboard statistics
try {
    $db = getDB();
    
    // Get statistics
    $stats = [
        'total_bookings' => $db->count('bookings'),
        'pending_bookings' => $db->count('bookings', ['status' => 'pending']),
        'confirmed_bookings' => $db->count('bookings', ['status' => 'confirmed']),
        'total_services' => $db->count('services', ['status' => 'active']),
        'total_staff' => $db->count('staff', ['status' => 'active']),
        'total_categories' => $db->count('main_categories', ['status' => 'active'])
    ];
    
    // Get recent bookings
    $recentBookings = $db->fetchAll(
        "SELECT b.*, s.name as staff_name 
         FROM bookings b 
         JOIN staff s ON b.staff_id = s.id 
         ORDER BY b.created_at DESC 
         LIMIT 10"
    );
    
    // Get today's appointments
    $todayAppointments = $db->fetchAll(
        "SELECT b.*, s.name as staff_name 
         FROM bookings b 
         JOIN staff s ON b.staff_id = s.id 
         WHERE b.booking_date = CURDATE() 
         AND b.status IN ('pending', 'confirmed')
         ORDER BY b.booking_time ASC"
    );
    
    // Get monthly revenue (current month)
    $monthlyRevenue = $db->fetch(
        "SELECT COALESCE(SUM(total_price), 0) as revenue 
         FROM bookings 
         WHERE MONTH(booking_date) = MONTH(CURDATE()) 
         AND YEAR(booking_date) = YEAR(CURDATE()) 
         AND status IN ('confirmed', 'completed')"
    );
    
    $salonSettings = getSalonSettings();
    
} catch (Exception $e) {
    error_log("Error loading dashboard data: " . $e->getMessage());
    $stats = [];
    $recentBookings = [];
    $todayAppointments = [];
    $monthlyRevenue = ['revenue' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?></title>
    
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
            --sidebar-width: 250px;
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
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color), #b8941f);
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            border: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-icon.primary { background: var(--primary-color); }
        .stat-icon.success { background: #28a745; }
        .stat-icon.warning { background: #ffc107; }
        .stat-icon.info { background: #17a2b8; }
        .stat-icon.secondary { background: #6c757d; }
        
        .table-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table-card .card-header {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
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
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header text-white text-center">
            <h4 class="font-playfair mb-0">
                <i class="fas fa-cut me-2"></i>Admin Panel
            </h4>
            <small class="opacity-75"><?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?></small>
        </div>
        
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="bookings.php">
                    <i class="fas fa-calendar-alt me-2"></i>Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="categories.php">
                    <i class="fas fa-tags me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="services.php">
                    <i class="fas fa-list me-2"></i>Services
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="staff.php">
                    <i class="fas fa-users me-2"></i>Staff
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-external-link-alt me-2"></i>View Website
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-link d-md-none p-0" id="sidebar-toggle">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
                <h2 class="font-playfair fw-bold mb-0 d-none d-md-block">Dashboard</h2>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3">Welcome, Admin</span>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>Account
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../index.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="container-fluid p-4">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon primary me-3">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Bookings</div>
                                <div class="h4 mb-0"><?php echo number_format($stats['total_bookings'] ?? 0); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon warning me-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Pending Bookings</div>
                                <div class="h4 mb-0"><?php echo number_format($stats['pending_bookings'] ?? 0); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon success me-3">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Monthly Revenue</div>
                                <div class="h4 mb-0"><?php echo formatPrice($monthlyRevenue['revenue'] ?? 0); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon info me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Active Staff</div>
                                <div class="h4 mb-0"><?php echo number_format($stats['total_staff'] ?? 0); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="table-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="bookings.php?action=add" class="btn btn-primary-custom w-100">
                                        <i class="fas fa-plus me-2"></i>New Booking
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="services.php?action=add" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-plus me-2"></i>Add Service
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="staff.php?action=add" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-user-plus me-2"></i>Add Staff
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="categories.php?action=add" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-tag me-2"></i>Add Category
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Today's Appointments -->
                <div class="col-lg-6 mb-4">
                    <div class="table-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Appointments</h5>
                            <a href="bookings.php?date=<?php echo date('Y-m-d'); ?>" class="btn btn-sm btn-light">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($todayAppointments)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No appointments scheduled for today</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Time</th>
                                            <th>Customer</th>
                                            <th>Staff</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($todayAppointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo formatTime($appointment['booking_time']); ?></td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($appointment['customer_name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($appointment['customer_phone']); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($appointment['staff_name']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="col-lg-6 mb-4">
                    <div class="table-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Bookings</h5>
                            <a href="bookings.php" class="btn btn-sm btn-light">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recentBookings)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No recent bookings</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($recentBookings, 0, 8) as $booking): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo formatDate($booking['created_at'], 'M j, g:i A'); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo formatDate($booking['booking_date'], 'M j'); ?></td>
                                            <td><?php echo formatPrice($booking['total_price']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Overview -->
            <div class="row">
                <div class="col-12">
                    <div class="table-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>System Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3">
                                    <div class="h3 text-primary"><?php echo number_format($stats['total_categories'] ?? 0); ?></div>
                                    <div class="text-muted">Categories</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="h3 text-info"><?php echo number_format($stats['total_services'] ?? 0); ?></div>
                                    <div class="text-muted">Services</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="h3 text-success"><?php echo number_format($stats['confirmed_bookings'] ?? 0); ?></div>
                                    <div class="text-muted">Confirmed</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="h3 text-secondary"><?php echo number_format($stats['total_staff'] ?? 0); ?></div>
                                    <div class="text-muted">Staff Members</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // Auto-refresh dashboard data every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000); // 5 minutes
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>