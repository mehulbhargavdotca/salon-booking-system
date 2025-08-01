-- Salon Booking System Database Schema
-- Created for comprehensive salon management and booking system

CREATE DATABASE IF NOT EXISTS salon_booking;
USE salon_booking;

-- Main Categories Table
CREATE TABLE main_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subcategories Table
CREATE TABLE subcategories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    main_category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (main_category_id) REFERENCES main_categories(id) ON DELETE CASCADE
);

-- Services Table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subcategory_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE CASCADE
);

-- Salon Settings Table
CREATE TABLE salon_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Operating Hours Table
CREATE TABLE operating_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_name ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL UNIQUE,
    opening_time TIME DEFAULT NULL,
    closing_time TIME DEFAULT NULL,
    is_open BOOLEAN DEFAULT TRUE
);

-- Staff Table
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Staff Services Junction Table
CREATE TABLE staff_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    service_id INT NOT NULL,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_staff_service (staff_id, service_id)
);

-- Staff Availability Table
CREATE TABLE staff_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    day_name ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
    UNIQUE KEY unique_staff_day (staff_id, day_name)
);

-- Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    staff_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    special_notes TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(id)
);

-- Booking Services Junction Table
CREATE TABLE booking_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    service_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Insert Default Operating Hours
INSERT INTO operating_hours (day_name, opening_time, closing_time, is_open) VALUES
('Monday', '10:00:00', '20:00:00', TRUE),
('Tuesday', '10:00:00', '20:00:00', TRUE),
('Wednesday', '10:00:00', '20:00:00', TRUE),
('Thursday', '10:00:00', '20:00:00', TRUE),
('Friday', '10:00:00', '20:00:00', TRUE),
('Saturday', '10:00:00', '20:00:00', TRUE),
('Sunday', '10:00:00', '19:00:00', TRUE);

-- Insert Default Salon Settings
INSERT INTO salon_settings (setting_name, setting_value) VALUES
('buffer_time', '30'),
('booking_advance_days', '30'),
('salon_name', 'Elegant Salon & Spa'),
('salon_phone', '+1-555-0123'),
('salon_email', 'info@elegantsalon.com');

-- Insert Sample Data for Testing
INSERT INTO main_categories (name) VALUES
('Hair Services'),
('Skin Care'),
('Nail Services'),
('Spa Treatments');

INSERT INTO subcategories (main_category_id, name) VALUES
(1, 'Hair Cutting'),
(1, 'Hair Coloring'),
(1, 'Hair Styling'),
(2, 'Facial Treatments'),
(2, 'Anti-Aging'),
(3, 'Manicure'),
(3, 'Pedicure'),
(4, 'Massage Therapy'),
(4, 'Body Treatments');

INSERT INTO services (subcategory_id, name, price, duration, description) VALUES
-- Hair Services
(1, 'Women\'s Haircut', 65.00, 60, 'Professional haircut with wash and blow-dry'),
(1, 'Men\'s Haircut', 35.00, 30, 'Classic men\'s cut with styling'),
(1, 'Children\'s Haircut', 25.00, 30, 'Haircut for children under 12'),
(2, 'Full Color', 120.00, 120, 'Complete hair color transformation'),
(2, 'Root Touch-up', 75.00, 90, 'Color refresh for roots'),
(2, 'Highlights', 150.00, 150, 'Partial or full highlights'),
(3, 'Blowout', 45.00, 45, 'Professional styling and blowout'),
(3, 'Updo', 85.00, 75, 'Special occasion updo styling'),
-- Skin Care
(4, 'Classic Facial', 80.00, 60, 'Deep cleansing and moisturizing facial'),
(4, 'Anti-Aging Facial', 120.00, 90, 'Advanced anti-aging treatment'),
(5, 'Microdermabrasion', 100.00, 60, 'Skin resurfacing treatment'),
(5, 'Chemical Peel', 150.00, 75, 'Professional chemical exfoliation'),
-- Nail Services
(6, 'Classic Manicure', 35.00, 45, 'Traditional manicure with polish'),
(6, 'Gel Manicure', 45.00, 60, 'Long-lasting gel polish manicure'),
(7, 'Classic Pedicure', 45.00, 60, 'Relaxing foot care and polish'),
(7, 'Spa Pedicure', 65.00, 75, 'Luxury pedicure with massage'),
-- Spa Treatments
(8, 'Swedish Massage', 90.00, 60, 'Relaxing full-body massage'),
(8, 'Deep Tissue Massage', 110.00, 60, 'Therapeutic deep muscle massage'),
(9, 'Body Wrap', 120.00, 90, 'Detoxifying body treatment'),
(9, 'Salt Scrub', 80.00, 60, 'Exfoliating body scrub treatment');

INSERT INTO staff (name, email, phone) VALUES
('Sarah Johnson', 'sarah@elegantsalon.com', '555-0101'),
('Michael Chen', 'michael@elegantsalon.com', '555-0102'),
('Emma Rodriguez', 'emma@elegantsalon.com', '555-0103'),
('David Kim', 'david@elegantsalon.com', '555-0104');

-- Assign services to staff (Hair stylist, Colorist, Esthetician, Massage therapist)
INSERT INTO staff_services (staff_id, service_id) VALUES
-- Sarah - Hair Services
(1, 1), (1, 2), (1, 3), (1, 7), (1, 8),
-- Michael - Hair Coloring
(2, 4), (2, 5), (2, 6),
-- Emma - Skin and Nail Services
(3, 9), (3, 10), (3, 11), (3, 12), (3, 13), (3, 14), (3, 15), (3, 16),
-- David - Spa Services
(4, 17), (4, 18), (4, 19), (4, 20);

-- Set staff availability (all staff work Monday-Saturday)
INSERT INTO staff_availability (staff_id, day_name, start_time, end_time) VALUES
-- Sarah
(1, 'Monday', '10:00:00', '18:00:00'),
(1, 'Tuesday', '10:00:00', '18:00:00'),
(1, 'Wednesday', '10:00:00', '18:00:00'),
(1, 'Thursday', '10:00:00', '18:00:00'),
(1, 'Friday', '10:00:00', '18:00:00'),
(1, 'Saturday', '09:00:00', '17:00:00'),
-- Michael
(2, 'Tuesday', '11:00:00', '19:00:00'),
(2, 'Wednesday', '11:00:00', '19:00:00'),
(2, 'Thursday', '11:00:00', '19:00:00'),
(2, 'Friday', '11:00:00', '19:00:00'),
(2, 'Saturday', '10:00:00', '18:00:00'),
(2, 'Sunday', '10:00:00', '16:00:00'),
-- Emma
(3, 'Monday', '09:00:00', '17:00:00'),
(3, 'Tuesday', '09:00:00', '17:00:00'),
(3, 'Wednesday', '09:00:00', '17:00:00'),
(3, 'Thursday', '09:00:00', '17:00:00'),
(3, 'Friday', '09:00:00', '17:00:00'),
(3, 'Saturday', '09:00:00', '15:00:00'),
-- David
(4, 'Monday', '12:00:00', '20:00:00'),
(4, 'Wednesday', '12:00:00', '20:00:00'),
(4, 'Friday', '12:00:00', '20:00:00'),
(4, 'Saturday', '10:00:00', '18:00:00'),
(4, 'Sunday', '11:00:00', '17:00:00');