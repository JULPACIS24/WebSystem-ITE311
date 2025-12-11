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
          <?php elseif (session()->get('role') === 'student'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= site_url('/dashboard') ?>">My Courses</a></li>
          <?php endif; ?>
        <?php endif; ?>

        </ul>

        <?php if (session()->get('isLoggedIn')): ?>
          <?php
              $notifItems = [];
              $notifCount = 0;
              try {
                  $notifModel = new \App\Models\NotificationModel();
                  $userId     = (int) session()->get('id');

                  // Load only unread notifications for this user
                  $notifItems = $notifModel
                      ->where('user_id', $userId)
                      ->where('is_read', 0)
                      ->orderBy('created_at', 'DESC')
                      ->findAll(5, 0);

                  $notifCount = count($notifItems);
              } catch (\Throwable $e) {
                  $notifItems = [];
                  $notifCount = 0;
              }
          ?>
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item dropdown">
              <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Notifications
                <?php if ($notifCount > 0): ?>
                  <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $notifCount ?></span>
                <?php else: ?>
                  <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
                <?php endif; ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="min-width: 360px; max-width: 420px;">
                <li class="dropdown-header">Notifications</li>
                <li><hr class="dropdown-divider"></li>
                <?php if (empty($notifItems)): ?>
                  <li id="notifEmpty"><span class="dropdown-item text-muted">No notifications.</span></li>
                <?php else: ?>
                  <?php foreach ($notifItems as $item): ?>
                    <li>
                      <div class="dropdown-item small" style="white-space: normal; line-height: 1.3;">
                        <?= esc($item['message']) ?>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary w-100 mt-1 notif-mark-btn"
                                data-id="<?= (int) $item['id'] ?>">
                          Mark as read
                        </button>
                      </div>
                    </li>
                  <?php endforeach; ?>
                  <li id="notifEmpty" style="display:none;"><span class="dropdown-item text-muted">No notifications.</span></li>
                <?php endif; ?>
                <div id="notifList"></div>
              </ul>
            </li>
            <li class="nav-item"><a class="nav-link text-danger" href="<?= site_url('/logout') ?>">Logout</a></li>
          </ul>
        <?php endif; ?>


        </div>
    </div>
</nav>
