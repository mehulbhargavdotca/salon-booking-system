<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdminLogin();

$page = $_GET['page'] ?? 'overview';
$salonInfo = getSalonInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dena Beauty Salon</title>
    <link href="../Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center p-3">
                        <h5 class="text-white">Admin Panel</h5>
                        <p class="text-muted small">Welcome, <?php echo $_SESSION['admin_username']; ?></p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'overview' ? 'active' : ''; ?>" href="?page=overview">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'categories' ? 'active' : ''; ?>" href="?page=categories">
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'services' ? 'active' : ''; ?>" href="?page=services">
                                Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'stylists' ? 'active' : ''; ?>" href="?page=stylists">
                                Stylists
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'appointments' ? 'active' : ''; ?>" href="?page=appointments">
                                Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'business_hours' ? 'active' : ''; ?>" href="?page=business_hours">
                                Business Hours
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'settings' ? 'active' : ''; ?>" href="?page=settings">
                                Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/logout.php">
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <?php
                        switch($page) {
                            case 'categories': echo 'Categories Management'; break;
                            case 'services': echo 'Services Management'; break;
                            case 'stylists': echo 'Stylists Management'; break;
                            case 'appointments': echo 'Appointments'; break;
                            case 'business_hours': echo 'Business Hours'; break;
                            case 'settings': echo 'Settings'; break;
                            default: echo 'Dashboard Overview'; break;
                        }
                        ?>
                    </h1>
                </div>

                <?php
                switch($page) {
                    case 'categories':
                        include 'pages/categories.php';
                        break;
                    case 'services':
                        include 'pages/services.php';
                        break;
                    case 'stylists':
                        include 'pages/stylists.php';
                        break;
                    case 'appointments':
                        include 'pages/appointments.php';
                        break;
                    case 'business_hours':
                        include 'pages/business_hours.php';
                        break;
                    case 'settings':
                        include 'pages/settings.php';
                        break;
                    default:
                        include 'pages/overview.php';
                        break;
                }
                ?>
            </main>
        </div>
    </div>

    <script src="../Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>