<?php require_once 'views/templates/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-success text-white py-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="h3 mb-1">
                            <i class="fas fa-ticket-alt me-2"></i>Booking Details
                        </h2>
                        <p class="mb-0 opacity-75">Access Code: <?php echo htmlspecialchars($booking['access_code']); ?></p>
                    </div>
                    <div class="col-auto">
                        <?php
                        $statusConfig = [
                            'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Under Review'],
                            'approved' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Approved'],
                            'rejected' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Not Approved'],
                            'cancelled' => ['class' => 'secondary', 'icon' => 'ban', 'text' => 'Cancelled']
                        ];
                        $status = $statusConfig[$booking['status']] ?? $statusConfig['pending'];
                        ?>
                        <span class="badge bg-<?php echo $status['class']; ?> fs-6 px-3 py-2">
                            <i class="fas fa-<?php echo $status['icon']; ?> me-1"></i>
                            <?php echo $status['text']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Status Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success d-flex align-items-center fade-in">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>
                            <?php if (isset($_GET['status']) && $_GET['status'] === 'pending'): ?>
                                Your booking has been updated successfully and is now pending review.
                            <?php else: ?>
                                Your booking has been updated successfully.
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger d-flex align-items-center fade-in">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <?php
                            $errorMessages = [
                                'updatefailed' => 'Could not update your booking. The selected time slot may no longer be available.',
                                'cannot_cancel' => 'This booking cannot be cancelled at this time.',
                                'cancel_failed' => 'Failed to cancel the booking. Please try again or contact support.'
                            ];
                            echo $errorMessages[$_GET['error']] ?? 'An error occurred. Please try again.';
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] === 'cancelled'): ?>
                    <div class="alert alert-info d-flex align-items-center fade-in">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>Your booking has been cancelled successfully. You will receive a confirmation email shortly.</div>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Booking Information -->
                    <div class="col-lg-8 mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-info-circle me-2"></i>Booking Information
                        </h5>

                        <div class="booking-info-grid">
                            <!-- Core booking details -->
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>Date & Time
                                </div>
                                <div class="info-value">
                                    <?php
                                    $bookingDateTime = $booking['booking_datetime']->toDateTime();
                                    echo $bookingDateTime->format('l, F j, Y \a\t g:i A');
                                    ?>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-user text-primary me-2"></i>Name
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($booking['name'] ?? 'Not specified'); ?>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-envelope text-primary me-2"></i>Email
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($booking['email'] ?? 'Not specified'); ?>
                                </div>
                            </div>

                            <!-- Dynamic form fields -->
                            <?php if (isset($booking['form_data']) && is_array($booking['form_data'])): ?>
                                <?php foreach ($booking['form_data'] as $fieldName => $fieldValue): ?>
                                    <?php if (!in_array($fieldName, ['name', 'email']) && !empty($fieldValue)): ?>
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="fas fa-tag text-primary me-2"></i>
                                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $fieldName))); ?>
                                            </div>
                                            <div class="info-value">
                                                <?php
                                                if (is_array($fieldValue)) {
                                                    echo htmlspecialchars(implode(', ', $fieldValue));
                                                } else {
                                                    echo htmlspecialchars($fieldValue);
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-clock text-primary me-2"></i>Submitted
                                </div>
                                <div class="info-value">
                                    <?php echo $booking['created_at']->toDateTime()->format('F j, Y \a\t g:i A'); ?>
                                </div>
                            </div>

                            <?php if (isset($booking['updated_at'])): ?>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-edit text-primary me-2"></i>Last Updated
                                    </div>
                                    <div class="info-value">
                                        <?php echo $booking['updated_at']->toDateTime()->format('F j, Y \a\t g:i A'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Status-specific messages -->
                        <?php if ($booking['status'] === 'pending'): ?>
                            <div class="alert alert-info mt-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-hourglass-half fa-2x text-info me-3 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">Under Review</h6>
                                        <p class="mb-2">Your booking request is currently being reviewed by our team. You will receive an email notification once a decision has been made.</p>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Most bookings are reviewed within 24 hours during business days.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($booking['status'] === 'approved'): ?>
                            <div class="alert alert-success mt-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle fa-2x text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">Booking Confirmed!</h6>
                                        <p class="mb-2">Your booking has been approved and confirmed. Please arrive on time and bring valid identification.</p>
                                        <small class="text-success">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            Your session is scheduled for <?php echo $bookingDateTime->format('l, F j \a\t g:i A'); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($booking['status'] === 'rejected'): ?>
                            <div class="alert alert-danger mt-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-times-circle fa-2x text-danger me-3 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">Booking Not Approved</h6>
                                        <p class="mb-2">Unfortunately, your booking request could not be approved. This may be due to scheduling conflicts or capacity limitations.</p>
                                        <small class="text-muted">
                                            <i class="fas fa-redo me-1"></i>
                                            You can submit a new booking request for a different time slot.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions Panel -->
                    <div class="col-lg-4">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-tools me-2"></i>Available Actions
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if ($booking['status'] === 'pending' || $booking['status'] === 'approved'): ?>
                                    <?php if ($canModify): ?>
                                        <div class="d-grid gap-2 mb-3">
                                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                                <i class="fas fa-calendar-alt me-2"></i>Reschedule
                                            </button>
                                        </div>
                                        <p class="small text-muted mb-3">
                                            <i class="fas fa-clock me-1"></i>
                                            You can modify this booking up to <?php echo htmlspecialchars($modificationHours); ?> hours before the scheduled time.
                                        </p>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-lock me-2"></i>
                                            <strong>Modification Locked</strong><br>
                                            <small>Changes are not allowed within <?php echo htmlspecialchars($modificationHours); ?> hours of the booking time.</small>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <div class="d-grid gap-2 mb-3">
                                            <button class="btn btn-outline-danger" onclick="confirmCancellation()">
                                                <i class="fas fa-times me-2"></i>Cancel Booking
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Always available actions -->
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary" onclick="printBooking()">
                                        <i class="fas fa-print me-2"></i>Print Details
                                    </button>

                                    <button class="btn btn-outline-secondary" onclick="shareBooking()">
                                        <i class="fas fa-share me-2"></i>Share
                                    </button>
                                </div>

                                <hr>

                                <div class="text-center">
                                    <a href="<?php echo BASE_PATH; ?>/view" class="btn btn-link text-decoration-none">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Search
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-headset me-2"></i>Need Help?
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $companySettings = new CompanySettings();
                                $supportEmail = $companySettings->getSetting('support_email', 'support@techhub.com');
                                $companyPhone = $companySettings->getSetting('company_phone');
                                ?>

                                <div class="mb-2">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($supportEmail); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($supportEmail); ?>
                                    </a>
                                </div>

                                <?php if ($companyPhone): ?>
                                    <div class="mb-2">
                                        <i class="fas fa-phone text-primary me-2"></i>
                                        <a href="tel:<?php echo htmlspecialchars($companyPhone); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($companyPhone); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-clock me-1"></i>
                                    Support hours: Monday-Friday, 9 AM - 5 PM
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<?php if (($booking['status'] === 'pending' || $booking['status'] === 'approved') && $canModify): ?>
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rescheduleModalLabel">
                        <i class="fas fa-calendar-alt me-2"></i>Reschedule Booking
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo BASE_PATH; ?>/view/update" method="POST" id="rescheduleForm">
                    <div class="modal-body">
                        <input type="hidden" name="access_code" value="<?php echo htmlspecialchars($booking['access_code']); ?>">

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Current booking:</strong> <?php echo $bookingDateTime->format('l, F j, Y \a\t g:i A'); ?>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div id="reschedule-calendar-container"></div>
                            </div>
                            <div class="col-md-4">
                                <div id="reschedule-time-slots-wrapper" style="display: none;">
                                    <h6 id="reschedule-selected-date-display" class="mb-2"></h6>
                                    <div id="reschedule-time-slots" class="mb-3"></div>
                                    <div id="reschedule-no-slots-message" class="alert alert-warning" style="display: none;">
                                        No available time slots for this date.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="reschedule_booking_datetime" name="booking_datetime" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="rescheduleSubmitBtn" disabled>
                            <i class="fas fa-calendar-alt me-1"></i>Reschedule Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize reschedule calendar if modal exists
        const rescheduleModal = document.getElementById('rescheduleModal');
        if (rescheduleModal) {
            rescheduleModal.addEventListener('shown.bs.modal', function() {
                initializeRescheduleCalendar();
            });
        }
    });

    function initializeRescheduleCalendar() {
        // Similar to main calendar but for rescheduling
        const calendarContainer = document.getElementById('reschedule-calendar-container');
        const timeSlotsWrapper = document.getElementById('reschedule-time-slots-wrapper');
        const timeSlotsDiv = document.getElementById('reschedule-time-slots');
        const noSlotsMessage = document.getElementById('reschedule-no-slots-message');
        const hiddenDateTimeInput = document.getElementById('reschedule_booking_datetime');
        const submitBtn = document.getElementById('rescheduleSubmitBtn');
        const selectedDateDisplay = document.getElementById('reschedule-selected-date-display');

        let currentDate = new Date();
        currentDate.setDate(1);

        // Initialize calendar (similar to main calendar implementation)
        if (window.initializeCalendar) {
            window.initializeCalendar({
                container: calendarContainer,
                timeSlotsWrapper: timeSlotsWrapper,
                timeSlotsDiv: timeSlotsDiv,
                noSlotsMessage: noSlotsMessage,
                hiddenDateTimeInput: hiddenDateTimeInput,
                submitBtn: submitBtn,
                selectedDateDisplay: selectedDateDisplay,
                prefix: 'reschedule-'
            });
        }
    }

    function confirmCancellation() {
        if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo BASE_PATH; ?>/view/cancel/<?php echo htmlspecialchars($booking['access_code']); ?>'; // Fixed this URL

            document.body.appendChild(form);
            form.submit();
        }
    }

    function printBooking() {
        const printContent = `
        <html>
        <head>
            <title>Booking Details - ${document.title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #ccc; padding-bottom: 20px; margin-bottom: 20px; }
                .info-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 10px; margin-bottom: 10px; }
                .label { font-weight: bold; color: #666; }
                .value { color: #333; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Booking Details</h1>
                <p>Access Code: <?php echo htmlspecialchars($booking['access_code']); ?></p>
            </div>
            <div class="info-grid">
                <div class="label">Date & Time:</div>
                <div class="value"><?php echo $bookingDateTime->format('l, F j, Y \a\t g:i A'); ?></div>
                <div class="label">Status:</div>
                <div class="value"><?php echo ucfirst($booking['status']); ?></div>
                <div class="label">Name:</div>
                <div class="value"><?php echo htmlspecialchars($booking['name'] ?? 'Not specified'); ?></div>
                <div class="label">Email:</div>
                <div class="value"><?php echo htmlspecialchars($booking['email'] ?? 'Not specified'); ?></div>
            </div>
            <p><small>Printed on: ${new Date().toLocaleString()}</small></p>
        </body>
        </html>
    `;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }

    function shareBooking() {
        const shareData = {
            title: 'Booking Details',
            text: `Booking for <?php echo $bookingDateTime->format('F j, Y \a\t g:i A'); ?>`,
            url: window.location.href
        };

        if (navigator.share) {
            navigator.share(shareData);
        } else {
            // Fallback - copy URL to clipboard
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Booking URL copied to clipboard!');
            });
        }
    }

    // Form submission handling
    document.getElementById('rescheduleForm')?.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('rescheduleSubmitBtn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Rescheduling...';
        submitBtn.disabled = true;
    });
</script>

<style>
    .card-header.bg-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    }

    .booking-info-grid {
        display: grid;
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
        border-left: 4px solid var(--primary-color);
    }

    .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 140px;
        flex-shrink: 0;
    }

    .info-value {
        color: #212529;
        word-break: break-word;
    }

    .alert .fa-2x {
        font-size: 1.5rem;
    }

    .badge.fs-6 {
        font-size: 1rem !important;
    }

    @media (max-width: 768px) {
        .info-item {
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            min-width: auto;
        }

        .card-header .row {
            text-align: center;
        }

        .col-auto {
            margin-top: 1rem;
        }
    }

    /* Print styles */
    @media print {
        .btn, .modal, .alert {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .card-header {
            background: #f8f9fa !important;
            color: #212529 !important;
        }
    }
</style>

<?php require_once 'views/templates/footer.php'; ?>
