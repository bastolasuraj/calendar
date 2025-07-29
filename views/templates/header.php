<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $companySettings = new CompanySettings();
    $companyName = $companySettings->getCompanyName();
    $primaryColor = $companySettings->getPrimaryColor();
    $secondaryColor = $companySettings->getSecondaryColor();
    $accentColor = $companySettings->getAccentColor();
    $faviconUrl = $companySettings->getFaviconUrl();
    ?>
    <title><?php echo htmlspecialchars($companyName); ?> - Professional Booking System</title>

    <!-- Favicon -->
    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($faviconUrl); ?>" type="image/x-icon">
    <?php endif; ?>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Flatpickr CSS for date/time picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- SortableJS for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="views/assets/css/admin.css">

    <!-- Dynamic CSS Variables -->
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($primaryColor); ?>;
            --secondary-color: <?php echo htmlspecialchars($secondaryColor); ?>;
            --accent-color: <?php echo htmlspecialchars($accentColor); ?>;
        }

        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: color-mix(in srgb, var(--primary-color) 90%, black);
            border-color: color-mix(in srgb, var(--primary-color) 90%, black);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .border-primary {
            border-color: var(--primary-color) !important;
        }

        .container {
            max-width: 1200px;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: none;
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
            background: linear-gradient(135deg, var(--primary-color), color-mix(in srgb, var(--primary-color) 80%, var(--secondary-color)));
            border: none;
        }

        .footer {
            margin-top: 3rem;
            padding: 2rem 0;
            background-color: #e9ecef;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease-in-out;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            transition: border-color 0.2s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--primary-color) 20%, transparent);
        }

        /* Loading spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: white !important;
        }

        .navbar-nav .nav-link {
            color: var(--secondary-color) !important;
            font-weight: 500;
            transition: color 0.2s ease-in-out;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }

            .card {
                margin-bottom: 1rem;
            }

            .btn {
                font-size: 0.875rem;
            }
        }

        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Admin specific styles */
        <?php if (isset($_SESSION['user_id'])):
         var_dump($_SESSION);

         ?>
        .admin-nav {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .admin-nav .nav-link {
            color: white !important;
        }

        .admin-nav .nav-link:hover {
            color: #f8f9fa !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
        }
        <?php endif; ?>

        /* Status badges */
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-approved {
            background-color: var(--accent-color);
            color: white;
        }

        .status-rejected {
            background-color: #dc3545;
            color: white;
        }

        .status-cancelled {
            background-color: var(--secondary-color);
            color: white;
        }
    </style>
    <!-- Define BASE_PATH_JS for JavaScript usage -->
    <script>
        const BASE_PATH_JS = '<?php echo BASE_PATH; ?>';
    </script>
</head>
<body>

<!-- Loading overlay -->
<div class="spinner-overlay" id="loadingSpinner">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<nav class="navbar navbar-expand-lg <?php echo isset($_SESSION['user_id']) ? 'admin-nav' : 'navbar-light'; ?> mb-4">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_PATH; ?>/">
            <?php
            $logoUrl = $companySettings->getCompanyLogo();
            if ($logoUrl):
                ?>
                <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logo" height="30" class="me-2">
            <?php endif; ?>
            <span><?php echo htmlspecialchars($companyName); ?></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_PATH; ?>/book">
                        <i class="bi bi-calendar-plus me-1"></i>Book Space
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_PATH; ?>/view">
                        <i class="bi bi-search me-1"></i>View Bookings
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-gear me-1"></i><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/bookings">
                                    <i class="bi bi-calendar-check me-2"></i>Manage Bookings
                                </a></li>
                            <?php
                            // FIX: Ensure user_permissions is an array before using it in in_array
                            $userPermissions = isset($_SESSION['user_permissions']) && is_array($_SESSION['user_permissions']) ? $_SESSION['user_permissions'] : [];
                            ?>
                            <?php if (in_array('manage_forms', $userPermissions)): // This is line 286 or similar ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/form-builder">
                                        <i class="bi bi-ui-checks me-2"></i>Form Builder
                                    </a></li>
                            <?php endif; ?>
                            <?php if (in_array('manage_settings', $userPermissions)): ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/company-settings">
                                        <i class="bi bi-building me-2"></i>Company Settings
                                    </a></li>
                            <?php endif; ?>
                            <?php if (in_array('manage_templates', $userPermissions)): ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/email-templates">
                                        <i class="bi bi-envelope me-2"></i>Email Templates
                                    </a></li>
                            <?php endif; ?>
                            <?php if (in_array('manage_users', $userPermissions)): ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/users">
                                        <i class="bi bi-people me-2"></i>User Management
                                    </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_PATH; ?>/admin/logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>/admin">
                            <i class="bi bi-shield-lock me-1"></i>Admin Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <?php
    // Display flash messages
    if (isset($_GET['success'])) {
        $successMessages = [
            'statusupdated' => 'Booking status updated successfully!',
            'created' => 'Item created successfully!',
            'updated' => 'Item updated successfully!',
            'deleted' => 'Item deleted successfully!',
            'activated' => 'Configuration activated successfully!',
            'cancelled' => 'Booking cancelled successfully!',
            'reset' => 'Settings reset to defaults successfully!'
        ];
        $message = $successMessages[$_GET['success']] ?? 'Operation completed successfully!';
        echo '<div class="alert alert-success alert-dismissible fade show fade-in-up" role="alert">
                <i class="bi bi-check-circle me-2"></i>' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }

    if (isset($_GET['error'])) {
        $errorMessages = [
            'missingdata' => 'Please fill in all required fields.',
            'updatefailed' => 'Update failed. Please try again.',
            'createfailed' => 'Creation failed. Please check your input.',
            'deletefailed' => 'Deletion failed. Please try again.',
            'notfound' => 'The requested item was not found.',
            'accessdenied' => 'You do not have permission to perform this action.',
            'systemerror' => 'A system error occurred. Please try again later.',
            'invaliddata' => 'Invalid data provided. Please check your input.'
        ];
        $message = $errorMessages[$_GET['error']] ?? 'An error occurred. Please try again.';
        echo '<div class="alert alert-danger alert-dismissible fade show fade-in-up" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
    ?>
