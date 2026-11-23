# RescueBite — Installation & Deployment Guide

## 1. Prerequisites
- **PHP:** 8.2+ (with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` extensions)
- **Composer:** 2.x
- **MySQL:** 8.x (via XAMPP or Laragon)
- **Node.js & npm:** Node 20+ LTS

## 2. Local Setup (macOS + XAMPP)
1. Start **Apache** and **MySQL** in XAMPP (`manager-osx`).
2. Open `http://localhost/phpmyadmin` and create a database named `rescuebite` (`utf8mb4_unicode_ci`).
3. Ensure XAMPP PHP is in your PATH (`~/.zshrc`):
   ```bash
   export PATH="/Applications/XAMPP/xamppfiles/bin:$PATH"
   ```
4. Configure `.env` with MySQL credentials (include `DB_SOCKET=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock` on macOS XAMPP if `127.0.0.1` refuses connections).
5. Install dependencies and initialize the database:
   ```bash
   composer install
   npm install
   npm run build
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan storage:link
   php artisan serve
   ```
6. Visit `http://127.0.0.1:8000`.

## 3. Local Setup (Windows + XAMPP / Laragon)
1. Start Apache and MySQL in XAMPP Control Panel or Laragon.
2. Create the `rescuebite` MySQL database in phpMyAdmin.
3. Copy `.env.example.rescuebite` to `.env` and set `DB_DATABASE=rescuebite`, `DB_USERNAME=root`, `DB_PASSWORD=`.
4. Run `composer install`, `npm install`, `npm run build`, `php artisan key:generate`, `php artisan migrate:fresh --seed`, `php artisan storage:link`, and `php artisan serve`.

## 4. Configuring the Automatic Reservation Expiry Scheduler
RescueBite schedules `php artisan reservations:expire` every minute in `routes/console.php`.
- **Local Development:** Run `php artisan schedule:work` in a separate terminal tab.
- **macOS / Linux Cron (`crontab -e`):**
  ```cron
  * * * * * cd /path/to/rescuebite && php artisan schedule:run >> /dev/null 2>&1
  ```
- **Windows Task Scheduler:** Create a task repeating every 1 minute executing `php.exe` with arguments `artisan schedule:run` in the `rescuebite` directory.

## 5. Seeded Demo Accounts (Password: `Password123!`)
- **Admin:** `admin@rescuebite.local`
- **Seller 1:** `seller1@rescuebite.local`
- **Seller 2:** `seller2@rescuebite.local`
- **Buyer 1:** `buyer1@rescuebite.local`
- **Buyer 2:** `buyer2@rescuebite.local`
