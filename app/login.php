<?php

require_once '../config/common.php';

use classes\Auth;

$title = 'Login';
$auth = new Auth();

if ($auth->isLogin()) {
    header('Location: ../index.php');
    exit;
}

$errorMessage = '';

if (isset($_POST['username']) && isset($_POST['password'])) {
    if ($auth->doLogin($_POST['username'], $_POST['password'])) {
        header('Location: index.php');
        exit;
    } else {
        $errorMessage = 'Username atau password salah. Silakan coba lagi.';
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
                        <h1 class="fs-4 card-title fw-bold mb-4">Login</h1>
                        <form method="POST" class="needs-validation" novalidate="" autocomplete="off">
                            <div class="mb-3">
                                <label class="mb-2 text-muted" for="username">Username</label>
                                <input id="username" type="text" class="form-control" name="username" value="" required autofocus>

                            </div>

                            <div class="mb-3">
                                <label class="mb-2 text-muted" for="password">Password</label>
                                <input id="password" type="password" class="form-control" name="password" required>

                            </div>

                            <div class="d-flex align-items-center">
                                <button type="submit" class="btn btn-primary ms-auto">Login</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer py-3 border-0">

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