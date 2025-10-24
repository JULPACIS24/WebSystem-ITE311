<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Welcome, Admin!</h2>
<p>This is your admin dashboard.</p>

<!-- 🔹 Upload Lessons Button -->
<a href="<?= site_url('/admin/upload') ?>" class="btn btn-primary mt-3">Upload Lessons</a>

<!-- 🔹 Logout Button -->
<div class="mt-4">
    <a href="<?= site_url('/logout') ?>" class="btn btn-danger">Logout</a>
</div>

<?= $this->endSection() ?>
