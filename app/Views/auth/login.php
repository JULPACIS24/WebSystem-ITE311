<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Login</h2>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<?= \Config\Services::validation()->listErrors() ?>

<form method="post" action="<?= site_url('/login') ?>">
    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
    <button class="btn btn-primary">Login</button>
</form>

<a href="<?= site_url('/register') ?>">Create an account</a>

<?= $this->endSection() ?>
