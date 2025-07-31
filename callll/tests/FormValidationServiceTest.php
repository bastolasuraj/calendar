<?php
// tests/FormValidationServiceTest.php

use PHPUnit\Framework\TestCase;

class FormValidationServiceTest extends TestCase
{
    private $formValidationService;
    private $mockFormConfig;
    private $mockCompanySettings;
    private $mockBookingModel; // Mock for Booking model

    protected function setUp(): void
    {
        // Create mocks for all dependencies of FormValidationService
        $this->mockFormConfig = $this->createMock(FormConfiguration::class);
        $this->mockCompanySettings = $this->createMock(CompanySettings::class);
        $this->mockBookingModel = $this->createMock(Booking::class);

        // Configure mock CompanySettings methods that FormValidationService calls
        $this->mockCompanySettings->method('getBookingSettings')->willReturn([
            'max_advance_booking_days' => 30,
            'min_advance_booking_hours' => 2,
        ]);
        // Add other `get` methods if FormValidationService uses them
        $this->mockCompanySettings->method('get')->willReturnCallback(function($key, $default) {
            switch ($key) {
                case 'timezone': return 'UTC';
                // Add other CompanySettings methods that FormValidationService might call
                default: return $default;
            }
        });


        // Configure mock Booking model methods that FormValidationService calls
        $this->mockBookingModel->method('isTimeSlotAvailable')->willReturn(true); // Assume slot is available by default

        // Instantiate FormValidationService with its mocked dependencies
        $this->formValidationService = new FormValidationService(
            $this->mockFormConfig,
            $this->mockCompanySettings,
            $this->mockBookingModel
        );
    }

    // --- Test `validateField` for various types ---

    public function testValidateFieldRequiredPasses()
    {
        $fieldConfig = ['name' => 'fullname', 'type' => 'text', 'label' => 'Full Name', 'required' => true];
        $result = $this->formValidationService->validateField($fieldConfig, 'John Doe');
        $this->assertTrue($result['valid']);
    }

    public function testValidateFieldRequiredFails()
    {
        $fieldConfig = ['name' => 'fullname', 'type' => 'text', 'label' => 'Full Name', 'required' => true];
        $result = $this->formValidationService->validateField($fieldConfig, '');
        $this->assertFalse($result['valid']);
        $this->assertContains('Full Name is required', $result['errors']);
    }

    public function testValidateFieldEmailPasses()
    {
        $fieldConfig = ['name' => 'email', 'type' => 'email', 'label' => 'Email Address', 'required' => true];
        $result = $this->formValidationService->validateField($fieldConfig, 'test@example.com');
        $this->assertTrue($result['valid']);
    }

    public function testValidateFieldEmailFailsInvalidFormat()
    {
        $fieldConfig = ['name' => 'email', 'type' => 'email', 'label' => 'Email Address', 'required' => true];
        $result = $this->formValidationService->validateField($fieldConfig, 'invalid-email');
        $this->assertFalse($result['valid']);
        $this->assertContains('Email Address must be a valid email address', $result['errors']);
    }

    public function testValidateFieldPhonePasses()
    {
        $fieldConfig = ['name' => 'phone', 'type' => 'phone', 'label' => 'Phone Number', 'required' => false];
        $result = $this->formValidationService->validateField($fieldConfig, '123-456-7890');
        $this->assertTrue($result['valid']);
    }

    public function testValidateFieldPhoneFailsShort()
    {
        $fieldConfig = ['name' => 'phone', 'type' => 'phone', 'label' => 'Phone Number', 'required' => true];
        $result = $this->formValidationService->validateField($fieldConfig, '12345');
        $this->assertFalse($result['valid']);
        $this->assertContains('Phone Number must be a valid phone number', $result['errors']);
    }

    public function testValidateFieldNumberPasses()
    {
        $fieldConfig = ['name' => 'age', 'type' => 'number', 'label' => 'Age', 'required' => false, 'min' => 18, 'max' => 65];
        $result = $this->formValidationService->validateField($fieldConfig, 30);
        $this->assertTrue($result['valid']);
    }

    public function testValidateFieldNumberFailsMin()
    {
        $fieldConfig = ['name' => 'age', 'type' => 'number', 'label' => 'Age', 'required' => true, 'min' => 18];
        $result = $this->formValidationService->validateField($fieldConfig, 17);
        $this->assertFalse($result['valid']);
        $this->assertContains('Age must be at least 18', $result['errors']);
    }

    public function testValidateFieldNumberFailsMax()
    {
        $fieldConfig = ['name' => 'age', 'type' => 'number', 'label' => 'Age', 'required' => true, 'max' => 65];
        $result = $this->formValidationService->validateField($fieldConfig, 66);
        $this->assertFalse($result['valid']);
        $this->assertContains('Age must be no more than 65', $result['errors']);
    }

    public function testValidateFieldTextareaMinLengthFails()
    {
        $fieldConfig = ['name' => 'purpose', 'type' => 'textarea', 'label' => 'Purpose', 'required' => true, 'min_length' => 10];
        $result = $this->formValidationService->validateField($fieldConfig, 'Short');
        $this->assertFalse($result['valid']);
        $this->assertContains('Purpose must be at least 10 characters', $result['errors']);
    }

    public function testValidateFieldSelectPasses()
    {
        $fieldConfig = ['name' => 'option', 'type' => 'select', 'label' => 'Option', 'required' => true, 'options' => ['value1', 'value2']];
        $result = $this->formValidationService->validateField($fieldConfig, 'value1');
        $this->assertTrue($result['valid']);
    }

    public function testValidateFieldSelectFailsInvalidOption()
    {
        $fieldConfig = ['name' => 'option', 'type' => 'select', 'label' => 'Option', 'required' => true, 'options' => ['value1', 'value2']];
        $result = $this->formValidationService->validateField($fieldConfig, 'value3');
        $this->assertFalse($result['valid']);
        $this->assertContains('Option contains an invalid selection', $result['errors']);
    }

    public function testValidateFieldDatePasses()
    {
        $fieldConfig = ['name' => 'dob', 'type' => 'date', 'label' => 'Date of Birth', 'required' => false];
        $result = $this->formValidationService->validateField($fieldConfig, '2000-01-01');
        $this->assertTrue($result['valid']);
    }

    public function testValidateFieldDateFailsInvalidFormat()
    {
        $fieldConfig = ['name' => 'dob', 'type' => 'date', 'label' => 'Date of Birth', 'required' => true];
        $result = $this->formValidationService->validateField($fieldConfig, '01/01/2000');
        $this->assertFalse($result['valid']);
        $this->assertContains('Date of Birth must be a valid date', $result['errors']);
    }

    public function testValidateFieldDateTimePassesFutureDate()
    {
        $futureDateTime = date('Y-m-d H:i', strtotime('+5 days 10:00'));
        $fieldConfig = ['name' => 'booking_time', 'type' => 'datetime', 'label' => 'Booking Time', 'required' => true];
        $result = $this->formValidationService->validateField($fieldConfig, $futureDateTime);
        $this->assertTrue($result['valid']);
    }

    public function testValidateFieldDateTimeFailsPastDate()
    {
        $pastDateTime = date('Y-m-d H:i', strtotime('-1 hour'));
        $fieldConfig = ['name' => 'booking_time', 'type' => 'datetime', 'label' => 'Booking Time', 'required' => true];
        $result = $this->formValidationService->validateField($fieldConfig, $pastDateTime);
        $this->assertFalse($result['valid']);
        // The error message for past date is now more specific from validateDateTime
        $this->assertContains('Date and time must be in the future', $result['errors']);
    }

    // --- Test `validateSubmission` ---

    public function testValidateSubmissionPassesWithValidData()
    {
        $mockFields = [
            ['name' => 'full_name', 'type' => 'text', 'label' => 'Full Name', 'required' => true],
            ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
        ];

        $this->mockFormConfig->method('getActiveConfiguration')->willReturn([
            'fields' => $mockFields
        ]);

        $formData = [
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ];

        $result = $this->formValidationService->validateSubmission($formData);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testValidateSubmissionFailsWithMissingRequiredField()
    {
        $mockFields = [
            ['name' => 'full_name', 'type' => 'text', 'label' => 'Full Name', 'required' => true],
            ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
        ];

        $this->mockFormConfig->method('getActiveConfiguration')->willReturn([
            'fields' => $mockFields
        ]);

        $formData = [
            'email' => 'jane@example.com',
            // 'full_name' is missing
        ];

        $result = $this->formValidationService->validateSubmission($formData);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('full_name', $result['errors']);
        $this->assertContains('Full Name is required', $result['errors']['full_name']);
    }

    public function testValidateSubmissionLogsUnexpectedFields()
    {
        $mockFields = [
            ['name' => 'full_name', 'type' => 'text', 'label' => 'Full Name', 'required' => true],
        ];

        $this->mockFormConfig->method('getActiveConfiguration')->willReturn([
            'fields' => $mockFields
        ]);

        $formData = [
            'full_name' => 'Jane Doe',
            'unexpected_field' => 'some_value', // This field is not in mockFields
        ];

        // We can't directly assert a log message without mocking error_log
        // but we can ensure the validation still passes if required fields are met.
        $result = $this->formValidationService->validateSubmission($formData);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }
}