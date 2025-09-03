<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>
</head>
<body class="container mt-5">
    <h2>Welcome, <?= session()->get('name') ?></h2>
    <p>Your role: <?= session()->get('role') ?></p>
    <a href="/logout" class="btn btn-danger">Logout</a>
</body>
</html>
