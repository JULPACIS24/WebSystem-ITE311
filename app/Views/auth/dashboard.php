<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Welcome, <?= esc($name) ?>!</h2>
<p>Your role: <strong><?= esc($role) ?></strong></p>

<?php if ($role === 'admin'): ?>
    <div class="alert alert-primary">Admin Dashboard Content</div>
<?php elseif ($role === 'teacher'): ?>
    <div class="alert alert-success">Teacher Dashboard Content</div>
<?php elseif ($role === 'student'): ?>
    <div class="alert alert-warning">Student Dashboard Content</div>
<?php endif; ?>

<?= $this->endSection() ?>
