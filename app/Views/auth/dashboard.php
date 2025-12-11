<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Welcome, <?= esc($name) ?>!</h2>
<p>Your role: <strong><?= esc($role) ?></strong></p>

<?php $normalizedRole = strtolower(trim($role ?? '')); ?>

<?php if ($normalizedRole === 'admin'): ?>

    <div class="alert alert-primary mb-4">Admin Dashboard</div>
    <p>Use the navigation above to manage academic settings, courses, enrollment, users, and reports.</p>

<?php elseif ($normalizedRole === 'teacher'): ?>
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
            <div class="col-md-3">
                <label class="form-label">Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach (($teacherCourses ?? []) as $course): ?>
                        <?php $u = $course['units'] ?? null; ?>
                        <option value="<?= $course['id'] ?>">
                            <?= esc($course['title']) ?><?= $u !== null && $u !== '' ? ' (' . esc($u) . ' units)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach (($students ?? []) as $student): ?>
                        <option value="<?= $student['id'] ?>"><?= esc($student['name']) ?> (<?= esc($student['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-select" required>
                    <option value="1st Sem">1st Sem</option>
                    <option value="2nd Sem">2nd Sem</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">School Year</label>
                <input type="text" name="school_year" class="form-control" value="2025-2026" placeholder="e.g. 2025-2026" required />
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Enroll Student</button>
            </div>
        </div>
    </form>

    <?php if (!empty($pendingEnrollments ?? [])): ?>
        <h4 class="mt-4">View Pending Enrollment Requests</h4>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>School Year</th>
                        <th>Requested On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingEnrollments as $enroll): ?>
                        <?php
                            $requestedAt = $enroll['enrolled_at'] ?? null;
                            $requestedDisp = $requestedAt ? date('M d, Y', strtotime($requestedAt)) : 'N/A';
                        ?>
                        <tr>
                            <td><?= esc($enroll['student_name']) ?> (<?= esc($enroll['student_email']) ?>)</td>
                            <td><?= esc($enroll['course_title'] ?? 'Course') ?></td>
                            <td><?= esc($enroll['semester'] ?? '') ?></td>
                            <td><?= esc($enroll['school_year'] ?? '') ?></td>
                            <td><?= esc($requestedDisp) ?></td>
                            <td>
                                <form method="post" action="<?= site_url('/teacher/approve-enrollment/' . $enroll['id']) ?>" onsubmit="return confirm('Approve this enrollment?');" class="d-inline me-1">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form method="post" action="<?= site_url('/teacher/reject-enrollment/' . $enroll['id']) ?>" onsubmit="return confirm('Reject this enrollment?');" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No pending enrollments at the moment.</p>
    <?php endif; ?>

    <?php if (!empty($teacherCourses ?? [])): ?>
        <h4 class="mt-4">My Courses</h4>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>CN</th>
                        <th>Course</th>
                        <th>Units</th>
                        <th>Default Semester</th>
                        <th>Term</th>
                        <th>School Year</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Open</th>
                        <th>Materials</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teacherCourses as $course): ?>
                        <?php
                            $cCreatedAt   = $course['created_at'] ?? null;
                            $cDayDisp     = $cCreatedAt ? date('D', strtotime($cCreatedAt)) : '';
                            $cTimeDisp    = $cCreatedAt ? date('h:i A', strtotime($cCreatedAt)) : '';
                            $cTerm        = $course['default_semester'] ?? '';
                        ?>
                        <tr>
                            <td><?= esc($course['course_code'] ?? '') ?></td>
                            <td><?= esc($course['title'] ?? 'Course') ?></td>
                            <td><?= esc($course['units'] ?? '') ?></td>
                            <td><?= esc($course['default_semester'] ?? '') ?></td>
                            <td><?= esc($cTerm) ?></td>
                            <td>
                                <?php
                                    $courseYear  = $course['default_school_year'] ?? null;
                                    $displayYear = $courseYear ?: ($currentSchoolYear ?? null);
                                ?>
                                <?= esc($displayYear ?? '') ?>
                            </td>
                            <td><?= esc($cDayDisp) ?></td>
                            <td><?= esc($cTimeDisp) ?></td>
                            <td><?= !empty($course['is_open']) ? 'Open' : 'Closed' ?></td>
                            <td>
                                <a href="<?= site_url('materials/upload/' . $course['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    Upload Materials
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if (!empty($activeEnrollments ?? [])): ?>
        <h4 class="mt-4">Enrolled Students Per Course</h4>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Course</th>
                        <th>Student</th>
                        <th>Units</th>
                        <th>Semester</th>
                        <th>School Year</th>
                        <th>Enrolled On</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeEnrollments as $enroll): ?>
                        <?php
                            $tEnrolledAt   = $enroll['enrolled_at'] ?? null;
                            $tEnrolledDisp = $tEnrolledAt ? date('M d, Y', strtotime($tEnrolledAt)) : 'N/A';
                            $tDayDisp      = $tEnrolledAt ? date('D', strtotime($tEnrolledAt)) : '';
                            $tTimeDisp     = $tEnrolledAt ? date('h:i A', strtotime($tEnrolledAt)) : '';
                        ?>
                        <tr>
                            <td><?= esc($enroll['course_title'] ?? 'Course') ?></td>
                            <td><?= esc($enroll['student_name']) ?> (<?= esc($enroll['student_email']) ?>)</td>
                            <td><?= esc($enroll['course_units'] ?? '') ?></td>
                            <td><?= esc($enroll['semester'] ?? '') ?></td>
                            <td><?= esc($enroll['school_year'] ?? '') ?></td>
                            <td><?= esc($tEnrolledDisp) ?></td>
                            <td><?= esc($tDayDisp) ?></td>
                            <td><?= esc($tTimeDisp) ?></td>
                            <td><span class="badge bg-success">Active</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php elseif ($normalizedRole === 'student'): ?>
    <div class="alert alert-warning mb-4">Student Dashboard</div>

    <div id="enrollment-alert" class="alert d-none" role="alert"></div>

    <h4>Your Enrolled Courses</h4>
    <?php if (!empty($enrolledCourses ?? [])): ?>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>CN</th>
                        <th>Course Name</th>
                        <th>Units</th>
                        <th>Semester</th>
                        <th>School Year</th>
                        <th>Enrolled On</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Materials</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <?php
                            $sem        = $course['semester']    ?? '';
                            $sy         = $course['school_year'] ?? '';
                            $enrolledAt   = $course['enrolled_at'] ?? null;
                            $enrolledDisp = $enrolledAt ? date('M d, Y', strtotime($enrolledAt)) : 'N/A';
                            $dayDisp      = $enrolledAt ? date('D', strtotime($enrolledAt)) : '';
                            $timeDisp     = $enrolledAt ? date('h:i A', strtotime($enrolledAt)) : '';
                            $status     = $course['status']      ?? '';
                        ?>
                        <?php $cid = $course['course_id'] ?? $course['id'] ?? null; ?>
                        <tr data-course-id="<?= $cid ?>">
                            <td><?= esc($course['course_code'] ?? '') ?></td>
                            <td><?= esc($course['title'] ?? 'Course') ?></td>
                            <td><?= esc($course['units'] ?? '') ?></td>
                            <td><?= esc($sem) ?></td>
                            <td><?= esc($sy) ?></td>
                            <td><?= esc($enrolledDisp) ?></td>
                            <td><?= esc($dayDisp) ?></td>
                            <td><?= esc($timeDisp) ?></td>
                            <td>
                                <?php if ($status === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif ($status === 'active'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= esc($status ?: 'Unknown') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $courseMaterials = ($cid && !empty($materialsByCourse[$cid])) ? $materialsByCourse[$cid] : []; ?>
                                <?php if (!empty($courseMaterials)): ?>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($courseMaterials as $material): ?>
                                            <li class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="me-2 text-truncate" style="max-width: 220px;">
                                                    <?= esc($material['file_name'] ?? 'Material') ?>
                                                </span>
                                                <a href="<?= site_url('materials/download/' . $material['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                    Download
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span class="text-muted">No materials</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p id="no-enrolled-message" class="text-muted">You are not enrolled in any courses yet.</p>
        <ul id="enrolled-courses" class="list-group mb-4 d-none"></ul>
    <?php endif; ?>

    <h4>Available Courses</h4>
    <?php if (!empty($availableCourses ?? [])): ?>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>CN</th>
                        <th>Course Name</th>
                        <th>Semester</th>
                        <th>School Year</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availableCourses as $course): ?>
                        <tr>
                            <td><?= esc($course['course_code'] ?? '') ?></td>
                            <td><?= esc($course['title'] ?? 'Course') ?></td>
                            <?php $open = !empty($course['is_open']); ?>
                            <td>
                                <?php if ($open): ?>
                                    <form method="post" action="<?= site_url('/course/enroll') ?>" class="d-flex align-items-center gap-1">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>" />
                                        <select name="semester" class="form-select form-select-sm" required>
                                            <option value="1st Sem">1st Sem</option>
                                            <option value="2nd Sem">2nd Sem</option>
                                            <option value="Summer">Summer</option>
                                        </select>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($open): ?>
                                        <input type="text" name="school_year" class="form-control form-control-sm" value="2025-2026" placeholder="e.g. 2025-2026" required />
                                <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $open ? 'Open' : 'Closed' ?>
                            </td>
                            <td>
                                <?php if ($open): ?>
                                        <button type="submit" class="btn btn-sm btn-primary">Enroll</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Closed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No more available courses to enroll.</p>
    <?php endif; ?>

<?php endif; ?>

<div class="mt-4">
    <a href="<?= site_url('/logout') ?>" class="btn btn-danger">Logout</a>
</div>

<?= $this->endSection() ?>
