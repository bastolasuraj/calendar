<?php require_once 'views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h2 class="h4 mb-0">
                    <i class="fas fa-search me-2"></i>View or Edit Your Booking
                </h2>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['error']) && $_GET['error'] == 'notfound'): ?>
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>Booking not found. Please check your access code and try again.</div>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Enter your access code to view your booking details, check status, or make changes.
                    </p>
                </div>

                <form action="<?php echo BASE_PATH; ?>/view/find" method="POST" data-validate="true">
                    <div class="mb-3">
                        <label for="access_code" class="form-label">Access Code</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-key text-muted"></i>
                            </span>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="access_code"
                                   name="access_code"
                                   placeholder="Enter your booking access code"
                                   required
                                   autocomplete="off"
                                   style="letter-spacing: 1px;"
                                   maxlength="20">
                        </div>
                        <div class="form-text">
                            <i class="fas fa-lightbulb me-1"></i>
                            Your access code was provided when you made your booking
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-info btn-lg">
                            <i class="fas fa-search me-2"></i>Find My Booking
                        </button>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <small class="text-muted">
                        <i class="fas fa-envelope me-1"></i>
                        Lost your access code? Check your confirmation email
                    </small>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card shadow-sm mt-4 border-primary">
            <div class="card-header bg-light">
                <h6 class="mb-0 text-primary">
                    <i class="fas fa-question-circle me-2"></i>Need Help?
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Common Issues:</h6>
                    <ul class="small text-muted mb-0">
                        <li>Access codes are case-sensitive</li>
                        <li>Check your email for the original booking confirmation</li>
                        <li>Ensure you're entering the complete code</li>
                    </ul>
                </div>

                <div class="text-center">
                    <?php
                    $companySettings = new CompanySettings();
                    // FIX: Changed getSetting() to get()
                    $supportEmail = $companySettings->get('support_email', 'support@techhub.com');
                    ?>
                    <a href="mailto:<?php echo htmlspecialchars($supportEmail); ?>"
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-envelope me-1"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">
                    <i class="fas fa-calendar-check me-2"></i>Upcoming Approved Sessions
                </h2>
                <span class="badge bg-success"><?php echo iterator_count($publicBookings); ?> Active</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Below are the currently scheduled and approved booking sessions. Only the first name and time are shown for privacy.
                </p>

                <?php
                $bookingsArray = iterator_to_array($publicBookings);
                if (empty($bookingsArray)):
                    ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Approved Sessions</h5>
                        <p class="text-muted mb-4">There are currently no approved booking sessions scheduled.</p>
                        <a href="<?php echo BASE_PATH; ?>/book" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Make a Booking
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php
                        // Group bookings by date
                        $groupedBookings = [];
                        foreach ($bookingsArray as $booking) {
                            $date = $booking['booking_datetime']->toDateTime()->format('Y-m-d');
                            if (!isset($groupedBookings[$date])) {
                                $groupedBookings[$date] = [];
                            }
                            $groupedBookings[$date][] = $booking;
                        }

                        // Sort dates
                        ksort($groupedBookings);

                        foreach ($groupedBookings as $date => $dayBookings):
                            $dateObj = new DateTime($date);
                            ?>
                            <div class="col-12 mb-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    <?php echo $dateObj->format('l, F j, Y'); ?>
                                </h6>

                                <div class="row g-3">
                                    <?php foreach ($dayBookings as $booking): ?>
                                        <div class="col-md-6">
                                            <div class="booking-card">
                                                <div class="d-flex align-items-center">
                                                    <div class="booking-avatar">
                                                        <?php
                                                        $name = $booking['name'] ?? 'Unknown';
                                                        $displayName = explode(' ', $name)[0]; // First name only
                                                        echo strtoupper(substr($displayName, 0, 1));
                                                        ?>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($displayName); ?></h6>
                                                        <div class="d-flex align-items-center text-muted">
                                                            <small>
                                                                <i class="fas fa-clock me-1"></i>
                                                                <?php echo $booking['booking_datetime']->toDateTime()->format('g:i A'); ?>
                                                            </small>
                                                            <?php if (isset($booking['form_data']['department'])): ?>
                                                                <span class="badge bg-light text-dark ms-2">
                                                        <?php echo htmlspecialchars($booking['form_data']['department']); ?>
                                                    </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="booking-status">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Statistics -->
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-4 text-center">
                            <div class="stat-box">
                                <div class="stat-number text-primary"><?php echo count($bookingsArray); ?></div>
                                <div class="stat-label">Total Sessions</div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="stat-box">
                                <div class="stat-number text-success">
                                    <?php echo count(array_unique(array_column($bookingsArray, 'name'))); ?>
                                </div>
                                <div class="stat-label">Unique Users</div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="stat-box">
                                <div class="stat-number text-info">
                                    <?php echo count($groupedBookings); ?>
                                </div>
                                <div class="stat-label">Active Days</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <a href="<?php echo BASE_PATH; ?>/book" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>New Booking
                        </a>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-outline-secondary w-100" onclick="showAccessCodeHelp()">
                            <i class="fas fa-question-circle me-2"></i>Find Access Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Access Code Help Modal -->
<div class="modal fade" id="accessCodeHelpModal" tabindex="-1" aria-labelledby="accessCodeHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accessCodeHelpModalLabel">
                    <i class="fas fa-question-circle me-2"></i>Finding Your Access Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6><i class="fas fa-envelope text-primary me-2"></i>Check Your Email</h6>
                    <p class="small text-muted">Your access code was sent to your email address when you made the booking. Look for:</p>
                    <ul class="small text-muted">
                        <li>Subject line containing "Booking Confirmation"</li>
                        <li>A unique code (usually 10-15 characters)</li>
                        <li>The code might be highlighted or in a special box</li>
                    </ul>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-search text-info me-2"></i>Search Tips</h6>
                    <ul class="small text-muted">
                        <li>Search your email for "<?php echo htmlspecialchars($companyName); ?>"</li>
                        <li>Check your spam/junk folder</li>
                        <li>Look for emails from the past few days</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Still can't find it?</strong> Contact our support team with your email address and approximate booking date.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="mailto:<?php echo htmlspecialchars($supportEmail); ?>" class="btn btn-primary">
                    <i class="fas fa-envelope me-1"></i>Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Access code input formatting
        const accessCodeInput = document.getElementById('access_code');

        accessCodeInput.addEventListener('input', function(e) {
            // Convert to uppercase and remove non-alphanumeric characters
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });

        // Form validation
        const form = document.querySelector('form[data-validate="true"]');
        form.addEventListener('submit', function(e) {
            const accessCode = accessCodeInput.value.trim();

            if (accessCode.length < 5) {
                e.preventDefault();
                accessCodeInput.classList.add('is-invalid');
                const feedback = accessCodeInput.parentElement.parentElement.querySelector('.invalid-feedback');
                feedback.textContent = 'Access code must be at least 5 characters long.';
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';
            submitBtn.disabled = true;
        });

        // Clear validation on input
        accessCodeInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });

    function showAccessCodeHelp() {
        new bootstrap.Modal(document.getElementById('accessCodeHelpModal')).show();
    }

    // Auto-focus on access code input
    document.getElementById('access_code').focus();
</script>

<style>
    .booking-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        padding: 1rem;
        transition: all 0.2s ease;
        height: 100%;
    }

    .booking-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
    }

    .booking-avatar {
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .booking-status {
        font-size: 1.2rem;
    }

    .stat-box {
        padding: 1rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 600;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.25rem;
    }

    .form-control-lg {
        font-size: 1.1rem;
        padding: 0.75rem 1rem;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1.1rem;
    }

    .card.border-primary {
        border-width: 2px;
    }

    .input-group-text {
        background: transparent;
        border-right: none;
    }

    .input-group .form-control {
        border-left: none;
    }

    .input-group:focus-within .input-group-text {
        border-color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .booking-card {
            margin-bottom: 1rem;
        }

        .stat-box {
            padding: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .stat-box:last-child {
            border-bottom: none;
        }
    }
</style>

<?php require_once 'views/templates/footer.php'; ?>
