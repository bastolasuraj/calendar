<?php
// Validation Service for dynamic form validation

class ValidationService {

    private $companySettings;

    public function __construct() {
        $this->companySettings = new CompanySettings();
    }

    // Validate entire form data against field definitions
    public function validateFormData($formData, $fields) {
        $errors = [];
        $warnings = [];

        foreach ($fields as $field) {
            $fieldId = $field['id'];
            $value = $formData[$fieldId] ?? '';

            $fieldResult = $this->validateSingleField($fieldId, $value, $field);

            if (!$fieldResult['valid']) {
                $errors[$fieldId] = $fieldResult['errors'];
            }

            if (isset($fieldResult['warnings']) && !empty($fieldResult['warnings'])) {
                $warnings[$fieldId] = $fieldResult['warnings'];
            }
        }

        // Additional business logic validations
        $businessValidation = $this->validateBusinessRules($formData);
        if (!$businessValidation['valid']) {
            $errors = array_merge($errors, $businessValidation['errors']);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    // Validate single field
    public function validateSingleField($fieldId, $value, $fieldDefinition) {
        $errors = [];
        $warnings = [];

        $required = $fieldDefinition['required'] ?? false;
        $type = $fieldDefinition['type'] ?? 'text';
        $validation = $fieldDefinition['validation'] ?? [];

        // Check if field is required and empty
        if ($required && $this->isEmpty($value)) {
            $errors[] = $fieldDefinition['label'] . ' is required';
            return ['valid' => false, 'errors' => $errors];
        }

        // Skip further validation if field is empty and not required
        if (!$required && $this->isEmpty($value)) {
            return ['valid' => true, 'errors' => [], 'warnings' => []];
        }

        // Type-specific validation
        switch ($type) {
            case 'email':
                $result = $this->validateEmail($value);
                break;
            case 'phone':
                $result = $this->validatePhone($value);
                break;
            case 'text':
            case 'textarea':
                $result = $this->validateText($value, $validation);
                break;
            case 'number':
                $result = $this->validateNumber($value, $validation);
                break;
            case 'date':
                $result = $this->validateDate($value, $validation);
                break;
            case 'datetime':
                $result = $this->validateDateTime($value, $validation);
                break;
            case 'select':
                $result = $this->validateSelect($value, $fieldDefinition['options'] ?? []);
                break;
            case 'radio':
                $result = $this->validateRadio($value, $fieldDefinition['options'] ?? []);
                break;
            case 'checkbox':
                $result = $this->validateCheckbox($value, $fieldDefinition['options'] ?? [], $validation);
                break;
            case 'file':
                $result = $this->validateFile($value, $validation);
                break;
            default:
                $result = ['valid' => true, 'errors' => [], 'warnings' => []];
        }

        if (!$result['valid']) {
            $errors = array_merge($errors, $result['errors']);
        }

        if (isset($result['warnings'])) {
            $warnings = array_merge($warnings, $result['warnings']);
        }

        // Custom validation rules
        if (isset($validation['pattern'])) {
            $patternResult = $this->validatePattern($value, $validation['pattern']);
            if (!$patternResult['valid']) {
                $errors = array_merge($errors, $patternResult['errors']);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    // Validate field definition (for form builder)
    public function validateFieldDefinition($field) {
        $errors = [];

        // Required properties
        $requiredProps = ['id', 'type', 'label'];
        foreach ($requiredProps as $prop) {
            if (!isset($field[$prop]) || empty($field[$prop])) {
                $errors[] = "Field property '{$prop}' is required";
            }
        }

        // Validate field ID format
        if (isset($field['id'])) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field['id'])) {
                $errors[] = 'Field ID must contain only letters, numbers, and underscores, and start with a letter or underscore';
            }
        }

        // Validate field type
        $allowedTypes = array_keys(FormConfiguration::getFieldTypes());
        if (isset($field['type']) && !in_array($field['type'], $allowedTypes)) {
            $errors[] = 'Invalid field type: ' . $field['type'];
        }

        // Validate options for fields that require them
        $optionRequiredTypes = ['select', 'radio', 'checkbox'];
        if (isset($field['type']) && in_array($field['type'], $optionRequiredTypes)) {
            if (!isset($field['options']) || !is_array($field['options']) || empty($field['options'])) {
                $errors[] = 'Options are required for ' . $field['type'] . ' fields';
            } else {
                foreach ($field['options'] as $option) {
                    if (!isset($option['value']) || !isset($option['label'])) {
                        $errors[] = 'Each option must have both value and label properties';
                        break;
                    }
                }
            }
        }

        // Validate validation rules
        if (isset($field['validation']) && is_array($field['validation'])) {
            $validationErrors = $this->validateValidationRules($field['validation'], $field['type']);
            $errors = array_merge($errors, $validationErrors);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    // Individual field type validators
    private function validateEmail($value) {
        $errors = [];
        $warnings = [];

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check email length
        if (strlen($value) > 254) {
            $errors[] = 'Email address is too long';
        }

        // Check for blocked domains
        $emailConfig = EmailConfig::getValidationRules();
        $domain = strtolower(substr(strrchr($value, "@"), 1));

        if (in_array($domain, $emailConfig['blocked_domains'])) {
            $errors[] = 'Email domain is not allowed';
        }

        // Warning for common typos
        $commonTypos = ['gmail.co', 'yahoo.co', 'hotmail.co', 'outlook.co'];
        if (in_array($domain, $commonTypos)) {
            $warnings[] = 'Please check your email domain - did you mean .com?';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    private function validatePhone($value) {
        $errors = [];

        // Remove all non-numeric characters for validation
        $numericPhone = preg_replace('/[^0-9]/', '', $value);

        // Check minimum length (10 digits for North American numbers)
        if (strlen($numericPhone) < 10) {
            $errors[] = 'Phone number must be at least 10 digits';
        }

        // Check maximum length
        if (strlen($numericPhone) > 15) {
            $errors[] = 'Phone number is too long';
        }

        // Basic format validation (allow various formats)
        if (!preg_match('/^[\+]?[0-9\s\-\(\)\.]{10,20}$/', $value)) {
            $errors[] = 'Please enter a valid phone number';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function validateText($value, $validation) {
        $errors = [];

        if (isset($validation['minLength'])) {
            if (strlen($value) < $validation['minLength']) {
                $errors[] = 'Must be at least ' . $validation['minLength'] . ' characters long';
            }
        }

        if (isset($validation['maxLength'])) {
            if (strlen($value) > $validation['maxLength']) {
                $errors[] = 'Must be no more than ' . $validation['maxLength'] . ' characters long';
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function validateNumber($value, $validation) {
        $errors = [];

        if (!is_numeric($value)) {
            $errors[] = 'Must be a valid number';
            return ['valid' => false, 'errors' => $errors];
        }

        $numValue = (float)$value;

        if (isset($validation['min'])) {
            if ($numValue < $validation['min']) {
                $errors[] = 'Must be at least ' . $validation['min'];
            }
        }

        if (isset($validation['max'])) {
            if ($numValue > $validation['max']) {
                $errors[] = 'Must be no more than ' . $validation['max'];
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function validateDate($value, $validation) {
        $errors = [];

        $date = DateTime::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            $errors[] = 'Please enter a valid date (YYYY-MM-DD)';
            return ['valid' => false, 'errors' => $errors];
        }

        if (isset($validation['minDate'])) {
            $minDate = new DateTime($validation['minDate']);
            if ($date < $minDate) {
                $errors[] = 'Date must be on or after ' . $minDate->format('Y-m-d');
            }
        }

        if (isset($validation['maxDate'])) {
            $maxDate = new DateTime($validation['maxDate']);
            if ($date > $maxDate) {
                $errors[] = 'Date must be on or before ' . $maxDate->format('Y-m-d');
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function validateDateTime($value, $validation) {
        $errors = [];

        $dateTime = DateTime::createFromFormat('Y-m-d H:i', $value);
        if (!$dateTime) {
            $errors[] = 'Please enter a valid date and time (YYYY-MM-DD HH:MM)';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check if datetime is in the past
        if ($dateTime <= new DateTime()) {
            $errors[] = 'Date and time must be in the future';
        }

        // Business logic validation
        $bookingSettings = $this->companySettings->getBookingSettings();

        // Check advance booking limits
        $maxAdvanceDays = $bookingSettings['max_advance_booking_days'];
        $maxDate = new DateTime();
        $maxDate->modify("+{$maxAdvanceDays} days");

        if ($dateTime > $maxDate) {
            $errors[] = "Bookings can only be made up to {$maxAdvanceDays} days in advance";
        }

        $minAdvanceHours = $bookingSettings['min_advance_booking_hours'];
        $minDate = new DateTime();
        $minDate->modify("+{$minAdvanceHours} hours");

        if ($dateTime < $minDate) {
            $errors[] = "Bookings must be made at least {$minAdvanceHours} hours in advance";
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function validateSelect($value, $options) {
        $errors = [];

        $validValues = array_column($options, 'value');
        if (!in_array($value, $validValues)) {
            $errors[] = 'Please select a valid option';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function validateRadio($value, $options) {
        return $this->validateSelect($value, $options);
    }

    private function validateCheckbox($value, $options, $validation) {
        $errors = [];

        if (!is_array($value)) {
            $value = [$value];
        }

        $validValues = array_column($options, 'value');
        foreach ($value as $selectedValue) {
            if (!in_array($selectedValue, $validValues)) {
                $errors[] = 'Invalid option selected';
                break;
            }
        }

        if (isset($validation['minChecked'])) {
            if (count($value) < $validation['minChecked']) {
                $errors[] = 'Please select at least ' . $validation['minChecked'] . ' option(s)';
            }
        }

        if (isset($validation['maxChecked'])) {
            if (count($value) > $validation['maxChecked']) {
                $errors[] = 'Please select no more than ' . $validation['maxChecked'] . ' option(s)';
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function validateFile($filePath, $validation) {
        $errors = [];

        if (!file_exists($filePath)) {
            $errors[] = 'File upload failed';
            return ['valid' => false, 'errors' => $errors];
        }

        $fileInfo = pathinfo($filePath);
        $fileSize = filesize($filePath);
        $fileExtension = strtolower($fileInfo['extension']);

        // Validate file type
        if (isset($validation['fileTypes']) && is_array($validation['fileTypes'])) {
            if (!in_array($fileExtension, $validation['fileTypes'])) {
                $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $validation['fileTypes']);
            }
        }

        // Validate file size
        $maxSize = 5 * 1024 * 1024; // 5MB default
        if (isset($validation['maxSize'])) {
            $maxSize = $this->parseFileSize($validation['maxSize']);
        }

        if ($fileSize > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size of ' . $this->formatFileSize($maxSize);
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function validatePattern($value, $pattern) {
        $errors = [];

        if (!preg_match('/' . str_replace('/', '\/', $pattern) . '/', $value)) {
            $errors[] = 'Format is not valid';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    // Business rules validation
    private function validateBusinessRules($formData) {
        $errors = [];

        // Check for duplicate bookings (same email, same time)
        if (isset($formData['email']) && isset($formData['booking_datetime'])) {
            $booking = new Booking();
            if (!$booking->isTimeSlotAvailable($formData['booking_datetime'])) {
                $errors['booking_datetime'] = ['This time slot is no longer available'];
            }
        }

        // Add more business rules as needed

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    // Helper methods
    private function isEmpty($value) {
        if (is_array($value)) {
            return empty($value);
        }
        return $value === '' || $value === null;
    }

    private function validateValidationRules($rules, $fieldType) {
        $errors = [];
        $fieldTypes = FormConfiguration::getFieldTypes();

        if (!isset($fieldTypes[$fieldType])) {
            return ['Invalid field type'];
        }

        $allowedRules = $fieldTypes[$fieldType]['validation'];

        foreach ($rules as $rule => $value) {
            if (!in_array($rule, $allowedRules)) {
                $errors[] = "Validation rule '{$rule}' is not allowed for field type '{$fieldType}'";
            }
        }

        return $errors;
    }

    private function parseFileSize($sizeString) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $number = (int)$sizeString;
        $unit = strtoupper(substr($sizeString, -2));

        if (in_array($unit, $units)) {
            $power = array_search($unit, $units);
            return $number * pow(1024, $power);
        }

        return $number; // Assume bytes if no unit
    }

    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    // Sanitize input data
    public function sanitizeFormData($formData) {
        $sanitized = [];

        foreach ($formData as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = array_map(function($item) {
                    return trim(htmlspecialchars($item, ENT_QUOTES, 'UTF-8'));
                }, $value);
            } else {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }

        return $sanitized;
    }
}
