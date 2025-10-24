<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<div class="container mt-5">
    <h3>Upload Material for: <?= esc($course['title']) ?></h3>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Upload Form -->
    <form action="<?= site_url('materials/upload/' . $course['id']) ?>" 
          method="post" enctype="multipart/form-data" class="mt-4">

        <div class="form-group mb-3">
            <label for="file">Select File:</label>
            <input type="file" name="file" id="file" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="<?= site_url('/dashboard') ?>" class="btn btn-secondary">Back</a>
    </form>

    <!-- Optional: Show uploaded files -->
    <?php if (!empty($materials)): ?>
        <hr>
        <h4 class="mt-4">Uploaded Materials</h4>
        <ul class="list-group mt-2">
            <?php foreach ($materials as $m): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= esc($m['file_name']) ?>
                    <div>
                        <a href="<?= site_url('materials/download/' . $m['id']) ?>" class="btn btn-sm btn-success">Download</a>
                        <a href="<?= site_url('materials/delete/' . $m['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this file?')">Delete</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
