<?= $this->extend('admin/layout') ?>

<?= $this->section('styles') ?>
<!-- Select2 CSS -->
<link href="<?= base_url('assets/vendor/select2/select2.min.css') ?>" rel="stylesheet" />
<link href="<?= base_url('assets/vendor/select2/select2-bootstrap-5-theme.min.css') ?>" rel="stylesheet" />
<!-- DateRangePicker CSS -->
<link href="<?= base_url('assets/vendor/daterangepicker/daterangepicker.css') ?>" rel="stylesheet" />
<!-- DataTables CSS -->
<link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap5.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/datatables/fixedHeader.bootstrap5.min.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(to right, #4e73df, #224abe);">
                    <h6 class="m-0 font-weight-bold text-white">Material Shortage Report</h6>
                    <div class="d-flex">
                        <button type="button" class="btn btn-light btn-sm me-2" id="toggle-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-success btn-sm" id="export-excel">
                            <i class="fas fa-file-excel"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Filter section - hidden by default -->
                <div class="card-body filter-container" style="display: none;">
                    <div class="filter-box p-3 border rounded shadow-sm bg-light">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label for="date-range" class="form-label fw-bold">Date Range</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                    <input type="text" class="form-control" id="date-range" style="height: 38px;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="part-no" class="form-label fw-bold">Part No</label>
                                <select class="form-control select2-dropdown" id="part-no" data-placeholder="All Parts" style="height: 38px; width: 100%;">
                                    <option value="">All Parts</option>
                                </select>
                            </div>
                            <!-- H Class filter commented out as requested -->
                            <!-- <div class="col-md-2">
                                <label for="h-class" class="form-label fw-bold">H Class</label>
                                <select class="form-control select2-dropdown" id="h-class" data-placeholder="All H Classes" style="height: 38px; width: 100%;">
                                    <option value="">All H Classes</option>
                                </select>
                            </div> -->
                            <div class="col-md-2">
                                <label for="class" class="form-label fw-bold">Class</label>
                                <select class="form-control select2-dropdown" id="class" data-placeholder="All Classes" style="height: 38px; width: 100%;">
                                    <option value="">All Classes</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="minus-only">
                                    <label class="form-check-label fw-bold" for="minus-only">
                                        Minus Only
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-primary w-100" id="search-btn">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading report data...</p>
                    </div>

                    <!-- Report Table -->
                    <div class="card-body table-responsive p-0" style="max-height: 70vh;">
                    <div class="table-container">
                        <table id="material-shortage-table" class="table table-bordered table-hover">
                            <thead style="background: linear-gradient(to bottom, rgba(220, 53, 69, 0.85), rgba(220, 53, 69, 0.65)) !important;"></thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <!-- No Data Message -->
                    <div id="noDataMessage" class="alert alert-info text-center" style="display: none;">
                        No data available for the selected filters. Please adjust your filter criteria and try again.
                    </div>
                </div>
                <div class="overlay" id="loading-overlay">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('plugins/daterangepicker/daterangepicker.css') ?>">
<link rel="stylesheet" href="<?= base_url('plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
<!-- DataTables CSS -->
<link rel="stylesheet" href="<?= base_url('plugins/datatables/dataTables.bootstrap5.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('plugins/datatables/fixedHeader.bootstrap5.min.css') ?>">
<style>
    /* Global styling reset for this page */
    .table {
        --bs-table-bg: transparent;
        --bs-table-accent-bg: transparent;
    }
    
    /* Filter Section Styling - Similar to Delivery Shortage Report */
    .filter-container {
        transition: all 0.3s ease-in-out;
        padding: 1rem;
    }
    
    .filter-box {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .filter-box:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }
    
    /* Card Header Styling - Same as Delivery Shortage Report */
    .card .card-header.bg-gradient-primary {
        background: linear-gradient(to right, #4e73df, #224abe) !important;
        background-color: #4e73df !important;
        padding: 0.75rem 1.25rem;
        border-bottom: 0;
        border-radius: 0.35rem 0.35rem 0 0;
        color: white !important;
    }
    
    .card-header.bg-gradient-primary .card-title {
        color: white !important;
    }
    
    /* Professional Table Styling - Custom Material Shortage Design */
    #material-shortage-table {
        width: 100%;
        margin-bottom: 1rem;
        color: #4a5568;
        border-collapse: collapse;
        font-size: 0.88rem;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }
    
    #material-shortage-table tr.bg-light {
        background-color: #f8f9fc !important;
    }
    
    #material-shortage-table th {
        background: linear-gradient(to bottom, rgba(220, 53, 69, 0.85), rgba(220, 53, 69, 0.65));
        color: white;
        padding: 0.7rem 0.75rem;
        font-weight: 500;
        font-size: 0.82rem;
        text-align: center;
        vertical-align: middle;
        border: 1px solid rgba(220, 53, 69, 0.5);
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 1px 5px rgba(0,0,0,0.12);
        transition: all 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        white-space: nowrap;
    }
    
    /* Add subtle hover effect to headers */
    #material-shortage-table th:hover {
        background: linear-gradient(to bottom, rgba(220, 53, 69, 0.9), rgba(220, 53, 69, 0.7)) !important;
        cursor: default;
    }
    
    /* Special styling for first column header */
    #material-shortage-table th:first-child {
        border-left: none;
        border-top-left-radius: 0.25rem;
    }
    
    /* Special styling for last column header */
    #material-shortage-table th:last-child {
        border-right: none;
        border-top-right-radius: 0.25rem;
    }
    
    /* Fixed column widths for specific columns */
    #material-shortage-table th.h-class-cell,
    #material-shortage-table td.h-class-cell {
        min-width: 100px;
        width: 100px;
    }
    
    #material-shortage-table th.label-cell,
    #material-shortage-table td.label-cell {
        min-width: 180px;
        width: 180px;
    }
    
    #material-shortage-table th.stock-cell,
    #material-shortage-table td.stock-cell {
        min-width: 120px;
        width: 120px;
    }
    
    #material-shortage-table th.part-cell,
    #material-shortage-table td.part-cell {
        min-width: 150px;
        width: 150px;
    }
    
    #material-shortage-table th.desc-cell,
    #material-shortage-table td.desc-cell {
        min-width: 200px;
        width: 200px;
    }
    
    #material-shortage-table th.class-cell,
    #material-shortage-table td.class-cell {
        min-width: 100px;
        width: 100px;
    }
    
    #material-shortage-table th.model-cell,
    #material-shortage-table td.model-cell {
        min-width: 150px;
        width: 150px;
    }
    
    #material-shortage-table td {
        padding: 0.7rem 0.75rem;
        border: 1px solid rgba(0, 0, 0, 0.08);
        vertical-align: middle;
        text-align: center;
        transition: all 0.2s ease;
        background-color: white;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        white-space: nowrap;
    }
    
    #material-shortage-table tbody tr:hover td {
        background-color: #f8fafc;
    }
    
    /* Zebra striping for better readability */
    #material-shortage-table tbody tr:nth-child(even) td:not([class*="-cell"]) {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    #material-shortage-table tbody tr:nth-child(even):hover td:not([class*="-cell"]) {
        background-color: rgba(13, 110, 253, 0.03);
    }
    
    /* Improved hover effect for all rows */
    #material-shortage-table tbody tr:hover td {
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    /* Group header styling */
    .group-header td {
        background-color: #edf2f7 !important;
        font-weight: 600;
        color: #2d3748;
        border-top: 2px solid #4299e1 !important;
        border-bottom: 1px solid #cbd5e0 !important;
    }
    
    .group-header:hover td {
        background-color: #e2e8f0 !important;
    }
    
    /* Table responsive styling */
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-radius: 0.5rem;
        background: white;
        border: 1px solid #e2e8f0;
    }
    
    /* Add subtle animation for table loading */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    #reportTableContainer {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    #material-shortage-table th.date-header {
        width: 60px;
    }
    
    #material-shortage-table td.negative-value {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        font-weight: 600;
        transition: background-color 0.2s ease;
    }
    
    #material-shortage-table td.negative-value:hover {
        background-color: rgba(220, 53, 69, 0.15);
    }
    
    #material-shortage-table tr:hover td {
        background-color: rgba(78, 115, 223, 0.05);
    }
    
    #material-shortage-table tr.part-group {
        border-top: 2px solid #4e73df;
    }
    
    /* Column-specific styling */
    .model-cell {
        width: 120px;
        background-color: #edf2f7 !important;
        font-weight: 600;
    }
    
    .h-class-cell {
        width: 100px; /* Increased width */
        background-color: #edf2f7 !important;
        white-space: nowrap;
    }
    
    .part-cell {
        width: 100px;
        background-color: #edf2f7 !important;
        font-weight: 500;
    }
    
    .desc-cell {
        width: 150px;
        background-color: #edf2f7 !important;
    }
    
    .class-cell {
        width: 80px;
        background-color: #edf2f7 !important;
    }
    
    .label-cell {
        width: 130px; /* Increased width for ITEM */
        font-weight: 600;
        background-color: #edf2f7 !important;
        color: #334155;
        white-space: nowrap;
    }
    
    .stock-cell {
        width: 120px; /* Increased width for Begin Stock */
        background-color: #edf2f7 !important;
        white-space: nowrap;
    }
    
    /* Ensure all cells have proper background */
    #material-shortage-table td {
        background-color: white;
    }
    
    /* Negative value styling */
    .negative-value {
        color: rgba(220, 53, 69, 0.9);
        font-weight: 500;
        position: relative;
        background-color: rgba(220, 53, 69, 0.05);
    }
    
    .negative-value:hover {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
    
    /* Add subtle indicator for negative values */
    .negative-value::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background-color: #ef4444;
    }
    
    /* Date columns styling */
    .date-column {
        text-align: center;
        width: 65px;
        font-size: 0.8rem;
        font-weight: 500;
        background-color: #f0f7ff !important;
        transition: all 0.2s ease;
        border-left: 1px solid #e2e8f0 !important;
        border-right: 1px solid #e2e8f0 !important;
    }
    
    .date-column:hover {
        background-color: #e6f0fd !important;
        box-shadow: inset 0 0 0 1px rgba(66, 153, 225, 0.5);
    }
    
    /* Highlight today's column */
    .today-column {
        background-color: #ebf8ff !important;
        border-left: 1px solid #90cdf4 !important;
        border-right: 1px solid #90cdf4 !important;
    }
    
    .today-column:hover {
        background-color: #bee3f8 !important;
    }
    
    /* Select2 styling */
    .select2-container--bootstrap4 .select2-selection {
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: 0;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem);
    }
    
    /* Filter toggle animation */
    .filter-container {
        transition: all 0.3s ease-in-out;
        background-color: #f8f9fa;
        border-radius: 0;
        padding: 1rem;
    }
    
    /* Loading overlay */
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        display: none;
        z-index: 1000;
        border-radius: 0.5rem;
    }
    
    /* Row spacing */
    .spacer-row td {
        padding: 0.15rem !important;
        background-color: #f8f9fa;
        border-left: none;
        border-right: none;
    }
    
    /* Form controls */
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-label {
        font-weight: 500;
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }
    
    /* Toggle button */
    #toggle-filter.active {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    /* Alternating row colors for each part */
    .part-group:nth-child(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Moment.js -->
<script src="<?= base_url('assets/vendor/momentjs/moment.min.js') ?>"></script>
<!-- Date Range Picker -->
<script src="<?= base_url('assets/vendor/daterangepicker/daterangepicker.min.js') ?>"></script>
<!-- Select2 -->
<script src="<?= base_url('assets/vendor/select2/select2.min.js') ?>"></script>
<!-- DataTables JS -->
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.fixedHeader.min.js') ?>"></script>
<script>
// Helper function to format numbers
function formatNumber(value) {
    if (value === undefined || value === null) return '0';
    var num = parseFloat(value);
    if (isNaN(num)) return '0';
    // Return integer without removing trailing zeros
    return Math.round(num).toString();
}

$(function() {
    // Sembunyikan loading overlay saat halaman pertama kali dibuka
    $('#loading-overlay').hide();
    
    // Inisialisasi Select2 dengan fitur pencarian
    $('.select2-dropdown').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        placeholder: function() {
            return $(this).data('placeholder');
        }
    });
    
    // Check if filter was previously visible and restore state
    if (localStorage.getItem('material_shortage_filter_visible') === 'true') {
        const $filterContainer = $('.filter-container');
        const $icon = $('#toggle-filter').find('i');
        
        $filterContainer.show();
        $('#toggle-filter').removeClass('btn-primary').addClass('btn-danger active');
        $icon.removeClass('fa-filter').addClass('fa-times');
    }

    // Inisialisasi Date Range Picker dengan default 30 hari
    var start = moment();
    var end = moment().add(29, 'days');

    function cb(start, end) {
        $('#date-range').val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
    }

    $('#date-range').daterangepicker({
        startDate: start,
        endDate: end,
        opens: 'left',
        autoApply: true,
        showDropdowns: true,
        showWeekNumbers: false,
        showISOWeekNumbers: false,
        timePicker: false,
        timePickerIncrement: 1,
        timePicker24Hour: false,
        timePickerSeconds: false,
        ranges: false, // Remove all preset ranges, only allow custom selection
        showCustomRangeLabel: false,
        alwaysShowCalendars: true,
        locale: {
            format: 'YYYY-MM-DD',
            separator: ' to ',
            applyLabel: 'Apply',
            cancelLabel: 'Cancel',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom',
            weekLabel: 'W',
            daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            firstDay: 1
        }
    }, cb);

    cb(start, end);
    
    // Toggle filter section with color change animation
    $('#toggle-filter').on('click', function() {
        const $filterContainer = $('.filter-container');
        const $icon = $(this).find('i');
        const $button = $(this);
        
        // Add transition for smoother color change
        $button.css('transition', 'all 0.3s ease');
        
        $filterContainer.slideToggle(300, function() {
            if ($filterContainer.is(':visible')) {
                // Change to danger color when filter is visible
                $button.removeClass('btn-primary').addClass('btn-danger active');
                $icon.removeClass('fa-filter').addClass('fa-times');
                // Store filter state in local storage
                localStorage.setItem('material_shortage_filter_visible', 'true');
            } else {
                // Change back to primary color when filter is hidden
                $button.removeClass('btn-danger active').addClass('btn-primary');
                $icon.removeClass('fa-times').addClass('fa-filter');
                // Store filter state in local storage
                localStorage.setItem('material_shortage_filter_visible', 'false');
            }
        });
    });

    // Load part_no options
    loadPartOptions();
    loadHClassOptions();
    loadClassOptions();

    // Event handler untuk tombol search
    $('#search-btn').on('click', function() {
        loadMaterialShortageData();
    });

    // Event handler untuk tombol export
    $('#export-excel').on('click', function() {
        exportToExcel();
    });

    // Load data saat halaman pertama kali dibuka
    // Tidak load data otomatis, tunggu user klik search
    // loadMaterialShortageData();
});

function loadPartOptions() {
    // Initialize Select2 with AJAX
    $('#part-no').select2({
        placeholder: 'Select Part Number',
        allowClear: true,
        ajax: {
            url: '<?= base_url('material-shortage/parts') ?>',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term, // search term
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                
                if (!data.success) {
                    toastr.error('Failed to load part options');
                    return { results: [] };
                }
                
                // Add 'All Parts' option only on first page
                var results = data.parts.map(function(part) {
                    return {
                        id: part.part_no,
                        text: part.part_no
                    };
                });
                
                if (params.page === 1) {
                    results.unshift({
                        id: '',
                        text: 'All Parts'
                    });
                }
                
                return {
                    results: results,
                    pagination: {
                        more: (params.page * 20) < data.total_count
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        width: '100%',
        dropdownCssClass: 'select2-dropdown-large'
    });
}

function loadHClassOptions() {
    $.ajax({
        url: '<?= base_url('material-shortage/h-classes') ?>',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#h-class').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                var options = '<option value="">All H Classes</option>';
                $.each(response.h_classes, function(index, hClass) {
                    options += '<option value="' + hClass.h_class + '">' + hClass.h_class + '</option>';
                });
                $('#h-class').html(options);
            } else {
                toastr.error('Failed to load H Class options');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Error loading H Class options: ' + error);
        },
        complete: function() {
            $('#h-class').prop('disabled', false);
        }
    });
}

function loadClassOptions() {
    $.ajax({
        url: '<?= base_url('material-shortage/classes') ?>',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#class').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                var options = '<option value="">All Classes</option>';
                $.each(response.classes, function(index, classItem) {
                    options += '<option value="' + classItem.class + '">' + classItem.class + '</option>';
                });
                $('#class').html(options);
            } else {
                toastr.error('Failed to load Class options');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Error loading Class options: ' + error);
        },
        complete: function() {
            $('#class').prop('disabled', false);
        }
    });
}

function loadMaterialShortageData() {
    var dateRange = $('#date-range').val().split(' to ');
    var startDate = dateRange[0] ? dateRange[0].trim() : null;
    var endDate = dateRange[1] ? dateRange[1].trim() : null;
    var partNo = $('#part-no').val();
    var hClass = $('#h-class').val();
    var classVal = $('#class').val();
    var minusOnly = $('#minus-only').prop('checked');
    
    // Validasi tanggal
    if (!startDate || !endDate) {
        alert('Please select a valid date range');
        return;
    }
    
    // Pastikan format YYYY-MM-DD dengan validasi
    try {
        // Validasi format tanggal
        if (!startDate.match(/^\d{4}-\d{2}-\d{2}$/) || !endDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
            console.warn('Date format validation failed, attempting to reformat...');
            // Coba format ulang dengan moment jika format tidak sesuai
            startDate = moment(startDate).format('YYYY-MM-DD');
            endDate = moment(endDate).format('YYYY-MM-DD');
        }
        
        // Simpan format yang sudah benar
        var startDateFormatted = startDate;
        var endDateFormatted = endDate;
        
        console.log('Validated dates:', startDateFormatted, endDateFormatted);
    } catch (error) {
        console.error('Date validation error:', error);
        alert('Invalid date format. Please select a valid date range.');
        $('#loading-overlay').hide();
        return;
    }
    
    // Show loading overlay
    $('#loading-overlay').show();
    
    // Log request parameters
    console.log('Material Shortage Request Parameters:', {
        start_date: startDateFormatted,
        end_date: endDateFormatted,
        part_no: partNo,
        h_class: hClass,
        class: classVal,
        minus_only: minusOnly
    });
    
    $.ajax({
        url: '<?= base_url('material-shortage/data') ?>',
        type: 'POST',
        data: {
            start_date: startDateFormatted,
            end_date: endDateFormatted,
            part_no: partNo,
            h_class: hClass,
            class: classVal,
            minus_only: minusOnly
        },
        dataType: 'json',
        success: function(response) {
            console.log('Material Shortage API Response:', response);
            
            if (response.success) {
                // Check if data is valid
                if (Array.isArray(response.data) && response.data.length > 0) {
                    console.log('Data received:', response.data.length, 'items');
                    console.log('Sample item:', response.data[0]);
                    
                    // Check if date 17 exists in the API response data
                    var found17th = false;
                    if (response.data && response.data.length > 0 && response.data[0].daily_data) {
                        Object.keys(response.data[0].daily_data).forEach(function(dateKey) {
                            try {
                                // Extract day safely without moment
                                var dateParts = dateKey.split('-');
                                if (dateParts.length === 3) {
                                    var day = dateParts[2];
                                    if (day === '17') {
                                        found17th = true;
                                        console.log('Found date 17 in API data:', dateKey);
                                    }
                                }
                            } catch (error) {
                                console.error('Error checking date:', dateKey, error);
                            }
                        });
                        if (!found17th) {
                            console.warn('WARNING: Date 17 is missing from API response data!');
                        }
                    }
                    
                    // Generate all dates in range (sorted chronologically)
                    var allDates = [];
                    
                    try {
                        // Parse dates to get components
                        var startParts = startDateFormatted.split('-');
                        var endParts = endDateFormatted.split('-');
                        
                        if (startParts.length !== 3 || endParts.length !== 3) {
                            throw new Error('Invalid date format');
                        }
                        
                        // Create Date objects
                        var startObj = new Date(parseInt(startParts[0]), parseInt(startParts[1])-1, parseInt(startParts[2]));
                        var endObj = new Date(parseInt(endParts[0]), parseInt(endParts[1])-1, parseInt(endParts[2]));
                        
                        // Calculate difference in days
                        var diffTime = Math.abs(endObj - startObj);
                        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        
                        console.log('Date range spans', diffDays, 'days');
                        
                        // Generate all dates in range
                        var currentDate = new Date(startObj);
                        for (var i = 0; i <= diffDays; i++) {
                            var year = currentDate.getFullYear();
                            var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
                            var day = currentDate.getDate().toString().padStart(2, '0');
                            var dateStr = year + '-' + month + '-' + day;
                            allDates.push(dateStr);
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                    } catch (error) {
                        console.error('Error generating date range:', error);
                        // Fallback: use a simple date range with the current month
                        var today = new Date();
                        var year = today.getFullYear();
                        var month = (today.getMonth() + 1).toString().padStart(2, '0');
                        
                        // Generate dates 1-31 for current month
                        for (var day = 1; day <= 31; day++) {
                            var dateStr = year + '-' + month + '-' + day.toString().padStart(2, '0');
                            allDates.push(dateStr);
                        }
                    }
                    
                    console.log('Generated date range for rendering:', allDates);
                    renderTable(response.data, startDateFormatted, endDateFormatted, allDates);
                } else {
                    console.log('No data or empty array received');
                    $('#material-shortage-table tbody').html('<tr><td colspan="100%" class="text-center py-3">No data available</td></tr>');
                    $('#loading-overlay').hide();
                }
            } else {
                console.error('API returned error:', response.message);
                alert('Error: ' + response.message);
            }
            $('#loading-overlay').hide();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            alert('Error: ' + error);
            $('#loading-overlay').hide();
        }
    });
}

function renderTable(data, startDate, endDate, sortedDates) {
    console.log('DEBUG: Starting renderTable function');
    console.log('DEBUG: Rendering table with data:', data);
    
    // Clear existing table content
    $('#material-shortage-table thead').empty();
    $('#material-shortage-table tbody').empty();
    
    console.log('DEBUG: Table cleared');
    
    if (!data || !Array.isArray(data) || data.length === 0) {
        console.error('No data to render');
        $('#error-message').text('No data available').show();
        $('#loading-overlay').hide();
        return;
    }
    
    // Ensure we have valid start and end dates
    if (!startDate || !endDate) {
        console.error('Invalid start or end date');
        $('#error-message').text('Invalid date range').show();
        $('#loading-overlay').hide();
        return;
    }
    
    // Use the provided sortedDates if available
    var allDates = [];
    
    if (Array.isArray(sortedDates) && sortedDates.length > 0) {
        console.log('Using provided sorted dates array');
        allDates = sortedDates;
    } else {
        // Generate dates array from scratch
        try {
            console.log('Generating fresh dates array from', startDate, 'to', endDate);
            
            // Parse dates to get components
            var startParts = startDate.split('-');
            var endParts = endDate.split('-');
            
            if (startParts.length !== 3 || endParts.length !== 3) {
                throw new Error('Invalid date format');
            }
            
            // Create Date objects
            var startObj = new Date(parseInt(startParts[0]), parseInt(startParts[1])-1, parseInt(startParts[2]));
            var endObj = new Date(parseInt(endParts[0]), parseInt(endParts[1])-1, parseInt(endParts[2]));
            
            // Calculate difference in days
            var diffTime = Math.abs(endObj - startObj);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            console.log('Date range spans', diffDays, 'days');
            
            // Generate all dates in range
            var currentDate = new Date(startObj);
            for (var i = 0; i <= diffDays; i++) {
                var year = currentDate.getFullYear();
                var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
                var day = currentDate.getDate().toString().padStart(2, '0');
                var dateStr = year + '-' + month + '-' + day;
                allDates.push(dateStr);
                currentDate.setDate(currentDate.getDate() + 1);
            }
        } catch (error) {
            console.error('Error generating dates:', error);
            $('#error-message').text('Error processing dates').show();
            $('#loading-overlay').hide();
            return;
        }
    }
    
    console.log('Using dates:', startDate, 'to', endDate);
    console.log('All dates in range (sorted):', allDates);
    
    // Debug: Check if dates are actually sorted correctly
    console.log('Dates in ISO format for verification:');
    allDates.forEach(function(date, index) {
        if (date && typeof date === 'string') {
            var parts = date.split('-');
            if (parts.length === 3) {
                console.log(index + ':', date, '=', parts[2] + '-' + parts[1] + '-' + parts[0]);
            } else {
                console.log(index + ':', date, '= Invalid format');
            }
        } else {
            console.log(index + ':', date, '= Invalid date');
        }
    });
    
    // Debug: Log all dates before processing
    console.log('All dates before processing:', allDates);
    
    // Ensure all dates are valid strings in YYYY-MM-DD format
    console.log('Ensuring all dates are valid...');
    for (var i = 0; i < allDates.length; i++) {
        if (typeof allDates[i] !== 'string' || !allDates[i].match(/^\d{4}-\d{2}-\d{2}$/)) {
            console.error('Invalid date format at index', i, ':', allDates[i]);
            allDates[i] = null; // Mark for removal
        }
    }
    
    // Remove null entries
    allDates = allDates.filter(function(date) {
        return date !== null;
    });
    
    console.log('Dates after format validation:', allDates);
    
    // Sort dates chronologically using proper Date object comparison
    console.log('Sorting dates chronologically...');
    
    // Ensure all dates are valid before sorting
    var validDates = allDates.filter(function(dateStr) {
        return dateStr && typeof dateStr === 'string' && dateStr.match(/^\d{4}-\d{2}-\d{2}$/);
    });
    
    // CRITICAL FIX: Sort using Date objects to ensure proper chronological order
    // This ensures dates are sorted by actual date value, not string comparison
    console.log('BEFORE SORTING:', validDates);
    validDates.sort(function(a, b) {
        // Convert strings to Date objects for proper comparison
        var dateA = new Date(a + 'T00:00:00Z'); // Add time component for consistent parsing
        var dateB = new Date(b + 'T00:00:00Z');
        
        // Debug output to verify sorting
        if (validDates.length < 50) { // Only log if not too many dates to avoid console spam
            console.log('Comparing:', a, '(', dateA, ') vs', b, '(', dateB, ') =', dateA - dateB);
        }
        
        return dateA - dateB;
    });
    console.log('AFTER SORTING:', validDates);
    
    // Double-check the sort worked correctly
    var isSorted = true;
    for (var i = 1; i < validDates.length; i++) {
        var prevDate = new Date(validDates[i-1] + 'T00:00:00Z');
        var currDate = new Date(validDates[i] + 'T00:00:00Z');
        if (currDate < prevDate) {
            console.error('CRITICAL ERROR: Dates still not sorted correctly after sorting!');
            console.error('Date at position', i-1, ':', validDates[i-1], 'comes AFTER', validDates[i]);
            isSorted = false;
            break;
        }
    }
    
    if (isSorted) {
        console.log('✓ VERIFICATION: Dates are properly sorted in chronological order');
    } else {
        console.error('✗ ERROR: Dates are NOT properly sorted! Manual fix required.');
        // Force manual sort as a last resort
        validDates.sort(function(a, b) {
            // Parse dates manually to avoid timezone issues
            var partsA = a.split('-');
            var partsB = b.split('-');
            
            // Compare years
            if (parseInt(partsA[0]) !== parseInt(partsB[0])) {
                return parseInt(partsA[0]) - parseInt(partsB[0]);
            }
            
            // Compare months
            if (parseInt(partsA[1]) !== parseInt(partsB[1])) {
                return parseInt(partsA[1]) - parseInt(partsB[1]);
            }
            
            // Compare days
            return parseInt(partsA[2]) - parseInt(partsB[2]);
        });
        console.log('After manual sort:', validDates);
    }
    
    // Replace the original array with the sorted valid dates
    allDates = validDates;
    
    console.log('Dates sorted successfully using Date object comparison');
    
    // Debug: Check if dates are sorted correctly AFTER sorting
    console.log('AFTER SORTING - Dates in ISO format:');
    console.log('Total dates after sorting:', allDates.length);
    
    // Verify the dates are in correct chronological order
    var previousDate = null;
    var isChronological = true;
    
    allDates.forEach(function(date, index) {
        var dateParts = date.split('-');
        var year = dateParts[0];
        var month = dateParts[1];
        var day = dateParts[2];
        console.log(index + ':', date, '=', day + '-' + month + '-' + year);
        
        // Check if dates are in chronological order
        var currentDate = new Date(date);
        if (previousDate !== null) {
            if (currentDate < previousDate) {
                console.error('SORTING ERROR: Date', date, 'is earlier than previous date', previousDate);
                isChronological = false;
            }
        }
        previousDate = currentDate;
    });
    
    if (isChronological) {
        console.log('✓ Dates are correctly sorted in chronological order');
    } else {
        console.error('✗ Dates are NOT in chronological order! This will cause calculation errors.');
    }
    
    // Ensure all dates in the selected range are present
    console.log('Validating all dates in the selected range are present...');
    
    // Convert start and end dates to Date objects
    var startDateObj = new Date(startDate);
    var endDateObj = new Date(endDate);
    
    // Create a map of all dates in the array for quick lookup
    var dateMap = {};
    allDates.forEach(function(date) {
        dateMap[date] = true;
    });
    
    // Check each date in the range
    var missingDates = [];
    var currentDate = new Date(startDateObj);
    
    while (currentDate <= endDateObj) {
        // Format the current date as YYYY-MM-DD
        var year = currentDate.getFullYear();
        var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        var day = currentDate.getDate().toString().padStart(2, '0');
        var dateStr = year + '-' + month + '-' + day;
        
        // Check if this date is in our array
        if (!dateMap[dateStr]) {
            console.warn('Missing date in range:', dateStr);
            missingDates.push(dateStr);
        }
        
        // Move to next day
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    // Add any missing dates
    if (missingDates.length > 0) {
        console.warn('Found', missingDates.length, 'missing dates in the selected range');
        
        // Add all missing dates to the array
        missingDates.forEach(function(dateStr) {
            console.log('Adding missing date:', dateStr);
            allDates.push(dateStr);
        });
        
        // Sort again after adding missing dates
        allDates.sort(function(a, b) {
            var dateA = new Date(a);
            var dateB = new Date(b);
            return dateA - dateB;
        });
        
        console.log('Re-sorted dates after adding missing dates');
    } else {
        console.log('✓ All dates in the selected range are present');
    }
    
    // Specifically check for date 17 (as it was mentioned as problematic)
    var has17th = false;
    allDates.forEach(function(date) {
        var day = date.split('-')[2];
        if (day === '17') {
            has17th = true;
            console.log('Verified date 17 is present:', date);
        }
    });
    
    if (!has17th) {
        console.error('CRITICAL ERROR: Date 17 is still missing after validation!');
    }
    
    // Clear existing table
    var table = $('#material-shortage-table');
    var thead = table.find('thead');
    var tbody = table.find('tbody');
    
    // Calculate date range difference for logging
    var startParts = startDate.split('-');
    var endParts = endDate.split('-');
    var startObj = new Date(parseInt(startParts[0]), parseInt(startParts[1])-1, parseInt(startParts[2]));
    var endObj = new Date(parseInt(endParts[0]), parseInt(endParts[1])-1, parseInt(endParts[2]));
    var diffTime = Math.abs(endObj - startObj);
    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    console.log('Date range:', startDate, 'to', endDate, '(', diffDays, 'days)');
    
    // Create header row with proper styling
    var headerRow = $('<tr>');
    
    // Function to create header cell with gray gradient background
    function createHeaderCell(className, text) {
        var width = '100px'; // Default width
        
        // Set specific widths based on column type
        if (className === 'label-cell') {
            width = '180px';
        } else if (className === 'h-class-cell') {
            width = '100px';
        } else if (className === 'stock-cell') {
            width = '120px';
        } else if (className === 'part-cell') {
            width = '150px';
        } else if (className === 'desc-cell') {
            width = '200px';
        } else if (className === 'model-cell') {
            width = '150px';
        } else if (className === 'class-cell') {
            width = '100px';
        }
        
        return $('<th>')
            .addClass(className)
            .text(text)
            .css({
                'background': 'linear-gradient(to bottom, rgba(108, 117, 125, 0.85), rgba(108, 117, 125, 0.65))',
                'color': 'white',
                'border': '1px solid rgba(108, 117, 125, 0.5)',
                'min-width': width,
                'width': width,
                'font-weight': '500',
                'font-size': '0.82rem',
                'text-transform': 'uppercase',
                'letter-spacing': '0.6px',
                'font-family': "'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
                'white-space': 'nowrap'
            });
    }
    
    // Add header cells with red styling
    headerRow.append(createHeaderCell('model-cell', 'Model No'));
    headerRow.append(createHeaderCell('h-class-cell', 'H Class'));
    headerRow.append(createHeaderCell('part-cell', 'Part No'));
    headerRow.append(createHeaderCell('desc-cell', 'Description'));
    headerRow.append(createHeaderCell('class-cell', 'Class'));
    headerRow.append(createHeaderCell('label-cell', 'Item'));
    headerRow.append(createHeaderCell('stock-cell', 'Begin Stock'));
    
    // Add date columns to header using sorted dates
    console.log('Adding date columns to header:');
    
    // Verify dates are sorted before adding to header
    console.log('Verifying dates are sorted before adding to header...');
    var isSorted = true;
    var prevDate = null;
    
    for (var i = 0; i < allDates.length; i++) {
        var currentDate = new Date(allDates[i]);
        if (prevDate !== null && currentDate < prevDate) {
            console.error('CRITICAL ERROR: Dates are not properly sorted at index', i, ':', allDates[i], 'comes after', allDates[i-1]);
            isSorted = false;
            break;
        }
        prevDate = currentDate;
    }
    
    if (!isSorted) {
        console.warn('Re-sorting dates before adding to header...');
        allDates.sort(function(a, b) {
            return new Date(a) - new Date(b);
        });
    }
    
    // Now add the properly sorted dates to the header
    allDates.forEach(function(dateStr, index) {
        try {
            // Parse date using native JS
            var dateParts = dateStr.split('-');
            if (dateParts.length === 3) {
                var dayStr = dateParts[2].padStart(2, '0'); // Ensure 2 digits
                var monthStr = dateParts[1].padStart(2, '0'); // Ensure 2 digits
                console.log('Header column', index, ':', dateStr, '=', dayStr + '-' + monthStr);
                
                // Add data-date attribute for easier debugging and reference
                headerRow.append($('<th>')
                    .addClass('date-header text-center')
                    .attr('data-date', dateStr)
                    .text(dayStr)
                    .css({
                        'background': 'linear-gradient(to bottom, rgba(108, 117, 125, 0.85), rgba(108, 117, 125, 0.65))',
                        'color': 'white',
                        'border': '1px solid rgba(108, 117, 125, 0.5)',
                        'min-width': '60px',
                        'width': '60px',
                        'font-weight': '500',
                        'font-size': '0.82rem',
                        'text-transform': 'uppercase',
                        'letter-spacing': '0.6px',
                        'font-family': "'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
                        'white-space': 'nowrap'
                    }));
            } else {
                console.error('Invalid date format for header:', dateStr);
                headerRow.append($('<th>').addClass('date-header text-center').text('??'));
            }
        } catch (error) {
            console.error('Error parsing date for header:', dateStr, error);
            headerRow.append($('<th>').addClass('date-header text-center').text('??'));
        }
    });
    
    // Debug: Log the final header structure and verify date order
    setTimeout(function() {
        console.log('FINAL HEADER STRUCTURE:');
        
        // Collect all date headers for verification
        var dateHeaders = [];
        var dateHeadersText = [];
        var fullDateValues = [];
        
        $('#material-shortage-table thead th').each(function(index) {
            console.log('Header', index, ':', $(this).text());
            
            // Check if this is a date header (has data-date attribute)
            var dateAttr = $(this).attr('data-date');
            if (dateAttr) {
                dateHeaders.push({
                    index: index,
                    date: dateAttr,
                    text: $(this).text()
                });
                dateHeadersText.push($(this).text());
                fullDateValues.push(dateAttr); // Store the full date value
            }
        });
        
        // CRITICAL DEBUG: Log the actual full date values in the header
        console.log('CRITICAL DEBUG - Full date values in header (YYYY-MM-DD):', fullDateValues);
        
        // Check if dates are sorted by day only (ignoring month)
        var dayOnlyValues = fullDateValues.map(function(dateStr) {
            return parseInt(dateStr.split('-')[2]); // Extract day part
        });
        console.log('CRITICAL DEBUG - Day-only values:', dayOnlyValues);
        
        // Check if dates are sorted by full date
        var dateObjects = fullDateValues.map(function(dateStr) {
            return new Date(dateStr + 'T00:00:00Z');
        });
        
        // Log the actual date objects for comparison
        console.log('CRITICAL DEBUG - Date objects:');
        dateObjects.forEach(function(dateObj, index) {
            console.log(index, ':', fullDateValues[index], '=', dateObj.toISOString());
        });
        
        // Verify date headers are in chronological order
        var isChronological = true;
        for (var i = 1; i < dateHeaders.length; i++) {
            var prevDate = new Date(dateHeaders[i-1].date + 'T00:00:00Z');
            var currDate = new Date(dateHeaders[i].date + 'T00:00:00Z');
            
            if (currDate < prevDate) {
                console.error('CRITICAL ERROR: Date headers are not in chronological order!');
                console.error('Date at position', i-1, ':', dateHeaders[i-1].date, '(', dateHeaders[i-1].text, ')');
                console.error('comes AFTER date at position', i, ':', dateHeaders[i].date, '(', dateHeaders[i].text, ')');
                isChronological = false;
                break;
            }
        }
        
        // Check if we're crossing month boundary
        var monthBoundary = false;
        var monthChange = [];
        for (var i = 1; i < fullDateValues.length; i++) {
            var prevMonth = parseInt(fullDateValues[i-1].split('-')[1]);
            var currMonth = parseInt(fullDateValues[i].split('-')[1]);
            
            if (prevMonth !== currMonth) {
                monthBoundary = true;
                monthChange.push({
                    position: i,
                    from: fullDateValues[i-1],
                    to: fullDateValues[i]
                });
            }
        }
        
        if (monthBoundary) {
            console.log('CRITICAL DEBUG - Month boundary detected! Month changes at:', monthChange);
        }
        
        if (isChronological) {
            console.log('✓ VERIFICATION SUCCESSFUL: All date headers are in correct chronological order');
            console.log('Date sequence:', dateHeadersText.join(', '));
            
            // Additional verification for month boundary issue
            if (monthBoundary) {
                console.log('CRITICAL DEBUG - WARNING: Dates are chronologically sorted but cross month boundary!');
                console.log('This may explain why days appear out of order (e.g., 31, 01, 02...)');
            }
        } else {
            console.error('✗ VERIFICATION FAILED: Date headers are NOT in chronological order!');
        }
    }, 500);
    
    thead.append(headerRow);
    
    // Count header columns for validation
    var headerColumnCount = headerRow.find('th').length;
    console.log('Header has', headerColumnCount, 'columns');
    
    // Check if data is valid
    if (!data || !Array.isArray(data) || data.length === 0) {
        console.warn('No data or invalid data received:', data);
        var noDataRow = $('<tr>');
        var noDataCell = $('<td>').attr('colspan', headerColumnCount).addClass('text-center').text('No data available');
        noDataRow.append(noDataCell);
        tbody.append(noDataRow);
        return;
    }
    
    // Group data by model_no, h_class, part_no, description, class
    console.log('Raw data from API:', data);
    console.log('Sample data item structure:', data[0]);
    
    // Group data by model_no and part_no
    var groupedData = {};
    
    // Process each data item
    data.forEach(function(item) {
        if (!item.model_no || !item.part_no) {
            console.warn('Item missing model_no or part_no:', item);
            return;
        }
        
        var key = item.model_no + '_' + item.part_no;
        
        if (!groupedData[key]) {
            groupedData[key] = {
                model_no: item.model_no,
                h_class: item.h_class || '',
                part_no: item.part_no,
                description: item.description || '',
                class: item.class || '',
                begin_stock: parseFloat(item.begin_stock || 0),
                daily_data: {}
            };
            
            // Initialize daily_data for all dates in range
            // Make sure we're using the sorted dates array
            console.log('Initializing daily_data for all dates in range for item:', key);
            
            // Verify dates are sorted before initializing
            var verifiedDates = [...allDates]; // Create a copy to avoid modifying the original
            verifiedDates.sort(function(a, b) {
                return new Date(a) - new Date(b);
            });
            
            // Check if our allDates array is properly sorted
            var datesAreSorted = JSON.stringify(allDates) === JSON.stringify(verifiedDates);
            if (!datesAreSorted) {
                console.error('CRITICAL ERROR: allDates array is not properly sorted when initializing daily_data!');
                console.log('Current order:', allDates);
                console.log('Correct order:', verifiedDates);
                // Use the correctly sorted array
                allDates = verifiedDates;
            }
            
            // Now initialize with the verified sorted dates
            allDates.forEach(function(dateStr, index) {
                if (dateStr && typeof dateStr === 'string') {
                    groupedData[key].daily_data[dateStr] = {
                        use_plan: 0,
                        use_act: 0,
                        eta: 0,
                        inv_no: '',
                        stock_plan: 0,
                        stock_act: 0
                    };
                    
                    if (index % 10 === 0) { // Log every 10th date to avoid console spam
                        console.log('Initialized date', index, ':', dateStr, 'for item', key);
                    }
                }
            });
        }
        
        // Process daily_data from API response (API now returns correct dates)
        if (item.daily_data && typeof item.daily_data === 'object') {
            console.log('Processing daily_data for', key, ':', item.daily_data);
            
            // Iterate through each date in daily_data
            Object.keys(item.daily_data).forEach(function(apiDate) {
                var apiDailyData = item.daily_data[apiDate];
                console.log('Processing API date:', apiDate, 'with data:', apiDailyData);
                
                // Check if this date is within our range using native JS
                try {
                    // Parse dates to compare
                    var isDateInRange = false;
                    
                    if (apiDate && typeof apiDate === 'string' && apiDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        // Check if date is in allDates array (which is already filtered to our range)
                        isDateInRange = allDates.includes(apiDate);
                        
                        console.log('Checking if', apiDate, 'is in range:', isDateInRange);
                    } else {
                        console.warn('Invalid API date format:', apiDate);
                    }
                    
                    if (isDateInRange) {
                        // Date is within range, use it directly
                        groupedData[key].daily_data[apiDate] = {
                            use_plan: parseFloat(apiDailyData.use_plan || 0),
                            use_act: parseFloat(apiDailyData.use_act || 0),
                            eta: parseFloat(apiDailyData.eta || 0),
                            inv_no: apiDailyData.inv_no || '',
                            stock_plan: parseFloat(apiDailyData.stock_plan || 0),
                            stock_act: parseFloat(apiDailyData.stock_act || 0)
                        };
                        
                        console.log('✓ Mapped data to date', apiDate, ':', groupedData[key].daily_data[apiDate]);
                    } else {
                        console.log('✗ API date outside range or invalid:', apiDate, 'ignoring');
                    }
                } catch (error) {
                    console.error('Error processing API date:', apiDate, error);
                }
            });
        } else {
            console.warn('Item has no daily_data or invalid daily_data:', item);
        }
        
        console.log('Processed daily_data for', key, ':', groupedData[key].daily_data);
    });
    
    console.log('Grouped data ready for rendering:', groupedData);
    
    // API already returns correct calculated data, no need for recalculation
    // Just ensure all dates in range exist with default values if missing
    Object.keys(groupedData).forEach(function(key) {
        var group = groupedData[key];
        
        console.log('Ensuring all dates exist for group:', key);
        
        // Use allDates array which is already properly generated and sorted
        allDates.forEach(function(date) {
            if (date && typeof date === 'string') {
                // Only add default values if date doesn't exist
                if (!group.daily_data[date]) {
                    group.daily_data[date] = {
                        use_plan: 0,
                        use_act: 0,
                        eta: 0,
                        inv_no: '',
                        stock_plan: 0,
                        stock_act: 0
                    };
                    console.log('Added default values for missing date:', date);
                }
            }
        });
        
        console.log('Final daily_data for', key, ':', group.daily_data);
    });
    
    var groupKeys = Object.keys(groupedData);
    console.log('Rendering', groupKeys.length, 'groups');
    console.log('GroupedData object:', groupedData);
    console.log('GroupKeys:', groupKeys);
    
    if (groupKeys.length === 0) {
        // No data, show message
        var noDataRow = $('<tr>');
        var noDataCell = $('<td>').attr('colspan', headerColumnCount).addClass('text-center').text('No data available');
        noDataRow.append(noDataCell);
        tbody.append(noDataRow);
    } else {
        // Render each group
        groupKeys.forEach(function(key, index) {
            var group = groupedData[key];
            console.log('Rendering group:', key);
            
            // Create rows for this group
            // First row shows model_no and h_class
            var usePlanRow = $('<tr>').addClass('use-plan-row part-group');
            usePlanRow.append($('<td>').addClass('model-cell').text(group.model_no || ''));
            usePlanRow.append($('<td>').addClass('h-class-cell').text(group.h_class || ''));
            usePlanRow.append($('<td>').addClass('part-cell').text(group.part_no || ''));
            usePlanRow.append($('<td>').addClass('desc-cell').text(group.description || ''));
            usePlanRow.append($('<td>').addClass('class-cell').text(group.class || ''));
            usePlanRow.append($('<td>').addClass('label-cell').text('Use Plan'));
            usePlanRow.append($('<td>').addClass('stock-cell').text('-')); // Use Plan should not show begin_stock
            
            // Create row for use_act
            var useActRow = $('<tr>').addClass('use-act-row');
            useActRow.append($('<td>').addClass('model-cell').text(''));
            useActRow.append($('<td>').addClass('h-class-cell').text(''));
            useActRow.append($('<td>').addClass('part-cell').text(''));
            useActRow.append($('<td>').addClass('desc-cell').text(''));
            useActRow.append($('<td>').addClass('class-cell').text(''));
            useActRow.append($('<td>').addClass('label-cell').text('Use Act'));
            useActRow.append($('<td>').addClass('stock-cell').text(''));
            
            // Create row for eta
            var etaRow = $('<tr>').addClass('eta-row');
            etaRow.append($('<td>').addClass('model-cell').text(''));
            etaRow.append($('<td>').addClass('h-class-cell').text(''));
            etaRow.append($('<td>').addClass('part-cell').text(''));
            etaRow.append($('<td>').addClass('desc-cell').text(''));
            etaRow.append($('<td>').addClass('class-cell').text(''));
            etaRow.append($('<td>').addClass('label-cell').text('ETA'));
            etaRow.append($('<td>').addClass('stock-cell').text(''));
            
            // Create row for inv_no
            var invNoRow = $('<tr>').addClass('inv-no-row');
            invNoRow.append($('<td>').addClass('model-cell').text(''));
            invNoRow.append($('<td>').addClass('h-class-cell').text(''));
            invNoRow.append($('<td>').addClass('part-cell').text(''));
            invNoRow.append($('<td>').addClass('desc-cell').text(''));
            invNoRow.append($('<td>').addClass('class-cell').text(''));
            invNoRow.append($('<td>').addClass('label-cell').text('Inv No'));
            invNoRow.append($('<td>').addClass('stock-cell').text(''));
            
            // Create row for stock_plan
            var stockPlanRow = $('<tr>').addClass('stock-plan-row');
            stockPlanRow.append($('<td>').addClass('model-cell').text(''));
            stockPlanRow.append($('<td>').addClass('h-class-cell').text(''));
            stockPlanRow.append($('<td>').addClass('part-cell').text(''));
            stockPlanRow.append($('<td>').addClass('desc-cell').text(''));
            stockPlanRow.append($('<td>').addClass('class-cell').text(''));
            stockPlanRow.append($('<td>').addClass('label-cell').text('Stock Plan'));
            stockPlanRow.append($('<td>').addClass('stock-cell text-right').text(formatNumber(group.begin_stock)));
            
            // Create row for stock_act
            var stockActRow = $('<tr>').addClass('stock-act-row');
            stockActRow.append($('<td>').addClass('model-cell').text(''));
            stockActRow.append($('<td>').addClass('h-class-cell').text(''));
            stockActRow.append($('<td>').addClass('part-cell').text(''));
            stockActRow.append($('<td>').addClass('desc-cell').text(''));
            stockActRow.append($('<td>').addClass('class-cell').text(''));
            stockActRow.append($('<td>').addClass('label-cell').text('Stock Act'));
            stockActRow.append($('<td>').addClass('stock-cell text-right').text(formatNumber(group.begin_stock)));
            
            // Add date columns using sorted dates
            console.log('Adding date columns to body rows for group:', group.model_no, group.part_no);
            console.log('Processing API date with daily_data:', group.daily_data);
            
            // Ensure group.daily_data exists to prevent errors
            if (!group.daily_data) {
                console.error('daily_data is undefined for group:', group);
                group.daily_data = {};
            }
            
            // Verify dates are sorted before rendering
            var verifiedDates = [...allDates]; // Create a copy to avoid modifying the original
            verifiedDates.sort(function(a, b) {
                return new Date(a) - new Date(b);
            });
            
            // Check if our allDates array is properly sorted
            var datesAreSorted = JSON.stringify(allDates) === JSON.stringify(verifiedDates);
            if (!datesAreSorted) {
                console.error('CRITICAL ERROR: allDates array is not properly sorted when rendering table rows!');
                console.log('Current order:', allDates);
                console.log('Correct order:', verifiedDates);
                // Use the correctly sorted array
                allDates = verifiedDates;
            }
            
            // Now render with the verified sorted dates
            allDates.forEach(function(dateStr, index) {
                // Ensure dateStr is valid before processing
                if (!dateStr) {
                    console.error('Invalid dateStr at index', index);
                    return; // Skip this iteration
                }
                
                var dailyData;
                try {
                    // Parse date using native JS
                    var dateParts = dateStr.split('-');
                    if (dateParts.length === 3) {
                        var dayStr = dateParts[2].padStart(2, '0'); // Ensure 2 digits
                        var monthStr = dateParts[1].padStart(2, '0'); // Ensure 2 digits
                        
                        // Log every 5th date to avoid console spam
                        if (index % 5 === 0) {
                            console.log('Body column', index, ':', dateStr, '=', dayStr + '-' + monthStr);
                        }
                        
                        // Verify this date exists in the group's daily_data
                        if (!group.daily_data[dateStr]) {
                            console.warn('Missing daily data for date', dateStr, 'in group', key);
                            // Add default values
                            group.daily_data[dateStr] = {
                                use_plan: 0,
                                use_act: 0,
                                eta: 0,
                                inv_no: '',
                                stock_plan: 0,
                                stock_act: 0
                            };
                        }
                        
                        dailyData = group.daily_data[dateStr];
                    } else {
                        throw new Error('Invalid date format: ' + dateStr);
                    }
                } catch (error) {
                    console.error('Error processing date:', dateStr, error);
                    dailyData = {
                        use_plan: 0,
                        use_act: 0,
                        eta: 0,
                        inv_no: '',
                        stock_plan: 0,
                        stock_act: 0
                    };
                }
                
                // Format numbers and add classes for negative values
                var usePlanValue = formatNumber(dailyData.use_plan || 0);
                var useActValue = formatNumber(dailyData.use_act || 0);
                var etaValue = formatNumber(dailyData.eta || 0);
                var stockPlanValue = formatNumber(dailyData.stock_plan || 0);
                var stockActValue = formatNumber(dailyData.stock_act || 0);
                
                // Add classes for negative values - ensure values are numbers
                var usePlanClass = (dailyData.use_plan || 0) < 0 ? 'negative-value' : '';
                var useActClass = (dailyData.use_act || 0) < 0 ? 'negative-value' : '';
                var etaClass = (dailyData.eta || 0) < 0 ? 'negative-value' : '';
                var stockPlanClass = (dailyData.stock_plan || 0) < 0 ? 'negative-value' : '';
                var stockActClass = (dailyData.stock_act || 0) < 0 ? 'negative-value' : '';
                
                // Append cells with proper formatting and date-column class
                usePlanRow.append($('<td>').addClass('date-column text-right ' + usePlanClass).text(usePlanValue));
                useActRow.append($('<td>').addClass('date-column text-right ' + useActClass).text(useActValue));
                etaRow.append($('<td>').addClass('date-column text-right ' + etaClass).text(etaValue));
                invNoRow.append($('<td>').addClass('date-column text-center').text(dailyData.inv_no || ''));
                stockPlanRow.append($('<td>').addClass('date-column text-right ' + stockPlanClass).text(stockPlanValue));
                stockActRow.append($('<td>').addClass('date-column text-right ' + stockActClass).text(stockActValue));
            });
            
            // Append all rows to tbody as a group
            var partGroup = $('<div>').addClass('part-group-container');
            tbody.append(usePlanRow);
            tbody.append(useActRow);
            tbody.append(etaRow);
            tbody.append(invNoRow);
            tbody.append(stockPlanRow);
            tbody.append(stockActRow);
            
            // Add spacer row after each group
            var spacerRow = $('<tr>').addClass('spacer-row');
            var spacerCell = $('<td>').attr('colspan', headerColumnCount).css('height', '10px');
            spacerRow.append(spacerCell);
            tbody.append(spacerRow);
            
            // Verify column count for each row
            console.log('Use Plan row columns:', usePlanRow.find('td').length, 'should be', headerColumnCount);
            console.log('Use Act row columns:', useActRow.find('td').length, 'should be', headerColumnCount);
            console.log('ETA row columns:', etaRow.find('td').length, 'should be', headerColumnCount);
            console.log('INV_NO row columns:', invNoRow.find('td').length, 'should be', headerColumnCount);
            console.log('Stock Plan row columns:', stockPlanRow.find('td').length, 'should be', headerColumnCount);
            console.log('Stock Act row columns:', stockActRow.find('td').length, 'should be', headerColumnCount);
        });
    }
    
    // Log the number of columns in the first row of the body
    if (tbody.find('tr').length > 0) {
        console.log('Number of columns in first body row:', tbody.find('tr:first').find('td').length);
    }
    
    // Ensure all rows have the same number of columns as the header
    // Use the headerColumnCount that was calculated earlier (line 634)
    console.log('Ensuring all rows have', headerColumnCount, 'columns');
    
    // Final check before initializing DataTables
    var allRowsValid = true;
    
    // Log detailed column counts for debugging
    console.log('HEADER COLUMN COUNT:', headerColumnCount);
    console.log('HEADER COLUMNS:', thead.find('th').map(function() { return $(this).text(); }).get());
    
    // First verify the header has the expected number of columns
    var actualHeaderCount = thead.find('th').length;
    if (actualHeaderCount !== headerColumnCount) {
        console.error('Header column count mismatch:', actualHeaderCount, 'should be', headerColumnCount);
        // Update the headerColumnCount to match actual header
        headerColumnCount = actualHeaderCount;
        console.log('Updated headerColumnCount to', headerColumnCount);
    }
    
    // Now check and fix each row
    tbody.find('tr').each(function(index) {
        var rowColumnCount = $(this).find('td').length;
        
        // Skip spacer rows in the check
        if ($(this).hasClass('spacer-row')) {
            // Update colspan for spacer rows to match header
            $(this).find('td').attr('colspan', headerColumnCount);
            return; // continue to next iteration
        }
        
        console.log('Row', index, 'columns:', rowColumnCount, 'should be', headerColumnCount);
        
        if (rowColumnCount !== headerColumnCount) {
            console.error('Row column count mismatch at row', index, ':', rowColumnCount, 'should be', headerColumnCount);
            allRowsValid = false;
            
            // Fix the row by adding missing columns or removing extra columns
            if (rowColumnCount < headerColumnCount) {
                // Add missing columns
                for (var i = rowColumnCount; i < headerColumnCount; i++) {
                    $(this).append($('<td>').addClass('date-column').text(''));
                }
                console.log('Added', (headerColumnCount - rowColumnCount), 'columns to row', index);
            } else if (rowColumnCount > headerColumnCount) {
                // Remove extra columns
                $(this).find('td').slice(headerColumnCount).remove();
                console.log('Removed', (rowColumnCount - headerColumnCount), 'columns from row', index);
            }
        }
    });
    
    if (!allRowsValid) {
        console.warn('Some rows had incorrect column counts and were fixed');
    }
    
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#material-shortage-table')) {
        $('#material-shortage-table').DataTable().destroy();
        console.log('Existing DataTable destroyed');
    }
    
    // Ensure table has proper structure before initializing
    var tableHasData = tbody.find('tr').length > 0;
    
    if (!tableHasData) {
        console.warn('Table has no data rows');
    }
    
    // Additional validation to ensure table structure is valid for DataTables
    console.log('Performing additional validation on table structure...');
    
    // Check for non-TR elements in tbody
    var nonTrElements = tbody.children().not('tr');
    if (nonTrElements.length > 0) {
        console.error('Found invalid elements in tbody:', nonTrElements.length);
        // Remove invalid elements
        nonTrElements.remove();
        console.log('Removed invalid elements from tbody');
    }
    
    // Check for empty rows or rows without cells
    tbody.find('tr').each(function(index) {
        // Skip spacer rows in this check
        if ($(this).hasClass('spacer-row')) {
            return;
        }
        
        if ($(this).children().length === 0) {
            console.error('Found empty row at index', index);
            $(this).remove();
            console.log('Removed empty row at index', index);
        }
    });
    
    // Ensure all cells are properly created as TD elements
    tbody.find('tr').each(function(index) {
        $(this).children().each(function(cellIndex) {
            if (this.tagName.toLowerCase() !== 'td') {
                console.error('Found non-TD element in row', index, 'cell', cellIndex);
                // Replace with proper TD
                var content = $(this).html();
                var newTd = $('<td>').html(content).attr('class', $(this).attr('class'));
                $(this).replaceWith(newTd);
                console.log('Replaced non-TD element with proper TD in row', index, 'cell', cellIndex);
            }
        });
    });
    
    console.log('Skipping DataTables initialization, using basic table styling');
    
    // Apply basic Bootstrap styling to the table
    $('#material-shortage-table').addClass('table table-bordered table-striped');
    
    // Add scrolling capability with CSS
    $('#table-container').css({
        'overflow-x': 'auto',
        'max-height': '60vh',
        'overflow-y': 'auto'
    });
    
    // Make sure header cells have proper styling
    $('#material-shortage-table thead th').addClass('text-center sticky-top bg-primary text-white');
    
    // Add zebra striping to rows
    $('#material-shortage-table tbody tr:not(.spacer-row):even').addClass('table-light');
    
    // Add hover effect
    $('#material-shortage-table tbody tr:not(.spacer-row)').hover(
        function() { $(this).addClass('table-hover'); },
        function() { $(this).removeClass('table-hover'); }
    );
    
    // Make sure negative values are highlighted
    $('.negative-value').css('color', 'red');
    
    // Hide loading indicator if any
    $('.dataTables_processing').hide();
    
    console.log('Basic table styling applied successfully');
    
    // Show success message
    toastr.success('Data loaded successfully');
    
    // Hide any error messages
    $('#error-message').hide();
}

function exportToExcel() {
    var dateRange = $('#date-range').val().split(' to ');
    var startDate = dateRange[0];
    var endDate = dateRange[1];
    var partNo = $('#part-no').val();
    var hClass = $('#h-class').val();
    var classVal = $('#class').val();
    var minusOnly = $('#minus-only').prop('checked');

    var form = $('<form>', {
        'action': '<?= base_url('material-shortage/export') ?>',
        'method': 'post',
        'target': '_blank'
    });

    form.append($('<input>', {
        'name': 'start_date',
        'value': startDate,
        'type': 'hidden'
    }));

    form.append($('<input>', {
        'name': 'end_date',
        'value': endDate,
        'type': 'hidden'
    }));

    form.append($('<input>', {
        'name': 'part_no',
        'value': partNo,
        'type': 'hidden'
    }));

    form.append($('<input>', {
        'name': 'h_class',
        'value': hClass,
        'type': 'hidden'
    }));

    form.append($('<input>', {
        'name': 'class',
        'value': classVal,
        'type': 'hidden'
    }));

    form.append($('<input>', {
        'name': 'minus_only',
        'value': minusOnly,
        'type': 'hidden'
    }));

    $('body').append(form);
    form.submit();
    form.remove();
}
</script>
<?= $this->endSection() ?>
