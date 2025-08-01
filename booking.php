<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get main categories and salon settings
$mainCategories = getMainCategories();
$salonSettings = getSalonSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - <?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?></title>
    
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
            background-color: #f8f9fa;
        }
        
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
        
        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            color: #6c757d;
        }
        
        .step.active {
            color: var(--primary-color);
        }
        
        .step.completed {
            color: #28a745;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #dee2e6;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-number {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .step-connector {
            width: 60px;
            height: 2px;
            background-color: #dee2e6;
            margin: 0 20px;
        }
        
        .step.completed + .step-connector {
            background-color: #28a745;
        }
        
        .booking-step {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }
        
        .booking-step.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .service-card {
            border: 2px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .service-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .service-card.selected {
            border-color: var(--primary-color);
            background-color: rgba(212, 175, 55, 0.1);
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-top: 1rem;
        }
        
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .calendar-day:hover {
            background-color: #e9ecef;
        }
        
        .calendar-day.disabled {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }
        
        .calendar-day.selected {
            background-color: var(--primary-color);
            color: white;
        }
        
        .time-slot {
            border: 2px solid #dee2e6;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .time-slot:hover {
            border-color: var(--primary-color);
        }
        
        .time-slot.selected {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .time-slot.disabled {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
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
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand font-playfair fw-bold fs-3" href="index.php" style="color: var(--primary-color);">
                <i class="fas fa-cut me-2"></i><?php echo $salonSettings['salon_name'] ?? SITE_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-arrow-left me-1"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="booking-container">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-5 font-playfair fw-bold">Book Your Appointment</h1>
                <p class="lead text-muted">Follow the steps below to schedule your visit</p>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" id="step-1-indicator">
                    <div class="step-number">1</div>
                    <span>Select Services</span>
                </div>
                <div class="step-connector"></div>
                <div class="step" id="step-2-indicator">
                    <div class="step-number">2</div>
                    <span>Choose Date</span>
                </div>
                <div class="step-connector"></div>
                <div class="step" id="step-3-indicator">
                    <div class="step-number">3</div>
                    <span>Select Time</span>
                </div>
                <div class="step-connector"></div>
                <div class="step" id="step-4-indicator">
                    <div class="step-number">4</div>
                    <span>Choose Stylist</span>
                </div>
                <div class="step-connector"></div>
                <div class="step" id="step-5-indicator">
                    <div class="step-number">5</div>
                    <span>Your Details</span>
                </div>
                <div class="step-connector"></div>
                <div class="step" id="step-6-indicator">
                    <div class="step-number">6</div>
                    <span>Confirm</span>
                </div>
            </div>

            <!-- Booking Steps -->
            <div class="row">
                <div class="col-lg-8">
                    <!-- Step 1: Service Selection -->
                    <div class="booking-step active" id="step-1">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-list me-2"></i>Select Your Services</h4>
                            </div>
                            <div class="card-body">
                                <!-- Category Selection -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Choose Category:</label>
                                    <div class="row">
                                        <?php foreach ($mainCategories as $category): ?>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <button type="button" class="btn btn-outline-primary w-100 category-btn" 
                                                    data-category-id="<?php echo $category['id']; ?>"
                                                    onclick="selectCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                                <?php
                                                $icons = [
                                                    'Hair Services' => 'fas fa-cut',
                                                    'Skin Care' => 'fas fa-spa',
                                                    'Nail Services' => 'fas fa-hand-sparkles',
                                                    'Spa Treatments' => 'fas fa-leaf'
                                                ];
                                                $icon = $icons[$category['name']] ?? 'fas fa-star';
                                                ?>
                                                <i class="<?php echo $icon; ?> me-2"></i>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </button>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Services Display -->
                                <div id="services-container" style="display: none;">
                                    <label class="form-label fw-bold">Select Services:</label>
                                    <div id="services-list" class="row">
                                        <!-- Services will be loaded here -->
                                    </div>
                                </div>

                                <!-- Loading -->
                                <div id="services-loading" class="text-center py-4" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading services...</p>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary-custom" onclick="nextStep(2)" id="step-1-next" disabled>
                                    Next: Choose Date <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Date Selection -->
                    <div class="booking-step" id="step-2">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-calendar me-2"></i>Choose Your Date</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="calendar-container">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <button type="button" class="btn btn-outline-secondary" onclick="changeMonth(-1)">
                                                    <i class="fas fa-chevron-left"></i>
                                                </button>
                                                <h5 class="mb-0" id="calendar-month-year"></h5>
                                                <button type="button" class="btn btn-outline-secondary" onclick="changeMonth(1)">
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                            </div>
                                            <div class="calendar-header row text-center fw-bold mb-2">
                                                <div class="col">Sun</div>
                                                <div class="col">Mon</div>
                                                <div class="col">Tue</div>
                                                <div class="col">Wed</div>
                                                <div class="col">Thu</div>
                                                <div class="col">Fri</div>
                                                <div class="col">Sat</div>
                                            </div>
                                            <div id="calendar-days" class="calendar-grid">
                                                <!-- Calendar days will be generated here -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="selected-date-info">
                                            <h6>Selected Date:</h6>
                                            <p id="selected-date-display" class="text-muted">Please select a date</p>
                                            
                                            <div class="mt-3">
                                                <h6>Salon Hours:</h6>
                                                <div id="salon-hours-display" class="text-muted">
                                                    Select a date to view hours
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="prevStep(1)">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                                <button type="button" class="btn btn-primary-custom" onclick="nextStep(3)" id="step-2-next" disabled>
                                    Next: Select Time <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Time Selection -->
                    <div class="booking-step" id="step-3">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-clock me-2"></i>Select Your Time</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <p class="text-muted">Available time slots for <span id="selected-date-time"></span></p>
                                </div>
                                <div id="time-slots-container" class="row">
                                    <!-- Time slots will be loaded here -->
                                </div>
                                <div id="time-loading" class="text-center py-4" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading available times...</p>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="prevStep(2)">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                                <button type="button" class="btn btn-primary-custom" onclick="nextStep(4)" id="step-3-next" disabled>
                                    Next: Choose Stylist <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Stylist Selection -->
                    <div class="booking-step" id="step-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-user me-2"></i>Choose Your Stylist</h4>
                            </div>
                            <div class="card-body">
                                <div id="stylists-container" class="row">
                                    <!-- Stylists will be loaded here -->
                                </div>
                                <div id="stylists-loading" class="text-center py-4" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading available stylists...</p>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="prevStep(3)">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                                <button type="button" class="btn btn-primary-custom" onclick="nextStep(5)" id="step-4-next" disabled>
                                    Next: Your Details <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Customer Details -->
                    <div class="booking-step" id="step-5">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Your Information</h4>
                            </div>
                            <div class="card-body">
                                <form id="customer-form">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="customer-name" class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" id="customer-name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="customer-phone" class="form-label">Phone Number *</label>
                                            <input type="tel" class="form-control" id="customer-phone" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="customer-email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="customer-email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="special-notes" class="form-label">Special Notes / Allergies</label>
                                        <textarea class="form-control" id="special-notes" rows="3" 
                                                  placeholder="Any special requests, allergies, or notes for your stylist..."></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="prevStep(4)">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                                <button type="button" class="btn btn-primary-custom" onclick="nextStep(6)" id="step-5-next">
                                    Next: Review & Confirm <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 6: Confirmation -->
                    <div class="booking-step" id="step-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h4 class="mb-0"><i class="fas fa-check me-2"></i>Review & Confirm</h4>
                            </div>
                            <div class="card-body">
                                <div id="booking-summary">
                                    <!-- Booking summary will be displayed here -->
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="prevStep(5)">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                                <button type="button" class="btn btn-success btn-lg" onclick="confirmBooking()" id="confirm-booking-btn">
                                    <i class="fas fa-check me-2"></i>Confirm Booking
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="summary-card card sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
                        </div>
                        <div class="card-body">
                            <div id="summary-services" class="mb-3">
                                <h6>Selected Services:</h6>
                                <p class="text-muted">No services selected</p>
                            </div>
                            <div id="summary-datetime" class="mb-3" style="display: none;">
                                <h6>Date & Time:</h6>
                                <p id="summary-datetime-text" class="text-muted"></p>
                            </div>
                            <div id="summary-stylist" class="mb-3" style="display: none;">
                                <h6>Stylist:</h6>
                                <p id="summary-stylist-text" class="text-muted"></p>
                            </div>
                            <div id="summary-duration" class="mb-3" style="display: none;">
                                <h6>Total Duration:</h6>
                                <p id="summary-duration-text" class="text-muted"></p>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>Total Price:</h6>
                                <h4 class="text-primary mb-0" id="summary-total">$0.00</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Processing your booking...</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global booking data
        let bookingData = {
            services: [],
            date: null,
            time: null,
            stylist: null,
            customer: {}
        };
        
        let currentStep = 1;
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            generateCalendar();
            
            // Form validation
            document.getElementById('customer-form').addEventListener('input', validateCustomerForm);
        });
        
        // Step navigation
        function nextStep(step) {
            if (validateCurrentStep()) {
                document.getElementById(`step-${currentStep}`).classList.remove('active');
                document.getElementById(`step-${currentStep}-indicator`).classList.remove('active');
                document.getElementById(`step-${currentStep}-indicator`).classList.add('completed');
                
                currentStep = step;
                document.getElementById(`step-${step}`).classList.add('active');
                document.getElementById(`step-${step}-indicator`).classList.add('active');
                
                // Load data for new step
                if (step === 3) loadTimeSlots();
                if (step === 4) loadStylists();
                if (step === 6) generateBookingSummary();
                
                // Scroll to top
                window.scrollTo({top: 0, behavior: 'smooth'});
            }
        }
        
        function prevStep(step) {
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`step-${currentStep}-indicator`).classList.remove('active');
            
            currentStep = step;
            document.getElementById(`step-${step}`).classList.add('active');
            document.getElementById(`step-${step}-indicator`).classList.add('active');
            document.getElementById(`step-${step}-indicator`).classList.remove('completed');
            
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        
        // Step validation
        function validateCurrentStep() {
            switch(currentStep) {
                case 1:
                    return bookingData.services.length > 0;
                case 2:
                    return bookingData.date !== null;
                case 3:
                    return bookingData.time !== null;
                case 4:
                    return bookingData.stylist !== null;
                case 5:
                    return validateCustomerForm();
                default:
                    return true;
            }
        }
        
        // Service selection functions
        function selectCategory(categoryId, categoryName) {
            // Update category button states
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            event.target.classList.remove('btn-outline-primary');
            event.target.classList.add('btn-primary');
            
            loadServices(categoryId, categoryName);
        }
        
        async function loadServices(categoryId, categoryName) {
            document.getElementById('services-loading').style.display = 'block';
            document.getElementById('services-container').style.display = 'none';
            
            try {
                const response = await fetch('api/get-services.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({category_id: categoryId})
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayServices(data.data);
                } else {
                    showAlert('error', 'Failed to load services: ' + data.message);
                }
            } catch (error) {
                showAlert('error', 'Error loading services. Please try again.');
                console.error('Error:', error);
            } finally {
                document.getElementById('services-loading').style.display = 'none';
            }
        }
        
        function displayServices(data) {
            const container = document.getElementById('services-list');
            let html = '';
            
            if (data.subcategories && data.subcategories.length > 0) {
                data.subcategories.forEach(subcategory => {
                    if (subcategory.services && subcategory.services.length > 0) {
                        html += `<div class="col-12 mb-3"><h6 class="text-primary">${subcategory.name}</h6></div>`;
                        
                        subcategory.services.forEach(service => {
                            const isSelected = bookingData.services.some(s => s.id == service.id);
                            html += `
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card ${isSelected ? 'selected' : ''}" 
                                         onclick="toggleService(${service.id}, '${service.name}', ${service.price}, ${service.duration})">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="card-title mb-1">${service.name}</h6>
                                                    ${service.description ? `<p class="card-text small text-muted mb-1">${service.description}</p>` : ''}
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>${service.duration} min
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="fw-bold text-primary">$${parseFloat(service.price).toFixed(2)}</span>
                                                    <div class="mt-1">
                                                        <i class="fas fa-check-circle text-success" style="display: ${isSelected ? 'inline' : 'none'}"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    }
                });
            }
            
            container.innerHTML = html;
            document.getElementById('services-container').style.display = 'block';
        }
        
        function toggleService(id, name, price, duration) {
            const existingIndex = bookingData.services.findIndex(s => s.id == id);
            
            if (existingIndex > -1) {
                bookingData.services.splice(existingIndex, 1);
            } else {
                if (bookingData.services.length < <?php echo MAX_SERVICES_PER_BOOKING; ?>) {
                    bookingData.services.push({id, name, price, duration});
                } else {
                    showAlert('warning', 'Maximum <?php echo MAX_SERVICES_PER_BOOKING; ?> services can be selected');
                    return;
                }
            }
            
            updateServiceDisplay();
            updateSummary();
            
            // Update next button state
            document.getElementById('step-1-next').disabled = bookingData.services.length === 0;
        }
        
        function updateServiceDisplay() {
            document.querySelectorAll('.service-card').forEach(card => {
                const serviceId = card.getAttribute('onclick').match(/toggleService\((\d+)/)[1];
                const isSelected = bookingData.services.some(s => s.id == serviceId);
                
                card.classList.toggle('selected', isSelected);
                const checkIcon = card.querySelector('.fa-check-circle');
                if (checkIcon) {
                    checkIcon.style.display = isSelected ? 'inline' : 'none';
                }
            });
        }
        
        // Calendar functions
        function generateCalendar() {
            const daysContainer = document.getElementById('calendar-days');
            const monthYearDisplay = document.getElementById('calendar-month-year');
            
            const months = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
            
            monthYearDisplay.textContent = `${months[currentMonth]} ${currentYear}`;
            
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const today = new Date();
            
            let html = '';
            
            // Empty cells for days before month starts
            for (let i = 0; i < firstDay; i++) {
                html += '<div class="calendar-day disabled"></div>';
            }
            
            // Days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentYear, currentMonth, day);
                const dateStr = date.toISOString().split('T')[0];
                const isPast = date < today.setHours(0,0,0,0);
                const isSelected = bookingData.date === dateStr;
                
                html += `
                    <div class="calendar-day ${isPast ? 'disabled' : ''} ${isSelected ? 'selected' : ''}" 
                         onclick="${isPast ? '' : `selectDate('${dateStr}')`}">
                        ${day}
                    </div>
                `;
            }
            
            daysContainer.innerHTML = html;
        }
        
        function changeMonth(direction) {
            currentMonth += direction;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            } else if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            generateCalendar();
        }
        
        async function selectDate(dateStr) {
            bookingData.date = dateStr;
            
            // Update calendar display
            document.querySelectorAll('.calendar-day').forEach(day => {
                day.classList.remove('selected');
            });
            event.target.classList.add('selected');
            
            // Update date display
            const date = new Date(dateStr);
            document.getElementById('selected-date-display').textContent = 
                date.toLocaleDateString('en-US', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'});
            
            // Load salon hours for selected date
            await loadSalonHours(dateStr);
            
            // Enable next button
            document.getElementById('step-2-next').disabled = false;
            
            updateSummary();
        }
        
        async function loadSalonHours(dateStr) {
            try {
                const response = await fetch('api/get-salon-hours.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({date: dateStr})
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const hours = data.data;
                    if (hours.is_open) {
                        document.getElementById('salon-hours-display').innerHTML = 
                            `<span class="text-success">Open: ${formatTime(hours.opening_time)} - ${formatTime(hours.closing_time)}</span>`;
                    } else {
                        document.getElementById('salon-hours-display').innerHTML = 
                            '<span class="text-danger">Closed</span>';
                    }
                }
            } catch (error) {
                console.error('Error loading salon hours:', error);
            }
        }
        
        // Time slot functions
        async function loadTimeSlots() {
            if (!bookingData.date || bookingData.services.length === 0) return;
            
            document.getElementById('time-loading').style.display = 'block';
            document.getElementById('selected-date-time').textContent = 
                new Date(bookingData.date).toLocaleDateString('en-US', {weekday: 'long', month: 'long', day: 'numeric'});
            
            try {
                const response = await fetch('api/get-time-slots.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        date: bookingData.date,
                        services: bookingData.services.map(s => s.id)
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayTimeSlots(data.data);
                } else {
                    showAlert('error', 'Failed to load time slots: ' + data.message);
                }
            } catch (error) {
                showAlert('error', 'Error loading time slots. Please try again.');
                console.error('Error:', error);
            } finally {
                document.getElementById('time-loading').style.display = 'none';
            }
        }
        
        function displayTimeSlots(slots) {
            const container = document.getElementById('time-slots-container');
            let html = '';
            
            if (slots.length === 0) {
                html = '<div class="col-12 text-center"><p class="text-muted">No available time slots for this date.</p></div>';
            } else {
                slots.forEach(slot => {
                    html += `
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="time-slot card text-center p-3" onclick="selectTime('${slot.time}')">
                                <strong>${formatTime(slot.time)}</strong>
                            </div>
                        </div>
                    `;
                });
            }
            
            container.innerHTML = html;
        }
        
        function selectTime(time) {
            bookingData.time = time;
            
            // Update time slot display
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            // Enable next button
            document.getElementById('step-3-next').disabled = false;
            
            updateSummary();
        }
        
        // Stylist functions
        async function loadStylists() {
            if (bookingData.services.length === 0) return;
            
            document.getElementById('stylists-loading').style.display = 'block';
            
            try {
                const response = await fetch('api/get-stylists.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        services: bookingData.services.map(s => s.id),
                        date: bookingData.date,
                        time: bookingData.time
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayStylists(data.data);
                } else {
                    showAlert('error', 'Failed to load stylists: ' + data.message);
                }
            } catch (error) {
                showAlert('error', 'Error loading stylists. Please try again.');
                console.error('Error:', error);
            } finally {
                document.getElementById('stylists-loading').style.display = 'none';
            }
        }
        
        function displayStylists(stylists) {
            const container = document.getElementById('stylists-container');
            let html = '';
            
            if (stylists.length === 0) {
                html = '<div class="col-12 text-center"><p class="text-muted">No stylists available for your selected services and time.</p></div>';
            } else {
                stylists.forEach(stylist => {
                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="card service-card" onclick="selectStylist(${stylist.id}, '${stylist.name}')">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-user-circle fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title">${stylist.name}</h5>
                                    <p class="card-text text-muted">Professional Stylist</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            container.innerHTML = html;
        }
        
        function selectStylist(id, name) {
            bookingData.stylist = {id, name};
            
            // Update stylist display
            document.querySelectorAll('#stylists-container .service-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            // Enable next button
            document.getElementById('step-4-next').disabled = false;
            
            updateSummary();
        }
        
        // Customer form validation
        function validateCustomerForm() {
            const name = document.getElementById('customer-name').value.trim();
            const phone = document.getElementById('customer-phone').value.trim();
            const email = document.getElementById('customer-email').value.trim();
            
            const isValid = name && phone && email && validateEmail(email) && validatePhone(phone);
            
            if (isValid) {
                bookingData.customer = {
                    name,
                    phone,
                    email,
                    notes: document.getElementById('special-notes').value.trim()
                };
            }
            
            document.getElementById('step-5-next').disabled = !isValid;
            return isValid;
        }
        
        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
        
        function validatePhone(phone) {
            return /^\d{10,}$/.test(phone.replace(/\D/g, ''));
        }
        
        // Summary functions
        function updateSummary() {
            // Services
            let servicesHtml = '';
            if (bookingData.services.length > 0) {
                servicesHtml = '<ul class="list-unstyled mb-0">';
                bookingData.services.forEach(service => {
                    servicesHtml += `<li class="d-flex justify-content-between">
                        <span>${service.name}</span>
                        <span>$${parseFloat(service.price).toFixed(2)}</span>
                    </li>`;
                });
                servicesHtml += '</ul>';
            } else {
                servicesHtml = '<p class="text-muted">No services selected</p>';
            }
            document.getElementById('summary-services').innerHTML = '<h6>Selected Services:</h6>' + servicesHtml;
            
            // Date & Time
            if (bookingData.date && bookingData.time) {
                const dateStr = new Date(bookingData.date).toLocaleDateString('en-US', {
                    weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
                });
                document.getElementById('summary-datetime-text').textContent = `${dateStr} at ${formatTime(bookingData.time)}`;
                document.getElementById('summary-datetime').style.display = 'block';
            }
            
            // Stylist
            if (bookingData.stylist) {
                document.getElementById('summary-stylist-text').textContent = bookingData.stylist.name;
                document.getElementById('summary-stylist').style.display = 'block';
            }
            
            // Duration
            if (bookingData.services.length > 0) {
                const totalDuration = bookingData.services.reduce((sum, service) => sum + parseInt(service.duration), 0);
                document.getElementById('summary-duration-text').textContent = `${totalDuration} minutes`;
                document.getElementById('summary-duration').style.display = 'block';
            }
            
            // Total price
            const totalPrice = bookingData.services.reduce((sum, service) => sum + parseFloat(service.price), 0);
            document.getElementById('summary-total').textContent = `$${totalPrice.toFixed(2)}`;
        }
        
        function generateBookingSummary() {
            const container = document.getElementById('booking-summary');
            
            let html = `
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6><i class="fas fa-list me-2"></i>Selected Services</h6>
                        <div class="card">
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
            `;
            
            bookingData.services.forEach(service => {
                html += `
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <div>
                            <strong>${service.name}</strong><br>
                            <small class="text-muted">${service.duration} minutes</small>
                        </div>
                        <span class="fw-bold">$${parseFloat(service.price).toFixed(2)}</span>
                    </li>
                `;
            });
            
            const totalPrice = bookingData.services.reduce((sum, service) => sum + parseFloat(service.price), 0);
            const totalDuration = bookingData.services.reduce((sum, service) => sum + parseInt(service.duration), 0);
            
            html += `
                                </ul>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong class="text-primary">$${totalPrice.toFixed(2)}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <h6><i class="fas fa-calendar me-2"></i>Appointment Details</h6>
                        <div class="card">
                            <div class="card-body">
                                <p><strong>Date:</strong> ${new Date(bookingData.date).toLocaleDateString('en-US', {
                                    weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
                                })}</p>
                                <p><strong>Time:</strong> ${formatTime(bookingData.time)}</p>
                                <p><strong>Duration:</strong> ${totalDuration} minutes</p>
                                <p><strong>Stylist:</strong> ${bookingData.stylist.name}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <h6><i class="fas fa-user me-2"></i>Customer Information</h6>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> ${bookingData.customer.name}</p>
                                        <p><strong>Phone:</strong> ${bookingData.customer.phone}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> ${bookingData.customer.email}</p>
                                    </div>
                                </div>
                                ${bookingData.customer.notes ? `
                                    <div class="mt-3">
                                        <strong>Special Notes:</strong>
                                        <p class="mt-2 p-3 bg-light rounded">${bookingData.customer.notes}</p>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
        }
        
        // Booking confirmation
        async function confirmBooking() {
            document.getElementById('loading-overlay').style.display = 'flex';
            document.getElementById('confirm-booking-btn').disabled = true;
            
            try {
                const response = await fetch('api/create-booking.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(bookingData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect to success page
                    window.location.href = `booking-success.php?id=${data.data.booking_id}`;
                } else {
                    showAlert('error', 'Booking failed: ' + data.message);
                    document.getElementById('confirm-booking-btn').disabled = false;
                }
            } catch (error) {
                showAlert('error', 'Error creating booking. Please try again.');
                console.error('Error:', error);
                document.getElementById('confirm-booking-btn').disabled = false;
            } finally {
                document.getElementById('loading-overlay').style.display = 'none';
            }
        }
        
        // Utility functions
        function formatTime(time) {
            return new Date('2000-01-01 ' + time).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }
        
        function showAlert(type, message) {
            const alertClass = type === 'error' ? 'alert-danger' : 
                              type === 'warning' ? 'alert-warning' : 'alert-info';
            
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insert at top of booking container
            const container = document.querySelector('.booking-container');
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>
