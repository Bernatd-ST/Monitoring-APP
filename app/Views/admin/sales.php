<!-- File: app/Views/admin/sales.php -->

<?= $this->extend('admin/layout') ?>

<?= $this->section('page_buttons') ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-excel"></i>
            Import Excel
        </button>
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

<div class="card shadow">
    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold">Data Sales</h5>
        <div>
            <button id="toggle-filters" class="btn btn-sm btn-light"><i class="fas fa-filter"></i> Filter</button>
        </div>
    </div>
    
    <!-- Filter Section (Hidden by default) -->
    <div id="filter-section" class="card-body border-bottom" style="display: none;">
        <form id="filter-form" class="row g-3">
            <div class="col-md-4">
                <label for="filter-model" class="form-label">Model No</label>
                <select class="form-select form-select-sm" id="filter-model">
                    <option value="">Semua Model</option>
                    <?php foreach ($model_list as $model): ?>
                        <option value="<?= esc($model['model_no']) ?>"><?= esc($model['model_no']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-class" class="form-label">Class</label>
                <select class="form-select form-select-sm" id="filter-class">
                    <option value="">Semua Class</option>
                    <?php foreach ($class_list as $cls): ?>
                        <option value="<?= esc($cls['class']) ?>"><?= esc($cls['class']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-date" class="form-label">Tanggal</label>
                <select class="form-select form-select-sm" id="filter-date">
                    <option value="">Semua tanggal</option>
                    <?php for ($i = 1; $i <= 31; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="apply-filter" class="btn btn-primary btn-sm me-2">Terapkan</button>
                <button type="button" id="reset-filter" class="btn btn-secondary btn-sm">Reset</button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="width: 100%; overflow-x: auto;">
            <table id="sales-table" class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ModelNo</th>
                        <th>Class</th>
                        <!-- Kolom schedule -->
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <th class="date-column"><?= $i ?></th>
                        <?php endfor; ?>
                        <th>Total</th>
                    </tr>
                </thead>
                <!-- Ganti bagian <tbody> di app/Views/admin/sales.php -->
                <tbody>
                    <?php if (!empty($sales_data)): ?>
                        <?php foreach ($sales_data as $row): ?>
                            <tr>
                                <td><?= esc($row['model_no']) ?></td>
                                <td><?= esc($row['class']) ?></td>
                                <?php 
                                    for ($i = 1; $i <= 31; $i++): 
                                    $schedule_val = $row["schedule_{$i}"] ?? 0;
                                ?>
                                    <td><?= esc($schedule_val) ?></td>
                                <?php endfor; ?>
                                <td><strong><?= esc($row['total']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="33" class="text-center">Tidak ada data untuk ditampilkan. Silakan import file Excel.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importModalLabel">Import Sales Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Form Upload -->
      <form action="/admin/sales/upload" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label for="excel_file" class="form-label">Pilih file Excel (.xlsx, .xls)</label>
            <input class="form-control" type="file" id="excel_file" name="excel_file" required>
          </div>
          <div class="alert alert-info">
            <strong>Penting:</strong> Pastikan file Excel Anda memiliki kolom 'model_no', 'class', dan data schedule dari hari ke-1 sampai ke-31.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Upload dan Proses</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Script untuk auto-hide alert messages dan inisialisasi DataTables -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide alerts after 5 seconds
        let alerts = document.querySelectorAll('.alert-success, .alert-danger');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.classList.add('fade');
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500); // wait for fade animation
            }, 5000); // 5 seconds
        });
        
        // Inisialisasi Select2 untuk filter dropdown
        $('#filter-model').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Model No',
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
        
        // Inisialisasi DataTables
        var salesTable = $('#sales-table').DataTable({
            responsive: false, // Nonaktifkan responsive agar semua kolom terlihat
            scrollX: true,     // Aktifkan scrolling horizontal
            fixedColumns: {    // Aktifkan fixed columns
                leftColumns: 2,  // Model No dan Class tetap terlihat saat scroll
                rightColumns: 1  // Total tetap terlihat saat scroll
            },
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Tidak ada data yang ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data yang tersedia",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
            },
            columnDefs: [
                { className: "text-nowrap", targets: [0, 1] },      // ModelNo dan Class tidak wrap
                { className: "text-start", targets: [0] },         // ModelNo rata kiri
                { className: "text-end", targets: "_all" }         // Semua kolom lain rata kanan
            ]
        });
        
        // Toggle filter section
        $('#toggle-filters').click(function() {
            $('#filter-section').slideToggle();
        });
        
        // Filter handling
        $('#apply-filter').click(function() {
            applyCustomFilters();
        });
        
        $('#reset-filter').click(function() {
            $('#filter-model').val('');
            $('#filter-class').val('');
            $('#filter-date').val('');
            salesTable.search('').columns().search('').draw();
        });
        
        function applyCustomFilters() {
            var modelFilter = $('#filter-model').val();
            var classFilter = $('#filter-class').val();
            var dateFilter = $('#filter-date').val();
            
            // Reset semua filter
            salesTable.search('').columns().search('').draw();
            
            // Terapkan filter model dan class - gunakan filter exact match karena model dan class harus persis sama
            if (modelFilter) {
                salesTable.column(0).search('^' + $.fn.dataTable.util.escapeRegex(modelFilter) + '$', true, false);
            }
            
            if (classFilter) {
                salesTable.column(1).search('^' + $.fn.dataTable.util.escapeRegex(classFilter) + '$', true, false);
            }
            
            // Filter tanggal - ini lebih kompleks karena perlu filter kolom tertentu
            if (dateFilter) {
                var columnIndex = parseInt(dateFilter) + 1; // +1 karena ModelNo dan Class ada di kolom 0 dan 1
                // Kita perlu filter baris dengan nilai kolom dateFilter > 0
                salesTable.column(columnIndex).search('(?!^0$)', true, false);
            }
            
            salesTable.draw();
        }
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
    
    .dataTables_scroll {
        overflow: auto;
    }
    
    /* Styling untuk kolom total */
    #sales-table th:nth-child(34), 
    #sales-table td:nth-child(34) {
        font-weight: bold;
        border-left: 2px solid #dee2e6;
        background-color: #f8f9fa !important;
    }
</style>

<?= $this->endSection() ?>