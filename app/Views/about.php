<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <h1>About Us</h1>
    <p>This is the About page of the application.</p>

    <a href="<?= base_url('/') ?>" class="btn btn-primary">Home</a>
    <a href="<?= base_url('about') ?>" class="btn btn-secondary">About</a>
    <a href="<?= base_url('contact') ?>" class="btn btn-success">Contact</a>
<?= $this->endSection() ?>
