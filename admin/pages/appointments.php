<?php
$message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'delete') {
        $id = (int)$_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = '<div class="alert alert-success">Appointment deleted successfully!</div>';
        }
    }
}

// Get filter parameters
$date_filter = $_GET['date'] ?? date('Y-m-d');
$stylist_filter = $_GET['stylist'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($date_filter) {
    $where_conditions[] = "a.appointment_date = ?";
    $params[] = $date_filter;
}

if ($stylist_filter) {
    $where_conditions[] = "a.stylist_id = ?";
    $params[] = $stylist_filter;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get appointments
$stmt = $pdo->prepare("
    SELECT a.*, 
           s.name as service_name, 
           s.price as service_price,
           s.duration_in_minutes,
           st.name as stylist_name,
           c.name as category_name
    FROM appointments a 
    JOIN services s ON a.service_id = s.id 
    JOIN stylists st ON a.stylist_id = st.id 
    JOIN categories c ON s.category_id = c.id
    $where_clause
    ORDER BY a.appointment_date, a.appointment_time
");
$stmt->execute($params);
$appointments = $stmt->fetchAll();

// Get all stylists for filter dropdown
$stmt = $pdo->query("SELECT * FROM stylists ORDER BY name");
$stylists = $stmt->fetchAll();
?>

<?php echo $message; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Filter Appointments</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="stylist" class="form-label">Stylist</label>
                        <select class="form-control" id="stylist" name="stylist">
                            <option value="">All Stylists</option>
                            <?php foreach ($stylists as $stylist): ?>
                                <option value="<?php echo $stylist['id']; ?>" <?php echo $stylist_filter == $stylist['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($stylist['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="?page=appointments" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Appointments <?php echo $date_filter ? 'for ' . date('F j, Y', strtotime($date_filter)) : ''; ?></h5>
                <span class="badge bg-primary"><?php echo count($appointments); ?> appointments</span>
            </div>
            <div class="card-body">
                <?php if (empty($appointments)): ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No appointments found for the selected criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Service</th>
                                    <th>Category</th>
                                    <th>Stylist</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['id']; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['customer_phone']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($appointment['category_name']); ?></small></td>
                                        <td><?php echo htmlspecialchars($appointment['stylist_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td><?php echo $appointment['duration_in_minutes']; ?> min</td>
                                        <td>$<?php echo number_format($appointment['service_price'], 2); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this appointment?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Summary -->
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Summary</h6>
                                <p class="mb-1"><strong>Total Appointments:</strong> <?php echo count($appointments); ?></p>
                                <p class="mb-1"><strong>Total Revenue:</strong> $<?php echo number_format(array_sum(array_column($appointments, 'service_price')), 2); ?></p>
                                <p class="mb-1"><strong>Total Duration:</strong> <?php echo array_sum(array_column($appointments, 'duration_in_minutes')); ?> minutes</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Stylists Summary</h6>
                                <?php
                                $stylistSummary = [];
                                foreach ($appointments as $appointment) {
                                    $stylistName = $appointment['stylist_name'];
                                    if (!isset($stylistSummary[$stylistName])) {
                                        $stylistSummary[$stylistName] = ['count' => 0, 'revenue' => 0];
                                    }
                                    $stylistSummary[$stylistName]['count']++;
                                    $stylistSummary[$stylistName]['revenue'] += $appointment['service_price'];
                                }
                                
                                foreach ($stylistSummary as $stylistName => $summary):
                                ?>
                                    <p class="mb-1">
                                        <strong><?php echo htmlspecialchars($stylistName); ?>:</strong> 
                                        <?php echo $summary['count']; ?> appointments, 
                                        $<?php echo number_format($summary['revenue'], 2); ?>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>