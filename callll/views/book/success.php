<?php require_once 'views/templates/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-lg border-0 text-center fade-in">
            <div class="card-header bg-success text-white py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h2 class="h3 mb-0">Booking Successful!</h2>
                <p class="mb-0 opacity-75">Your request has been submitted successfully</p>
            </div>
            <div class="card-body p-5">
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <p class="lead mb-4">Thank you for your booking request! We have received your submission and will review it shortly.</p>

                        <div class="alert alert-info d-flex align-items-start mb-4">
                            <i class="fas fa-key fa-2x me-3 mt-1"></i>
                            <div class="text-start">
                                <h5 class="alert-heading mb-2">Your Access Code</h5>
                                <div class="access-code-display">
                                    <code class="fs-4 fw-bold text-primary"><?php echo htmlspecialchars($accessCode); ?></code>
                                    <button class="btn btn-outline-primary btn-sm ms-2" onclick="copyAccessCode()" title="Copy to clipboard">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Save this code! You'll need it to view or modify your booking.
                                </small>
                            </div>
                        </div>

                        <div class="row text-center mb-4">
                            <div class="col-4">
                                <div class="feature-box">
                                    <i class="fas fa-envelope text-primary fa-2x mb-2"></i>
                                    <h6>Email Confirmation</h6>
                                    <small class="text-muted">Check your inbox for confirmation details</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="feature-box">
                                    <i class="fas fa-clock text-info fa-2x mb-2"></i>
                                    <h6>Under Review</h6>
                                    <small class="text-muted">We'll review your request promptly</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="feature-box">
                                    <i class="fas fa-bell text-success fa-2x mb-2"></i>
                                    <h6>Status Updates</h6>
                                    <small class="text-muted">You'll receive email notifications</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="<?php echo BASE_PATH; ?>/view/details/<?php echo htmlspecialchars($accessCode); ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-eye me-2"></i>View Your Booking
                            </a>
                            <a href="<?php echo BASE_PATH; ?>/book" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-plus me-2"></i>Make Another Booking
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light text-muted py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small>
                            <i class="fas fa-shield-alt me-1"></i>
                            Your booking is secure and encrypted
                        </small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small>
                            Need help?
                            <?php
                            $companySettings = new CompanySettings();
                            $supportEmail = $companySettings->getSetting('support_email', 'support@techhub.com');
                            ?>
                            <a href="mailto:<?php echo htmlspecialchars($supportEmail); ?>" class="text-decoration-none">
                                Contact Support
                            </a>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- What Happens Next Card -->
        <div class="card shadow mt-4 fade-in" style="animation-delay: 0.3s;">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list-ol me-2"></i>What Happens Next?
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Booking Submitted</h6>
                            <p class="timeline-text">Your booking request has been received and recorded in our system.</p>
                            <small class="text-success"><i class="fas fa-check-circle me-1"></i>Completed</small>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Review Process</h6>
                            <p class="timeline-text">Our team will review your booking request and check availability.</p>
                            <small class="text-warning"><i class="fas fa-spinner fa-spin me-1"></i>In Progress</small>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker bg-info">
                            <i class="fas fa-envelope text-white"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Status Notification</h6>
                            <p class="timeline-text">You'll receive an email notification with the decision on your booking.</p>
                            <small class="text-muted"><i class="fas fa-hourglass-half me-1"></i>Pending</small>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary">
                            <i class="fas fa-calendar-check text-white"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Booking Confirmed</h6>
                            <p class="timeline-text">If approved, you'll receive final confirmation with all necessary details.</p>
                            <small class="text-muted"><i class="fas fa-hourglass-half me-1"></i>Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips and Reminders -->
        <div class="card shadow mt-4 border-info fade-in" style="animation-delay: 0.6s;">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>Important Reminders
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                            <i class="fas fa-save text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Save Your Access Code</h6>
                                <small class="text-muted">You'll need this code to view, modify, or cancel your booking.</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-3">
                            <i class="fas fa-inbox text-success me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Check Your Email</h6>
                                <small class="text-muted">Confirmation and status updates will be sent to your email address.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                            <i class="fas fa-edit text-warning me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Need Changes?</h6>
                                <small class="text-muted">Use your access code to modify your booking if needed.</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-3">
                            <i class="fas fa-question-circle text-info me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Questions?</h6>
                                <small class="text-muted">Contact our support team if you need assistance.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyAccessCode() {
        const accessCode = '<?php echo htmlspecialchars($accessCode); ?>';

        if (navigator.clipboard) {
            navigator.clipboard.writeText(accessCode).then(function() {
                showCopySuccess();
            }, function(err) {
                fallbackCopyTextToClipboard(accessCode);
            });
        } else {
            fallbackCopyTextToClipboard(accessCode);
        }
    }

    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess();
            }
        } catch (err) {
            console.error('Failed to copy access code');
        }

        document.body.removeChild(textArea);
    }

    function showCopySuccess() {
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;

        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');

        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = '<i class="fas fa-check-circle me-2"></i>Access code copied to clipboard!';
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');

            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 2000);
    }

    // Auto-scroll to view the success message
    document.addEventListener('DOMContentLoaded', function() {
        window.scrollTo(0, 0);

        // Add entrance animations
        const elements = document.querySelectorAll('.fade-in');
        elements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';

            setTimeout(() => {
                el.style.transition = 'all 0.6s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, index * 200);
        });
    });
</script>

<style>
    .card-header.bg-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    }

    .access-code-display {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .access-code-display code {
        background-color: #e3f2fd;
        color: var(--primary-color);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: 2px solid var(--primary-color);
        letter-spacing: 2px;
    }

    .feature-box {
        padding: 1rem;
        border-radius: 0.5rem;
        transition: transform 0.2s ease;
    }

    .feature-box:hover {
        transform: translateY(-2px);
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }

    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        border: 3px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .timeline-content {
        padding-left: 1rem;
    }

    .timeline-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .timeline-text {
        color: #6c757d;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transform: translateY(-100px);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 1050;
        font-size: 0.9rem;
    }

    .toast-notification.show {
        transform: translateY(0);
        opacity: 1;
    }

    .btn-lg {
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
    }

    @media (max-width: 768px) {
        .access-code-display {
            flex-direction: column;
            gap: 0.75rem;
        }

        .access-code-display code {
            font-size: 1.1rem;
        }

        .d-grid.d-md-flex {
            gap: 0.5rem;
        }

        .timeline {
            padding-left: 1.5rem;
        }

        .timeline-marker {
            left: -1.5rem;
            width: 24px;
            height: 24px;
            font-size: 0.75rem;
        }
    }
</style>

<?php require_once 'views/templates/footer.php'; ?>
