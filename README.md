# Digital Identity Card System

## Run with XAMPP

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. In phpMyAdmin, import `database.sql`. It creates/uses the `Digital_identity` database and creates the required tables.
3. If your MySQL credentials are not the XAMPP defaults, update `DB_USER` and `DB_PASS` in `config.php`.
4. Open `http://localhost/Digital_identity/` to use the registration form.
5. Open `http://localhost/Digital_identity/setup_admin.php` once to create the first administrator. The password must be at least 12 characters. After setup, this page redirects to admin sign-in.
6. Sign in at `http://localhost/Digital_identity/admin/login.php` to review applications.

## How it works

- Applicants submit their name, student ID, email, phone, department, program, and an optional photo.
- Admins can review pending applications and approve or reject them. Approval creates a card number and a random verification code.
- Admins can edit any application from its review page, including correcting the program, and can replace or remove its photo. Corrections appear on the card and verification page.
- The admin dashboard provides a printable card. Its QR code links to `verify.php`.
- The public verification page displays the approved name, student ID, card number, department, program, and optional photo. Applicant contact details are not public.

The QR is generated in the browser using QRCode.js loaded from cdnjs, so the card-printing page needs an internet connection when it is opened. Student information is not sent to that library; the QR contains only the verification URL and opaque code.

For a real deployment, use HTTPS, set a strong non-default database password, configure backups, and add institution-specific identity checks and privacy requirements.
