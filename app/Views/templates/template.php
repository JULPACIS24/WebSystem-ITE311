<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Unified Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>
<body>
    <?= $this->include('templates/header') ?>

    <div class="container">
        <?= $this->renderSection('content') ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script>
    $(function () {
        // Mark-as-read handler for server-rendered notification buttons
        $(document).on('click', '.notif-mark-btn', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (!id) {
                return;
            }

            $.post('<?= site_url('/notifications/mark_read') ?>/' + id, function (resp) {
                if (resp && resp.status === 'success') {
                    // Remove this notification item from the dropdown
                    var item = $('.notif-mark-btn[data-id="' + id + '"]').closest('li');
                    item.remove();

                    // Update badge count
                    var badge = $('#notifBadge');
                    var current = parseInt(badge.text() || '0', 10);
                    if (!isNaN(current) && current > 1) {
                        badge.text(current - 1).show();
                    } else {
                        badge.text('0').hide();
                    }

                    // If no more items, show the "No notifications" placeholder
                    if ($('.notif-mark-btn').length === 0) {
                        $('#notifEmpty').show();
                    }
                }
            }, 'json');
        });
    });
    </script>
</body>
</html>
