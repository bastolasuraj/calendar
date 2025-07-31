<?php require_once 'views/templates/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white text-center py-4">
                <div class="mb-2">
                    <i class="fas fa-calendar-plus fa-3x"></i>
                </div>
                <h2 class="h3 mb-0"><?php echo htmlspecialchars($companyName); ?></h2>
                <p class="mb-0 opacity-75"><?php echo htmlspecialchars($companyDescription); ?></p>
            </div>
            <div class="card-body p-4">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger d-flex align-items-center fade-in">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <?php
                            $errorMessages = [
                                '1' => 'Booking submission failed. Please check your information and try again.',
                                'no_form_config' => 'Form configuration not available. Please contact support.',
                                'missing_datetime' => 'Please select a date and time for your booking.'
                            ];
                            echo $errorMessages[$_GET['error']] ?? 'An error occurred. Please try again.';
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_PATH; ?>/book/create" method="POST" id="bookingForm" data-validate="true" class="fade-in">
                    <div class="row">
                        <!-- Dynamic Form Fields -->
                        <div class="col-lg-8 mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-user-edit me-2"></i>Your Information
                            </h5>

                            <?php if ($activeForm && isset($activeForm['fields'])): ?>
                                <?php
                                // Sort fields by order
                                $fields = $activeForm['fields'];
                                usort($fields, function($a, $b) {
                                    return ($a['order'] ?? 0) - ($b['order'] ?? 0);
                                });

                                foreach ($fields as $field):
                                    $fieldId = 'field_' . $field['name'];
                                    $isRequired = $field['required'] ?? false;
                                    ?>
                                    <div class="mb-3">
                                        <label for="<?php echo $fieldId; ?>" class="form-label">
                                            <?php echo htmlspecialchars($field['label']); ?>
                                            <?php if ($isRequired): ?>
                                                <span class="text-danger">*</span>
                                            <?php endif; ?>
                                        </label>

                                        <?php switch ($field['type']):
                                            case 'text':
                                            case 'email':
                                            case 'phone':
                                            case 'number': ?>
                                                <input type="<?php echo $field['type']; ?>"
                                                       class="form-control"
                                                       id="<?php echo $fieldId; ?>"
                                                       name="<?php echo htmlspecialchars($field['name']); ?>"
                                                       placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>"
                                                    <?php echo $isRequired ? 'required' : ''; ?>
                                                    <?php if (isset($field['validation']['min_length'])): ?>
                                                        minlength="<?php echo $field['validation']['min_length']; ?>"
                                                    <?php endif; ?>
                                                    <?php if (isset($field['validation']['max_length'])): ?>
                                                        maxlength="<?php echo htmlspecialchars($field['validation']['max_length']); ?>"
                                                    <?php endif; ?>
                                                    <?php if (isset($field['validation']['pattern'])): ?>
                                                        pattern="<?php echo htmlspecialchars($field['validation']['pattern']); ?>"
                                                    <?php endif; ?>>
                                                <?php break; ?>

                                            <?php case 'textarea': ?>
                                                <textarea class="form-control"
                                                          id="<?php echo $fieldId; ?>"
                                                          name="<?php echo htmlspecialchars($field['name']); ?>"
                                                          rows="3"
                                                          placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>"
                                                      <?php echo $isRequired ? 'required' : ''; ?>
                                                    <?php if (isset($field['validation']['max_length'])): ?>
                                                        maxlength="<?php echo htmlspecialchars($field['validation']['max_length']); ?>"
                                                    <?php endif; ?>></textarea>
                                                <?php break; ?>

                                            <?php case 'select': ?>
                                                <select class="form-select"
                                                        id="<?php echo $fieldId; ?>"
                                                        name="<?php echo htmlspecialchars($field['name']); ?>"
                                                    <?php echo $isRequired ? 'required' : ''; ?>>
                                                    <option value="">Choose...</option>
                                                    <?php if (isset($field['options'])): ?>
                                                        <?php foreach ($field['options'] as $value => $label): ?>
                                                            <option value="<?php echo htmlspecialchars($value); ?>">
                                                                <?php echo htmlspecialchars($label); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                                <?php break; ?>

                                            <?php case 'radio': ?>
                                                <?php if (isset($field['options'])): ?>
                                                    <?php foreach ($field['options'] as $value => $label): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input"
                                                                   type="radio"
                                                                   name="<?php echo htmlspecialchars($field['name']); ?>"
                                                                   id="<?php echo $fieldId . '_' . $value; ?>"
                                                                   value="<?php echo htmlspecialchars($value); ?>"
                                                                <?php echo $isRequired ? 'required' : ''; ?>>
                                                            <label class="form-check-label" for="<?php echo $fieldId . '_' . $value; ?>">
                                                                <?php echo htmlspecialchars($label); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <?php break; ?>

                                            <?php case 'checkbox': ?>
                                                <?php if (isset($field['options'])): ?>
                                                    <?php foreach ($field['options'] as $value => $label): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input"
                                                                   type="checkbox"
                                                                   name="<?php echo htmlspecialchars($field['name']); ?>[]"
                                                                   id="<?php echo $fieldId . '_' . $value; ?>"
                                                                   value="<?php echo htmlspecialchars($value); ?>">
                                                            <label class="form-check-label" for="<?php echo $fieldId . '_' . $value; ?>">
                                                                <?php echo htmlspecialchars($label); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <?php break; ?>

                                            <?php case 'file': ?>
                                                <input type="file"
                                                       class="form-control"
                                                       id="<?php echo $fieldId; ?>"
                                                       name="<?php echo htmlspecialchars($field['name']); ?>"
                                                    <?php echo $isRequired ? 'required' : ''; ?>
                                                    <?php if (isset($field['validation']['accept'])): ?>
                                                        accept="<?php echo htmlspecialchars($field['validation']['accept']); ?>"
                                                    <?php endif; ?>>
                                                <?php break; ?>

                                            <?php case 'date': ?>
                                                <input type="date"
                                                       class="form-control"
                                                       id="<?php echo $fieldId; ?>"
                                                       name="<?php echo htmlspecialchars($field['name']); ?>"
                                                    <?php echo $isRequired ? 'required' : ''; ?>
                                                    <?php if (isset($field['validation']['min'])): ?>
                                                        min="<?php echo htmlspecialchars($field['validation']['min']); ?>"
                                                    <?php endif; ?>
                                                    <?php if (isset($field['validation']['max'])): ?>
                                                        max="<?php echo htmlspecialchars($field['validation']['max']); ?>"
                                                    <?php endif; ?>>
                                                <?php break; ?>

                                            <?php default: ?>
                                                <input type="text"
                                                       class="form-control"
                                                       id="<?php echo $fieldId; ?>"
                                                       name="<?php echo htmlspecialchars($field['name']); ?>"
                                                       placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>"
                                                    <?php echo $isRequired ? 'required' : ''; ?>>
                                                <?php break; ?>
                                            <?php endswitch; ?>

                                        <div class="invalid-feedback"></div>

                                        <?php if (!empty($field['validation']['max_length'])): ?>
                                            <div class="form-text">Maximum <?php echo htmlspecialchars($field['validation']['max_length']); ?> characters</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No form configuration available. Please contact the administrator.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Calendar and Time Selection -->
                        <div class="col-lg-4">
                            <h5 class="mb-3">
                                <i class="fas fa-calendar-alt me-2"></i>Select Date & Time
                            </h5>

                            <!-- Calendar Container -->
                            <div id="calendar-container" class="mb-4"></div>

                            <!-- Time Slots -->
                            <div id="time-slots-wrapper" style="display: none;">
                                <h6 id="selected-date-display" class="mb-2"></h6>
                                <div id="time-slots" class="mb-3"></div>
                                <div id="no-slots-message" class="alert alert-warning" style="display: none;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No available time slots for this date.
                                </div>
                            </div>

                            <!-- Hidden datetime input -->
                            <input type="hidden" id="booking_datetime" name="booking_datetime" required>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn" disabled>
                            <i class="fas fa-paper-plane me-2"></i>Submit Booking Request
                        </button>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                You will receive a confirmation email with your booking details
                            </small>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced form validation
        const form = document.getElementById('bookingForm');
        const submitBtn = document.getElementById('submitBtn');

        // Real-time validation
        form.addEventListener('input', function(e) {
            validateField(e.target);
            checkFormValidity();
        });

        form.addEventListener('change', function(e) {
            validateField(e.target);
            checkFormValidity();
        });

        function validateField(field) {
            const value = field.value.trim();
            const isRequired = field.hasAttribute('required');
            const fieldContainer = field.closest('.mb-3');
            const feedback = fieldContainer.querySelector('.invalid-feedback');

            // Reset validation state
            field.classList.remove('is-valid', 'is-invalid');

            // Required field validation
            if (isRequired && !value) {
                setFieldError(field, feedback, 'This field is required.');
                return false;
            }

            // Type-specific validation
            if (value) {
                switch (field.type) {
                    case 'email':
                        if (!isValidEmail(value)) {
                            setFieldError(field, feedback, 'Please enter a valid email address.');
                            return false;
                        }
                        break;

                    case 'tel':
                        if (!isValidPhone(value)) {
                            setFieldError(field, feedback, 'Please enter a valid phone number.');
                            return false;
                        }
                        break;

                    case 'number':
                        if (isNaN(value)) {
                            setFieldError(field, feedback, 'Please enter a valid number.');
                            return false;
                        }
                        break;
                }

                // Length validation
                const minLength = field.getAttribute('minlength');
                const maxLength = field.getAttribute('maxlength');

                if (minLength && value.length < parseInt(minLength)) {
                    setFieldError(field, feedback, `Minimum ${minLength} characters required.`);
                    return false;
                }

                if (maxLength && value.length > parseInt(maxLength)) {
                    setFieldError(field, feedback, `Maximum ${maxLength} characters allowed.`);
                    return false;
                }

                // Pattern validation
                const pattern = field.getAttribute('pattern');
                if (pattern && !new RegExp(pattern).test(value)) {
                    setFieldError(field, feedback, 'Please enter a valid format.');
                    return false;
                }
            }

            // Field is valid
            field.classList.add('is-valid');
            if (feedback) feedback.style.display = 'none';
            return true;
        }

        function setFieldError(field, feedback, message) {
            field.classList.add('is-invalid');
            if (feedback) {
                feedback.textContent = message;
                feedback.style.display = 'block';
            }
        }

        function checkFormValidity() {
            const requiredFields = form.querySelectorAll('[required]');
            const dateTimeField = document.getElementById('booking_datetime');
            let allValid = true;

            // Check all required fields
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    allValid = false;
                }
            });

            // Check datetime selection
            if (!dateTimeField.value) {
                allValid = false;
            }

            submitBtn.disabled = !allValid;
        }

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function isValidPhone(phone) {
            // Remove all non-digit characters
            const cleaned = phone.replace(/\D/g, '');
            // Check if it's a valid length (10-15 digits)
            return cleaned.length >= 10 && cleaned.length <= 15;
        }

        // Form submission handling
        form.addEventListener('submit', function(e) {
            // Final validation
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            if (!document.getElementById('booking_datetime').value) {
                isValid = false;
                alert('Please select a date and time for your booking.');
            }

            if (!isValid) {
                e.preventDefault();
                return;
            }

            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
        });

        // Initialize calendar
        if (window.initializeCalendar) {
            window.initializeCalendar();
        }
    });

    // Phone number formatting
    document.addEventListener('input', function(e) {
        if (e.target.type === 'tel' || e.target.name === 'phone') {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{3})/, '($1) $2');
            }
            e.target.value = value;
        }
    });
</script>

<style>
    .card-header.bg-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 85%, black) 100%) !important;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--primary-color) 25%, transparent);
    }

    .form-control.is-valid {
        border-color: #28a745;
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .btn-primary.btn-lg {
        background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 85%, black) 100%);
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .btn-primary.btn-lg:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .btn-primary.btn-lg:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .invalid-feedback {
        display: none;
    }

    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem !important;
        }

        #calendar-container {
            margin-bottom: 2rem;
        }
    }
</style>

<?php require_once 'views/templates/footer.php'; ?>
