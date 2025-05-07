
# 💇‍♀️ Salon Appointment Booking System

A full-stack web application that allows salon clients to book appointments online by selecting services, time slots, and stylists. Built using **HTML, CSS, Bootstrap, JavaScript, jQuery, PHP, and MySQL**.

---

## 📌 Features

- 🧾 Service selection by category (Hair, Skin, etc.)
- 🧠 Dynamic pricing and real-time duration updates
- 📅 Calendar-based time slot booking (30-min intervals)
- 👤 Customer details form with validations
- 📧 Email confirmations to customer and admin
- 🛠 Admin panel to manage and filter bookings
- 🔒 Secure data handling with input validation
- 📲 Mobile responsive design with Bootstrap

---

## 🛠 Tech Stack

| Frontend             | Backend       | Database     |
|----------------------|---------------|--------------|
| HTML5                | PHP 8+        | MySQL        |
| CSS3 / Bootstrap 5   | PHPMailer     | phpMyAdmin   |
| JavaScript / jQuery  | AJAX          |              |

---

## 📁 Folder Structure

```
salon-booking-system/
│
├── css/
│   └── style.css
├── js/
│   └── main.js
├── includes/
│   └── db.php
│   └── functions.php
├── templates/
│   └── header.php
│   └── footer.php
├── admin/
│   └── dashboard.php
├── index.php
├── booking.php
├── summary.php
└── README.md
```

---

## 🚀 How to Run Locally

1. Install **XAMPP** or any local PHP server.
2. Clone the repository:
   ```bash
   git clone https://github.com/mehulbhargavdotca/salon-booking-system.git
   ```
3. Move the project folder into `htdocs/` (XAMPP users).
4. Start Apache and MySQL via XAMPP.
5. Import the `salon_booking.sql` file into **phpMyAdmin**.
6. Open your browser and go to:
   ```
   http://localhost/salon-booking-system/
   ```

---

## 🛡 Security & Best Practices

- Input validation (client-side and server-side)
- `.gitignore` to exclude sensitive files
- PHPMailer with SMTP for secure emails

---

## 📧 Email Setup (PHPMailer)

1. Open `/includes/mail.php`
2. Add your SMTP configuration:
   ```php
   $mail->isSMTP();
   $mail->Host = 'smtp.example.com';
   $mail->SMTPAuth = true;
   $mail->Username = 'your@email.com';
   $mail->Password = 'yourpassword';
   $mail->SMTPSecure = 'tls';
   $mail->Port = 587;
   ```

---

## 👨‍💻 Author

**Mehul Bhargav**  
📧 Email: mehulbhargav.div@gmail.com  
🌐 GitHub: [@mehulbhargavdotca](https://github.com/mehulbhargavdotca)  
🔗 LinkedIn: [linkedin.com/in/mrmehulbhargav](https://linkedin.com/in/mrmehulbhargav)

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

## 🙌 Contributing

Pull requests are welcome.  
For major changes, please open an issue first to discuss what you would like to change.
