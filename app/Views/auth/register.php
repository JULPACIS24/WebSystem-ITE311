<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Register</h2>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?= \Config\Services::validation()->listErrors() ?>

    <form method="post" action="/register">
        <input type="text" name="name" class="form-control mb-2" placeholder="Full Name">
        <input type="email" name="email" class="form-control mb-2" placeholder="Email">
        <input type="password" name="password" class="form-control mb-2" placeholder="Password">
        <input type="password" name="password_confirm" class="form-control mb-2" placeholder="Confirm Password">
        <button class="btn btn-success">Register</button>
    </form>
    <a href="/login">Already have an account? Login</a>
</body>
</html>
