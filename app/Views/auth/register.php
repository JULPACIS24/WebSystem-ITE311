<?= $this->extend('templates/template') ?>
<?= $this->section('content') ?>

<div class="min-vh-75 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h3 text-center mb-4">Register</h2>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($validation)): ?>
                        <div class="alert alert-danger">
                            <?= $validation->listErrors() ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= site_url('/register') ?>">
                        <input type="text" name="name" class="form-control mb-2"
                               placeholder="Full Name" value="<?= set_value('name') ?>" required>

                        <input type="email" name="email" class="form-control mb-2"
                               placeholder="Email" value="<?= set_value('email') ?>" required>

                        <input type="password" name="password" class="form-control mb-2"
                               placeholder="Password" required>

                        <input type="password" name="password_confirm" class="form-control mb-3"
                               placeholder="Confirm Password" required>

                        <button class="btn btn-success w-100">Register</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="<?= site_url('/login') ?>">Already have an account? Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
