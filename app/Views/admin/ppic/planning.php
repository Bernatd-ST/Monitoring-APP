<!-- File: app/Views/admin/ppic/planning.php -->

<?= $this->extend('admin/layout') ?>

<?= $this->section('page_buttons') ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importPlanningModal">
            <i class="fas fa-file-excel"></i>
            Import Excel
        </button>
        <a href="<?= base_url('admin/ppic/export-planning') ?>" class="btn btn-sm btn-outline-success ms-2">
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
        <h5 class="m-0 font-weight-bold">Planning Production</h5>
        <div>
            <button id="toggle-filters" class="btn btn-sm btn-light"><i class="fas fa-filter"></i> Filter</button>
        </div>
    </div>
    
    <!-- Filter Section (Hidden by default) -->
    <div id="filter-section" class="card-body border-bottom" style="display: none;">
        <form id="filter-form" class="row g-3">
            <div class="col-md-3">
                <label for="filter-update" class="form-label">Update Value</label>
                <select class="form-select form-select-sm" id="filter-update">
                    <option value="">Semua Update</option>
                    <?php if(isset($update_list)): ?>
                        <?php foreach ($update_list as $update): ?>
                            <option value="<?= esc($update['update_value']) ?>"><?= esc($update['update_value']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-prdcode" class="form-label">Product Code</label>
                <select class="form-select form-select-sm" id="filter-prdcode">
                    <option value="">Semua Product Code</option>
                    <?php if(isset($prdcode_list)): ?>
                        <?php foreach ($prdcode_list as $prdcode): ?>
                            <option value="<?= esc($prdcode['prd_code']) ?>"><?= esc($prdcode['prd_code']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
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
            <table id="planning-table" class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Update</th>
                        <th>Prd Code</th>
                        <th>ModelNo</th>
                        <th>Class</th>
                        <!-- Kolom schedule -->
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <th class="date-column"><?= $i ?></th>
                        <?php endfor; ?>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($planning_data) && !empty($planning_data)): ?>
                        <?php foreach ($planning_data as $row): ?>
                            <tr>
                                <td><?= esc($row['update_value']) ?></td>
                                <td><?= esc($row['prd_code']) ?></td>
                                <td><?= esc($row['model_no']) ?></td>
                                <td><?= esc($row['class']) ?></td>
                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                    <td class="text-end"><?= esc($row['day_' . $i]) ?></td>
                                <?php endfor; ?>
                                <td class="text-end"><?= esc($row['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="36" class="text-center">Tidak ada data untuk ditampilkan. Silakan import file Excel.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importPlanningModal" tabindex="-1" aria-labelledby="importPlanningModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importPlanningModalLabel">Import Data Planning Production</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= base_url('admin/ppic/upload-planning') ?>" method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label for="planning_file" class="form-label">Pilih File Excel (.xlsx, .xls)</label>
            <input class="form-control" type="file" id="planning_file" name="planning_file" accept=".xlsx, .xls" required>
            <div class="form-text">Format: Kolom B=Model No, C=Class, D-AH=Schedule harian (1-31)</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
        
        // Inisialisasi DataTables
        // Menghapus baris 'no data' statis sebelum inisialisasi DataTables jika ada
        if($('#planning-table tbody tr').length === 1 && $('#planning-table tbody tr td').attr('colspan')) {
            $('#planning-table tbody tr').remove();
        }
        
        // Hitung jumlah kolom dari header
        var columnCount = $('#planning-table thead th').length;
        
        // Inisialisasi DataTables
        var planningTable = $('#planning-table').DataTable({
            responsive: false,  // Nonaktifkan responsive agar semua kolom terlihat
            scrollX: true,      // Aktifkan scrolling horizontal
            fixedColumns: {     // Aktifkan fixed columns
                leftColumns: 4,   // Update, Prd Code, Model No, dan Class tetap terlihat
                rightColumns: 1   // Total tetap terlihat
            },
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
            order: [[2, 'asc']], // Kolom index 2 = Model No
            language: {
                search: "Pencarian:",
                lengthMenu: "Tampilkan _MENU_ data",
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
                { className: "text-nowrap", targets: [0, 1, 2, 3] },  // Update, Prd Code, ModelNo, Class tidak wrap
                { className: "text-start", targets: [0, 1, 2, 3] },   // Update, Prd Code, ModelNo, Class rata kiri
                { className: "text-end", targets: "_all" }            // Semua kolom lain rata kanan
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
            $('#filter-update').val('').trigger('change');
            $('#filter-prdcode').val('').trigger('change');
            $('#filter-model').val('').trigger('change');
            $('#filter-class').val('').trigger('change');
            $('#filter-daterange').val('');
            $('#filter-date-start').val('');
            $('#filter-date-end').val('');
            planningTable.search('').columns().search('').draw();
        });
        
        // Inisialisasi daterangepicker
        $('#filter-daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD',
                cancelLabel: 'Batal',
                applyLabel: 'Terapkan',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
            },
            minDate: 1,
            maxDate: 31,
            startDate: 1,
            endDate: 31,
            opens: 'right',
            showDropdowns: false,
            showCustomRangeLabel: false,
            alwaysShowCalendars: false,
            autoApply: true,
            linkedCalendars: false
        });
        
        // Update inputan tanggal saat range dipilih
        $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val('Tanggal ' + picker.startDate.format('D') + ' - ' + picker.endDate.format('D'));
            $('#filter-date-start').val(picker.startDate.format('D'));
            $('#filter-date-end').val(picker.endDate.format('D'));
        });
        
        $('#filter-daterange').on('cancel.daterangepicker', function() {
            $(this).val('');
            $('#filter-date-start').val('');
            $('#filter-date-end').val('');
        });
        
        function applyCustomFilters() {
            var updateFilter = $('#filter-update').val();
            var prdcodeFilter = $('#filter-prdcode').val();
            var modelFilter = $('#filter-model').val();
            var classFilter = $('#filter-class').val();
            var startDate = $('#filter-date-start').val();
            var endDate = $('#filter-date-end').val();
            
            // Reset semua filter
            planningTable.search('').columns().search('').draw();
            
            // Terapkan filter - gunakan filter exact match
            if (updateFilter) {
                planningTable.column(0).search('^' + $.fn.dataTable.util.escapeRegex(updateFilter) + '$', true, false);
            }
            
            if (prdcodeFilter) {
                planningTable.column(1).search('^' + $.fn.dataTable.util.escapeRegex(prdcodeFilter) + '$', true, false);
            }
            
            if (modelFilter) {
                planningTable.column(2).search('^' + $.fn.dataTable.util.escapeRegex(modelFilter) + '$', true, false);
            }
            
            if (classFilter) {
                planningTable.column(3).search('^' + $.fn.dataTable.util.escapeRegex(classFilter) + '$', true, false);
            }
            
            // Filter rentang tanggal - tampilkan baris yang memiliki nilai > 0 pada rentang kolom tanggal
            if (startDate && endDate) {
                var start = parseInt(startDate);
                var end = parseInt(endDate);
                
                // Buat Custom Filter Function untuk DataTables
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var hasValue = false;
                    
                    // Kolom dimulai dari index 4 (setelah Update, Prd Code, ModelNo, Class)
                    // dan berakhir di 4+30 = 34 (untuk hari 1-31)
                    for (var i = start + 3; i <= end + 3; i++) {
                        var colValue = parseFloat(data[i]) || 0;
                        if (colValue > 0) {
                            hasValue = true;
                            break;
                        }
                    }
                    
                    return hasValue;
                });
            }
            
            planningTable.draw();
            
            // Hapus filter custom setelah digunakan
            if (startDate && endDate) {
                $.fn.dataTable.ext.search.pop();
            }
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
    #planning-table th:last-child, 
    #planning-table td:last-child {
        font-weight: bold;
        border-left: 2px solid #dee2e6;
        background-color: #f8f9fa !important;
    }
</style>

<?= $this->endSection() ?>