<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="m-0 font-weight-bold">Add Material Control</h5>
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/material/save-material') ?>" method="post" id="addMaterialForm">
                <?= csrf_field() ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="ckd" class="form-label">CKD <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ckd" name="ckd" required>
                    </div>
                    <div class="col-md-6">
                        <label for="period" class="form-label">Period <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="period" name="period" required>
                        <small class="text-muted">Format: YYYY-MM-DD</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description">
                    </div>
                    <div class="col-md-6">
                        <label for="part_no" class="form-label">Part No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="part_no" name="part_no" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="class" class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-control" id="class" name="class" required>
                            <option value="">Select Class</option>
                            <?php for ($i = 1; $i <= 10; $i++) : ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="beginning" class="form-label">Beginning</label>
                        <input type="text" class="form-control" id="beginning" name="beginning">
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= base_url('admin/material/material-control') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
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
    // Form validation and submission
    $('#addMaterialForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = $(this).serialize();
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Show success message
                    toastr.success(response.message);
                    
                    // Redirect to material control page after 1 second
                    setTimeout(function() {
                        window.location.href = '<?= base_url('admin/material/material-control') ?>';
                    }, 1000);
                } else {
                    // Show error message
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                toastr.error('An error occurred while saving material data');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
