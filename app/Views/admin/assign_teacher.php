<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Assign Teacher to Courses</h2>
<p>Your role: <strong><?= esc($role) ?></strong></p>

<?php $normalizedRole = strtolower(trim($role ?? '')); ?>
<?php if ($normalizedRole === 'admin'): ?>

<div class="mb-3">
    <a href="<?= site_url('/courses') ?>" class="btn btn-outline-primary btn-sm">Search Courses</a>
</div>

<?php $assignSuccess = session()->getFlashdata('assign_success'); ?>
<?php $assignError   = session()->getFlashdata('assign_error'); ?>

<?php if ($assignSuccess): ?>
    <div class="alert alert-success mb-3"><?= $assignSuccess ?></div>
<?php endif; ?>
<?php if ($assignError): ?>
    <div class="alert alert-danger mb-3" id="assignErrorMessage"><?= $assignError ?></div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('assignErrorMessage');
            if (el) {
                var msg = el.textContent || el.innerText || '';
                msg = msg.trim();
                if (msg) {
                    alert(msg);
                }
            }
        });
    </script>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header fw-bold">Assign Teacher to Course</div>
    <div class="card-body">
        <form method="post" action="<?= site_url('/admin/assign-teacher') ?>" class="row g-3 align-items-end">
            <?= csrf_field() ?>

            <div class="col-md-4">
                <label class="form-label">Teacher <span class="text-danger">*</span></label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">Select Teacher</option>
                    <?php foreach (($teachers ?? []) as $teacher): ?>
                        <option value="<?= $teacher['id'] ?>"><?= esc($teacher['name'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Course <span class="text-danger">*</span></label>
                <select name="course_id" class="form-select" required>
                    <option value="">Select Course</option>
                    <?php foreach (($courses ?? []) as $course): ?>
                        <option value="<?= $course['id'] ?>"><?= esc($course['title'] ?? 'Course') ?> (<?= esc($course['course_code'] ?? '') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Assign Teacher</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header fw-bold">All Teacher Assignments</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%">ID</th>
                        <th style="width: 15%">Teacher</th>
                        <th style="width: 20%">Course</th>
                        <th style="width: 10%">Control Number</th>
                        <th style="width: 8%">Units</th>
                        <th style="width: 10%">Day</th>
                        <th style="width: 15%">Time</th>
                        <th style="width: 12%">School Year</th>
                        <th style="width: 10%">Semester</th>
                        <th style="width: 8%">Term</th>
                        <th style="width: 10%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $teacherNames = [];
                        foreach (($teachers ?? []) as $t) {
                            $teacherNames[$t['id']] = $t['name'] ?? '';
                        }
                    ?>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                            <?php
                                // Skip courses with no assigned teacher
                                if (empty($course['teacher_id'])) {
                                    continue;
                                }

                                $teacherName = '';
                                if (isset($teacherNames[$course['teacher_id']])) {
                                    $teacherName = $teacherNames[$course['teacher_id']];
                                }
                            ?>
                            <tr>
                                <td><?= esc($course['id']) ?></td>
                                <td><?= esc($teacherName ?: 'Not assigned') ?></td>
                                <td><?= esc($course['title'] ?? 'Course') ?></td>
                                <td><strong><?= esc($course['course_code'] ?? '') ?></strong></td>
                                <td>
                                    <?php if (!empty($course['units'])): ?>
                                        <span class="badge bg-info text-dark"><?= esc($course['units']) ?> units</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($course['schedule_day'] ?? '-') ?></td>
                                <td>
                                    <?php
                                        $start = $course['schedule_start_time'] ?? null;
                                        $end   = $course['schedule_end_time'] ?? null;
                                    ?>
                                    <?php if ($start && $end): ?>
                                        <?= esc($start) ?> - <?= esc($end) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $courseYear = $course['default_school_year'] ?? null;
                                        $displayYear = $courseYear ?: ($currentSchoolYear ?? null);
                                    ?>
                                    <?= esc($displayYear ?? '') ?>
                                </td>
                                <td><?= esc($course['default_semester'] ?? '') ?></td>
                                <td><?= esc($course['term'] ?? '-') ?></td>
                                <td class="text-center">
                                    <a href="<?= site_url('/admin/course/edit/' . $course['id']) ?>" class="btn btn-sm btn-warning me-1 px-2" title="Edit Course">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-pencil-square"></i>
                                            <small style="font-size: 10px; line-height: 1;">Edit</small>
                                        </div>
                                    </a>
                                    <form method="post" action="<?= site_url('/admin/course/delete/' . $course['id']) ?>" style="display:inline-block;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger px-2" title="Delete Course" onclick="return confirm('Delete this course assignment? This will remove the course itself.');">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bi bi-trash"></i>
                                                <small style="font-size: 10px; line-height: 1;">Del</small>
                                            </div>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No teacher assignments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: ?>
    <p class="text-muted">Assign Teacher is available only to administrators.</p>
<?php endif; ?>

<?= $this->endSection() ?>
