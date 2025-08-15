<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title m-0">Material Shortage Report</h3>
                    <div class="d-flex">
                        <button type="button" class="btn btn-primary btn-sm me-2" id="toggle-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-success btn-sm" id="export-excel">
                            <i class="fas fa-file-excel"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Filter section - hidden by default -->
                <div class="card-body filter-container" style="display: none; border-bottom: 1px solid #dee2e6;">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Date Range</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                    <input type="text" class="form-control" id="date-range">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Model No</label>
                                <select class="form-control select2-dropdown" id="model-no" data-placeholder="All Models">
                                    <option value="">All Models</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">H Class</label>
                                <select class="form-control select2-dropdown" id="h-class" data-placeholder="All H Classes">
                                    <option value="">All H Classes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Class</label>
                                <select class="form-control select2-dropdown" id="class" data-placeholder="All Classes">
                                    <option value="">All Classes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Show Options</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="minus-only">
                                    <label class="form-check-label" for="minus-only">Minus Only</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="search-btn">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover" id="material-shortage-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">MODEL_NO</th>
                                    <th class="text-nowrap">H Class</th>
                                    <th class="text-nowrap">PART_NO</th>
                                    <th class="text-nowrap">Desc</th>
                                    <th class="text-nowrap">Class</th>
                                    <th class="text-nowrap"></th>
                                    <th class="text-nowrap">Begin_Stock</th>
                                    <!-- Kolom tanggal akan ditambahkan secara dinamis -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan diisi secara dinamis -->
                            </tbody>
                        </table>
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
    /* Filter section styling */
    .filter-container {
        transition: all 0.3s ease-in-out;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1rem 0.5rem 1rem;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 0.75rem 1.25rem;
    }
    
    /* Table styling */
    .table-responsive {
        overflow-x: auto;
        margin: 0;
    }
    
    #material-shortage-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }
    
    #material-shortage-table th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        font-weight: 600;
        text-align: center;
        padding: 0.5rem;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    #material-shortage-table td {
        padding: 0.4rem 0.5rem;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    #material-shortage-table th, 
    #material-shortage-table td {
        white-space: nowrap;
    }
    
    /* Date columns styling */
    .date-column {
        text-align: center;
        min-width: 40px;
        font-size: 0.8rem;
    }
    
    /* Negative value styling */
    .negative-value {
        color: #dc3545;
        font-weight: 500;
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
<script src="<?= base_url('plugins/moment/moment.min.js') ?>"></script>
<script src="<?= base_url('plugins/daterangepicker/daterangepicker.js') ?>"></script>
<script src="<?= base_url('plugins/select2/js/select2.full.min.js') ?>"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
<script>
$(function() {
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
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
           'Next 30 Days': [moment(), moment().add(29, 'days')]
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
    var startDate = dateRange[0];
    var endDate = dateRange[1];
    var modelNo = $('#model-no').val();
    var hClass = $('#h-class').val();
    var classVal = $('#class').val();
    var minusOnly = $('#minus-only').prop('checked');
    
    // Show loading overlay
    $('#loading-overlay').show();
    
    $.ajax({
        url: '<?= base_url('material-shortage/data') ?>',
        type: 'POST',
        data: {
            start_date: startDate,
            end_date: endDate,
            model_no: modelNo,
            h_class: hClass,
            class: classVal,
            minus_only: minusOnly
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderTable(response.data, startDate, endDate);
                
                // Jika tabel kosong, tampilkan pesan
                if (response.data.length === 0) {
                    $('#material-shortage-table tbody').html('<tr><td colspan="100%" class="text-center py-3">No data available</td></tr>');
                }
            } else {
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
    var table = $('#material-shortage-table');
    var thead = table.find('thead');
    var tbody = table.find('tbody');
    
    // Clear existing data
    thead.find('tr').find('th:gt(6)').remove();
    tbody.empty();
    
    // Calculate date difference
    var start = moment(startDate);
    var end = moment(endDate);
    var diff = end.diff(start, 'days');
    
    // Add date columns to header
    var headerRow = thead.find('tr');
    for (var i = 0; i <= diff; i++) {
        var date = moment(startDate).add(i, 'days');
        // Hanya tampilkan tanggal (DD) saja
        headerRow.append('<th class="date-column">' + date.format('DD') + '</th>');
    }
    
    // Group data by model_no and part_no
    var groupedData = {};
    data.forEach(function(item) {
        var key = item.model_no + '_' + item.part_no;
        if (!groupedData[key]) {
            groupedData[key] = {
                model_no: item.model_no,
                h_class: item.h_class,
                part_no: item.part_no,
                description: item.description,
                class: item.class,
                begin_stock: item.begin_stock,
                daily: {}
            };
        }
        
        // Store daily data
        if (!groupedData[key].daily[item.date]) {
            groupedData[key].daily[item.date] = {};
        }
        
        groupedData[key].daily[item.date].use_plan = item.use_plan;
        groupedData[key].daily[item.date].use_act = item.use_act;
        groupedData[key].daily[item.date].eta = item.eta;
        groupedData[key].daily[item.date].inv_no = item.inv_no;
        groupedData[key].daily[item.date].stock_plan = item.stock_plan;
        groupedData[key].daily[item.date].stock_act = item.stock_act;
    });
    
    // Render rows for each group
    Object.values(groupedData).forEach(function(group, index) {
        var modelNo = group.model_no || '';
        var hClass = group.h_class || '';
        var partNo = group.part_no || '';
        var description = group.description || '';
        var classVal = group.class || '';
        var beginStock = group.begin_stock || 0;
        var dailyData = group.daily;
        
        // Create part group container
        var partGroupClass = 'part-group part-group-' + index;
        
        // Use Plan row
        var usePlanRow = $('<tr class="' + partGroupClass + '">');
        usePlanRow.append('<td class="fw-bold">' + modelNo + '</td>');
        usePlanRow.append('<td>' + hClass + '</td>');
        usePlanRow.append('<td>' + partNo + '</td>');
        usePlanRow.append('<td>' + description + '</td>');
        usePlanRow.append('<td>' + classVal + '</td>');
        usePlanRow.append('<td class="fw-bold">Use Plan</td>');
        usePlanRow.append('<td></td>'); // Begin Stock kosong untuk Use Plan
        
        // Add daily Use Plan data
        for (var i = 0; i <= diff; i++) {
            var date = moment(startDate).add(i, 'days').format('YYYY-MM-DD');
            var value = dailyData[date] && dailyData[date].use_plan ? dailyData[date].use_plan : '';
            usePlanRow.append('<td class="text-center">' + value + '</td>');
        }
        
        tbody.append(usePlanRow);
        
        // Use Act row
        var useActRow = $('<tr class="' + partGroupClass + '">');
        useActRow.append('<td></td>');
        useActRow.append('<td></td>');
        useActRow.append('<td></td>');
        useActRow.append('<td></td>');
        useActRow.append('<td></td>');
        useActRow.append('<td class="fw-bold">Use Act</td>');
        useActRow.append('<td></td>'); // Begin Stock kosong untuk Use Act
        
        // Add daily Use Act data
        for (var i = 0; i <= diff; i++) {
            var date = moment(startDate).add(i, 'days').format('YYYY-MM-DD');
            var value = dailyData[date] && dailyData[date].use_act ? dailyData[date].use_act : '';
            useActRow.append('<td class="text-center">' + value + '</td>');
        }
        
        tbody.append(useActRow);
        
        // ETA row
        var etaRow = $('<tr class="' + partGroupClass + '">');
        etaRow.append('<td></td>');
        etaRow.append('<td></td>');
        etaRow.append('<td></td>');
        etaRow.append('<td></td>');
        etaRow.append('<td></td>');
        etaRow.append('<td class="fw-bold">ETA_MEAINA</td>');
        etaRow.append('<td></td>'); // Begin Stock kosong untuk ETA
        
        // Add daily ETA data
        for (var i = 0; i <= diff; i++) {
            var date = moment(startDate).add(i, 'days').format('YYYY-MM-DD');
            var value = dailyData[date] && dailyData[date].eta ? dailyData[date].eta : '';
            etaRow.append('<td class="text-center">' + value + '</td>');
        }
        
        tbody.append(etaRow);
        
        // INV_NO row
        var invRow = $('<tr class="' + partGroupClass + '">');
        invRow.append('<td></td>');
        invRow.append('<td></td>');
        invRow.append('<td></td>');
        invRow.append('<td></td>');
        invRow.append('<td></td>');
        invRow.append('<td class="fw-bold">INV_NO</td>');
        invRow.append('<td></td>'); // Begin Stock kosong untuk INV_NO
        
        // Add daily INV_NO data
        for (var i = 0; i <= diff; i++) {
            var date = moment(startDate).add(i, 'days').format('YYYY-MM-DD');
            var value = dailyData[date] && dailyData[date].inv_no ? dailyData[date].inv_no : '';
            invRow.append('<td class="text-center">' + value + '</td>');
        }
        
        tbody.append(invRow);
        
        // Stock Plan row
        var stockPlanRow = $('<tr class="' + partGroupClass + '">');
        stockPlanRow.append('<td></td>');
        stockPlanRow.append('<td></td>');
        stockPlanRow.append('<td></td>');
        stockPlanRow.append('<td></td>');
        stockPlanRow.append('<td></td>');
        stockPlanRow.append('<td class="fw-bold">Stock Plan</td>');
        stockPlanRow.append('<td class="text-center">' + beginStock + '</td>'); // Begin Stock untuk Stock Plan
        
        // Add daily Stock Plan data
        for (var i = 0; i <= diff; i++) {
            var date = moment(startDate).add(i, 'days').format('YYYY-MM-DD');
            var value = dailyData[date] && dailyData[date].stock_plan !== undefined ? dailyData[date].stock_plan : 0;
            var cell = $('<td class="text-center">' + value + '</td>');
            
            // Highlight negative values
            if (value < 0) {
                cell.addClass('negative-value');
            }
            
            stockPlanRow.append(cell);
        }
        
        tbody.append(stockPlanRow);
        
        // Stock Act row
        var stockActRow = $('<tr class="' + partGroupClass + '">');
        stockActRow.append('<td></td>');
        stockActRow.append('<td></td>');
        stockActRow.append('<td></td>');
        stockActRow.append('<td></td>');
        stockActRow.append('<td></td>');
        stockActRow.append('<td class="fw-bold">Stock Act</td>');
        stockActRow.append('<td class="text-center">' + beginStock + '</td>'); // Begin Stock untuk Stock Act
        
        // Add daily Stock Act data
        for (var i = 0; i <= diff; i++) {
            var date = moment(startDate).add(i, 'days').format('YYYY-MM-DD');
            var value = dailyData[date] && dailyData[date].stock_act !== undefined ? dailyData[date].stock_act : 0;
            var cell = $('<td class="text-center">' + value + '</td>');
            
            // Highlight negative values
            if (value < 0) {
                cell.addClass('negative-value');
            }
            
            stockActRow.append(cell);
        }
        
        tbody.append(stockActRow);
        
        // Add spacer row
        tbody.append('<tr class="spacer-row"><td colspan="' + (7 + diff + 1) + '"></td></tr>');
    });
    
    // Initialize DataTables with fixed header if not already initialized
    if (!$.fn.DataTable.isDataTable('#material-shortage-table')) {
        $('#material-shortage-table').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            fixedHeader: {
                header: true,
                headerOffset: 60
            },
            scrollX: true,
            scrollY: '60vh',
            scrollCollapse: true,
            autoWidth: true
        });
    } else {
        // Refresh the DataTable
        $('#material-shortage-table').DataTable().draw();
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
