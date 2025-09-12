<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <h1 class="mb-3">Welcome to Pacis Home Page!</h1>
    <p class="lead">This page is using a Bootstrap template Pacis.</p>

    <?php if (!session()->get('isLoggedIn')): ?>
                <div class="mt-4">
                    <a href="<?= site_url('/register') ?>" class="btn btn-primary me-2">Register</a>
                    <a href="<?= site_url('/login') ?>" class="btn btn-outline-primary">Login</a>
                </div>
            <?php else: ?>
                <div class="mt-4">
                    <h3>Welcome back, <?= session()->get('name') ?>!</h3>
                    <a href="<?= site_url('/dashboard') ?>" class="btn btn-success">Go to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
  
<?= $this->endSection() ?>
