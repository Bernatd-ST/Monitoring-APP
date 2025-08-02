<?= $this->extend('admin/layout') ?>

<?= $this->section('styles') ?>
<!-- Custom styles for this page -->
<link href="<?= base_url('vendor/datatables/dataTables.bootstrap4.min.css') ?>" rel="stylesheet">
<style>
    /* Styling untuk tabel dengan scroll horizontal */
    .dataTables_wrapper {
        width: 100%;
        overflow: hidden;
    }
    
    .table-responsive {
        position: relative;
        overflow-x: auto;
    }
    
    /* Membuat container khusus untuk tabel dengan posisi relatif */
    .table-container {
        position: relative;
    }
    
    /* Styling untuk kolom sticky */
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
    
    /* Kolom No dan Model No harus berurutan untuk sticky */
    #bom-table th:nth-child(1),
    #bom-table td:nth-child(1) {
        position: sticky;
        left: 0;
        z-index: 10;
    }
    
    #bom-table th:nth-child(2),
    #bom-table td:nth-child(2) {
        position: sticky;
        left: 40px; /* Sesuaikan dengan lebar kolom No */
        z-index: 10;
    }
    
    #bom-table thead th:nth-child(1),
    #bom-table thead th:nth-child(2) {
        z-index: 11;
        background-color: #212529;
    }
    
    #bom-table tbody td:nth-child(1),
    #bom-table tbody td:nth-child(2) {
        background-color: white;
    }
    
    /* Styling untuk kolom Actions di sebelah kanan */
    .freeze-column-right {
        position: sticky;
        right: 0;
        z-index: 10;
        background-color: white;
        box-shadow: -2px 0 5px -2px rgba(0,0,0,0.2);
        min-width: 120px; /* Memastikan lebar minimum untuk tombol action */
        width: 120px; /* Lebar tetap untuk kolom action */
    }
    
    .table-dark .freeze-column-right {
        background-color: #212529;
        z-index: 11;
    }
    
    #bom-table th:last-child,
    #bom-table td:last-child {
        position: sticky;
        right: 0;
        z-index: 10;
        min-width: 120px; /* Memastikan lebar minimum untuk tombol action */
        width: 120px; /* Lebar tetap untuk kolom action */
    }
    
    #bom-table thead th:last-child {
        z-index: 11;
        background-color: #212529;
    }
    
    #bom-table tbody td:last-child {
        background-color: white;
    }
    
    /* Memastikan tombol action selalu terlihat */
    .table-container {
        position: relative;
        overflow: hidden; /* Mencegah scroll horizontal pada container */
    }
    
    .table-responsive {
        margin-right: 0; /* Menghilangkan margin yang mungkin menyebabkan masalah */
    }
    
    /* Meningkatkan visibilitas kolom action */
    #bom-table th:last-child,
    #bom-table td:last-child {
        right: 0;
        box-shadow: -5px 0 10px -5px rgba(0,0,0,0.3);
        background-color: white !important; /* Memastikan background selalu putih */
    }
    
    /* Class khusus untuk memastikan kolom action selalu terlihat */
    #bom-table th.always-visible,
    #bom-table td.always-visible {
        position: absolute;
        right: 0;
        z-index: 20;
        box-shadow: -8px 0 15px -5px rgba(0,0,0,0.4);
    }
    
    #bom-table thead th:last-child {
        background-color: #212529 !important; /* Memastikan header tetap gelap */
    }
    
    /* Memperbaiki tampilan pada layar kecil */
    @media (max-width: 992px) {
        .action-buttons .btn {
            padding: 0.2rem 0.4rem; /* Tombol sedikit lebih kecil pada layar kecil */
            font-size: 0.8rem;
        }
    }
    
    /* Styling khusus untuk tombol action */
    .action-buttons {
        padding: 0.5rem !important;
        text-align: center;
        white-space: nowrap;
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 1px;
    }
    
    /* Memastikan tombol action tetap terlihat saat hover */
    #bom-table tbody tr:hover td.action-buttons,
    #bom-table tbody tr.hovered td.action-buttons {
        background-color: #f2f2f2;
    }
    
    /* Styling untuk hover pada baris */
    #bom-table tbody tr:hover td,
    #bom-table tbody tr.hovered td {
        background-color: #f2f2f2;
    }
    
    #bom-table tbody tr:hover td:nth-child(1),
    #bom-table tbody tr:hover td:nth-child(2),
    #bom-table tbody tr:hover td:last-child,
    #bom-table tbody tr.hovered td:nth-child(1),
    #bom-table tbody tr.hovered td:nth-child(2),
    #bom-table tbody tr.hovered td:last-child {
        background-color: #f2f2f2;
    }
    
    /* Styling untuk card header gradient */
    .card-header.bg-gradient-primary {
        background: linear-gradient(to right, #4e73df, #224abe);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_buttons') ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-excel"></i>
            Import Excel
        </button>
        <a href="<?= base_url('admin/material/export-bom') ?>" class="btn btn-sm btn-outline-success ms-2">
            <i class="fas fa-download"></i>
            Export Excel
        </a>
    </div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Notification Messages ditangani oleh Toastr di layout.php -->
    
    <div class="card shadow">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold">Bill of Material (BOM)</h5>
            <div>
                <button type="button" class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus"></i> Tambah BOM
                </button>
                <button id="toggleFilter" class="btn btn-sm btn-light">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="card-body border-bottom" id="filterSection" style="display:none;">
            <form id="filterForm" method="get" action="<?= base_url('admin/material/bom') ?>" class="row g-3">
                <div class="col-md-3">
                    <label for="filterModel" class="form-label">Model No</label>
                    <select class="form-control select2" id="filterModel" name="model_no">
                        <option value="">All</option>
                        <?php 
                        $models = [];
                        foreach ($bom_data as $bom) {
                            if (!in_array($bom['model_no'], $models) && !empty($bom['model_no'])) {
                                $models[] = $bom['model_no'];
                                $selected = (isset($filter['model_no']) && $filter['model_no'] == $bom['model_no']) ? 'selected' : '';
                                echo '<option value="' . $bom['model_no'] . '" ' . $selected . '>' . $bom['model_no'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterClass" class="form-label">Class</label>
                    <select class="form-control select2" id="filterClass" name="class">
                        <option value="">All</option>
                        <?php 
                        $classes = [];
                        foreach ($bom_data as $bom) {
                            if (!in_array($bom['class'], $classes) && !empty($bom['class'])) {
                                $classes[] = $bom['class'];
                                $selected = (isset($filter['class']) && $filter['class'] == $bom['class']) ? 'selected' : '';
                                echo '<option value="' . $bom['class'] . '" ' . $selected . '>' . $bom['class'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-h-class" class="form-label">H Class</label>
                    <select class="form-control select2" id="filter-h-class" name="h_class">
                        <option value="">All</option>
                        <?php 
                        $h_classes = [];
                        foreach ($bom_data as $bom) {
                            if (!in_array($bom['h_class'], $h_classes) && !empty($bom['h_class'])) {
                                $h_classes[] = $bom['h_class'];
                                $selected = (isset($filter['h_class']) && $filter['h_class'] == $bom['h_class']) ? 'selected' : '';
                                echo '<option value="' . $bom['h_class'] . '" ' . $selected . '>' . $bom['h_class'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-prd-code" class="form-label">PRD Code</label>
                    <select class="form-control select2" id="filter-prd-code" name="prd_code">
                        <option value="">All</option>
                        <?php 
                        $prd_codes = [];
                        foreach ($bom_data as $bom) {
                            if (!in_array($bom['prd_code'], $prd_codes) && !empty($bom['prd_code'])) {
                                $prd_codes[] = $bom['prd_code'];
                                $selected = (isset($filter['prd_code']) && $filter['prd_code'] == $bom['prd_code']) ? 'selected' : '';
                                echo '<option value="' . $bom['prd_code'] . '" ' . $selected . '>' . $bom['prd_code'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Terapkan
                        </button>
                        <a href="<?= base_url('admin/material/bom') ?>" class="btn btn-secondary ms-2">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- BOM Data Table -->
        <div class="card-body">
            <div class="table-container">
                <div class="table-responsive" style="width: 100%; overflow-x: auto;">
                    <table class="table table-striped table-bordered table-hover" id="bom-table" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th class="freeze-column">No</th>
                            <th class="freeze-column">Model No</th>
                            <th>H Class</th>
                            <th>Qty Assy</th>
                            <th>Part No</th>
                            <th>Description</th>
                            <th>PRD Code</th>
                            <th>Class</th>
                            <th>Upd Date</th>
                            <th class="freeze-column-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($bom_data as $bom): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $bom['model_no'] ?></td>
                            <td><?= $bom['h_class'] ?></td>
                            <td><?= $bom['qty_assy'] ?></td>
                            <td><?= $bom['part_no'] ?></td>
                            <td><?= $bom['description'] ?></td>
                            <td><?= $bom['prd_code'] ?></td>
                            <td><?= $bom['class'] ?></td>
                            <td><?= $bom['upd_date'] ?></td>
                            <td class="action-buttons">
                                <div class="d-flex justify-content-center">
                                    <button type="button" class="btn btn-info btn-sm view-btn me-1" data-bs-id="<?= $bom['id'] ?>" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm edit-btn me-1" data-bs-id="<?= $bom['id'] ?>" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn" data-bs-id="<?= $bom['id'] ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import BOM from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/admin/material/import-bom" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Excel File</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx" required>
                        <div class="form-text">Format file harus sesuai dengan template Excel BOM.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewModalLabel"><i class="fas fa-info-circle me-2"></i>BOM Detail</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th class="bg-light" width="30%">Model No</th>
                                <td id="view-model-no" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">H Class</th>
                                <td id="view-h-class" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Qty Assy</th>
                                <td id="view-qty-assy" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Part No</th>
                                <td id="view-part-no" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Description</th>
                                <td id="view-description" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">PRD Code</th>
                                <td id="view-prd-code" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Class</th>
                                <td id="view-class" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Update Date</th>
                                <td id="view-upd-date" class="fw-bold"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editModalLabel"><i class="fas fa-edit me-2"></i>Edit BOM</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBomForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-model-no" class="form-label">Model No</label>
                                <input type="text" class="form-control" id="edit-model-no" name="model_no" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-h-class" class="form-label">H Class</label>
                                <input type="text" class="form-control" id="edit-h-class" name="h_class">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-qty-assy" class="form-label">Qty Assy</label>
                                <input type="number" class="form-control" id="edit-qty-assy" name="qty_assy">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-part-no" class="form-label">Part No</label>
                                <input type="text" class="form-control" id="edit-part-no" name="part_no" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit-description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit-description" name="description" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit-prd-code" class="form-label">PRD Code</label>
                                <input type="text" class="form-control" id="edit-prd-code" name="prd_code">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit-class" class="form-label">Class</label>
                                <input type="text" class="form-control" id="edit-class" name="class">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit-upd-date" class="form-label">Update Date</label>
                                <input type="date" class="form-control" id="edit-upd-date" name="upd_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add BOM Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addModalLabel"><i class="fas fa-plus me-2"></i>Add New BOM</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addBomForm">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add-model-no" class="form-label">Model No</label>
                                <input type="text" class="form-control" id="add-model-no" name="model_no" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add-h-class" class="form-label">H Class</label>
                                <input type="text" class="form-control" id="add-h-class" name="h_class">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add-qty-assy" class="form-label">Qty Assy</label>
                                <input type="number" class="form-control" id="add-qty-assy" name="qty_assy">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add-part-no" class="form-label">Part No</label>
                                <input type="text" class="form-control" id="add-part-no" name="part_no" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add-description" class="form-label">Description</label>
                                <textarea class="form-control" id="add-description" name="description" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="add-prd-code" class="form-label">PRD Code</label>
                                <input type="text" class="form-control" id="add-prd-code" name="prd_code">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="add-class" class="form-label">Class</label>
                                <input type="text" class="form-control" id="add-class" name="class">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="add-upd-date" class="form-label">Update Date</label>
                                <input type="date" class="form-control" id="add-upd-date" name="upd_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this BOM data?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Fungsi untuk memastikan kolom fixed tetap terlihat dengan benar
function adjustFixedColumns() {
    // Mengatur ulang posisi kolom action
    const tableWidth = $('#bom-table').width();
    const containerWidth = $('.table-responsive').width();
    
    // Jika tabel lebih lebar dari container, pastikan kolom action tetap terlihat
    if (tableWidth > containerWidth) {
        // Pastikan kolom action tetap terlihat dengan menambahkan class khusus
        $('#bom-table th:last-child, #bom-table td:last-child').addClass('always-visible');
    } else {
        $('#bom-table th:last-child, #bom-table td:last-child').removeClass('always-visible');
    }
}

// Panggil fungsi saat window resize
$(window).on('resize', function() {
    adjustFixedColumns();
});

$(document).ready(function() {
    // Initialize DataTable dengan konfigurasi yang tepat
    const bomTable = $('#bom-table').DataTable({
        "processing": true,
        "pageLength": 10, // Menampilkan 10 data per halaman
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], // Opsi jumlah data per halaman
        "pagingType": "simple_numbers", // Tipe pagination
        "ordering": true, // Mengaktifkan pengurutan
        "info": true, // Menampilkan info jumlah data
        "autoWidth": false,
        "responsive": false, // Matikan responsive bawaan DataTables karena kita menggunakan CSS kustom
        "scrollX": true, // Aktifkan scroll horizontal
        "scrollCollapse": true,
        "fixedColumns": false, // Kita menggunakan CSS kustom untuk fixed columns
        "drawCallback": function(settings) {
            // Memastikan kolom sticky tetap berfungsi setelah paging atau sorting
            $('#bom-table tbody tr').hover(function() {
                $(this).addClass('hovered');
            }, function() {
                $(this).removeClass('hovered');
            });
            
            // Memastikan kolom action tetap terlihat saat scrolling
            adjustFixedColumns();
        },
        "language": {
            "paginate": {
                "previous": "<i class='fas fa-chevron-left'></i>",
                "next": "<i class='fas fa-chevron-right'></i>"
            },
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries"
        },
        "drawCallback": function(settings) {
            console.log('DataTable redrawn - page ' + (Math.ceil(settings._iDisplayStart / settings._iDisplayLength) + 1));
        }
    });
    
    // Toggle filter section
    $('#toggleFilter').on('click', function() {
        $('#filterSection').slideToggle();
        
        // Toggle icon
        const icon = $(this).find('i');
        if (icon.hasClass('fa-filter')) {
            icon.removeClass('fa-filter').addClass('fa-times');
            $(this).attr('title', 'Close Filter');
        } else {
            icon.removeClass('fa-times').addClass('fa-filter');
            $(this).attr('title', 'Show Filter');
        }
    });
    
    // View BOM - menggunakan event delegation
    $(document).on('click', '.view-btn', function() {
        const id = $(this).data('bs-id');
        console.log('View button clicked for ID:', id);
        
        $.ajax({
            url: '/admin/material/get-bom/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                if (response.status === 'success') {
                    const data = response.data;
                    $('#view-model-no').text(data.model_no);
                    $('#view-h-class').text(data.h_class);
                    $('#view-qty-assy').text(data.qty_assy);
                    $('#view-part-no').text(data.part_no);
                    $('#view-description').text(data.description);
                    $('#view-prd-code').text(data.prd_code);
                    $('#view-class').text(data.class);
                    $('#view-upd-date').text(data.upd_date);
                    
                    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
                    viewModal.show();
                } else {
                    // Gunakan toastr untuk notifikasi error
                    toastr.error('Failed to load BOM data: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                toastr.error('An error occurred while loading BOM data');
            }
        });
    });
    
    // Edit BOM - menggunakan event delegation
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('bs-id');
        console.log('Edit button clicked for ID:', id);
        
        $.ajax({
            url: '/admin/material/get-bom/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const bom = response.data;
                    $('#edit-id').val(bom.id);
                    $('#edit-model-no').val(bom.model_no || '');
                    $('#edit-h-class').val(bom.h_class || '');
                    $('#edit-qty-assy').val(bom.qty_assy || '');
                    $('#edit-part-no').val(bom.part_no || '');
                    $('#edit-description').val(bom.description || '');
                    $('#edit-prd-code').val(bom.prd_code || '');
                    $('#edit-class').val(bom.class || '');
                    $('#edit-upd-date').val(bom.upd_date || '');
                    
                    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                } else {
                    toastr.error('Failed to load BOM data: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                toastr.error('An error occurred while loading BOM data');
            }
        });
    });
    
    // Submit form add BOM
    $('#addBomForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        // Log data yang dikirim untuk debugging
        console.log('Form data being sent:', formData);
        
        $.ajax({
            url: '/admin/material/save-bom',
            type: 'POST',
            dataType: 'json',
            data: formData + '&<?= csrf_token() ?>=' + '<?= csrf_hash() ?>',
            success: function(response) {
                console.log('Server response:', response);
                if (response.status === 'success') {
                    const addModalEl = document.getElementById('addModal');
                    const addModal = bootstrap.Modal.getInstance(addModalEl);
                    addModal.hide();
                    
                    // Gunakan toastr untuk notifikasi sukses
                    toastr.success(response.message);
                    
                    // Reload halaman setelah 1 detik
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error('Failed to add: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                console.error('Status:', status);
                console.error('Error:', error);
                toastr.error('An error occurred while adding BOM data');
            }
        });
    });
    
    // Submit form edit BOM
    $('#editBomForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit-id').val();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '/admin/material/update-bom/' + id,
            type: 'POST',
            dataType: 'json',
            data: formData + '&<?= csrf_token() ?>=' + '<?= csrf_hash() ?>',
            success: function(response) {
                if (response.status === 'success') {
                    const editModalEl = document.getElementById('editModal');
                    const editModal = bootstrap.Modal.getInstance(editModalEl);
                    editModal.hide();
                    
                    // Gunakan toastr untuk notifikasi sukses
                    toastr.success(response.message);
                    
                    // Reload halaman setelah 1 detik
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error('Failed to update: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                toastr.error('An error occurred while updating BOM data');
            }
        });
    });
    
    // Delete BOM data - menggunakan event delegation
    let deleteId = null;
    
    $(document).on('click', '.delete-btn', function() {
        deleteId = $(this).data('bs-id');
        console.log('Delete button clicked for ID:', deleteId);
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });
    
    $('#confirm-delete').click(function() {
        if (deleteId) {
            $.ajax({
                url: '/admin/material/delete-bom/' + deleteId,
                type: 'POST',
                dataType: 'json',
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        const deleteModalEl = document.getElementById('deleteModal');
                        const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
                        deleteModal.hide();
                        
                        // Gunakan toastr untuk notifikasi sukses
                        toastr.success(response.message);
                        
                        // Reload halaman setelah 1 detik
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error('Failed to delete: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    toastr.error('An error occurred while deleting BOM data');
                }
            });
        }
    });
});
</script>
<?= $this->endSection() ?>
