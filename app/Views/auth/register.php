<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Register</h2>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<!-- Show validation errors -->
<?php if (isset($validation)): ?>
    <div class="alert alert-danger">
        <?= $validation->listErrors() ?>
    </div>
<?php endif; ?>

<form method="post" action="/register">
    <input type="text" name="name" class="form-control mb-2" 
           placeholder="Full Name" value="<?= set_value('name') ?>" required>

    <input type="email" name="email" class="form-control mb-2" 
           placeholder="Email" value="<?= set_value('email') ?>" required>

    <input type="password" name="password" class="form-control mb-2" 
           placeholder="Password" required>

    <input type="password" name="password_confirm" class="form-control mb-2" 
           placeholder="Confirm Password" required>

    <button class="btn btn-success">Register</button>
</form>

<a href="/login">Already have an account? Login</a>

<?= $this->endSection() ?>
