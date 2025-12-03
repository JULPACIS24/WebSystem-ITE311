<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>
<h2>Add User</h2>
<?php if (isset($validation)): ?>
    <div class="alert alert-danger">
        <?= $validation->listErrors() ?>
    </div>
<?php endif; ?>
<form method="post" action="<?= site_url('/add-user') ?>">
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required />
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required />
    </div>
    <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required />
    </div>
    <div class="mb-3">
        <label>Role</label>
        <select name="role" class="form-control">
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="admin">Admin</option>
        </select>
    </div>
    <button type="submit" class="btn btn-success">Add User</button>
    <a href="<?= site_url('/manage-users') ?>" class="btn btn-secondary">Cancel</a>
</form>
<?= $this->endSection() ?>
