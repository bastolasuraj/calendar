<?php
// Enhanced Book Controller with dynamic form support
require_once 'services/EmailService.php';
require_once 'models/User.php'; // Ensure User model is included
require_once 'models/Booking.php'; // Ensure Booking model is included
require_once 'models/FormConfiguration.php'; // Ensure FormConfiguration model is included
require_once 'models/EmailTemplate.php'; // Ensure EmailTemplate model is included
require_once 'models/CompanySettings.php'; // Ensure CompanySettings model is included
class BookController {
    private $formConfig;
    private $bookingModel;
    private $companySettings;

    public function __construct() {
        $this->formConfig = new FormConfiguration();
        $this->bookingModel = new Booking();
        $this->companySettings = new CompanySettings();
    }

    public function index() {
        // Check if system is in maintenance mode
        if ($this->companySettings->isMaintenanceMode()) {
            require 'views/maintenance.php';
            return;
        }

        // Get active form configuration
        $activeForm = $this->formConfig->getActiveConfiguration();

        if (!$activeForm) {
            require 'views/templates/header.php';
            echo '<div class="alert alert-warning"><h2>Form Not Available</h2><p>The booking form is currently being configured. Please check back later.</p></div>';
            require 'views/templates/footer.php';
            return;
        }

        // Get company settings for branding
        $companyName = $this->companySettings->getCompanyName();
        $workingHours = $this->companySettings->getWorkingHours();

        require 'views/book/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $formData = $_POST;
            $bookingDateTime = trim($_POST['booking_datetime'] ?? '');

            // Remove non-form fields
            unset($formData['booking_datetime']);

            // Validate required fields
            if (empty($formData) || empty($bookingDateTime)) {
                header('Location: /book?error=missingdata');
                exit;
            }

            // Create booking with dynamic form data
            $result = $this->bookingModel->createBooking($formData, $bookingDateTime);

            if ($result['success']) {
                $accessCode = $result['access_code'];
                require 'views/book/success.php';
            } else {
                $errors = $result['errors'] ?? ['general' => 'Booking creation failed'];
                $errorMsg = urlencode(implode(', ', $errors));
                header('Location: /book?error=createfailed&details=' . $errorMsg);
                exit;
            }
        } else {
            header('Location: /book');
            exit;
        }
    }

    public function getAvailableTimes($date = '') {
        header('Content-Type: application/json');

        if (empty($date)) {
            echo json_encode(['error' => 'Date is required.']);
            return;
        }

        if (!DateTime::createFromFormat('Y-m-d', $date)) {
            echo json_encode(['error' => 'Invalid date format.']);
            return;
        }

        try {
            $availableSlots = $this->bookingModel->getAvailableSlotsForDate($date);
            echo json_encode($availableSlots);
        } catch (Exception $e) {
            error_log('Get available times error: ' . $e->getMessage());
            echo json_encode(['error' => 'Failed to get available times.']);
        }
    }

    public function getFormConfiguration() {
        header('Content-Type: application/json');

        try {
            $activeForm = $this->formConfig->getActiveConfiguration();

            if (!$activeForm) {
                echo json_encode(['error' => 'No active form configuration found.']);
                return;
            }

            // Return form configuration for AJAX requests
            echo json_encode([
                'success' => true,
                'form' => [
                    'fields' => $activeForm['fields'],
                    'settings' => $activeForm['settings']
                ]
            ]);
        } catch (Exception $e) {
            error_log('Get form config error: ' . $e->getMessage());
            echo json_encode(['error' => 'Failed to get form configuration.']);
        }
    }

    public function validateField() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Method not allowed.']);
            return;
        }

        try {
            $fieldName = $_POST['field_name'] ?? '';
            $fieldValue = $_POST['field_value'] ?? '';

            if (empty($fieldName)) {
                echo json_encode(['error' => 'Field name is required.']);
                return;
            }

            // Get form configuration
            $activeForm = $this->formConfig->getActiveConfiguration();
            if (!$activeForm) {
                echo json_encode(['error' => 'No active form found.']);
                return;
            }

            // Find field configuration
            $fieldConfig = null;
            foreach ($activeForm['fields'] as $field) {
                if ($field['name'] === $fieldName) {
                    $fieldConfig = $field;
                    break;
                }
            }

            if (!$fieldConfig) {
                echo json_encode(['error' => 'Field not found.']);
                return;
            }

            // Validate field
            $formValidation = new FormValidationService();
            $isValid = $formValidation->validateField($fieldConfig, $fieldValue);

            echo json_encode([
                'valid' => $isValid['valid'],
                'errors' => $isValid['errors'] ?? []
            ]);

        } catch (Exception $e) {
            error_log('Field validation error: ' . $e->getMessage());
            echo json_encode(['error' => 'Validation failed.']);
        }
    }

    public function checkAvailability() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Method not allowed.']);
            return;
        }

        try {
            $dateTime = $_POST['datetime'] ?? '';

            if (empty($dateTime)) {
                echo json_encode(['error' => 'DateTime is required.']);
                return;
            }

            $isAvailable = $this->bookingModel->isTimeSlotAvailable($dateTime);

            echo json_encode([
                'available' => $isAvailable,
                'message' => $isAvailable ? 'Time slot is available' : 'Time slot is already booked'
            ]);

        } catch (Exception $e) {
            error_log('Availability check error: ' . $e->getMessage());
            echo json_encode(['error' => 'Failed to check availability.']);
        }
    }

    public function preview() {
        // Preview form without submitting (for testing)
        if (!isset($_SESSION['user_id']) || !in_array('manage_forms', $_SESSION['user_permissions'] ?? [])) {
            header('Location: /book');
            exit;
        }

        $formId = $_GET['form_id'] ?? '';
        if (empty($formId)) {
            header('Location: /admin/form_builder');
            exit;
        }

        $formConfig = $this->formConfig->getConfigurationById($formId);
        if (!$formConfig) {
            header('Location: /admin/form_builder?error=formnotfound');
            exit;
        }

        // Override active form for preview
        $activeForm = $formConfig;
        $companyName = $this->companySettings->getCompanyName();
        $workingHours = $this->companySettings->getWorkingHours();
        $isPreview = true;

        require 'views/book/index.php';
    }
}
?>
