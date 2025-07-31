<?php
// Enhanced View Controller with improved booking management

class ViewController {
    private $bookingModel;
    private $formConfig;
    private $companySettings;

    public function __construct() {
        $this->bookingModel = new Booking();
        $this->formConfig = new FormConfiguration();
        $this->companySettings = new CompanySettings();
    }

    public function index() {
        try {
            // Get public bookings
            $publicBookings = $this->bookingModel->getPublicBookings();

            // Convert to array for easier handling
            $bookingsArray = [];
            foreach ($publicBookings as $booking) {
                $bookingsArray[] = $booking;
            }

            require 'views/view/index.php';
        } catch (Exception $e) {
            error_log('View index error: ' . $e->getMessage());
            require 'views/templates/header.php';
            echo '<div class="alert alert-danger">Unable to load bookings. Please try again later.</div>';
            require 'views/templates/footer.php';
        }
    }

    public function find() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accessCode = trim($_POST['access_code'] ?? '');

            if (!empty($accessCode)) {
                header('Location: ' . BASE_PATH . '/view/details/' . urlencode($accessCode));
            } else {
                header('Location: ' . BASE_PATH . '/view?error=notfound');
            }
        } else {
            header('Location: ' . BASE_PATH . '/view');
        }
        exit;
    }

    public function details($accessCode = '') {
        if (empty($accessCode)) {
            header('Location: ' . BASE_PATH . '/view');
            exit;
        }

        try {
            $booking = $this->bookingModel->getBookingByAccessCode($accessCode);

            if ($booking) {
                // Get current form configuration for field labels
                $activeForm = $this->formConfig->getActiveConfiguration();
                $fieldLabels = [];

                if ($activeForm && isset($activeForm['fields'])) {
                    foreach ($activeForm['fields'] as $field) {
                        $fieldLabels[$field['name']] = $field['label'];
                    }
                }

                // Check if booking updates are allowed
                $allowUpdates = $this->companySettings->allowBookingUpdates();

                require 'views/view/details.php';
            } else {
                header('Location: ' . BASE_PATH . '/view?error=notfound');
                exit;
            }
        } catch (Exception $e) {
            error_log('View details error: ' . $e->getMessage());
            header('Location: ' . BASE_PATH . '/view?error=systemerror');
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . '/view');
            exit;
        }

        try {
            $accessCode = trim($_POST['access_code'] ?? '');
            $newDateTime = trim($_POST['booking_datetime'] ?? '');

            if (empty($accessCode)) {
                header('Location: ' . BASE_PATH . '/view?error=missingcode');
                exit;
            }

            // Check if updates are allowed
            if (!$this->companySettings->allowBookingUpdates()) {
                header('Location: ' . BASE_PATH . '/view/details/' . urlencode($accessCode) . '?error=updatesdisabled');
                exit;
            }

            $success = false;

            if (!empty($newDateTime)) {
                // Update date/time only
                $success = $this->bookingModel->updateBooking($accessCode, $newDateTime);
            } else {
                // Check for form data updates
                $formData = $_POST;
                unset($formData['access_code'], $formData['booking_datetime']);

                if (!empty($formData)) {
                    // Update form data
                    $booking = $this->bookingModel->getBookingByAccessCode($accessCode);
                    if ($booking) {
                        $currentDateTime = $booking['booking_datetime']->toDateTime()->format('Y-m-d H:i:s');
                        $success = $this->bookingModel->updateBooking($accessCode, $currentDateTime, $formData);
                    }
                }
            }

            if ($success) {
                header('Location: ' . BASE_PATH . '/view/details/' . urlencode($accessCode) . '?success=updated&status=pending');
            } else {
                header('Location: ' . BASE_PATH . '/view/details/' . urlencode($accessCode) . '?error=updatefailed');
            }
        } catch (Exception $e) {
            error_log('Update booking error: ' . $e->getMessage());
            header('Location: ' . BASE_PATH . '/view/details/' . urlencode($accessCode ?? '') . '?error=systemerror');
        }
        exit;
    }

    public function cancel($accessCode = '') {
        if (empty($accessCode)) {
            header('Location: ' . BASE_PATH . '/view');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $success = $this->bookingModel->updateBookingStatus($accessCode, 'cancelled');

                if ($success) {
                    header('Location: ' . BASE_PATH . '/view?success=cancelled');
                } else {
                    header('Location: ' . BASE_PATH . '/view/details/' . urlencode($accessCode) . '?error=cancelfailed');
                }
            } catch (Exception $e) {
                error_log('Cancel booking error: ' . $e->getMessage());
                header('Location: ' . BASE_PATH . '/view/details/' . urlencode($accessCode) . '?error=systemerror');
            }
        } else {
            // Show confirmation page
            $booking = $this->bookingModel->getBookingByAccessCode($accessCode);
            if (!$booking) {
                header('Location: ' . BASE_PATH . '/view?error=notfound');
                exit;
            }

            require 'views/view/cancel.php';
        }
        exit;
    }

    public function getBookingJson($accessCode = '') {
        header('Content-Type: application/json');

        if (empty($accessCode)) {
            echo json_encode(['error' => 'Access code required']);
            return;
        }

        try {
            $booking = $this->bookingModel->getBookingByAccessCode($accessCode);

            if ($booking) {
                // Convert MongoDB objects to arrays for JSON
                $bookingData = [
                    'access_code' => $booking['access_code'],
                    'name' => $booking['name'] ?? '',
                    'email' => $booking['email'] ?? '',
                    'status' => $booking['status'],
                    'booking_datetime' => $booking['booking_datetime']->toDateTime()->format('c'),
                    'created_at' => $booking['created_at']->toDateTime()->format('c'),
                    'form_data' => $booking['form_data'] ?? []
                ];

                echo json_encode(['success' => true, 'booking' => $bookingData]);
            } else {
                echo json_encode(['error' => 'Booking not found']);
            }
        } catch (Exception $e) {
            error_log('Get booking JSON error: ' . $e->getMessage());
            echo json_encode(['error' => 'System error']);
        }
    }

    public function printBooking($accessCode = '') {
        if (empty($accessCode)) {
            header('Location: ' . BASE_PATH . '/view');
            exit;
        }

        try {
            $booking = $this->bookingModel->getBookingByAccessCode($accessCode);

            if ($booking) {
                $activeForm = $this->formConfig->getActiveConfiguration();
                $fieldLabels = [];

                if ($activeForm && isset($activeForm['fields'])) {
                    foreach ($activeForm['fields'] as $field) {
                        $fieldLabels[$field['name']] = $field['label'];
                    }
                }

                $companyName = $this->companySettings->getCompanyName();

                require 'views/view/print.php';
            } else {
                header('Location: ' . BASE_PATH . '/view?error=notfound');
                exit;
            }
        } catch (Exception $e) {
            error_log('Print booking error: ' . $e->getMessage());
            header('Location: ' . BASE_PATH . '/view?error=systemerror');
            exit;
        }
    }
}
?>
