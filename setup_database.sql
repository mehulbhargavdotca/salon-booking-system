CREATE DATABASE IF NOT EXISTS dena_salon;
USE dena_salon;

-- 0. admin_users
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 1. categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    note TEXT
);

-- 2. services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_in_minutes INT DEFAULT 60,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- 3. stylists
CREATE TABLE IF NOT EXISTS stylists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(100)
);

-- 4. stylist_services
CREATE TABLE IF NOT EXISTS stylist_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stylist_id INT NOT NULL,
    service_id INT NOT NULL,
    FOREIGN KEY (stylist_id) REFERENCES stylists(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- 5. appointments
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(30),
    service_id INT NOT NULL,
    stylist_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (stylist_id) REFERENCES stylists(id)
);

-- 6. business_hours
CREATE TABLE IF NOT EXISTS business_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_of_week VARCHAR(20) NOT NULL UNIQUE,
    open_time TIME NOT NULL,
    close_time TIME NOT NULL
);

-- 7. settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buffer_time_minutes INT NOT NULL
);

-- 8. salon_info
CREATE TABLE IF NOT EXISTS salon_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slogan VARCHAR(255),
    phone VARCHAR(30),
    email VARCHAR(100)
);

-- Seed categories
INSERT INTO categories (name) VALUES
('Women Haircuts & Style'),
('Hair Texture'),
('Laser Hair Removal'),
('HIFU Treatment'),
('Hair Extensions'),
('Skin Services'),
('Hair Treatments'),
('Bridal Services'),
('Color Services'),
('Men''s Services'),
('Brow & Lash'),
('Omber-Somber'),
('Body & Contouring'),
('Baby Haircuts (3 & Under)'),
('Highlight & Balayage');

-- Seed services (all from index.html)
INSERT INTO services (category_id, name, price, duration_in_minutes) VALUES
-- Women Haircuts & Style
(1, 'Women Short Hair Cut', 59, 45),
(1, 'Updo/Chignon', 89, 60),
(1, 'Trim', 45, 30),
(1, 'Braids', 150, 60),
(1, 'Blow Dry & Style (Medium Hair)', 45, 30),
(1, 'Bangs', 17, 15),
(1, 'Women''s Medium Hair Cut', 69, 45),
(1, 'Women''s Long Hair Cut', 79, 60),
-- Hair Texture
(2, 'Waving, Perming', 245, 90),
(2, 'Smoothing, Relaxing', 88, 60),
-- Laser Hair Removal
(3, 'Upper Body', 195, 60),
(3, 'Underarms', 55, 20),
(3, 'Full Legs', 145, 60),
(3, 'Half Legs', 79, 30),
(3, 'Brazilian', 69, 30),
(3, 'Bikini', 55, 20),
(3, 'Full Face', 55, 20),
(3, 'Full Body', 369, 120),
(3, 'Stomach', 53, 20),
(3, 'Side-Burns', 32, 15),
(3, 'Lip and Chin', 42, 15),
(3, 'Full Back', 59, 30),
(3, 'Full Arms', 68, 30),
(3, 'Ears', 43, 10),
(3, 'Chin', 35, 10),
(3, 'Chest', 49, 20),
(3, 'Cheeks', 49, 10),
(3, 'Buttocks', 63, 20),
(3, 'Front & Back Neck', 58, 20),
-- HIFU Treatment
(4, 'Thighs', 750, 90),
(4, 'Double Chin', 275, 30),
(4, 'Chin - Neck Fat Reduction', 165, 30),
(4, 'Belly Fat Reduction', 425, 60),
-- Hair Extensions
(5, 'Tape / Hidden Hair Collection', 80, 60),
(5, 'Keratin Fusions', 195, 60),
(5, 'Hair Extensions', 250, 60),
(5, 'Full Sets', 780, 120),
(5, 'Detox Wash, Cut & Style (Included)', 350, 60),
-- Skin Services
(6, 'Skin Rejuvenation', 270, 60),
(6, 'Microdermabrasion Facial', 128, 60),
(6, 'Mesotherapy (Skin Treatment)', 310, 60),
(6, 'Mesoneedling/Microneedling', 285, 60),
(6, 'Light Therapy', 89, 30),
(6, 'Hydro Facial', 175, 60),
(6, 'Face & Neck Massage', 58, 30),
(6, 'Deep Cleansing Facial', 125, 60),
(6, 'Customized Facials', 0, 60),
(6, 'Chemical-Free Peel', 112, 45),
(6, 'Chemical + Vitamin C Facial', 98, 45),
(6, 'Cellulite & Reduction Treatment', 290, 60),
(6, 'Acne Treatment', 165, 60),
(6, 'Acne Facial – Back', 125, 60),
(6, 'Anti-Aging Facial', 198, 60),
-- Hair Treatments
(7, 'Scalp Treatment', 60, 30),
(7, 'PH-Bonder', 40, 20),
(7, 'Olaplex', 40, 20),
(7, 'Micromist Therapy', 125, 45),
(7, 'Mesotherapy (Hair Treatment)', 295, 60),
(7, 'Mesoneedling/Microneedling', 385, 60),
(7, 'Keratin Hair Treatment', 265, 90),
(7, 'Hair Loss Treatments', 125, 60),
(7, 'Hair Protein Treatment', 220, 60),
(7, 'Conditioning Treatment', 55, 30),
(7, 'Botox Hair Treatment', 275, 90),
-- Bridal Services
(8, 'Pre-Wedding Makeup (Mobile)', 275, 60),
(8, 'Pre-Wedding Hair (Mobile)', 275, 60),
(8, 'Makeup Classes', 115, 60),
(8, 'Makeup Trial', 185, 60),
(8, 'Evening Makeup', 150, 60),
(8, 'Cultural Bride Makeup (Lashes Included)', 300, 90),
(8, 'Cultural Bride Hair', 300, 90),
(8, 'Bride Party Makeup', 135, 60),
(8, 'Bride Party Hair', 135, 60),
(8, 'Bride Hair', 250, 90),
-- Color Services
(9, 'Root Touchup For Short Hair( No Bleach )', 89, 30),
(9, 'Root Touchup for Medium Hair ( No Bleach )', 85, 30),
(9, 'Root Touchup for Long Hair ( No Bleach )', 98, 30),
(9, 'Maintenance Toning', 78, 30),
(9, 'Full Colour Hair (Medium, No Bleach)', 98, 60),
(9, 'Full Colour Hair (Long, No Bleach)', 125, 60),
(9, 'Hair Colour With Bleach', 215, 90),
(9, 'Root Touchup with Bleach', 215, 60),
-- Men's Services
(10, 'Medium & Short Haircut', 37, 30),
(10, 'Hair Short Colour', 89, 30),
(10, 'Beard Trim', 17, 15),
(10, 'Beard Colour', 55, 20),
-- Brow & Lash
(11, 'Full Face Waxing ( Includes brows )', 47, 30),
(11, 'Full Face Threading ( Includes brows )', 55, 30),
(11, 'Brow Waxing, Threading, Shaping', 21, 15),
(11, 'Lip/Chin', 10, 10),
-- Omber-Somber
(12, 'One Process', 172, 60),
(12, 'Multi Process', 320, 90),
(12, 'Colour Correction', 0, 60),
-- Body & Contouring
(13, 'Neck', 49, 20),
(13, 'Inner & Outer Thighs', 290, 60),
(13, 'Full Body', 980, 120),
(13, 'Flanks', 270, 60),
(13, 'Buttocks', 325, 60),
(13, 'Abdomen', 285, 60),
(13, 'Full Body (Deluxe)', 980, 150),
-- Baby Haircuts (3 & Under)
(14, 'Girls Haircut', 32, 20),
(14, 'Boys Haircut', 32, 20),
-- Highlight & Balayage
(15, 'Full Highlights (Medium)', 220, 90),
(15, 'Half Highlights', 165, 60),
(15, 'Balayage Full (Includes Glaze)', 245, 120),
(15, 'Balayage Half (Includes Glaze)', 220, 90),
(15, 'Balayage T-Zone (Hairline/Parting)', 140, 60),
(15, 'Root Touchup With Bleach', 185, 60),
(15, 'Partial Head', 140, 60),
(15, 'Full Highlights (Long)', 295, 120);

-- Seed salon_info
INSERT INTO salon_info (name, slogan, phone, email) VALUES
('Dena Beauty Salon', 'Your Trusted Destination For Advanced Beauty Solutions.', '123-456-7890', 'info@denabeautysalon.ca');

-- Seed settings
INSERT INTO settings (buffer_time_minutes) VALUES (15);

-- Seed business_hours
INSERT INTO business_hours (day_of_week, open_time, close_time) VALUES
('Monday', '09:00:00', '19:00:00'),
('Tuesday', '09:00:00', '19:00:00'),
('Wednesday', '09:00:00', '19:00:00'),
('Thursday', '09:00:00', '19:00:00'),
('Friday', '09:00:00', '19:00:00'),
('Saturday', '09:00:00', '17:00:00'),
('Sunday', '10:00:00', '16:00:00');