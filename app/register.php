<?php
require_once '../config/common.php';

use classes\Auth;

$title = 'Sign Up';
$auth = new Auth();

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Format email tidak valid';
    } else {
        // Proses pembuatan akun
        $result = $auth->createAccount($username, $email, $password);
        if ($result === true) {
            // Jika pembuatan akun berhasil, redirect ke halaman login
            header('Location: login.php');
            exit;
        } else {
            // Jika terjadi kesalahan, tampilkan pesan kesalahan
            $errorMessage = $result;
        }
    }
}

include 'templates/header.php';
?>

<section class="h-100">
    <div class="container h-100">
        <div class="row justify-content-sm-center h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-7 col-sm-9">
                <div class="text-center my-5">
                    <img src="https://getbootstrap.com/docs/5.0/assets/brand/bootstrap-logo.svg" alt="logo" width="100">
                </div>
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <!-- alert if error -->
                        <?php if ($errorMessage) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                        <h1 class="fs-4 card-title fw-bold mb-4">Sign Up</h1>
                        <form method="POST" class="needs-validation" novalidate="" autocomplete="off">
                            <div class="mb-3">
                                <label class="mb-2 text-muted" for="username">Username</label>
                                <input id="username" type="text" class="form-control" name="username" value="" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="mb-2 text-muted" for="email">Email</label>
                                <input id="email" type="email" class="form-control" name="email" value="" required>
                            </div>
                            <div class="mb-3">
                                <label class="mb-2 text-muted" for="password">Password</label>
                                <input id="password" type="password" class="form-control" name="password" required>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="submit" class="btn btn-primary ms-auto">Sign Up</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer py-3 border-0">
                        <p>Already have an account? <a href="login.php">Login here</a>.</p>
                    </div>
                </div>
                <div class="text-center mt-5 text-muted">
                    Copyright &copy; <?php echo date('Y'); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'templates/footer.php'; ?>