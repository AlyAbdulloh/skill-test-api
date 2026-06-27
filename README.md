# GriyaHub - Backend API Installation Guide

Panduan instalasi dan persiapan untuk service backend API berbasis Laravel.

## Prasyarat (Requirements)
Pastikan server/mesin lokal Anda sudah terinstall:
* **PHP >= 8.2** (lengkap dengan ekstensi openssl, pdo, mbstring, xml, ctype, dsb.)
* **Composer** (PHP dependency manager)
* **Database Engine** (MySQL, MariaDB, atau PostgreSQL)
* *Rekomendasi:* **Laravel Herd** atau **Valet** untuk mempermudah management PHP dan server lokal di macOS.

---

## Langkah-langkah Instalasi

### 1. Masuk ke Direktori Project
```bash
cd skill-test-api
```

### 2. Install PHP Dependencies
Jalankan composer untuk mendownload dan menginstall seluruh package yang dibutuhkan:
```bash
composer install
```

### 3. Setup Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

Buka file `.env` yang baru dibuat dan sesuaikan konfigurasi database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=username_database_anda
DB_PASSWORD=password_database_anda
```

### 4. Generate Application Key
Jalankan command berikut untuk men-generate key enkripsi Laravel:
```bash
php artisan key:generate
```

### 5. Jalankan Database Migrations & Seeders
Jalankan perintah migrasi tabel beserta pengisian data awal (*seeding*) untuk akun administrator:
```bash
php artisan migrate:fresh --seed
```

> [!IMPORTANT]
> Proses seeding di atas akan menghasilkan data berikut untuk mempermudah pengujian:
> 1. **Akun Admin** default untuk login di frontend:
>    * **Email:** `admin@gmail.com`
>    * **Password:** `admin123`
> 2. **Master Kategori Iuran** (`mst_fee_types`):
>    * **Iuran Satpam**: Rp100.000
>    * **Iuran Kebersihan**: Rp15.000
> 3. **20 Unit Rumah** (`mst_houses`) yang digenerasi acak dengan status hunian.

### 6. Menjalankan Development Server
* **Jika menggunakan Laravel Herd / Valet:**
  Aplikasi backend akan otomatis langsung dapat diakses lewat domain local Herd Anda (contoh: `http://skill-test-api.test`).
  
* **Jika menggunakan command serve bawaan:**
  ```bash
  php artisan serve
  ```
  Aplikasi akan berjalan di `http://127.0.0.1:8000`.

---

## Menjalankan Unit & Feature Testing
Untuk memastikan seluruh endpoint REST API berjalan sesuai spesifikasi (terutama validasi token, reports, dan CRUD), jalankan perintah pengujian berikut:
```bash
php artisan test
```
