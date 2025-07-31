<?php require_once 'views/templates/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header text-center">
                <h2 class="h4 mb-0">
                    <i class="bi bi-person-plus me-2"></i>Create Admin Account
                </h2>
                <p class="text-light mb-0 mt-2">Set up your first administrator account</p>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Welcome!</strong> Create the first administrator account to get started.
                    This page will be disabled after the first user is created.
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Could not create the account. Please check your inputs and try again.
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_PATH; ?>/admin/store" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       placeholder="Enter first name">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       placeholder="Enter last name">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" required
                                   placeholder="admin@yourcompany.com">
                        </div>
                        <div class="form-text">This will be your login email address.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required
                                   placeholder="Create a strong password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="passwordHelp" class="form-text">
                            Password must be at least 8 characters long and contain a mix of letters, numbers, and symbols.
                        </div>
                        <div class="password-strength mt-2">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar" id="passwordStrengthBar" style="width: 0%"></div>
                            </div>
                            <small id="passwordStrengthText" class="text-muted"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                   placeholder="Confirm your password">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="accept_terms" required>
                            <label class="form-check-label" for="accept_terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a>
                                and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                <span class="text-danger">*</span>
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="createAccountBtn">
                            <i class="bi bi-check-circle me-2"></i>Create Admin Account
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- System Features -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-gear me-1"></i>What you'll get access to:
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle text-success me-2"></i>Dynamic Form Builder</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Company Branding Settings</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Email Template Customization</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle text-success me-2"></i>Booking Management</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Analytics Dashboard</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>User Management</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms of Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Acceptance of Terms</h6>
                <p>By using this booking management system, you agree to these terms of service.</p>

                <h6>2. User Responsibilities</h6>
                <p>As an administrator, you are responsible for:</p>
                <ul>
                    <li>Maintaining the security of your account credentials</li>
                    <li>Managing user data in compliance with privacy regulations</li>
                    <li>Ensuring appropriate use of the system by all users</li>
                </ul>

                <h6>3. Data Security</h6>
                <p>We implement industry-standard security measures to protect your data, including encryption and secure access controls.</p>

                <h6>4. System Availability</h6>
                <p>While we strive for 99.9% uptime, we cannot guarantee uninterrupted service due to maintenance, updates, or unforeseen circumstances.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Information We Collect</h6>
                <p>We collect information necessary to provide booking management services, including:</p>
                <ul>
                    <li>Account information (name, email)</li>
                    <li>Booking details and preferences</li>
                    <li>System usage analytics</li>
                </ul>

                <h6>How We Use Your Information</h6>
                <p>Your information is used to:</p>
                <ul>
                    <li>Provide and improve our services</li>
                    <li>Send important system notifications</li>
                    <li>Ensure system security and prevent abuse</li>
                </ul>

                <h6>Data Protection</h6>
                <p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction.</p>

                <h6>Data Retention</h6>
                <p>We retain your data only as long as necessary to provide our services and comply with legal obligations.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .password-strength .progress {
        background-color: #e9ecef;
    }

    .password-strength .progress-bar {
        transition: all 0.3s ease;
    }

    .password-weak { background-color: #dc3545; }
    .password-fair { background-color: #ffc107; }
    .password-good { background-color: #28a745; }
    .password-strong { background-color: #198754; }

    .input-group-text {
        background: rgba(255,255,255,0.1);
        border-color: #e9ecef;
        color: var(--primary-color);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const togglePassword = document.getElementById('togglePassword');
        const acceptTerms = document.getElementById('accept_terms');

        // Password toggle functionality
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });

        // Password strength checker
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');

            let strength = 0;
            let strengthLabel = '';

            // Length check
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 10;

            // Character variety checks
            if (/[a-z]/.test(password)) strength += 15;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;

            // Set color and label based on strength
            strengthBar.className = 'progress-bar';
            if (strength < 30) {
                strengthBar.classList.add('password-weak');
                strengthLabel = 'Weak';
            } else if (strength < 60) {
                strengthBar.classList.add('password-fair');
                strengthLabel = 'Fair';
            } else if (strength < 90) {
                strengthBar.classList.add('password-good');
                strengthLabel = 'Good';
            } else {
                strengthBar.classList.add('password-strong');
                strengthLabel = 'Strong';
            }

            strengthBar.style.width = strength + '%';
            strengthText.textContent = password.length > 0 ? `Password strength: ${strengthLabel}` : '';
        });

        // Password confirmation check
        function checkPasswordMatch() {
            if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
                confirmPasswordInput.classList.add('is-invalid');
            } else {
                confirmPasswordInput.setCustomValidity('');
                confirmPasswordInput.classList.remove('is-invalid');
                if (confirmPasswordInput.value) {
                    confirmPasswordInput.classList.add('is-valid');
                }
            }
        }

        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        // Email validation
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !/\S+@\S+\.\S+/.test(email)) {
                this.setCustomValidity('Please enter a valid email address');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                if (email) this.classList.add('is-valid');
            }
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Validate all required fields
            const requiredFields = [emailInput, passwordInput, confirmPasswordInput];
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });

            // Check password strength
            if (passwordInput.value.length < 8) {
                passwordInput.classList.add('is-invalid');
                isValid = false;
            }

            // Check password match
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.classList.add('is-invalid');
                isValid = false;
            }

            // Check terms acceptance
            if (!acceptTerms.checked) {
                acceptTerms.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();

                // Focus first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                // Show loading state
                const submitBtn = document.getElementById('createAccountBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
            }
        });

        // Auto-focus first field
        document.getElementById('first_name').focus();
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>
