# 💎 Dricash — Proactive Personal Wealth Planner

> **Dricash** bukan sekadar pencatat keuangan biasa, melainkan *Wealth Planner* personal yang proaktif. Sistem dirancang untuk membantu pengguna mengelola arus kas harian secara instan, mengamankan kebutuhan bulanan wajib (*Ring-Fencing*), membatasi pengeluaran harian secara dinamis (*Daily Budgeting with Rollover*), dan memastikan tren kekayaan selalu bertumbuh melalui *Growth Tracker* dengan sistem intervensi otomatis.

---

## 🚀 Fitur Utama & Algoritma Finansial

### 1. ⚡ Quick Entry (Aksi Instan Reaktif)
- Akses instan persistent bar & modal: **`+ Pemasukan`** dan **`- Pengeluaran`** dengan keyboard shortcut (`I` untuk Pemasukan, `E` untuk Pengeluaran).
- Tombol nominal cepat (`+50rb`, `+100rb`, `+500rb`, `+1jt`, `+5jt`), pemilihan kategori visual, dan metode pembayaran.
- Real-time pre-check kalkulasi dampak pengeluaran terhadap target pertumbuhan bulanan.

### 2. 🛡️ Ring-Fencing Kebutuhan Bulanan (Fixed Expenses)
- Pendataan kewajiban rutin (Sewa kos/rumah, listrik/PAM, internet fiber, asuransi, cicilan).
- **Sistem Kunci Dana**: Akumulasi kebutuhan bulanan langsung dipisahkan (*reserved*) dari total pemasukan di awal siklus, sehingga tidak akan pernah bocor ke jatah jajan harian.
- **Checklist Pembayaran Interaktif**: Tandai kewajiban yang sudah lunas dengan 1-klik yang otomatis tersinkronisasi ke buku besar.

### 3. 🎯 Dynamic Daily Budgeting & Rollover Calculator
- **Formula Jatah Harian**:
  $$\text{Jatah Harian} = \frac{\text{Total Pemasukan} - \text{Kebutuhan Bulanan (Reserved)} - \text{Target Tabungan} - \text{Pengeluaran Sebelum Hari Ini}}{\text{Sisa Hari dalam Bulan Ini (termasuk Hari Ini)}}$$
- **Sistem Reward (Rollover Surplus)**: Jika pengeluaran hari sebelumnya hemat (di bawah jatah), sisa uang otomatis mendistribusikan kenaikan jatah harian untuk hari-hari berikutnya.
- **Penyesuaian Defisit (Overbudget Absorption)**: Jika pengeluaran melebihi batas harian, sistem otomatis memotong proporsional jatah hari-hari berikutnya agar target tabungan akhir bulan tetap aman.
- **Indikator Visual Traffic-Light**: Hijau (Aman < 70%), Kuning (Waspada 70%-100%), Merah (Overbudget > 100%).

### 4. 📈 Growth Tracker & Alert Interceptor (Kurva Kekayaan)
- Menetapkan target persentase pertumbuhan kekayaan bulanan (contoh: *Asset harus naik minimal 5% dari bulan lalu*).
- **Intervensi Proaktif**: Jika pengguna mencoba menginput pengeluaran yang akan mengakibatkan persentase tabungan turun di bawah target, modal peringatan keras (*Soft-Block Intervention*) akan muncul dan menanyakan konfirmasi (*Sesuaikan Anggaran* atau *Tetap Lanjutkan dengan Override*).
- Visualisasi Kurva Kekayaan (Net Worth actual vs target goal line) dan Doughnut Chart distribusi pengeluaran.

### 5. 📑 Buku Besar Transaksi & Laporan Analitik
- Tabel histori transaksi dengan filter multi-kriteria (Tipe, Kategori, Rentang Tanggal, Search).
- Laporan diagnostik: rasio beban kewajiban, rata-rata burn rate harian, dan rasio tabungan.

---

## 🛠️ Stack Teknologi

- **Backend & API:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vue.js 3 (Composition API `<script setup>`) terintegrasi via **Inertia.js**
- **Styling & Theme:** Tailwind CSS (Dark Luxury Fintech Obsidian Theme `#080B11`)
- **Icons & Visuals:** Lucide Vue Next & Chart.js (`vue-chartjs`)
- **Database & Precision:** MySQL 8.x / SQLite dengan presisi moneter mutlak `DECIMAL(15,2)` dan *Soft Deletes*.
- **Containerization:** Docker & Laravel Sail support (`docker-compose.yml`).

---

## 🏁 Panduan Menjalankan Aplikasi

### 1. Akun Demo Bawaan (Default Credentials)
Aplikasi sudah dilengkapi dengan seeder akun demo realistis:
- **Email:** `demo@dricash.app`
- **Password:** `password`
- Terdapat tombol **"Isi Akun Demo (1-Click Test Drive)"** pada halaman login.

### 2. Menjalankan Server Lokal (Local Development)
```bash
# 1. Clone & masuk ke direktori proyek
cd dricash

# 2. Migrasi dan Seed Database
php artisan migrate:fresh --seed

# 3. Jalankan Vite Compiler
npm run dev

# 4. Di terminal baru, jalankan server Laravel
php artisan serve
```
Buka browser di: `http://127.0.0.1:8000`

### 3. Menjalankan Unit & Feature Test
```bash
./vendor/bin/phpunit
# atau
php artisan test
```

---

## 📁 Struktur Basis Data Utama

| Tabel | Deskripsi | Presisi Kolom Nominal |
|---|---|---|
| `users` | Akun pengguna, preferensi mata uang, hari gajian, net worth awal | `DECIMAL(15,2)` |
| `categories` | Kategori pemasukan, pengeluaran, dan kewajiban | Soft Deletes |
| `monthly_obligations` | Master kewajiban bulanan rutin (Ring-fencing) | `DECIMAL(15,2)` |
| `monthly_obligation_payments` | Checklist riwayat pembayaran per bulan & tahun | `DECIMAL(15,2)` |
| `growth_targets` | Target pertumbuhan kekayaan & tabungan per periode | `DECIMAL(5,2)` & `DECIMAL(15,2)` |
| `transactions` | Buku besar transaksi arus kas | `DECIMAL(15,2)` |
