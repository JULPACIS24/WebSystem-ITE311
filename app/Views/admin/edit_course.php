<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Edit Course</h2>
<p>Your role: <strong><?= esc($role) ?></strong></p>

<?php if (!empty($course)): ?>
    <form method="post" action="<?= site_url('/admin/course/update/' . $course['id']) ?>" class="mt-3">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Control Number (CN)</label>
                <input type="text" name="course_code" value="<?= esc($course['course_code'] ?? '') ?>" class="form-control" readonly />
            </div>
            <div class="col-md-5">
                <label class="form-label">Title</label>
                <input type="text" name="title" value="<?= esc($course['title'] ?? '') ?>" class="form-control" required />
            </div>
            <div class="col-md-2">
                <label class="form-label">Units</label>
                <input type="number" name="units" value="<?= esc($course['units'] ?? '') ?>" class="form-control" min="1" max="10" />
            </div>
            <div class="col-md-2">
                <label class="form-label">Semester</label>
                <select name="default_semester" class="form-select" required>
                    <option value="">-- Select Semester --</option>
                    <option value="1st Sem" <?= (isset($course['default_semester']) && $course['default_semester'] === '1st Sem') ? 'selected' : '' ?>>1st Sem</option>
                    <option value="2nd Sem" <?= (isset($course['default_semester']) && $course['default_semester'] === '2nd Sem') ? 'selected' : '' ?>>2nd Sem</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Term</label>
                <select name="term" class="form-select">
                    <option value="">-- Select Term --</option>
                    <option value="1" <?= (isset($course['term']) && (int)$course['term'] === 1) ? 'selected' : '' ?>>Term 1</option>
                    <option value="2" <?= (isset($course['term']) && (int)$course['term'] === 2) ? 'selected' : '' ?>>Term 2</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= site_url('/manage-courses') ?>" class="btn btn-secondary ms-2">Cancel</a>
        </div>
    </form>
<?php else: ?>
    <p class="text-danger">Course not found.</p>
<?php endif; ?>

<?= $this->endSection() ?>
