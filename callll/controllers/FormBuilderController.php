<?php
// Form Builder Controller for drag-and-drop form creation

class FormBuilderController {
    private $formConfig;
    private $userModel;

    public function __construct() {
        $this->formConfig = new FormConfiguration();
        $this->userModel = new User();
    }

    public function index() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_forms')) {
            $this->accessDenied();
            return;
        }

        // Get all form configurations and convert the MongoDB Cursor to an array immediately.
        $configurations = $this->formConfig->getAllConfigurations()->toArray();
        $activeForm = $this->formConfig->getActiveConfiguration();

        // FIX: Define fieldTypes here to be passed to the view
        $fieldTypes = [
            'text' => [
                'label' => 'Text Input',
                'icon' => 'bi bi-type',
                'properties' => ['placeholder', 'min_length', 'max_length', 'pattern']
            ],
            'email' => [
                'label' => 'Email',
                'icon' => 'bi bi-envelope',
                'properties' => ['placeholder']
            ],
            'phone' => [
                'label' => 'Phone Number',
                'icon' => 'bi bi-phone',
                'properties' => ['placeholder', 'pattern']
            ],
            'number' => [
                'label' => 'Number',
                'icon' => 'bi bi-hash',
                'properties' => ['placeholder', 'min', 'max', 'step']
            ],
            'textarea' => [
                'label' => 'Text Area',
                'icon' => 'bi bi-text-left',
                'properties' => ['placeholder', 'rows', 'min_length', 'max_length']
            ],
            'select' => [
                'label' => 'Dropdown',
                'icon' => 'bi bi-chevron-down',
                'properties' => ['options', 'multiple']
            ],
            'radio' => [
                'label' => 'Radio Buttons',
                'icon' => 'bi bi-circle',
                'properties' => ['options']
            ],
            'checkbox' => [
                'label' => 'Checkboxes',
                'icon' => 'bi bi-check-square',
                'properties' => ['options']
            ],
            'file' => [
                'label' => 'File Upload',
                'icon' => 'bi bi-upload',
                'properties' => ['allowed_types', 'max_size', 'multiple']
            ],
            'date' => [
                'label' => 'Date',
                'icon' => 'bi bi-calendar',
                'properties' => ['min_date', 'max_date']
            ],
            'time' => [
                'label' => 'Time',
                'icon' => 'bi bi-clock',
                'properties' => ['min_time', 'max_time']
            ],
            'datetime' => [
                'label' => 'Date & Time',
                'icon' => 'bi bi-calendar',
                'properties' => ['min_datetime', 'max_datetime']
            ]
        ];

        require 'views/admin/form_builder.php';
    }

    public function create() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_forms')) {
            $this->accessDenied();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $fields = json_decode($_POST['fields'], true);
            $settings = json_decode($_POST['settings'], true);

            if (empty($name) || empty($fields)) {
                header('Location: ' . BASE_PATH . '/admin/form_builder?error=missingdata');
                exit;
            }

            // Validate fields
            foreach ($fields as $field) {
                if (!$this->formConfig->validateFieldConfig($field)) {
                    header('Location: ' . BASE_PATH . '/admin/form_builder?error=invalidfield');
                    exit;
                }
            }

            $result = $this->formConfig->saveConfiguration($name, $fields, $settings);

            if ($result) {
                header('Location: ' . BASE_PATH . '/admin/form_builder?success=created');
            } else {
                header('Location: ' . BASE_PATH . '/admin/form_builder?error=savefailed');
            }
        } else {
            // Show create form
            // This part is for `create_form.php` which is not the main form builder view.
            // Ensure fieldTypes is defined here too if this view needs it.
            $fieldTypes = $this->getStaticFieldTypes(); // Use a new helper method
            require 'views/admin/create_form.php';
        }
        exit;
    }

    public function edit($id = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_forms')) {
            $this->accessDenied();
            return;
        }

        if (empty($id)) {
            header('Location: ' . BASE_PATH . '/admin/form_builder');
            exit;
        }

        $form = $this->formConfig->getConfigurationById($id);
        if (!$form) {
            header('Location: ' . BASE_PATH . '/admin/form_builder?error=formnotfound');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $fields = json_decode($_POST['fields'], true);
            $settings = json_decode($_POST['settings'], true);

            if (empty($name) || empty($fields)) {
                header('Location: ' . BASE_PATH . '/admin/form_builder/edit/' . $id . '?error=missingdata');
                exit;
            }

            // Save as new version
            $result = $this->formConfig->saveConfiguration($name, $fields, $settings);

            if ($result) {
                header('Location: ' . BASE_PATH . '/admin/form_builder?success=updated');
            } else {
                header('Location: ' . BASE_PATH . '/admin/form_builder/edit/' . $id . '?error=savefailed');
            }
        } else {
            // Ensure fieldTypes is defined here too if this view needs it.
            $fieldTypes = $this->getStaticFieldTypes(); // Use a new helper method
            require 'views/admin/edit_form.php';
        }
        exit;
    }

    public function activate($id = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_forms')) {
            $this->accessDenied();
            return;
        }

        if (empty($id)) {
            header('Location: ' . BASE_PATH . '/admin/form_builder');
            exit;
        }

        if ($this->formConfig->activateConfiguration($id)) {
            header('Location: ' . BASE_PATH . '/admin/form_builder?success=activated');
        } else {
            header('Location: ' . BASE_PATH . '/admin/form_builder?error=activatefailed');
        }
        exit;
    }

    public function delete($id = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_forms')) {
            $this->accessDenied();
            return;
        }

        if (empty($id)) {
            header('Location: ' . BASE_PATH . '/admin/form_builder');
            exit;
        }

        if ($this->formConfig->deleteConfiguration($id)) {
            header('Location: ' . BASE_PATH . '/admin/form_builder?success=deleted');
        } else {
            header('Location: ' . BASE_PATH . '/admin/form_builder?error=deletefailed');
        }
        exit;
    }

    public function preview() { // Remove $id parameter as per JavaScript
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_forms')) {
            $this->accessDenied();
            return;
        }

        header('Content-Type: application/json'); // Set header for JSON response

        // FIX: The JS is sending fields in the POST body for preview, not ID in URL
        $fields = json_decode(file_get_contents('php://input'), true)['fields'] ?? [];

        if (empty($fields)) {
            echo json_encode(['success' => false, 'error' => 'No fields provided for preview.']);
            exit;
        }

        try {
            $formRenderer = new FormRenderer();
            $htmlPreview = $formRenderer->renderPreview($fields);

            echo json_encode(['success' => true, 'html' => $htmlPreview]);
            exit;

        } catch (Exception $e) {
            error_log('Form preview generation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to generate preview: ' . $e->getMessage()]);
            exit;
        }
    }
    public function getConfiguration($id = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_forms')) {
            $this->accessDenied();
            return;
        }

        header('Content-Type: application/json');

        if (empty($id)) {
            echo json_encode(['success' => false, 'error' => 'Form ID is required.']);
            exit;
        }

        try {
            $form = $this->formConfig->getConfigurationById($id);

            if ($form) {
                // Convert MongoDB BSON ObjectId to string for JSON compatibility
                if (isset($form['_id'])) {
                    $form['_id'] = (string)$form['_id'];
                }
                // Convert MongoDB BSON UTCDateTime objects to string
                if (isset($form['created_at'])) {
                    $form['created_at'] = $form['created_at']->toDateTime()->format('c');
                }
                // Add any other BSON object conversions needed

                echo json_encode(['success' => true, 'configuration' => $form]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Form configuration not found.']);
            }
        } catch (Exception $e) {
            error_log('Error getting form configuration: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to retrieve form configuration.']);
        }
        exit;
    }

    public function getFieldTypes() {
        header('Content-Type: application/json');
        echo json_encode($this->getStaticFieldTypes()); // Use the new helper method
    }

    // New helper method to return the static field types array
    private function getStaticFieldTypes() {
        return [
            'text' => [
                'label' => 'Text Input',
                'icon' => 'bi bi-type',
                'properties' => ['placeholder', 'min_length', 'max_length', 'pattern']
            ],
            'email' => [
                'label' => 'Email',
                'icon' => 'bi bi-envelope',
                'properties' => ['placeholder']
            ],
            'phone' => [
                'label' => 'Phone Number',
                'icon' => 'bi bi-phone',
                'properties' => ['placeholder', 'pattern']
            ],
            'number' => [
                'label' => 'Number',
                'icon' => 'bi bi-hash',
                'properties' => ['placeholder', 'min', 'max', 'step']
            ],
            'textarea' => [
                'label' => 'Text Area',
                'icon' => 'bi bi-text-left',
                'properties' => ['placeholder', 'rows', 'min_length', 'max_length']
            ],
            'select' => [
                'label' => 'Dropdown',
                'icon' => 'bi bi-chevron-down',
                'properties' => ['options', 'multiple']
            ],
            'radio' => [
                'label' => 'Radio Buttons',
                'icon' => 'bi bi-circle',
                'properties' => ['options']
            ],
            'checkbox' => [
                'label' => 'Checkboxes',
                'icon' => 'bi bi-check-square',
                'properties' => ['options']
            ],
            'file' => [
                'label' => 'File Upload',
                'icon' => 'bi bi-upload',
                'properties' => ['allowed_types', 'max_size', 'multiple']
            ],
            'date' => [
                'label' => 'Date',
                'icon' => 'bi bi-calendar',
                'properties' => ['min_date', 'max_date']
            ],
            'time' => [
                'label' => 'Time',
                'icon' => 'bi bi-clock',
                'properties' => ['min_time', 'max_time']
            ],
            'datetime' => [
                'label' => 'Date & Time',
                'icon' => 'bi bi-calendar',
                'properties' => ['min_datetime', 'max_datetime']
            ]
        ];
    }

    public function validateForm() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            $fields = json_decode($_POST['fields'], true);

            if (!$fields) {
                echo json_encode(['valid' => false, 'errors' => ['Invalid form data']]);
                return;
            }

            $errors = [];
            $fieldNames = [];

            foreach ($fields as $index => $field) {
                // Validate field structure
                if (!$this->formConfig->validateFieldConfig($field)) {
                    $errors[] = "Field " . ($index + 1) . " has invalid configuration";
                    continue;
                }

                // Check for duplicate field names
                if (in_array($field['name'], $fieldNames)) {
                    $errors[] = "Duplicate field name: " . $field['name'];
                } else {
                    $fieldNames[] = $field['name'];
                }

                // Validate field-specific properties
                if ($field['type'] === 'select' || $field['type'] === 'radio' || $field['type'] === 'checkbox') {
                    if (empty($field['options']) || !is_array($field['options'])) {
                        $errors[] = "Field '" . $field['label'] . "' requires options";
                    }
                }
            }

            // Check for at least one email field
            $hasEmail = false;
            foreach ($fields as $field) {
                if ($field['type'] === 'email' ||
                    (isset($field['validation']) && $field['validation'] === 'email')) {
                    $hasEmail = true;
                    break;
                }
            }

            if (!$hasEmail) {
                $errors[] = "Form must contain at least one email field for notifications";
            }

            echo json_encode([
                'valid' => empty($errors),
                'errors' => $errors
            ]);

        } catch (Exception $e) {
            error_log('Form validation error: ' . $e->getMessage());
            echo json_encode(['valid' => false, 'errors' => ['Validation failed']]);
        }
    }

    public function getAnalytics($days = 30) {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('view_analytics')) {
            $this->accessDenied();
            return;
        }

        header('Content-Type: application/json');

        try {
            $analytics = $this->formConfig->getFormAnalytics($days);
            echo json_encode($analytics);
        } catch (Exception $e) {
            error_log('Form analytics error: ' . $e->getMessage());
            echo json_encode(['error' => 'Failed to get analytics']);
        }
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