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
            <button id="btn-add-planning" class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#planningFormModal"><i class="fas fa-plus"></i> Tambah Data</button>
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
                    <?php if(isset($update_values)): ?>
                        <?php foreach ($update_values as $update): ?>
                            <option value="<?= esc($update['update_value']) ?>"><?= esc($update['update_value']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-prdcode" class="form-label">Product Code</label>
                <select class="form-select form-select-sm" id="filter-prdcode">
                    <option value="">Semua Product Code</option>
                    <?php if(isset($prd_codes)): ?>
                        <?php foreach ($prd_codes as $prdcode): ?>
                            <option value="<?= esc($prdcode['prd_code']) ?>"><?= esc($prdcode['prd_code']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-model" class="form-label">Model No</label>
                <select class="form-select form-select-sm" id="filter-model">
                    <option value="">Semua Model</option>
                    <?php if(isset($model_nos)): ?>
                        <?php foreach ($model_nos as $model): ?>
                            <option value="<?= esc($model['model_no']) ?>"><?= esc($model['model_no']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-class" class="form-label">Class</label>
                <select class="form-select form-select-sm" id="filter-class">
                    <option value="">Semua Class</option>
                    <?php if(isset($classes)): ?>
                        <?php foreach ($classes as $cls): ?>
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
                        <th>Aksi</th>
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
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info btn-detail" data-id="<?= $row['id'] ?>"><i class="fas fa-eye"></i></button>
                                        <button type="button" class="btn btn-warning btn-edit" data-id="<?= $row['id'] ?>"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-danger btn-delete" data-id="<?= $row['id'] ?>"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
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
            $('#filter-section').slideToggle(150, 'linear');
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
            
            console.log('Applying filters:', {
                update: updateFilter,
                prdcode: prdcodeFilter,
                model: modelFilter,
                class: classFilter,
                startDate: startDate,
                endDate: endDate
            });
            
            // Reset semua filter
            planningTable.search('').columns().search('').draw();
            
            // Terapkan filter - gunakan filter exact match
            if (updateFilter) {
                console.log('Filtering update:', updateFilter);
                planningTable.column(0).search(updateFilter);
            }
            
            if (prdcodeFilter) {
                console.log('Filtering prdcode:', prdcodeFilter);
                planningTable.column(1).search(prdcodeFilter);
            }
            
            if (modelFilter) {
                console.log('Filtering model:', modelFilter);
                planningTable.column(2).search(modelFilter);
            }
            
            if (classFilter) {
                console.log('Filtering class:', classFilter);
                planningTable.column(3).search(classFilter);
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
        
        // ===== Event Handlers untuk operasi CRUD =====
        
        // Kalkulasi otomatis total dari input hari
        $('.day-input').on('input', function() {
            calculateTotal();
        });
        
        // Fungsi untuk menghitung total dari semua input hari
        function calculateTotal() {
            var total = 0;
            $('.day-input').each(function() {
                var value = parseFloat($(this).val()) || 0;
                total += value;
            });
            $('#total').val(total.toFixed(1));
        }
        
        // Reset form planning ketika modal ditutup
        $('#planningFormModal').on('hidden.bs.modal', function() {
            resetPlanningForm();
        });
        
        // Reset form ke nilai default
        function resetPlanningForm() {
            $('#planningForm')[0].reset();
            $('#planning-id').val('');
            $('#planningFormModalLabel').text('Tambah Data Planning');
            $('.day-input').val('0');
            calculateTotal();
        }
        
        // Ketika tombol Tambah Data diklik (modal baru)
        $('#btn-add-planning').click(function() {
            resetPlanningForm();
            $('#planningFormModalLabel').text('Tambah Data Planning');
        });
        
        // Ketika tombol Edit diklik (ambil data lalu tampilkan di modal)
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            
            // Reset form
            resetPlanningForm();
            
            // Ubah judul modal
            $('#planningFormModalLabel').text('Edit Data Planning');
            
            // Ajax request untuk mengambil data planning
            $.ajax({
                url: '<?= base_url('admin/ppic/get-planning-detail') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var data = response.data;
                        
                        // Isi form dengan data yang ada
                        $('#planning-id').val(data.id);
                        $('#update-value').val(data.update_value);
                        $('#prd-code').val(data.prd_code);
                        $('#model-no').val(data.model_no);
                        $('#class').val(data.class);
                        
                        // Isi nilai hari 1-31
                        for (var i = 1; i <= 31; i++) {
                            var dayKey = 'day_' + i;
                            var value = parseFloat(data[dayKey]) || 0;
                            $('#day-' + i).val(value.toFixed(1));
                        }
                        
                        // Isi total
                        $('#total').val(parseFloat(data.total).toFixed(1));
                        
                        // Tampilkan modal
                        $('#planningFormModal').modal('show');
                    } else {
                        showToast('error', 'Error', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showToast('error', 'Error', 'Gagal mengambil data: ' + error);
                }
            });
        });
        
        // Ketika tombol Simpan diklik pada modal form
        $('#savePlanning').click(function() {
            // Validasi form
            if (!$('#planningForm')[0].checkValidity()) {
                $('#planningForm')[0].reportValidity();
                return;
            }
            
            // Persiapan data untuk dikirim
            var formData = $('#planningForm').serialize();
            
            // URL untuk create atau update
            var url = $('#planning-id').val() ? 
                '<?= base_url('admin/ppic/update-planning') ?>/' + $('#planning-id').val() : 
                '<?= base_url('admin/ppic/add-planning') ?>';
            
            // Ajax request untuk menyimpan data
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#planningFormModal').modal('hide');
                        showToast('success', 'Sukses', response.message);
                        // Reload halaman untuk refresh data
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast('error', 'Error', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showToast('error', 'Error', 'Gagal menyimpan data: ' + error);
                }
            });
        });
        
        // Ketika tombol Detail diklik
        $(document).on('click', '.btn-detail', function() {
            var id = $(this).data('id');
            
            // Ajax request untuk mengambil detail planning
            $.ajax({
                url: '<?= base_url('admin/ppic/get-planning-detail') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var data = response.data;
                        
                        // Isi detail data
                        $('#detail-update-value').text(data.update_value);
                        $('#detail-prd-code').text(data.prd_code);
                        $('#detail-model-no').text(data.model_no);
                        $('#detail-class').text(data.class);
                        $('#detail-total').text(parseFloat(data.total).toFixed(1));
                        
                        // Isi nilai hari 1-31
                        var dayValues = [];
                        var labels = [];
                        
                        for (var i = 1; i <= 31; i++) {
                            var dayKey = 'day_' + i;
                            var value = parseFloat(data[dayKey]) || 0;
                            $('#detail-day-' + i).text(value.toFixed(1));
                            
                            if (value > 0) {
                                dayValues.push(value);
                                labels.push('Hari ' + i);
                            }
                        }
                        
                        // Buat grafik distribusi planning harian
                        createPlanningChart(labels, dayValues);
                        
                        // Tampilkan modal
                        $('#planningDetailModal').modal('show');
                    } else {
                        showToast('error', 'Error', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showToast('error', 'Error', 'Gagal mengambil data: ' + error);
                }
            });
        });
        
        // Ketika tombol Delete diklik
        $(document).on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            var modelNo = row.find('td:eq(2)').text();
            var prdCode = row.find('td:eq(1)').text();
            
            // Isi modal konfirmasi
            $('#delete-model-no').text(modelNo);
            $('#delete-prd-code').text(prdCode);
            $('#delete-id').val(id);
            
            // Tampilkan modal konfirmasi
            $('#deleteConfirmModal').modal('show');
        });
        
        // Ketika tombol konfirmasi hapus diklik
        $('#confirmDelete').click(function() {
            var id = $('#delete-id').val();
            
            // Ajax request untuk menghapus data
            $.ajax({
                url: '<?= base_url('admin/ppic/delete-planning') ?>/' + id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $('#deleteConfirmModal').modal('hide');
                    
                    if (response.status === 'success') {
                        showToast('success', 'Sukses', response.message);
                        // Reload halaman untuk refresh data
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast('error', 'Error', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('#deleteConfirmModal').modal('hide');
                    showToast('error', 'Error', 'Gagal menghapus data: ' + error);
                }
            });
        });
        
        // Fungsi untuk membuat grafik planning
        function createPlanningChart(labels, values) {
            // Jika sudah ada chart sebelumnya, hancurkan dulu
            if (window.planningChart instanceof Chart) {
                window.planningChart.destroy();
            }
            
            var ctx = document.getElementById('planningChart').getContext('2d');
            window.planningChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Planning Produksi',
                        data: values,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Fungsi untuk menampilkan toast notification
        function showToast(type, title, message) {
            // Cek apakah toastr sudah di-load
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000
                };
                
                switch(type) {
                    case 'success':
                        toastr.success(message, title);
                        break;
                    case 'error':
                        toastr.error(message, title);
                        break;
                    case 'warning':
                        toastr.warning(message, title);
                        break;
                    case 'info':
                        toastr.info(message, title);
                        break;
                    default:
                        console.log(title + ': ' + message);
                }
            } else {
                // Fallback jika toastr tidak tersedia
                alert(title + ': ' + message);
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
    
    /* Styling untuk tabs pada form modal */
    .nav-tabs .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    /* Styling untuk form dalam modal */
    .day-inputs {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }
    
    .day-inputs .form-group {
        margin-bottom: 10px;
    }
    
    /* Styling untuk detail modal */
    .planning-details .detail-row {
        display: flex;
        margin-bottom: 8px;
    }
    
    .planning-details .detail-label {
        font-weight: bold;
        min-width: 120px;
    }
    
    .planning-chart-container {
        height: 200px;
        margin-top: 15px;
    }
</style>

<!-- Modal Form Tambah/Edit Data Planning -->
<div class="modal fade" id="planningFormModal" tabindex="-1" aria-labelledby="planningFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="planningFormModalLabel">Tambah Data Planning</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="planningForm">
                    <input type="hidden" id="planning-id" name="id" value="">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="update-value" class="form-label">Update Value / Remark</label>
                                <input type="text" class="form-control" id="update-value" name="update_value" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="prd-code" class="form-label">Product Code</label>
                                <input type="text" class="form-control" id="prd-code" name="prd_code" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="model-no" class="form-label">Model No</label>
                                <input type="text" class="form-control" id="model-no" name="model_no" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="class" class="form-label">Class</label>
                                <input type="text" class="form-control" id="class" name="class" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Nav tabs untuk daily input -->
                    <ul class="nav nav-tabs" id="planningTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="days-1-7-tab" data-bs-toggle="tab" data-bs-target="#days-1-7" type="button" role="tab" aria-controls="days-1-7" aria-selected="true">Hari 1-7</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="days-8-14-tab" data-bs-toggle="tab" data-bs-target="#days-8-14" type="button" role="tab" aria-controls="days-8-14" aria-selected="false">Hari 8-14</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="days-15-21-tab" data-bs-toggle="tab" data-bs-target="#days-15-21" type="button" role="tab" aria-controls="days-15-21" aria-selected="false">Hari 15-21</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="days-22-31-tab" data-bs-toggle="tab" data-bs-target="#days-22-31" type="button" role="tab" aria-controls="days-22-31" aria-selected="false">Hari 22-31</button>
                        </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content mt-3">
                        <!-- Hari 1-7 -->
                        <div class="tab-pane fade show active" id="days-1-7" role="tabpanel" aria-labelledby="days-1-7-tab">
                            <div class="day-inputs">
                                <?php for ($i = 1; $i <= 7; $i++): ?>
                                <div class="form-group">
                                    <label for="day-<?= $i ?>" class="form-label">Hari <?= $i ?></label>
                                    <input type="number" step="0.1" min="0" class="form-control day-input" id="day-<?= $i ?>" name="day_<?= $i ?>" value="0">
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Hari 8-14 -->
                        <div class="tab-pane fade" id="days-8-14" role="tabpanel" aria-labelledby="days-8-14-tab">
                            <div class="day-inputs">
                                <?php for ($i = 8; $i <= 14; $i++): ?>
                                <div class="form-group">
                                    <label for="day-<?= $i ?>" class="form-label">Hari <?= $i ?></label>
                                    <input type="number" step="0.1" min="0" class="form-control day-input" id="day-<?= $i ?>" name="day_<?= $i ?>" value="0">
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Hari 15-21 -->
                        <div class="tab-pane fade" id="days-15-21" role="tabpanel" aria-labelledby="days-15-21-tab">
                            <div class="day-inputs">
                                <?php for ($i = 15; $i <= 21; $i++): ?>
                                <div class="form-group">
                                    <label for="day-<?= $i ?>" class="form-label">Hari <?= $i ?></label>
                                    <input type="number" step="0.1" min="0" class="form-control day-input" id="day-<?= $i ?>" name="day_<?= $i ?>" value="0">
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Hari 22-31 -->
                        <div class="tab-pane fade" id="days-22-31" role="tabpanel" aria-labelledby="days-22-31-tab">
                            <div class="day-inputs">
                                <?php for ($i = 22; $i <= 31; $i++): ?>
                                <div class="form-group">
                                    <label for="day-<?= $i ?>" class="form-label">Hari <?= $i ?></label>
                                    <input type="number" step="0.1" min="0" class="form-control day-input" id="day-<?= $i ?>" name="day_<?= $i ?>" value="0">
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total" class="form-label">Total</label>
                                <input type="text" class="form-control" id="total" name="total" readonly>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="savePlanning">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Planning -->
<div class="modal fade" id="planningDetailModal" tabindex="-1" aria-labelledby="planningDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title" id="planningDetailModalLabel">Detail Planning</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="planning-details">
                    <div class="detail-row">
                        <div class="detail-label">Update Value:</div>
                        <div class="detail-value" id="detail-update-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Product Code:</div>
                        <div class="detail-value" id="detail-prd-code"></div>
                    </div>
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
                
                <h6 class="mt-4 mb-3">Distribusi Planning Harian</h6>
                <div class="planning-chart-container">
                    <canvas id="planningChart"></canvas>
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
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data planning ini?</p>
                <p class="mb-0"><strong>Model No: </strong><span id="delete-model-no"></span></p>
                <p><strong>Product Code: </strong><span id="delete-prd-code"></span></p>
                <input type="hidden" id="delete-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>