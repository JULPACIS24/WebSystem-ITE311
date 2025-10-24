<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<div class="container mt-5">
    <h2 class="mb-4">Upload Course Material (Teacher)</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('teacher/course/' . ($course_id ?? 1) . '/upload') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="material_file" class="form-label">Choose File (PDF, PPT, DOC)</label>
            <input type="file" name="material_file" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Upload</button>
        <a href="<?= base_url('teacher/dashboard') ?>" class="btn btn-secondary">Back</a>
    </form>
</div>

<?= $this->endSection() ?>
