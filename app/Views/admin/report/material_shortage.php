<?= $this->extend('admin/layout') ?>

<?= $this->section('styles') ?>
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<!-- DateRangePicker CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css" rel="stylesheet" />
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css" rel="stylesheet">
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
                                <label for="model-no" class="form-label fw-bold">Model No</label>
                                <select class="form-control select2-dropdown" id="model-no" data-placeholder="All Models" style="height: 38px; width: 100%;">
                                    <option value="">All Models</option>
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
                    <div id="reportTableContainer" class="table-responsive shadow-sm rounded">
                        <table id="material-shortage-table" class="table table-bordered">
                            <thead>
                                <tr id="headerRow" class="bg-light">
                                    <!-- Headers will be added dynamically -->
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <!-- Report data will be added dynamically -->
                            </tbody>
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
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
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
        font-size: 0.85rem;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    #material-shortage-table tr.bg-light {
        background-color: #f8f9fc !important;
    }
    
    #material-shortage-table th {
        vertical-align: bottom;
        text-align: center;
        padding: 0.85rem 0.75rem;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        color: #2d3748;
        white-space: nowrap;
        background: linear-gradient(to bottom, #f7fafc, #edf2f7) !important;
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    
    /* Add subtle hover effect to headers */
    #material-shortage-table th:hover {
        background: linear-gradient(to bottom,#edf2f7, #e2e8f0) !important;
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
    
    #material-shortage-table td {
        padding: 0.65rem 0.75rem;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
        text-align: center;
        transition: all 0.2s ease;
        background-color: white;
    }
    
    #material-shortage-table tbody tr:hover td {
        background-color: #f8fafc;
    }
    
    /* Zebra striping for better readability */
    #material-shortage-table tbody tr:nth-child(even) td:not([class*="-cell"]) {
        background-color: #fafafa;
    }
    
    #material-shortage-table tbody tr:nth-child(even):hover td:not([class*="-cell"]) {
        background-color: #f8fafc;
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
        background-color: #fee2e2 !important;
        color: #dc2626;
        font-weight: 600;
        position: relative;
    }
    
    .negative-value:hover {
        background-color: #fecaca !important;
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<!-- Date Range Picker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
<!-- Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
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
    
    // Toggle filter section
    $('#toggle-filter').on('click', function() {
        const $filterContainer = $('.filter-container');
        const $icon = $(this).find('i');
        
        $filterContainer.slideToggle(300, function() {
            if ($filterContainer.is(':visible')) {
                $('#toggle-filter').removeClass('btn-primary').addClass('btn-danger active');
                $icon.removeClass('fa-filter').addClass('fa-times');
            } else {
                $('#toggle-filter').removeClass('btn-danger active').addClass('btn-primary');
                $icon.removeClass('fa-times').addClass('fa-filter');
            }
        });
    });

    // Load model_no options
    loadModelOptions();
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

function loadModelOptions() {
    $.ajax({
        url: '<?= base_url('material-shortage/models') ?>',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#model-no').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                var options = '<option value="">All Models</option>';
                $.each(response.models, function(index, model) {
                    options += '<option value="' + model.model_no + '">' + model.model_no + '</option>';
                });
                $('#model-no').html(options);
            } else {
                toastr.error('Failed to load model options');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Error loading model options: ' + error);
        },
        complete: function() {
            $('#model-no').prop('disabled', false);
        }
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
    var modelNo = $('#model-no').val();
    var hClass = $('#h-class').val();
    var classVal = $('#class').val();
    var minusOnly = $('#minus-only').prop('checked');
    
    // Validasi tanggal
    if (!startDate || !endDate) {
        alert('Please select a valid date range');
        return;
    }
    
    // Pastikan format YYYY-MM-DD
    var startDateFormatted = moment(startDate).format('YYYY-MM-DD');
    var endDateFormatted = moment(endDate).format('YYYY-MM-DD');
    
    // Show loading overlay
    $('#loading-overlay').show();
    
    // Log request parameters
    console.log('Material Shortage Request Parameters:', {
        start_date: startDateFormatted,
        end_date: endDateFormatted,
        model_no: modelNo,
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
            model_no: modelNo,
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
                    renderTable(response.data, startDateFormatted, endDateFormatted);
                } else {
                    console.log('No data or empty array received');
                    $('#material-shortage-table tbody').html('<tr><td colspan="100%" class="text-center py-3">No data available</td></tr>');
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

function renderTable(data, startDate, endDate) {
    console.log('Rendering table with data:', data);
    console.log('Date range:', startDate, 'to', endDate);
    
    // Clear existing table
    var table = $('#material-shortage-table');
    var thead = table.find('thead');
    var tbody = table.find('tbody');
    
    // Clear previous data
    thead.empty();
    tbody.empty();
    
    // FIXED: startDate and endDate are already in YYYY-MM-DD format from the clean AJAX call
    var startDateFormatted = startDate;
    var endDateFormatted = endDate;
    
    console.log('Using dates:', startDateFormatted, 'to', endDateFormatted);
    
    // Calculate date difference using properly formatted dates
    var start = moment(startDateFormatted);
    var end = moment(endDateFormatted);
    var diff = end.diff(start, 'days');
    
    console.log('Date range:', startDateFormatted, 'to', endDateFormatted, '(', diff, 'days)');
    
    // Create header row with proper styling
    var headerRow = $('<tr>');
    headerRow.append($('<th>').addClass('model-cell').text('Model No'));
    headerRow.append($('<th>').addClass('h-class-cell').text('H Class'));
    headerRow.append($('<th>').addClass('part-cell').text('Part No'));
    headerRow.append($('<th>').addClass('desc-cell').text('Description'));
    headerRow.append($('<th>').addClass('class-cell').text('Class'));
    headerRow.append($('<th>').addClass('label-cell').text('Item'));
    headerRow.append($('<th>').addClass('stock-cell').text('Begin Stock'));
    
    // Add date columns to header
    for (var i = 0; i <= diff; i++) {
        var date = moment(startDateFormatted).add(i, 'days');
        headerRow.append($('<th>').addClass('date-header date-column').text(date.format('DD')));
    }
    
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
            for (var i = 0; i <= diff; i++) {
                var dateStr = moment(startDateFormatted).add(i, 'days').format('YYYY-MM-DD');
                groupedData[key].daily_data[dateStr] = {
                    use_plan: 0,
                    use_act: 0,
                    eta: 0,
                    inv_no: '',
                    stock_plan: 0,
                    stock_act: 0
                };
            }
        }
        
        // Process daily_data from API response (API now returns correct dates)
        if (item.daily_data && typeof item.daily_data === 'object') {
            console.log('Processing daily_data for', key, ':', item.daily_data);
            
            // Iterate through each date in daily_data
            Object.keys(item.daily_data).forEach(function(apiDate) {
                var apiDailyData = item.daily_data[apiDate];
                console.log('Processing API date:', apiDate, 'with data:', apiDailyData);
                
                // Check if this date is within our range
                var apiMoment = moment(apiDate, 'YYYY-MM-DD', true);
                console.log('Checking date range for', apiDate, '- start:', start.format('YYYY-MM-DD'), 'end:', end.format('YYYY-MM-DD'));
                console.log('API moment valid:', apiMoment.isValid(), 'isSameOrAfter:', apiMoment.isSameOrAfter(start), 'isSameOrBefore:', apiMoment.isSameOrBefore(end));
                
                if (apiMoment.isValid() && apiMoment.isSameOrAfter(start) && apiMoment.isSameOrBefore(end)) {
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
        
        // Generate dates array from startDate to endDate
        for (var i = 0; i <= diff; i++) {
            var date = moment(startDateFormatted).add(i, 'days').format('YYYY-MM-DD');
            
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
            
            // Add date columns
            for (var i = 0; i <= diff; i++) {
                var date = moment(startDateFormatted).add(i, 'days');
                var dateStr = date.format('YYYY-MM-DD');
                var dailyData = group.daily_data[dateStr] || {
                    use_plan: 0,
                    use_act: 0,
                    eta: 0,
                    inv_no: '',
                    stock_plan: 0,
                    stock_act: 0
                };
                
                // Format numbers and add classes for negative values
                var usePlanValue = formatNumber(dailyData.use_plan);
                var useActValue = formatNumber(dailyData.use_act);
                var etaValue = formatNumber(dailyData.eta);
                var stockPlanValue = formatNumber(dailyData.stock_plan);
                var stockActValue = formatNumber(dailyData.stock_act);
                
                // Add classes for negative values
                var usePlanClass = dailyData.use_plan < 0 ? 'negative-value' : '';
                var useActClass = dailyData.use_act < 0 ? 'negative-value' : '';
                var etaClass = dailyData.eta < 0 ? 'negative-value' : '';
                var stockPlanClass = dailyData.stock_plan < 0 ? 'negative-value' : '';
                var stockActClass = dailyData.stock_act < 0 ? 'negative-value' : '';
                
                // Append cells with proper formatting and date-column class
                usePlanRow.append($('<td>').addClass('date-column text-right ' + usePlanClass).text(usePlanValue));
                useActRow.append($('<td>').addClass('date-column text-right ' + useActClass).text(useActValue));
                etaRow.append($('<td>').addClass('date-column text-right ' + etaClass).text(etaValue));
                invNoRow.append($('<td>').addClass('date-column text-center').text(dailyData.inv_no || ''));
                stockPlanRow.append($('<td>').addClass('date-column text-right ' + stockPlanClass).text(stockPlanValue));
                stockActRow.append($('<td>').addClass('date-column text-right ' + stockActClass).text(stockActValue));
            }
            
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
    tbody.find('tr').each(function() {
        var rowColumnCount = $(this).find('td').length;
        
        // Skip spacer rows in the check
        if ($(this).hasClass('spacer-row')) {
            return; // continue to next iteration
        }
        
        if (rowColumnCount !== headerColumnCount) {
            console.error('Row column count mismatch:', rowColumnCount, 'should be', headerColumnCount);
            allRowsValid = false;
            
            // Fix the row by adding missing columns or removing extra columns
            if (rowColumnCount < headerColumnCount) {
                // Add missing columns
                for (var i = rowColumnCount; i < headerColumnCount; i++) {
                    $(this).append($('<td>').text(''));
                }
            } else if (rowColumnCount > headerColumnCount) {
                // Remove extra columns
                $(this).find('td').slice(headerColumnCount).remove();
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
    
    try {
        // Initialize DataTables with error handling and minimal configuration
        var dataTable = $('#material-shortage-table').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            processing: true,
            fixedHeader: true,
            scrollX: true,
            scrollY: '60vh',
            scrollCollapse: true,
            columnDefs: [{
                defaultContent: '',
                targets: '_all'
            }],
            language: {
                emptyTable: 'No data available'
            }
        });
        
        console.log('DataTable initialized successfully');
    } catch (error) {
        console.error('Error initializing DataTable:', error);
        $('#error-message').text('Error initializing table: ' + error.message).show();
    }
}

function exportToExcel() {
    var dateRange = $('#date-range').val().split(' to ');
    var startDate = dateRange[0];
    var endDate = dateRange[1];
    var modelNo = $('#model-no').val();
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
        'name': 'model_no',
        'value': modelNo,
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
