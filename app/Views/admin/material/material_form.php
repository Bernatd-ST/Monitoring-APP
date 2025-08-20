<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="m-0 font-weight-bold"><?= empty($material['id']) ? 'Add' : 'Edit' ?> Material Control</h5>
        </div>
        <div class="card-body">
            <form action="<?= empty($material['id']) ? base_url('admin/material/save-material') : base_url('admin/material/update-material/' . $material['id']) ?>" method="post" id="<?= empty($material['id']) ? 'addMaterialForm' : 'editMaterialForm' ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $material['id'] ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="ckd" class="form-label">CKD <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ckd" name="ckd" value="<?= $material['ckd'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="period" class="form-label">Period <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="period" name="period" value="<?= $material['period'] ?>" required>
                        <small class="text-muted">Format: YYYY-MM-DD</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" value="<?= $material['description'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="part_no" class="form-label">Part No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="part_no" name="part_no" value="<?= $material['part_no'] ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="class" class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-control" id="class" name="class" required>
                            <option value="">Select Class</option>
                            <?php for ($i = 1; $i <= 10; $i++) : ?>
                                <option value="<?= $i ?>" <?= ($material['class'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="beginning" class="form-label">Beginning</label>
                        <input type="text" class="form-control" id="beginning" name="beginning" value="<?= $material['beginning'] ?>">
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= base_url('admin/material/material-control') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-<?= empty($material['id']) ? 'success' : 'primary' ?>">
                        <i class="fas fa-save"></i> <?= empty($material['id']) ? 'Save' : 'Update' ?>
                    </button>
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
