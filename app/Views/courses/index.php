<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h2 class="mb-4">Courses</h2>

    <div class="mb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back(); return false;">
            &laquo; Back
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <form id="searchForm" class="d-flex" method="get" action="<?= site_url('/courses/search') ?>">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="Search courses..." name="search_term">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($courses ?? [])): ?>
        <div id="coursesContainer" class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-4">
                    <div class="card course-card">
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($course['course_name'] ?? $course['title'] ?? 'Course') ?></h5>
                            <p class="card-text"><?= esc($course['course_description'] ?? $course['description'] ?? '') ?></p>
                            <a href="<?= site_url('/courses/view/' . ($course['id'] ?? '')) ?>" class="btn btn-primary">View Course</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="row" id="coursesContainer">
            <div class="col-12">
                <div class="alert alert-info mb-0">No courses found.</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
window.addEventListener('load', function () {
    function performSearch(term) {
        $.get('<?= site_url('/courses/search') ?>', { search_term: term }, function (data) {
            $('#coursesContainer').empty();

            if (data && data.length > 0) {
                $.each(data, function (index, course) {
                    var name = course.title || course.course_name || 'Course';
                    var desc = course.description || course.course_description || '';
                    var id   = course.id || '';

                    var courseHtml =
                        '<div class="col-md-4 mb-4">' +
                            '<div class="card course-card">' +
                                '<div class="card-body">' +
                                    '<h5 class="card-title">' + name + '</h5>' +
                                    '<p class="card-text">' + desc + '</p>' +
                                    '<a href="/courses/view/' + id + '" class="btn btn-primary">View Course</a>' +
                                '</div>' +
                            '</div>' +
                        '</div>';

                    $('#coursesContainer').append(courseHtml);
                });
            } else {
                $('#coursesContainer').html('<div class="col-12"><div class="alert alert-info">No courses found matching your search.</div></div>');
            }
        }, 'json');
    }

    // Instant server-side search on keyup (1 or more letters)
    $('#searchInput').on('keyup', function () {
        var value = $(this).val();

        // If box is empty, load all courses
        if (value.length === 0) {
            performSearch('');
            return;
        }

        // Trigger AJAX search as user types
        performSearch(value);
    });

    // Optional: keep form submit from reloading page
    $('#searchForm').on('submit', function (e) {
        e.preventDefault();
        var value = $('#searchInput').val();
        performSearch(value);
    });
});
</script>

<?= $this->endSection() ?>
