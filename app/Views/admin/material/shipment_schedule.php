<?= $this->extend('admin/layout') ?>

<?= $this->section('styles') ?>
<!-- Custom styles for this page -->
<link href="<?= base_url('vendor/datatables/dataTables.bootstrap4.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/select2/select2.min.css') ?>" rel="stylesheet" />
<link href="<?= base_url('assets/vendor/select2/select2-bootstrap-5-theme.min.css') ?>" rel="stylesheet" />
<link href="<?= base_url('assets/vendor/toastr/toastr.min.css') ?>" rel="stylesheet">
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
    
    #shipment-table th:last-child,
    #shipment-table td:last-child {
        position: sticky;
        right: 0;
        z-index: 10;
        min-width: 120px; /* Memastikan lebar minimum untuk tombol action */
        width: 120px; /* Lebar tetap untuk kolom action */
    }
    
    /* Header tabel berwarna hitam */
    .table thead th,
    #shipment-table thead th {
        background-color: #212529;
        color: white;
    }
    
    #shipment-table thead th:last-child {
        z-index: 11;
        background-color: #212529;
    }
    
    #shipment-table tbody td:last-child {
        background-color: white;
    }
    
    /* Styling untuk card header dengan gradient */
    .card-header-gradient {
        background: linear-gradient(to right, #4e73df, #224abe);
        color: white;
    }
    
    /* Styling untuk filter section */
    #filterSection {
        padding: 15px;
        border-bottom: 1px solid #e3e6f0;
        display: none;
    }
    
    /* Styling untuk tombol filter */
    .filter-button {
        margin-right: 10px;
    }
    
    /* Styling untuk select2 di dalam filter */
    .select2-container--bootstrap-5 .select2-selection {
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
    }
    
    #shipment-table thead th:last-child {
        z-index: 11;
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
        <button type="button" class="btn btn-sm btn-outline-primary mr-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import"></i>
            Import Excel
        </button>
        <a href="<?= base_url('admin/material/export-shipment-schedule') ?>" class="btn btn-sm btn-outline-success mr-2">
            <i class="fas fa-file-download"></i>
            Export Excel
        </a>
    </div>
<?= $this->endSection() ?>


<?= $this->section('content') ?>
<div class="container-fluid">
    
    <!-- Notification Messages -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold">Shipment Schedule</h5>
            <div>
                <button type="button" class="btn btn-sm btn-light mr-2" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus"></i> Tambah Shipment
                </button>
                <button id="toggleFilter" class="btn btn-sm btn-light">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="card-body" id="filterSection" style="display: <?= !empty($filters) ? 'block' : 'none' ?>">
            <form action="<?= base_url('admin/material/shipment-schedule') ?>" method="get">
                <div class="row mb-3">
                    <div class="col-md-3 mb-2">
                        <label for="filterInvNo" class="form-label">Invoice No</label>
                        <div class="input-group border">
                            <select class="form-select select2" id="filterInvNo" name="inv_no" style="width: 100%;">
                                <option value="">All</option>
                                <?php 
                                // Ambil data unik untuk Invoice No
                                $invNoValues = array_unique(array_column($shipmentSchedule ?? [], 'inv_no'));
                                sort($invNoValues);
                                foreach ($invNoValues as $invNoValue) : 
                                    if (!empty($invNoValue)) :
                                ?>
                                    <option value="<?= $invNoValue ?>" <?= (isset($filters['inv_no']) && $filters['inv_no'] == $invNoValue) ? 'selected' : '' ?>>
                                        <?= esc($invNoValue) ?>
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="filterItemNo" class="form-label">Part No</label>
                        <div class="input-group border">
                            <select class="form-select select2" id="filterItemNo" name="item_no" style="width: 100%;">
                                <option value="">All</option>
                                <?php 
                                // Ambil data unik untuk Item No
                                $itemNoValues = array_unique(array_column($shipmentSchedule ?? [], 'item_no'));
                                sort($itemNoValues);
                                foreach ($itemNoValues as $itemNoValue) : 
                                    if (!empty($itemNoValue)) :
                                ?>
                                    <option value="<?= $itemNoValue ?>" <?= (isset($filters['item_no']) && $filters['item_no'] == $itemNoValue) ? 'selected' : '' ?>>
                                        <?= esc($itemNoValue) ?>
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
                                $classValues = array_unique(array_column($shipmentSchedule ?? [], 'class'));
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
                    <div class="col-md-3 mb-2">
                        <label for="filterSchQty" class="form-label">Schedule Qty</label>
                        <div class="input-group border">
                            <select class="form-select select2" id="filterSchQty" name="sch_qty" style="width: 100%;">
                                <option value="">All</option>
                                <?php 
                                // Ambil data unik untuk Schedule Qty
                                $schQtyValues = array_unique(array_column($shipmentSchedule ?? [], 'sch_qty'));
                                sort($schQtyValues);
                                foreach ($schQtyValues as $schQtyValue) : 
                                    if (!empty($schQtyValue)) :
                                ?>
                                    <option value="<?= $schQtyValue ?>" <?= (isset($filters['sch_qty']) && $filters['sch_qty'] == $schQtyValue) ? 'selected' : '' ?>>
                                        <?= esc($schQtyValue) ?>
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
                        <a href="<?= base_url('admin/material/shipment-schedule') ?>" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Data Table -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="shipment-table" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Invoice No</th>
                            <th>Part No</th>
                            <th>Class</th>
                            <th>Schedule Qty</th>
                            <th>ETD Date</th>
                            <th>ETA Date</th>
                            <th>ETA Meina</th>
                            <th class="freeze-column-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($shipmentSchedule)) : ?>
                            <?php $no = 1; foreach ($shipmentSchedule as $item) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc($item['inv_no']) ?></td>
                                    <td><?= esc($item['item_no']) ?></td>
                                    <td><?= esc($item['class']) ?></td>
                                    <td><?= esc($item['sch_qty']) ?></td>
                                    <td><?= esc($item['etd_date']) ?></td>
                                    <td><?= esc($item['eta_date']) ?></td>
                                    <td><?= esc($item['eta_meina']) ?></td>
                                    <td class="freeze-column-right">
                                        <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="<?= $item['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="<?= $item['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Add Shipment Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addShipmentForm" action="<?= base_url('admin/material/add-shipment-schedule') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add_inv_no" class="form-label">Invoice No</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="add_inv_no" name="inv_no" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="add_item_no" class="form-label">Part No</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="add_item_no" name="item_no" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add_class" class="form-label">Class</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="add_class" name="class">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="add_sch_qty" class="form-label">Schedule Qty</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="add_sch_qty" name="sch_qty">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add_etd_date" class="form-label">ETD Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="add_etd_date" name="etd_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="add_eta_date" class="form-label">ETA Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="add_eta_date" name="eta_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="add_eta_meina" class="form-label">ETA Meina</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="add_eta_meina" name="eta_meina">
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Shipment Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editShipmentForm">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_shipment_id" name="id">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_inv_no" class="form-label">Invoice No</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="edit_inv_no" name="inv_no" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_item_no" class="form-label">Part No</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="edit_item_no" name="item_no" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_class" class="form-label">Class</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="edit_class" name="class">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_sch_qty" class="form-label">Schedule Qty</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="edit_sch_qty" name="sch_qty">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_etd_date" class="form-label">ETD Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="edit_etd_date" name="etd_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_eta_date" class="form-label">ETA Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="edit_eta_date" name="eta_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_eta_meina" class="form-label">ETA Meina</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="edit_eta_meina" name="eta_meina">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="update-shipment">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this shipment schedule data? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Shipment Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/material/import-shipment-schedule') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Excel File</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xls,.xlsx" required>
                    </div>
                    <div class="alert alert-info">
                        <strong>Format Excel:</strong>
                        <ul>
                            <li>Column A: Invoice No</li>
                            <li>Column B: Part No</li>
                            <li>Column C: Class</li>
                            <li>Column D: Schedule Qty</li>
                            <li>Column E: ETD Date (format: dd/mm/yy)</li>
                            <li>Column F: ETA Date (format: dd/mm/yy)</li>
                            <li>Column G: ETA Meina (format: dd/mm/yy)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Page level plugins -->
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/select2/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/toastr/toastr.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with search functionality
    $('#shipment-table').DataTable({
        responsive: true,
        columnDefs: [
            { orderable: false, targets: -1 } // Disable sorting on action column
        ],
        order: [[0, 'asc']],
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>><"table-responsive my-3"t><"row"<"col-sm-5"i><"col-sm-7"p>>',
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records...",
            lengthMenu: "Show _MENU_ entries"
        }
    });
    
    // Initialize Select2 for all dropdowns
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select an option',
        allowClear: true
    });
    
    // Toggle filter section with animation
    $('#toggleFilter').click(function() {
        $('#filterSection').slideToggle('fast', function() {
            // Update icon based on visibility
            if ($(this).is(':visible')) {
                $('#toggleFilter i').removeClass('fa-filter').addClass('fa-times');
            } else {
                $('#toggleFilter i').removeClass('fa-times').addClass('fa-filter');
            }
        });
    });
    
    // Show filter section if there are active filters
    if (<?= !empty($filter) && (isset($filter['inv_no']) || isset($filter['item_no']) || isset($filter['class']) || isset($filter['sch_qty'])) ? 'true' : 'false' ?>) {
        $('#filterSection').show();
        $('#toggleFilter i').removeClass('fa-filter').addClass('fa-times');
    }
    
    // Edit button click handler
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        
        // Fetch data using AJAX
        $.ajax({
            url: '<?= base_url('admin/material/get-shipment-schedule') ?>/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    
                    // Populate form fields
                    $('#edit_shipment_id').val(data.id);
                    $('#edit_inv_no').val(data.inv_no);
                    $('#edit_item_no').val(data.item_no);
                    $('#edit_class').val(data.class);
                    $('#edit_sch_qty').val(data.sch_qty);
                    $('#edit_etd_date').val(data.etd_date);
                    $('#edit_eta_date').val(data.eta_date);
                    $('#edit_eta_meina').val(data.eta_meina);
                    
                    // Show modal
                    $('#editModal').modal('show');
                } else {
                    toastr.error('Failed to fetch data');
                }
            },
            error: function() {
                toastr.error('An error occurred while fetching data');
            }
        });
    });
    
    // Update shipment form submit handler
    $('#editShipmentForm').submit(function(e) {
        e.preventDefault();
        
        const id = $('#edit_shipment_id').val();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('admin/material/update-shipment-schedule') ?>/' + id,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#editModal').modal('hide');
                    toastr.success('Shipment schedule updated successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Failed to update shipment schedule');
                }
            },
            error: function() {
                toastr.error('An error occurred while updating data');
            }
        });
    });
    
    // Add shipment form submit handler
    $('#addShipmentForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('admin/material/add-shipment-schedule') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#addModal').modal('hide');
                    toastr.success('Shipment schedule added successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Failed to add shipment schedule');
                }
            },
            error: function() {
                toastr.error('An error occurred while adding data');
            }
        });
    });
    
    // Delete button click handler
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        $('#confirm-delete').data('id', id);
        $('#deleteModal').modal('show');
    });
    
    // Confirm delete button click handler
    $('#confirm-delete').click(function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('admin/material/delete-shipment-schedule') ?>/' + id,
            type: 'POST',
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#deleteModal').modal('hide');
                    toastr.success('Shipment schedule deleted successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Failed to delete shipment schedule');
                }
            },
            error: function() {
                toastr.error('An error occurred while deleting data');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Page level plugins -->
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/select2/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/toastr/toastr.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // DataTable sudah diinisialisasi di script sebelumnya
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select an option',
        allowClear: true
    });
    
    // Filter toggle is handled in the first script block
    
    // Edit button click handler
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        
        // Fetch data using AJAX
        $.ajax({
            url: '<?= base_url('admin/material/get-shipment-schedule') ?>/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    
                    // Populate form fields
                    $('#edit_shipment_id').val(data.id);
                    $('#edit_inv_no').val(data.inv_no);
                    $('#edit_item_no').val(data.item_no);
                    $('#edit_class').val(data.class);
                    $('#edit_sch_qty').val(data.sch_qty);
                    $('#edit_etd_date').val(data.etd_date);
                    $('#edit_eta_date').val(data.eta_date);
                    $('#edit_eta_meina').val(data.eta_meina);
                    
                    // Show modal
                    $('#editModal').modal('show');
                } else {
                    toastr.error('Failed to fetch data');
                }
            },
            error: function() {
                toastr.error('An error occurred while fetching data');
            }
        });
    });
    
    // Update shipment form submit handler
    $('#editShipmentForm').submit(function(e) {
        e.preventDefault();
        
        const id = $('#edit_shipment_id').val();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('admin/material/update-shipment-schedule') ?>/' + id,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#editModal').modal('hide');
                    toastr.success('Shipment schedule updated successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Failed to update shipment schedule');
                }
            },
            error: function() {
                toastr.error('An error occurred while updating data');
            }
        });
    });
    
    // Add shipment form submit handler
    $('#addShipmentForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('admin/material/add-shipment-schedule') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#addModal').modal('hide');
                    toastr.success('Shipment schedule added successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Failed to add shipment schedule');
                }
            },
            error: function() {
                toastr.error('An error occurred while adding data');
            }
        });
    });
    
    // Delete button click handler
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        $('#confirm-delete').data('id', id);
        $('#deleteModal').modal('show');
    });
    
    // Confirm delete button click handler
    $('#confirm-delete').click(function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('admin/material/delete-shipment-schedule') ?>/' + id,
            type: 'POST',
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#deleteModal').modal('hide');
                    toastr.success('Shipment schedule deleted successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Failed to delete shipment schedule');
                }
            },
            error: function() {
                toastr.error('An error occurred while deleting data');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
