<?php require_once 'views/templates/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                </h1>
                <p class="text-muted mb-0">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?: $_SESSION['user_email']); ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <?php if ($user->hasPermission($_SESSION['user_id'], 'export_data')): ?>
                    <a href="<?php echo BASE_PATH; ?>/admin/export/bookings" class="btn btn-outline-primary">
                        <i class="bi bi-download me-1"></i>Export Data
                    </a>
                <?php endif; ?>
                <?php if ($user->hasPermission($_SESSION['user_id'], 'manage_company_settings')): ?>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                        <i class="bi bi-envelope-check me-1"></i>Test Email
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0"><?php echo $analytics['total']; ?></h4>
                        <p class="card-text mb-0">Total Bookings</p>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0"><?php echo $analytics['pending']; ?></h4>
                        <p class="card-text mb-0">Pending Review</p>
                    </div>
                    <i class="bi bi-clock-history fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0"><?php echo $analytics['approved']; ?></h4>
                        <p class="card-text mb-0">Approved</p>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0"><?php echo $analytics['rejected']; ?></h4>
                        <p class="card-text mb-0">Rejected</p>
                    </div>
                    <i class="bi bi-x-circle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($user->hasPermission($_SESSION['user_id'], 'manage_form_builder')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo BASE_PATH; ?>/admin/form-builder" class="text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded hover-shadow">
                                    <i class="bi bi-ui-checks fs-3 me-3 text-primary"></i>
                                    <div>
                                        <h6 class="mb-1">Form Builder</h6>
                                        <small class="text-muted">Create & customize booking forms</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($user->hasPermission($_SESSION['user_id'], 'manage_company_settings')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo BASE_PATH; ?>/admin/company-settings" class="text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded hover-shadow">
                                    <i class="bi bi-gear fs-3 me-3 text-success"></i>
                                    <div>
                                        <h6 class="mb-1">Company Settings</h6>
                                        <small class="text-muted">Manage branding & configuration</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($user->hasPermission($_SESSION['user_id'], 'view_analytics')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo BASE_PATH; ?>/admin/analytics" class="text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded hover-shadow">
                                    <i class="bi bi-graph-up fs-3 me-3 text-info"></i>
                                    <div>
                                        <h6 class="mb-1">Analytics</h6>
                                        <small class="text-muted">View detailed reports</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Current Form Configuration -->
<?php if ($activeForm): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>Active Form Configuration
                    </h5>
                    <?php if ($user->hasPermission($_SESSION['user_id'], 'manage_form_builder')): ?>
                        <a href="<?php echo BASE_PATH; ?>/admin/form-builder" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h6><?php echo htmlspecialchars($activeForm['name']); ?></h6>
                    <p class="text-muted mb-2">
                        <?php echo count($activeForm['fields']); ?> fields configured
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($activeForm['fields'] as $field): ?>
                            <span class="badge bg-light text-dark">
                            <?php echo htmlspecialchars($field['label']); ?>
                                <?php if ($field['required']): ?><i class="bi bi-asterisk text-danger ms-1"></i><?php endif; ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Bookings Management -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-event me-2"></i>Recent Bookings
                </h5>
                <div class="d-flex gap-2">
                    <!-- Filters -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?status=pending">Pending Only</a></li>
                            <li><a class="dropdown-item" href="?status=approved">Approved Only</a></li>
                            <li><a class="dropdown-item" href="?status=rejected">Rejected Only</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin">All Bookings</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Search and Filters -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search"
                               placeholder="Search by name, email, or access code"
                               value="<?php echo htmlspecialchars($filters['search']); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from"
                               value="<?php echo htmlspecialchars($filters['date_from']); ?>" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to"
                               value="<?php echo htmlspecialchars($filters['date_to']); ?>" placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                    </div>
                </form>

                <!-- Bookings Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($bookingsData['total'] > 0): ?>
                            <?php foreach ($bookingsData['bookings'] as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['name'] ?? 'N/A'); ?></strong>
                                        <?php if (!empty($booking['department'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($booking['department']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['email']); ?>
                                        <?php if (!empty($booking['phone'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($booking['phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo $booking['booking_datetime']->toDateTime()->format('M d, Y'); ?></strong>
                                        <br><small class="text-muted"><?php echo $booking['booking_datetime']->toDateTime()->format('g:i A'); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'cancelled' => 'secondary'
                                        ];
                                        $badgeColor = $statusColors[$booking['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $badgeColor; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo $booking['created_at']->toDateTime()->format('M d, Y g:i A'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-outline-success"
                                                        onclick="updateStatus('<?php echo $booking['access_code']; ?>', 'approved')">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="updateStatus('<?php echo $booking['access_code']; ?>', 'rejected')">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-info"
                                                    onclick="viewBookingDetails('<?php echo $booking['access_code']; ?>')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0">No bookings found matching your criteria.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($bookingsData['pages'] > 1): ?>
                    <nav aria-label="Bookings pagination">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $bookingsData['pages']; $i++): ?>
                                <li class="page-item <?php echo $bookingsData['page'] == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Booking Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusUpdateForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="updateAccessCode" name="access_code">
                    <input type="hidden" id="updateStatus" name="status">

                    <div class="mb-3">
                        <label for="adminNotes" class="form-label">Admin Notes (Optional)</label>
                        <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3"
                                  placeholder="Add any notes for the customer..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        The customer will receive an email notification about this status change.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetailsContent">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<?php if ($user->hasPermission($_SESSION['user_id'], 'manage_company_settings')): ?>
    <div class="modal fade" id="testEmailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Email Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo BASE_PATH; ?>/admin/testEmail" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="testEmail" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="testEmail" name="test_email" required
                                   placeholder="Enter email to receive test message">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Test Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        transform: translateY(-2px);
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: var(--primary-color);
    }

    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
    }
</style>

<script>
    function updateStatus(accessCode, status) {
        document.getElementById('updateAccessCode').value = accessCode;
        document.getElementById('updateStatus').value = status;
        document.getElementById('statusUpdateForm').action = `${BASE_PATH_JS}/admin/updateStatus/${accessCode}/${status}`; // Fixed this URL

        // Update modal title based on action
        const modalTitle = document.querySelector('#statusUpdateModal .modal-title');
        modalTitle.textContent = status === 'approved' ? 'Approve Booking' : 'Reject Booking';

        // Show appropriate alert
        const alertElement = document.querySelector('#statusUpdateModal .alert');
        if (status === 'approved') {
            alertElement.className = 'alert alert-success';
            alertElement.innerHTML = '<i class="bi bi-check-circle me-2"></i>This booking will be approved and the customer will receive a confirmation email.';
        } else {
            alertElement.className = 'alert alert-warning';
            alertElement.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>This booking will be rejected. Please provide a reason in the notes.';
        }

        const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
        modal.show();
    }

    function viewBookingDetails(accessCode) {
        const modalBody = document.getElementById('bookingDetailsContent');
        modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
        modal.show();

        // Fetch booking details (in a real app, you'd make an AJAX call)
        // For now, we'll show a placeholder
        setTimeout(() => {
            modalBody.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Booking details for access code: <strong>${accessCode}</strong>
            </div>
            <p>This would show detailed booking information, custom fields, history, etc.</p>
            <p class="text-muted">Integration with the view controller would provide full details here.</p>
        `;
        }, 500);
    }

    // Auto-refresh booking counts every 30 seconds
    setInterval(() => {
        fetch(`${BASE_PATH_JS}/admin/analytics`) // Fixed this URL
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update counters (you would implement this endpoint)
                    console.log('Analytics refreshed');
                }
            })
            .catch(error => console.error('Error refreshing analytics:', error));
    }, 30000);
</script>

<?php require_once 'views/templates/footer.php'; ?>
