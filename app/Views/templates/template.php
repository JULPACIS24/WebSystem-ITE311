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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-Y8G8Hqv7ZtJkFh3H6Wcv16O3Qai9nEIOWeItPXIk7N0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script>
    $(function () {
        function renderNotifications(data) {
            var badge = $('#notifBadge');
            var listContainer = $('#notifList');
            var emptyItem = $('#notifEmpty');

            if (!badge.length || !listContainer.length) {
                return;
            }

            var count = data.count || 0;

            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }

            listContainer.empty();

            var items = data.items || [];
            if (items.length === 0) {
                emptyItem.show();
                return;
            }

            emptyItem.hide();

            items.forEach(function (item) {
                var msg = item.message || 'Notification';
                var id  = item.id;

                var row = $('<li></li>');
                var wrapper = $('<div class="dropdown-item"></div>');
                var text = $('<div class="alert alert-info mb-1 py-1 px-2"></div>').text(msg);
                var btn = $('<button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-1">Mark as read</button>');

                btn.on('click', function () {
                    $.post('<?= site_url('/notifications/mark_read') ?>/' + id, function (resp) {
                        fetchNotifications();
                    }, 'json');
                });

                wrapper.append(text).append(btn);
                row.append(wrapper);
                listContainer.append(row);
            });
        }

        function fetchNotifications() {
            if (!$('#notifBadge').length) {
                return;
            }

            $.get('<?= site_url('/notifications') ?>', function (resp) {
                if (resp && resp.status === 'success') {
                    renderNotifications(resp);
                }
            }, 'json');
        }

        fetchNotifications();
        setInterval(fetchNotifications, 60000);
    });
    </script>
</body>
</html>
