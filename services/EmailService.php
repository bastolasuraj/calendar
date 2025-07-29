<?php
// Professional Email Service using SMTP2GO/SendGrid/Brevo

require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    private $companySettings;
    private $emailTemplate;
    private $activeService;

    public function __construct() {
        $this->companySettings = new CompanySettings();
        $this->emailTemplate = new EmailTemplate();
        $this->activeService = EmailConfig::getActiveService();
        $this->initializeMailer();
    }

    // Initialize PHPMailer with configured service
    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = EmailConfig::SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Port = EmailConfig::SMTP_PORT;
            $this->mailer->SMTPSecure = EmailConfig::SMTP_ENCRYPTION;

            // Authentication
            $this->mailer->Username = EmailConfig::getUsername();
            $this->mailer->Password = EmailConfig::getPassword();

            // Default sender
            $fromEmail = EmailConfig::getFromEmail();
            $fromName = EmailConfig::getFromName();

            $this->mailer->setFrom($fromEmail, $fromName);
            $this->mailer->addReplyTo($fromEmail, $fromName);

            // Enable verbose debug output in development
            if (getenv('APP_ENV') === 'development') {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }

        } catch (Exception $e) {
            error_log('Email service initialization error: ' . $e->getMessage());
            throw new Exception('Failed to initialize email service');
        }
    }

    // Send booking confirmation email
    public function sendBookingConfirmation($formData, $accessCode, $bookingDateTime) {
        try {
            $customerEmail = $this->extractEmailFromFormData($formData);
            $customerName = $this->extractNameFromFormData($formData);

            if (empty($customerEmail)) {
                error_log('No email address found in form data');
                return false;
            }

            // Prepare template variables
            $variables = $this->prepareTemplateVariables([
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'booking_id' => $accessCode,
                'booking_datetime' => date('F j, Y \a\t g:i A', strtotime($bookingDateTime)),
                'booking_date' => date('F j, Y', strtotime($bookingDateTime)),
                'booking_time' => date('g:i A', strtotime($bookingDateTime)),
                'booking_status' => 'Pending',
                'booking_url' => $this->getBaseUrl() . BASE_PATH . '/view/details/' . $accessCode // Added booking_url
            ]);

            // Render template
            $template = $this->emailTemplate->renderTemplate(EmailTemplate::TYPE_BOOKING_CONFIRMATION, $variables);

            if (!$template) {
                error_log('Failed to render booking confirmation template');
                return false;
            }

            // Send email
            $success = $this->sendEmail(
                $customerEmail,
                $customerName,
                $template['subject'],
                $template['html_body'],
                $template['text_body']
            );

            // Send admin notification if enabled
            if ($success && $this->companySettings->getNotificationSettings()['admin_new_booking']) {
                $this->sendAdminNotification($formData, $accessCode, $bookingDateTime);
            }

            return $success;

        } catch (Exception $e) {
            error_log('Booking confirmation email error: ' . $e->getMessage());
            return false;
        }
    }

    // Send status update notification
    public function sendStatusUpdate($booking, $status) {
        try {
            $customerEmail = $booking['email'] ?? $this->extractEmailFromFormData($booking['form_data'] ?? []);
            $customerName = $booking['name'] ?? $this->extractNameFromFormData($booking['form_data'] ?? []);

            if (empty($customerEmail)) {
                error_log('No email address found for status update');
                return false;
            }

            // Prepare template variables
            $variables = $this->prepareTemplateVariables([
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'booking_id' => $booking['access_code'],
                'booking_datetime' => $booking['booking_datetime']->toDateTime()->format('F j, Y \a\t g:i A'),
                'booking_date' => $booking['booking_datetime']->toDateTime()->format('F j, Y'),
                'booking_time' => $booking['booking_datetime']->toDateTime()->format('g:i A'),
                'booking_status' => ucfirst($status),
                'booking_url' => $this->getBaseUrl() . BASE_PATH . '/view/details/' . $booking['access_code'] // Added booking_url
            ]);

            // Render template
            $template = $this->emailTemplate->renderTemplate(EmailTemplate::TYPE_STATUS_UPDATE, $variables);

            if (!$template) {
                error_log('Failed to render status update template');
                return false;
            }

            // Send email
            return $this->sendEmail(
                $customerEmail,
                $customerName,
                $template['subject'],
                $template['html_body'],
                $template['text_body']
            );

        } catch (Exception $e) {
            error_log('Status update email error: ' . $e->getMessage());
            return false;
        }
    }

    // Send booking update notification
    public function sendBookingUpdate($booking) {
        try {
            $customerEmail = $booking['email'] ?? $this->extractEmailFromFormData($booking['form_data'] ?? []);
            $customerName = $booking['name'] ?? $this->extractNameFromFormData($booking['form_data'] ?? []);

            if (empty($customerEmail)) {
                return false;
            }

            // Use confirmation template for updates
            $variables = $this->prepareTemplateVariables([
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'booking_id' => $booking['access_code'],
                'booking_datetime' => $booking['booking_datetime']->toDateTime()->format('F j, Y \a\t g:i A'),
                'booking_date' => $booking['booking_datetime']->toDateTime()->format('F j, Y'),
                'booking_time' => $booking['booking_datetime']->toDateTime()->format('g:i A'),
                'booking_status' => 'Updated (Pending Review)',
                'booking_url' => $this->getBaseUrl() . BASE_PATH . '/view/details/' . $booking['access_code'] // Added booking_url
            ]);

            $template = $this->emailTemplate->renderTemplate(EmailTemplate::TYPE_BOOKING_UPDATE, $variables);

            if (!$template) {
                return false;
            }

            return $this->sendEmail(
                $customerEmail,
                $customerName,
                'Booking Updated - ' . $template['subject'],
                $template['html_body'],
                $template['text_body']
            );

        } catch (Exception $e) {
            error_log('Booking update email error: ' . $e->getMessage());
            return false;
        }
    }

    // Send admin notification
    public function sendAdminNotification($formData, $accessCode, $bookingDateTime) {
        try {
            // Get admin emails
            $adminEmails = $this->getAdminEmails();

            if (empty($adminEmails)) {
                error_log('No admin emails found for notifications');
                return false;
            }

            $customerEmail = $this->extractEmailFromFormData($formData);
            $customerName = $this->extractNameFromFormData($formData);

            // Prepare template variables
            $variables = $this->prepareTemplateVariables([
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'booking_id' => $accessCode,
                'booking_datetime' => date('F j, Y \a\t g:i A', strtotime($bookingDateTime)),
                'booking_date' => date('F j, Y', strtotime($bookingDateTime)),
                'booking_time' => date('g:i A', strtotime($bookingDateTime)),
                'booking_status' => 'Pending',
                'admin_dashboard_url' => $this->getBaseUrl() . BASE_PATH . '/admin', // Fixed this URL
                'booking_url' => $this->getBaseUrl() . BASE_PATH . '/view/details/' . $accessCode // Added booking_url
            ]);

            // Render template
            $template = $this->emailTemplate->renderTemplate(EmailTemplate::TYPE_ADMIN_NOTIFICATION, $variables);

            if (!$template) {
                error_log('Failed to render admin notification template');
                return false;
            }

            // Send to all admins
            $success = true;
            foreach ($adminEmails as $adminEmail) {
                if (!$this->sendEmail(
                    $adminEmail['email'],
                    $adminEmail['name'],
                    $template['subject'],
                    $template['html_body'],
                    $template['text_body']
                )) {
                    $success = false;
                }
            }

            return $success;

        } catch (Exception $e) {
            error_log('Admin notification email error: ' . $e->getMessage());
            return false;
        }
    }

    // Send reminder email
    public function sendBookingReminder($booking) {
        try {
            $customerEmail = $booking['email'] ?? $this->extractEmailFromFormData($booking['form_data'] ?? []);
            $customerName = $booking['name'] ?? $this->extractNameFromFormData($booking['form_data'] ?? []);

            if (empty($customerEmail)) {
                return false;
            }

            $variables = $this->prepareTemplateVariables([
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'booking_id' => $booking['access_code'],
                'booking_datetime' => $booking['booking_datetime']->toDateTime()->format('F j, Y \a\t g:i A'),
                'booking_date' => $booking['booking_datetime']->toDateTime()->format('F j, Y'),
                'booking_time' => $booking['booking_datetime']->toDateTime()->format('g:i A'),
                'booking_url' => $this->getBaseUrl() . BASE_PATH . '/view/details/' . $booking['access_code'] // Added booking_url
            ]);

            $template = $this->emailTemplate->renderTemplate(EmailTemplate::TYPE_BOOKING_REMINDER, $variables);

            if (!$template) {
                return false;
            }

            return $this->sendEmail(
                $customerEmail,
                $customerName,
                $template['subject'],
                $template['html_body'],
                $template['text_body']
            );

        } catch (Exception $e) {
            error_log('Booking reminder email error: ' . $e->getMessage());
            return false;
        }
    }

    // Send test email
    public function sendTestEmail($testEmail, $testData) {
        try {
            $variables = $this->prepareTemplateVariables([
                'customer_name' => $testData['full_name'] ?? 'Test User',
                'customer_email' => $testEmail,
                'booking_id' => 'TEST123456',
                'booking_datetime' => date('F j, Y \a\t g:i A', time() + 86400),
                'booking_date' => date('F j, Y', time() + 86400),
                'booking_time' => date('g:i A', time() + 86400),
                'booking_status' => 'Test',
                'booking_url' => $this->getBaseUrl() . BASE_PATH . '/view/details/TEST123456', // Added booking_url
                'admin_dashboard_url' => $this->getBaseUrl() . BASE_PATH . '/admin' // Fixed this URL
            ]);

            return $this->sendEmail(
                $testEmail,
                $testData['full_name'] ?? 'Test User',
                'Email Configuration Test - ' . $this->companySettings->getCompanyName(),
                $this->getTestEmailHtml($variables),
                $this->getTestEmailText($variables)
            );

        } catch (Exception $e) {
            error_log('Test email error: ' . $e->getMessage());
            return false;
        }
    }

    // Send template test email
    public function sendTemplateTest($templateType, $testEmail, $testData) {
        try {
            $variables = $this->prepareTemplateVariables([
                'customer_name' => $testData['full_name'] ?? 'Test User',
                'customer_email' => $testEmail,
                'booking_id' => $testData['access_code'] ?? 'TEST123456',
                'booking_datetime' => date('F j, Y \a\t g:i A', strtotime($testData['booking_datetime'] ?? '+1 day')),
                'booking_date' => date('F j, Y', strtotime($testData['booking_datetime'] ?? '+1 day')),
                'booking_time' => date('g:i A', strtotime($testData['booking_datetime'] ?? '+1 day')),
                'booking_status' => 'Test',
                'booking_url' => $this->getBaseUrl() . BASE_PATH . '/view/details/' . ($testData['access_code'] ?? 'TEST123456'), // Added booking_url
                'admin_dashboard_url' => $this->getBaseUrl() . BASE_PATH . '/admin' // Fixed this URL
            ]);

            $template = $this->emailTemplate->renderTemplate($templateType, $variables);

            if (!$template) {
                return false;
            }

            return $this->sendEmail(
                $testEmail,
                $testData['full_name'] ?? 'Test User',
                '[TEST] ' . $template['subject'],
                $template['html_body'],
                $template['text_body']
            );

        } catch (Exception $e) {
            error_log('Template test email error: ' . $e->getMessage());
            return false;
        }
    }

    // Core email sending method
    private function sendEmail($toEmail, $toName, $subject, $htmlBody, $textBody = '') {
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Set recipient
            $this->mailer->addAddress($toEmail, $toName);

            // Set email content
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->addEmailFooter($htmlBody);

            if (!empty($textBody)) {
                $this->mailer->AltBody = $this->addEmailFooter($textBody, false);
            }

            // Send the email
            $result = $this->mailer->send();

            if ($result) {
                error_log("Email sent successfully to: $toEmail");
                return true;
            } else {
                error_log("Failed to send email to: $toEmail - " . $this->mailer->ErrorInfo);
                return false;
            }

        } catch (Exception $e) {
            error_log('Send email error: ' . $e->getMessage());
            return false;
        }
    }

    // Helper methods
    private function extractEmailFromFormData($formData) {
        if (is_array($formData)) {
            // Look for email field variations
            $emailFields = ['email', 'Email', 'email_address', 'Email Address'];

            foreach ($emailFields as $field) {
                if (isset($formData[$field]) && filter_var($formData[$field], FILTER_VALIDATE_EMAIL)) {
                    return $formData[$field];
                }
            }
        }

        return '';
    }

    private function extractNameFromFormData($formData) {
        if (is_array($formData)) {
            // Look for name field variations
            $nameFields = ['full_name', 'name', 'Name', 'Full Name', 'customer_name'];

            foreach ($nameFields as $field) {
                if (isset($formData[$field]) && !empty($formData[$field])) {
                    return $formData[$field];
                }
            }

            // Fallback: use first non-empty value
            foreach ($formData as $value) {
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $value;
                }
            }
        }

        return 'Customer';
    }

    private function prepareTemplateVariables($customVariables = []) {
        $baseVariables = [
            'company_name' => $this->companySettings->getCompanyName(),
            'company_email' => $this->companySettings->getCompanyEmail(),
            'company_phone' => $this->companySettings->getCompanyPhone(),
            'company_website' => $this->companySettings->getCompanyWebsite(),
            'current_date' => date('F j, Y'),
            'current_time' => date('g:i A'),
        ];

        return array_merge($baseVariables, $customVariables);
    }

    private function getAdminEmails() {
        try {
            $userModel = new User();
            $adminUsers = $userModel->getAllUsers(1, 100, ['role' => User::ROLE_ADMIN]);

            $emails = [];
            foreach ($adminUsers['users'] as $user) {
                $emails[] = [
                    'email' => $user['email'],
                    'name' => $user['name'] ?? 'Admin'
                ];
            }

            return $emails;
        } catch (Exception $e) {
            error_log('Get admin emails error: ' . $e->getMessage());
            return [];
        }
    }

    // This method is crucial for dynamic base URL in emails
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost'; // Use localhost as a fallback
        // BASE_PATH is already defined in index.php and included globally
        // So we just need the protocol and host here.
        return $protocol . '://' . $host;
    }

    private function addEmailFooter($content, $isHtml = true) {
        $footerText = $this->companySettings->getEmailSettings()['footer_text'];

        if ($isHtml) {
            return $content . '<br><br><hr><small>' . htmlspecialchars($footerText) . '</small>';
        } else {
            return $content . "\n\n---\n" . $footerText;
        }
    }

    private function getTestEmailHtml($variables) {
        return "
        <h2>Email Configuration Test</h2>
        <p>This is a test email to verify your email configuration is working correctly.</p>
        <p><strong>Company:</strong> {$variables['company_name']}</p>
        <p><strong>Test Time:</strong> {$variables['current_date']} at {$variables['current_time']}</p>
        <p>If you received this email, your email service is configured properly!</p>
        ";
    }

    private function getTestEmailText($variables) {
        return "Email Configuration Test

This is a test email to verify your email configuration is working correctly.

Company: {$variables['company_name']}
Test Time: {$variables['current_date']} at {$variables['current_time']}

If you received this email, your email service is configured properly!";
    }
}
