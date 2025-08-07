# 🚍 Prototipe Aplikasi Manajemen Armada PO Bus Pariwisata

Aplikasi ini merupakan prototipe sistem internal untuk PO Bus Pariwisata guna memantau dan memverifikasi **ketersediaan armada** berdasarkan tanggal pemesanan. Dirancang untuk digunakan oleh staf operasional PO guna mencegah tabrakan jadwal sewa dan mempermudah pengelolaan data armada dan booking.

## 🎯 Fitur Utama

- Manajemen data armada (bus)
- Pencatatan booking (pemesanan unit)
- Validasi benturan jadwal pemesanan
- Cek ketersediaan armada berdasarkan tanggal

## 🛠️ Tech Stack

- **Backend**: Laravel 11, PHP 8+
- **Frontend**: Inertia.js + Quasar Framework + TypeScript
- **Database**: MySQL / SQLite (local dev)
- **Tooling**: Vite, Laravel Sail / Valet / XAMPP

## 🏗️ Struktur Modul (v1 MVP)

1. **Bus Management** – Data unit armada
2. **Booking Management** – Form booking dan validasi jadwal
3. **Availability Check** – Pencarian armada tersedia berdasarkan rentang tanggal

## 🚀 Instalasi

```bash
git clone https://github.com/ffrz/po-bus-prototype.git
cd po-bus-prototype
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install && npm run dev
php artisan serve
```

## 📝 Lisensi
MIT. Gunakan bebas untuk eksplorasi dan riset internal.