<?php
// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
$totalCategories = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM services");
$totalServices = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM stylists");
$totalStylists = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM appointments WHERE appointment_date >= CURDATE()");
$upcomingAppointments = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM appointments WHERE appointment_date = CURDATE()");
$todayAppointments = $stmt->fetch()['total'];
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $totalCategories; ?></h4>
                        <p class="mb-0">Categories</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $totalServices; ?></h4>
                        <p class="mb-0">Services</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-cut fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $totalStylists; ?></h4>
                        <p class="mb-0">Stylists</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $todayAppointments; ?></h4>
                        <p class="mb-0">Today's Appointments</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-day fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Appointments</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $pdo->query("
                    SELECT a.*, s.name as service_name, st.name as stylist_name 
                    FROM appointments a 
                    JOIN services s ON a.service_id = s.id 
                    JOIN stylists st ON a.stylist_id = st.id 
                    WHERE a.appointment_date >= CURDATE() 
                    ORDER BY a.appointment_date, a.appointment_time 
                    LIMIT 5
                ");
                $recentAppointments = $stmt->fetchAll();
                ?>
                
                <?php if (empty($recentAppointments)): ?>
                    <p class="text-muted">No upcoming appointments</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Stylist</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAppointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['stylist_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="?page=categories" class="btn btn-primary">Manage Categories</a>
                    <a href="?page=services" class="btn btn-success">Manage Services</a>
                    <a href="?page=stylists" class="btn btn-info">Manage Stylists</a>
                    <a href="?page=appointments" class="btn btn-warning">View Appointments</a>
                    <a href="?page=business_hours" class="btn btn-secondary">Business Hours</a>
                </div>
            </div>
        </div>
    </div>
</div>