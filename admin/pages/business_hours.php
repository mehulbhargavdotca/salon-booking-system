<?php
$message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_hours') {
        try {
            $pdo->beginTransaction();
            
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            foreach ($days as $day) {
                $open_time = $_POST['open_time'][$day] ?? '';
                $close_time = $_POST['close_time'][$day] ?? '';
                
                if ($open_time && $close_time) {
                    $stmt = $pdo->prepare("
                        INSERT INTO business_hours (day_of_week, open_time, close_time) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE open_time = VALUES(open_time), close_time = VALUES(close_time)
                    ");
                    $stmt->execute([$day, $open_time, $close_time]);
                }
            }
            
            $pdo->commit();
            $message = '<div class="alert alert-success">Business hours updated successfully!</div>';
        } catch (Exception $e) {
            $pdo->rollback();
            $message = '<div class="alert alert-danger">Error updating business hours: ' . $e->getMessage() . '</div>';
        }
    }
    
    if ($action == 'update_buffer') {
        $buffer_time = (int)$_POST['buffer_time_minutes'];
        
        $stmt = $pdo->prepare("UPDATE settings SET buffer_time_minutes = ? WHERE id = 1");
        if ($stmt->execute([$buffer_time])) {
            $message = '<div class="alert alert-success">Buffer time updated successfully!</div>';
        }
    }
}

// Get current business hours
$stmt = $pdo->query("SELECT * FROM business_hours ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
$businessHours = [];
while ($row = $stmt->fetch()) {
    $businessHours[$row['day_of_week']] = $row;
}

// Get current buffer time
$stmt = $pdo->query("SELECT buffer_time_minutes FROM settings LIMIT 1");
$currentBufferTime = $stmt->fetch()['buffer_time_minutes'] ?? 15;

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Business Hours</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_hours">
                    
                    <?php foreach ($days as $day): ?>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold"><?php echo $day; ?></label>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Open Time</label>
                                <input type="time" class="form-control" 
                                       name="open_time[<?php echo $day; ?>]" 
                                       value="<?php echo $businessHours[$day]['open_time'] ?? '09:00'; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Close Time</label>
                                <input type="time" class="form-control" 
                                       name="close_time[<?php echo $day; ?>]" 
                                       value="<?php echo $businessHours[$day]['close_time'] ?? '17:00'; ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn btn-primary">Update Business Hours</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Buffer Time Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_buffer">
                    <div class="mb-3">
                        <label for="buffer_time_minutes" class="form-label">Buffer Time Between Appointments</label>
                        <select class="form-control" id="buffer_time_minutes" name="buffer_time_minutes" required>
                            <option value="15" <?php echo $currentBufferTime == 15 ? 'selected' : ''; ?>>15 minutes</option>
                            <option value="30" <?php echo $currentBufferTime == 30 ? 'selected' : ''; ?>>30 minutes</option>
                            <option value="45" <?php echo $currentBufferTime == 45 ? 'selected' : ''; ?>>45 minutes</option>
                            <option value="60" <?php echo $currentBufferTime == 60 ? 'selected' : ''; ?>>60 minutes</option>
                        </select>
                        <div class="form-text">
                            This is the gap between each appointment. For example, if buffer time is 30 minutes and salon opens at 10:00 AM, 
                            appointment times will be 10:00, 10:30, 11:00, 11:30, etc.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Buffer Time</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Preview Time Slots</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">Example time slots for Monday with current buffer time:</p>
                <?php
                $mondayHours = $businessHours['Monday'] ?? ['open_time' => '09:00:00', 'close_time' => '17:00:00'];
                $sampleSlots = generateTimeSlots($mondayHours['open_time'], $mondayHours['close_time'], $currentBufferTime, 60);
                $displaySlots = array_slice($sampleSlots, 0, 8); // Show first 8 slots
                ?>
                <div class="d-flex flex-wrap">
                    <?php foreach ($displaySlots as $slot): ?>
                        <span class="badge bg-secondary me-1 mb-1"><?php echo date('g:i A', strtotime($slot)); ?></span>
                    <?php endforeach; ?>
                    <?php if (count($sampleSlots) > 8): ?>
                        <span class="text-muted">... and <?php echo count($sampleSlots) - 8; ?> more</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>