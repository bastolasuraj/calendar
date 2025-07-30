<?php
// Email Template Controller for managing notification templates

class EmailController {
    private $emailTemplate;
    private $userModel;
    private $emailService;

    public function __construct() {
        $this->emailTemplate = new EmailTemplate();
        $this->userModel = new User();
        $this->emailService = new EmailService();
    }

    public function index() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_templates')) {
            $this->accessDenied();
            return;
        }

        // Get all email templates
        $templates = $this->emailTemplate->getAllTemplates();

        // Group templates by type
        $groupedTemplates = [];
        foreach ($templates as $template) {
            $groupedTemplates[$template['type']][] = $template;
        }

        require 'views/admin/email-templates.php';
    }

    public function create() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_templates')) {
            $this->accessDenied();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = trim($_POST['type']);
            $name = trim($_POST['name']);
            $subject = trim($_POST['subject']);
            $htmlBody = trim($_POST['html_body']);
            $textBody = trim($_POST['text_body']);

            if (empty($type) || empty($subject) || empty($htmlBody)) {
                header('Location: ' . BASE_PATH . '/admin/email-templates?error=missingdata');
                exit;
            }

            $result = $this->emailTemplate->saveTemplate($type, $subject, $htmlBody, $textBody, $name);

            if ($result) {
                header('Location: ' . BASE_PATH . '/admin/email-templates?success=created');
            } else {
                header('Location: ' . BASE_PATH . '/admin/email-templates?error=savefailed');
            }
        } else {
            $templateTypes = [
                EmailTemplate::TYPE_BOOKING_CONFIRMATION => 'Booking Confirmation',
                EmailTemplate::TYPE_STATUS_UPDATE => 'Status Update',
                EmailTemplate::TYPE_BOOKING_REMINDER => 'Booking Reminder',
                EmailTemplate::TYPE_ADMIN_NOTIFICATION => 'Admin Notification'
            ];

            require 'views/admin/create_template.php';
        }
        exit;
    }

    public function edit($id = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_templates')) {
            $this->accessDenied();
            return;
        }

        if (empty($id)) {
            header('Location: ' . BASE_PATH . '/admin/email-templates');
            exit;
        }

        $template = $this->emailTemplate->getTemplateById($id);
        if (!$template) {
            header('Location: ' . BASE_PATH . '/admin/email-templates?error=templatenotfound');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $subject = trim($_POST['subject']);
            $htmlBody = trim($_POST['html_body']);
            $textBody = trim($_POST['text_body']);

            if (empty($subject) || empty($htmlBody)) {
                header('Location: ' . BASE_PATH . '/admin/email-templates/edit/' . $id . '?error=missingdata');
                exit;
            }

            // Save as new template (versioning)
            $result = $this->emailTemplate->saveTemplate($template['type'], $subject, $htmlBody, $textBody, $name ?: $template['name']);

            if ($result) {
                header('Location: ' . BASE_PATH . '/admin/email-templates?success=updated');
            } else {
                header('Location: ' . BASE_PATH . '/admin/email-templates/edit/' . $id . '?error=savefailed');
            }
        } else {
            $availableVariables = $this->emailTemplate->getAvailableVariables($template['type']);
            require 'views/admin/edit_template.php';
        }
        exit;
    }

    public function delete($id = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_templates')) {
            $this->accessDenied();
            return;
        }

        if (empty($id)) {
            header('Location: ' . BASE_PATH . '/admin/email-templates');
            exit;
        }

        if ($this->emailTemplate->deleteTemplate($id)) {
            header('Location: ' . BASE_PATH . '/admin/email-templates?success=deleted');
        } else {
            header('Location: ' . BASE_PATH . '/admin/email-templates?error=deletefailed');
        }
        exit;
    }

    public function preview($id = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_templates')) {
            $this->accessDenied();
            return;
        }

        if (empty($id)) {
            header('Location: ' . BASE_PATH . '/admin/email-templates');
            exit;
        }

        $template = $this->emailTemplate->getTemplateById($id);
        if (!$template) {
            header('Location: ' . BASE_PATH . '/admin/email-templates?error=templatenotfound');
            exit;
        }

        // Sample variables for preview
        $sampleVariables = [
            'company_name' => 'TechHub Coworking Space',
            'company_email' => 'contact@techhub.local',
            'company_phone' => '+1 (555) 123-4567',
            'company_website' => 'https://techhub.local',
            'customer_name' => 'John Doe',
            'customer_email' => 'john.doe@example.com',
            'booking_id' => 'ABC123XYZ789',
            'booking_date' => 'March 15, 2025',
            'booking_time' => '2:00 PM',
            'booking_datetime' => 'March 15, 2025 at 2:00 PM',
            'booking_status' => 'Approved',
            'current_date' => date('F d, Y'),
            'current_time' => date('g:i A'),
            'admin_dashboard_url' => BASE_PATH . '/admin'
        ];

        $rendered = $this->emailTemplate->renderTemplate($template['type'], $sampleVariables);

        require 'views/admin/preview_template.php';
    }

    public function test($id = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_templates')) {
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

            if (empty($id)) {
                echo json_encode(['error' => 'Template ID required']);
                return;
            }

            $template = $this->emailTemplate->getTemplateById($id);
            if (!$template) {
                echo json_encode(['error' => 'Template not found']);
                return;
            }

            // Sample data for testing
            $testData = [
                'email' => $testEmail,
                'full_name' => 'Test User',
                'booking_datetime' => date('Y-m-d H:i:s', strtotime('+1 day')),
                'access_code' => 'TEST123456'
            ];

            $success = $this->emailService->sendTemplateTest($template['type'], $testEmail, $testData);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Test email sent successfully']);
            } else {
                echo json_encode(['error' => 'Failed to send test email']);
            }

        } catch (Exception $e) {
            error_log('Template test error: ' . $e->getMessage());
            echo json_encode(['error' => 'System error: ' . $e->getMessage()]);
        }
    }

    public function getVariables($type = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_templates')) {
            $this->accessDenied();
            return;
        }

        header('Content-Type: application/json');

        if (empty($type)) {
            echo json_encode(['error' => 'Template type required']);
            return;
        }

        try {
            $variables = $this->emailTemplate->getAvailableVariables($type);
            echo json_encode(['variables' => $variables]);
        } catch (Exception $e) {
            error_log('Get variables error: ' . $e->getMessage());
            echo json_encode(['error' => 'Failed to get variables']);
        }
    }

    public function reset($type = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('system_admin')) {
            $this->accessDenied();
            return;
        }

        if (empty($type)) {
            header('Location: ' . BASE_PATH . '/admin/email-templates');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // This would reset to default template - simplified for now
                $this->emailTemplate->initializeDefaultTemplates();

                header('Location: ' . BASE_PATH . '/admin/email-templates?success=reset');
            } catch (Exception $e) {
                error_log('Template reset error: ' . $e->getMessage());
                header('Location: ' . BASE_PATH . '/admin/email-templates?error=resetfailed');
            }
        } else {
            header('Location: ' . BASE_PATH . '/admin/email-templates');
        }
        exit;
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
