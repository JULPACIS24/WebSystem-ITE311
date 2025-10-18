<!DOCTYPE html>
<html>
<head>
    <title>Announcements</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h2>Announcements</h2>

    <?php if (!empty($announcements) && is_array($announcements)) : ?>
        <ul class="list-group mt-3">
            <?php foreach ($announcements as $announcement): ?>
                <li class="list-group-item">
                    <h5><?= esc($announcement['title']) ?></h5>
                    <p><?= esc($announcement['content']) ?></p>
                    <small class="text-muted">Posted on: <?= date('F d, Y h:i A', strtotime($announcement['created_at'])) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted mt-3">No announcements available.</p>
    <?php endif; ?>
</body>
</html>
