<?= $this->extend('admin/layout') ?>

<?= $this->section('page_buttons') ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-excel"></i>
            Import Excel
        </button>
        <button type="button" class="btn btn-sm btn-outline-success me-2" id="exportExcelBtn">
            <i class="fas fa-file-export"></i>
            Export Excel
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
            <button type="button" class="btn btn-sm btn-light me-2" id="addNewBtn" data-bs-toggle="modal" data-bs-target="#formModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
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
                        <th>Actions</th>
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
                                <td>
                                    <?php 
                                        $date = date_create($row['shp_date']);
                                        echo date_format($date, 'd-M-y');
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info view-btn me-1" data-id="<?= $row['id'] ?>" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning edit-btn me-1" data-id="<?= $row['id'] ?>" title="Edit Data">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="<?= $row['id'] ?>" title="Hapus Data">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data untuk ditampilkan. Silakan import file Excel.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Add/Edit -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formModalLabel">Tambah Data Actual Sales</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="salesActualForm" method="post">
        <div class="modal-body">
          <input type="hidden" name="id" id="sales_id">
          <div class="mb-3">
            <label for="model_no" class="form-label">Model No</label>
            <input type="text" class="form-control" id="model_no" name="model_no" required>
          </div>
          <div class="mb-3">
            <label for="class" class="form-label">Class</label>
            <input type="text" class="form-control" id="class" name="class" required>
          </div>
          <div class="mb-3">
            <label for="sch_qty" class="form-label">Schedule Qty</label>
            <input type="number" class="form-control" id="sch_qty" name="sch_qty" required>
          </div>
          <div class="mb-3">
            <label for="act_qty" class="form-label">Actual Qty</label>
            <input type="number" class="form-control" id="act_qty" name="act_qty" required>
          </div>
          <div class="mb-3">
            <label for="prd_cd" class="form-label">Product Code</label>
            <input type="text" class="form-control" id="prd_cd" name="prd_cd">
          </div>
          <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <input type="text" class="form-control" id="content" name="content">
          </div>
          <div class="mb-3">
            <label for="shp_date" class="form-label">Ship Date</label>
            <input type="date" class="form-control" id="shp_date" name="shp_date" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="saveBtn">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Delete Confirmation -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus data ini?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
      </div>
    </div>
  </div>
</div>

<!-- View Detail Modal -->
<div class="modal fade" id="viewDetailModal" tabindex="-1" aria-labelledby="viewDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title" id="viewDetailModalLabel">Detail Sales Actual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <tbody>
                            <tr>
                                <th width="30%" class="bg-light">ID</th>
                                <td id="detail-id"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Model No</th>
                                <td id="detail-model-no"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Class</th>
                                <td id="detail-class"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Schedule Quantity</th>
                                <td id="detail-sch-qty"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Actual Quantity</th>
                                <td id="detail-act-qty"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Product Code</th>
                                <td id="detail-prd-cd"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Content</th>
                                <td id="detail-content"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Ship Date</th>
                                <td id="detail-shp-date"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Created At</th>
                                <td id="detail-created-at"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Updated At</th>
                                <td id="detail-updated-at"></td>
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

<!-- View Detail Modal -->
<div class="modal fade" id="viewDetailModal" tabindex="-1" aria-labelledby="viewDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewDetailModalLabel">Detail Sales Actual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <tbody>
                            <tr>
                                <th width="30%" class="bg-light">ID</th>
                                <td id="detail-id"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Model No</th>
                                <td id="detail-model-no"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Class</th>
                                <td id="detail-class"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Schedule Quantity</th>
                                <td id="detail-sch-qty"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Actual Quantity</th>
                                <td id="detail-act-qty"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Product Code</th>
                                <td id="detail-prd-cd"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Content</th>
                                <td id="detail-content"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Ship Date</th>
                                <td id="detail-shp-date"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Created At</th>
                                <td id="detail-created-at"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Updated At</th>
                                <td id="detail-updated-at"></td>
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
        // Configure Toastr for attractive notifications
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            preventDuplicates: false,
            onclick: null,
            showDuration: '300',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };
        
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
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                },
                columnDefs: [
                    { className: "text-nowrap", targets: [0, 1, 4, 5, 6] },
                    { className: "text-start", targets: [0, 4, 5] },
                    { className: "text-end", targets: [1, 2, 3] },
                    { className: "text-center", targets: [6] }
                ],
                // Perbaikan untuk error columns
                drawCallback: function(settings) {
                    // Force update colspan for empty message
                    $('.dataTables_empty').attr('colspan', 8);
                }
            });
            
            // Tambahan untuk memastikan colspan selalu benar
            actualSalesTable.on('draw', function() {
                $('.dataTables_empty').attr('colspan', 8);
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
        
        // CRUD Operations
        
        // Add New Record
        $('#addNewBtn').click(function() {
            $('#formModalLabel').text('Tambah Data Actual Sales');
            $('#salesActualForm').attr('action', '<?= base_url('admin/sales/actual/add') ?>');
            $('#salesActualForm')[0].reset();
            $('#sales_id').val('');
        });
        
        // View Detail Record
        $(document).on('click', '.view-btn', function() {
            var id = $(this).data('id');
            
            $.ajax({
                url: '<?= base_url('admin/sales/actual/get') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    // Reset modal content
                    $('#detail-id, #detail-model-no, #detail-class, #detail-sch-qty, #detail-act-qty, #detail-prd-cd, #detail-content, #detail-shp-date, #detail-created-at, #detail-updated-at').text('Loading...');
                },
                success: function(response) {
                    if (response.status) {
                        var data = response.data;
                        
                        // Format dates
                        var shipDate = new Date(data.shp_date);
                        var formattedShipDate = shipDate.toLocaleDateString('id-ID', { 
                            year: 'numeric', month: 'long', day: 'numeric' 
                        });
                        
                        var createdAt = data.created_at ? new Date(data.created_at) : null;
                        var formattedCreatedAt = createdAt ? createdAt.toLocaleDateString('id-ID', { 
                            year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' 
                        }) : '-';
                        
                        var updatedAt = data.updated_at ? new Date(data.updated_at) : null;
                        var formattedUpdatedAt = updatedAt ? updatedAt.toLocaleDateString('id-ID', { 
                            year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' 
                        }) : '-';
                        
                        // Populate detail fields
                        $('#detail-id').text(data.id || '-');
                        $('#detail-model-no').text(data.model_no || '-');
                        $('#detail-class').text(data.class || '-');
                        $('#detail-sch-qty').text(data.sch_qty || '0');
                        $('#detail-act-qty').text(data.act_qty || '0');
                        $('#detail-prd-cd').text(data.prd_cd || '-');
                        $('#detail-content').text(data.content || '-');
                        $('#detail-shp-date').text(formattedShipDate);
                        $('#detail-created-at').text(formattedCreatedAt);
                        $('#detail-updated-at').text(formattedUpdatedAt);
                        
                        // Store ID for edit button
                        $('#editFromDetail').data('id', data.id);
                        
                        $('#viewDetailModal').modal('show');
                    } else {
                        toastr.error(response.message, 'Error');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to load data: ' + error, 'Error');
                }
            });
        });
        
        // Edit Record
        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            $('#formModalLabel').text('Edit Data Actual Sales');
            $('#salesActualForm').attr('action', '<?= base_url('admin/sales/actual/update') ?>');
            
            // Fetch data via AJAX
            $.ajax({
                url: '<?= base_url('admin/sales/actual/get') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        var data = response.data;
                        $('#sales_id').val(data.id);
                        $('#model_no').val(data.model_no);
                        $('#class').val(data.class);
                        $('#sch_qty').val(data.sch_qty);
                        $('#act_qty').val(data.act_qty);
                        $('#prd_cd').val(data.prd_cd);
                        $('#content').val(data.content);
                        
                        // Format date to YYYY-MM-DD for input
                        var shipDate = new Date(data.shp_date);
                        var formattedDate = shipDate.toISOString().split('T')[0];
                        $('#shp_date').val(formattedDate);
                        
                        $('#formModal').modal('show');
                    } else {
                        toastr.error(response.message, 'Error');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to load data: ' + error, 'Error');
                }
            });
        });
        
        // Submit Form
        $('#salesActualForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            var actionUrl = $(this).attr('action');
            
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#saveBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
                },
                success: function(response) {
                    if (response.status) {
                        $('#formModal').modal('hide');
                        
                        // Enhanced success message
                        var action = $('#sales_id').val() ? 'diperbarui' : 'ditambahkan';
                        toastr.success(
                            'Data Sales Actual berhasil ' + action + '! <br>' + 
                            '<strong>Model:</strong> ' + $('#model_no').val() + '<br>' + 
                            '<strong>Class:</strong> ' + $('#class').val(), 
                            'Berhasil'
                        );
                        
                        setTimeout(function() {
                            location.reload(); // Reload to see changes
                        }, 1500);
                    } else {
                        toastr.error(response.message || 'Terjadi kesalahan saat menyimpan data', 'Gagal');
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = '';
                    try {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        errorMessage = jsonResponse.message || 'Terjadi kesalahan pada server';
                    } catch(e) {
                        errorMessage = 'Gagal menyimpan data: ' + error;
                    }
                    toastr.error(errorMessage, 'Error')
                },
                complete: function() {
                    $('#saveBtn').prop('disabled', false).html('Simpan');
                }
            });
        });
        
        // Delete Record
        var deleteId = null;
        $(document).on('click', '.delete-btn', function() {
            deleteId = $(this).data('id');
            $('#deleteModal').modal('show');
        });
        
        $('#confirmDelete').click(function() {
            if (deleteId) {
                $.ajax({
                    url: '<?= base_url('admin/sales/actual/delete') ?>/' + deleteId,
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function() {
                        $('#confirmDelete').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...');
                    },
                    success: function(response) {
                        $('#deleteModal').modal('hide');
                        if (response.status) {
                            // Enhanced delete success message
                            toastr.success(
                                'Data Sales Actual berhasil dihapus!<br>' +
                                'ID: ' + deleteId,
                                'Berhasil'
                            );
                            setTimeout(function() {
                                location.reload(); // Reload to see changes
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'Terjadi kesalahan saat menghapus data', 'Gagal');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#deleteModal').modal('hide');
                        var errorMessage = '';
                        try {
                            var jsonResponse = JSON.parse(xhr.responseText);
                            errorMessage = jsonResponse.message || 'Terjadi kesalahan pada server';
                        } catch(e) {
                            errorMessage = 'Gagal menghapus data: ' + error;
                        }
                        toastr.error(errorMessage, 'Error')
                    },
                    complete: function() {
                        $('#confirmDelete').prop('disabled', false).html('Hapus');
                    }
                });
            }
        });
        
        // Export Excel
        $('#exportExcelBtn').click(function() {
            window.location.href = '<?= base_url('admin/sales/actual/export') ?>';
        });
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
