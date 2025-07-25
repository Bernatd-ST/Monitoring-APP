<?= $this->extend('admin/layout') ?>

<?= $this->section('page_buttons') ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('admin/ppic/planning') ?>" class="btn btn-sm btn-outline-primary me-2">
            <i class="fas fa-calendar-alt"></i>
            Planning Production
        </a>
        <a href="<?= base_url('admin/ppic/actual') ?>" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-clipboard-list"></i>
            Actual Production
        </a>
        <a href="<?= base_url('admin/ppic/finishgood') ?>" class="btn btn-sm btn-outline-success me-2">
            <i class="fas fa-box"></i>
            Finish Good
        </a>
        <a href="<?= base_url('admin/ppic/semifinishgood') ?>" class="btn btn-sm btn-outline-info">
            <i class="fas fa-box-open"></i>
            Semi Finish Good
        </a>
    </div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">PPIC Dashboard</h5>
                </div>
                <div class="card-body">
                    <p>Selamat datang di PPIC (Production Planning and Inventory Control) dashboard.</p>
                    <p>Gunakan menu di bawah untuk mengakses fitur-fitur PPIC:</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Planning Production</h5>
                </div>
                <div class="card-body">
                    <p>Modul untuk mengelola data perencanaan produksi.</p>
                    <ul>
                        <li>Lihat data planning produksi</li>
                        <li>Import data planning dari Excel</li>
                        <li>Export data planning ke Excel</li>
                        <li>Filter data berdasarkan beberapa kriteria</li>
                    </ul>
                    <a href="<?= base_url('admin/ppic/planning') ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Buka Planning
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Actual Production</h5>
                </div>
                <div class="card-body">
                    <p>Modul untuk mengelola data produksi aktual.</p>
                    <ul>
                        <li>Lihat data produksi aktual</li>
                        <li>Import data aktual dari Excel</li>
                        <li>Export data aktual ke Excel</li>
                        <li>Filter data berdasarkan beberapa kriteria</li>
                    </ul>
                    <a href="<?= base_url('admin/ppic/actual') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> Buka Actual
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Finish Good</h5>
                </div>
                <div class="card-body">
                    <p>Modul untuk mengelola data produk jadi (Finish Good).</p>
                    <ul>
                        <li>Lihat data produk jadi</li>
                        <li>Import data dari Excel</li>
                        <li>Filter berdasarkan kriteria dan periode</li>
                    </ul>
                    <a href="<?= base_url('admin/ppic/finishgood') ?>" class="btn btn-success">
                        <i class="fas fa-arrow-right"></i> Buka Finish Good
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Semi Finish Good</h5>
                </div>
                <div class="card-body">
                    <p>Modul untuk mengelola data produk setengah jadi (Semi Finish Good).</p>
                    <ul>
                        <li>Lihat data produk setengah jadi</li>
                        <li>Import data dari Excel</li>
                        <li>Filter berdasarkan kriteria dan periode</li>
                    </ul>
                    <a href="<?= base_url('admin/ppic/semifinishgood') ?>" class="btn btn-info">
                        <i class="fas fa-arrow-right"></i> Buka Semi Finish Good
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
