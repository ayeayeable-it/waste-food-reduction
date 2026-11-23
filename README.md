# RescueBite — Location-Aware Surplus Food Reservation System

**RescueBite** is a full-stack, location-aware web application designed to reduce food waste in convenience stores and mini-markets while giving nearby consumers access to discounted surplus food for in-store collection.

Built with **Laravel**, **MySQL 8.x**, **Blade**, **Tailwind CSS**, **Vanilla JavaScript (ES6+)**, **Leaflet.js (OpenStreetMap)**, and **Chart.js**.

---

## Key Features

- **Role-Based Authentication & Security:** Separate dashboards and strict HTTP 403 authorization for **Buyers**, **Sellers**, and **Administrators**, with CSRF protection, rate limiting, and audit logging.
- **Location-Aware Food Discovery:** HTML5 Geolocation API + manual map pin selection, interactive **Leaflet.js + OpenStreetMap** store markers, and **Haversine** great-circle distance calculation (km) with nearest-store sorting.
- **Concurrency-Safe Reservation Engine:** Uses MySQL InnoDB transactions (`DB::transaction`) and pessimistic row locking (`SELECT ... FOR UPDATE`) to prevent overselling and negative stock.
- **3-Hour Expiry & Idempotent Stock Restoration:** Enforces `T_expiry = MIN(T_reservation + 3 hours, T_collection_deadline)`, automated expiry via Laravel Task Scheduling (`reservations:expire`), and idempotent conditional stock restoration.
- **Food Acknowledgement & Collection Confirmation:** Mandatory 6-point buyer acknowledgement before reserving, historical price snapshots, and seller reference-based in-store collection confirmation.
- **Notifications, Governance & Analytics:** In-app notification bell & history, Admin CRUD for users/stores/categories/listings, immutable audit logs, Chart.js analytics, and CSV report export.

---

## Technology Stack

- **Backend:** PHP 8.2+, Laravel MVC, Eloquent ORM, Form Requests, Task Scheduling
- **Database:** MySQL 8.x (InnoDB, 9 normalized tables)
- **Frontend:** Blade Templates, Tailwind CSS, ES6+ JavaScript, Chart.js
- **Maps & Location:** HTML5 Geolocation API, Leaflet.js, OpenStreetMap, Haversine Formula
- **Testing:** PHPUnit (24 automated unit & feature tests)

---

## Local Installation Guide

### Prerequisites
- PHP 8.2+ & Composer
- MySQL 8.x (XAMPP or Laragon)
- Node.js 20+ & npm

### Setup Steps
1. **Clone the repository:**
   ```bash
   git clone [https://github.com/YOUR_USERNAME/rescuebite.git](https://github.com/YOUR_USERNAME/rescuebite.git)
   cd rescuebite
   Install PHP and Node dependencies:
Bash
composer install
npm install
Configure environment:
Bash
cp .env.example.rescuebite .env
php artisan key:generate
Update DB_DATABASE=rescuebite, DB_USERNAME=root, and DB_PASSWORD= in .env. Create a MySQL database named rescuebite in phpMyAdmin.(macOS XAMPP users: add DB_SOCKET=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock if 127.0.0.1 refuses connection.)
Migrate, seed, link storage, and build assets:
Bash
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
Run the application:
Bash
php artisan serve
Open http://127.0.0.1:8000 in your browser.
Run the Automatic Expiry Scheduler (optional separate terminal):
Bash
php artisan schedule:work
Demo Accounts (Password: Password123!)
Role	Email
Administrator	admin@rescuebite.local
Seller 1	seller1@rescuebite.local
Seller 2	seller2@rescuebite.local
Buyer 1	buyer1@rescuebite.local
Buyer 2	buyer2@rescuebite.local
Running Automated Tests
Bash
php artisan test
Documentation
Additional documentation is available in the docs/ directory:
docs/INSTALLATION_GUIDE.md
docs/USER_MANUAL.md
docs/TECHNICAL_DOCUMENTATION.md
docs/USABILITY_TESTING_AND_SUS_TEMPLATE.md
docs/BROWSER_AND_RESPONSIVE_TEST_PLAN.md