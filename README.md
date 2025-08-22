<div align="center">
  <h1>Stock Supplier Monitoring System</h1>
  <p>Sistem Monitoring Stok, Produksi, dan Material berbasis Web</p>
  <p>
    <a href="#fitur">Fitur</a> •
    <a href="#teknologi">Teknologi</a> •
    <a href="#instalasi">Instalasi</a> •
    <a href="#penggunaan">Penggunaan</a> •
    <a href="#struktur-aplikasi">Struktur Aplikasi</a>
  </p>
</div>

## Deskripsi

Stock Supplier Monitoring System adalah aplikasi web berbasis CodeIgniter 4 yang dirancang untuk memantau dan mengelola stok, produksi, dan material dalam lingkungan manufaktur. Sistem ini menyediakan dashboard komprehensif untuk admin dan pengguna untuk mengelola data sales, planning produksi (PPIC), material control, serta laporan analisis yang mendalam.

## Fitur

### 1. Sales Dashboard
- **Import Excel**: Import data sales dari file Excel dengan validasi otomatis
- **Visualisasi Data**: Tampilan grafik perbandingan sales plan vs actual
- **Filter Dinamis**: Filter berdasarkan model, class, dan periode
- **Schedule View**: Tampilan jadwal 31 hari dengan highlight untuk nilai penting
- **Kalkulasi Otomatis**: Perhitungan total dan persentase pencapaian

### 2. PPIC (Production Planning and Inventory Control)
- **Planning Production**: Import dan manajemen data perencanaan produksi
- **Actual Production**: Pencatatan dan tracking produksi aktual
- **Analisis Perbandingan**: Visualisasi gap antara planning dan actual
- **Finish Good & Semi-Finish Good**: Manajemen inventori produk jadi dan setengah jadi
- **Dashboard Produksi**: Visualisasi efisiensi produksi harian dan bulanan

### 3. Material Control
- **BOM Management**: Pengelolaan Bill of Material dengan struktur hierarkis
- **Material Tracking**: Pelacakan penggunaan material per produk
- **Stock Control**: Monitoring stok material dengan alert level minimum
- **Supplier Management**: Pengelolaan data supplier dan lead time
- **Material Usage Analysis**: Analisis penggunaan material berdasarkan produksi

### 4. Report System
- **Material Shortage Report**: Analisis kekurangan material dengan proyeksi kebutuhan
- **Delivery Shortage Report**: Monitoring keterlambatan pengiriman dari supplier
- **Export Excel**: Ekspor semua laporan ke format Excel untuk analisis lanjutan
- **Filter Canggih**: Filter multi-dimensi untuk analisis yang mendalam
- **Data Visualization**: Representasi visual data untuk pengambilan keputusan

## Teknologi

### Backend
- **PHP 8.4.8**: Bahasa pemrograman server-side
- **CodeIgniter 4.6.1**: Framework PHP yang cepat dan ringan
- **MySQL/MariaDB**: Database relasional untuk penyimpanan data
- **RESTful API**: Arsitektur API untuk komunikasi client-server

### Frontend
- **Bootstrap 5**: Framework CSS untuk UI responsif
- **jQuery**: Library JavaScript untuk manipulasi DOM
- **DataTables 1.13.6**: Plugin untuk manajemen tabel interaktif
- **Select2 4.1.0**: Komponen dropdown dengan fitur pencarian
- **Chart.js**: Library untuk visualisasi data grafis
- **Moment.js**: Manipulasi dan format tanggal
- **DateRangePicker**: Komponen pemilihan rentang tanggal
- **Toastr**: Notifikasi non-blocking

### Tools & Libraries
- **PhpSpreadsheet**: Library untuk manipulasi file Excel
- **Composer**: Dependency manager untuk PHP
- **Git**: Version control system

## Instalasi

### Prasyarat
- PHP 8.0 atau lebih tinggi
- Composer
- MySQL/MariaDB
- Web Server (Apache/Nginx)

### Langkah Instalasi

1. Clone repository
```bash
git clone https://github.com/username/stok-supplier-web.git
cd stok-supplier-web
```

2. Install dependencies
```bash
composer install
```

3. Setup environment
```bash
cp env-example .env
```

4. Konfigurasi database di file .env
```
database.default.hostname = localhost
database.default.database = stok_supplier_db
database.default.username = root
database.default.password = password
database.default.DBDriver = MySQLi
```

5. Jalankan migrasi database
```bash
php spark migrate
```

6. Jalankan seeder (opsional)
```bash
php spark db:seed InitialSeeder
```

7. Jalankan aplikasi
```bash
php spark serve
```

8. Akses aplikasi di browser: `http://localhost:8080`

## Penggunaan

### Login
- Admin: username `admin`, password `admin123`
- User: username `user`, password `user123`

### Import Data
1. Siapkan file Excel sesuai template yang tersedia
2. Akses menu yang sesuai (Sales, PPIC, Material Control)
3. Klik tombol Import dan pilih file
4. Verifikasi data preview dan konfirmasi import

### Laporan
1. Akses menu Reports
2. Pilih jenis laporan (Material Shortage, Delivery Shortage)
3. Atur filter sesuai kebutuhan
4. Klik Search untuk menampilkan data
5. Gunakan tombol Export untuk mengunduh laporan dalam format Excel

## Struktur Aplikasi

```
stok-supplier-web/
├── app/                    # Kode aplikasi
│   ├── Config/             # Konfigurasi aplikasi
│   ├── Controllers/        # Controller aplikasi
│   ├── Database/           # Migrasi dan seeder
│   ├── Filters/            # Filter HTTP
│   ├── Helpers/            # Helper functions
│   ├── Models/             # Model database
│   └── Views/              # Template view
├── public/                 # Aset publik
│   ├── assets/             # Aset lokal (JS, CSS)
│   │   └── bundling/       # Library pihak ketiga
│   ├── css/                # CSS kustom
│   ├── js/                 # JavaScript kustom
│   └── image/              # Gambar dan ikon
├── vendor/                 # Dependencies (dikelola Composer)
├── writable/               # File yang dapat ditulis (logs, cache)
├── .env                    # Konfigurasi environment
├── composer.json           # Definisi dependencies
└── README.md              # Dokumentasi aplikasi
```

## Keamanan

- Semua input divalidasi baik di sisi client maupun server
- Implementasi CSRF protection untuk semua form
- Penggunaan prepared statements untuk query database
- Role-based access control untuk pembatasan akses
- Semua library eksternal dimuat secara lokal (tidak menggunakan CDN)

## Kontribusi

Kontribusi selalu diterima. Untuk berkontribusi:

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/amazing-feature`)
3. Commit perubahan (`git commit -m 'Add some amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

---

<div align="center">
  <p>Dikembangkan dengan ❤️ oleh <strong>Bernatd. Situmeang</strong></p>
  <p>© 2025 Stock Supplier Monitoring System. All Rights Reserved.</p>
</div>
