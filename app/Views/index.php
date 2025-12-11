<?= $this->extend('templates/template') ?>

<?= $this->section('content') ?>
    <div class="min-vh-75 d-flex align-items-center justify-content-center py-5"
         style="
            background-image:
                linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)),
                url('/ITE311-PACIS/public/images/School%20building.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
         ">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-7 col-md-10">
                <div class="p-5 p-md-4 rounded-4 shadow-sm bg-white text-center">
                    <p class="text-uppercase text-primary fw-semibold mb-2">Pacis LMS</p>
                    <h1 class="display-5 fw-bold mb-3">Welcome to Pacis Home Page!</h1>
                    <p class="lead text-muted mb-4">
                        This page is using a Bootstrap template Pacis.
                    </p>

                    <?php if (!session()->get('isLoggedIn')): ?>
                        <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">
                            <a href="<?= site_url('/register') ?>" class="btn btn-primary btn-lg px-4">Register</a>
                            <a href="<?= site_url('/login') ?>" class="btn btn-outline-primary btn-lg px-4">Login</a>
                        </div>
                    <?php else: ?>
                        <div class="mt-4">
                            <h3 class="h4 mb-3">Welcome back, <?= session()->get('name') ?>!</h3>
                            <a href="<?= site_url('/dashboard') ?>" class="btn btn-success btn-lg px-4">Go to Dashboard</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
