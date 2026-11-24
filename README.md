# ADISOMA Solution Recruitment Website (PHP 8 + MySQL)

## XAMPP (Windows) setup
1. Install XAMPP (PHP 8+). Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy the `adisoma` folder to `C:\xampp\htdocs\adisoma`.
3. Open http://localhost/phpmyadmin, click **Import**, choose `database/adisoma.sql`, click **Go**. This creates the `adisoma` database, tables and sample data.
4. If your MySQL has a password or the folder name differs, edit `config/database.php` (DB_USER, DB_PASS, BASE_URL).
5. Open http://localhost/adisoma/ for the website.
6. Open http://localhost/adisoma/admin/login.php. On first visit you will be asked to **create the admin account** (password 8+ characters). No default password is shipped.

## Customise
- Company address/phone/email/social links: `CO` array in `config/database.php` (placeholders now).
- Colors: CSS variables at the top of `assets/css/style.css`.
- Services: Admin > Services. Jobs: Admin > Jobs (draft/publish/edit/delete).
- Sample stats and About text: edit `index.php`, `about.php`.

## Security notes
PDO prepared statements, CSRF tokens, output escaping, password hashing, honeypot on public forms, resumes stored under random names in a deny-all folder and served only to logged-in admins via `admin/download.php`. For live hosting use HTTPS, set a DB password, and keep `display_errors` off.

## Not included
Email notifications, pagination, a rich text editor, and an editor for About/Home text from the admin panel.
