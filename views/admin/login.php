<?php require_once 'views/templates/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header text-center">
                <h2 class="h4 mb-0">
                    <i class="bi bi-shield-lock me-2"></i>Admin Login
                </h2>
                <p class="text-light mb-0 mt-2">Access your admin dashboard</p>
            </div>
            <div class="card-body p-4">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php
                        switch ($_GET['error']) {
                            case '1':
                                echo 'Invalid email or password. Please try again.';
                                break;
                            case 'permissions':
                                echo 'You do not have permission to access the admin area.';
                                break;
                            case 'inactive':
                                echo 'Your account is inactive. Please contact an administrator.';
                                break;
                            default:
                                echo 'An error occurred. Please try again.';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        Registration successful! You can now log in with your credentials.
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_PATH; ?>/admin/authenticate" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" required
                                   placeholder="admin@example.com"
                                   value="<?php echo isset($_GET['demo']) ? 'admin@demo.com' : ''; ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required
                                   placeholder="Enter your password"
                                   value="<?php echo isset($_GET['demo']) ? 'AdminDemo123!' : ''; ?>">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Remember me
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </div>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>Demo Credentials
                    </h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">Super Admin</h6>
                                    <p class="card-text small mb-2">
                                        <strong>Email:</strong> admin@demo.com<br>
                                        <strong>Password:</strong> AdminDemo123!
                                    </p>
                                    <a href="<?php echo BASE_PATH; ?>/admin/login?demo=super" class="btn btn-sm btn-outline-primary">
                                        Use Demo Login
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">Manager</h6>
                                    <p class="card-text small mb-2">
                                        <strong>Email:</strong> manager@demo.com<br>
                                        <strong>Password:</strong> ManagerDemo123!
                                    </p>
                                    <a href="<?php echo BASE_PATH; ?>/admin/login?demo=manager" class="btn btn-sm btn-outline-success">
                                        Use Demo Login
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Demo accounts have pre-configured permissions and sample data
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Overview -->
        <div class="card mt-4">
            <div class="card-body text-center">
                <h6 class="card-title">
                    <i class="bi bi-star me-1"></i>Admin Features
                </h6>
                <div class="row text-center">
                    <div class="col-4">
                        <i class="bi bi-ui-checks fs-3 text-primary d-block mb-1"></i>
                        <small>Form Builder</small>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-gear fs-3 text-success d-block mb-1"></i>
                        <small>Settings</small>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-graph-up fs-3 text-info d-block mb-1"></i>
                        <small>Analytics</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .input-group-text {
        background: rgba(255,255,255,0.1);
        border-color: #e9ecef;
        color: var(--primary-color);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    }

    .demo-login {
        transition: all 0.3s ease;
    }

    .demo-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });

        // Form validation
        const form = document.querySelector('form');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Reset previous validation styles
            [emailInput, passwordInput].forEach(input => {
                input.classList.remove('is-invalid');
            });

            // Validate email
            if (!emailInput.value.trim()) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else if (!/\S+@\S+\.\S+/.test(emailInput.value)) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            }

            // Validate password
            if (!passwordInput.value.trim()) {
                passwordInput.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();

                // Focus first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            } else {
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing In...';
            }
        });

        // Demo login auto-fill
        const urlParams = new URLSearchParams(window.location.search);
        const demo = urlParams.get('demo');

        if (demo === 'manager') {
            emailInput.value = 'manager@demo.com';
            passwordInput.value = 'ManagerDemo123!';
        }

        // Auto-focus email field
        emailInput.focus();
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>
