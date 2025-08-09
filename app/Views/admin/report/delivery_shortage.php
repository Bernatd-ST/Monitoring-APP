<?= $this->extend('admin/layout') ?>

<?= $this->section('page_buttons') ?>
<div class="btn-toolbar mb-2 mb-md-0">
    <button type="button" class="btn btn-sm btn-outline-primary me-2" id="toggleFilter">
        <i class="fas fa-filter"></i> Filter
    </button>
    <button type="button" class="btn btn-sm btn-success" id="exportExcel">
        <i class="fas fa-file-excel"></i> Export to Excel
    </button>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(to right, #4e73df, #224abe);">
        <h6 class="m-0 font-weight-bold text-white">Delivery Shortage Report</h6>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div id="filterSection" class="mb-4" style="display: none;">
            <div class="filter-container p-3 border rounded shadow-sm bg-light">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="dateRange" class="form-label fw-bold">Date Range</label>
                        <input type="text" class="form-control" id="dateRange" name="dateRange" style="height: 38px;">
                    </div>
                    <div class="col-md-4">
                        <label for="modelFilter" class="form-label fw-bold">Model</label>
                        <select class="form-control select2" id="modelFilter" name="model" style="height: 38px; width: 100%;">
                            <option value="">All Models</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="minusOnly" name="minusOnly">
                            <label class="form-check-label fw-bold" for="minusOnly">
                                Minus Only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-primary w-100" id="applyFilter">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="text-center my-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading report data...</p>
        </div>

        <!-- Report Table -->
        <div id="reportTableContainer" class="table-responsive shadow-sm rounded" style="display: none;">
            <style>
                #reportTable {
                    table-layout: fixed;
                    width: 100%;
                    border-collapse: collapse;
                    border-radius: 8px;
                    overflow: hidden;
                    font-size: 14px;
                }
                #reportTable th, #reportTable td {
                    text-align: center;
                    border: 1px solid #e9ecef;
                    vertical-align: middle;
                    padding: 10px 6px;
                    transition: background-color 0.2s;
                }
                #reportTable th {
                    background-color: #f1f5f9;
                    font-weight: 600;
                    color: #334155;
                    position: sticky;
                    top: 0;
                    z-index: 10;
                    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                }
                #reportTable th.date-header {
                    width: 60px;
                }
                #reportTable td.negative-value {
                    background-color: rgba(220, 53, 69, 0.1);
                    color: #dc3545;
                    font-weight: 600;
                }
                #reportTable tr:hover td {
                    background-color: rgba(0,0,0,0.02);
                }
                .model-cell {
                    width: 120px;
                    background-color: #f8fafc !important;
                    font-weight: 600;
                }
                .class-cell {
                    width: 80px;
                    background-color: #f8fafc !important;
                }
                .label-cell {
                    width: 100px;
                    font-weight: 600;
                    background-color: #f8fafc !important;
                    color: #334155;
                }
                .stock-cell {
                    width: 100px;
                    background-color: #f8fafc !important;
                }
                .negative-value {
                    color: #dc3545;
                    font-weight: 600;
                }
                .select2-dropdown-scrollable .select2-results__options {
                    max-height: 200px;
                    overflow-y: auto;
                }
            </style>
            <table id="reportTable" class="table table-bordered">
                <thead>
                    <tr id="headerRow">
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
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap4 .select2-dropdown-scrollable {
        max-height: 300px;
        overflow-y: auto;
    }
    .select2-container--bootstrap4 .select2-selection--single {
        height: 38px !important;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
        top: 60%;
        border-color: #343a40 transparent transparent;
        border-style: solid;
        border-width: 5px 4px 0;
        width: 0;
        height: 0;
        left: 50%;
        margin-left: -4px;
        margin-top: -2px;
        position: absolute;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 26px;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    .select2-results__message {
        color: #6c757d;
    }
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: 38px !important;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
        box-sizing: border-box;
        list-style: none;
        margin: 0;
        padding: 0 8px;
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize date range picker
    $('#dateRange').daterangepicker({
        singleDatePicker: false,
        showDropdowns: true,
        locale: {
            format: 'YYYY-MM-DD'
        },
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month')
    });

    // Initialize select2
    $('#modelFilter').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Select Model',
        allowClear: true,
        maximumSelectionSize: 1,
        dropdownAutoWidth: true,
        minimumResultsForSearch: 0, // Enable search for any number of items
        maximumSelectionLength: 1,
        dropdownCssClass: 'select2-dropdown-scrollable'
    });
    
    $('#classFilter').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Select Class',
        allowClear: true,
        maximumSelectionSize: 1,
        dropdownAutoWidth: true,
        minimumResultsForSearch: Infinity // Disable search for class
    });

    // Initialize model dropdown
    var $modelSelect = $('#modelFilter');
    $modelSelect.empty().append('<option value="">All Models</option>');
    
    // Pastikan nilai default adalah kosong (All Models)
    $modelSelect.val('');
    
    // Add some sample models directly to ensure dropdown is always populated
    var sampleModels = ['MODEL-A', 'MODEL-B', 'MODEL-C', 'MODEL-D', 'MODEL-E'];
    $.each(sampleModels, function(i, model) {
        $modelSelect.append('<option value="' + model + '">' + model + '</option>');
    });
    
    // Initialize Select2 immediately
    $modelSelect.select2({
        placeholder: 'Select Model',
        allowClear: true,
        width: '100%'
    });
    
    // Pastikan nilai default adalah kosong setelah inisialisasi Select2
    $modelSelect.val('').trigger('change');
    
    // Try to get real models from API
    console.log('Fetching models from API...');
    $.ajax({
        url: base_url + '/admin/report/delivery-shortage/models',
        type: 'GET',
        dataType: 'json',
        contentType: 'application/json',
        success: function(response) {
            console.log('Models response received:', response);
            
            // Check if we have valid data
            if (response && response.models && Array.isArray(response.models) && response.models.length > 0) {
                console.log('Valid models data received, count:', response.models.length);
                
                // Clear previous options but keep the 'All Models' option
                $modelSelect.empty().append('<option value="">All Models</option>');
                
                // Add each model from the response
                $.each(response.models, function(i, item) {
                    if (item && item.model_no) {
                        $modelSelect.append('<option value="' + item.model_no + '">' + item.model_no + '</option>');
                    }
                });
                
                console.log('Added ' + response.models.length + ' models from API');
            } else {
                console.log('API returned no valid models data, keeping sample data');
            }
            
            // Destroy and reinitialize Select2 to refresh options
            $modelSelect.select2('destroy').select2({
                placeholder: 'Select Model',
                allowClear: true,
                width: '100%'
            });
            
            // Pastikan nilai default adalah kosong setelah mendapatkan data dari API
            $modelSelect.val('').trigger('change');
            console.log('Reset model filter to empty after API load');
        },
        error: function(xhr, status, error) {
            console.error('Error loading models:', error);
            console.log('Response text:', xhr.responseText);
            console.log('Status:', status);
            console.log('API error, keeping sample data');
        }
    });

    // Hardcode class options to 99 and 1
    var $classSelect = $('#classFilter');
    $classSelect.empty().append('<option value="">All Classes</option>');
    $classSelect.append('<option value="99">99</option>');
    $classSelect.append('<option value="1">1</option>');
    $classSelect.trigger('change');

    // Toggle filter section dengan animasi yang lebih smooth
    $('#toggleFilter').on('click', function() {
        $('#filterSection').slideToggle(300); // Hapus easing yang tidak tersedia
        const icon = $(this).find('i');
        if (icon.hasClass('fa-filter')) {
            icon.removeClass('fa-filter').addClass('fa-times');
            $(this).removeClass('btn-outline-primary').addClass('btn-outline-danger');
        } else {
            icon.removeClass('fa-times').addClass('fa-filter');
            $(this).removeClass('btn-outline-danger').addClass('btn-outline-primary');
        }
    });

    // Apply filter button click
    $('#applyFilter').on('click', function() {
        loadReportData();
    });

    // Export to Excel button click
    $('#exportExcel').on('click', function() {
        exportToExcel();
    });

    // Load report data on page load
    loadReportData();

    // Function to load report data
    function loadReportData() {
        // Pastikan dateRange sudah diinisialisasi
        let startDate, endDate;
        if ($('#dateRange').val()) {
            const dateRange = $('#dateRange').val().split(' - ');
            startDate = dateRange[0];
            endDate = dateRange.length > 1 ? dateRange[1] : dateRange[0];
        } else {
            // Default tanggal jika belum diinisialisasi
            startDate = moment().startOf('month').format('YYYY-MM-DD');
            endDate = moment().endOf('month').format('YYYY-MM-DD');
            $('#dateRange').val(startDate + ' - ' + endDate);
        }
        
        const classFilter = $('#classFilter').val() || '';
        // Pastikan nilai model_no tidak undefined atau null
        const modelFilter = $('#modelFilter').val() || '';
        const minusOnly = $('#minusOnly').is(':checked');

        // Debug: Periksa nilai model_no sebelum dikirim
        console.log("Model filter value:", modelFilter);
        console.log("Model filter type:", typeof modelFilter);
        console.log("Model select element value:", $('#modelFilter').val());
        
        // Log parameter yang dikirim untuk debugging
        console.log("Sending parameters:", {
            start_date: startDate,
            end_date: endDate,
            class: classFilter,
            model_no: modelFilter,
            minus_only: minusOnly
        });

        // Show loading indicator
        $('#reportTableContainer').hide();
        $('#noDataMessage').hide();
        $('#loadingIndicator').show();

        // Make AJAX request to get report data
        // Pastikan model_no tidak kosong jika dipilih
        const requestData = {
            start_date: startDate,
            end_date: endDate,
            class: classFilter,
            minus_only: minusOnly
        };
        
        // Hanya tambahkan model_no jika benar-benar ada nilai
        if (modelFilter && modelFilter.trim() !== '') {
            requestData.model_no = modelFilter.trim();
            console.log("Adding model_no to request:", requestData.model_no);
        } else {
            console.log("No model_no selected, sending empty value");
            requestData.model_no = '';
        }
        
        $.ajax({
            url: base_url + '/admin/report/delivery-shortage/data',
            type: 'POST',
            data: requestData,
            dataType: 'json',
            success: function(response) {
                // Hide loading indicator
                $('#loadingIndicator').hide();
                
                console.log("Response received:", response);
                console.log("Response structure:", JSON.stringify(response));

                if (response.data && response.data.length > 0) {
                    console.log("Data found, rendering table with", response.data.length, "items");
                    console.log("Dates array:", response.dates);
                    
                    // Get start and end day from dates array
                    const startDay = response.dates.length > 0 ? Math.min(...response.dates) : 1;
                    const endDay = response.dates.length > 0 ? Math.max(...response.dates) : 31;
                    console.log("Calculated start day:", startDay, "end day:", endDay);
                    
                    // Render report table
                    renderReportTable(response.data, startDay, endDay);
                    $('#reportTableContainer').show();
                } else {
                    console.log("No data found in response");
                    // Show no data message
                    $('#noDataMessage').show();
                }
            },
            error: function(xhr, status, error) {
                // Hide loading indicator
                $('#loadingIndicator').hide();
                
                // Show error message
                toastr.error('Failed to load report data: ' + error);
            }
        });
    }

    // Function to render report table
    function renderReportTable(data, startDay, endDay) {
        console.log("Rendering report table with data:", data);
        console.log("Start day:", startDay, "End day:", endDay);
        
        // Clear existing table content
        const headerRow = $('#headerRow');
        headerRow.empty();
        $('#reportBody').empty();

        // Add fixed headers
        headerRow.append('<th class="model-cell">Model No</th>');
        headerRow.append('<th class="class-cell">Class</th>');
        headerRow.append('<th class="label-cell">Label</th>');
        headerRow.append('<th class="stock-cell">Begin Stock</th>');

        // Add date columns to header
        for (let day = parseInt(startDay); day <= parseInt(endDay); day++) {
            headerRow.append(`<th class="date-header">${day}</th>`);
        }

        // Add data rows
        data.forEach(function(model) {
            const modelNo = model.model_no;
            const classValue = model.class;
            const beginStock = model.begin_stock;
            const dlvPlan = model.dlv_plan || [];
            const dlvAct = model.dlv_act || [];
            const prdPlan = model.prd_plan || [];
            const prdAct = model.prd_act || [];
            
            console.log(`Processing model: ${modelNo}, class: ${classValue}`);
            console.log("dlv_plan:", dlvPlan);
            console.log("dlv_act:", dlvAct);
            console.log("prd_plan:", prdPlan);
            console.log("prd_act:", prdAct);

            // Row labels
            const rowLabels = ['Dlv Plan', 'Dlv Act', 'Prd Plan', 'Prd Act', 'Stock Plan', 'Stock Act'];
            
            // Calculate stock plan and stock act arrays
            let stockPlan = [];
            let stockAct = [];
            let currentStockPlan = parseInt(beginStock);
            let currentStockAct = parseInt(beginStock);
            
            for (let i = 0; i < 31; i++) {
                const dlvPlanVal = parseInt(dlvPlan[i] || 0);
                const dlvActVal = parseInt(dlvAct[i] || 0);
                const prdPlanVal = parseInt(prdPlan[i] || 0);
                const prdActVal = parseInt(prdAct[i] || 0);
                
                currentStockPlan = currentStockPlan - dlvPlanVal + prdPlanVal;
                currentStockAct = currentStockAct - dlvActVal + prdActVal;
                
                stockPlan.push(currentStockPlan);
                stockAct.push(currentStockAct);
            }
            
            console.log("stockPlan:", stockPlan);
            console.log("stockAct:", stockAct);
            
            // Create rows for each label
            rowLabels.forEach(function(label, index) {
                const row = $('<tr></tr>');
                
                // First row for this model/class combination
                if (index === 0) {
                    row.append(`<td class="model-cell" rowspan="6">${modelNo}</td>`);
                    row.append(`<td class="class-cell" rowspan="6">${classValue}</td>`);
                }
                
                // Add label cell
                row.append(`<td class="label-cell">${label}</td>`);
                
                // Add begin_stock cell (only for Stock Plan and Stock Act rows)
                if (index === 4 || index === 5) {
                    const stockClass = parseInt(beginStock) < 0 ? 'negative-value' : '';
                    row.append(`<td class="stock-cell ${stockClass}">${beginStock}</td>`);
                } else {
                    row.append('<td class="stock-cell"></td>');
                }
                
                // Add day cells
                for (let day = parseInt(startDay); day <= parseInt(endDay); day++) {
                    const dayIndex = day - 1; // Convert to 0-based index
                    let value = 0;
                    let cellClass = '';
                    
                    // Get appropriate value based on row index
                    switch (index) {
                        case 0: value = dlvPlan[dayIndex] || 0; break;
                        case 1: value = dlvAct[dayIndex] || 0; break;
                        case 2: value = prdPlan[dayIndex] || 0; break;
                        case 3: value = prdAct[dayIndex] || 0; break;
                        case 4: 
                            value = stockPlan[dayIndex] || 0; 
                            if (value < 0) cellClass = 'negative-value';
                            break;
                        case 5: 
                            value = stockAct[dayIndex] || 0; 
                            if (value < 0) cellClass = 'negative-value';
                            break;
                    }
                    
                    row.append(`<td class="${cellClass}">${value}</td>`);
                }
                
                $('#reportBody').append(row);
            });
        });
    }

    // Function to export to Excel
    function exportToExcel() {
        const dateRange = $('#dateRange').val().split(' - ');
        const startDate = dateRange[0];
        const endDate = dateRange.length > 1 ? dateRange[1] : dateRange[0];
        const classFilter = $('#classFilter').val() || '';
        const modelFilter = $('#modelFilter').val() || '';
        const minusOnly = $('#minusOnly').is(':checked');

        // Debug: Periksa nilai model_no sebelum dikirim
        console.log("[EXCEL] Model filter value:", modelFilter);
        console.log("[EXCEL] Model filter type:", typeof modelFilter);

        // Build URL with query parameters
        let url = base_url + '/admin/report/delivery-shortage/export';
        url += '?start_date=' + encodeURIComponent(startDate);
        url += '&end_date=' + encodeURIComponent(endDate);
        
        // Selalu kirim parameter class, bahkan jika kosong
        url += '&class=' + encodeURIComponent(classFilter);
        
        // Selalu kirim parameter model_no, bahkan jika kosong
        // Pastikan model_no yang dikirim sudah di-trim
        const trimmedModelFilter = modelFilter.trim();
        url += '&model_no=' + encodeURIComponent(trimmedModelFilter);
        console.log("[EXCEL] Adding model_no to URL:", trimmedModelFilter);
        
        if (minusOnly) {
            url += '&minus_only=true';
        }
        
        console.log("[EXCEL] Export URL:", url);

        // Open URL in new window to trigger download
        window.open(url, '_blank');
    }
});
</script>
<?= $this->endSection() ?>
