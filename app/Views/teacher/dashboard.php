<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Welcome, instructor (Teacher)</h2>
<p>This is your teacher dashboard.</p>

<!-- 🔹 Logout Button -->
<div class="mt-4">
    <a href="<?= site_url('/logout') ?>" class="btn btn-danger">Logout</a>
</div>

<?= $this->endSection() ?>
