<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<div class="container mt-5">
    <h2 class="mb-4">Upload Course Material (Admin)</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= current_url() ?>"
          method="post"
          enctype="multipart/form-data">

        <?= csrf_field() ?>

        <?php if (!empty($course_id)): ?>
            <input type="hidden" name="course_id" value="<?= esc($course_id) ?>">
        <?php elseif (!empty($courses)): ?>
            <div class="mb-3">
                <label for="course_id" class="form-label">Select Course</label>
                <select name="course_id" id="course_id" class="form-select" required>
                    <option value="" selected disabled>-- Choose course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= esc($course['id']) ?>"><?= esc($course['name'] ?? ('Course #' . $course['id'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="material_file" class="form-label">Choose File (PDF, PPT, DOC, etc.)</label>
            <input type="file" name="material_file" id="material_file" class="form-control" required accept=".pdf,.ppt,.pptx,.doc,.docx">
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-secondary">Back</a>
    </form>
</div>

<?= $this->endSection() ?>
