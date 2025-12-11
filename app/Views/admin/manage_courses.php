<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<h2>Manage Courses</h2>
<p>Your role: <strong><?= esc($role) ?></strong></p>

<h4 class="mt-3">Add Course</h4>
<form method="post" action="<?= site_url('/admin/course/add') ?>" class="mb-4">
    <?= csrf_field() ?>
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label">Course CN (auto)</label>
            <input type="text" class="form-control" placeholder="Auto-generated" readonly />
        </div>
        <div class="col-md-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required />
        </div>
        <div class="col-md-2">
            <label class="form-label">Units</label>
            <input type="number" name="units" class="form-control" min="1" max="10" />
        </div>
        <div class="col-md-3">
            <label class="form-label">Semester</label>
            <select name="default_semester" class="form-select" required>
                <option value="">-- Select Semester --</option>
                <option value="1st Sem">1st Sem</option>
                <option value="2nd Sem">2nd Sem</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Term</label>
            <select name="term" class="form-select">
                <option value="">-- Select Term --</option>
                <option value="1">Term 1</option>
                <option value="2">Term 2</option>
            </select>
        </div>
        <div class="col-md-2 mt-2">
            <label class="form-label">Day</label>
            <select name="schedule_day" class="form-select">
                <option value="">-- Select Day --</option>
                <option value="Mon">Mon</option>
                <option value="Tue">Tue</option>
                <option value="Wed">Wed</option>
                <option value="Thu">Thu</option>
                <option value="Fri">Fri</option>
                <option value="Sat">Sat</option>
                <option value="Sun">Sun</option>
            </select>
        </div>
        <div class="col-md-4 mt-2">
            <label class="form-label">Time</label>
            <select id="time_slot" class="form-select">
                <option value="">Select Time</option>
                <option value="07:00-08:00">7:00 AM - 8:00 AM</option>
                <option value="08:00-09:00">8:00 AM - 9:00 AM</option>
                <option value="09:00-10:00">9:00 AM - 10:00 AM</option>
                <option value="10:00-11:00">10:00 AM - 11:00 AM</option>
                <option value="11:00-12:00">11:00 AM - 12:00 PM</option>
                <option value="12:00-13:00">12:00 PM - 1:00 PM</option>
                <option value="13:00-14:00">1:00 PM - 2:00 PM</option>
                <option value="14:00-15:00">2:00 PM - 3:00 PM</option>
                <option value="15:00-16:00">3:00 PM - 4:00 PM</option>
                <option value="16:00-17:00">4:00 PM - 5:00 PM</option>
                <option value="17:00-18:00">5:00 PM - 6:00 PM</option>
                <option value="18:00-19:00">6:00 PM - 7:00 PM</option>
                <option value="19:00-20:00">7:00 PM - 8:00 PM</option>
            </select>
            <input type="hidden" name="schedule_start_time" id="schedule_start_time" />
            <input type="hidden" name="schedule_end_time" id="schedule_end_time" />
        </div>
        <div class="col-md-2 d-flex align-items-end mt-2">
            <button type="submit" class="btn btn-success w-100">Add Course</button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var timeSlotSelect = document.getElementById('time_slot');
    var startInput = document.getElementById('schedule_start_time');
    var endInput = document.getElementById('schedule_end_time');

    if (timeSlotSelect) {
        timeSlotSelect.addEventListener('change', function () {
            var value = this.value; // format HH:MM-HH:MM
            if (!value) {
                startInput.value = '';
                endInput.value = '';
                return;
            }
            var parts = value.split('-');
            if (parts.length === 2) {
                startInput.value = parts[0];
                endInput.value = parts[1];
            }
        });
    }
});
</script>

<div class="card mt-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-list-ul me-2"></i>
        <span>All Courses</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0 table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Control Number</th>
                    <th>Title</th>
                    <th style="width: 90px;">Units</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Instructor</th>
                    <th>School Year</th>
                    <th>Semester</th>
                    <th>Term</th>
                    <th style="width: 120px;" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($courses ?? [])): ?>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?= esc($course['id']) ?></td>
                            <td><strong><?= esc($course['course_code'] ?? '') ?></strong></td>
                            <td><?= esc($course['title'] ?? 'Course') ?></td>
                            <td>
                                <?php $units = $course['units'] ?? null; ?>
                                <?php if ($units): ?>
                                    <span class="badge bg-info text-dark"><?= esc($units) ?> units</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
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
                            <td><?= esc($course['instructor_name'] ?? '-') ?></td>
                            <td>
                                <?php
                                    $courseYear = $course['default_school_year'] ?? null;
                                    $displayYear = $courseYear ?: ($currentSchoolYear ?? null);
                                ?>
                                <?= esc($displayYear ?? '-') ?>
                            </td>
                            <td><?= esc($course['default_semester'] ?? '-') ?></td>
                            <td><?= esc($course['term'] ?? '-') ?></td>
                            <td class="text-center">
                                <a href="<?= site_url('/admin/course/materials/' . $course['id']) ?>" class="btn btn-sm btn-primary me-1 px-2" title="Manage Materials">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-folder"></i>
                                        <small style="font-size: 10px; line-height: 1;">Mat</small>
                                    </div>
                                </a>
                                <a href="<?= site_url('/admin/course/edit/' . $course['id']) ?>" class="btn btn-sm btn-warning me-1 px-2" title="Edit Course">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-pencil-square"></i>
                                        <small style="font-size: 10px; line-height: 1;">Edit</small>
                                    </div>
                                </a>
                                <form method="post" action="<?= site_url('/admin/course/delete/' . $course['id']) ?>" style="display:inline-block;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger px-2" title="Delete Course" onclick="return confirm('Delete this course?');">
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
                        <td colspan="10" class="text-center text-muted py-3">No courses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
