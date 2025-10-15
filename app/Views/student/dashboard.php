<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Welcome, <?= esc($name) ?>!</h2>
<p>Your role: <strong><?= esc($role) ?></strong></p>

<div class="alert alert-warning mb-4">Student Dashboard</div>

<!-- ✅ Enrolled Courses -->
<h4>Your Enrolled Courses</h4>
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

<!-- ✅ Available Courses -->
<h4>Available Courses</h4>
<?php if (!empty($availableCourses)): ?>
    <ul class="list-group mb-4">
        <?php foreach ($availableCourses as $course): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= esc($course['title']) ?>
                <form action="<?= site_url('/course/enroll') ?>" method="post" class="m-0">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Enroll</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="text-muted">No more available courses to enroll.</p>
<?php endif; ?>

<div class="mt-4">
    <a href="<?= site_url('/logout') ?>" class="btn btn-danger">Logout</a>
</div>

<?= $this->endSection() ?>
