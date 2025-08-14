<?php
$message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $name = sanitize($_POST['name']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $services = $_POST['services'] ?? [];
        
        try {
            $pdo->beginTransaction();
            
            // Insert stylist
            $stmt = $pdo->prepare("INSERT INTO stylists (name, phone, email) VALUES (?, ?, ?)");
            $stmt->execute([$name, $phone, $email]);
            $stylistId = $pdo->lastInsertId();
            
            // Insert stylist services
            if (!empty($services)) {
                $stmt = $pdo->prepare("INSERT INTO stylist_services (stylist_id, service_id) VALUES (?, ?)");
                foreach ($services as $serviceId) {
                    $stmt->execute([$stylistId, $serviceId]);
                }
            }
            
            $pdo->commit();
            $message = '<div class="alert alert-success">Stylist added successfully!</div>';
        } catch (Exception $e) {
            $pdo->rollback();
            $message = '<div class="alert alert-danger">Error adding stylist: ' . $e->getMessage() . '</div>';
        }
    }
    
    if ($action == 'edit') {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $services = $_POST['services'] ?? [];
        
        try {
            $pdo->beginTransaction();
            
            // Update stylist
            $stmt = $pdo->prepare("UPDATE stylists SET name = ?, phone = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $email, $id]);
            
            // Delete existing services
            $stmt = $pdo->prepare("DELETE FROM stylist_services WHERE stylist_id = ?");
            $stmt->execute([$id]);
            
            // Insert new services
            if (!empty($services)) {
                $stmt = $pdo->prepare("INSERT INTO stylist_services (stylist_id, service_id) VALUES (?, ?)");
                foreach ($services as $serviceId) {
                    $stmt->execute([$id, $serviceId]);
                }
            }
            
            $pdo->commit();
            $message = '<div class="alert alert-success">Stylist updated successfully!</div>';
        } catch (Exception $e) {
            $pdo->rollback();
            $message = '<div class="alert alert-danger">Error updating stylist: ' . $e->getMessage() . '</div>';
        }
    }
    
    if ($action == 'delete') {
        $id = (int)$_POST['id'];
        
        // Check if stylist has appointments
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE stylist_id = ?");
        $stmt->execute([$id]);
        $appointmentCount = $stmt->fetch()['count'];
        
        if ($appointmentCount > 0) {
            $message = '<div class="alert alert-danger">Cannot delete stylist with existing appointments!</div>';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Delete stylist services
                $stmt = $pdo->prepare("DELETE FROM stylist_services WHERE stylist_id = ?");
                $stmt->execute([$id]);
                
                // Delete stylist
                $stmt = $pdo->prepare("DELETE FROM stylists WHERE id = ?");
                $stmt->execute([$id]);
                
                $pdo->commit();
                $message = '<div class="alert alert-success">Stylist deleted successfully!</div>';
            } catch (Exception $e) {
                $pdo->rollback();
                $message = '<div class="alert alert-danger">Error deleting stylist: ' . $e->getMessage() . '</div>';
            }
        }
    }
}

// Get all services grouped by category
$stmt = $pdo->query("
    SELECT s.*, c.name as category_name 
    FROM services s 
    JOIN categories c ON s.category_id = c.id 
    ORDER BY c.name, s.name
");
$allServices = $stmt->fetchAll();

// Get all stylists with their services
$stmt = $pdo->query("
    SELECT s.*, 
           GROUP_CONCAT(DISTINCT ss.service_id) as service_ids,
           GROUP_CONCAT(DISTINCT srv.name SEPARATOR ', ') as service_names,
           COUNT(DISTINCT a.id) as appointment_count
    FROM stylists s 
    LEFT JOIN stylist_services ss ON s.id = ss.stylist_id
    LEFT JOIN services srv ON ss.service_id = srv.id
    LEFT JOIN appointments a ON s.id = a.stylist_id
    GROUP BY s.id 
    ORDER BY s.name
");
$stylists = $stmt->fetchAll();
?>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Add New Stylist</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="name" class="form-label">Stylist Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Services</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                            <?php
                            $currentCategory = '';
                            foreach ($allServices as $service):
                                if ($service['category_name'] != $currentCategory):
                                    if ($currentCategory != '') echo '</div>';
                                    $currentCategory = $service['category_name'];
                                    echo '<h6 class="mt-2 mb-1 text-primary">' . htmlspecialchars($currentCategory) . '</h6>';
                                    echo '<div>';
                                endif;
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="services[]" value="<?php echo $service['id']; ?>" id="service_<?php echo $service['id']; ?>">
                                    <label class="form-check-label" for="service_<?php echo $service['id']; ?>">
                                        <?php echo htmlspecialchars($service['name']); ?>
                                    </label>
                                </div>
                            <?php 
                            endforeach;
                            if ($currentCategory != '') echo '</div>';
                            ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Stylist</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Existing Stylists</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Services</th>
                                <th>Appointments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stylists as $stylist): ?>
                                <tr>
                                    <td><?php echo $stylist['id']; ?></td>
                                    <td><?php echo htmlspecialchars($stylist['name']); ?></td>
                                    <td><?php echo htmlspecialchars($stylist['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($stylist['email']); ?></td>
                                    <td>
                                        <?php if ($stylist['service_names']): ?>
                                            <small><?php echo htmlspecialchars($stylist['service_names']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">No services assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-warning"><?php echo $stylist['appointment_count']; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editStylist(<?php echo htmlspecialchars(json_encode($stylist)); ?>)">Edit</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this stylist?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $stylist['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" <?php echo $stylist['appointment_count'] > 0 ? 'disabled' : ''; ?>>Delete</button>
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

<!-- Edit Stylist Modal -->
<div class="modal fade" id="editStylistModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Stylist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Stylist Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone">
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Services</label>
                            <div id="edit_services" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                                <?php
                                $currentCategory = '';
                                foreach ($allServices as $service):
                                    if ($service['category_name'] != $currentCategory):
                                        if ($currentCategory != '') echo '</div>';
                                        $currentCategory = $service['category_name'];
                                        echo '<h6 class="mt-2 mb-1 text-primary">' . htmlspecialchars($currentCategory) . '</h6>';
                                        echo '<div>';
                                    endif;
                                ?>
                                    <div class="form-check">
                                        <input class="form-check-input edit-service-checkbox" type="checkbox" name="services[]" value="<?php echo $service['id']; ?>" id="edit_service_<?php echo $service['id']; ?>">
                                        <label class="form-check-label" for="edit_service_<?php echo $service['id']; ?>">
                                            <?php echo htmlspecialchars($service['name']); ?>
                                        </label>
                                    </div>
                                <?php 
                                endforeach;
                                if ($currentCategory != '') echo '</div>';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stylist</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStylist(stylist) {
    document.getElementById('edit_id').value = stylist.id;
    document.getElementById('edit_name').value = stylist.name;
    document.getElementById('edit_phone').value = stylist.phone || '';
    document.getElementById('edit_email').value = stylist.email || '';
    
    // Clear all checkboxes
    document.querySelectorAll('.edit-service-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Check the services this stylist provides
    if (stylist.service_ids) {
        const serviceIds = stylist.service_ids.split(',');
        serviceIds.forEach(serviceId => {
            const checkbox = document.getElementById('edit_service_' + serviceId);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
    
    new bootstrap.Modal(document.getElementById('editStylistModal')).show();
}
</script>