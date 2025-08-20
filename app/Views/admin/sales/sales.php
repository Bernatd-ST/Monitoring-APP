<!-- File: app/Views/admin/sales.php -->

<?= $this->extend('admin/layout') ?>

<?= $this->section('page_buttons') ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-excel"></i>
            Import Excel
        </button>
        <button type="button" class="btn btn-sm btn-outline-success me-2" id="export-btn">
            <i class="fas fa-file-download"></i>
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
        <h5 class="m-0 font-weight-bold">Data Sales</h5>
        <div>
            <?php if(session()->get('user_role') === 'admin'): ?>
            <button type="button" class="btn btn-sm btn-light me-2" id="add-btn">
                <i class="fas fa-plus"></i>
                Tambah Data
            </button>
            <?php endif; ?>
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
                        <?php if(session()->get('user_role') === 'admin'): ?>
                        <th class="freeze-column-right text-center">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
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
                                    <td><?= esc($row["schedule_{$i}"]) ?? 0 ?></td>
                                <?php endfor; ?>
                                <td><strong><?= esc($row['total']) ?></strong></td>
                                <?php if(session()->get('user_role') === 'admin'): ?>
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
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="34" class="text-center">Tidak ada data untuk ditampilkan. Silakan import file Excel.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Sales Data -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title" id="formModalLabel">Tambah Data Sales</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="salesForm">
          <input type="hidden" id="sales_id" name="id">
          <div class="row mb-4">
            <div class="col-md-6">
              <label for="model_no" class="form-label">Model No <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="model_no" name="model_no" required>
            </div>
            <div class="col-md-6">
              <label for="class" class="form-label">Class <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="class" name="class" required>
            </div>
          </div>
          
          <div class="card shadow-sm mb-3">
            <div class="card-header bg-light">
              <h6 class="fw-bold mb-0">Schedule Data Harian</h6>
            </div>
            <div class="card-body">
              <div class="row g-2">
                <!-- Baris 1: Tanggal 1-6 -->
                <div class="col-12 mb-2">
                  <div class="row g-1">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                      <div class="col-md-2">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text"><?= $i ?></span>
                          <input type="number" class="form-control schedule-input" 
                                id="schedule_<?= $i ?>" name="schedule_<?= $i ?>" 
                                min="0" value="0" required>
                        </div>
                      </div>
                    <?php endfor; ?>
                  </div>
                </div>
                
                <!-- Baris 2: Tanggal 7-12 -->
                <div class="col-12 mb-2">
                  <div class="row g-1">
                    <?php for ($i = 7; $i <= 12; $i++): ?>
                      <div class="col-md-2">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text"><?= $i ?></span>
                          <input type="number" class="form-control schedule-input" 
                                id="schedule_<?= $i ?>" name="schedule_<?= $i ?>" 
                                min="0" value="0" required>
                        </div>
                      </div>
                    <?php endfor; ?>
                  </div>
                </div>
                
                <!-- Baris 3: Tanggal 13-18 -->
                <div class="col-12 mb-2">
                  <div class="row g-1">
                    <?php for ($i = 13; $i <= 18; $i++): ?>
                      <div class="col-md-2">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text"><?= $i ?></span>
                          <input type="number" class="form-control schedule-input" 
                                id="schedule_<?= $i ?>" name="schedule_<?= $i ?>" 
                                min="0" value="0" required>
                        </div>
                      </div>
                    <?php endfor; ?>
                  </div>
                </div>
                
                <!-- Baris 4: Tanggal 19-24 -->
                <div class="col-12 mb-2">
                  <div class="row g-1">
                    <?php for ($i = 19; $i <= 24; $i++): ?>
                      <div class="col-md-2">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text"><?= $i ?></span>
                          <input type="number" class="form-control schedule-input" 
                                id="schedule_<?= $i ?>" name="schedule_<?= $i ?>" 
                                min="0" value="0" required>
                        </div>
                      </div>
                    <?php endfor; ?>
                  </div>
                </div>
                
                <!-- Baris 5: Tanggal 25-30 -->
                <div class="col-12 mb-2">
                  <div class="row g-1">
                    <?php for ($i = 25; $i <= 30; $i++): ?>
                      <div class="col-md-2">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text"><?= $i ?></span>
                          <input type="number" class="form-control schedule-input" 
                                id="schedule_<?= $i ?>" name="schedule_<?= $i ?>" 
                                min="0" value="0" required>
                        </div>
                      </div>
                    <?php endfor; ?>
                  </div>
                </div>
                
                <!-- Baris 6: Tanggal 31 -->
                <div class="col-12">
                  <div class="row g-1">
                    <div class="col-md-2">
                      <div class="input-group input-group-sm">
                        <span class="input-group-text">31</span>
                        <input type="number" class="form-control schedule-input" 
                              id="schedule_31" name="schedule_31" 
                              min="0" value="0" required>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="total" class="form-label">Total</label>
                <input type="number" class="form-control form-control-lg fw-bold" id="total" name="total" readonly>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
        <button type="button" id="saveBtn" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal View Detail -->
<div class="modal fade" id="viewDetailModal" tabindex="-1" aria-labelledby="viewDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-gradient-info text-white">
        <h5 class="modal-title" id="viewDetailModalLabel">Detail Sales Planning</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-4">
            <h6 class="fw-bold">Model No</h6>
            <p id="detail-model-no" class="fs-5"></p>
          </div>
          <div class="col-md-4">
            <h6 class="fw-bold">Class</h6>
            <p id="detail-class" class="fs-5"></p>
          </div>
          <div class="col-md-4">
            <h6 class="fw-bold">Total</h6>
            <p id="detail-total" class="fs-5 fw-bold"></p>
          </div>
        </div>
        
        <!-- Chart.js Visualization -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Distribusi Planning Harian</h6>
              </div>
              <div class="card-body" style="min-height: 300px;">
                <canvas id="scheduleChart" height="250"></canvas>
                <div id="noDataMessage" style="display: none; text-align: center; padding: 50px 0;">
                  <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                  <p class="text-muted">Tidak ada data planning dengan nilai lebih dari 0.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Table Data -->
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Data Tabel</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive" style="max-width: 100%; overflow-x: auto;">
                  <table class="table table-sm table-bordered table-hover" style="min-width: 1000px;">
                    <thead class="table-light">
                      <tr>
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                          <th class="text-center" style="min-width: 60px;"><?= $i ?></th>
                        <?php endfor; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <tr id="detail-schedule-row">
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                          <td class="text-center" id="detail-schedule-<?= $i ?>">0</td>
                        <?php endfor; ?>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
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
        <p>Apakah Anda yakin ingin menghapus data Sales dengan:</p>
        <p><strong>Model:</strong> <span id="delete-model"></span></p>
        <p><strong>Class:</strong> <span id="delete-class"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
      </div>
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

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom scripts for this page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize toastr options
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 5000,
            showMethod: "fadeIn",
            hideMethod: "fadeOut"
        };
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
        
        // Hitung jumlah kolom di tabel untuk digunakan di DataTables
        var columnCount = $('#sales-table thead th').length;
        console.log('Jumlah kolom di tabel:', columnCount);
        
        // Inisialisasi DataTables dengan perbaikan untuk kasus data kosong
        try {
            // Hapus dulu HTML no-data agar tidak bentrok dengan DataTables
            if ($('#sales-table tbody tr td[colspan]').length) {
                $('#sales-table tbody').html('');
            }
            
            var salesTable = $('#sales-table').DataTable({
                responsive: false, // Nonaktifkan responsive agar semua kolom terlihat
                scrollX: true,     // Aktifkan scrolling horizontal
                fixedColumns: {    // Aktifkan fixed columns
                    left: 2,      // Model No dan Class tetap terlihat saat scroll
                    right: 1      // Total tetap terlihat saat scroll
                },
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Tidak ada data untuk ditampilkan. Silakan import file Excel.",
                    emptyTable: "Tidak ada data untuk ditampilkan. Silakan import file Excel.",
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
                ],
                // Override template untuk pesan kosong dengan colspan yang benar
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                // Perbaikan untuk error columns
                drawCallback: function(settings) {
                    // Force update colspan for empty message
                    $('.dataTables_empty').attr('colspan', columnCount);
                }
            });
            
            // Tambahan untuk memastikan colspan selalu benar
            // Ini akan dijalankan setiap kali tabel di-redraw, misalnya saat filter
            salesTable.on('draw', function() {
                $('.dataTables_empty').attr('colspan', columnCount);
            });
            
            console.log('DataTables berhasil diinisialisasi');
        } catch (e) {
            console.error('Error saat inisialisasi DataTables:', e);
            alert('Terjadi error saat memuat tabel. Silakan refresh halaman.');
        }
        
        // Toggle filter section
        $('#toggle-filters').click(function() {
            $('#filter-section').slideToggle();
        });
        
        // Calculate total when schedule inputs change
        $(document).on('input', '.schedule-input', function() {
            calculateTotal();
        });
        
        function calculateTotal() {
            let total = 0;
            for (let i = 1; i <= 31; i++) {
                const value = parseInt($('#schedule_' + i).val()) || 0;
                total += value;
            }
            $('#total').val(total);
        }
        
        // Form submission handler
        $('#saveBtn').click(function() {
            // Validate form
            if (!$('#salesForm')[0].checkValidity()) {
                $('#salesForm')[0].reportValidity();
                return;
            }
            
            const id = $('#sales_id').val();
            const url = id ? '/admin/sales/update' : '/admin/sales/add';
            
            $.ajax({
                url: url,
                type: 'POST',
                data: $('#salesForm').serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#formModal').modal('hide');
                    
                    if (response.status) {
                        toastr.success(response.message);
                        // Reload table after successful submission
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    $('#formModal').modal('hide');
                    toastr.error('Terjadi kesalahan saat menyimpan data');
                }
            });
        });
        
        // Add button click
        $('#add-btn').click(function() {
            $('#formModalLabel').text('Tambah Data Sales');
            $('#salesForm')[0].reset();
            $('#sales_id').val('');
            // Reset all schedule inputs to 0
            for (let i = 1; i <= 31; i++) {
                $('#schedule_' + i).val(0);
            }
            $('#total').val(0);
            $('#formModal').modal('show');
        });
        
        // Edit button click
        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            $('#formModalLabel').text('Edit Data Sales');
            
            // Fetch data by ID
            $.ajax({
                url: '/admin/sales/get/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        const data = response.data;
                        $('#sales_id').val(data.id);
                        $('#model_no').val(data.model_no);
                        $('#class').val(data.class);
                        
                        // Set schedule values
                        let total = 0;
                        for (let i = 1; i <= 31; i++) {
                            const value = parseInt(data['schedule_' + i]) || 0;
                            $('#schedule_' + i).val(value);
                            total += value;
                        }
                        $('#total').val(total);
                        
                        $('#formModal').modal('show');
                    } else {
                        toastr.error('Gagal mengambil data');
                    }
                },
                error: function() {
                    toastr.error('Terjadi kesalahan saat mengambil data');
                }
            });
        });
        
        // View detail button click
        $(document).on('click', '.view-btn', function() {
            const id = $(this).data('id');
            
            // Fetch data by ID
            $.ajax({
                url: '/admin/sales/get/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        const data = response.data;
                        $('#detail-model-no').text(data.model_no);
                        $('#detail-class').text(data.class);
                        
                        // Set schedule values
                        const scheduleData = [];
                        const labels = [];
                        let hasNonZeroValues = false;
                        
                        for (let i = 1; i <= 31; i++) {
                            const value = parseInt(data['schedule_' + i]) || 0;
                            $('#detail-schedule-' + i).text(value);
                            scheduleData.push(value);
                            labels.push(i.toString());
                            
                            if (value > 0) {
                                hasNonZeroValues = true;
                            }
                        }
                        $('#detail-total').text(data.total);
                        
                        // Store ID for edit button
                        $('#detail-edit-btn').data('id', data.id);
                        
                        // Create Chart.js visualization
                        createScheduleChart(labels, scheduleData, data.model_no);
                        
                        $('#viewDetailModal').modal('show');
                    } else {
                        toastr.error('Gagal mengambil data');
                    }
                },
                error: function() {
                    toastr.error('Terjadi kesalahan saat mengambil data');
                }
            });
        });
        
        // Function to create Chart.js visualization
        function createScheduleChart(labels, data, modelNo) {
            // Destroy previous chart instance if exists
            if (window.scheduleChart instanceof Chart) {
                window.scheduleChart.destroy();
            }
            
            // Filter out days with zero values
            const filteredData = [];
            const filteredLabels = [];
            
            for (let i = 0; i < data.length; i++) {
                if (data[i] > 0) {
                    filteredData.push(data[i]);
                    filteredLabels.push('Hari ' + labels[i]);
                }
            }
            
            // If no data with values > 0, show a message
            if (filteredData.length === 0) {
                document.getElementById('scheduleChart').style.display = 'none';
                document.getElementById('noDataMessage').style.display = 'block';
                return;
            } else {
                document.getElementById('scheduleChart').style.display = 'block';
                document.getElementById('noDataMessage').style.display = 'none';
            }
            
            const ctx = document.getElementById('scheduleChart').getContext('2d');
            
            window.scheduleChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: filteredLabels,
                    datasets: [{
                        label: 'Planning Sales',
                        data: filteredData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribusi Planning Harian',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
                                },
                                label: function(context) {
                                    return 'Planning Sales: ' + context.raw;
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 10,
                            cornerRadius: 4,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            title: {
                                display: false
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
        
        // Detail edit button click
        $('#detail-edit-btn').click(function() {
            const id = $(this).data('id');
            $('#viewDetailModal').modal('hide');
            
            // Trigger edit with delay to allow modal to close
            setTimeout(function() {
                $('.edit-btn[data-id="' + id + '"]').click();
            }, 500);
        });
        
        // Delete button click
        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            const model = $(this).closest('tr').find('td:eq(0)').text();
            const cls = $(this).closest('tr').find('td:eq(1)').text();
            
            $('#delete-model').text(model);
            $('#delete-class').text(cls);
            
            $('#confirmDelete').data('id', id);
            $('#deleteModal').modal('show');
        });
        
        // Confirm delete
        $('#confirmDelete').click(function() {
            const id = $(this).data('id');
            
            $.ajax({
                url: '/admin/sales/delete/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    
                    if (response.status) {
                        toastr.success(response.message);
                        // Reload table after successful deletion
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    $('#deleteModal').modal('hide');
                    toastr.error('Gagal menghapus data');
                }
            });
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
        
        // Event handler untuk tombol Export Excel
        $('#export-btn').click(function() {
            window.location.href = '/admin/sales/export';
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
    .card-header.bg-gradient-info {
        background: linear-gradient(to right, #36b9cc, #1a8a9e);
    }
    .freeze-column-right {
        position: sticky;
        right: 0;
        background-color: #fff;
        z-index: 1;
        box-shadow: -2px 0 5px rgba(0,0,0,0.1);
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

<!-- Modal for Add/Edit Sales Data -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="formModalLabel">Tambah Data Sales</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="salesForm">
                    <input type="hidden" id="sales_id" name="id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="model_no" class="form-label">Model No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="model_no" name="model_no" required>
                        </div>
                        <div class="col-md-6">
                            <label for="class" class="form-label">Class <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="class" name="class" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <ul class="nav nav-tabs" id="scheduleTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="days-1-10-tab" data-bs-toggle="tab" data-bs-target="#days-1-10" type="button" role="tab">Tanggal 1-10</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="days-11-20-tab" data-bs-toggle="tab" data-bs-target="#days-11-20" type="button" role="tab">Tanggal 11-20</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="days-21-31-tab" data-bs-toggle="tab" data-bs-target="#days-21-31" type="button" role="tab">Tanggal 21-31</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content pt-3" id="scheduleTabsContent">
                                <!-- Tab 1-10 -->
                                <div class="tab-pane fade show active" id="days-1-10" role="tabpanel">
                                    <div class="row">
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <div class="col-md-3 mb-3">
                                                <label for="schedule_<?= $i ?>" class="form-label">Tanggal <?= $i ?></label>
                                                <input type="number" class="form-control schedule-input" id="schedule_<?= $i ?>" name="schedule_<?= $i ?>" min="0" value="0">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <!-- Tab 11-20 -->
                                <div class="tab-pane fade" id="days-11-20" role="tabpanel">
                                    <div class="row">
                                        <?php for ($i = 11; $i <= 20; $i++): ?>
                                            <div class="col-md-3 mb-3">
                                                <label for="schedule_<?= $i ?>" class="form-label">Tanggal <?= $i ?></label>
                                                <input type="number" class="form-control schedule-input" id="schedule_<?= $i ?>" name="schedule_<?= $i ?>" min="0" value="0">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <!-- Tab 21-31 -->
                                <div class="tab-pane fade" id="days-21-31" role="tabpanel">
                                    <div class="row">
                                        <?php for ($i = 21; $i <= 31; $i++): ?>
                                            <div class="col-md-3 mb-3">
                                                <label for="schedule_<?= $i ?>" class="form-label">Tanggal <?= $i ?></label>
                                                <input type="number" class="form-control schedule-input" id="schedule_<?= $i ?>" name="schedule_<?= $i ?>" min="0" value="0">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="total_display" class="form-label">Total</label>
                            <input type="text" class="form-control" id="total_display" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data ini?</p>
                <p>Model: <span id="delete-model"></span></p>
                <p>Class: <span id="delete-class"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>