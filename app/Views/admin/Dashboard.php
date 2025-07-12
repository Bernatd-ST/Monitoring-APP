<!-- File: app/Views/admin/dashboard.php -->

<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<p>Selamat datang di halaman dashboard admin. Halaman ini adalah pusat kendali untuk semua fitur manajemen.</p>

<!-- Konten utama dashboard akan ditampilkan di sini -->
<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Sales</div>
            <div class="card-body">
                <h5 class="card-title">Fitur Sales</h5>
                <p class="card-text">Import dan lihat data penjualan dari file Excel.</p>
            </div>
        </div>
    </div>
    <!-- Kartu lain bisa ditambahkan di sini -->
</div>

<?= $this->endSection() ?>