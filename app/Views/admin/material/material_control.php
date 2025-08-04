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
    
    /* Styling untuk kolom sticky */
    .freeze-column-right {
        position: sticky;
        right: 0;
        z-index: 10;
        background-color: white;
        box-shadow: -2px 0 5px -2px rgba(0,0,0,0.2);
        min-width: 120px; /* Memastikan lebar minimum untuk tombol action */
        width: 120px; /* Lebar tetap untuk kolom action */
    }
    
    #material-table th:last-child,
    #material-table td:last-child {
        position: sticky;
        right: 0;
        z-index: 10;
        min-width: 120px; /* Memastikan lebar minimum untuk tombol action */
        width: 120px; /* Lebar tetap untuk kolom action */
    }
    
    #material-table thead th:last-child {
        z-index: 11;
        background-color: #212529;
    }
    
    #material-table tbody td:last-child {
        background-color: white;
    }
    
    /* Meningkatkan visibilitas kolom action */
    #material-table th:last-child,
    #material-table td:last-child {
        right: 0;
        box-shadow: -5px 0 10px -5px rgba(0,0,0,0.3);
        background-color: white !important; /* Memastikan background selalu putih */
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
    #material-table tbody tr:hover td.action-buttons,
    #material-table tbody tr.hovered td.action-buttons {
        background-color: #f2f2f2;
    }
    
    /* Styling untuk hover pada baris */
    #material-table tbody tr:hover td,
    #material-table tbody tr.hovered td {
        background-color: #f2f2f2;
    }
    
    /* Styling untuk card header gradient */
    .card-header.bg-gradient-primary {
        background: linear-gradient(to right, #4e73df, #224abe);
    }
    
    /* Styling untuk Select2 */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container .select2-selection--single,
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    
    /* Truncate long text in select2 options */
    .select2-results__option {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    
    /* Border box untuk elemen filter */
    #filterSection .input-group {
        border: 1px solid #e2e6ea;
        border-radius: 0.5rem;
        padding: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    #filterSection .input-group:focus-within {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    #filterSection .select2-container .select2-selection--single,
    #filterSection .select2-container--default .select2-selection--single {
        border: none;
        background-color: transparent;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_buttons') ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-success mr-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import"></i>
            Import Excel
        </button>
        <a href="<?= base_url('admin/material/export-material') ?>" class="btn btn-sm btn-outline-info mr-2">
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
            <h5 class="m-0 font-weight-bold">Material Control</h5>
            <div>
                <button id="addMaterialBtn" class="btn btn-sm btn-light mr-2" onclick="window.location.href='<?= base_url('admin/material/add-material') ?>'">
                    <i class="fas fa-plus"></i> Add
                </button>
                <button id="toggleFilter" class="btn btn-sm btn-light">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>
    
        <!-- Filter Section -->
        <div class="card-body border-bottom" id="filterSection" style="display:none;">
            <form id="filterForm" method="get" action="<?= base_url('admin/material/material-control') ?>" class="row g-3">
                <div class="row mb-3">
                    <div class="col-md-3 mb-2">
                        <label for="filterCkd" class="form-label">CKD</label>
                        <div class="input-group border">
                            <select class="form-select select2" id="filterCkd" name="ckd" style="width: 100%;">
                                <option value="">All</option>
                                <?php 
                                // Ambil data unik untuk CKD
                                $ckdValues = array_unique(array_column($material_data ?? [], 'ckd'));
                                sort($ckdValues);
                                foreach ($ckdValues as $ckdValue) : 
                                    if (!empty($ckdValue)) :
                                ?>
                                    <option value="<?= $ckdValue ?>" <?= (isset($filters['ckd']) && $filters['ckd'] == $ckdValue) ? 'selected' : '' ?>>
                                        <?= esc($ckdValue) ?>
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="filterPartNo" class="form-label">Part No</label>
                        <div class="input-group border">
                            <select class="form-select select2" id="filterPartNo" name="part_no" style="width: 100%;">
                                <option value="">All</option>
                                <?php 
                                // Ambil data unik untuk Part No
                                $partNoValues = array_unique(array_column($material_data ?? [], 'part_no'));
                                sort($partNoValues);
                                foreach ($partNoValues as $partNoValue) : 
                                    if (!empty($partNoValue)) :
                                ?>
                                    <option value="<?= $partNoValue ?>" <?= (isset($filters['part_no']) && $filters['part_no'] == $partNoValue) ? 'selected' : '' ?>>
                                        <?= esc($partNoValue) ?>
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="filterDescription" class="form-label">Description</label>
                        <div class="input-group border">
                            <select class="form-select select2" id="filterDescription" name="description" style="width: 100%;">
                                <option value="">All</option>
                                <?php 
                                // Ambil data unik untuk Description
                                $descValues = array_unique(array_column($material_data ?? [], 'description'));
                                sort($descValues);
                                foreach ($descValues as $descValue) : 
                                    if (!empty($descValue)) :
                                ?>
                                    <option value="<?= $descValue ?>" <?= (isset($filters['description']) && $filters['description'] == $descValue) ? 'selected' : '' ?>>
                                        <?= esc($descValue) ?>
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="filterClass" class="form-label">Class</label>
                        <div class="input-group border">
                            <select class="form-select select2" id="filterClass" name="class" style="width: 100%;">
                                <option value="">All</option>
                                <?php 
                                // Ambil data unik untuk Class
                                $classValues = array_unique(array_column($material_data ?? [], 'class'));
                                sort($classValues);
                                foreach ($classValues as $classValue) : 
                                    if (!empty($classValue)) :
                                ?>
                                    <option value="<?= $classValue ?>" <?= (isset($filters['class']) && $filters['class'] == $classValue) ? 'selected' : '' ?>>
                                        <?= esc($classValue) ?>
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="<?= base_url('admin/material/material-control') ?>" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Material Control Data Table -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="material-table" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>CKD</th>
                            <th>Period</th>
                            <th>Description</th>
                            <th>Part No</th>
                            <th>Class</th>
                            <th>Beginning</th>
                            <th class="freeze-column-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($material_data)) : ?>
                            <tr>
                                <td colspan="8" class="text-center">No material control data found</td>
                            </tr>
                        <?php else : ?>
                            <?php $i = 1; foreach ($material_data as $material) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= esc($material['ckd']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($material['period'])) ?></td>
                                    <td><?= esc($material['description']) ?></td>
                                    <td><?= esc($material['part_no']) ?></td>
                                    <td><?= esc($material['class']) ?></td>
                                    <td><?= esc($material['beginning']) ?></td>
                                    <td class="action-buttons freeze-column-right">
                                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-bs-id="<?= $material['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-bs-id="<?= $material['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Material Control Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/material/import-material') ?>" method="post" enctype="multipart/form-data" id="importForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Excel File</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                    </div>
                    <div class="alert alert-info">
                        <strong>Format Excel:</strong>
                        <ul class="mb-0">
                            <li>CKD (Column A) - Required</li>
                            <li>Period (Column B) - Format: dd/mm/yy (e.g., 01/05/25)</li>
                            <li>Description (Column C)</li>
                            <li>Part No (Column D) - Required</li>
                            <li>Class (Column E)</li>
                            <li>Beginning (Column F)</li>
                        </ul>
                        <p class="mt-2 mb-0"><small>Note: First row should be header row. CKD and Part No are required fields.</small></p>
                    </div>
                    <div class="alert alert-warning">
                        <small>Make sure your Excel file follows the exact column order as specified above. The import will stop if it encounters a row with empty CKD or Part No.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="importSubmitBtn">Import</button>
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
                <p>Are you sure you want to delete this material control data? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Material Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMaterialForm">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_material_id" name="id">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_ckd" class="form-label">CKD</label>
                            <input type="text" class="form-control" id="edit_ckd" name="ckd" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_period" class="form-label">Period</label>
                            <input type="date" class="form-control" id="edit_period" name="period">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_part_no" class="form-label">Part No</label>
                            <input type="text" class="form-control" id="edit_part_no" name="part_no" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="edit_description" name="description">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_class" class="form-label">Class</label>
                            <input type="number" class="form-control" id="edit_class" name="class">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_beginning" class="form-label">Beginning</label>
                            <input type="text" class="form-control" id="edit_beginning" name="beginning">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="update-material">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize DataTable with sticky columns
    const materialTable = $('#material-table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: -1 } // Disable sorting on action column
        ],
        drawCallback: function() {
            // Ensure action column stays sticky after draw
            $('.freeze-column-right').css('position', 'sticky');
        },
        // Pastikan jumlah kolom sesuai dengan struktur tabel
        columns: [
            { data: 'no' },
            { data: 'ckd' },
            { data: 'period' },
            { data: 'description' },
            { data: 'part_no' },
            { data: 'class' },
            { data: 'beginning' },
            { data: 'actions', orderable: false }
        ]
    });
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
    });
    
    // Toggle filter section
    $('#toggleFilter').click(function() {
        $('#filterSection').slideToggle();
    });
    
    // Delete Material data - menggunakan event delegation
    let deleteId = null;
    let editId = null;
    
    $(document).on('click', '.delete-btn', function() {
        deleteId = $(this).data('bs-id');
        console.log('Delete button clicked for ID:', deleteId);
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });
    
    // Edit Material data
    $(document).on('click', '.edit-btn', function() {
        editId = $(this).data('bs-id');
        console.log('Edit button clicked for ID:', editId);
        
        // Fetch material data via AJAX
        $.ajax({
            url: '<?= base_url('admin/material/edit-material') ?>/' + editId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Fill the form with material data
                    const material = response.data;
                    $('#edit_material_id').val(material.id);
                    $('#edit_ckd').val(material.ckd);
                    
                    // Format date for input type="date"
                    if (material.period) {
                        const date = new Date(material.period);
                        const formattedDate = date.toISOString().split('T')[0];
                        $('#edit_period').val(formattedDate);
                    }
                    
                    $('#edit_part_no').val(material.part_no);
                    $('#edit_description').val(material.description);
                    $('#edit_class').val(material.class);
                    $('#edit_beginning').val(material.beginning);
                    
                    // Show the modal
                    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                } else {
                    toastr.error(response.message || 'Failed to load material data');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                toastr.error('An error occurred while loading material data');
            }
        });
    });
    
    $('#confirm-delete').click(function() {
        if (deleteId) {
            $.ajax({
                url: '<?= base_url('admin/material/delete-material') ?>/' + deleteId,
                type: 'POST',
                dataType: 'json',
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success) {
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
                    toastr.error('An error occurred while deleting material data');
                }
            });
        }
    });
    
    // Handle Edit Form Submit
    $('#editMaterialForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('admin/material/update-material') ?>/' + editId,
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.success) {
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
                    toastr.error(response.message || 'Failed to update material data');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                toastr.error('An error occurred while updating material data');
            }
        });
    });
    
    // Handle import form submission
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        
        // Tampilkan loading indicator
        $('#importSubmitBtn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importing...');
        $('#importSubmitBtn').prop('disabled', true);
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('admin/material/import-material') ?>',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                // Reset button state
                $('#importSubmitBtn').html('Import');
                $('#importSubmitBtn').prop('disabled', false);
                
                if (response.status === 'success') {
                    // Reset form
                    $('#importForm')[0].reset();
                    
                    // Hide modal
                    const importModalEl = document.getElementById('importModal');
                    const importModal = bootstrap.Modal.getInstance(importModalEl);
                    importModal.hide();
                    
                    // Gunakan toastr untuk notifikasi sukses
                    toastr.success(response.message);
                    
                    // Reload halaman setelah 1 detik
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error('Import failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                // Reset button state
                $('#importSubmitBtn').html('Import');
                $('#importSubmitBtn').prop('disabled', false);
                
                console.error('AJAX Error:', error);
                let errorMessage = 'An error occurred during import';
                
                // Coba parse response jika ada
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // Jika tidak bisa parse, gunakan pesan default
                }
                
                toastr.error(errorMessage);
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
