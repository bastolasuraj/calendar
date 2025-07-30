<?php
// Company Settings Controller for branding and configuration management

class CompanyController {
    private $companySettings;
    private $userModel;

    public function __construct() {
        $this->companySettings = new CompanySettings();
        $this->userModel = new User();
    }

    public function index() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_settings')) {
            $this->accessDenied();
            return;
        }

        // Get all settings formatted for display
        $settings = $this->companySettings->getFormattedSettings();

        require 'views/admin/company_settings.php';
    }

    public function update() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_settings')) {
            $this->accessDenied();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . '/admin/company-settings');
            exit;
        }

        try {
            $updates = [];

            // Company Information
            if (isset($_POST['company_name'])) {
                $updates['company_name'] = trim($_POST['company_name']);
            }
            if (isset($_POST['company_email'])) {
                $updates['company_email'] = trim($_POST['company_email']);
            }
            if (isset($_POST['company_phone'])) {
                $updates['company_phone'] = trim($_POST['company_phone']);
            }
            if (isset($_POST['company_address'])) {
                $updates['company_address'] = trim($_POST['company_address']);
            }
            if (isset($_POST['company_website'])) {
                $updates['company_website'] = trim($_POST['company_website']);
            }
            if (isset($_POST['company_description'])) {
                $updates['company_description'] = trim($_POST['company_description']);
            }

            // Branding
            if (isset($_POST['primary_color'])) {
                $updates['primary_color'] = trim($_POST['primary_color']);
            }
            if (isset($_POST['secondary_color'])) {
                $updates['secondary_color'] = trim($_POST['secondary_color']);
            }
            if (isset($_POST['accent_color'])) {
                $updates['accent_color'] = trim($_POST['accent_color']);
            }

            // Working Hours
            if (isset($_POST['working_hours_start'])) {
                $updates['working_hours_start'] = trim($_POST['working_hours_start']);
            }
            if (isset($_POST['working_hours_end'])) {
                $updates['working_hours_end'] = trim($_POST['working_hours_end']);
            }
            if (isset($_POST['slot_duration_hours'])) {
                $updates['slot_duration_hours'] = (int)$_POST['slot_duration_hours'];
            }
            if (isset($_POST['working_days'])) {
                $updates['working_days'] = json_encode($_POST['working_days']);
            }

            // Booking Settings
            if (isset($_POST['max_advance_booking_days'])) {
                $updates['max_advance_booking_days'] = (int)$_POST['max_advance_booking_days'];
            }
            if (isset($_POST['min_advance_booking_hours'])) {
                $updates['min_advance_booking_hours'] = (int)$_POST['min_advance_booking_hours'];
            }
            if (isset($_POST['require_booking_approval'])) {
                $updates['require_booking_approval'] = (bool)$_POST['require_booking_approval'];
            }
            if (isset($_POST['allow_booking_updates'])) {
                $updates['allow_booking_updates'] = (bool)$_POST['allow_booking_updates'];
            }

            // Email Settings
            if (isset($_POST['email_from_name'])) {
                $updates['email_from_name'] = trim($_POST['email_from_name']);
            }
            if (isset($_POST['email_from_email'])) {
                $updates['email_from_email'] = trim($_POST['email_from_email']);
            }
            if (isset($_POST['email_reply_to'])) {
                $updates['email_reply_to'] = trim($_POST['email_reply_to']);
            }

            // Notification Settings
            if (isset($_POST['notify_admin_new_booking'])) {
                $updates['notify_admin_new_booking'] = (bool)$_POST['notify_admin_new_booking'];
            }
            if (isset($_POST['notify_user_confirmation'])) {
                $updates['notify_user_confirmation'] = (bool)$_POST['notify_user_confirmation'];
            }
            if (isset($_POST['notify_user_status_update'])) {
                $updates['notify_user_status_update'] = (bool)$_POST['notify_user_status_update'];
            }

            // System Settings
            if (isset($_POST['timezone'])) {
                $updates['timezone'] = trim($_POST['timezone']);
            }
            if (isset($_POST['maintenance_mode'])) {
                $updates['maintenance_mode'] = (bool)$_POST['maintenance_mode'];
            }

            // Save all updates
            if ($this->companySettings->setMultiple($updates)) {
                header('Location: ' . BASE_PATH . '/admin/company-settings?success=updated');
            } else {
                header('Location: ' . BASE_PATH . '/admin/company-settings?error=updatefailed');
            }

        } catch (Exception $e) {
            error_log('Company settings update error: ' . $e->getMessage());
            header('Location: ' . BASE_PATH . '/admin/company-settings?error=systemerror');
        }
        exit;
    }

    public function uploadLogo() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_settings')) {
            $this->accessDenied();
            return;
        }

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['logo'])) {
            echo json_encode(['error' => 'No file uploaded']);
            return;
        }

        try {
            $file = $_FILES['logo'];

            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['error' => 'Upload failed']);
                return;
            }

            // Check file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['error' => 'Invalid file type']);
                return;
            }

            // Check file size (max 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                echo json_encode(['error' => 'File too large']);
                return;
            }

            // Create upload directory if it doesn't exist
            $uploadDir = 'assets/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $filename = 'logo_' . time() . '_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Save to settings
                $this->companySettings->set('company_logo', '/' . $filepath);

                echo json_encode([
                    'success' => true,
                    'url' => BASE_PATH . '/' . $filepath
                ]);
            } else {
                echo json_encode(['error' => 'Failed to save file']);
            }

        } catch (Exception $e) {
            error_log('Logo upload error: ' . $e->getMessage());
            echo json_encode(['error' => 'System error']);
        }
    }

    public function resetSettings() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('system_admin')) {
            $this->accessDenied();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Clear all settings
                $this->companySettings->clearCache();

                // Reinitialize defaults
                $this->companySettings->initializeDefaults();

                header('Location: ' . BASE_PATH . '/admin/company-settings?success=reset');
            } catch (Exception $e) {
                error_log('Settings reset error: ' . $e->getMessage());
                header('Location: ' . BASE_PATH . '/admin/company-settings?error=resetfailed');
            }
        } else {
            header('Location: ' . BASE_PATH . '/admin/company-settings');
        }
        exit;
    }

    public function testEmail() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_settings')) {
            $this->accessDenied();
            return;
        }

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            $testEmail = trim($_POST['test_email'] ?? '');

            if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['error' => 'Invalid email address']);
                return;
            }

            $emailService = new EmailService();
            $testData = [
                'email' => $testEmail,
                'full_name' => 'Test User'
            ];

            $success = $emailService->sendTestEmail($testEmail, $testData);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Test email sent successfully']);
            } else {
                echo json_encode(['error' => 'Failed to send test email']);
            }

        } catch (Exception $e) {
            error_log('Test email error: ' . $e->getMessage());
            echo json_encode(['error' => 'System error: ' . $e->getMessage()]);
        }
    }

    public function getTimezones() {
        header('Content-Type: application/json');

        $timezones = [
            'America/Toronto' => 'Eastern Time (Toronto)',
            'America/Vancouver' => 'Pacific Time (Vancouver)',
            'America/Edmonton' => 'Mountain Time (Edmonton)',
            'America/Winnipeg' => 'Central Time (Winnipeg)',
            'America/Halifax' => 'Atlantic Time (Halifax)',
            'America/St_Johns' => 'Newfoundland Time (St. Johns)',
            'UTC' => 'UTC',
            'America/New_York' => 'US Eastern Time',
            'America/Chicago' => 'US Central Time',
            'America/Denver' => 'US Mountain Time',
            'America/Los_Angeles' => 'US Pacific Time',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Asia/Tokyo' => 'Tokyo',
            'Australia/Sydney' => 'Sydney'
        ];

        echo json_encode($timezones);
    }

    // Helper methods
    private function checkAdminAccess() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/admin/login');
            exit;
            return false;
        }
        return true;
    }

    private function hasPermission($permission) {
        return in_array($permission, $_SESSION['user_permissions'] ?? []);
    }

    private function accessDenied() {
        require 'views/templates/header.php';
        echo '<div class="alert alert-danger"><h1>Access Denied</h1><p>You do not have permission to access this resource.</p></div>';
        require 'views/templates/footer.php';
    }
}
?>
