<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= site_url('/dashboard') ?>">PACIS LMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

 <?php if (!session()->get('isLoggedIn')): ?>
          <!-- Visible only when NOT logged in -->
          <li class="nav-item"><a class="nav-link" href="<?= site_url('/') ?>">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('/about') ?>">About</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('/contact') ?>">Contact</a></li>
        <?php else: ?>
          <!-- Visible only when logged in -->
          <?php if (session()->get('role') === 'admin'): ?>
            <li class="nav-item"><a class="nav-link admin-section-link" data-section="dashboard" href="#">Dashboard</a></li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="academicDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Academic Management
              </a>
              <ul class="dropdown-menu" aria-labelledby="academicDropdown">
                <li><h6 class="dropdown-header">Academic Management</h6></li>
                <li><hr class="dropdown-divider"></li>
                <!-- A. Manage Courses -->
                <li><a class="dropdown-item" href="<?= site_url('/manage-courses') ?>">Manage Courses</a></li>
                <!-- B. Assign Teacher to Courses -->
                <li><a class="dropdown-item" href="<?= site_url('/assign-teacher') ?>">Assign Teacher to Courses</a></li>
                <!-- C. Academic Year & Semester -->
                <li><a class="dropdown-item" href="<?= site_url('/academic-management') ?>">Academic Year &amp; Semester</a></li>
              </ul>
            </li>

            <li class="nav-item"><a class="nav-link admin-section-link" data-section="grades" href="#">Grades</a></li>
            <li class="nav-item"><a class="nav-link admin-section-link" data-section="reports" href="#">Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= site_url('/manage-users') ?>">Manage Users</a></li>
          <?php elseif (session()->get('role') === 'teacher'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= site_url('/upload-lessons') ?>">Upload Lessons</a></li>
          <?php elseif (session()->get('role') === 'student'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= site_url('/dashboard') ?>">My Courses</a></li>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (session()->get('isLoggedIn')): ?>
          <li class="nav-item"><a class="nav-link text-danger" href="<?= site_url('/logout') ?>">Logout</a></li>
        <?php endif; ?>

      </ul>
        </div>
    </div>
</nav>
