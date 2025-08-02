<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>
    
    <!-- Notification Messages -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(to right, #4e73df, #224abe);">
            <h6 class="m-0 font-weight-bold text-white"><?= isset($bom) ? 'Edit' : 'Add' ?> Bill of Material</h6>
        </div>
        <div class="card-body">
            <form action="<?= isset($bom) ? '/admin/material/update-bom/'.$bom['id'] : '/admin/material/save-bom' ?>" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="model_no">Model No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="model_no" name="model_no" value="<?= isset($bom) ? $bom['model_no'] : old('model_no') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="h_class">H Class</label>
                            <input type="text" class="form-control" id="h_class" name="h_class" value="<?= isset($bom) ? $bom['h_class'] : old('h_class') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="qty_assy">Qty Assy</label>
                            <input type="number" step="0.01" class="form-control" id="qty_assy" name="qty_assy" value="<?= isset($bom) ? $bom['qty_assy'] : old('qty_assy', '0.00') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="part_no">Part No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="part_no" name="part_no" value="<?= isset($bom) ? $bom['part_no'] : old('part_no') ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= isset($bom) ? $bom['description'] : old('description') ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="prd_code">PRD Code</label>
                            <input type="text" class="form-control" id="prd_code" name="prd_code" value="<?= isset($bom) ? $bom['prd_code'] : old('prd_code') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="class">Class</label>
                            <input type="text" class="form-control" id="class" name="class" value="<?= isset($bom) ? $bom['class'] : old('class') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="upd_date">Update Date</label>
                            <input type="date" class="form-control" id="upd_date" name="upd_date" value="<?= isset($bom) ? $bom['upd_date'] : old('upd_date', date('Y-m-d')) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= isset($bom) ? 'Update' : 'Save' ?>
                    </button>
                    <a href="/admin/material/bom" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Format date input for better compatibility
    $('#upd_date').on('change', function() {
        console.log('Date changed:', $(this).val());
    });
});
</script>
<?= $this->endSection() ?>
