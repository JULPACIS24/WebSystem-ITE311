<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<div class="container mt-5">
    <h2 class="mb-4">Upload Course Material (Admin)</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- ✅ Corrected: dynamic course_id, correct action, enctype required -->
    <form action="<?= base_url('admin/course/' . $course_id . '/upload') ?>" 
          method="post" 
          enctype="multipart/form-data">

        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="material_file" class="form-label">Choose File (PDF, PPT, DOC, etc.)</label>
            <input type="file" name="material_file" id="material_file" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-secondary">Back</a>
    </form>
</div>

<?= $this->endSection() ?>
