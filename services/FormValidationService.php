<?php
// Form Validation Service for dynamic form validation

class FormValidationService {
    private $formConfig;

    public function __construct() {
        $this->formConfig = new FormConfiguration();
    }

    // Validate entire form submission
    public function validateSubmission($formData) {
        try {
            $activeForm = $this->formConfig->getActiveConfiguration();

            if (!$activeForm || !isset($activeForm['fields'])) {
                return ['valid' => false, 'errors' => ['No active form configuration found']];
            }

            $errors = [];
            $processedFields = [];

            // Validate each configured field
            foreach ($activeForm['fields'] as $field) {
                $fieldName = $field['name'];
                $fieldValue = $formData[$fieldName] ?? '';

                $validation = $this->validateField($field, $fieldValue);

                if (!$validation['valid']) {
                    $errors[$fieldName] = $validation['errors'];
                }

                $processedFields[] = $fieldName;
            }

            // Check for unexpected fields (security)
            foreach ($formData as $fieldName => $value) {
                if (!in_array($fieldName, $processedFields)) {
                    error_log("Unexpected form field submitted: $fieldName");
                    // Don't add to errors, just log for security monitoring
                }
            }

            return [
                'valid' => empty($errors),
                'errors' => $errors
            ];

        } catch (Exception $e) {
            error_log('Form validation error: ' . $e->getMessage());
            return ['valid' => false, 'errors' => ['Validation system error']];
        }
    }

    // Validate individual field
    public function validateField($fieldConfig, $value) {
        $errors = [];
        $fieldName = $fieldConfig['name'];
        $fieldType = $fieldConfig['type'];
        $fieldLabel = $fieldConfig['label'] ?? $fieldName;

        // Required field validation
        if (!empty($fieldConfig['required']) && $this->isEmpty($value)) {
            $errors[] = "$fieldLabel is required";
            return ['valid' => false, 'errors' => $errors];
        }

        // Skip other validations if field is empty and not required
        if ($this->isEmpty($value)) {
            return ['valid' => true, 'errors' => []];
        }

        // Type-specific validation
        switch ($fieldType) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "$fieldLabel must be a valid email address";
                }
                break;

            case 'phone':
                if (!$this->validatePhone($value)) {
                    $errors[] = "$fieldLabel must be a valid phone number";
                }
                break;

            case 'number':
                if (!is_numeric($value)) {
                    $errors[] = "$fieldLabel must be a number";
                } else {
                    // Min/max validation
                    if (isset($fieldConfig['min']) && $value < $fieldConfig['min']) {
                        $errors[] = "$fieldLabel must be at least {$fieldConfig['min']}";
                    }
                    if (isset($fieldConfig['max']) && $value > $fieldConfig['max']) {
                        $errors[] = "$fieldLabel must be no more than {$fieldConfig['max']}";
                    }
                }
                break;

            case 'text':
            case 'textarea':
                // Length validation
                if (isset($fieldConfig['min_length']) && strlen($value) < $fieldConfig['min_length']) {
                    $errors[] = "$fieldLabel must be at least {$fieldConfig['min_length']} characters";
                }
                if (isset($fieldConfig['max_length']) && strlen($value) > $fieldConfig['max_length']) {
                    $errors[] = "$fieldLabel must be no more than {$fieldConfig['max_length']} characters";
                }

                // Pattern validation
                if (isset($fieldConfig['pattern']) && !preg_match($fieldConfig['pattern'], $value)) {
                    $errors[] = "$fieldLabel format is invalid";
                }
                break;

            case 'select':
            case 'radio':
                if (!empty($fieldConfig['options']) && !in_array($value, $fieldConfig['options'])) {
                    $errors[] = "$fieldLabel contains an invalid selection";
                }
                break;

            case 'checkbox':
                if (!empty($fieldConfig['options'])) {
                    $values = is_array($value) ? $value : [$value];
                    foreach ($values as $val) {
                        if (!in_array($val, $fieldConfig['options'])) {
                            $errors[] = "$fieldLabel contains an invalid selection";
                            break;
                        }
                    }
                }
                break;

            case 'file':
                // File validation would be handled separately in file upload processing
                break;

            case 'date':
                if (!$this->validateDate($value)) {
                    $errors[] = "$fieldLabel must be a valid date";
                } else {
                    // Date range validation
                    if (isset($fieldConfig['min_date']) && $value < $fieldConfig['min_date']) {
                        $errors[] = "$fieldLabel must be on or after {$fieldConfig['min_date']}";
                    }
                    if (isset($fieldConfig['max_date']) && $value > $fieldConfig['max_date']) {
                        $errors[] = "$fieldLabel must be on or before {$fieldConfig['max_date']}";
                    }
                }
                break;

            case 'time':
                if (!$this->validateTime($value)) {
                    $errors[] = "$fieldLabel must be a valid time";
                }
                break;

            case 'datetime':
                if (!$this->validateDateTime($value)) {
                    $errors[] = "$fieldLabel must be a valid date and time";
                }
                break;
        }

        // Custom validation rules
        if (isset($fieldConfig['custom_validation'])) {
            $customErrors = $this->applyCustomValidation($fieldConfig['custom_validation'], $value, $fieldLabel);
            $errors = array_merge($errors, $customErrors);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    // Validate file upload
    public function validateFileUpload($fieldConfig, $file) {
        $errors = [];
        $fieldLabel = $fieldConfig['label'] ?? 'File';

        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = "$fieldLabel upload failed";
            return ['valid' => false, 'errors' => $errors];
        }

        // Check upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                if (!empty($fieldConfig['required'])) {
                    $errors[] = "$fieldLabel is required";
                }
                return ['valid' => empty($errors), 'errors' => $errors];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = "$fieldLabel is too large";
                break;
            default:
                $errors[] = "$fieldLabel upload failed";
                break;
        }

        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }

        // File size validation
        if (isset($fieldConfig['max_size'])) {
            $maxSize = $this->parseFileSize($fieldConfig['max_size']);
            if ($file['size'] > $maxSize) {
                $errors[] = "$fieldLabel is too large (max: {$fieldConfig['max_size']})";
            }
        }

        // File type validation
        if (isset($fieldConfig['allowed_types'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedTypes = is_array($fieldConfig['allowed_types'])
                ? $fieldConfig['allowed_types']
                : explode(',', $fieldConfig['allowed_types']);

            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = "$fieldLabel type is not allowed";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    // Validation helper methods
    private function isEmpty($value) {
        return $value === null || $value === '' || (is_array($value) && empty($value));
    }

    private function validatePhone($phone) {
        // Remove all non-numeric characters
        $numericPhone = preg_replace('/[^0-9]/', '', $phone);

        // Check if it's a valid length (7-15 digits)
        return strlen($numericPhone) >= 7 && strlen($numericPhone) <= 15;
    }

    private function validateDate($date) {
        try {
            $d = DateTime::createFromFormat('Y-m-d', $date);
            return $d && $d->format('Y-m-d') === $date;
        } catch (Exception $e) {
            return false;
        }
    }

    private function validateTime($time) {
        try {
            $t = DateTime::createFromFormat('H:i', $time);
            return $t && $t->format('H:i') === $time;
        } catch (Exception $e) {
            return false;
        }
    }

    private function validateDateTime($datetime) {
        try {
            $dt = DateTime::createFromFormat('Y-m-d H:i', $datetime);
            return $dt && $dt->format('Y-m-d H:i') === $datetime;
        } catch (Exception $e) {
            return false;
        }
    }

    private function parseFileSize($size) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = trim($size);
        $last = strtoupper(substr($size, -2));

        if (in_array($last, $units)) {
            $size = (int) substr($size, 0, -2);
            $unitIndex = array_search($last, $units);
            return $size * pow(1024, $unitIndex);
        }

        return (int) $size;
    }

    private function applyCustomValidation($customRules, $value, $fieldLabel) {
        $errors = [];

        if (is_array($customRules)) {
            foreach ($customRules as $rule) {
                switch ($rule['type']) {
                    case 'regex':
                        if (!preg_match($rule['pattern'], $value)) {
                            $errors[] = $rule['message'] ?? "$fieldLabel format is invalid";
                        }
                        break;

                    case 'length':
                        $length = strlen($value);
                        if (isset($rule['min']) && $length < $rule['min']) {
                            $errors[] = "$fieldLabel must be at least {$rule['min']} characters";
                        }
                        if (isset($rule['max']) && $length > $rule['max']) {
                            $errors[] = "$fieldLabel must be no more than {$rule['max']} characters";
                        }
                        break;

                    case 'unique':
                        // This would require database checking - implement if needed
                        break;
                }
            }
        }

        return $errors;
    }

    // Generate client-side validation rules for JavaScript
    public function getClientValidationRules($fieldConfig) {
        $rules = [];

        if (!empty($fieldConfig['required'])) {
            $rules['required'] = true;
        }

        switch ($fieldConfig['type']) {
            case 'email':
                $rules['email'] = true;
                break;

            case 'number':
                $rules['number'] = true;
                if (isset($fieldConfig['min'])) {
                    $rules['min'] = $fieldConfig['min'];
                }
                if (isset($fieldConfig['max'])) {
                    $rules['max'] = $fieldConfig['max'];
                }
                break;

            case 'text':
            case 'textarea':
                if (isset($fieldConfig['min_length'])) {
                    $rules['minlength'] = $fieldConfig['min_length'];
                }
                if (isset($fieldConfig['max_length'])) {
                    $rules['maxlength'] = $fieldConfig['max_length'];
                }
                if (isset($fieldConfig['pattern'])) {
                    $rules['pattern'] = $fieldConfig['pattern'];
                }
                break;
        }

        return $rules;
    }

    // Sanitize form data
    public function sanitizeValue($value, $fieldType) {
        switch ($fieldType) {
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);

            case 'number':
                return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            case 'phone':
                // Keep only numbers, spaces, hyphens, parentheses, and plus
                return preg_replace('/[^0-9\s\-\(\)\+]/', '', $value);

            case 'text':
            case 'textarea':
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');

            default:
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }
}
