<?php
    $initialUnreadCount = (int) ($unreadNotificationsCount ?? 0);
    $badgeClasses = 'badge bg-danger ms-1';
    if ($initialUnreadCount === 0) {
        $badgeClasses .= ' d-none';
    }
?>

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
                        <li class="nav-item"><a class="nav-link" href="<?= site_url('/manage-users') ?>">Manage Users</a></li>
                    <?php elseif (session()->get('role') === 'teacher'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= site_url('/teacher/upload') ?>">Upload Lessons</a></li>
                    <?php elseif (session()->get('role') === 'student'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= site_url('student/mycourses') ?>">My Courses</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center mb-2 mb-lg-0">
                <?php if (session()->get('isLoggedIn')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>Notifications</span>
                            <span class="<?= esc($badgeClasses) ?>" id="notification-badge" data-count="<?= $initialUnreadCount ?>">
                                <?= $initialUnreadCount ?>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="notificationDropdown" style="min-width: 320px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="m-0">Notifications</h6>
                                <button type="button" class="btn btn-link btn-sm p-0" id="notification-refresh">Refresh</button>
                            </div>
                            <div id="notification-loading" class="text-center py-2 d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="notification-empty" class="text-muted small <?= $initialUnreadCount > 0 ? 'd-none' : '' ?>">
                                No notifications yet.
                            </div>
                            <div id="notification-list" class="notification-list"></div>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link text-danger" href="<?= site_url('/logout') ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('/login') ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script>
    (function ($) {
        'use strict';

        const $badge = $('#notification-badge');
        const $list = $('#notification-list');
        const $emptyState = $('#notification-empty');
        const $loading = $('#notification-loading');

        function setBadge(count) {
            const value = Number(count) || 0;
            $badge.attr('data-count', value);
            $badge.text(value);

            if (value > 0) {
                $badge.removeClass('d-none');
            } else {
                $badge.addClass('d-none');
            }
        }

        function renderNotifications(items) {
            $list.empty();

            if (!items || items.length === 0) {
                $emptyState.removeClass('d-none');
                return;
            }

            $emptyState.addClass('d-none');

            items.forEach(function (item) {
                const $item = $('<div/>', {
                    class: 'alert alert-info d-flex justify-content-between align-items-start gap-2 mb-2',
                });

                $('<div/>', {
                    class: 'flex-grow-1',
                    text: item.message || 'Notification',
                }).appendTo($item);

                $('<button/>', {
                    class: 'btn btn-sm btn-outline-secondary notification-mark-read',
                    text: 'Mark as read',
                    'data-id': item.id,
                }).appendTo($item);

                $list.append($item);
            });
        }

        function fetchNotifications() {
            $loading.removeClass('d-none');

            $.ajax({
                url: '<?= site_url('/notifications') ?>',
                method: 'GET',
                dataType: 'json',
            })
                .done(function (response) {
                    console.debug('Notifications fetch response', response);
                    if (!response || response.success === false) {
                        return;
                    }

                    setBadge(response.unread_count);
                    renderNotifications(response.notifications || []);
                })
                .fail(function (jqXHR, textStatus) {
                    console.error('Notifications fetch failed', textStatus, jqXHR.status, jqXHR.responseText);
                })
                .always(function () {
                    $loading.addClass('d-none');
                });
        }

        function markNotificationAsRead(id, $button) {
            if (!id) {
                return;
            }

            $.ajax({
                url: '<?= site_url('/notifications/mark_read') ?>/' + id,
                method: 'POST',
                dataType: 'json',
            })
                .done(function (response) {
                    console.debug('Notification mark response', response);
                    if (!response || response.success === false) {
                        if (response && response.message) {
                            console.warn('Mark as read failed:', response.message);
                        }
                        return;
                    }

                    $button.closest('.alert').remove();

                    // Refresh from server to ensure latest state
                    fetchNotifications();

                    const remaining = $list.children().length;
                    if (remaining === 0) {
                        $emptyState.removeClass('d-none');
                    }

                    const currentCount = Number($badge.attr('data-count') || 0);
                    const nextCount = Math.max(0, currentCount - 1);
                    setBadge(nextCount);
                });
        }

        $(function () {
            if ($badge.length) {
                fetchNotifications();
                setInterval(fetchNotifications, 60000);
            }

            $('#notificationDropdown').on('show.bs.dropdown', function () {
                fetchNotifications();
            });

            $('#notification-refresh').on('click', function (event) {
                event.preventDefault();
                fetchNotifications();
            });

            $list.on('click', '.notification-mark-read', function () {
                const $button = $(this);
                const notificationId = $button.data('id');
                markNotificationAsRead(notificationId, $button);
            });
        });
    })(jQuery);
</script>
