<?php include 'includes/auth.php'; ?>
<?php include 'templates/header.php'; ?>

<h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h2>

<p>You are logged in as: <strong><?= htmlspecialchars($_SESSION['user_email']) ?></strong></p>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card border-primary">
            <div class="card-body">
                <h5 class="card-title">Book an Appointment</h5>
                <p class="card-text">Choose your desired service and time slot.</p>
                <a href="booking.php" class="btn btn-primary">Start Booking</a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">My Appointments</h5>
                <p class="card-text">View your appointment history and upcoming bookings.</p>
                <a href="summary.php" class="btn btn-success">View Summary</a>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
