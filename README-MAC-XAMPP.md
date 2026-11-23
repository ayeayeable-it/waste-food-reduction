# RescueBite — MacBook + VS Code + XAMPP Setup Guide (Phases 1 & 2)

## 1. Start XAMPP
Open XAMPP Manager-OSX and start **Apache** and **MySQL Database**.
Open phpMyAdmin (`http://localhost/phpmyadmin`) and create a database named `rescuebite` (collation: `utf8mb4_unicode_ci`).

## 2. Create the Laravel 11 Base Project
Open Terminal on your Mac and run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs
composer create-project laravel/laravel:^11.0 rescuebite
cd rescuebite
```

## 3. Copy These Extracted Files Into `rescuebite/`
Unzip `rescuebite-phase1-2.zip` and copy its contents into `/Applications/XAMPP/xamppfiles/htdocs/rescuebite/`, replacing existing files when prompted.

Then copy `.env.example.rescuebite` over `.env`:
```bash
cp .env.example.rescuebite .env
```

## 4. Install Frontend & Run Migrations
```bash
mkdir -p app/Services
npm install
npm install -D tailwindcss@^3.4 postcss autoprefixer
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000` in Safari or Chrome.

## 5. Open in VS Code
Open `/Applications/XAMPP/xamppfiles/htdocs/rescuebite` directly via **File -> Open Folder** so Intelephense indexes `vendor/` cleanly.
