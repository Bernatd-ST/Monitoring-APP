<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($title); ?> - Monitoring App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- DateRangePicker CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="/css/Dashboard/dashboard.css">
    <link rel="stylesheet" href="/css/ppic/style.css">
    <link rel="stylesheet" href="/css/sidebar-custom.css">
    <link rel="shortcut icon" type="image/png" href="/image/icon.ico">
    
</head>
<body>

<header class="navbar navbar-light sticky-top bg-white flex-md-nowrap p-0 shadow-sm border-bottom header">
    <div class="container-fluid px-0">
        <p class="ms-3" style="margin-top: 0; margin-bottom: 0; padding-bottom: 10px; font-weight: bold; font-size: 1.2rem;">Monitoring App</p>
        <div class="navbar-nav ms-auto">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3 text-dark" href="/logout"><i class="fas fa-sign-out-alt me-1"></i> Sign out</a>
            </div>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar">
    <div class="position-sticky pt-0 mt-0">
        <!-- Admin info section - positioned right below Monitoring App -->
        <div class="admin-info mb-3 px-3 py-2" style="margin-top: 0;">
            <div class="d-flex align-items-center">
                <div class="admin-avatar me-2">
                    <i class="fas fa-user-circle fa-2x"></i>
                </div>
                <div class="welcome-text">
                    <div class="small">Welcome</div>
                    <div class="fw-bold"><?= session()->get('user_role') === 'admin' ? 'Admin' : 'User' ?></div>
                </div>
            </div>
        </div>    
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= (uri_string() == 'admin/dashboard') ? 'active' : '' ?>" href="/admin/dashboard">
                        <i class="fas fa-tachometer-alt fa-fw"></i>
                        Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos(uri_string(), 'admin/sales/') === 0) ? 'active' : '' ?>" data-bs-toggle="collapse" href="#salesSubmenu" role="button" aria-expanded="<?= (strpos(uri_string(), 'admin/sales/') === 0) ? 'true' : 'false' ?>" aria-controls="salesSubmenu">
                            <i class="fas fa-industry fa-fw"></i>
                            SALES <i class="fas fa-chevron-down ms-1"></i>
                        </a>
                        <div class="collapse <?= (strpos(uri_string(), 'admin/sales/') === 0) ? 'show' : '' ?>" id="salesSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/sales/sales') ? 'active' : '' ?>" href="/admin/sales/sales">
                                        <i class="fas fa-file-invoice-dollar fa-fw"></i>
                                        Planning
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/sales/actual') ? 'active' : '' ?>" href="/admin/sales/actual">
                                        <i class="fas fa-file-invoice-dollar fa-fw"></i>
                                        Actual
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <?php if(session()->get('user_role') === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos(uri_string(), 'admin/ppic') === 0) ? 'active' : '' ?>" data-bs-toggle="collapse" href="#ppicSubmenu" role="button" aria-expanded="<?= (strpos(uri_string(), 'admin/ppic') === 0) ? 'true' : 'false' ?>" aria-controls="ppicSubmenu">
                            <i class="fas fa-industry fa-fw"></i>
                            PPIC <i class="fas fa-chevron-down ms-1"></i>
                        </a>
                        <div class="collapse <?= (strpos(uri_string(), 'admin/ppic') === 0) ? 'show' : '' ?>" id="ppicSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/ppic/planning') ? 'active' : '' ?>" href="/admin/ppic/planning">
                                        <i class="fas fa-calendar-alt fa-fw"></i>
                                        Planning
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/ppic/actual') ? 'active' : '' ?>" href="/admin/ppic/actual">
                                        <i class="fas fa-clipboard-list fa-fw"></i>
                                        Actual
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/ppic/finishgood') ? 'active' : '' ?>" href="/admin/ppic/finishgood">
                                        <i class="fas fa-box fa-fw"></i>
                                        Finish Good
                                    </a>
                                </li>
                                <!-- <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/ppic/semifinishgood') ? 'active' : '' ?>" href="/admin/ppic/semifinishgood">
                                        <i class="fas fa-box-open fa-fw"></i>
                                        Semi Finish Good
                                    </a>
                                </li> -->
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                    <?php if(session()->get('user_role') === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos(uri_string(), 'admin/material') === 0) ? 'active' : '' ?>" data-bs-toggle="collapse" href="#materialSubmenu" role="button" aria-expanded="<?= (strpos(uri_string(), 'admin/material') === 0) ? 'true' : 'false' ?>" aria-controls="materialSubmenu">
                            <i class="fas fa-box-open fa-fw"></i>
                            Material Control <i class="fas fa-chevron-down ms-1"></i>
                        </a>
                        <div class="collapse <?= (strpos(uri_string(), 'admin/material') === 0) ? 'show' : '' ?>" id="materialSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/material/bom') ? 'active' : '' ?>" href="/admin/material/bom">
                                        <i class="fas fa-file-alt fa-fw"></i>
                                        BOM
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/material/material-control') ? 'active' : '' ?>" href="/admin/material/material-control">
                                        <i class="fas fa-boxes fa-fw"></i>
                                        Stock Material
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/material/shipment-schedule') ? 'active' : '' ?>" href="/admin/material/shipment-schedule">
                                        <i class="fas fa-shipping-fast fa-fw"></i>
                                        Shipment Schedule
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos(uri_string(), 'admin/report') === 0 || strpos(uri_string(), 'material-shortage') === 0) ? 'active' : '' ?>" 
                        data-bs-toggle="collapse" href="#reportSubmenu" role="button" aria-expanded="<?= (strpos(uri_string(), 'admin/report') === 0 || strpos(uri_string(), 'material-shortage') === 0) ? 'true' : 'false' ?>" aria-controls="reportSubmenu">
                            <i class="fas fa-file-alt fa-fw"></i>
                            Report <i class="fas fa-chevron-down ms-1"></i>
                        </a>
                        <div class="collapse <?= (strpos(uri_string(), 'admin/report') === 0 || strpos(uri_string(), 'material-shortage') === 0) ? 'show' : '' ?>" id="reportSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/report/delivery-shortage') ? 'active' : '' ?>" href="/admin/report/delivery-shortage">
                                        <i class="fas fa-chart-line fa-fw"></i>
                                        Delivery Shortage
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'material-shortage' || uri_string() == 'admin/report/material-shortage') ? 'active' : '' ?>" href="/material-shortage">
                                        <i class="fas fa-boxes fa-fw"></i>
                                        Material Shortage
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content-wrapper">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= esc($title ?? 'Page'); ?></h1>
                
                <!-- Placeholder untuk tombol-tombol di header halaman -->
                <?= $this->renderSection('page_buttons') ?>

            </div>

            <!-- Di sinilah konten spesifik halaman akan dimasukkan -->
            <?= $this->renderSection('content') ?>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Definisi base_url untuk JavaScript -->
<script>
    var base_url = '<?= base_url() ?>';
    console.log('Base URL:', base_url);
</script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- DataTables FixedColumns -->
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<!-- Moment.js & DateRangePicker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Toastr Notifications -->
<script>
$(document).ready(function() {
    // Konfigurasi Toastr
    toastr.options = {
        closeButton: true,
        newestOnTop: true,
        progressBar: true,
        positionClass: "toast-top-right",
        preventDuplicates: false,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "5000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    };
    
    // Menampilkan notifikasi dari flashdata
    <?php if (session()->getFlashdata('success')) : ?>
        toastr.success('<?= session()->getFlashdata('success') ?>');
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')) : ?>
        toastr.error('<?= session()->getFlashdata('error') ?>');
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('warning')) : ?>
        toastr.warning('<?= session()->getFlashdata('warning') ?>');
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('info')) : ?>
        toastr.info('<?= session()->getFlashdata('info') ?>');
    <?php endif; ?>
});
</script>

<!-- Custom scripts section -->
<?= $this->renderSection('scripts') ?>
<!-- Custom Sidebar Script -->
<script>
$(document).ready(function() {
    // Add data-title attributes for tooltips
    $('.nav-item').each(function() {
        var linkText = $(this).find('> .nav-link').text().trim();
        $(this).attr('data-title', linkText);
    });
    
    // Ensure submenu opens on parent click in mobile view
    $('.nav-link[data-bs-toggle="collapse"]').on('click', function(e) {
        if (window.innerWidth < 768) {
            e.preventDefault();
            $($(this).data('bs-target')).collapse('toggle');
        }
    });
    
    // Add span around text in nav links for better control
    $('.nav-link').each(function() {
        var $link = $(this);
        var html = $link.html();
        var iconHtml = '';
        
        // Check if there's already an icon
        if (html.indexOf('<i class="fas') !== -1) {
            // Extract the icon part
            var iconMatch = html.match(/<i class="fas[^>]*><\/i>/g);
            if (iconMatch) {
                iconHtml = iconMatch[0];
                html = html.replace(iconHtml, '');
            }
        }
        
        // Check for chevron icon separately
        var chevronHtml = '';
        if (html.indexOf('<i class="fas fa-chevron-down') !== -1) {
            var chevronMatch = html.match(/<i class="fas fa-chevron-down[^>]*><\/i>/g);
            if (chevronMatch) {
                chevronHtml = chevronMatch[0];
                html = html.replace(chevronHtml, '');
            }
        }
        
        // Wrap the remaining text in a span
        html = html.trim();
        $link.html(iconHtml + ' <span>' + html + '</span> ' + chevronHtml);
    });
});
</script>
</body>
</html>