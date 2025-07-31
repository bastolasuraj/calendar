<?php
// Form Renderer Service for dynamic form generation

class FormRenderer {

    private $companySettings;

    public function __construct() {
        $this->companySettings = new CompanySettings();
    }

    // Render complete booking form
    public function renderBookingForm($formConfig) {
        if (!$formConfig || !isset($formConfig['fields'])) {
            return $this->renderFallbackForm();
        }

        $html = '';
        $html .= $this->renderFormStart($formConfig);

        // Sort fields by order
        $fields = $formConfig['fields'];
        usort($fields, function($a, $b) {
            return ($a['order'] ?? 0) - ($b['order'] ?? 0);
        });

        foreach ($fields as $field) {
            $html .= $this->renderField($field);
        }

        // Add booking date/time picker (always required)
        $html .= $this->renderDateTimePicker();

        $html .= $this->renderFormEnd($formConfig);

        return $html;
    }

    // Render form for preview (admin)
    public function renderPreview($fields) {
        $html = '<div class="form-preview-container">';
        $html .= '<form class="preview-form">';

        // Sort fields by order
        usort($fields, function($a, $b) {
            return ($a['order'] ?? 0) - ($b['order'] ?? 0);
        });

        foreach ($fields as $field) {
            $html .= $this->renderField($field, true);
        }

        // Always show date/time picker in preview
        $html .= '<div class="mb-3">';
        $html .= '<label class="form-label">Booking Date & Time <span class="text-danger">*</span></label>';
        $html .= '<div id="calendar-container" class="calendar-preview"></div>';
        $html .= '<div id="time-slots" class="mt-2" style="display:none;"></div>';
        $html .= '</div>';

        $html .= '<button type="button" class="btn btn-primary disabled">Submit Booking (Preview)</button>';
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    // Render individual field
    public function renderField($field, $isPreview = false) {
        $fieldId = htmlspecialchars($field['id']);
        $fieldType = $field['type'];
        $label = htmlspecialchars($field['label']);
        $placeholder = htmlspecialchars($field['placeholder'] ?? '');
        $required = $field['required'] ?? false;
        $validation = $field['validation'] ?? [];

        $html = '<div class="mb-3">';
        $html .= '<label for="' . $fieldId . '" class="form-label">';
        $html .= $label;
        if ($required) {
            $html .= ' <span class="text-danger">*</span>';
        }
        $html .= '</label>';

        switch ($fieldType) {
            case 'text':
                $html .= $this->renderTextInput($fieldId, $placeholder, $required, $validation, $isPreview);
                break;
            case 'email':
                $html .= $this->renderEmailInput($fieldId, $placeholder, $required, $isPreview);
                break;
            case 'phone':
                $html .= $this->renderPhoneInput($fieldId, $placeholder, $required, $isPreview);
                break;
            case 'textarea':
                $html .= $this->renderTextarea($fieldId, $placeholder, $required, $validation, $isPreview);
                break;
            case 'select':
                $html .= $this->renderSelect($fieldId, $field['options'] ?? [], $required, $isPreview);
                break;
            case 'radio':
                $html .= $this->renderRadio($fieldId, $field['options'] ?? [], $required, $isPreview);
                break;
            case 'checkbox':
                $html .= $this->renderCheckbox($fieldId, $field['options'] ?? [], $required, $isPreview);
                break;
            case 'number':
                $html .= $this->renderNumberInput($fieldId, $placeholder, $required, $validation, $isPreview);
                break;
            case 'date':
                $html .= $this->renderDateInput($fieldId, $required, $validation, $isPreview);
                break;
            case 'file':
                $html .= $this->renderFileInput($fieldId, $required, $validation, $isPreview);
                break;
            default:
                $html .= $this->renderTextInput($fieldId, $placeholder, $required, [], $isPreview);
        }

        // Add help text if exists
        if (!empty($field['help_text'])) {
            $html .= '<div class="form-text">' . htmlspecialchars($field['help_text']) . '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    // Individual field renderers
    private function renderTextInput($fieldId, $placeholder, $required, $validation, $isPreview) {
        $attributes = [];
        $attributes[] = 'type="text"';
        $attributes[] = 'class="form-control"';
        $attributes[] = 'id="' . $fieldId . '"';
        $attributes[] = 'name="' . $fieldId . '"';

        if ($placeholder) {
            $attributes[] = 'placeholder="' . $placeholder . '"';
        }

        if ($required) {
            $attributes[] = 'required';
        }

        if (isset($validation['minLength'])) {
            $attributes[] = 'minlength="' . (int)$validation['minLength'] . '"';
        }

        if (isset($validation['maxLength'])) {
            $attributes[] = 'maxlength="' . (int)$validation['maxLength'] . '"';
        }

        if (isset($validation['pattern'])) {
            $attributes[] = 'pattern="' . htmlspecialchars($validation['pattern']) . '"';
        }

        if ($isPreview) {
            $attributes[] = 'disabled';
        }

        return '<input ' . implode(' ', $attributes) . '>';
    }

    private function renderEmailInput($fieldId, $placeholder, $required, $isPreview) {
        $attributes = [];
        $attributes[] = 'type="email"';
        $attributes[] = 'class="form-control"';
        $attributes[] = 'id="' . $fieldId . '"';
        $attributes[] = 'name="' . $fieldId . '"';

        if ($placeholder) {
            $attributes[] = 'placeholder="' . $placeholder . '"';
        }

        if ($required) {
            $attributes[] = 'required';
        }

        if ($isPreview) {
            $attributes[] = 'disabled';
        }

        return '<input ' . implode(' ', $attributes) . '>';
    }

    private function renderPhoneInput($fieldId, $placeholder, $required, $isPreview) {
        $attributes = [];
        $attributes[] = 'type="tel"';
        $attributes[] = 'class="form-control"';
        $attributes[] = 'id="' . $fieldId . '"';
        $attributes[] = 'name="' . $fieldId . '"';

        if ($placeholder) {
            $attributes[] = 'placeholder="' . $placeholder . '"';
        }

        if ($required) {
            $attributes[] = 'required';
        }

        if ($isPreview) {
            $attributes[] = 'disabled';
        }

        return '<input ' . implode(' ', $attributes) . '>';
    }

    private function renderTextarea($fieldId, $placeholder, $required, $validation, $isPreview) {
        $attributes = [];
        $attributes[] = 'class="form-control"';
        $attributes[] = 'id="' . $fieldId . '"';
        $attributes[] = 'name="' . $fieldId . '"';
        $attributes[] = 'rows="4"';

        if ($placeholder) {
            $attributes[] = 'placeholder="' . $placeholder . '"';
        }

        if ($required) {
            $attributes[] = 'required';
        }

        if (isset($validation['minLength'])) {
            $attributes[] = 'minlength="' . (int)$validation['minLength'] . '"';
        }

        if (isset($validation['maxLength'])) {
            $attributes[] = 'maxlength="' . (int)$validation['maxLength'] . '"';
        }

        if ($isPreview) {
            $attributes[] = 'disabled';
        }

        return '<textarea ' . implode(' ', $attributes) . '></textarea>';
    }

    private function renderSelect($fieldId, $options, $required, $isPreview) {
        $attributes = [];
        $attributes[] = 'class="form-select"';
        $attributes[] = 'id="' . $fieldId . '"';
        $attributes[] = 'name="' . $fieldId . '"';

        if ($required) {
            $attributes[] = 'required';
        }

        if ($isPreview) {
            $attributes[] = 'disabled';
        }

        $html = '<select ' . implode(' ', $attributes) . '>';
        $html .= '<option value="">Choose...</option>';

        foreach ($options as $option) {
            $value = htmlspecialchars($option['value']);
            $label = htmlspecialchars($option['label']);
            $html .= '<option value="' . $value . '">' . $label . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    private function renderRadio($fieldId, $options, $required, $isPreview) {
        $html = '';

        foreach ($options as $index => $option) {
            $value = htmlspecialchars($option['value']);
            $label = htmlspecialchars($option['label']);
            $radioId = $fieldId . '_' . $index;

            $html .= '<div class="form-check">';
            $html .= '<input class="form-check-input" type="radio" name="' . $fieldId . '" id="' . $radioId . '" value="' . $value . '"';

            if ($required) {
                $html .= ' required';
            }

            if ($isPreview) {
                $html .= ' disabled';
            }

            $html .= '>';
            $html .= '<label class="form-check-label" for="' . $radioId . '">' . $label . '</label>';
            $html .= '</div>';
        }

        return $html;
    }

    private function renderCheckbox($fieldId, $options, $required, $isPreview) {
        $html = '';

        foreach ($options as $index => $option) {
            $value = htmlspecialchars($option['value']);
            $label = htmlspecialchars($option['label']);
            $checkboxId = $fieldId . '_' . $index;

            $html .= '<div class="form-check">';
            $html .= '<input class="form-check-input" type="checkbox" name="' . $fieldId . '[]" id="' . $checkboxId . '" value="' . $value . '"';

            if ($required && $index === 0) {
                $html .= ' required';
            }

            if ($isPreview) {
                $html .= ' disabled';
            }

            $html .= '>';
            $html .= '<label class="form-check-label" for="' . $checkboxId . '">' . $label . '</label>';
            $html .= '</div>';
        }

        return $html;
    }

    private function renderNumberInput($fieldId, $placeholder, $required, $validation, $isPreview) {
        $attributes = [];
        $attributes[] = 'type="number"';
        $attributes[] = 'class="form-control"';
        $attributes[] = 'id="' . $fieldId . '"';
        $attributes[] = 'name="' . $fieldId . '"';

        if ($placeholder) {
            $attributes[] = 'placeholder="' . $placeholder . '"';
        }

        if ($required) {
            $attributes[] = 'required';
        }

        if (isset($validation['min'])) {
            $attributes[] = 'min="' . (int)$validation['min'] . '"';
        }

        if (isset($validation['max'])) {
            $attributes[] = 'max="' . (int)$validation['max'] . '"';
        }

        if (isset($validation['step'])) {
            $attributes[] = 'step="' . (float)$validation['step'] . '"';
        }

        if ($isPreview) {
            $attributes[] = 'disabled';
        }

        return '<input ' . implode(' ', $attributes) . '>';
    }

    private function renderDateInput($fieldId, $required, $validation, $isPreview) {
        $attributes = [];
        $attributes[] = 'type="date"';
        $attributes[] = 'class="form-control"';
        $attributes[] = 'id="' . $fieldId . '"';
        $attributes[] = 'name="' . $fieldId . '"';

        if ($required) {
            $attributes[] = 'required';
        }

        if (isset($validation['minDate'])) {
            $attributes[] = 'min="' . htmlspecialchars($validation['minDate']) . '"';
        }

        if (isset($validation['maxDate'])) {
            $attributes[] = 'max="' . htmlspecialchars($validation['maxDate']) . '"';
        }

        if ($isPreview) {
            $attributes[] = 'disabled';
        }

        return '<input ' . implode(' ', $attributes) . '>';
    }

    private function renderFileInput($fieldId, $required, $validation, $isPreview) {
        $attributes = [];
        $attributes[] = 'type="file"';
        $attributes[] = 'class="form-control"';
        $attributes[] = 'id="' . $fieldId . '"';
        $attributes[] = 'name="' . $fieldId . '"';

        if ($required) {
            $attributes[] = 'required';
        }

        if (isset($validation['fileTypes']) && is_array($validation['fileTypes'])) {
            $accept = [];
            foreach ($validation['fileTypes'] as $type) {
                $accept[] = '.' . $type;
            }
            $attributes[] = 'accept="' . implode(',', $accept) . '"';
        }

        if ($isPreview) {
            $attributes[] = 'disabled';
        }

        return '<input ' . implode(' ', $attributes) . '>';
    }

    private function renderDateTimePicker() {
        $html = '<div class="mb-3">';
        $html .= '<label for="booking_datetime" class="form-label">Booking Date & Time <span class="text-danger">*</span></label>';
        $html .= '<div id="calendar-container"></div>';
        $html .= '<div id="time-slots-wrapper" style="display: none;">';
        $html .= '<h6 class="mt-3">Available Times for <span id="selected-date-display"></span>:</h6>';
        $html .= '<div id="time-slots" class="d-flex flex-wrap gap-2"></div>';
        $html .= '<p id="no-slots-message" class="text-muted mt-2" style="display: none;">No available slots for this date.</p>';
        $html .= '</div>';
        $html .= '<input type="hidden" id="booking_datetime" name="booking_datetime" required>';
        $html .= '</div>';

        return $html;
    }

    private function renderFormStart($formConfig) {
        $settings = $formConfig['settings'] ?? [];

        $html = '<form id="bookingForm" method="POST" action="' . BASE_PATH . '/book/create" enctype="multipart/form-data" novalidate>'; // Fixed form action

        // Add CSRF token
        if (isset($_SESSION['csrf_token'])) {
            $html .= '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
        }

        // Add required fields note
        if (!empty($settings['required_fields_note'])) {
            $html .= '<p class="text-muted small">' . htmlspecialchars($settings['required_fields_note']) . '</p>';
        }

        return $html;
    }

    private function renderFormEnd($formConfig) {
        $settings = $formConfig['settings'] ?? [];
        $submitText = $settings['submit_button_text'] ?? 'Submit Booking';

        $html = '<div class="d-grid gap-2 mt-4">';
        $html .= '<button type="submit" id="submitBtn" class="btn btn-primary btn-lg" disabled>';
        $html .= htmlspecialchars($submitText);
        $html .= '</button>';
        $html .= '</div>';

        // Add terms acceptance if specified
        if (!empty($settings['booking_terms'])) {
            $html .= '<div class="mt-3">';
            $html .= '<div class="form-check">';
            $html .= '<input class="form-check-input" type="checkbox" id="accept_terms" required>';
            $html .= '<label class="form-check-label small" for="accept_terms">';
            $html .= 'I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>';
            $html .= '</label>';
            $html .= '</div>';
            $html .= '</div>';

            // Terms modal
            $html .= $this->renderTermsModal($settings['booking_terms']);
        }

        $html .= '</form>';

        return $html;
    }

    private function renderTermsModal($terms) {
        $html = '<div class="modal fade" id="termsModal" tabindex="-1">';
        $html .= '<div class="modal-dialog modal-lg">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header">';
        $html .= '<h5 class="modal-title">Terms and Conditions</h5>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        $html .= '<div class="terms-content">' . nl2br(htmlspecialchars($terms)) . '</div>';
        $html .= '</div>';
        $html .= '<div class="modal-footer">';
        $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    // Render fallback form if no configuration is available
    private function renderFallbackForm() {
        $html = '<form id="bookingForm" method="POST" action="' . BASE_PATH . '/book/create" novalidate>'; // Fixed form action

        if (isset($_SESSION['csrf_token'])) {
            $html .= '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
        }

        $html .= '<div class="alert alert-warning">';
        $html .= 'Form configuration not available. Using basic booking form.';
        $html .= '</div>';

        // Basic fields
        $html .= '<div class="mb-3">';
        $html .= '<label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>';
        $html .= '<input type="text" class="form-control" id="name" name="name" required>';
        $html .= '</div>';

        $html .= '<div class="mb-3">';
        $html .= '<label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>';
        $html .= '<input type="email" class="form-control" id="email" name="email" required>';
        $html .= '</div>';

        $html .= '<div class="mb-3">';
        $html .= '<label for="phone" class="form-label">Phone Number</label>';
        $html .= '<input type="tel" class="form-control" id="phone" name="phone">';
        $html .= '</div>';

        $html .= $this->renderDateTimePicker();

        $html .= '<div class="d-grid gap-2 mt-4">';
        $html .= '<button type="submit" id="submitBtn" class="btn btn-primary btn-lg" disabled>Submit Booking</button>';
        $html .= '</div>';

        $html .= '</form>';

        return $html;
    }
}
