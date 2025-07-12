# Stock Supplier Monitoring System

Aplikasi web monitoring stok dan produksi berbasis CodeIgniter 4. Sistem ini menyediakan dashboard untuk admin untuk mengelola data sales, planning produksi (PPIC), dan material control.

## Fitur

### 1. Sales Dashboard
- Import data Excel sales dengan mudah
- Tampilan data dalam bentuk tabel dengan fitur filter dan pencarian
- Dropdown filter untuk Model No dan Class yang diambil dari database
- Tampilan schedule 31 hari dan kolom total
- Pagination dan scrolling horizontal untuk data yang besar

### 2. PPIC (Production Planning and Inventory Control)
- Import planning production dari Excel
- Import actual production dari Excel 
- Perbandingan data planning dan actual
- Fitur CRUD untuk data PPIC

### 3. Material Control
- Import BOM (Bill of Material)
- Material Control System
- Material Usage Tracking

## Teknologi

- PHP 8.4.8
- CodeIgniter 4.6.1
- Bootstrap 5
- DataTables 1.13.6
- Select2 4.1.0-rc.0
- PhpSpreadsheet untuk handling Excel

## Instalasi

1. Clone repository ini
```bash
git clone [URL_REPOSITORY]
cd stok-supplier-web