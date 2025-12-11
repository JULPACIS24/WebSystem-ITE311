<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Academic Year &amp; Semester</h2>
<p>Your role: <strong><?= esc($role) ?></strong></p>

<?php $normalizedRole = strtolower(trim($role ?? '')); ?>
<?php if ($normalizedRole === 'admin'): ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php elseif (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header fw-bold">Academic Years</div>
    <div class="card-body">
        <form method="post" action="<?= site_url('/admin/academic-year/save') ?>" class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <div class="col-md-4">
                <label class="form-label">Current Academic Year</label>
                <input type="text" name="active_school_year" class="form-control" placeholder="e.g. 2025-2026" value="<?= esc($setting['current_school_year'] ?? '') ?>" />
            </div>
            <div class="col-md-3 d-flex justify-content-start">
                <button type="submit" class="btn btn-primary mt-4">Save Academic Year</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var semesterSelect = document.getElementById('active_semester');
    var startInput = document.getElementById('semester_start_date');
    var endInput = document.getElementById('semester_end_date');

    function formatDate(date) {
        var y = date.getFullYear();
        var m = String(date.getMonth() + 1).padStart(2, '0');
        var d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function autoFillStartIfEmpty() {
        if (!startInput.value) {
            var today = new Date();
            startInput.value = formatDate(today);
        }
    }

    function updateEndDate() {
        if (!semesterSelect || !startInput || !endInput) return;

        var sem = semesterSelect.value;
        var startVal = startInput.value;
        if (!sem || !startVal) {
            endInput.value = '';
            return;
        }

        var startDate = new Date(startVal + 'T00:00:00');
        // Approximate durations based on your schedule:
        // 1st Semester: ~18 weeks, 2nd Semester: ~19 weeks
        var weeks = 0;
        if (sem === '1st Semester') {
            weeks = 18;
        } else if (sem === '2nd Semester') {
            weeks = 19;
        } else {
            weeks = 18;
        }

        var daysToAdd = weeks * 7;
        startDate.setDate(startDate.getDate() + daysToAdd);
        endInput.value = formatDate(startDate);
    }

    if (semesterSelect) {
        semesterSelect.addEventListener('change', function () {
            autoFillStartIfEmpty();
            updateEndDate();
        });
    }

    if (startInput) {
        startInput.addEventListener('change', function () {
            updateEndDate();
        });
    }
});
</script>

<div class="card mb-3">
    <div class="card-header fw-bold">Semesters</div>
    <div class="card-body">
        <form method="post" action="<?= site_url('/admin/semester/save') ?>" class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <div class="col-md-5">
                <label class="form-label">School Year</label>
                <select name="school_year" class="form-select">
                    <option value="">-- Select School Year --</option>
                    <option value="2025-2026">2025-2026</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Semester</label>
                <select name="active_semester" id="active_semester" class="form-select">
                    <option value="">-- Select Semester --</option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="Summer">Summer (optional)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Start Date</label>
                <input type="date" name="semester_start_date" id="semester_start_date" class="form-control" />
            </div>
            <div class="col-md-2">
                <label class="form-label">End Date</label>
                <input type="date" name="semester_end_date" id="semester_end_date" class="form-control" />
            </div>
            <div class="col-12 mt-2 d-flex justify-content-start">
                <button type="submit" class="btn btn-primary">Save Semester Settings</button>
            </div>
        </form>

        <hr class="my-3" />
        <h6>Semester List</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%">School Year</th>
                        <th style="width: 20%">Semester</th>
                        <th style="width: 20%">Start Date</th>
                        <th style="width: 20%">End Date</th>
                        <th style="width: 20%">Enrollment Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($semesters ?? [])): ?>
                        <?php foreach ($semesters as $sem): ?>
                            <?php
                                $end = $sem['end_date'] ?? null;
                                $statusLabel = 'Closed';
                                if ($end) {
                                    $today = date('Y-m-d');
                                    $statusLabel = ($today <= substr($end, 0, 10)) ? 'Open' : 'Closed';
                                }
                            ?>
                            <tr>
                                <td><?= esc($sem['school_year'] ?? '') ?></td>
                                <td><?= esc($sem['semester_name'] ?? '') ?></td>
                                <td><?= esc($sem['start_date'] ?? '') ?></td>
                                <td><?= esc($sem['end_date'] ?? '') ?></td>
                                <td>
                                    <?php if ($statusLabel === 'Open'): ?>
                                        <span class="badge bg-success">Open</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Closed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No semesters configured yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: ?>
    <p class="text-muted">Academic Year &amp; Semester is available only to administrators.</p>
<?php endif; ?>

<?= $this->endSection() ?>
