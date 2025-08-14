<?php
$message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_salon_info') {
        $name = sanitize($_POST['name']);
        $slogan = sanitize($_POST['slogan']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        
        $stmt = $pdo->prepare("UPDATE salon_info SET name = ?, slogan = ?, phone = ?, email = ? WHERE id = 1");
        if ($stmt->execute([$name, $slogan, $phone, $email])) {
            $message = '<div class="alert alert-success">Salon information updated successfully!</div>';
        }
    }
    
    if ($action == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Get current admin password
        $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        
        if (!password_verify($current_password, $admin['password'])) {
            $message = '<div class="alert alert-danger">Current password is incorrect!</div>';
        } elseif ($new_password !== $confirm_password) {
            $message = '<div class="alert alert-danger">New passwords do not match!</div>';
        } elseif (strlen($new_password) < 6) {
            $message = '<div class="alert alert-danger">New password must be at least 6 characters long!</div>';
        } else {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $_SESSION['admin_id']])) {
                $message = '<div class="alert alert-success">Password changed successfully!</div>';
            }
        }
    }
}

// Get current salon info
$salonInfo = getSalonInfo();
?>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Salon Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_salon_info">
                    <div class="mb-3">
                        <label for="name" class="form-label">Salon Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($salonInfo['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="slogan" class="form-label">Slogan</label>
                        <input type="text" class="form-control" id="slogan" name="slogan" 
                               value="<?php echo htmlspecialchars($salonInfo['slogan'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($salonInfo['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($salonInfo['email'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Salon Information</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Change Admin Password</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Admin Username:</strong></td>
                        <td><?php echo htmlspecialchars($_SESSION['admin_username']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Current Buffer Time:</strong></td>
                        <td><?php echo getBufferTime(); ?> minutes</td>
                    </tr>
                    <tr>
                        <td><strong>Total Categories:</strong></td>
                        <td>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
                            echo $stmt->fetch()['count'];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total Services:</strong></td>
                        <td>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM services");
                            echo $stmt->fetch()['count'];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total Stylists:</strong></td>
                        <td>
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM stylists");
                            echo $stmt->fetch()['count'];
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>