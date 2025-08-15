<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<!-- Dashboard Analytics Content -->
<div class="analytics-dashboard">
    <!-- Filters Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-filter"></i> Dashboard Filters
            </h5>
            <button id="toggleFilters" class="btn btn-sm btn-light">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>
        <div class="card-body" id="filtersContainer">
            <form id="dashboardFilters" class="row g-3">
                <div class="col-md-4">
                    <label for="dateRange" class="form-label">Date Range</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="text" class="form-control" id="dateRange" name="dateRange">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="modelNo" class="form-label">Model No</label>
                    <select class="form-select select2" id="modelNo" name="modelNo">
                        <option value="">All Models</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="class" class="form-label">Class</label>
                    <select class="form-select select2" id="class" name="class">
                        <option value="">All Classes</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row mb-4">
        <!-- Sales Achievement Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Sales Achievement</div>
                            <div class="h3 mb-0 font-weight-bold" id="salesAchievement">0%</div>
                            <div class="mt-2 text-muted small">
                                <span id="salesActualTotal">0</span> / <span id="salesPlanTotal">0</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div id="salesProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Production Efficiency Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Production Efficiency</div>
                            <div class="h3 mb-0 font-weight-bold" id="productionEfficiency">0%</div>
                            <div class="mt-2 text-muted small">
                                <span id="productionActualTotal">0</span> / <span id="productionPlanTotal">0</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-industry fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div id="productionProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Average Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Daily Sales</div>
                            <div class="h3 mb-0 font-weight-bold" id="avgDailySales">0</div>
                            <div class="mt-2 text-muted small">
                                Units per day
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trend Indicator Card 
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Sales Trend</div>
                            <div class="h3 mb-0 font-weight-bold" id="salesTrend">
                                <i class="fas fa-minus"></i> Stable
                            </div>
                            <div class="mt-2 text-muted small" id="trendPercentage">
                                0% change
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
    
    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Sales Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Sales Plan vs Actual</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Production Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0"><i class="fas fa-industry"></i> Production Plan vs Actual</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="productionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Daily Performance Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="mb-0"><i class="fas fa-table"></i> Daily Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="performanceTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Sales Plan</th>
                                    <th>Sales Actual</th>
                                    <th>Achievement</th>
                                    <th>Production Plan</th>
                                    <th>Production Actual</th>
                                    <th>Efficiency</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for Dashboard -->
<style>
    .analytics-dashboard .card {
        transition: all 0.3s ease;
    }
    
    .analytics-dashboard .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .analytics-dashboard .card-header {
        font-weight: bold;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(45deg, #4e73df, #224abe);
    }
    
    .bg-gradient-success {
        background: linear-gradient(45deg, #1cc88a, #13855c);
    }
    
    .bg-gradient-info {
        background: linear-gradient(45deg, #36b9cc, #258391);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(45deg, #f6c23e, #dda20a);
    }
    
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    
    .text-xs {
        font-size: 0.7rem;
    }
    
    .chart-container {
        position: relative;
        margin: auto;
    }
    
    #performanceTable td.positive {
        color: #1cc88a;
        font-weight: bold;
    }
    
    #performanceTable td.negative {
        color: #e74a3b;
        font-weight: bold;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5'
    });
    
    // Initialize DateRangePicker
    $('#dateRange').daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        locale: {
            format: 'YYYY-MM-DD'
        }
    });
    
    // Toggle filters
    $('#toggleFilters').on('click', function() {
        $('#filtersContainer').slideToggle();
        $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
    });
    
    // Load model and class options
    loadModelClassOptions();
    
    // Load dashboard data on page load
    loadDashboardData();
    
    // Handle filter form submission
    $('#dashboardFilters').on('submit', function(e) {
        e.preventDefault();
        loadDashboardData();
    });
});

// Load model and class options for filters
function loadModelClassOptions() {
    $.ajax({
        url: '<?= base_url('admin/dashboard-analytics/model-class-options') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Populate model dropdown
                let modelOptions = new Set();
                let classOptions = new Set();
                
                response.data.forEach(function(item) {
                    modelOptions.add(item.model_no);
                    classOptions.add(item.class);
                });
                
                // Add model options
                modelOptions.forEach(function(model) {
                    $('#modelNo').append(`<option value="${model}">${model}</option>`);
                });
                
                // Add class options
                classOptions.forEach(function(cls) {
                    $('#class').append(`<option value="${cls}">${cls}</option>`);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading model/class options:', error);
        }
    });
}

// Load dashboard data
function loadDashboardData() {
    // Get filter values
    const dateRange = $('#dateRange').val().split(' - ');
    const startDate = dateRange[0];
    const endDate = dateRange[1];
    const modelNo = $('#modelNo').val();
    const classVal = $('#class').val();
    
    // Show loading indicators
    showLoading();
    
    $.ajax({
        url: '<?= base_url('admin/dashboard-analytics/dashboard-data') ?>',
        type: 'GET',
        data: {
            start_date: startDate,
            end_date: endDate,
            model_no: modelNo,
            class: classVal
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Update dashboard with data
                updateDashboard(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading dashboard data:', error);
        },
        complete: function() {
            // Hide loading indicators
            hideLoading();
        }
    });
}

// Show loading indicators
function showLoading() {
    // You can implement loading indicators here
}

// Hide loading indicators
function hideLoading() {
    // You can implement hiding loading indicators here
}

// Update dashboard with data
function updateDashboard(data) {
    // Update KPI cards
    updateKPICards(data);
    
    // Update charts
    updateCharts(data);
    
    // Update performance table
    updatePerformanceTable(data);
}

// Update KPI cards
function updateKPICards(data) {
    // Sales achievement
    const salesAchievement = data.summary.sales.achievement;
    $('#salesAchievement').text(salesAchievement + '%');
    $('#salesActualTotal').text(data.summary.sales.totalActual);
    $('#salesPlanTotal').text(data.summary.sales.totalPlan);
    $('#salesProgressBar').css('width', Math.min(100, salesAchievement) + '%');
    
    // Production efficiency
    const productionEfficiency = data.summary.production.efficiency;
    $('#productionEfficiency').text(productionEfficiency + '%');
    $('#productionActualTotal').text(data.summary.production.totalActual);
    $('#productionPlanTotal').text(data.summary.production.totalPlan);
    $('#productionProgressBar').css('width', Math.min(100, productionEfficiency) + '%');
    
    // Average daily sales
    const totalDays = data.labels.length;
    const avgDailySales = totalDays > 0 ? Math.round(data.summary.sales.totalActual / totalDays) : 0;
    $('#avgDailySales').text(avgDailySales);
    
    // Sales trend
    updateSalesTrend(data);
}

// Update sales trend indicator
function updateSalesTrend(data) {
    // Calculate trend based on last few days
    const salesActual = data.salesActual;
    if (salesActual.length < 2) {
        $('#salesTrend').html('<i class="fas fa-minus"></i> Stable');
        $('#trendPercentage').text('0% change');
        return;
    }
    
    // Compare first half with second half
    const midPoint = Math.floor(salesActual.length / 2);
    let firstHalfSum = 0;
    let secondHalfSum = 0;
    
    for (let i = 0; i < midPoint; i++) {
        firstHalfSum += salesActual[i];
    }
    
    for (let i = midPoint; i < salesActual.length; i++) {
        secondHalfSum += salesActual[i];
    }
    
    const firstHalfAvg = midPoint > 0 ? firstHalfSum / midPoint : 0;
    const secondHalfAvg = (salesActual.length - midPoint) > 0 ? secondHalfSum / (salesActual.length - midPoint) : 0;
    
    let trendPercentage = 0;
    if (firstHalfAvg > 0) {
        trendPercentage = Math.round(((secondHalfAvg - firstHalfAvg) / firstHalfAvg) * 100);
    }
    
    let trendIcon, trendText, trendClass;
    if (trendPercentage > 5) {
        trendIcon = 'fa-arrow-up';
        trendText = 'Increasing';
        trendClass = 'text-success';
    } else if (trendPercentage < -5) {
        trendIcon = 'fa-arrow-down';
        trendText = 'Decreasing';
        trendClass = 'text-danger';
    } else {
        trendIcon = 'fa-minus';
        trendText = 'Stable';
        trendClass = 'text-warning';
    }
    
    $('#salesTrend').html(`<i class="fas ${trendIcon} ${trendClass}"></i> ${trendText}`);
    $('#trendPercentage').text(trendPercentage + '% change');
}

// Update charts
function updateCharts(data) {
    // Sales chart
    updateSalesChart(data);
    
    // Production chart
    updateProductionChart(data);
}

// Sales chart
let salesChart;
function updateSalesChart(data) {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (salesChart) {
        salesChart.destroy();
    }
    
    // Create new chart
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Sales Plan',
                    data: data.salesPlan,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#4e73df',
                    pointRadius: 3,
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Sales Actual',
                    data: data.salesActual,
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#1cc88a',
                    pointRadius: 3,
                    fill: true,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Units'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Day'
                    }
                }
            }
        }
    });
}

// Production chart
let productionChart;
function updateProductionChart(data) {
    const ctx = document.getElementById('productionChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (productionChart) {
        productionChart.destroy();
    }
    
    // Create new chart
    productionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Production Plan',
                    data: data.productionPlan,
                    borderColor: '#e74a3b',
                    backgroundColor: 'rgba(231, 74, 59, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#e74a3b',
                    pointRadius: 3,
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Production Actual',
                    data: data.productionActual,
                    borderColor: '#f6c23e',
                    backgroundColor: 'rgba(246, 194, 62, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#f6c23e',
                    pointRadius: 3,
                    fill: true,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Units'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Day'
                    }
                }
            }
        }
    });
}

// Update performance table
function updatePerformanceTable(data) {
    const tableBody = $('#performanceTable tbody');
    tableBody.empty();
    
    // Create table rows
    for (let i = 0; i < data.labels.length; i++) {
        const day = data.labels[i];
        const salesPlan = data.salesPlan[i];
        const salesActual = data.salesActual[i];
        const salesAchievement = salesPlan > 0 ? Math.round((salesActual / salesPlan) * 100) : 0;
        
        const productionPlan = data.productionPlan[i];
        const productionActual = data.productionActual[i];
        const productionEfficiency = productionPlan > 0 ? Math.round((productionActual / productionPlan) * 100) : 0;
        
        const salesAchievementClass = salesAchievement >= 100 ? 'positive' : (salesAchievement < 80 ? 'negative' : '');
        const productionEfficiencyClass = productionEfficiency >= 100 ? 'positive' : (productionEfficiency < 80 ? 'negative' : '');
        
        tableBody.append(`
            <tr>
                <td>${day}</td>
                <td>${salesPlan}</td>
                <td>${salesActual}</td>
                <td class="${salesAchievementClass}">${salesAchievement}%</td>
                <td>${productionPlan}</td>
                <td>${productionActual}</td>
                <td class="${productionEfficiencyClass}">${productionEfficiency}%</td>
            </tr>
        `);
    }
}
</script>
<?= $this->endSection() ?>
