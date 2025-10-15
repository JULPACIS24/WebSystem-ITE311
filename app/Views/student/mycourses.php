<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>My Courses</h2>

<?php if (!empty($enrolledCourses)): ?>
    <ul class="list-group mb-4">
        <?php foreach ($enrolledCourses as $course): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= esc($course['title']) ?>
                <span class="badge bg-success">Enrolled</span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="text-muted">You are not enrolled in any courses yet.</p>
<?php endif; ?>

<a href="<?= site_url('/dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>

<?= $this->endSection() ?>
