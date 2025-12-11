<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Upload Course Material</h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= session()->getFlashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success" role="alert">
                            <?= session()->getFlashdata('success'); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= isset($action) ? $action : ''; ?>" method="post" enctype="multipart/form-data" id="material-upload-form">
                        <?= csrf_field(); ?>

                        <div class="mb-3">
                            <label for="material_file" class="form-label">Select file</label>
                            <input type="file" name="material_file" id="material_file" class="form-control" accept=".pdf,.ppt" required>
                            <div class="form-text">Allowed: PDF, PPT only. Max 10MB.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Upload</button>
                        <a href="<?= site_url('dashboard'); ?>" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('material-upload-form');
    var fileInput = document.getElementById('material_file');

    if (!form || !fileInput) {
        return;
    }

    form.addEventListener('submit', function (e) {
        var file = fileInput.files[0];
        if (!file) {
            return; // backend will handle "uploaded" rule
        }

        var name = file.name.toLowerCase();
        if (!(name.endsWith('.pdf') || name.endsWith('.ppt'))) {
            e.preventDefault();
            alert('Only PDF and PPT files are allowed.');
            return false;
        }
    });
});
</script>

<?= $this->endSection() ?>
