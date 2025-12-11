<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Manage Users</h2>
<p>Your role: <strong><?= esc($role) ?></strong></p>

<h4 class="mt-3">Add User</h4>
<form method="post" action="<?= site_url('/add-user') ?>" class="mb-4">
    <?= csrf_field() ?>
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required />
        </div>
        <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required />
        </div>
        <div class="col-md-2">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required />
        </div>
        <div class="col-md-2">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">Add User</button>
        </div>
    </div>
</form>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($users ?? [])): ?>
            <?php $currentUserId = session()->get('id'); ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= esc($user['id']) ?></td>
                    <td><?= esc($user['name']) ?></td>
                    <td><?= esc($user['email']) ?></td>
                    <td><?= esc($user['role']) ?></td>
                    <td><?= esc($user['created_at']) ?></td>
                    <td>
                        <?php if (!empty($user['is_deleted'])): ?>
                            <span class="text-muted me-2">Inactive</span>
                            <form method="post" action="<?= site_url('/restore-user/' . $user['id']) ?>" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Restore this user and make the account active again?');">Activate</button>
                            </form>
                        <?php elseif ((int)$user['id'] === (int)$currentUserId && $user['role'] === 'admin'): ?>
                            <span class="text-muted">Admin account</span>
                        <?php else: ?>
                            <form method="post" action="<?= site_url('/update-user-role/' . $user['id']) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <select name="role" class="form-select form-select-sm d-inline-block w-auto me-1">
                                    <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                    <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>

                            <form method="post" action="<?= site_url('/delete-user/' . $user['id']) ?>" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center text-muted">No users found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>
