<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Material Control</h1>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(to right, #4e73df, #224abe);">
                    <h6 class="m-0 font-weight-bold text-white">Bill of Material (BOM)</h6>
                </div>
                <div class="card-body">
                    <p>Manage Bill of Material data including import and export functionality.</p>
                    <a href="/admin/material/bom" class="btn btn-primary btn-block">
                        <i class="fas fa-file-alt fa-fw"></i> Go to BOM
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(to right, #4e73df, #224abe);">
                    <h6 class="m-0 font-weight-bold text-white">Material Control</h6>
                </div>
                <div class="card-body">
                    <p>Manage Material Control data including import and export functionality.</p>
                    <a href="/admin/material/material-control" class="btn btn-primary btn-block">
                        <i class="fas fa-boxes fa-fw"></i> Go to Material Control
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(to right, #4e73df, #224abe);">
                    <h6 class="m-0 font-weight-bold text-white">Shipment Schedule</h6>
                </div>
                <div class="card-body">
                    <p>Manage Shipment Schedule data including import and export functionality.</p>
                    <a href="/admin/material/shipment-schedule" class="btn btn-primary btn-block">
                        <i class="fas fa-shipping-fast fa-fw"></i> Go to Shipment Schedule
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
