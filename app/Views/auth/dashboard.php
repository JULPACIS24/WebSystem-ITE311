<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Welcome, <?= esc($name) ?>!</h2>
<p>Your role: <strong><?= esc($role) ?></strong></p>

<?php if ($role === 'admin'): ?>
    <div class="alert alert-primary mb-4">Admin Dashboard - Manage Users</div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Add User Form (inline on dashboard) -->
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
                                <span class="text-muted">Marked as deleted</span>
                            <?php elseif ((int)$user['id'] === (int)$currentUserId && $user['role'] === 'admin'): ?>
                                <span class="text-muted">Admin account</span>
                            <?php else: ?>
                                <!-- Change role form -->
                                <form method="post" action="<?= site_url('/update-user-role/' . $user['id']) ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <select name="role" class="form-select form-select-sm d-inline-block w-auto me-1">
                                        <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                        <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>

                                <!-- Delete user form -->
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

<?php elseif ($role === 'teacher'): ?>
    <div class="alert alert-success mb-4">Teacher Dashboard - Enroll Students to Courses</div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/teacher/enroll-student') ?>" class="card card-body mb-4">
        <?= csrf_field() ?>
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach (($teacherCourses ?? []) as $course): ?>
                        <option value="<?= $course['id'] ?>"><?= esc($course['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach (($students ?? []) as $student): ?>
                        <option value="<?= $student['id'] ?>"><?= esc($student['name']) ?> (<?= esc($student['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Enroll Student</button>
            </div>
        </div>
    </form>

<?php elseif ($role === 'student'): ?>
    <div class="alert alert-warning mb-4">Student Dashboard</div>

    <!-- ✅ Enrolled Courses -->
    <div id="enrollment-alert" class="alert d-none" role="alert"></div>

    <h4>Your Enrolled Courses</h4>
    <?php if (!empty($enrolledCourses ?? [])): ?>
        <ul id="enrolled-courses" class="list-group mb-4">
            <?php foreach ($enrolledCourses as $course): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center" data-course-id="<?= $course['id'] ?>">
                    <?= esc($course['title'] ?? 'Course') ?>
                    <span class="badge bg-success">Enrolled</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p id="no-enrolled-message" class="text-muted">You are not enrolled in any courses yet.</p>
        <ul id="enrolled-courses" class="list-group mb-4 d-none"></ul>
    <?php endif; ?>

    <!-- ✅ Available Courses (Dropdown) -->
    <h4>Available Courses</h4>
    <?php if (!empty($availableCourses ?? [])): ?>
        <form id="student-enroll-form" class="row g-3 align-items-end mb-4">
            <div class="col-md-8">
                <label class="form-label">Select Course</label>
                <select id="available-course-select" class="form-select" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($availableCourses as $course): ?>
                        <option value="<?= $course['id'] ?>"><?= esc($course['title'] ?? 'Course') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" id="btn-enroll-selected" class="btn btn-primary w-100">Enroll</button>
            </div>
        </form>
    <?php else: ?>
        <p class="text-muted">No more available courses to enroll.</p>
    <?php endif; ?>

    <script>
        $(function () {
            $('#student-enroll-form').on('submit', function (e) {
                e.preventDefault();

                var select   = $('#available-course-select');
                var courseId = select.val();
                var courseText = select.find('option:selected').text();

                if (!courseId) {
                    var alertBox = $('#enrollment-alert');
                    alertBox.removeClass('d-none alert-success').addClass('alert-danger')
                        .text('Please select a course to enroll.');
                    return;
                }

                $.ajax({
                    url: '<?= site_url('/course/enroll') ?>',
                    method: 'POST',
                    data: {
                        course_id: courseId,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function (response) {
                        var alertBox = $('#enrollment-alert');
                        alertBox.removeClass('d-none alert-success alert-danger');

                        if (response.status === 'success') {
                            alertBox.addClass('alert-success').text(response.message);

                            // Add to enrolled list
                            $('#no-enrolled-message').addClass('d-none');
                            $('#enrolled-courses').removeClass('d-none').append(
                                '<li class="list-group-item d-flex justify-content-between align-items-center" data-course-id="' + courseId + '">' +
                                $('<div>').text(courseText).html() +
                                '<span class="badge bg-success">Enrolled</span>' +
                                '</li>'
                            );

                            // Remove selected option from dropdown
                            select.find('option[value="' + courseId + '"]').remove();

                            // Reset select
                            select.val('');

                            // If no more courses, hide form
                            if (select.find('option').length === 1) {
                                $('#student-enroll-form').remove();
                                $('#enrollment-alert').after('<p class="text-muted">No more available courses to enroll.</p>');
                            }
                        } else {
                            alertBox.addClass('alert-danger').text(response.message || 'Enrollment failed.');
                        }
                    },
                    error: function () {
                        var alertBox = $('#enrollment-alert');
                        alertBox.removeClass('d-none alert-success').addClass('alert-danger')
                            .text('An error occurred while enrolling.');
                    }
                });
            });
        });
    </script>

<?php endif; ?>

<div class="mt-4">
    <a href="<?= site_url('/logout') ?>" class="btn btn-danger">Logout</a>
</div>

<?= $this->endSection() ?>
