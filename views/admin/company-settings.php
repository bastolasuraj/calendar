<?php require_once 'views/templates/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-gear me-2"></i>Company Settings
                </h1>
                <p class="text-muted mb-0">Manage your organization's branding, configuration, and preferences</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-1"></i>Import Settings
                </button>
                <a href="<?php echo BASE_PATH; ?>/admin/company/exportSettings" class="btn btn-outline-primary">
                    <i class="bi bi-download me-1"></i>Export Settings
                </a>
                <button type="button" class="btn btn-outline-warning" onclick="resetSettings()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Reset to Defaults
                </button>
            </div>
        </div>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" id="settingsForm">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <div class="row">
        <!-- Main Settings -->
        <div class="col-lg-8">

            <!-- Company Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-building me-2"></i>Company Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company_name" name="company_name"
                                   value="<?php echo htmlspecialchars($settings['company_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_website" class="form-label">Website URL</label>
                            <input type="url" class="form-control" id="company_website" name="company_website"
                                   value="<?php echo htmlspecialchars($settings['company_website']); ?>"
                                   placeholder="https://yourcompany.com">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="form-label">Contact Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email"
                                   value="<?php echo htmlspecialchars($settings['contact_email']); ?>" required>
                            <div class="form-text">This will be used as the sender address for notifications</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="contact_phone" name="contact_phone"
                                   value="<?php echo htmlspecialchars($settings['contact_phone']); ?>"
                                   placeholder="+1 (555) 123-4567">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"
                                  placeholder="Enter your company's full address"><?php echo htmlspecialchars($settings['address']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select" id="timezone" name="timezone">
                                <?php
                                $timezones = [
                                    'America/New_York' => 'Eastern Time (ET)',
                                    'America/Chicago' => 'Central Time (CT)',
                                    'America/Denver' => 'Mountain Time (MT)',
                                    'America/Los_Angeles' => 'Pacific Time (PT)',
                                    'America/Toronto' => 'Eastern Time - Toronto',
                                    'America/Vancouver' => 'Pacific Time - Vancouver',
                                    'UTC' => 'Coordinated Universal Time (UTC)'
                                ];
                                foreach ($timezones as $value => $label):
                                    ?>
                                    <option value="<?php echo $value; ?>" <?php echo $settings['timezone'] === $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                                <option value="CAD" <?php echo $settings['currency'] === 'CAD' ? 'selected' : ''; ?>>CAD - Canadian Dollar</option>
                                <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                                <option value="GBP" <?php echo $settings['currency'] === 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-palette me-2"></i>Branding & Appearance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_logo" class="form-label">Company Logo</label>
                            <input type="file" class="form-control" id="company_logo" name="company_logo"
                                   accept="image/*">
                            <div class="form-text">Upload PNG, JPG, GIF, or SVG. Max size: 2MB</div>
                            <?php if (!empty($settings['company_logo'])): ?>
                                <div class="mt-2">
                                    <img src="<?php echo htmlspecialchars($settings['company_logo']); ?>"
                                         alt="Current Logo" class="img-thumbnail" style="max-height: 60px;">
                                    <div class="form-text">Current logo</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="font_family" class="form-label">Font Family</label>
                            <select class="form-select" id="font_family" name="font_family">
                                <?php
                                $fonts = [
                                    'Inter, sans-serif' => 'Inter (Recommended)',
                                    'Arial, sans-serif' => 'Arial',
                                    'Helvetica, sans-serif' => 'Helvetica',
                                    'Georgia, serif' => 'Georgia',
                                    'Times New Roman, serif' => 'Times New Roman',
                                    'Roboto, sans-serif' => 'Roboto',
                                    'Open Sans, sans-serif' => 'Open Sans'
                                ];
                                foreach ($fonts as $value => $label):
                                    ?>
                                    <option value="<?php echo $value; ?>" <?php echo $settings['font_family'] === $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="primary_color" class="form-label">Primary Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="primary_color"
                                       name="primary_color" value="<?php echo htmlspecialchars($settings['primary_color']); ?>">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['primary_color']); ?>"
                                       readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="secondary_color" class="form-label">Secondary Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="secondary_color"
                                       name="secondary_color" value="<?php echo htmlspecialchars($settings['secondary_color']); ?>">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['secondary_color']); ?>"
                                       readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="accent_color" class="form-label">Accent Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="accent_color"
                                       name="accent_color" value="<?php echo htmlspecialchars($settings['accent_color']); ?>">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['accent_color']); ?>"
                                       readonly>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Color Preview</h6>
                            <div class="d-flex gap-3 align-items-center">
                                <div class="color-preview" style="width: 60px; height: 40px; border-radius: 8px; background: var(--primary-color);"></div>
                                <span>Primary</span>
                                <div class="color-preview" style="width: 60px; height: 40px; border-radius: 8px; background: var(--secondary-color);"></div>
                                <span>Secondary</span>
                                <div class="color-preview" style="width: 60px; height: 40px; border-radius: 8px; background: var(--accent-color);"></div>
                                <span>Accent</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Working Hours & Booking Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock me-2"></i>Working Hours & Booking Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="working_hours_start" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="working_hours_start" name="working_hours_start"
                                   value="<?php echo htmlspecialchars($settings['working_hours_start']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="working_hours_end" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="working_hours_end" name="working_hours_end"
                                   value="<?php echo htmlspecialchars($settings['working_hours_end']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="slot_duration" class="form-label">Slot Duration (minutes)</label>
                            <select class="form-select" id="slot_duration" name="slot_duration">
                                <?php
                                $durations = [30 => '30 minutes', 60 => '1 hour', 90 => '1.5 hours', 120 => '2 hours', 180 => '3 hours'];
                                foreach ($durations as $value => $label):
                                    ?>
                                    <option value="<?php echo $value; ?>" <?php echo $settings['slot_duration'] == $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="max_advance_booking_days" class="form-label">Max Advance Booking (days)</label>
                            <input type="number" class="form-control" id="max_advance_booking_days" name="max_advance_booking_days"
                                   value="<?php echo htmlspecialchars($settings['max_advance_booking_days']); ?>" min="1" max="365">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="min_advance_booking_hours" class="form-label">Min Advance Booking (hours)</label>
                            <input type="number" class="form-control" id="min_advance_booking_hours" name="min_advance_booking_hours"
                                   value="<?php echo htmlspecialchars($settings['min_advance_booking_hours']); ?>" min="0" max="72">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cancellation_hours_before" class="form-label">Cancellation Deadline (hours)</label>
                            <input type="number" class="form-control" id="cancellation_hours_before" name="cancellation_hours_before"
                                   value="<?php echo htmlspecialchars($settings['cancellation_hours_before']); ?>" min="0" max="72">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_concurrent_bookings" class="form-label">Max Concurrent Bookings</label>
                            <input type="number" class="form-control" id="max_concurrent_bookings" name="max_concurrent_bookings"
                                   value="<?php echo htmlspecialchars($settings['max_concurrent_bookings']); ?>" min="1" max="10">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="booking_buffer_minutes" class="form-label">Booking Buffer (minutes)</label>
                            <input type="number" class="form-control" id="booking_buffer_minutes" name="booking_buffer_minutes"
                                   value="<?php echo htmlspecialchars($settings['booking_buffer_minutes']); ?>" min="0" max="60">
                            <div class="form-text">Buffer time between consecutive bookings</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-envelope me-2"></i>Email Notifications
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="send_confirmation_emails"
                                           name="send_confirmation_emails" <?php echo $settings['send_confirmation_emails'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="send_confirmation_emails">
                                        Send Confirmation Emails
                                    </label>
                                </div>
                                <div class="form-text">Automatically send booking confirmations to customers</div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="send_reminder_emails"
                                           name="send_reminder_emails" <?php echo $settings['send_reminder_emails'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="send_reminder_emails">
                                        Send Reminder Emails
                                    </label>
                                </div>
                                <div class="form-text">Send reminders before scheduled bookings</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reminder_hours_before" class="form-label">Reminder Time (hours before)</label>
                                <select class="form-select" id="reminder_hours_before" name="reminder_hours_before">
                                    <?php
                                    $reminderHours = [1 => '1 hour', 2 => '2 hours', 4 => '4 hours', 8 => '8 hours', 24 => '24 hours', 48 => '48 hours'];
                                    foreach ($reminderHours as $value => $label):
                                        ?>
                                        <option value="<?php echo $value; ?>" <?php echo $settings['reminder_hours_before'] == $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-file-text me-2"></i>Terms and Conditions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="booking_terms" class="form-label">Booking Terms</label>
                        <textarea class="form-control" id="booking_terms" name="booking_terms" rows="8"
                                  placeholder="Enter your booking terms and conditions..."><?php echo htmlspecialchars($settings['booking_terms']); ?></textarea>
                        <div class="form-text">These terms will be displayed to customers during the booking process</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">

            <!-- Booking Preferences -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-toggles me-2"></i>Booking Preferences
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_approve_bookings"
                                   name="auto_approve_bookings" <?php echo $settings['auto_approve_bookings'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="auto_approve_bookings">
                                Auto-Approve Bookings
                            </label>
                        </div>
                        <div class="form-text">Automatically approve new bookings without admin review</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="require_approval"
                                   name="require_approval" <?php echo $settings['require_approval'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="require_approval">
                                Require Admin Approval
                            </label>
                        </div>
                        <div class="form-text">All bookings must be approved by an administrator</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_public_calendar"
                                   name="enable_public_calendar" <?php echo $settings['enable_public_calendar'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_public_calendar">
                                Enable Public Calendar
                            </label>
                        </div>
                        <div class="form-text">Show approved bookings on public calendar</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_user_updates"
                                   name="enable_user_updates" <?php echo $settings['enable_user_updates'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_user_updates">
                                Allow User Updates
                            </label>
                        </div>
                        <div class="form-text">Customers can modify their own bookings</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_user_cancellations"
                                   name="enable_user_cancellations" <?php echo $settings['enable_user_cancellations'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_user_cancellations">
                                Allow User Cancellations
                            </label>
                        </div>
                        <div class="form-text">Customers can cancel their own bookings</div>
                    </div>
                </div>
            </div>

            <!-- Email Testing -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-envelope-check me-2"></i>Email Testing
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Test your email configuration to ensure notifications are working properly.</p>

                    <div class="mb-3">
                        <label for="test_email" class="form-label">Test Email Address</label>
                        <input type="email" class="form-control" id="test_email" placeholder="your@email.com">
                    </div>

                    <div class="mb-3">
                        <label for="test_template" class="form-label">Email Template</label>
                        <select class="form-select" id="test_template">
                            <option value="test_config">Configuration Test</option>
                            <option value="booking_confirmation">Booking Confirmation</option>
                            <option value="booking_approved">Booking Approved</option>
                            <option value="booking_rejected">Booking Rejected</option>
                            <option value="booking_updated">Booking Updated</option>
                            <option value="booking_reminder">Booking Reminder</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-outline-primary w-100" onclick="sendTestEmail()">
                        <i class="bi bi-send me-1"></i>Send Test Email
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_PATH; ?>/admin/company/email-templates" class="btn btn-outline-primary">
                            <i class="bi bi-envelope-paper me-1"></i>Manage Email Templates
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/admin/form-builder" class="btn btn-outline-success">
                            <i class="bi bi-ui-checks me-1"></i>Form Builder
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/admin/analytics" class="btn btn-outline-info">
                            <i class="bi bi-graph-up me-1"></i>View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Changes will take effect immediately after saving
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Cancel Changes
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-1"></i>Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Import Settings Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>Import Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/admin/company/importSettings" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will overwrite your current settings. Make sure to export your current settings first as a backup.
                    </div>

                    <div class="mb-3">
                        <label for="settings_file" class="form-label">Settings File</label>
                        <input type="file" class="form-control" id="settings_file" name="settings_file"
                               accept=".json" required>
                        <div class="form-text">Select a JSON file exported from the company settings.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Import Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-control-color {
        width: 60px;
        height: 38px;
        padding: 0.375rem;
    }

    .color-preview {
        border: 2px solid #dee2e6;
        display: inline-block;
    }

    .form-switch .form-check-input {
        width: 2.5em;
        height: 1.25em;
    }

    .card-header h5 {
        color: var(--primary-color);
    }

    /* Real-time color updates */
    :root {
        --primary-color: <?php echo $settings['primary_color']; ?>;
        --secondary-color: <?php echo $settings['secondary_color']; ?>;
        --accent-color: <?php echo $settings['accent_color']; ?>;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Color picker functionality
        const colorInputs = document.querySelectorAll('input[type="color"]');
        colorInputs.forEach(input => {
            input.addEventListener('change', function() {
                const textInput = this.parentNode.querySelector('input[type="text"]');
                textInput.value = this.value;

                // Update CSS custom properties for real-time preview
                if (this.id === 'primary_color') {
                    document.documentElement.style.setProperty('--primary-color', this.value);
                } else if (this.id === 'secondary_color') {
                    document.documentElement.style.setProperty('--secondary-color', this.value);
                } else if (this.id === 'accent_color') {
                    document.documentElement.style.setProperty('--accent-color', this.value);
                }

                updateColorPreviews();
            });
        });

        function updateColorPreviews() {
            const primaryColor = document.getElementById('primary_color').value;
            const secondaryColor = document.getElementById('secondary_color').value;
            const accentColor = document.getElementById('accent_color').value;

            const previews = document.querySelectorAll('.color-preview');
            previews[0].style.background = primaryColor;
            previews[1].style.background = secondaryColor;
            previews[2].style.background = accentColor;
        }

        // Initialize color previews
        updateColorPreviews();

        // Form validation
        const form = document.getElementById('settingsForm');
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Validate required fields
            const requiredFields = ['company_name', 'contact_email'];
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Validate email format
            const emailField = document.getElementById('contact_email');
            if (emailField.value && !/\S+@\S+\.\S+/.test(emailField.value)) {
                emailField.classList.add('is-invalid');
                isValid = false;
            }

            // Validate time range
            const startTime = document.getElementById('working_hours_start').value;
            const endTime = document.getElementById('working_hours_end').value;
            if (startTime && endTime && startTime >= endTime) {
                document.getElementById('working_hours_end').classList.add('is-invalid');
                showToast('End time must be after start time', 'error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }
        });

        // Auto-save draft functionality
        const autoSaveInterval = 30000; // 30 seconds
        let autoSaveTimer;

        function autoSave() {
            const formData = new FormData(form);
            // Store in localStorage as draft
            const draftData = {};
            for (let [key, value] of formData.entries()) {
                draftData[key] = value;
            }
            localStorage.setItem('settings_draft', JSON.stringify(draftData));
            console.log('Settings auto-saved to draft');
        }

        // Set up auto-save
        form.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSave, autoSaveInterval);
        });

        // Load draft on page load
        const savedDraft = localStorage.getItem('settings_draft');
        if (savedDraft && confirm('Would you like to restore your unsaved changes?')) {
            const draftData = JSON.parse(savedDraft);
            Object.keys(draftData).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field && field.type !== 'file') {
                    if (field.type === 'checkbox') {
                        field.checked = draftData[key] === 'on';
                    } else {
                        field.value = draftData[key];
                    }
                }
            });
            updateColorPreviews();
        }

        // Clear draft on successful save
        form.addEventListener('submit', function() {
            localStorage.removeItem('settings_draft');
        });
    });

    function sendTestEmail() {
        const testEmail = document.getElementById('test_email').value;
        const templateType = document.getElementById('test_template').value;

        if (!testEmail) {
            showToast('Please enter a test email address', 'error');
            return;
        }

        if (!/\S+@\S+\.\S+/.test(testEmail)) {
            showToast('Please enter a valid email address', 'error');
            return;
        }

        // Show loading state
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';

        // Send test email
        fetch(`${BASE_PATH_JS}/admin/company/testEmail`, { // Fixed this URL
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `test_email=${encodeURIComponent(testEmail)}&template_type=${templateType}`
        })
            .then(response => {
                if (response.ok) {
                    showToast('Test email sent successfully!', 'success');
                } else {
                    throw new Error('Failed to send test email');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to send test email. Please check your email configuration.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    }

    function resetSettings() {
        if (confirm('Are you sure you want to reset all settings to default values? This action cannot be undone.')) {
            window.location.href = `${BASE_PATH_JS}/admin/company/resetSettings`; // Fixed this URL
        }
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        document.body.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }
</script>

<?php require_once 'views/templates/footer.php'; ?>
