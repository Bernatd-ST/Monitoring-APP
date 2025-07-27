<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title); ?> - Monitoring App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    
</head>
<body>

<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">Monitoring App</a>
    <div class="navbar-nav">
        <div class="nav-item text-nowrap">
            <a class="nav-link px-3" href="/logout">Sign out</a>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= (uri_string() == 'admin/dashboard') ? 'active' : '' ?>" href="/admin/dashboard">
                        <i class="fas fa-tachometer-alt fa-fw"></i>
                        Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'admin/sales') ? 'active' : '' ?>" href="/admin/sales">
                            <i class="fas fa-file-invoice-dollar fa-fw"></i>
                            Sales
                    </li>
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
                                <li class="nav-item">
                                    <a class="nav-link <?= (uri_string() == 'admin/ppic/semifinishgood') ? 'active' : '' ?>" href="/admin/ppic/semifinishgood">
                                        <i class="fas fa-box-open fa-fw"></i>
                                        Semi Finish Good
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-box-open fa-fw"></i>
                            Material Control
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-file-alt fa-fw"></i>
                            Report
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
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

<!-- Custom scripts section -->
<?= $this->renderSection('scripts') ?>
</body>
</html>