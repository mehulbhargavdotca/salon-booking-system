<?php
$message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $category_id = (int)$_POST['category_id'];
        $name = sanitize($_POST['name']);
        $price = (float)$_POST['price'];
        $duration = (int)$_POST['duration_in_minutes'];
        
        $stmt = $pdo->prepare("INSERT INTO services (category_id, name, price, duration_in_minutes) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$category_id, $name, $price, $duration])) {
            $message = '<div class="alert alert-success">Service added successfully!</div>';
        }
    }
    
    if ($action == 'edit') {
        $id = (int)$_POST['id'];
        $category_id = (int)$_POST['category_id'];
        $name = sanitize($_POST['name']);
        $price = (float)$_POST['price'];
        $duration = (int)$_POST['duration_in_minutes'];
        
        $stmt = $pdo->prepare("UPDATE services SET category_id = ?, name = ?, price = ?, duration_in_minutes = ? WHERE id = ?");
        if ($stmt->execute([$category_id, $name, $price, $duration, $id])) {
            $message = '<div class="alert alert-success">Service updated successfully!</div>';
        }
    }
    
    if ($action == 'delete') {
        $id = (int)$_POST['id'];
        
        // Check if service has appointments
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE service_id = ?");
        $stmt->execute([$id]);
        $appointmentCount = $stmt->fetch()['count'];
        
        if ($appointmentCount > 0) {
            $message = '<div class="alert alert-danger">Cannot delete service with existing appointments!</div>';
        } else {
            // Delete from stylist_services first
            $stmt = $pdo->prepare("DELETE FROM stylist_services WHERE service_id = ?");
            $stmt->execute([$id]);
            
            // Then delete the service
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            if ($stmt->execute([$id])) {
                $message = '<div class="alert alert-success">Service deleted successfully!</div>';
            }
        }
    }
}

// Get all categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Get all services with category names
$stmt = $pdo->query("
    SELECT s.*, c.name as category_name, 
           COUNT(DISTINCT ss.stylist_id) as stylist_count,
           COUNT(DISTINCT a.id) as appointment_count
    FROM services s 
    JOIN categories c ON s.category_id = c.id 
    LEFT JOIN stylist_services ss ON s.id = ss.service_id
    LEFT JOIN appointments a ON s.id = a.service_id
    GROUP BY s.id 
    ORDER BY c.name, s.name
");
$services = $stmt->fetchAll();
?>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Add New Service</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="duration_in_minutes" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="duration_in_minutes" name="duration_in_minutes" min="15" step="15" value="60" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Service</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Existing Services</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Service Name</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Stylists</th>
                                <th>Appointments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo $service['id']; ?></td>
                                    <td><?php echo htmlspecialchars($service['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td>$<?php echo number_format($service['price'], 2); ?></td>
                                    <td><?php echo $service['duration_in_minutes']; ?> min</td>
                                    <td><span class="badge bg-info"><?php echo $service['stylist_count']; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $service['appointment_count']; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">Edit</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this service?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" <?php echo $service['appointment_count'] > 0 ? 'disabled' : ''; ?>>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label for="edit_category_id" class="form-label">Category</label>
                        <select class="form-control" id="edit_category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price ($)</label>
                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_duration_in_minutes" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="edit_duration_in_minutes" name="duration_in_minutes" min="15" step="15" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editService(service) {
    document.getElementById('edit_id').value = service.id;
    document.getElementById('edit_category_id').value = service.category_id;
    document.getElementById('edit_name').value = service.name;
    document.getElementById('edit_price').value = service.price;
    document.getElementById('edit_duration_in_minutes').value = service.duration_in_minutes;
    new bootstrap.Modal(document.getElementById('editServiceModal')).show();
}
</script>