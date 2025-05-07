<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'templates/header.php';

// Fetch categories and stylists
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$stylists = $conn->query("SELECT * FROM stylists ORDER BY name");
?>

<h2 class="mb-4">Book an Appointment</h2>

<form action="summary.php" method="POST" id="bookingForm">
    <!-- Category -->
    <div class="mb-3">
        <label for="category">Select Category</label>
        <select name="category" id="category" class="form-control" required>
            <option value="">-- Select Category --</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Service (dynamic) -->
    <div class="mb-3">
        <label for="service">Select Service</label>
        <select name="service" id="service" class="form-control" required>
            <option value="">-- Select Service --</option>
        </select>
    </div>

    <!-- Stylist -->
    <div class="mb-3">
        <label for="stylist">Preferred Stylist</label>
        <select name="stylist" id="stylist" class="form-control">
            <option value="">-- Any Stylist --</option>
            <?php while ($row = $stylists->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Date -->
    <div class="mb-3">
        <label for="date">Choose Date</label>
        <input type="date" name="date" id="date" class="form-control" required min="<?= date('Y-m-d') ?>">
    </div>

    <!-- Time Slot (to be populated dynamically later) -->
    <div class="mb-3">
        <label for="time">Choose Time Slot</label>
        <select name="time" id="time" class="form-control" required>
            <option value="">-- Select Time --</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Continue</button>
</form>

<?php include 'templates/footer.php'; ?>
