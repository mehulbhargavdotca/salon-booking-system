<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get main categories for price list
$mainCategories = getMainCategories();
$salonSettings = getSalonSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?> - Price List & Services</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }
        
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
        
        .hero-section {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.9), rgba(44, 62, 80, 0.9)), 
                        url('https://images.unsplash.com/photo-1560066984-138dadb4c035?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            min-height: 60vh;
        }
        
        .category-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .service-item {
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }
        
        .service-item:hover {
            background-color: #f8f9fa;
        }
        
        .price-tag {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4);
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
        
        .section-title {
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .loading {
            display: none;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand font-playfair fw-bold fs-3" href="index.php" style="color: var(--primary-color);">
                <i class="fas fa-cut me-2"></i><?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary-custom text-white px-3 py-2 rounded ms-2" href="booking.php">
                            <i class="fas fa-calendar-plus me-1"></i>Book Now
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center">
        <div class="container text-center text-white">
            <h1 class="display-4 font-playfair fw-bold mb-4">Welcome to <?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?></h1>
            <p class="lead mb-5">Experience luxury and elegance with our premium salon services</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <p class="fs-5 mb-4">Professional beauty treatments in a relaxing, modern environment. Browse our services below and book your appointment today.</p>
                    <a href="booking.php" class="btn btn-primary-custom btn-lg px-5 py-3 me-3">
                        <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                    </a>
                    <a href="#services" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="fas fa-list me-2"></i>View Services
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 font-playfair fw-bold section-title">Our Services & Prices</h2>
                <p class="lead text-muted mt-4">Choose from our wide range of professional beauty services</p>
            </div>

            <!-- Category Selection -->
            <div class="row mb-5">
                <?php foreach ($mainCategories as $category): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card category-card h-100 cursor-pointer" onclick="loadCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <?php
                                $icons = [
                                    'Hair Services' => 'fas fa-cut',
                                    'Skin Care' => 'fas fa-spa',
                                    'Nail Services' => 'fas fa-hand-sparkles',
                                    'Spa Treatments' => 'fas fa-leaf'
                                ];
                                $icon = $icons[$category['name']] ?? 'fas fa-star';
                                ?>
                                <i class="<?php echo $icon; ?> fa-3x" style="color: var(--primary-color);"></i>
                            </div>
                            <h5 class="card-title font-playfair fw-bold"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="card-text text-muted">Click to view services</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Services Display Area -->
            <div id="services-display" class="row">
                <div class="col-12 text-center">
                    <div class="p-5">
                        <i class="fas fa-mouse-pointer fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Select a category above to view services and prices</h4>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loading" class="loading text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Loading services...</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-6 font-playfair fw-bold mb-4">About Our Salon</h2>
                    <p class="lead mb-4">With years of experience in the beauty industry, we provide exceptional services in a luxurious and relaxing environment.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Professional Staff</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Premium Products</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Modern Equipment</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Hygenic Environment</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Salon Interior" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-6 font-playfair fw-bold section-title">Contact Us</h2>
                <p class="lead text-muted mt-4">Get in touch to book your appointment or ask any questions</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="p-4">
                                <i class="fas fa-phone fa-2x text-primary mb-3"></i>
                                <h5>Phone</h5>
                                <p class="text-muted"><?php echo $salonSettings['salon_phone'] ?? '+1-555-0123'; ?></p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <div class="p-4">
                                <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                                <h5>Email</h5>
                                <p class="text-muted"><?php echo $salonSettings['salon_email'] ?? 'info@elegantsalon.com'; ?></p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <div class="p-4">
                                <i class="fas fa-clock fa-2x text-primary mb-3"></i>
                                <h5>Hours</h5>
                                <p class="text-muted">Mon-Sat: 10AM-8PM<br>Sun: 10AM-7PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <!-- Floating Book Appointment Button -->
    <a href="booking.php" class="btn btn-primary-custom btn-lg floating-btn rounded-circle p-3" title="Book Appointment">
        <i class="fas fa-calendar-plus fa-lg"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let currentCategory = null;
        
        // Load category services
        async function loadCategory(categoryId, categoryName) {
            currentCategory = categoryId;
            
            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('services-display').innerHTML = '';
            
            try {
                const response = await fetch('api/get-services.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        category_id: categoryId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayServices(data.data, categoryName);
                } else {
                    showError('Failed to load services: ' + data.message);
                }
            } catch (error) {
                showError('Error loading services. Please try again.');
                console.error('Error:', error);
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }
        
        // Display services
        function displayServices(data, categoryName) {
            const servicesDisplay = document.getElementById('services-display');
            
            if (!data.subcategories || data.subcategories.length === 0) {
                servicesDisplay.innerHTML = `
                    <div class="col-12 text-center">
                        <div class="p-5">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No services available in this category</h4>
                        </div>
                    </div>
                `;
                return;
            }
            
            let html = `
                <div class="col-12 mb-4">
                    <h3 class="font-playfair fw-bold text-center">${categoryName}</h3>
                    <hr class="w-25 mx-auto" style="border-color: var(--primary-color); border-width: 2px;">
                </div>
            `;
            
            data.subcategories.forEach(subcategory => {
                if (subcategory.services && subcategory.services.length > 0) {
                    html += `
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0 font-playfair">${subcategory.name}</h5>
                                </div>
                                <div class="card-body p-0">
                    `;
                    
                    subcategory.services.forEach((service, index) => {
                        const isLast = index === subcategory.services.length - 1;
                        html += `
                            <div class="service-item p-3 ${isLast ? '' : 'border-bottom'}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">${service.name}</h6>
                                        ${service.description ? `<p class="text-muted small mb-1">${service.description}</p>` : ''}
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>${service.duration} minutes
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="price-tag fs-5 fw-bold">$${parseFloat(service.price).toFixed(2)}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                }
            });
            
            servicesDisplay.innerHTML = html;
            servicesDisplay.classList.add('fade-in');
        }
        
        // Show error message
        function showError(message) {
            const servicesDisplay = document.getElementById('services-display');
            servicesDisplay.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger text-center" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>${message}
                    </div>
                </div>
            `;
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Load first category by default
        document.addEventListener('DOMContentLoaded', function() {
            const firstCategory = <?php echo json_encode($mainCategories[0] ?? null); ?>;
            if (firstCategory) {
                setTimeout(() => {
                    loadCategory(firstCategory.id, firstCategory.name);
                }, 500);
            }
        });
    </script>
</body>
</html>
