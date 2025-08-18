<!-- File: app/Views/admin/ppic/actual.php -->

<?= $this->extend('admin/layout') ?>

<?= $this->section('page_buttons') ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importActualModal">
            <i class="fas fa-file-excel"></i>
            Import Excel
        </button>
        <a href="<?= base_url('admin/ppic/export-actual') ?>" class="btn btn-sm btn-outline-success ms-2">
            <i class="fas fa-download"></i>
            Export Excel
        </a>
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
        <h5 class="m-0 font-weight-bold">Actual Production</h5>
        <div>
            <button id="btn-add-actual" class="btn btn-sm btn-light me-2"><i class="fas fa-plus"></i> Tambah Data</button>
            <button id="toggle-filters" class="btn btn-sm btn-light"><i class="fas fa-filter"></i> Filter</button>
        </div>
    </div>
    
    <!-- Filter Section (Hidden by default) -->
    <div id="filter-section" class="card-body border-bottom" style="display: none;">
        <form id="filter-form" class="row g-3">
            <div class="col-md-3">
                <label for="filter-model" class="form-label">Model No</label>
                <select class="form-select form-select-sm" id="filter-model">
                    <option value="">Semua Model</option>
                    <?php if(isset($model_list)): ?>
                        <?php foreach ($model_list as $model): ?>
                            <option value="<?= esc($model['model_no']) ?>"><?= esc($model['model_no']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-class" class="form-label">Class</label>
                <select class="form-select form-select-sm" id="filter-class">
                    <option value="">Semua Class</option>
                    <?php if(isset($class_list)): ?>
                        <?php foreach ($class_list as $cls): ?>
                            <option value="<?= esc($cls['class']) ?>"><?= esc($cls['class']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-daterange" class="form-label">Rentang Tanggal</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="filter-daterange" placeholder="Pilih rentang tanggal" readonly>
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                </div>
                <input type="hidden" id="filter-date-start" value="">
                <input type="hidden" id="filter-date-end" value="">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="apply-filter" class="btn btn-primary btn-sm me-2">Terapkan</button>
                <button type="button" id="reset-filter" class="btn btn-secondary btn-sm">Reset</button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="width: 100%; overflow-x: auto;">
            <table id="actual-table" class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <!-- Kolom tetap (2) -->
                        <th class="freeze-column">Model No</th>
                        <th>Class</th>
                        <!-- Kolom hari (31) -->
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <th class="date-column text-center"><?= $i ?></th>
                        <?php endfor; ?>
                        <!-- Kolom total (1) -->
                        <th class="text-center">Total</th>
                        <!-- Kolom aksi (1) -->
                        <th class="freeze-column-right text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($actual_data) && !empty($actual_data)): ?>
                        <?php foreach ($actual_data as $row): ?>
                            <tr data-id="<?= $row['id'] ?>">
                                <!-- Kolom tetap (2) -->
                                <td class="freeze-column"><?= esc($row['model_no']) ?></td>
                                <td><?= esc($row['class']) ?></td>
                                <!-- Kolom hari (31) -->
                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                    <td class="text-end"><?= esc($row['day_'.$i]) ?></td>
                                <?php endfor; ?>
                                <!-- Kolom total (1) -->
                                <td class="text-end font-weight-bold"><?= esc($row['total']) ?></td>
                                <!-- Kolom aksi (1) -->
                                <td class="freeze-column-right text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info view-actual" data-id="<?= $row['id'] ?>"><i class="fas fa-eye"></i></button>
                                        <button type="button" class="btn btn-sm btn-warning edit-actual" data-id="<?= $row['id'] ?>"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-danger delete-actual" data-id="<?= $row['id'] ?>"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Jika tidak ada data, tampilkan pesan dengan colspan yang sama dengan jumlah kolom di thead -->
                        <tr>
                            <td colspan="35" class="text-center">Tidak ada data untuk ditampilkan. Silakan import file Excel.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importActualModal" tabindex="-1" aria-labelledby="importActualModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importActualModalLabel">Import Data Actual Production</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formImportActual" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label for="excelFile" class="form-label">Pilih File Excel (.xls, .xlsx)</label>
            <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xls,.xlsx">
            <div class="form-text">Format file harus sesuai dengan template Excel actual production.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnImportActual" class="btn btn-primary"><i class="fa fa-upload"></i> Upload dan Proses</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Tambah/Edit Data Actual Production -->
<div class="modal fade" id="actualModal" tabindex="-1" aria-labelledby="actualModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="actualModalLabel">Tambah Data Actual Production</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="actualForm">
          <?= csrf_field() ?>
          <!-- CSRF token field akan ditambahkan oleh csrf_field() -->
          <input type="hidden" name="id" id="actual_id">
          <div class="mb-3">
            <label for="model_no" class="form-label">Model No</label>
            <input type="text" class="form-control" id="model_no" name="model_no" required>
          </div>
          <div class="mb-3">
            <label for="class" class="form-label">Class</label>
            <input type="text" class="form-control" id="class" name="class" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Schedule</label>
            <div class="row g-2">
              <?php for ($i = 1; $i <= 31; $i++): ?>
              <div class="col-md-2">
                <div class="input-group input-group-sm mb-2">
                  <span class="input-group-text"><?= $i ?></span>
                  <input type="number" class="form-control schedule-input" id="day_<?= $i ?>" name="day_<?= $i ?>" value="0" min="0">
                </div>
              </div>
              <?php endfor; ?>
            </div>
          </div>
          <div class="mb-3">
            <label for="total" class="form-label">Total</label>
            <input type="number" class="form-control" id="total" name="total" readonly>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="saveActual">Simpan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Actual Production -->
<div class="modal fade" id="actualDetailModal" tabindex="-1" aria-labelledby="actualDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title" id="actualDetailModalLabel">Detail Actual Production</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="actual-details">
                    <div class="detail-row">
                        <div class="detail-label">Model No:</div>
                        <div class="detail-value" id="detail-model-no"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Class:</div>
                        <div class="detail-value" id="detail-class"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Total:</div>
                        <div class="detail-value" id="detail-total"></div>
                    </div>
                </div>
                
                <h6 class="mt-4 mb-3">Distribusi Actual Production Harian</h6>
                <div class="actual-chart-container">
                    <canvas id="actualChart"></canvas>
                </div>
                
                <h6 class="mt-4 mb-2">Data Harian</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <?php for ($i = 1; $i <= 31; $i += 1): ?>
                                <th class="text-center"><?= $i ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="daily-values-row">
                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                <td class="text-center" id="detail-day-<?= $i ?>">0</td>
                                <?php endfor; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteActualModal" tabindex="-1" aria-labelledby="deleteActualModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-gradient-danger text-white">
        <h5 class="modal-title" id="deleteActualModalLabel">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus data actual production ini?</p>
        <p class="mb-0"><strong>Model No: </strong><span id="delete-model-no"></span></p>
        <p><strong>Class: </strong><span id="delete-class"></span></p>
        <input type="hidden" id="delete_actual_id">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
      </div>
    </div>
  </div>
</div>

<!-- Script untuk auto-hide alert messages dan inisialisasi DataTables -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide alerts after 5 seconds
        let alerts = document.querySelectorAll('.alert-success, .alert-danger, .alert-info');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.classList.add('fade');
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });

        // Inisialisasi Select2 untuk dropdown filter
        $('#filter-update').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Update Value',
            allowClear: true,
            dropdownParent: $('#filter-form')
        });
        
        $('#filter-prdcode').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Product Code',
            allowClear: true,
            dropdownParent: $('#filter-form')
        });
        
        $('#filter-model').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Model',
            allowClear: true,
            dropdownParent: $('#filter-form')
        });
        
        $('#filter-class').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Class',
            allowClear: true,
            dropdownParent: $('#filter-form')
        });
    });
</script>

<!-- CSS tambahan untuk tabel -->
<style>
    .date-column {
        min-width: 40px;
    }
    .table th, .table td {
        vertical-align: middle;
        white-space: nowrap; /* Mencegah text wrapping dalam sel */
    }
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 0.5rem;
    }
    .card-header.bg-gradient-primary {
        background: linear-gradient(to right, #4e73df, #224abe);
    }
    
    /* Styling untuk tabel dengan scroll horizontal */
    .dataTables_wrapper {
        width: 100%;
        overflow: hidden;
    }
    
    /* Styling untuk modal detail */
    .actual-details {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 20px;
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }
    
    .detail-row {
        display: flex;
        width: 33%;
        margin-bottom: 10px;
    }
    
    .detail-label {
        font-weight: bold;
        margin-right: 8px;
    }
    
    .detail-value {
        color: #0d6efd;
    }
    
    .actual-chart-container {
        height: 300px;
        margin-bottom: 20px;
    }
    
    .table-responsive {
        position: relative;
        overflow-x: auto;
    }
    
    /* Styling untuk kolom sticky */
    .sticky-column {
        position: sticky;
        background-color: #fff;
        z-index: 1;
    }
    
    .left-column {
        left: 0;
        box-shadow: 5px 0 5px -5px rgba(0, 0, 0, 0.1);
    }
    
    .right-column {
        right: 0;
        box-shadow: -5px 0 5px -5px rgba(0, 0, 0, 0.1);
    }
    
    /* Styling untuk kolom total */
    #actual-table td:nth-last-child(2), 
    #actual-table th:nth-last-child(2) {
        font-weight: bold;
        border-left: 2px solid #dee2e6;
        background-color: #f8f9fa !important;
    }
    
    /* Styling untuk freeze columns */
    .freeze-column {
        position: sticky;
        left: 0;
        z-index: 10;
        background-color: white;
        box-shadow: 2px 0 5px -2px rgba(0,0,0,0.2);
    }
    
    .table-dark .freeze-column {
        background-color: #212529;
        z-index: 11;
    }
    
    .freeze-column-right {
        position: sticky;
        right: 0;
        z-index: 10;
        background-color: white;
        box-shadow: -2px 0 5px -2px rgba(0,0,0,0.2);
    }
    
    .table-dark .freeze-column-right {
        background-color: #212529;
        z-index: 11;
    }
    
    /* Styling untuk tombol aksi */
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    /* Modal detail styling */
    .detail-row {
        display: flex;
        margin-bottom: 0.5rem;
    }
    
    .detail-label {
        font-weight: bold;
        width: 120px;
    }
    
    .detail-value {
        flex-grow: 1;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="<?= base_url('js/ppic/actual.js') ?>"></script>
<?= $this->endSection() ?>