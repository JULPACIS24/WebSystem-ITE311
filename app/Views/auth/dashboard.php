<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

    <?php if (session()->get('isLoggedIn')): ?>
        <div class="card shadow p-4">
            <h2 class="mb-3">Welcome, <?= esc(session()->get('name')) ?></h2>
            <p><strong>Your role:</strong> <?= esc(session()->get('role')) ?></p>

            <div class="mt-4">
                <a href="/logout" class="btn btn-danger">Logout</a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            You are not logged in. <a href="/login">Login here</a>.
        </div>
    <?php endif; ?>

</body>
</html>
