#!/usr/bin/env bash
set -e

echo "🚀 [Dricash] Memulai proses deployment ke server Hostinger..."

# 1. Pastikan file .env tersedia
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        echo "📋 Menggunakan .env.production sebagai .env..."
        cp .env.production .env
    else
        echo "❌ File .env atau .env.production tidak ditemukan!"
        exit 1
    fi
fi

# 2. Sinkronisasi kode terbaru dari GitHub
echo "📥 Menarik update kode terbaru dari repository GitHub (main)..."
git fetch origin main
git reset --hard origin/main
git pull origin main

# 3. Instalasi Dependensi PHP (Composer)
echo "📦 Menginstall dependensi PHP (Composer)..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 4. Migrasi Database
echo "🗄️ Menjalankan migrasi database MySQL..."
php artisan migrate --force

# 5. Build Aset Frontend (Vite & Vue 3)
echo "⚡ Mengompilasi aset frontend Vite..."
if command -v npm &> /dev/null; then
    npm install --production=false
    npm run build
else
    echo "⚠️ npm tidak terdeteksi, melewati tahap kompilasi frontend lokal di server."
fi

# 6. Optimasi & Bersihkan Cache Laravel
echo "🧹 Mengoptimasi cache Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Pengaturan Izin Folder & File
echo "🔒 Menyesuaikan hak akses file & direktori..."
chmod -R 775 storage bootstrap/cache
chmod 644 .htaccess 2>/dev/null || true
chmod 644 public/.htaccess 2>/dev/null || true

echo "✅ [Dricash] Deployment berhasil diselesaikan!"
