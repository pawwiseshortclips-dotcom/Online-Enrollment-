# Enrollment System (local XAMPP)

This is a simple PHP/MySQL enrollment system with:
- Student enrollment form with file upload
- Server-side storage of enrollment records
- Receipt page with QR code linking to receipt
- Admin panel for viewing enrollments, managing courses, and exporting CSV
- Optional server-side PDF generation using Dompdf

Folder structure
- `index.php` — student form
- `submit.php` — form handler
- `receipt.php` — receipt display (public link via token)
- `receipt_pdf.php` — server-side PDF endpoint (optional Dompdf)
- `database.php` — DB connection (edit credentials if needed)
- `style.css` — site styles
- `uploads/` — uploaded payment proof images
- `receipts/` — generated QR images
- `admin/` — admin panel (login, dashboard, students, courses, export)
- `create_db.sql` — SQL to create DB and tables
- `admin/create_admin.php` — quick web script to create admin user (delete after use)

Setup (XAMPP on Windows)
1. Place this project inside your XAMPP `htdocs` folder (already done).
2. Start Apache + MySQL (via XAMPP control panel).
3. Create the database and tables:

   - Using phpMyAdmin or MySQL CLI, run `create_db.sql` or execute:

     ```sql
     CREATE DATABASE IF NOT EXISTS enrollment_db;
     USE enrollment_db;
     -- then run the table creation statements from create_db.sql
     ```

   - Alternatively, just submit the first form — `submit.php` will create the `enrollments` table automatically.

4. Create an admin user:
   - Open `http://localhost/Enrollment-system/admin/create_admin.php`, fill username & password, submit.
   - Immediately delete `admin/create_admin.php` after use for security.

5. Optional: enable server-side PDF generation
   - Install Composer (https://getcomposer.org/) if not installed.
   - From the project root (`C:\xampp\htdocs\Enrollment-system`) run in PowerShell:

     ```powershell
     cd 'C:\xampp\htdocs\Enrollment-system'
     composer require dompdf/dompdf
     ```

   - After installation `receipt_pdf.php` will generate PDF files for receipts.

6. Visit the site
   - Student form: `http://localhost/Enrollment-system/index.php`
   - Admin login: `http://localhost/Enrollment-system/admin/login.php`

Notes & Security
- The admin creation script is provided for convenience; remove it after creating an admin.
- For production use, secure uploads, sanitize inputs further, use HTTPS, and harden session handling.
- The QR code is generated using Google Charts API; to avoid external calls, consider using a PHP QR library.

If you want, I can install Dompdf and replace the Google QR generation with an in-project PHP library.
