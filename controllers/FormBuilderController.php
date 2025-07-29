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

        // Get all form configurations
        $forms = $this->formConfig->getAllConfigurations();
        $activeForm = $this->formConfig->getActiveConfiguration();

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
                header('Location: ' . BASE_PATH . '/admin/form-builder?error=missingdata');
                exit;
            }

            // Validate fields
            foreach ($fields as $field) {
                if (!$this->formConfig->validateFieldConfig($field)) {
                    header('Location: ' . BASE_PATH . '/admin/form-builder?error=invalidfield');
                    exit;
                }
            }

            $result = $this->formConfig->saveConfiguration($name, $fields, $settings);

            if ($result) {
                header('Location: ' . BASE_PATH . '/admin/form-builder?success=created');
            } else {
                header('Location: ' . BASE_PATH . '/admin/form-builder?error=savefailed');
            }
        } else {
            // Show create form
            $fieldTypes = $this->getFieldTypes();
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
            header('Location: ' . BASE_PATH . '/admin/form-builder');
            exit;
        }

        $form = $this->formConfig->getConfigurationById($id);
        if (!$form) {
            header('Location: ' . BASE_PATH . '/admin/form-builder?error=formnotfound');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $fields = json_decode($_POST['fields'], true);
            $settings = json_decode($_POST['settings'], true);

            if (empty($name) || empty($fields)) {
                header('Location: ' . BASE_PATH . '/admin/form-builder/edit/' . $id . '?error=missingdata');
                exit;
            }

            // Save as new version
            $result = $this->formConfig->saveConfiguration($name, $fields, $settings);

            if ($result) {
                header('Location: ' . BASE_PATH . '/admin/form-builder?success=updated');
            } else {
                header('Location: ' . BASE_PATH . '/admin/form-builder/edit/' . $id . '?error=savefailed');
            }
        } else {
            $fieldTypes = $this->getFieldTypes();
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
            header('Location: ' . BASE_PATH . '/admin/form-builder');
            exit;
        }

        if ($this->formConfig->activateConfiguration($id)) {
            header('Location: ' . BASE_PATH . '/admin/form-builder?success=activated');
        } else {
            header('Location: ' . BASE_PATH . '/admin/form-builder?error=activatefailed');
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
            header('Location: ' . BASE_PATH . '/admin/form-builder');
            exit;
        }

        if ($this->formConfig->deleteConfiguration($id)) {
            header('Location: ' . BASE_PATH . '/admin/form-builder?success=deleted');
        } else {
            header('Location: ' . BASE_PATH . '/admin/form-builder?error=deletefailed');
        }
        exit;
    }

    public function preview($id = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_forms')) {
            $this->accessDenied();
            return;
        }

        if (empty($id)) {
            header('Location: ' . BASE_PATH . '/admin/form-builder');
            exit;
        }

        // Redirect to book controller preview
        header('Location: ' . BASE_PATH . '/book/preview?form_id=' . $id);
        exit;
    }

    public function getFieldTypes() {
        header('Content-Type: application/json');

        $fieldTypes = [
            'text' => [
                'label' => 'Text Input',
                'icon' => 'type',
                'properties' => ['placeholder', 'min_length', 'max_length', 'pattern']
            ],
            'email' => [
                'label' => 'Email',
                'icon' => 'mail',
                'properties' => ['placeholder']
            ],
            'phone' => [
                'label' => 'Phone Number',
                'icon' => 'phone',
                'properties' => ['placeholder', 'pattern']
            ],
            'number' => [
                'label' => 'Number',
                'icon' => 'hash',
                'properties' => ['placeholder', 'min', 'max', 'step']
            ],
            'textarea' => [
                'label' => 'Text Area',
                'icon' => 'align-left',
                'properties' => ['placeholder', 'rows', 'min_length', 'max_length']
            ],
            'select' => [
                'label' => 'Dropdown',
                'icon' => 'chevron-down',
                'properties' => ['options', 'multiple']
            ],
            'radio' => [
                'label' => 'Radio Buttons',
                'icon' => 'circle',
                'properties' => ['options']
            ],
            'checkbox' => [
                'label' => 'Checkboxes',
                'icon' => 'check-square',
                'properties' => ['options']
            ],
            'file' => [
                'label' => 'File Upload',
                'icon' => 'upload',
                'properties' => ['allowed_types', 'max_size', 'multiple']
            ],
            'date' => [
                'label' => 'Date',
                'icon' => 'calendar',
                'properties' => ['min_date', 'max_date']
            ],
            'time' => [
                'label' => 'Time',
                'icon' => 'clock',
                'properties' => ['min_time', 'max_time']
            ],
            'datetime' => [
                'label' => 'Date & Time',
                'icon' => 'calendar',
                'properties' => ['min_datetime', 'max_datetime']
            ]
        ];

        echo json_encode($fieldTypes);
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
?>
