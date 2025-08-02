<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Material Control</h1>
    
    <!-- Notification Messages -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <!-- Page Buttons -->
    <div class="row mb-3" id="page_buttons">
        <div class="col-md-12">
            <a href="#" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Material Control
            </a>
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importModal">
                <i class="fas fa-file-import"></i> Import Excel
            </button>
            <a href="#" class="btn btn-info btn-sm">
                <i class="fas fa-file-export"></i> Export Excel
            </a>
            <button type="button" class="btn btn-secondary btn-sm" id="toggle_filter">
                <i class="fas fa-filter"></i> Toggle Filter
            </button>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="card shadow mb-4" id="filter_section" style="display:none;">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(to right, #4e73df, #224abe);">
            <h6 class="m-0 font-weight-bold text-white">Filter</h6>
        </div>
        <div class="card-body">
            <form action="#" method="get">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="filter-inv-no">Inv No</label>
                        <select class="form-control select2" id="filter-inv-no" name="inv_no">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="filter-item-no">Item No</label>
                        <select class="form-control select2" id="filter-item-no" name="item_no">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="filter-class">Class</label>
                        <select class="form-control select2" id="filter-class" name="class">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Material Control Data Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(to right, #4e73df, #224abe);">
            <h6 class="m-0 font-weight-bold text-white">Material Control Data</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Inv No</th>
                            <th>Item No</th>
                            <th>Class</th>
                            <th>Sch Qty</th>
                            <th>ETD Date</th>
                            <th>ETA Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center">Material Control functionality will be implemented soon</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Material Control Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="#" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="excel_file">Excel File</label>
                        <input type="file" class="form-control-file" id="excel_file" name="excel_file" required>
                    </div>
                    <div class="alert alert-info">
                        <strong>Format Excel:</strong>
                        <ul>
                            <li>Inv No (Column A)</li>
                            <li>Item No (Column B)</li>
                            <li>Class (Column C)</li>
                            <li>Sch Qty (Column D)</li>
                            <li>ETD Date (Column E) - Format: dd-MMM-yy (e.g., 02-Apr-25)</li>
                            <li>ETA Date (Column F) - Format: dd-MMM-yy (e.g., 02-Apr-25)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#dataTable').DataTable();
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
    });
    
    // Toggle filter section
    $('#toggle_filter').click(function() {
        $('#filter_section').slideToggle();
    });
});
</script>
<?= $this->endSection() ?>
