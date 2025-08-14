# Dena Beauty Salon - Booking System

A complete full-stack booking system for beauty salons built with PHP, MySQL, Bootstrap, and JavaScript.

## Features

### Admin Panel
- **Categories Management**: Add, edit, and delete service categories
- **Services Management**: Manage services with pricing, duration, and category assignment
- **Stylists Management**: Add stylists and assign them to specific services
- **Appointments Management**: View, filter, and manage customer appointments
- **Business Hours**: Configure salon operating hours for each day of the week
- **Buffer Time Settings**: Set appointment intervals (15, 30, 45, or 60 minutes)
- **Salon Information**: Update salon name, slogan, contact information
- **Admin Security**: Secure login with password hashing and session management

### Customer Interface
- **Price List**: Beautiful, responsive display of all services organized by category
- **Multi-Step Booking**: Intuitive 4-step booking process
  1. Service Selection
  2. Date & Time Selection
  3. Stylist Selection
  4. Customer Information
- **Real-time Availability**: AJAX-powered availability checking
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices

### Technical Features
- **Real-time Scheduling**: Prevents double-booking and shows only available time slots
- **Smart Time Management**: Considers service duration and buffer times
- **Database Integrity**: Proper foreign key relationships and data validation
- **Security**: SQL injection prevention, input sanitization, session management
- **Modern UI**: Bootstrap-based responsive design with custom styling

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP/WAMP/MAMP (for local development)

### Setup Instructions

1. **Clone or Download**
   ```bash
   git clone [repository-url]
   # OR download and extract the ZIP file
   ```

2. **Database Setup**
   - Create a new MySQL database named `dena_salon`
   - Import the provided SQL file:
   ```bash
   mysql -u root -p dena_salon < setup_database.sql
   ```
   - OR run the SQL commands from `setup_database.sql` in phpMyAdmin

3. **Configuration**
   - Update database connection settings in `/includes/db.php`:
   ```php
   $servername = "localhost";
   $username = "root";        // Your MySQL username
   $password = "";            // Your MySQL password
   $dbname = "dena_salon";
   ```

4. **File Permissions**
   - Ensure the web server has read/write permissions to the project directory
   - For Linux/Mac: `chmod -R 755 /path/to/project`

5. **Bootstrap Files**
   - The system expects Bootstrap files in `/Bootstrap/` directory
   - Download Bootstrap 5.x and place files as:
     - `/Bootstrap/css/bootstrap.min.css`
     - `/Bootstrap/js/bootstrap.bundle.min.js`

## Default Login Credentials

**Admin Panel Access:**
- URL: `http://yoursite.com/admin/dashboard.php` (will redirect to login)
- Username: `admin`
- Password: `admin123`

**Important:** Change the default password immediately after first login!

## File Structure

```
/
├── admin/                  # Admin panel files
│   ├── dashboard.php      # Main admin dashboard
│   └── pages/             # Admin page components
│       ├── overview.php   # Dashboard overview
│       ├── categories.php # Category management
│       ├── services.php   # Service management
│       ├── stylists.php   # Stylist management
│       ├── appointments.php # Appointment management
│       ├── business_hours.php # Business hours settings
│       └── settings.php   # System settings
├── auth/                  # Authentication
│   ├── login.php         # Admin login page
│   └── logout.php        # Logout functionality
├── includes/             # Core functionality
│   ├── db.php           # Database connection
│   ├── auth.php         # Authentication functions
│   ├── functions.php    # Helper functions
│   ├── get_time_slots.php # AJAX endpoint for time slots
│   └── get_stylists.php # AJAX endpoint for stylists
├── css/
│   └── style.css        # Custom styles
├── js/
│   ├── booking.js       # Booking form functionality
│   └── admin.js         # Admin panel functionality
├── Bootstrap/           # Bootstrap framework files
│   ├── css/
│   └── js/
├── index.php           # Customer price list page
├── booking.php         # Customer booking page
├── setup_database.sql  # Database setup file
└── README.md           # This file
```

## Usage Guide

### For Salon Owners (Admin)

1. **Initial Setup**
   - Login to admin panel
   - Update salon information in Settings
   - Configure business hours
   - Set appropriate buffer time between appointments

2. **Managing Categories**
   - Add main service categories (e.g., "Haircuts", "Facial Services")
   - Edit category names and add descriptive notes

3. **Managing Services**
   - Add services under appropriate categories
   - Set accurate pricing and duration
   - Services automatically appear on customer price list

4. **Managing Stylists**
   - Add stylist information (name, phone, email)
   - Assign services each stylist can perform
   - Only assigned stylists will be available for specific services

5. **Viewing Appointments**
   - Filter appointments by date and stylist
   - View revenue summaries and statistics
   - Delete appointments if needed

### For Customers

1. **Viewing Services**
   - Browse the complete price list organized by category
   - See service prices and duration

2. **Booking Appointments**
   - Click "Book Appointment" button
   - Follow the 4-step booking process:
     - Choose desired service
     - Select preferred date and available time
     - Choose from available stylists
     - Enter contact information
   - Receive booking confirmation

## Customization

### Styling
- Edit `/css/style.css` to customize colors, fonts, and layout
- Color scheme uses CSS custom properties (variables) for easy theming
- Responsive breakpoints are defined for mobile optimization

### Business Logic
- Modify buffer time options in `/admin/pages/business_hours.php`
- Adjust time slot generation logic in `/includes/functions.php`
- Customize booking validation in `/js/booking.js`

### Database Schema
- All tables use proper foreign key relationships
- Add custom fields by modifying table structures
- Maintain referential integrity when making changes

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` and `password_verify()`
- **SQL Injection Prevention**: All queries use prepared statements
- **Input Sanitization**: User input is sanitized before processing
- **Session Management**: Secure admin session handling
- **CSRF Protection**: Forms include action verification
- **Access Control**: Admin pages require authentication

## Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `/includes/db.php`
   - Ensure MySQL service is running
   - Verify database exists and is accessible

2. **Bootstrap Not Loading**
   - Ensure Bootstrap files are in correct `/Bootstrap/` directory
   - Check file paths in HTML headers
   - Verify file permissions

3. **AJAX Errors**
   - Check browser developer console for JavaScript errors
   - Ensure `/includes/get_*.php` files are accessible
   - Verify database connection in AJAX endpoints

4. **Time Slots Not Showing**
   - Check business hours are configured
   - Verify buffer time settings
   - Ensure stylists are assigned to services

5. **Login Issues**
   - Verify admin credentials in database
   - Check session configuration
   - Clear browser cookies/cache

## Support

For technical support or feature requests, please refer to the documentation or contact the development team.

## License

This project is proprietary software developed for Dena Beauty Salon. All rights reserved.
