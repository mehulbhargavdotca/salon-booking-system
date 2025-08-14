// Booking form JavaScript
let currentStep = 1;
let selectedService = null;
let selectedDate = null;
let selectedTime = null;
let selectedStylist = null;

// Navigation functions
function nextStep(step) {
    if (validateStep(currentStep)) {
        document.getElementById('step' + currentStep).style.display = 'none';
        currentStep = step;
        document.getElementById('step' + currentStep).style.display = 'block';
        
        // Load data for specific steps
        if (step === 2) {
            loadTimeSlots();
        } else if (step === 3) {
            loadStylists();
        } else if (step === 4) {
            updateBookingSummary();
        }
    }
}

function prevStep(step) {
    document.getElementById('step' + currentStep).style.display = 'none';
    currentStep = step;
    document.getElementById('step' + currentStep).style.display = 'block';
}

// Validation functions
function validateStep(step) {
    switch(step) {
        case 1:
            const serviceRadio = document.querySelector('input[name="service_id"]:checked');
            if (!serviceRadio) {
                alert('Please select a service');
                return false;
            }
            selectedService = {
                id: serviceRadio.value,
                name: serviceRadio.dataset.name,
                price: serviceRadio.dataset.price,
                duration: serviceRadio.dataset.duration
            };
            return true;
            
        case 2:
            const date = document.getElementById('appointment_date').value;
            const time = document.getElementById('selected_time').value;
            if (!date) {
                alert('Please select a date');
                return false;
            }
            if (!time) {
                alert('Please select a time');
                return false;
            }
            selectedDate = date;
            selectedTime = time;
            return true;
            
        case 3:
            const stylist = document.querySelector('input[name="stylist_id"]:checked');
            if (!stylist) {
                alert('Please select a stylist');
                return false;
            }
            selectedStylist = {
                id: stylist.value,
                name: stylist.dataset.name
            };
            document.getElementById('selected_stylist').value = stylist.value;
            return true;
            
        default:
            return true;
    }
}

// Load time slots based on selected date and service
function loadTimeSlots() {
    const date = document.getElementById('appointment_date').value;
    const serviceId = selectedService ? selectedService.id : null;
    
    if (!date || !serviceId) {
        document.getElementById('timeSlots').innerHTML = '<p class="text-muted">Please select a service and date</p>';
        return;
    }
    
    // Show loading
    document.getElementById('timeSlots').innerHTML = '<p class="text-muted">Loading available times...</p>';
    
    // AJAX call to get available time slots
    fetch('includes/get_time_slots.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `date=${date}&service_id=${serviceId}`
    })
    .then(response => response.json())
    .then(data => {
        const timeSlotsDiv = document.getElementById('timeSlots');
        if (data.success && data.slots.length > 0) {
            let slotsHtml = '';
            data.slots.forEach(slot => {
                slotsHtml += `
                    <button type="button" class="time-slot" onclick="selectTime('${slot}')">
                        ${formatTime(slot)}
                    </button>
                `;
            });
            timeSlotsDiv.innerHTML = slotsHtml;
        } else {
            timeSlotsDiv.innerHTML = '<p class="text-muted">No available times for this date</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('timeSlots').innerHTML = '<p class="text-danger">Error loading time slots</p>';
    });
}

// Select time slot
function selectTime(time) {
    // Remove previous selection
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    // Add selection to clicked slot
    event.target.classList.add('selected');
    
    // Store selected time
    document.getElementById('selected_time').value = time;
    selectedTime = time;
}

// Load available stylists
function loadStylists() {
    if (!selectedService || !selectedDate || !selectedTime) {
        document.getElementById('stylistOptions').innerHTML = '<p class="text-muted">Please complete previous steps first</p>';
        return;
    }
    
    // Show loading
    document.getElementById('stylistOptions').innerHTML = '<p class="text-muted">Loading available stylists...</p>';
    
    // AJAX call to get available stylists
    fetch('includes/get_stylists.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `service_id=${selectedService.id}&date=${selectedDate}&time=${selectedTime}`
    })
    .then(response => response.json())
    .then(data => {
        const stylistDiv = document.getElementById('stylistOptions');
        if (data.success && data.stylists.length > 0) {
            let stylistsHtml = '<div class="row">';
            data.stylists.forEach(stylist => {
                stylistsHtml += `
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="stylist_id" 
                                   value="${stylist.id}" id="stylist_${stylist.id}"
                                   data-name="${stylist.name}">
                            <label class="form-check-label w-100" for="stylist_${stylist.id}">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>${stylist.name}</h6>
                                        ${stylist.phone ? `<small class="text-muted">${stylist.phone}</small>` : ''}
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                `;
            });
            stylistsHtml += '</div>';
            stylistDiv.innerHTML = stylistsHtml;
        } else {
            stylistDiv.innerHTML = '<p class="text-muted">No stylists available for this time slot</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('stylistOptions').innerHTML = '<p class="text-danger">Error loading stylists</p>';
    });
}

// Update booking summary
function updateBookingSummary() {
    if (!selectedService || !selectedDate || !selectedTime || !selectedStylist) {
        return;
    }
    
    const summaryHtml = `
        <div class="row">
            <div class="col-6"><strong>Service:</strong></div>
            <div class="col-6">${selectedService.name}</div>
        </div>
        <div class="row">
            <div class="col-6"><strong>Price:</strong></div>
            <div class="col-6">$${parseFloat(selectedService.price).toFixed(2)}</div>
        </div>
        <div class="row">
            <div class="col-6"><strong>Duration:</strong></div>
            <div class="col-6">${selectedService.duration} minutes</div>
        </div>
        <div class="row">
            <div class="col-6"><strong>Date:</strong></div>
            <div class="col-6">${formatDate(selectedDate)}</div>
        </div>
        <div class="row">
            <div class="col-6"><strong>Time:</strong></div>
            <div class="col-6">${formatTime(selectedTime)}</div>
        </div>
        <div class="row">
            <div class="col-6"><strong>Stylist:</strong></div>
            <div class="col-6">${selectedStylist.name}</div>
        </div>
    `;
    
    document.getElementById('bookingSummary').innerHTML = summaryHtml;
}

// Utility functions
function formatTime(time) {
    const [hours, minutes] = time.split(':');
    const hour12 = hours % 12 || 12;
    const ampm = hours >= 12 ? 'PM' : 'AM';
    return `${hour12}:${minutes} ${ampm}`;
}

function formatDate(date) {
    return new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Date change listener
    const dateInput = document.getElementById('appointment_date');
    if (dateInput) {
        dateInput.addEventListener('change', loadTimeSlots);
    }
});