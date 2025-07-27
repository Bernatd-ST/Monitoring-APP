<?= $this->extend('admin/layout') ?>

<?= $this->section('page_buttons') ?>
<div class="btn-toolbar mb-2 mb-md-0">
    <div class="btn-group me-2">
        <button type="button" class="btn btn-sm btn-outline-primary" id="uploadExcelBtn">
            <i class="fas fa-file-excel"></i> Import Excel
        </button>
        <a href="<?= base_url('admin/ppic') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- notifikasi -->
<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success" role="alert">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" role="alert">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('info')): ?>
    <div class="alert alert-info" role="alert">
        <?= session()->getFlashdata('info') ?>
    </div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold">Data Finish Good</h5>
        <div>
            <button id="btn-add-finishgood" class="btn btn-sm btn-light me-2"><i class="fas fa-plus"></i> Tambah Data</button>
            <button id="toggle-filters" class="btn btn-sm btn-light"><i class="fas fa-filter"></i> Filter</button>
        </div>
    </div>
    
    <!-- Filter Section (Hidden by default) -->
    <div id="filter-section" class="card-body border-bottom" style="display: none;">
        <form id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label for="criteriaFilter" class="form-label">Criteria</label>
                <select class="form-select" id="criteriaFilter">
                    <option value="">Semua Criteria</option>
                    <?php if (isset($criteria_list) && is_array($criteria_list)): ?>
                        <?php foreach ($criteria_list as $item): ?>
                            <option value="<?= esc($item['criteria']) ?>"><?= esc($item['criteria']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="periodFilter" class="form-label">Period</label>
                <input type="month" class="form-control" id="periodFilter">
            </div>
            <div class="col-md-3">
                <label for="partNoFilter" class="form-label">Part No</label>
                <select class="form-select" id="partNoFilter">
                    <option value="">Semua Part No</option>
                    <?php if (isset($part_no_list) && is_array($part_no_list)): ?>
                        <?php foreach ($part_no_list as $item): ?>
                            <option value="<?= esc($item['part_no']) ?>"><?= esc($item['part_no']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="classFilter" class="form-label">Class</label>
                <select class="form-select" id="classFilter">
                    <option value="">Semua Class</option>
                    <?php if (isset($class_list) && is_array($class_list)): ?>
                        <?php foreach ($class_list as $item): ?>
                            <option value="<?= esc($item['class']) ?>"><?= esc($item['class']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-12 mt-3 d-flex justify-content-end">
                <button type="button" id="resetFilterBtn" class="btn btn-sm btn-secondary me-2">Reset</button>
                <button type="button" id="applyFilterBtn" class="btn btn-sm btn-primary">Terapkan Filter</button>
            </div>
        </form>
    </div>

    <!-- Main Data Table -->
    <div class="card-body">
        <div class="table-responsive">
            <table id="finishGoodTable" class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Criteria</th>
                        <th>Period</th>
                        <th>Description</th>
                        <th>Part No</th>
                        <th>Class</th>
                        <th>End Balance</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($finish_good) && is_array($finish_good)): ?>
                        <?php $i = 1; ?>
                        <?php foreach ($finish_good as $item): ?>
                            <tr data-id="<?= $item['id'] ?>">
                                <td><?= $i++ ?></td>
                                <td><?= esc($item['criteria']) ?></td>
                                <td><?= esc($item['period']) ?></td>
                                <td><?= esc($item['description']) ?></td>
                                <td><?= esc($item['part_no']) ?></td>
                                <td><?= esc($item['class']) ?></td>
                                <td><?= esc($item['end_bal']) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-warning btn-edit"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Excel Modal -->
<div class="modal fade" id="uploadExcelModal" tabindex="-1" aria-labelledby="uploadExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadExcelModalLabel">Import Data Finish Good dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?= base_url('admin/ppic/upload-finishgood') ?>" method="post" enctype="multipart/form-data" id="uploadForm">
                    <div class="mb-3">
                        <label for="excelFile" class="form-label">Pilih File Excel</label>
                        <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls">
                        <div class="form-text">Format file harus Excel (.xlsx atau .xls)</div>
                    </div>
                </form>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Pastikan format Excel sesuai dengan struktur data yang dibutuhkan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="submitExcelBtn">Upload</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Finish Good -->
<div class="modal fade" id="finishGoodModal" tabindex="-1" aria-labelledby="finishGoodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finishGoodModalLabel">Form Data Finish Good</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="finishGoodForm">
                    <input type="hidden" id="finishGoodId" name="id">
                    
                    <div class="mb-3">
                        <label for="criteria" class="form-label">Criteria</label>
                        <input type="text" class="form-control" id="criteria" name="criteria" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="period" class="form-label">Period</label>
                        <input type="date" class="form-control" id="period" name="period">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description">
                    </div>
                    
                    <div class="mb-3">
                        <label for="part_no" class="form-label">Part No</label>
                        <input type="text" class="form-control" id="part_no" name="part_no" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="class" class="form-label">Class</label>
                        <input type="text" class="form-control" id="class" name="class" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_bal" class="form-label">End Balance</label>
                        <input type="number" class="form-control" id="end_bal" name="end_bal" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveFinishGoodBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteFinishGoodModal" tabindex="-1" aria-labelledby="deleteFinishGoodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteFinishGoodModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data ini?</p>
                <input type="hidden" id="deleteFinishGoodId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteFinishGoodBtn">Hapus</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Load custom JS file -->
<script src="<?= base_url('js/ppic/finishgood.js') ?>"></script>
<?= $this->endSection() ?>
