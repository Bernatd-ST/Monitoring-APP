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
        <h5 class="m-0 font-weight-bold">Data Actual Sales</h5>
        <div>
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
                    <?php foreach ($model_list as $model): ?>
                        <option value="<?= esc($model['model_no']) ?>"><?= esc($model['model_no']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter-class" class="form-label">Class</label>
                <select class="form-select form-select-sm" id="filter-class">
                    <option value="">Semua Class</option>
                    <?php foreach ($class_list as $cls): ?>
                        <option value="<?= esc($cls['class']) ?>"><?= esc($cls['class']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-prd-cd" class="form-label">Product Code</label>
                <select class="form-select form-select-sm" id="filter-prd-cd">
                    <option value="">Semua Product Code</option>
                    <?php foreach ($prd_cd_list as $prd): ?>
                        <option value="<?= esc($prd['prd_cd']) ?>"><?= esc($prd['prd_cd']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter-date" class="form-label">Tanggal</label>
                <input type="date" class="form-control form-control-sm" id="filter-date">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="apply-filter" class="btn btn-primary btn-sm me-2">Terapkan</button>
                <button type="button" id="reset-filter" class="btn btn-secondary btn-sm">Reset</button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="width: 100%; overflow-x: auto;">
            <table id="actual-sales-table" class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Model No</th>
                        <th>Class</th>
                        <th>Schedule Qty</th>
                        <th>Actual Qty</th>
                        <th>Product Code</th>
                        <th>Content</th>
                        <th>Ship Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sales_data)): ?>
                        <?php foreach ($sales_data as $row): ?>
                            <tr>
                                <td><?= esc($row['model_no']) ?></td>
                                <td><?= esc($row['class']) ?></td>
                                <td><?= esc($row['sch_qty']) ?></td>
                                <td><?= esc($row['act_qty']) ?></td>
                                <td><?= esc($row['prd_cd']) ?></td>
                                <td><?= esc($row['content']) ?></td>
                                <td><?= date('d-M-y', strtotime($row['shp_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data untuk ditampilkan. Silakan import file Excel.</td>
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
        <h5 class="modal-title" id="importModalLabel">Import Data Actual Sales</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= base_url('admin/sales/actual/upload') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label for="excel_file" class="form-label">File Excel (.xlsx, .xls)</label>
            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx, .xls" required>
            <div class="form-text">Format kolom: Model No (A), Class (B), Schedule Qty (C), Actual Qty (D), Product Code (E), Content (F), Ship Date (G)</div>
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
                alert.style.transition = 'opacity 1s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 1000);
            }, 5000);
        });
        
        // Initialize Select2 for all filter dropdowns
        $('#filter-model').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Model No',
            allowClear: true,
            dropdownParent: $('#filter-section')
        });
        
        $('#filter-class').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Class',
            allowClear: true,
            dropdownParent: $('#filter-section')
        });
        
        $('#filter-prd-cd').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Product Code',
            allowClear: true,
            dropdownParent: $('#filter-section')
        });
        
        // Inisialisasi DataTables
        try {
            // Hapus dulu HTML no-data agar tidak bentrok dengan DataTables
            if ($('#actual-sales-table tbody tr td[colspan]').length) {
                $('#actual-sales-table tbody').html('');
            }
            
            var actualSalesTable = $('#actual-sales-table').DataTable({
                responsive: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                columnDefs: [
                    { className: "text-nowrap", targets: [0, 1, 4, 5, 6] },
                    { className: "text-start", targets: [0, 4, 5] },
                    { className: "text-end", targets: [1, 2, 3] },
                    { className: "text-center", targets: [6] }
                ],
                // Override template untuk pesan kosong dengan colspan yang benar
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                // Perbaikan untuk error columns
                drawCallback: function(settings) {
                    // Force update colspan for empty message
                    $('.dataTables_empty').attr('colspan', 7);
                }
            });
            
            // Tambahan untuk memastikan colspan selalu benar
            actualSalesTable.on('draw', function() {
                $('.dataTables_empty').attr('colspan', 7);
            });
            
            console.log('DataTables berhasil diinisialisasi');
        } catch (e) {
            console.error('Error saat inisialisasi DataTables:', e);
            alert('Terjadi error saat memuat tabel. Silakan refresh halaman.');
        }
        
        // Toggle filter section
        $('#toggle-filters').click(function() {
            $('#filter-section').slideToggle();
            $(this).find('i').toggleClass('fa-filter fa-times');
        });
        
        // Filter handling
        $('#apply-filter').click(function() {
            applyCustomFilters();
        });
        
        $('#reset-filter').click(function() {
            $('#filter-model').val('').trigger('change');
            $('#filter-class').val('').trigger('change');
            $('#filter-prd-cd').val('').trigger('change');
            $('#filter-date').val('');
            actualSalesTable.search('').columns().search('').draw();
        });
        
        function applyCustomFilters() {
            var modelFilter = $('#filter-model').val();
            var classFilter = $('#filter-class').val();
            var prdCdFilter = $('#filter-prd-cd').val();
            var dateFilter = $('#filter-date').val();
            
            // Reset semua filter
            actualSalesTable.search('').columns().search('').draw();
            
            // Terapkan filter model dan class - gunakan filter exact match karena model dan class harus persis sama
            if (modelFilter) {
                actualSalesTable.column(0).search('^' + $.fn.dataTable.util.escapeRegex(modelFilter) + '$', true, false);
            }
            
            if (classFilter) {
                actualSalesTable.column(1).search('^' + $.fn.dataTable.util.escapeRegex(classFilter) + '$', true, false);
            }
            
            if (prdCdFilter) {
                actualSalesTable.column(4).search('^' + $.fn.dataTable.util.escapeRegex(prdCdFilter) + '$', true, false);
            }
            
            // Filter tanggal - ini lebih kompleks karena perlu format tanggal yang sama
            if (dateFilter) {
                // Format tanggal dari input (yyyy-mm-dd) ke format yang ditampilkan di tabel (dd-MMM-yy)
                var parts = dateFilter.split('-');
                if (parts.length === 3) {
                    var year = parts[0].substring(2); // Ambil 2 digit terakhir tahun
                    var month = new Date(dateFilter).toLocaleString('en', { month: 'short' });
                    var day = parseInt(parts[2], 10);
                    var formattedDate = day + '-' + month + '-' + year;
                    
                    // Cari tanggal yang cocok di kolom tanggal (kolom 6)
                    actualSalesTable.column(6).search(formattedDate, true, false);
                }
            }
            
            actualSalesTable.draw();
        }
    });
</script>

<!-- CSS tambahan untuk tabel -->
<style>
    .table th, .table td {
        vertical-align: middle;
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
</style>

<?= $this->endSection() ?>
